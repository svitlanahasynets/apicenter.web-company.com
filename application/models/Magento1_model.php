<?php

class Magento1_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->helper('NuSOAP/nusoap');
    }

    public function getSession($projectId, $url) {

        $dbSession = $this->Projects_model->getValue('soap_session', $projectId);
        $dbTime = $this->Projects_model->getValue('soap_session_time', $projectId);

        if($dbSession != '' && $dbTime > time() && $dbSession != 0) {
            return $dbSession;
        }

        $username = $this->Projects_model->getValue('soap_username', $projectId);
        $apiKey = $this->Projects_model->getValue('soap_apikey', $projectId);

        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);
        $err = $client->getError();
        if ($err)
        {
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
            exit();
        }

        $login_parameters = [
            'username' => $username,
            'apiKey' => $apiKey
        ];

        magento1_log($projectId, 'login', json_encode($login_parameters));
        $session = $client->call('login', $login_parameters);
        magento1_log($projectId, 'login', json_encode($session));

        if(isset($session['code']) || $session == 0) {
            api2cart_log($projectId, 'optiply_connection', 'Error 404: Magento connection error. Session not received. Please check credentials.');
            magento1_log($projectId, 'error', '1');
            return false;
        }

        $this->Projects_model->saveValue('soap_session', $session, $projectId);
        $this->Projects_model->saveValue('soap_session_time', strtotime('+50 minutes'), $projectId);
        return $session;
    }

    public function getSuppliersWithItems($projectId, $url, $amount, $offset) {

        $METRIC_starttime_Mag1supplier = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start Magento1 supplierItem ' . $METRIC_starttime_Mag1supplier);

        $amount = $amount != '' ? $amount : 10;
        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);
        
        api2cart_log($projectId, 'projectcontrol', 'Mag1 supplierItem ' . 'Amount: ' . $amount . '. Offset: ' . $offset);
        
        $inString = '';
        for($i = (int)$offset; $i < (int)$offset + (int) $amount; $i++) {
            $inString .= ','.$i;
        }
        $inString = substr($inString, 1, strlen($inString)-1);

        $params = [
            'sessionId' => $session,
            'filters' => [
                'complex_filter' => [
                    'complexObjectArray' => [
                        'key' => 'product_id',
                        'value' => ['key' => 'in', 'value' => $inString]
                    ],
                ]
            ]
        ];

        magento1_log($projectId, 'catalogProductList', json_encode($params));
        $result = $client->call('catalogProductList', $params);
        magento1_log($projectId, 'catalogProductList', json_encode($result));

        //log_message('debug', '136 - Optiply Supplier Ping '. var_export($result, true));
        
        $items = [];
        $itemsCount = 0;
        foreach ($result as $item) {

            $itemData = $this->getProduct($projectId, $url, $item['product_id']);
            $stockData = $this->getStockData($projectId, $url, $item['product_id']);
            $status = $itemData['status'] == '1' ? 'ENABLED' : 'DISABLED';
            
            if ($projectId == 136){
                $supplier = $this->getSupplierName($projectId, $url, $itemData['brand']);
            }
            else{
                $supplier = $this->getSupplierName($projectId, $url, $itemData['supplier']);
            }
            
            $itemMapData = [
                'name' => $item['name'],
                'price' => (float) $itemData['cost'],
                'priceStandart' => (float) $itemData['price'],
                'minQuantity' => 1,
                'supplierItemCode' => $item['sku'],
                'stock' => substr($stockData['qty'], 0, strpos($stockData['qty'], '.')),
                'code' => $item['sku'],
                'barcode' => $itemData['g_eancode'],
                'status' => $status,
                'lotSize' => $itemData['order_tier']
            ];

            $items[$supplier]['items'][] = $itemMapData;
            $itemsCount++;
        }

        $suppliersArray = [];
        foreach ($items as $name => $data) {
            $suppliersArray[] = [
                'name' => $name,
                'items' => $data['items']
            ];
        }

        $suppliersArray['count'] = $itemsCount;
        
        //log_message('debug', '136 - Optiply Supplier Ping22 '. var_export($suppliersArray, true));
        
        return $suppliersArray;
    }

    public function checkOffsetProduct($projectId, $url, $offset) {

        if($offset != '') {
            return $offset;
        }

        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $params = [
            $session
        ];

        magento1_log($projectId, 'catalogProductList', json_encode($params));
        $result = $client->call('catalogProductList', $params);
        magento1_log($projectId, 'catalogProductList', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        return $result[0]['product_id'];
    }

    public function getProduct($projectId, $url, $id) {

        $session = $this->getSession($projectId, $url);

        $client = new nusoap_client($url.'/api/soap/?wsdl', true);

        $params = [
            $session,
            'catalog_product.info',
            $id
        ];

        magento1_log($projectId, 'catalog_product.info', json_encode($params));
        $result = $client->call('call', $params);
        magento1_log($projectId, 'catalog_product.info', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        return $result;
    }

    public function getStockData($projectId, $url, $id) {

        $session = $this->getSession($projectId, $url);

        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $params = [
            $session,
            [$id]
        ];

        magento1_log($projectId, 'catalogInventoryStockItemList', json_encode($params));
        $result = $client->call('catalogInventoryStockItemList', $params);
        magento1_log($projectId, 'catalogInventoryStockItemList', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        return $result[0];
    }

    public function getSupplierName($projectId, $url, $id) {
        $session = $this->getSession($projectId, $url);

        $client = new nusoap_client($url.'api/v2_soap/?wsdl', true);
        
        if ($projectId == 136) {
            $params = [
                $session,
                'brand'
            ];
        }
        else {
            $params = [
                $session,
                'supplier'
            ];
        }

        magento1_log($projectId, 'catalogProductAttributeOptions', json_encode($params));
        $result = $client->call('catalogProductAttributeOptions', $params);
        magento1_log($projectId, 'catalogProductAttributeOptions', json_encode($result));
        $supplier = 'default';

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        foreach ($result as $option) {
            if($option['value'] == $id) {
                $supplier = $option['label'];
            }
        }

        return $supplier;
    }

    public function getSellOrders($projectId, $url, $amount, $offset, $fromDate = '') {

        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);
        $count = 0;

        $inString = '';
        for($i = (int)$offset; $i < (int)$offset + (int) $amount; $i++) {
            $inString .= ','.$i;
        }
        $inString = substr($inString, 1, strlen($inString)-1);

        $params = [
            'sessionId' => $session,
            'filters' => [
                'complex_filter' => [
                    'complexObjectArray' => [
                        'key' => 'order_id',
                        'value' => ['key' => 'in', 'value' => $inString]
                    ],
                ]
            ]
        ];

        if($fromDate != '') {
            $params['filters']['complex_filter']['complexObjectArray'][] = [
                'key' => 'store_id',
                'value' => ['key' => 'in', 'value' => $inString]
            ];
        }

        magento1_log($projectId, 'salesOrderList', json_encode($params));
        $result = $client->call('salesOrderList', $params);
        magento1_log($projectId, 'salesOrderList', json_encode($result));

        foreach ($result as $order) {

            $orderData = $this->getOrderInfo($projectId, $url, $order['increment_id']);
            $created = str_replace(' ', 'T', $order['created_at']).'.0000Z';
            $completed = NULL;
            if($order['status'] == 'completed') {
                $completed = str_replace(' ', 'T', $order['updated_at']) . '.0000Z';
            }

            $orderMappedData = [
                'id' => $order['increment_id'],
                'created' => $created,
                'completed' => $completed,
                'amount' => (float)$order['base_subtotal'],
                'date' => $order['created_at'],
            ];

            foreach ($orderData['items'] as $lineData) {

                $orderMappedData['lines'][] = [
                    'amount' => (float)$lineData['row_total'],
                    'id' => $lineData['item_id'],
                    'name' => $lineData['name'],
                    'quantity' => (float)$lineData['qty_ordered'],
                    'unitPrice' => (float)$lineData['base_price'],
                    'code' => $lineData['sku'],
                    'product' => [
                        'code' => $lineData['sku'],
                        'barcode' => $lineData['sku'],
                        'stock' => $this->getProductStock($projectId, $url, $lineData['sku'])['qty'],
                        'name' => $lineData['name'],
                        'id' => $lineData['item_id'],
                        'priceStandart' => (float)$lineData['price'],
                        'status' => 'ENABLED'
                    ]
                ];
            }

            $orders[] = $orderMappedData;
            $count++;
        }

        $orders['count'] = $count;
        magento1_log($projectId, 'salesOrdersResult', json_encode($orders));
        return $orders;
    }

    public function checkOrderOffset($projectId, $url, $offset) {
        if($offset != '') {
            return $offset;
        }

        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $params = [
            $session
        ];

        magento1_log($projectId, 'salesOrderList_offset', json_encode($params));
        $result = $client->call('salesOrderList', $params);
        magento1_log($projectId, 'salesOrdersResult', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        return $result[0]['store_id'];
    }

    public function getOrderInfo($projectId, $url, $id) {
        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $params = [
            $session,
            $id
        ];

        magento1_log($projectId, 'salesOrderInfo', json_encode($params));
        $result = $client->call('salesOrderInfo', $params);
        magento1_log($projectId, 'salesOrderInfo', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return false;
        }

        return $result;
    }

    public function getProductStock($projectId, $url, $sku) {
        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $params = [
            $session,
            [$sku]
        ];

        magento1_log($projectId, 'req', json_encode($params));
        $result = $client->call('catalogInventoryStockItemList', $params);
        magento1_log($projectId, 'catalogInventoryStockItemList', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', '1');
            return NULL;
        }

        $stock = [
            'qty' => (int)$result[0]["qty"],
            'id' => $result[0]['product_id']
        ];
        return $stock;
    }

    public function updateStockDelivered($stockData, $projectId, $url) {

        foreach ($stockData as $item) {
            $curStock = $this->getProductStock($projectId, $url, $item['sku']);
            $stock = $curStock['qty'] + $item['qty'];
            $item['qty'] = $stock;
            $item['product_id'] = $curStock['id'];

            $this->updateProductStock($projectId, $url, $item);
        }
    }

    public function updateProductStock($projectId, $url, $item) {

        $session = $this->getSession($projectId, $url);
        $client = new nusoap_client($url.'/api/v2_soap/?wsdl', true);

        $inStock = 0;
        if($item['qty'] > 0) {
            $inStock = 1;
        }

        $params = [
            'sessionId' => $session,
            'product' => $item['product_id'],
            'data' =>
            [
                'qty' => $item['qty'],
                'is_in_stock' => $inStock
            ]
        ];

        magento1_log($projectId, 'req', json_encode($params));
        $result = $client->call('catalogInventoryStockItemUpdate', $params);
        magento1_log($projectId, 'catalogInventoryStockItemUpdate', json_encode($result));

        if(isset($result['code'])) {
            magento1_log($projectId, 'error', json_encode($params));
            return false;
        }

        return true;
    }
}