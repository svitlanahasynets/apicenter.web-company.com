<?php

class Optiply_model extends CI_Model
{
    private $projectId = 0;

    function __construct()
    {
        parent::__construct();
    }

    public function updateOrders($projectId, $orders) {

        $this->load->model('Projects_model');
        $this->projectId = $projectId;

        if(empty($orders)) {
            apicenter_logs($projectId, 'exact_sell_orders', 'No new orders', false);
            optiply_log($projectId, 'sellorder_empty', 1);
            return;
        }

        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
        $token = $this->getAccesToken($projectId);
        $importedOrders = [];

        foreach ($orders as $order) {
            optiply_log($projectId, 'sellorder_input', json_encode($order));
            if($this->checkOrderExists($order['id'], $projectId)) {
                apicenter_logs($projectId, 'exact_sell_orders', 'Order '.$order['id'].' is already imported ', false);
                continue;
            }

            $orderId = $this->pushSellOrder($token, $accountId, $order);

            foreach ($order['lines'] as $line) {

                $productId = $this->checkProduct($token, $line);

                if(!$productId) {
                    apicenter_logs($projectId, 'exact_sell_orders', 'Cannot find the product "' . $line['name'].'"', true);
                    continue;
                }

                $this->pushOrderLine($token, $accountId, $line, $orderId, $productId);
            }

            $importedOrders[] = $order['id'];
            $data = [
                'project_id' => $projectId,
                'optiply_id' => $orderId,
                'order_id' => $order['id'],
                'date' => date("Y-m-d H:i:s")
            ];
            $this->db->insert('optiply_orders', $data);
        }

        if(!empty($importedOrders)) {
            $jsonOrders = json_encode($importedOrders);
            $ordersToLine = substr($jsonOrders, 1, strlen($jsonOrders) - 2);

            apicenter_logs($projectId, 'exact_sell_orders', 'Imported orders: ' . $ordersToLine, false);
        }
    }

    public function receiveToken($projectId) {
        optiply_log($projectId, 'call_token_func', date('Y-m-d H:i:s'));
        $this->load->model('Projects_model');

        $optClientId = $this->Projects_model->getValue('optiply_clientId', $projectId);
        $clientSecret = $this->Projects_model->getValue('optiply_secret', $projectId);
        $clientUsername= $this->Projects_model->getValue('optiply_username', $projectId);
        $clientPassword = $this->Projects_model->getValue('optiply_password', $projectId);

        $logData = [
            'optiply_clientId' => $optClientId,
            'optiply_secret' => $clientSecret,
            'optiply_username' => $clientUsername,
            'optiply_password' => $clientPassword
        ];
        optiply_log($projectId, 'token_cred', json_encode($logData));

        $auth = base64_encode($optClientId.':'.$clientSecret);

        $curl = curl_init("https://dashboard.optiply.nl/api/auth/oauth/token?grant_type=password");

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            "username=".urlencode($clientUsername)."&password=".urlencode($clientPassword));
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Basic ".$auth,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $token = json_decode($response, true);
        optiply_log($projectId, 'receive_resp', $response);
        if(isset($token['access_token'])) {
            return $token;
        } else {
            return $err;
        }
    }

    public function refreshToken($projectId) {
        optiply_log($projectId, 'call_ref_func', date('Y-m-d H:i:s'));
        $optClientId = $this->Projects_model->getValue('optiply_clientId', $projectId);
        $clientSecret = $this->Projects_model->getValue('optiply_secret', $projectId);
        $clientUsername= $this->Projects_model->getValue('optiply_username', $projectId);
        $clientPassword = $this->Projects_model->getValue('optiply_password', $projectId);
        $refresh_token = $this->Projects_model->getValue('optiply_refresh_token', $projectId);

        $auth = base64_encode($optClientId.':'.$clientSecret);

        $curl = curl_init("https://dashboard.optiply.nl/api/auth/oauth/token?grant_type=refresh_token&refresh_token=".$refresh_token);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            "");
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Basic ".$auth,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $token = json_decode($response, true);
        optiply_log($projectId, 'refresh_resp', json_encode($err));
        if(isset($token['access_token'])) {
            return $token;
        } else {
            return $err;
        }
    }

    public function getAccesToken($projectId) {

        $this->load->model('Projects_model');

        $token = $this->Projects_model->getValue('optiply_token', $projectId);
        $exp = $this->Projects_model->getValue('optiply_token_exp', $projectId);
        $expaired_in = strtotime("+23 hours");

        if(empty($token)) {
            $tokentData = $this->receiveToken($projectId);

            if(!isset($tokentData['access_token'])) {
                apicenter_logs($projectId, 'optiply_setup', 'Could not connect to Optiply. '.$tokentData, true);
                optiply_log($projectId, 'optiply_setup_err', $tokentData);
                return false;
            }
            optiply_log($projectId, 'optiply_token_resp', json_encode($tokentData));
            $this->Projects_model->saveValue('optiply_token', $tokentData['access_token'], $projectId);
            $this->Projects_model->saveValue('optiply_token_exp', $expaired_in, $projectId);
            $this->Projects_model->saveValue('optiply_refresh_token', $tokentData['refresh_token'], $projectId);

            $token = $tokentData['access_token'];
            $exp = $expaired_in;
        }

        if(isset($exp) && $exp < time()) {
            $tokentData = $this->refreshToken($projectId);

            if(!isset($tokentData['access_token'])) {
                optiply_log($projectId, 'optiply_refresh_err', $tokentData);
                apicenter_logs($projectId, 'optiply_setup', 'Could not connect to Optiply. '.$tokentData, true);
                return false;
            }
            optiply_log($projectId, 'optiply_refresh_resp', json_encode($tokentData));
            $this->Projects_model->saveValue('optiply_token', $tokentData['access_token'], $projectId);
            $this->Projects_model->saveValue('optiply_token_exp', $expaired_in, $projectId);
            $this->Projects_model->saveValue('optiply_refresh_token', $tokentData['refresh_token'], $projectId);

            $token = $tokentData['access_token'];
        }

        return $token;
    }

    public function pushOrderLine($token, $accountId, $data, $orderId, $productId) {

        $url = "https://api.optiply.com/v1/sellOrderLines?accountId=".$accountId;

        $quantity = isset($data['quantity']) ? $data['quantity'] : 1;
        $curlData = [
            'data' => [
                'type' => 'sellOrderLines',
                'attributes' => [
                    'quantity' => $quantity,
                    'subtotalValue' => $data['unitPrice'],
                    'productId' => $productId,
                    'sellOrderId' => $orderId
                ]
            ]
        ];
        optiply_log($this->projectId, 'sellOrderLine_req', json_encode($curlData));

        $response = $this->apiRequest($token, $url, $curlData, 'create');
        optiply_log($this->projectId, 'sellOrderLine_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function pushSellOrder($token, $accountId, $data) {

        $url = 'https://api.optiply.com/v1/sellOrders?accountId='.$accountId;

        $curlData = [
            'data' => [
                'type' => 'sellOrders',
                'attributes' => [
                    'orderLines' => [],
                    'completed' => $data['completed'],
                    'placed' => $data['created'],
                    'totalValue' => $data['amount']
                ]
            ]
        ];
        optiply_log($this->projectId, 'sellOrder_req', json_encode($curlData));

        $response = $this->apiRequest($token, $url, $curlData, 'create');
        optiply_log($this->projectId, 'sellOrder_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function createProduct($token, $accountId, $data) {

        $url = 'https://api.optiply.com/v1/products?accountId='.$accountId;

        $data['stock'] = isset($data['stock']) ? $data['stock'] : 0;
        $data['minQuantity'] = isset($data['minQuantity']) ? $data['minQuantity'] : 0;
        $data['lotSize'] = isset($data['lotSize']) ? $data['lotSize'] : 1;
        $curlData = [
            'data' => [
                'type' => 'products',
                'attributes' => [
                    'name' => $data['name'],
                    'skuCode' => $data['code'],
                    'eanCode' => $data['barcode'],
                    'articleCode' => null,
                    'price' => $data['priceStandart'],
                    'unlimitedStock' => false,
                    'minimumPurchaseQuantity' => $data['minQuantity'],
                    'lotSize' => $data['lotSize'],
                    'stockLevel' => $data['stock'],
                    'status' => $data['status']
                ]
            ]
        ];
        optiply_log($this->projectId, 'product_create_req', json_encode($curlData));
        $response = $this->apiRequest($token, $url, $curlData, 'create');

        optiply_log($this->projectId, 'product_create_resp', json_encode($response));
        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function checkProduct($token, $data) {

        $product = $this->getProduct($token, $data);

        if(isset($product[0]['id'])) {
            return $product[0]['id'];
        } else {
            $product = $this->getProductBySKU($token, $data);

            if(isset($product[0]['id'])) {
                return $product[0]['id'];
            }
        }

        return false;
    }

    public function checkSupProduct($token, $data, $supplierId) {
        $product = $this->getSupProduct($token, $data, $supplierId);
        optiply_log($this->projectId, 'test_log', json_encode($product));
        if(isset($product['id'])) {
            return $product['id'];
        }

        return false;
    }

    public function getProduct($token, $data) {
        $url = "https://api.optiply.com/v1/products?filter[name]=".urlencode($data['name']);

        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 'product_get_req', urlencode($data['name']));
        optiply_log($this->projectId, 'product_get_resp', json_encode($response));

        if(isset($response['data']) && count($response['data']) > 0)
            return $response['data'];

        return false;
    }

    public function getProductBySKU($token, $data) {
        $url = "https://api.optiply.com/v1/products?filter[skuCode]=".urlencode($data['sku']);

        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 'productsku_get_req', urlencode($data['sku']));
        optiply_log($this->projectId, 'productSKU_get_resp', json_encode($response));

        if(isset($response['data']) && count($response['data']) > 0)
            return $response['data'];

        return false;
    }

    public function getSupProduct($token, $data, $supplierId) {
        $url = "https://api.optiply.com/v1/supplierProducts?filter[name]=".urlencode($data['name']);

        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 's_product_get_req', urlencode($data['name']));
        optiply_log($this->projectId, 's_product_get_resp', json_encode($response));

        if(isset($response['data']) && count($response['data']) > 0) {
            foreach ($response['data'] as $item) {
                if($item['attributes']['supplierId'] == $supplierId) {
                    return $item;
                }
            }
        }

        return false;
    }

    public function createSupplierProducts($token, $supplierId, $productId, $accountId, $data) {
        optiply_log($this->projectId, 's_product_get_resp', json_encode($data));
                $url = 'https://api.optiply.com/v1/supplierProducts?accountId='.$accountId;
                $data['minQuantity'] = isset($data['minQuantity']) ? $data['minQuantity'] : 0;
                $data['lotSize'] = isset($data['lotSize']) ? $data['lotSize'] : 1;
                $data['price'] = isset($data['price']) ? $data['price'] : 0;
        optiply_log($this->projectId, 's_product_get_resp', json_encode($data));     
        $curlData = [
            'data' => [
                'type' => 'supplierProducts',
                'attributes' => [
                    'name' => $data['name'],
                    'skuCode' => $data['supplierItemCode'],
                    'eanCode' => $data['barcode'],
                    'articleCode' => null,
                    'price' => $data['price'],
                    'minimumPurchaseQuantity' => $data['minQuantity'],
                    'lotSize' => $data['lotSize'],
                    'supplierId' => $supplierId,
                    'productId' => $productId,
                    'status' => $data['status']
                ]
            ]
        ];
        optiply_log($this->projectId, 'Sproduct_created_req', json_encode($curlData));

        $response = $this->apiRequest($token, $url, $curlData, 'create');

        optiply_log($this->projectId, 'Sproduct_created_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return $response;
    }

    //Supplier import process
    public function updateSuppliers($projectId, $supliers)
    {
        $METRIC_starttime_optiplysup = microtime(true);
        apicenter_logs($projectId, 'projectcontrol', 'Optiply supplier start ' . $METRIC_starttime_optiplysup, false);
        
        
        optiply_log($projectId, 'supplier_start', json_encode($supliers));
        $this->projectId = $projectId;
        if (count($supliers) < 1) {
            optiply_log($projectId, 'supplier_empty', json_encode($supliers));
            return;
        }

        $this->load->model('Projects_model');
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);

        $token = $this->getAccesToken($projectId);

        $suppliers = $products = $supEx = $updProducts = $updSupp = 0;

        foreach ($supliers as $suplier) {

            optiply_log($projectId, 'supplier_input', json_encode($suplier));
            $supplierId = $this->checkSupplier($token, $suplier);
            if ( ! $supplierId) {
                $supplierId = $this->createSupplier($token, $accountId, $suplier);
                optiply_log($projectId, 'supplier_created', $supplierId);
            } else {
                $this->updateSupplier($token, $supplierId, $suplier);
                $supEx++;
            }

            if ( ! $supplierId) {
                apicenter_logs($projectId, 'optiply_suppliers', 'Supplier not imported ' . $suplier['name'], true);
                optiply_log($projectId, 'supplier_cr_err', 1);
                continue;
            }

            $suppliers++;

            foreach ($suplier['items'] as $item) {
                //Check if product already exists
                $productId = $this->checkProduct($token, $item);

                if ( ! $productId) {
                    //Add product if not exist
                    $productId = $this->createProduct($token, $accountId, $item);

                    if ( ! $productId) {
                        optiply_log($projectId, 'product_not_created', json_encode($item));
                        continue;
                    }

                    $products++;
                } else {
                    //Update product if exists
                    $this->updateItem($token, $item, $productId, $projectId);
                    $updProducts++;
                }

                $supProductId = $this->checkSupProduct($token, $item, $supplierId);
                if ( ! $supProductId) {
                    //Create supplier's product
                    $this->createSupplierProducts($token, $supplierId, $productId, $accountId, $item);
                } else {
                    //Update supplier's product
                    $this->updateSupplierProduct($token, $supplierId, $supProductId, $item);
                }
            }
        }

        apicenter_logs($projectId, 'optiply_suppliers',
            'Imported ' . $suppliers . ' Suppliers and ' . $products . ' Products', false);
        apicenter_logs($projectId, 'optiply_suppliers', $supEx . ' suppliers and '.$updProducts.' products already existed and updated', false);
    }


    public function updateSupplier($token, $supplierId, $data) {

        $url = 'https://api.optiply.com/v1/suppliers/'.$supplierId;

        $emails = empty($data['email']) ? [] : [$data['email']];

        $curlData = [
            'data' => [
                'type' => 'suppliers',
                'id' => $supplierId,
                'attributes' => [
                    'emails' => $emails
                ]
            ]
        ];

        optiply_log($this->projectId, 'supplier_update_req', json_encode($curlData));
        $response = $this->apiRequest($token, $url, $curlData, 'update');
        optiply_log($this->projectId, 'supplier_update_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return $response;
    }

    public function updateSupplierProduct($token, $supplierId, $productId, $item) {

        $url = 'https://api.optiply.com/v1/supplierProducts/'.$productId;
        $item['minQuantity'] = isset($item['minQuantity']) ? $item['minQuantity'] : 0;
        $item['lotSize'] = isset($item['lotSize']) ? $item['lotSize'] : 1;
        $item['price'] = isset($item['price']) ? $item['price'] : 0;
        $curlData = [
            'data' => [
                'type' => 'supplierProducts',
                'id' => $productId,
                'attributes' => [
                    'name' => $item['name'],
                    'eanCode' => $item['barcode'],
                    'articleCode' => null,
                    'price' => $item['price'],
                    'minimumPurchaseQuantity' => $item['minQuantity'],
                    'lotSize' => $item['lotSize'],
                    'supplierId' => $supplierId,
                    'productId' => $productId,
                    'status' => $item['status']
                ]
            ]
        ];
        optiply_log($this->projectId, 'Sproduct_update_req', json_encode($curlData));

        $response = $this->apiRequest($token, $url, $curlData, 'update');

        optiply_log($this->projectId, 'Sproduct_update_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return $response;
    }

    public function createSupplier($token, $accountId, $data) {

        $url = "https://api.optiply.com/v1/suppliers?accountId=".$accountId;

        if(isset($data['email']) && strpos($data['email'], '.afas')) {
            $data['email'] = str_replace('.afas', '.com', $data['email']);
        }

        $email = [$data['email']];
        if($data['email'] == '') {
            $email = [];
        }

        $curlData = [
            'data' => [
                'type' => 'suppliers',
                'attributes' => [
                    'name' => $data['name'],
                    'emails' => $email
                ]
            ]
        ];
        optiply_log($this->projectId, 'create_supplier_req', json_encode($curlData));

        $response = $this->apiRequest($token, $url, $curlData, 'create');

        optiply_log($this->projectId, 'create_supplier_resp', json_encode($response));

        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function checkSupplier($token, $data) {

        $supplier = $this->searchSupplier($token, $data);
        optiply_log($this->projectId, 'supplier_search', json_encode($supplier));

        if(isset($supplier[0])) {
            return $supplier[0]['id'];
        }

        return false;
    }

    public function searchSupplier($token, $data) {

        $url = "https://api.optiply.com/v1/suppliers?filter[name]=".urlencode($data['name']);

        $response = $this->apiRequest($token, $url, $data, 'get');
        optiply_log($this->projectId, 'supplier_search_resp', json_encode($response).':'.$data['name']);

        if(isset($response['data']) && count($response['data']) > 0)
            return $response['data'];

        return false;
    }

    public function apiRequest($token, $url, $data, $action = 'get', $retry = false) {

        $this->load->model('Projects_model');

        $count = $this->Projects_model->getValue('optiply_counter', 0);
        $count = $count == '' ? 0 : $count;

        optiply_log($this->projectId, 'counter', $count);

        if($count >= 9) {
            $this->Projects_model->saveValue('optiply_counter', '0', 0);
            optiply_log($this->projectId, 'sleep_start', '1');
            sleep(2);
            optiply_log($this->projectId, 'sleep_finish', '1');
        } else {
            $this->Projects_model->saveValue('optiply_counter', $count+1, 0);
        }

        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer ".$token,
            "Content-Type: application/vnd.api+json"
        ]);

        if($action == 'create' && !empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        } elseif ($action == 'update') {
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($curl);
        $err = curl_error($curl);
        $err_no = curl_errno($curl);
        $response = json_decode($response,true);

        if(empty($response) || isset($response['error'])) {
            if($retry) {
                sleep(60);
                optiply_log($this->projectId, 'api_error_resp', $err_no.':'.json_encode($response));
                optiply_log($this->projectId, 'api_error', $err_no.':'.json_encode($err));
                optiply_log($this->projectId, 'api_error_data', $url.':'.json_encode($data));
                apicenter_logs($this->projectId, 'optiply_connection', 'Connection error '.$response['error'], true);
                die("error");
            }

            optiply_log($this->projectId, 'api_error_resp', $err_no.':'.json_encode($response));
            optiply_log($this->projectId, 'api_error', $err_no.':'.json_encode($err));
            optiply_log($this->projectId, 'api_error_data', $url.':'.json_encode($data));
            optiply_log($this->projectId, 'api_error_retry', '1');
            $response = $this->apiRequest($token, $url, $data, $action, true);

        }
        curl_close($curl);

        return $response;
    }

    public function updateBuyOrders($projectId, $orders, $updateBuyOrders = '0') {

        $this->load->model('Projects_model');
        $this->projectId = $projectId;
        $imported = 0;
        $logType = 'exact_buy_orders';

        if($updateBuyOrders == '1') {
            $logType = 'reimport_orders';
        }

        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
        $token = $this->getAccesToken($projectId);
        $importedOrders = [];

        foreach ($orders as $order) {
            optiply_log($projectId, 'buy_order_input', json_encode($order));
            if($this->checkOrderExists($order['id'], $projectId)) {
                apicenter_logs($projectId, $logType, 'Order '.$order['id'].' was already imported', false);
                continue;
            }

            $supplierId = $this->checkSupplier($token, $order);
            if(!$supplierId){
                $supplierId = $this->createSupplier($token, $accountId, ['name' => $order['name'], 'email' => '']);

                if($supplierId) {
                    apicenter_logs($projectId, 'exact_buy_orders', 'Cannot find supplier ' . $order['name'] .
                        ' so it was created', true);
                    optiply_log($projectId, 'supp_not_found_created', json_encode($supplierId));
                } else {
                    apicenter_logs($projectId, 'exact_buy_orders', 'Cannot find supplier and create supplier '.
                        $order['name'], true);
                    continue;
                }
            }

            $orderId = $this->pushBuyOrder($token, $order, $accountId, $supplierId);

            if(!$orderId)
                continue;

            $orderLines = [];
            foreach ($order['lines'] as $line) {
                $productId = $this->checkProduct($token, $line);

                if(!$productId) {
                    apicenter_logs($projectId, 'exact_buy_orders', 'Cannot find the product ' . $line['name'], true);
                    continue;
                }


                $lineId = $this->pushBuyOrderLine($token, $line, $orderId, $accountId, $productId);
                $orderLines[] = [
                    'optiply_id' => $lineId,
                    'exact_id' => $line['line_id'],
                    'optiply_order_id' => $orderId,
                    'project_id' => $projectId
                ];
            }

            $importedOrders[] = $order['id'];
            $data = [
                'project_id' => $projectId,
                'optiply_id' => $orderId,
                'order_id' => $order['id'],
                'date' => date("Y-m-d H:i:s")
            ];
            $this->db->insert('optiply_orders', $data);
            $this->insertOrderLines($orderLines);

            $imported++;
        }

        if(!empty($importedOrders)) {
            $jsonOrders = json_encode($importedOrders);
            $lineOrders = substr($jsonOrders, 1, strlen($jsonOrders) - 2);

            apicenter_logs($projectId, $logType, 'Imported Buy orders: ' . $lineOrders, false);
        }

        return $imported;
    }

    public function insertOrderLines($lines) {
        foreach ($lines as $line) {
            $this->db->insert('purchase_order_lines', $line);
        }
    }

    public function pushBuyOrder($token, $order, $accountId, $supplierId) {

        $url = 'https://api.optiply.com/v1/buyOrders?accountId='.$accountId;

        $completed = null;
        
        if($order['status'] == 30 || $order['status'] == 40) {
            $complete = $order['completed'];
        }

        $data = [
            'data' => [
                'type' => 'buyOrders',
                'attributes' => [
                    'orderLines' => [],
                    'completed' => $completed,
                    'placed' => $order['created'],
                    'totalValue' => $order['amount'],
                    'supplierId' => $supplierId
                ]
            ]
        ];
        optiply_log($this->projectId, 'buy_order_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'create');
        optiply_log($this->projectId, 'buy_order_resp', json_encode($response));
        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function pushBuyOrderLine($token, $line, $orderId, $accountId, $productId) {

        $url = 'https://api.optiply.com/v1/buyOrderLines?accountId='.$accountId;

        $data = [
            'data' => [
                'type' => 'buyOrderLines',
                'attributes' => [
                    'quantity' => $line['quantity'],
                    'subtotalValue' => $line['amount'],
                    'productId' => $productId,
                    'buyOrderId' => $orderId,
                ]
            ]
        ];
        optiply_log($this->projectId, 'buy_ord_line_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'create');
        optiply_log($this->projectId, 'buy_ord_line_resp', json_encode($response));
        if(isset($response['data']['id']))
            return $response['data']['id'];

        return 0;
    }

    public function pushReceipLine($token, $line, $lineId, $accountId) {

        $url = 'https://api.optiply.com/v1/receiptLines?accountId='.$accountId;

        $occurred = substr($line['receiptDate'], 6, 10);
        $occurred = date('Y-m-d', $occurred).'T'.date('H:i:s.Z', $occurred).'Z';

        $data = [
            'data' => [
                'type' => 'receiptLines',
                'attributes' => [
                    'occurred' => $occurred,
                    'quantity' => $line['quantity'],
                    'buyOrderLineId' => $lineId
                ]
            ]
        ];
        optiply_log($this->projectId, 'receip_ln_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'create');
        optiply_log($this->projectId, 'receip_ln_resp', json_encode($response));
        if(isset($response['data']['id']))
            return $response['data']['id'];

        return 0;
    }

    public function getBuyOrderData($projectId, $onlyActive = 0, $updating = false, $offset = 0) {

        $this->load->model('Projects_model');
        $this->projectId = $projectId;
        optiply_log($projectId, 'start_get_ord', 1);
        $token = $this->getAccesToken($projectId);

        $orders = $this->getAllBuyOrders($token, $onlyActive, $updating = false, $offset);

        return $orders;
    }

    public function getAllBuyOrders($token, $onlyActive, $updating = false, $offset = 0) {
        optiply_log($this->projectId, 'start_get_ord_f', 1);

        $url = 'https://api.optiply.com/v1/buyOrders?sort=createdAt&page[limit]=50';

        if($onlyActive == '1') {
            $url = $url.'&filter[completed]=null';
        }

        if($offset != '') {
            $url = $url.'&page[offset]='.$offset;
        }

        optiply_log($this->projectId, 'buy_ord_url', $url);
        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 'buy_ord_resp', json_encode($response));
        if(!isset($response['data'])) {
            optiply_log($this->projectId, 'buy_ord_empty_resp', json_encode($response));
            return [];
        }

        $orders = [];

        foreach ($response['data'] as $order) {

            $orderArray = [
                'id' => $order['id'],
                'completed' => $order['attributes']['completed'],
                'placed' => $order['attributes']['placed'],
                'totalValue' => $order['attributes']['totalValue'],
                'createdAt' => $order['attributes']['createdAt'],
                'updatedAt' => $order['attributes']['updatedAt']
            ];

            $lineUrl = $order['relationships']['buyOrderLines']['links']['related'];
            $supplierLink = $order['relationships']['supplier']['links']['related'];

            $orderArray['supplier'] = $this->getSupplier($token, $supplierLink);
            $orderArray['warehouse'] = $this->getWarehouse($token)[0];

            $lineData = $this->apiRequest($token, $lineUrl, [], 'get');
            optiply_log($this->projectId, 'buy_line_data', json_encode($lineData));
            optiply_log($this->projectId, 'buy_line_url', json_encode($lineUrl));

            foreach ($lineData['data'] as $line) {
                $lineArray = [
                    'quantity' => $line['attributes']['quantity'],
                    'subtotalValue' => $line['attributes']['subtotalValue'],
                ];

                $productData = $this->apiRequest($token, $line['relationships']['product']['links']['related'], [], 'get');
                optiply_log($this->projectId, 'buy_prod_data', json_encode($productData));

				$SupProductData = $this->apiRequest($token, $productData['data']['relationships']['supplierProducts']['links']['related'], [], 'get');
                optiply_log($this->projectId, 'buy_supprod_data', json_encode($SupProductData));

                $lineArray['item'] = [
                    'name' => $productData['data']['attributes']['name'],
//                    'price' => $productData['data']['attributes']['price'],
					'price' => $SupProductData['data']['attributes']['price'],
                    'skuCode' => $productData['data']['attributes']['skuCode'],
                    'articleCode' => $productData['data']['attributes']['articleCode'],
                    'stockLevel' => $productData['data']['attributes']['stockLevel'],
                ];

                $orderArray['lines'][] = $lineArray;
            }

            $orders[] = $orderArray;
        }
        optiply_log($this->projectId, 'buy_orders_to_exact', json_encode($orders).':'.count($orders));
        $offset = $offset == '' ? 0 : $offset;

        if(count($response['data']) >= 50 && !$updating)
            $this->Projects_model->saveValue('optiply_orders_offset', $offset+50, $this->projectId);

        return $orders;
    }

    public function getSupplier($token, $link) {

        $response = $this->apiRequest($token, $link, [], 'get');

        return $response['data']['attributes'];
    }

    public function getWarehouse($token) {
        $url = 'https://api.optiply.com/v1/warehouses';
        $response = $this->apiRequest($token, $url, [], 'get');

        return $response['data'];
    }

    public function getAccountId($projectId) {

        $token = $this->getAccesToken($projectId);
        $url = "https://api.optiply.com/v1/accounts";

        $response = $this->apiRequest($token, $url, [], 'get');

        if(isset($response['data'][0]['id'])) {
            $id = $response['data'][0]['id'];
            $this->Projects_model->saveValue('optiply_acc_id', $id, $projectId);
            return $id;
        }

        return false;
    }

    public function checkOrderExists($id, $projectId) {

        $query = $this->db->get_where('optiply_orders',
            [
                'project_id' => $projectId,
                'order_id' => $id
            ]);

        $order = $query->row_array();

        if(empty($order))
            return false;

        return true;
    }

    public function updateStockData($projectId, $connection) {

        $items = $this->db
            ->where('status', 0)
            ->where('project_id', $projectId)
            ->get('exact_stock_changes')
            ->result_array();
        $itemsData = [];
        $updatedItems = [];

		if ($project['id'] == 165) { log_message('debug', "stock system 165= ". var_export($items, true)); }

        foreach ($items as $item) {
            $dataArray = $this->Exactonline_model->getItem($connection, $item['item_id'], 'id');
            $dataArray['db_id'] = $item['id'];
            $itemsData[] = $dataArray;
        }

        foreach ($itemsData as $itemData) {
            $result = $this->updateItemStock($itemData, $projectId);

            if($result) {
                $updatedItems[] = $item['id'];
            }
        }

        $updatedString = implode(', ', $updatedItems);
        apicenter_logs($projectId, 'optiply_suppliers', 'Updated items stock: ' . $updatedString, false);
    }

    public function updateStockItems($projectId, $stockData) {
        foreach ($stockData as $stock) {
            $this->updateItemStockBySKU($stock, $projectId);
        }
    }

    public function updateItemStockBySKU($itemData, $projectId) {

        $token = $this->getAccesToken($projectId);
        $item = $this->getProductBySKU($token, $itemData);
        $itemId = $item[0]['id'];

        if(!isset($itemId)) {
            return false;
        }

        $url = 'https://api.optiply.com/v1/products/'.$itemId;

        $data = [
            'data' => [
                'type' => 'products',
                'id' => $itemId,
                'attributes' => [
                    'stockLevel' => $itemData['stock']
                ]
            ]
        ];
        optiply_log($projectId, 'upd_stockSKU_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'update');
        optiply_log($projectId, 'upd_stockSKU_resp', json_encode($response));
    }

    public function updateItemStock($itemData, $projectId) {

        $token = $this->getAccesToken($projectId);
        optiply_log($projectId, 'upd_data', json_encode($itemData));
        $itemId = $this->checkProduct($token, $itemData);

        if(!$itemId) {
            $this->db->where('id', $itemData['db_id'])->update('exact_stock_changes', ['status' => 2]);
            return;
        }

        $stock = isset($itemData['stock']) ? $itemData['stock'] : $itemData['quantity'];

        $url = 'https://api.optiply.com/v1/products/'.$itemId;

        $data = [
            'data' => [
                'type' => 'products',
                'id' => $itemId,
                'attributes' => [
                    'stockLevel' => $stock
                ]
            ]
        ];
        optiply_log($projectId, 'upd_stock_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'update');

        if (isset($response['data']['id'])) {
            apicenter_logs($projectId, 'importarticles', 'Updated product stock for product sku: ' . $itemData['sku'] . ' OptiplyID: ' . $response['data']['id'], false);
        } else {
            apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '. $itemData['id'], true);
        }

        optiply_log($projectId, 'upd_stock_resp', json_encode($response));
        if(isset($itemData['stock'])) {
            $this->db->where('id', $itemData['db_id'])->update('exact_stock_changes', ['status' => 1]);
            return true;
        }

        return false;
    }

    public function updateArticles($projectId, $articles) {

        $this->load->model('Afas_model');

        $token = $this->getAccesToken($projectId);
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
        $count = 0;

        foreach ($articles as $article) {
            optiply_log($projectId, 'afas_update_prod', json_encode($article));

            //check if product exists
            $productId = $this->checkProduct($token, $article);

            //get supplier
            $supplierId = '';
            $afasSupplierName = $this->Afas_model->getSupplierByItem($projectId, $article['model']);
            if(!empty($afasSupplierName)) {
                $supplierId = $this->checkSupplier($token, ['name' => $afasSupplierName]);
            }

            if(!$supplierId) {
                optiply_log($projectId, 'afas_upd_not_supl', json_encode($article));
                apicenter_logs($projectId, '', 'Cannot find supplier '.$article['name'].' for add new product', true);
                return;
            }

            $data = [
                'id' => $article['model'],
                'name' => $article['name'],
                'price' => $article['price'],
                'priceStandart' => $article['price'],
                'minQuantity' => 0,
                'supplierItemCode' => $article['ArtGroup'],
                'lotSize' => 0,
                'stock' => $article['quantity'],
                'code' => $article['model'],
                'barcode' => $article['InkSerialNumber'],
            ];

            if(!$productId) {
                //add product
                $productId = $this->createProduct($token, $accountId, $data);
                $this->createSupplierProducts($token, $supplierId, $productId, $accountId, $data);
            } else {
                //Update Product
                $this->updateItem($token, $data, $productId, $projectId);
            }
        }

        if($count > 0)
            apicenter_logs($projectId, '', 'Added '.$count.' new products', false);
    }

    public function updateStockArticles($projectId, $articles, $erpSystem = '') {

        $token = $this->getAccesToken($projectId);
        $count = 0;

        foreach ($articles as $article) {
            optiply_log($projectId, 'afas_update_stock', json_encode($article));

            $data = [];

            switch ($erpSystem) {
                case 'afas':
                    $article['sku'] = $article['model'];
                    $productId = $this->getProductBySKU($token, $article);

                    if(!$productId) {
                        optiply_log($projectId, 'afas_prod_not_f', json_encode($article));
                        continue;
                    }

                    $data = [
                        'id' => $article['model'],
                        'sku' => $article['model'],
                        'stock' => $article['quantity'],
                    ];
                    break;
                default:
                    $productId = $this->checkProduct($token, $article);

                    if(!$productId) {
                        optiply_log($projectId, 'afas_prod_not_f', json_encode($article));
                        continue;
                    }

                    $data = [
                        'id' => $article['model'],
                        'sku' => $article['model'],
                        'name' => $article['name'],
                        'price' => $article['price'],
                        'priceStandart' => $article['price'],
                        'minQuantity' => 0,
                        'supplierItemCode' => $article['ArtGroup'],
                        'lotSize' => 0,
                        'stock' => $article['quantity'],
                        'code' => $article['Articlecode_intern'],
                        'barcode' => $article['UnitId'],
                    ];
            }
            //Update stock for product
            $this->updateItemStock($data, $projectId);
        }

        if($count > 0)
            apicenter_logs($projectId, '', 'Updated '.$count.' products', false);
    }

    public function updateItem($token, $data, $itemId, $projectId) {

        $url = 'https://api.optiply.com/v1/products/'.$itemId;
        optiply_log($projectId, 'update_item_url', $url);

        $data = [
            'data' => [
                'type' => 'products',
                'id' => $itemId,
                'attributes' => [
                    'skuCode' => $data['code'],
                    'eanCode' => $data['barcode'],
                    'articleCode' => null,
                    'price' => $data['priceStandart'],
                    'unlimitedStock' => false,
                    'minimumPurchaseQuantity' => $data['minQuantity'],
                    'lotSize' => $data['lotSize'],
                    'stockLevel' => $data['stock'],
                    'status' => $data['status']
                ]
            ]
        ];
        optiply_log($projectId, 'update_item_req', json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'update');
        optiply_log($projectId, 'update_item_res', json_encode($response));

        if(isset($response['data']))
            return true;

        return false;
    }

    //Update Product from Exact Webhook
    public function addItems($projectId, $connection) {

        $items = $this->db
            ->where('status', 0)
            ->where('project_id', $projectId)
            ->limit(20)
            ->get('exact_item_changes')->result_array();

        $token = $this->getAccesToken($projectId);
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
        $updatedItems = [];
        $createdItems = [];

        foreach ($items as $item) {
            optiply_log($projectId, 'add_item_w', json_encode($item));
            $dataArray = $this->Exactonline_model->getSupplierItemById($connection, $item['item_id']);

            if(empty($dataArray)) {
                optiply_log($projectId, 'empty_item_data', json_encode($item));
                continue;
            }

            $itemArray = $this->Exactonline_model->getItem($connection, $item['item_id'], 'id', null, true);

            $dataArray['name'] = $itemArray['name'];
            $dataArray['stock'] = $itemArray['stock'];
            $dataArray['barcode'] = $itemArray['barcode'];
            $dataArray['priceStandart'] = $this->Exactonline_model->getSalesPrice($connection, $item['item_id']);
            $dataArray['code'] = $itemArray['code'];
            $dataArray['status'] = $itemArray['status'];

            optiply_log($projectId, 'add_item_data', json_encode($dataArray));

            if($itemArray['isSales'] != true)
                continue;

            $supplierData = $this->Exactonline_model->getSupplierByItem($connection, $item['item_id']);
            $supplierName = $supplierData['Name'];
            $supplierId = $this->searchSupplier($token, ['name' => $supplierName])[0]['id'];

            if(!$supplierId) {
                optiply_log($projectId, 'sup_not_found', json_encode($item));
                $supplierCreateData = [
                    'name' => $supplierData['Name'],
                    'email' => isset($supplierData['Email']) ? $supplierData['Email'] : '',
                ];
                $supplierId = $this->createSupplier($token, $accountId, $supplierCreateData);

                if(!$supplierId) {
                    optiply_log($projectId, 'sup_not_created', json_encode($item));
                    $this->db->where('id', $item['id'])->update('exact_item_changes', ['status' => 2]);
                    continue;
                }
            }

            $productId = $this->checkProduct($token, $dataArray);
            if (!$productId) {
                $productId = $this->createProduct($token, $accountId, $dataArray);

                if (!$productId) {
                    optiply_log($projectId, 'product_not_created', json_encode($item));
                    $this->db->where('id', $item['id'])->update('exact_item_changes', ['status' => 2]);
                    continue;
                }

                $this->createSupplierProducts($token, $supplierId, $productId, $accountId, $dataArray);
                $this->db->where('id', $item['id'])->update('exact_item_changes', ['status' => 1]);
                $createdItems[] = $productId;
            } else {
                $upd = $this->updateItem($token, $dataArray, $productId, $projectId);

                if($upd){
                    $this->db->where('id', $item['id'])->update('exact_item_changes', ['status' => 1]);
                    continue;
                }

                $this->db->where('id', $item['id'])->update('exact_item_changes', ['status' => 2]);
                $updatedItems[] = $productId;
            }
        }

        $updatedString = implode(', ', $updatedItems) . implode(', ', $createdItems);
        apicenter_logs($projectId, 'optiply_suppliers', 'Updated or created items: ' . $updatedString, false);
    }

    public function checkLoginCredentials($data) {
        $token = $this->receiveAccesTokenWithCredetials($data);

        if(!$token)
            return false;

        //To check connection we can just get token.
        //This function designed to get account ID from Optiply
        return true;

        /*
        $url = "https://api.optiply.com/v1/accounts";
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Bearer ".$token,
            "Content-Type: application/vnd.api+json"
        ]);

        $response = curl_exec($curl);getBuyOrderData
        curl_close($curl);

        if(isset($response['data'][0]['id'])) {
            return true;
        }

        return false;
        */
    }

    public function receiveAccesTokenWithCredetials($data) {
        optiply_log(72, 'call_alien_func', date('Y-m-d H:i:s'));
        $optClientId = $data['client_id'];
        $clientSecret = $data['client_secret'];
        $clientUsername= $data['username'];
        $clientPassword = $data['password'];

        $auth = base64_encode($optClientId.':'.$clientSecret);

        $curl = curl_init("https://dashboard.optiply.nl/api/auth/oauth/token?grant_type=password");

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,
            "username=".$clientUsername."&password=".$clientPassword);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "Authorization: Basic ".$auth,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $token = json_decode($response, true);

        if(isset($token['access_token'])) {
            return $token;
        } else {
            return false;
        }
    }

    public function updateStatusBuyOrders($projectId, $connection) {

        $orders = $this->db
            ->where('status', 0)
            ->where('project_id', $projectId)
            ->limit(20)->get('exact_order_changes')->result_array();

        $token = $this->getAccesToken($projectId);

        $ordersUpdated = [];
        $ordersToUpdate = [];

        foreach ($orders as $order) {;

            optiply_log($projectId, 'update_status_data', json_encode($order));

            $statusData = $this->Exactonline_model->getBuyOrder($connection, $order['order_id']);
            optiply_log($projectId, 'statusData', json_encode($statusData));
            if(!$statusData) {
                optiply_log($projectId, 'status_err', json_encode($order));
                $this->db->where('id', $order['id'])->update('exact_order_changes', ['status' => 2]);
                continue;
            }
            
            if($statusData['status'] != 30 && $statusData['status'] != 40) {
                optiply_log($projectId, 'status_wrong', $statusData['status']);
                apicenter_logs($projectId, 'exact_buy_orders', 'Status for order: '.$order['order_id']. ' not updated. Order status'.
                    $statusData['status']. ' does not much criteria', true);
            }

            $optiplyOrderId = $this->checkOptiplyOrderId($projectId, $order['order_id']);

            //For old orders. Can be removed shortly
            if(!$optiplyOrderId) {
                $optiplyOrderId = $this->getOrderByParams($token, $projectId, $statusData);

                if(!$optiplyOrderId) {
                    optiply_log($projectId, 'order_not_fount', json_encode($order));
                    $this->db->where('id', $order['id'])->update('exact_order_changes', ['status' => 2]);
                    continue;
                }
            }

            $orderResult = $this->updateBuyOrder($projectId, $token, $statusData, $optiplyOrderId);

            if($orderResult) {
                $ordersUpdated[] = $optiplyOrderId;
                $this->db->where('id', $order['id'])->update('exact_order_changes', ['status' => 1]);
            }

            foreach ($statusData['lines'] as $line) {
                $line['optiply_id'] = $this->getOptiplyLineId($projectId, $line['lineId']);

                if(!$line['optiply_id']) {
                    $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
                    $productId = $this->getProductBySKU($token, ['sku' => $line['code']])[0]['id'];
                    $optLineId = $this->pushBuyOrderLine($token, $line, $optiplyOrderId, $accountId, $productId);
                    $this->saveOrderLineToDb($projectId, $optLineId, $line['lineId'], $optiplyOrderId);
                }

                $this->updateOrderLine($projectId, $token, $line);
            }

            $ordersToUpdate['orders'][] = $optiplyOrderId;
            $ordersToUpdate['counts'][$optiplyOrderId] = count($statusData['lines']);
        }

        $updatedString = implode(', ', $ordersUpdated);
        apicenter_logs($projectId, 'exact_buy_orders', 'Updated statuses for orders: '.$updatedString, false);

        return $ordersToUpdate;
    }

    public function saveOrderLineToDb($projectId, $optLineId, $exactLineId, $optiplyOrderId) {
        $data = [
            'project_id' => $projectId,
            'optiply_id' => $optLineId,
            'exact_id' => $exactLineId,
            'optiply_order_id' => $optiplyOrderId
        ];

        $this->db->insert('purchase_order_lines', $data);
    }

    public function updateBuyOrder($projectId, $token, $orderData, $optiplyOrderId)
    {
        $url = 'https://api.optiply.com/v1/buyOrders/'.$optiplyOrderId;

        $completed = null;
        if($orderData['status'] == 30 || $orderData['status'] == 40) {
            // $completed = date('Y-m-d', $orderData['completed']).'T'.date('H:i:s.Z', $orderData['completed']).'Z';
            $date = substr($orderData['completed'], 6, 10);
            // $completed = date('Y-m-d\TH:i:sZ', $completed);
            $completed = date('Y-m-d', $date). 'T' . date('H:i:s.Z', $date) . 'Z';
        }

        $data = [
            'data' => [
                'type' => 'buyOrders',
                'id' => $optiplyOrderId,
                'attributes' => [
                    'completed' => $completed,
                    'totalValue' => $orderData['amount']
                ]
            ]
        ];

        optiply_log($projectId, 'order_update_req', $url.':'.json_encode($data));
        $response = $this->apiRequest($token, $url, $data, 'update');
        optiply_log($projectId, 'order_update_resp', json_encode($response));

        if(isset($response['data']['id'])) {
            return true;
        }

        return false;
    }

    public function updateStatusOrder($token, $orderId) {
        $url = 'https://api.optiply.com/v1/buyOrders/'.$orderId;

        $data = [
            'data' => [
                'type' => 'buyOrders',
                'id' => $orderId,
                'attributes' => [
                    'completed' => date('Y-m-d').'T'.date('H:i:s.Z').'Z'
                ]
            ]
        ];

        $response = $this->apiRequest($token, $url, $data, 'update');
        if(isset($response['data']['id']))
            return $response['data']['id'];

        return false;
    }

    public function checkOptiplyOrderId($projectId, $orderId) {

        $orderData = $this->db
            ->where('order_id', $orderId)
            ->where('project_id', $projectId)
            ->get('optiply_orders')
            ->result_array();

        if(isset($orderData[0]['optiply_id'])) {
            return $orderData[0]['optiply_id'];
        }

        optiply_log($projectId, 'order_not_linked', $orderId);
        return false;
    }

    public function getOrderByParams($token, $projectId, $statusData) {

        $supplierId = $this->checkSupplier($token, ['name' => $statusData['supplier']]);

        if(!$supplierId) {
            return false;
        }

        $url = 'https://api.optiply.com/v1/buyOrders?filter[supplierId][EQ]='
            .$supplierId.'&filter[placed][EQ]='
            .$statusData['date'];

        optiply_log($projectId, 'search_order_url', $url);
        $order = $this->apiRequest($token, $url, [], 'get');
        optiply_log($projectId, 'search_order_resp', json_encode($order));

        if(isset($order['data'][0]['id'])) {
            return $order['data'][0]['id'];
        }

        return false;
    }

    public function getAllBuyOrdersforCheck($token, $onlyActive, $updating = true, $offset = 0) {

        $openOptOrders = $this->getAllBuyOrders($token, $onlyActive, $updating, $offset);

        if(count($openOptOrders) >= 50) {

            $offset = $offset + 50;
            $orderRes = $this->getAllBuyOrdersforCheck($token, $onlyActive, $updating, $offset);

            if(!empty($orderRes)) {
                $openOptOrders = array_merge($orderRes, $openOptOrders);
            }
        }

        return $openOptOrders;
    }

    public function getAllOpenOrdersId($projectId) {

        $token = $this->getAccesToken($projectId);
        $openOptOrders = $this->getOrderIds($token, '1','');

        if($openOptOrders['count'] > 50) {
            $iterations = round($openOptOrders['count'] / 50);
            for($i = 1; $i <= $iterations; $i++) {
                $orders = $this->getOrderIds($token, '1',$i * 50);
                $openOptOrders['orders'] = array_merge($openOptOrders['orders'], $orders['orders']);
            }
        }

        return $openOptOrders['orders'];
    }

    public function getOrderIds($token, $active, $offset) {

        $url = 'https://api.optiply.com/v1/buyOrders?sort=createdAt&page[limit]=50';

        if($active == '1') {
            $url = $url.'&filter[completed]=null';
        }

        if($offset != '') {
            $url = $url.'&page[offset]='.$offset;
        }

        optiply_log($this->projectId, 'buy_ord_url', $url);
        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 'buy_ord_resp', json_encode($response));

        if(!isset($response['data'])) {
            optiply_log($this->projectId, 'buy_ord_empty_resp', json_encode($response));
            return [];
        }

        $orders = [];

        foreach ($response['data'] as $order) {
            $orders[] = $order['id'];
        }

        $resultData = [
            'orders' => $orders,
            'count' => $response['meta']['totalResourceCount']
        ];

        return $resultData;
    }

    public function updateProductStatuses($projectId, $statuses) {

        $token = $this->getAccesToken($projectId);

        foreach ($statuses as $status) {

            $productId = $this->checkProduct($token, $status);
            $url = 'https://api.optiply.com/v1/products/'.$productId;
            optiply_log($projectId, 'update_item_status', $url);

            $data = [
                'data' => [
                    'type' => 'products',
                    'id' => $productId,
                    'attributes' => [
                        'status' => $status['status']
                    ]
                ]
            ];
            optiply_log($projectId, 'update_item_stat_req', json_encode($data));
            $response = $this->apiRequest($token, $url, $data, 'update');
            optiply_log($projectId, 'update_item_stat_res', json_encode($response));
        }
    }

    /**
     *
     */
    public function getDeliveries($projectId, $lastDate) {

        $token = $this->getAccesToken($projectId);
        $deliveries = [];

        if($lastDate == '') {
            $lastDate = date('Y-m-d H:i:s');
        }

        $lastDate = strtotime($lastDate);

        $url = 'https://api.optiply.com/v1/receiptLines';

        $data = $this->apiRequest($token, $url, [], 'get');

        optiply_log($projectId, 'get_delivery_req', $url);
        optiply_log($projectId, 'get_delivery_resp', json_encode($data));

        if(empty($data['data'])) {
            return [];
        }

        foreach ($data['data'] as $delivery) {
            
            $deliveryDate = str_replace('T', ' ',substr($delivery['attributes']['createdAt'], 0, 19));
            $dateParsed = strtotime($deliveryDate);

            if($dateParsed < $lastDate) {
                continue;
            }

            $lineUrl = 'https://api.optiply.com/v1/buyOrderLines/'.$delivery['attributes']['buyOrderLineId'];

            optiply_log($projectId, 'get_pline_req', $lineUrl);
            $lineData = $this->apiRequest($token, $lineUrl, [], 'get');
            optiply_log($projectId, 'get_pline_resp', json_encode($lineData));

            $productId = $lineData['data']["attributes"]['productId'];
            $quant = $lineData['data']["attributes"]['quantity'];
            $prodUrl = 'https://api.optiply.com/v1/products/'.$productId;

            optiply_log($projectId, 'get_pr_req', $prodUrl);
            $sku = $this->apiRequest($token, $prodUrl, [], 'get')['data']["attributes"]['skuCode'];
            optiply_log($projectId, 'get_pr_res', json_encode($sku));

            $deliveries[] = [
                'sku' => $sku,
                'qty' => $quant
            ];
        }

        optiply_log($projectId, 'get_delivery_data', json_encode($deliveries));

        if(count($deliveries) > 0) {
            apicenter_logs($projectId, 'exact_buy_orders', 'Got '. count($deliveries). ' new receipt lines', false);
        }

        return $deliveries;
    }

    public function getProductStatusesInArray($projectId, $data) {

        $token = $this->getAccesToken($projectId);
        $statuses = [];

        foreach ($data as $name => $item) {
            $statusData = $this->getProduct($token, $item);

            if(!isset($statusData[0]['attributes'])) {
                continue;
            }

            $statuses[$name] = [
                'name' => $name,
                'status' => $statusData[0]['attributes']['status'],
                'date' => str_replace('T', ' ', substr($statusData[0]['attributes']['updatedAt'], 0, 19)),
                'sku' => $statusData[0]['attributes']['skuCode']
            ];
        }

        return $statuses;
    }

    public function getProductStatuses($projectId, $fromDate) {
        $token = $this->getAccesToken($projectId);
        $statuses = [];

        if($fromDate == '') {
            $fromDate = time();
        } else {
            $fromDate = strtotime($fromDate);
        }

        $url = "https://api.optiply.com/v1/products?sort=-updatedAt";

        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($this->projectId, 'product_get_stat', urlencode($fromDate));
        optiply_log($this->projectId, 'product_get_stat', json_encode($response));

        if(isset($response['data']) && count($response['data']) > 0) {

            foreach ($response['data'] as $product) {
            	$dateUpdate = str_replace('T', ' ', substr($product['attributes']['updatedAt'], 0, 19));
                $dateUpdate = strtotime($dateUpdate);

                if($dateUpdate < $fromDate) {
                    continue;
                }
                
                $item = [
                    'name' => $product['attributes']['name'],
                    'sku' => $product['attributes']['skuCode'],
                    'status' => $product['attributes']['status'],
                    'date' => str_replace('T', ' ', substr($product['attributes']['updatedAt'], 0, 19)),
                ];

                $statuses[$product['attributes']['name']] = $item;
            }

            return $statuses;
        }

        return [];
    }

    public function getProducts($projectId, $offset) {

        $token = $this->getAccesToken($projectId);
        $url = 'https://api.optiply.com/v1/products?page[limit]=100';
        $products = [];

        if($offset != '') {
            $url .= '&page[offset]='.$offset;
        }
        optiply_log($projectId, 'get_prod_stat', $url);
        $response = $this->apiRequest($token, $url, [], 'get');
        optiply_log($projectId, 'get_prod_stat', json_encode($response));
        foreach ($response['data'] as $item) {
            $products[] = [
                'name' => $item['name'],
                'status' => $item['status'],
                'sku' => $item['sku']
            ];
        }

        return $products;
    }

    public function updateOrdersReceipts($projectId, $receiptLines) {
        $token = $this->getAccesToken($projectId);
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);

        foreach ($receiptLines as $order => $lines) {
            foreach ($lines as $line) {
                $this->pushReceipLine($token, $line, $line['optiply_id'], $accountId);
            }
        }
    }

    public function updateOrderLines($projectId, $lines)
    {
        $token = $this->getAccesToken($projectId);

        foreach ($lines as $line) {

            $line['optiply_id'] = $this->getOptiplyLineId($projectId, $line['line_id']);
            $status = $this->updateOrderLine($projectId, $token, $line);

            if($status) {
                $this->db
                    ->where('id', $line['db_id'])
                    ->update('exact_order_line_updates', ['status' => 1]);
                continue;
            }

            $this->db
                ->where('id', $line['db_id'])
                ->update('exact_order_line_updates', ['status' => 2]);
        }
    }

    public function updateOrderLine($projectId, $token, $line)
    {
        $url = 'https://api.optiply.com/v1/buyOrderLines/'.$line['optiply_id'];
        $data = [
            'data' => [
                'type' => 'buyOrderLines',
                'id' => $line['optiply_id'],
                'attributes' => [
                    'quantity' => $line['quantity'],
                ]
            ]
        ];

        optiply_log($projectId, 'update_line_req', json_encode($data).$url);
        $response = $this->apiRequest($token, $url, $data, 'update');
        optiply_log($projectId, 'update_line_resp', json_encode($response));

        if(!isset($response['data'])) {
            return false;
        }

        return true;
    }

    public function getOptiplyLineId($projectId, $id)
    {
        $lineData = $this->db
            ->where('exact_id', $id)
            ->where('project_id', $projectId)
            ->get('purchase_order_lines')
            ->result_array();

        if(isset($lineData[0]['optiply_id'])) {
            return $lineData[0]['optiply_id'];
        }

        return false;
    }

    public function closeOrders($projectId, $orders)
    {
        $token = $this->getAccesToken($projectId);

        foreach ($orders as $order) {

            $url = 'https://api.optiply.com/v1/buyOrders/'.$order;
            $data = [
                'data' => [
                    'type' => 'buyOrders',
                    'id' => $order,
                    'attributes' => [
                        'completed' => date('Y-m-d').'T'.date('H:i:s.Z').'Z'
                    ]
                ]
            ];

            optiply_log($projectId, 'update_line_req', json_encode($data));
            $response = $this->apiRequest($token, $url, $data, 'update');
            optiply_log($projectId, 'update_line_resp', json_encode($response));
        }
    }

    public function findOrderToClose($projectId, $ordersWithDeletedLines)
    {
        $toClose = [];
        foreach ($ordersWithDeletedLines as $order => $lines) {
            $orderLines = $this->getOrderLines($projectId, $order);

            if(!$orderLines) {
                optiply_log($projectId, 'lines_not_found', $order);
                continue;
            }

            $finishedDeliveries = 0;
            foreach ($orderLines as $orderLine) {
                $receipt = $this->getReceipt($projectId, $orderLine['id']);

                if(!$receipt) {
                    optiply_log($projectId, 'receipt_not_found', $orderLine['id']);
                    continue;
                }

                optiply_log($projectId, 'finished_deliveries', $orderLine['attributes']['quantity'].'+'.
                    $receipt['attributes']['quantity']);

                if($orderLine['attributes']['quantity'] == $receipt['attributes']['quantity']) {
                    $finishedDeliveries++;
                }
            }

            optiply_log($projectId, 'lines_counting', count($lines).'+'. $finishedDeliveries. '=='.count($orderLines));
            $checkedLines = count($lines) + $finishedDeliveries;
            if($checkedLines == count($orderLines)) {
                $toClose[] = [
                    'orderId' => $order,
                    'lines_db' => $lines
                ];
            }
        }

        return $toClose;
    }

    public function getOrderLines($projectId, $orderId)
    {
        $token = $this->getAccesToken($projectId);

        $url = 'https://api.optiply.com/v1/buyOrderLines?filter[buyOrderId]='.$orderId;

        optiply_log($projectId, 'get_ordln_req', $url);
        $orderData = $this->apiRequest($token, $url, [], 'get');
        optiply_log($projectId, 'get_ordln_resp', json_encode($orderData));

        if(!isset($orderData['data'])) {
            return false;
        }

        return $orderData['data'];
    }

    public function getReceipt($projectId, $lineId)
    {
        $token = $this->getAccesToken($projectId);

        $url = 'https://api.optiply.com/v1/receiptLines/'.$lineId;

        optiply_log($projectId, 'receipt_req', $url);
        $orderData = $this->apiRequest($token, $url, [], 'get');
        optiply_log($projectId, 'receipt_resp', json_encode($orderData));

        if(!isset($orderData['data'])) {
            return false;
        }

        return $orderData['data'];
    }

    public function createDefaultSupplier($projectId)
    {
        $exists = $this->Projects_model->getValue('default_supplier', $projectId);
        if($exists != '' && $exists != 'false') {
            return;
        }

        $token = $this->getAccesToken($projectId);
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);

        $data = [
            'name' => 'default'
        ];

        $id = $this->createSupplier($token, $accountId, $data);
        if(!$id) {
            optiply_log($projectId, 'default supplier', 'not created');
        }

        $this->Projects_model->saveValue('default_supplier', $id, $projectId);
    }
}