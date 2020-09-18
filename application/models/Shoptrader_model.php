<?php
class Shoptrader_model extends CI_Model {
    
    private $passDictionary = '1234567890ABCDEFGHIJKLMNOPQRSTUVWXYZabcefghijklmnopqrstuvwxyz';

    function __construct()
    {
        parent::__construct();
        $this->load->model('Projects_model');
    }
    
    private function requestInitV1($projectId, $method = 'GET', $type, $id = '', $params = []) {
        $project   = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $url       = trim($project['store_url'], '/');
        $strOutput = 'json';
        $strUrl    = $url . '/' . $type . '?' . $strOutput;
        // standaard parameters
        if (!count($params)) $params = [];
        $params['token'] = $this->Projects_model->getValue('shoptrader_api_key', $projectId);

        $objCurl = curl_init();
        curl_setopt($objCurl, CURLOPT_URL, $strUrl);
        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, CURLOPT_TIMEOUT, 30);
        curl_setopt($objCurl, CURLOPT_USERAGENT, 'Shoptrader');
        curl_setopt($objCurl, CURLOPT_POST, true);
        curl_setopt($objCurl, CURLOPT_POSTFIELDS, http_build_query($params));

        return $objCurl;
    }

    private function requestInitV2($projectId, $method = 'GET', $type, $id = '', $params = []) {

        $project  = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $url      = trim($project['store_url'], '/');
        $token    = $this->Projects_model->getValue('shoptrader_api_key', $projectId);
        $strUrl   = $id ? $url.'/api/v2/' . $type . '/' . $id . '?token=' . $token 
                        : $url . '/api/v2/' . $type . '?token=' . $token;

        $objCurl = curl_init();
        curl_setopt($objCurl, CURLOPT_URL, $strUrl);
        curl_setopt($objCurl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($objCurl, CURLOPT_TIMEOUT, 60); 
        curl_setopt($objCurl, CURLOPT_USERAGENT, 'Shoptrader');
        curl_setopt($objCurl, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($objCurl, CURLOPT_POSTFIELDS, json_encode($params));
        curl_setopt($objCurl, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));

        return $objCurl;
    }

    public function getOrders($projectId, $offset, $amount, $sortOrder) {
        $arrParams = [];
        $arrParams['offset'] = $offset;
        $arrParams['limit']  = $amount;

        $ch = $this->requestInitV1($projectId, null, 'Api/Get/Orders/', null, $arrParams);
        $result = curl_exec($ch);
        if ($result === FALSE) {
            log_message('error', 'Get orders error '. $projectId . ':' . curl_error($result->error));
        } else {
            $result = json_decode($result);
            if (isset($result->error)) {
                log_message('error', "Project = " . $projectId . " Error = " . var_export($result->error, true));
                return false;
            }

            $finalOrders = [];
            if ($projectId == 95) {
                echo "<pre>";
                log_message('debug', var_export($result->order));
            }
            foreach ($result->order as $order) {
                if ($order->{"@attributes"}->order_id == 2) {
                    continue;
                }
                $ordeId       = $order->{"@attributes"}->order_id;
                $ordeInfo     = $order->info;
                $customer     = $order->customer;
                $customerName = explode(' ', $customer->customers_name);
                
                $appendItem = array(
                    'id'         => $ordeId,
                    'order_id'   => $ordeId,
                    'store_id'   => '',
                    'state'      => $customer->customers_state,
                    'status'     => $ordeInfo->orders_status,
                    'customer'   => array(
                        'id'         => isset($customer->{"@attributes"}) ? $customer->{"@attributes"}->customer_id : '',
                        'email'      => $customer->customers_email_address,
                        'first_name' => isset($customerName[0]) ? $customerName[0] : '',
                        'last_name'  => isset($customerName[1]) ? $customerName[1] : ''
                    ),
                    'create_at'     => $ordeInfo->date_purchased,
                    'modified_at'   => $ordeInfo->date_purchased,
                    'currency'      => $ordeInfo->currency,
                    'totals' => $this->getOrdersTotals($order->order_totals)
                );

                if(isset($order->billing) && !empty($order->billing)){ 
                    $billingInfo = $order->billing;
                    $appendItem['billing_address'] = [
                        'id'         => '',
                        'type'       => '',
                        'first_name' => isset($customerName[0]) ? $customerName[0] : '',
                        'last_name'  => isset($customerName[1]) ? $customerName[1] : '',
                        'postcode'   => $billingInfo->billing_postcode,
                        'address1'   => $billingInfo->billing_street_address,
                        'address2'   => '',
                        'phone'      => $customer->customers_telephone,
                        'city'       => $billingInfo->billing_city,
                        'country'    => $billingInfo->billing_country_iso_2,
                        'state'      => $billingInfo->billing_state,
                        'company'    => $billingInfo->billing_company,
                        'gender'     => '', 
                    ];
                }
                if(isset($order->shipingData) && !empty($order->shipingData)){ 
                    $shippingInfo = $order->postnl_checkout;
                    $appendItem['shipping_address'] = [
                        'id'         => $shippingInfo->orders_postnl_id,
                        'type'       => '',
                        'first_name' => $shippingInfo->voornaam,
                        'last_name'  => $shippingInfo->achternaam,
                        'postcode'   => $shippingInfo->postcode,
                        'address1'   => $shippingInfo->straat,
                        'address2'   => '',
                        'phone'      => $shippingInfo->telefoon,
                        'city'       => $shippingInfo->stad,
                        'country'    => $shippingInfo->billing_country_iso_2,
                        'state'      => '',
                        'company'    => $shippingInfo->bedrijf,
                        'gender'     => '',
                    ];
                    $appendItem['shipping_method'] = $shippingInfo->shipping;
                } else if(isset($order->delivery) && !empty($order->delivery)) {
                    $shippingInfo = $order->delivery;
                    $appendItem['shipping_address'] = [
                        'id'         => '',
                        'type'       => '',
                        'first_name' => isset($customerName[0]) ? $customerName[0] : '',
                        'last_name'  => isset($customerName[1]) ? $customerName[1] : '',
                        'postcode'   => $shippingInfo->delivery_postcode,
                        'address1'   => $shippingInfo->delivery_street_address,
                        'address2'   => '',
                        'phone'      => $customer->customers_telephone,
                        'city'       => $shippingInfo->delivery_city,
                        'country'    => $shippingInfo->delivery_country_iso_2,
                        'state'      => $shippingInfo->delivery_state,
                        'company'    => $shippingInfo->delivery_company,
                        'gender'     => '', 
                    ];
                    $orderInfo = $order->info;
                    $appendItem['shipping_method'] = $orderInfo->shipping_module;
                }

                $appendItem['payment_method'] = $ordeInfo->payment_method;

                if (isset($order->order_products) && !empty($order->order_products)) {
                    $products = $order->order_products;
                    $products = isset($products->order_product) && !empty($products->order_product) ? $products->order_product : [];
                    
                    foreach ($products as $product) {
                        $appendItem['order_products'][] = [
                            'product_id'            => $product->product_id,
                            'order_product_id'      => isset($product->{"@attributes"}) ? $product->{"@attributes"}->order_product_id : '',
                            'model'                 => $product->products_sku,
                            'name'                  => $product->products_name,
                            'price'                 => $product->final_price,
                            'discount_amount'       => 0,
                            'quantity'              => isset($product->products_quantity) ? $product->products_quantity : 0,
                            'total_price'           => $product->final_price,
                            'total_price_incl_tax'  => $product->products_price,
                            'tax_percent'           => $product->products_tax,
                            'tax_value'             => isset($product->tax_value) ? $product->tax_value : 0,
                            'variant_id'            => '',
                        ];
                    }
                }
                
                $appendItem['comment'] =  isset($ordeInfo->invoice_comment) ? $ordeInfo->invoice_comment : '';
                
                if($appendItem != false){
                    $finalOrders[] = $appendItem;
                }
            }
            
            return $finalOrders;
        }
    }

    private function getOrdersTotals($data) {
        $result = [];

        foreach ($data->order_total as $order) {
            $result['tax']         = 0;
            $result['amount_paid'] = 0;
  
            if ($order->class == "ot_total") {
                $result['total'] = $order->value_in;
            }

            if ($order->class == "ot_subtotal") {
                $result['subtotal'] = $order->value_in;
            }
            
            if ($order->class == "ot_shipping") {
                $result['subtotal'] = $order->value_in;
            }

            if ($order->class == "ot_tax") {
                $result['tax']+= $order->value_in;
            }

            if ($order->class == "ot_discount_coupon") {
                $result['discount'] = $order->value_in;
            }
        }

        return $result;
    }

    public function findCategory($projectId, $categoryName) {
        $ch = $this->requestInitV1($projectId, null, 'Api/Get/Categories/', null, []);
        $result = curl_exec($ch);
        if ($result === FALSE) {
            echo "cURL Error: " . curl_error($ch);
        } else {
            $result = json_decode($result, true);
            $catId = $this->searchCategory($result, $categoryName);
            if ($catId) {;
                $cat = []; 
                $cat['items'][0] = ['id' => $catId];

                return $cat;
            }
        }
        return false;
    }

    private function searchCategory($categories, $categoryName) {
        if (isset($categories['categories']) && count($categories['categories'])) {
            $categories = array_values($categories['categories']);
            $index = array_search($categoryName, array_column($categories, 'categories_name'));
            if ($index !== false) {
                return $categories[$index]['category_id'];
            }
        }
        return false; 
    }

    public function createCategory($projectId, $categoryName, $parentId, $image) {
        $arrParams = [];
        $arrParams['categories']['category'] = [
            'categories_name_languages' => [
                'dutch' => [
                    'name' => $categoryName
                ],
                'english' => [
                    'name' => $categoryName
                ]
            ],
            'isActive' => 1, 
        ];

        if ($image) {
            $arrParams['categories']['category']['images'] = [
                'image_source_url' => $image,
                'image_alt'        => '',
                'action'           => 'add'
            ];
        }

        if ($parentId) { 
            $arrParams['categories']['category']['parent_id'] = $parentId;
        }

        $ch = $this->requestInitV1($projectId, null, 'Api/Post/Category/', null, $arrParams);

        $result = curl_exec($ch);
        // echo "create category";
        // $this->dd(curl_error($result));
        if ($result === FALSE) {
            apicenter_logs($projectId, 'importarticles', 'Could not create category '. var_export(curl_error($result), true), true);
        } else {
            apicenter_logs($projectId, 'importarticles', 'Created category '. $categoryName, false);
        }
    }
    
    public function updateArticles($projectId, $articles) {
        //$i = 0;
        foreach($articles as $article){
            $productExists = $this->checkProductExists($article, $projectId);

            if(!empty($productExists)) {
                $this->updateProduct($article, $projectId, $productExists);
            } else {
                $this->createProduct($article, $projectId);
            }
        }
        return;
    }

    private function checkProductExists($article, $projectId) {
        if (isset($article['model'])) {
	        $filter = array(
		        'filter' => array(
			        'search' => 'sku:'.$article['model']
		        )
	        );
			$ch = $this->requestInitV2($projectId, 'GET', 'products', '', $filter);
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            if(isset($result['numRows']) && $result['numRows'] > 0){
	            return $result['data'][0]['productId'];
            }
            return false;
            
/*
            $productId = $this->getFromFile($projectId, $article['model']);
            if ($productId) {
                $arrParams = [];
                $arrParams['product_id'] = $productId;

                $ch     = $this->requestInitV1($projectId, 'GET', 'products', null, $arrParams);
                $result = curl_exec($ch);

                if ($result === FALSE) {
                    log_message('error', "Project = " . $projectId . " cURL Error: " . var_export(curl_error($result), true));
                } else {
                    $result = json_decode($result);
                    if (isset($result->error)) {
                        log_message('error', "Project = " . $projectId . " Error = " . var_export($result->error, true));
                        return false;
                    }
                    echo "productId = " . $productId . "<br>";
                    return $productId;//$result->products;
                }
            }
*/
        }
        return false;
    }

    private function updateProduct($article, $projectId, $productId) {
        return $this->createProduct($article, $projectId, $productId);
        
		$arrParams = [];
        $arrParams['products'] = [
            'product1'    => [
                'product_id' => $productId, // all required
                'keep_image' => 0 // 0 of 1
            ]
        ];
        $ch = $this->requestInitV1($projectId, null, 'Api/Delete/Products/', null, $arrParams);
        $result = curl_exec($ch);
        if ($result === FALSE) {
            echo curl_error($result);
            log_message('error', "Could not delete product " .$article['model'] . "Project = " . $projectId  . " Error: " . curl_error($result));
        } else {
            $result = json_decode($result);
            if (isset($result->errors)) {
                apicenter_logs($projectId, 'importarticles', 'Could not delete product '. $article['model'] . '. Result: ' . $result->errors, true);
                return false;
            }
            $this->createProduct($article, $projectId);
        }
    }

/*
    private function createProduct($article, $projectId, $productId = '') {
        $arrParams   = [];
        $product_url = strtolower($article['name']);
        $product_url = str_replace(' ', '_', $product_url);

		if($productId != ''){
			$arrParams['product_id'] = intval($productId);
		}
        $arrParams['products']['product'] = array(
            'products_name_languages' => array(
                'dutch' => array(
                    'name' => trim($article['name'])
                ),
                'english' => array(
                    'name' => trim($article['name'])
                )
            ),
            'isActive' => 1,
            // 'status' => 1,
            'description' => Array (
                'dutch' => Array (
                    'description' => '<p>' .trim($article['description']). '</p>',
                    'shortDescription' => '<p>' .trim($article['description']). '</p>',
                    'extraInformation' => '<p>' .trim($article['description']). '</p>',
                ),
                'english' => Array (
                    'description' => '<p>' .trim($article['description']). '</p>',
                    'shortDescription' => '<p>' .trim($article['description']). '</p>',
                    'extraInformation' => '<p>' .trim($article['description']). '</p>',
                )
            ),
            'products_price_ex' => $article['price'] ?:0,
            'categories' => array(
                'category1' => array(
                    'category_id' => isset($article['categories_ids']) ? $article['categories_ids'] : '',
                    'is_main_category' => 1
                ),
            ),
            'meta' => array(
                'dutch' => array(
                    'products_seo_url' => $product_url
                ),
                'english'    => array(
                    'products_seo_url' => $product_url
                )
            ),
            'products_quantity' => isset($article['quantity']) ? $article['quantity'] : 0,
            'products_sku' => trim($article['model']),
            'offer_price' => array(
                'offer_price_ex' => $article['price'] ?:0,
                'offer_price_status' => 1,
            ),
            'products_tax_class_id' => $article['tax_class_id']
        );

        if (isset($article['image'])) {
            $arrParams['products']['product']['image'] = array(
                'image1'    => array(
                    'image_source_url' => $article['image']['url'],
                    'image_alt' => '',
                    'image_name' => $article['image']['image_name'],
                    'action' => 'add'
                )
            );
        }

        $ch     = $this->requestInitV1($projectId, null, 'Api/Post/Product/', null, $arrParams);
        $result = curl_exec($ch);
        // $this->dd($result);
        if ($result === FALSE) {
            api2cart_log($projectId, 'importarticles', 'Could not create/update product '  .$article['model'] . '. Result: ' . curl_error($result));
        } else {
            $result = json_decode($result);
            if (isset($result->errors)) {
                api2cart_log($projectId, 'importarticles', 'Could not create/update product '. $article['model'] . '. Result: ' . var_export($result->errors, true));
                return false;
            }
            $this->putToFile($projectId, trim($article['model']), $result->{'@attributes'}->product_id);
            api2cart_log($projectId, 'importarticles', 'Created/updated product ' . $article['model']);
        }
    }
*/

    private function createProduct($article, $projectId, $productId = '') {
        
		$Prodqty = $this->getItemQty($projectId, $article['model']);
				
		$saveData = array(
	        'model' => $article['model'],
	        'sku' => $article['model'],
	        'name' => trim($article['name']),
	        'quantity' => ($Prodqty > 0) ? $Prodqty : 0,
	        'price' => $article['price'] ?:0,
	        'status' => 1,
			'categoryId' => isset($article['categories_ids']) ? $article['categories_ids'] : '',
			'taxRate' => 0,
			'description' => 'asdf',
			'descriptions' => array(
				'description' => 'test'
			)
        );
        if($productId != ''){
			$saveData['productId'] = $productId;
			$saveData['uprid'] = $productId;
        }
//         echo '<pre>';print_r($saveData);exit;

/*
        if (isset($article['image'])) {
            $saveData['image'] = array(
                'image1'    => array(
                    'image_source_url' => $article['image']['url'],
                    'image_alt' => '',
                    'image_name' => $article['image']['image_name'],
                    'action' => 'add'
                )
            );
        }
*/

		if($productId != ''){
			$ch = $this->requestInitV2($projectId, 'PATCH', 'products', $productId, $saveData);
		} else {
			$ch = $this->requestInitV2($projectId, 'POST', 'products', $productId, $saveData);
		}
        $result = curl_exec($ch);
//         var_dump($result);exit;
        // $this->dd($result);
        if ($result === FALSE) {
            apicenter_logs($projectId, 'importarticles', 'Could not create/update product '  .$article['model'] . '. Result: ' . curl_error($result), true);
        } else {
            $result = json_decode($result);
            if (isset($result->errors)) {
                apicenter_logs($projectId, 'importarticles', 'Could not create/update product '. $article['model'] . '. Result: ' . var_export($result->errors, true), true);
                return false;
            }
            $this->putToFile($projectId, trim($article['model']), $result[0]->productId);
            apicenter_logs($projectId, 'importarticles', 'Created/updated product ' . $article['model'], false);
        }
    }

    private function putToFile($projectId, $sku, $productId) {
        $folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $file = $folder.'skus.txt';

        if(file_exists($file)){
            $skus = json_decode(file_get_contents($file), true);
        } else {
            $skus = [];
        }
        
        $skus[$sku] = $productId;

        file_put_contents($file, json_encode($skus));
    }

    private function getFromFile($projectId, $sku) {
        $folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $file = $folder.'skus.txt';

        if(file_exists($file)){
            $skus = json_decode(file_get_contents($file), true);
            if (isset($skus[$sku])) {
                return $skus[$sku];
            }
        }

        return false;
    }

    public function createCustomer($projectId, $customerData) {
        $pass = $this->password_generate(8);
        
        $parameters = [
            "emailAddress"      => $customerData['email'],
            "password"          => '',
            "firstName"         => isset($customerData['first_name']) ? $customerData['first_name'] : ' ',
            "lastName"          => isset($customerData['last_name']) ? $customerData['last_name'] : ' ',
            "gender"            => isset($customerData['gender']) ? $customerData['gender'] : '',
            "phoneNumber"       => $customerData['phone'],
            "gender"            => "",
            "faxNumber"         => "",
            "bankAccountNumner" => "",
            "discountGroupId"   => "1"
        ];
        
        $ch     = $this->requestInitV2($projectId, 'POST', 'customers', null, $parameters);
        $result = curl_exec($ch);

        if ($result === FALSE) {
            echo "cURL Error: ".curl_error($objCurl);
        } else {
            $result = json_decode($result);
            if(isset($result->errors)){
                apicenter_logs($projectId, 'importcustomers', 'Could not created customer '. $customerData['email'].'. Result: '.print_r($result, true), true);
            } else {
                apicenter_logs($projectId, 'importcustomers', 'Created customer '.$result->emailAddress, false);
            }
        }
    }
	
	public function updateStockArticles($projectId, $articleData){
		
		$saveData = array(
	        'model' => $articleData['model'],
	        'sku' => $articleData['model'],
	        'quantity' => $articleData['quantity'],
        );
        if($productId != ''){
			$saveData['productId'] = $productId;
			$saveData['uprid'] = $productId;
        }

		if($productId != ''){
			$ch = $this->requestInitV2($projectId, 'PATCH', 'products', $productId, $saveData);
		} else {
			$ch = $this->requestInitV2($projectId, 'POST', 'products', $productId, $saveData);
		}
		
        $result = curl_exec($ch);

        if ($result === FALSE) {
            apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$articleData['model'].'. Result: '. curl_error($result), true);
        } else {
            $result = json_decode($result);
            if (isset($result->errors)) {
                apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$articleData['model'].'. Result: '. var_export($result->errors, true), true);
                return false;
            }
            $this->putToFile($projectId, trim($articleData['model']), $result->{'@attributes'}->product_id);
            apicenter_logs($projectId, 'importarticles', 'Updated product stock for product ' . $articleData['model'], false);
        }
	}
	
	public function getItemQty($projectId, $itemCode){
				
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_Stock_Cuma_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_Stock_Cuma_App) && count($data->Profit_Stock_Cuma_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Profit_Stock_Cuma_App);
			if(!empty($itemData)){
				return intval($itemData['StockActual']);
			}
		}
		return 0;
	}
	
    private function object2array($data) {
        if (is_array($data) || is_object($data))
        {
            $result = array();
            foreach ($data as $key => $value)
            {
                $result[$key] = $this->object2array($value);
            }
            return $result;
        }
        return $data;
    }
	
    private function password_generate($chars) {
      $data = $this->passDictionary;

      return substr(str_shuffle($data), 0, $chars);
    }

    public function dd($dd) {
        echo "<pre>";
        var_dump($dd);
        echo "</pre>";
    }
}