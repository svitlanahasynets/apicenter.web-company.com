<?php
class Cscart_model extends CI_Model {

    private $compareProductField = [
        'product_code'      => 'model',
        'product'           => 'name',
        'full_description'  => 'description',
        // 'tax_ids'           => 'tax_class_id',
        'price'             => 'price',
        'main_category'     => 'categories_ids',
        'amount'            => 'quantity',
        // 'main_pair'         => 'image'
    ]; 
    /*
        N—new
        A—active
        P—pending
        D—disabled
        I-canceled
        O-open
        C-complete

        12 = Open, 
        20 = Partial, 
        21 = Complete, 
        45 = Cancelled.
    */
    private $orderStatus = [
        "P" => 20, 
        "C" => 21,
        "O" => 12,
        "I" => 45
    ];

    function __construct()
    {
        parent::__construct();
        $this->load->model('Projects_model');
    }

    private function requestInit($projectId, $method = 'GET', $url, $product_params = []) {
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $storeUrl   = $project['store_url'];
        $apiKey     = $this->Projects_model->getValue('cscart_api_key', $projectId);
        $user       = $this->Projects_model->getValue('cscart_user', $projectId);
        $token      = base64_encode("$user:$apiKey");
        $ch         = curl_init($storeUrl . $url);
        
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($method == 'GET') {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
        } else if ($method == 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_params));
            curl_setopt($ch, CURLOPT_POST, true);
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($product_params));
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json",'Authorization: Basic ' . $token));

        return $ch; 
    }

    public function findCategory($projectId, $categoryName) {
        $ch     = $this->requestInit($projectId, 'GET', "api/categories?items_per_page=10000");
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        $cat    = [];
        // log_message('debug', 'fix error');
        if (!$result['categories']) return $cat;
        $key    = array_search($categoryName, array_column($result['categories'], 'category'));
        
        if ($key !== false) {
            $cat['items'][0] = ['id' => $result['categories'][$key]['category_id']];
        }

        if (curl_errno($ch)) {
            $this->loggError($projectId,  curl_error($ch));
        }

        return $cat;
    }

    public function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
        $product_params['category']   = $categoryName;
        $product_params['company_id'] = $this->Projects_model->getValue('company_id', $projectId);
        $product_params['status']     = "A";

        if ($parentId) {
            $product_params['parent_id'] = $parentId;
        }

        $ch             = $this->requestInit($projectId, 'POST', "api/2.0/categories", $product_params);
        $result         = curl_exec($ch);
        $result         = json_decode($result, true);
        $category['id'] = [];
        
        if (isset($result['category_id'])) {
            $category['id'] = $result['category_id'];
            apicenter_log($projectId, 'importarticles', 'Created category '.$categoryName, false);
        } else {
            apicenter_log($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.print_r($result, true), true);
        }
        return $category;
    }

    public function updateArticles($projectId, $articles) {
        foreach($articles as $article){
            $productExists = $this->checkProductExists($article, $projectId);
            if(!empty($productExists) && isset($productExists['product_id'])){
                // Update product
                $this->updateProduct($article, $projectId, $productExists);
            } else {
                // Create product
                $this->createProduct($article, $projectId);
            }
        }
    }

    private function checkProductExists($productData, $projectId) {
        // $get_params = "api/products?pcode=" . $productData['model'] . "&q=" . trim(str_replace(" ", "+", $productData['name']));
        $get_params = "api/products?pcode=" . $productData['model'];// . "&q=" . trim(urlencode($productData['name']));
        $ch         = $this->requestInit($projectId, 'GET', $get_params);
        $result     = curl_exec($ch);
        if (curl_errno($ch)) $this->loggError($projectId,  curl_error($ch));
        $result     = json_decode($result, true);
        
        if (count($result['products'])) {
            $key = array_search($productData['model'], array_column($result['products'], 'product_code'));
            if ($key !== false) {
                //return $result['products'][$key];
                return  $this->searchProductForId( $projectId, $result['products'][$key]['product_id']);
            }
        }
    }
    
    private function searchProductForId( $projectId, $productId) {
        $get_params = "api/products/" . $productId;
        $ch         = $this->requestInit($projectId, 'GET', $get_params);
        $result     = curl_exec($ch);

        if (curl_errno($ch)) $this->loggError($projectId,  curl_error($ch));
        $result     = json_decode($result, true);

        return $result;
    }

    private function updateProduct($article, $projectId, $productExists) {

        $updateField = [];
        foreach($this->compareProductField as $compareKey => $value) {
            if (isset($productExists[$compareKey])) {
                if ($productExists[$compareKey] != $article[$value]) {
                    $updateField[$compareKey] = $article[$value];
                }
            }
        }
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'overrideCScartProductUpdate')){
				$updateField = $this->$projectModel->overrideCScartProductUpdate($productExists, $updateField);
			}
		}
        
        //log_message('debug', 'importarticles - content'. var_export($updateField, true));
        //log_message('debug', 'importarticles - content'. var_export(count($updateField), true));

        if (count($updateField)) {
            $url    = 'api/2.0/products/' . $productExists['product_id'];
            $ch     = $this->requestInit($projectId, 'PUT', $url, $updateField);
            $result = curl_exec($ch);
            $result = json_decode($result, true);
            apicenter_log($projectId, 'importarticles', 'Updated product '.$productExists['product_code'], false);
        } else {
        	//log_message('debug', 'importarticles - content'. var_export($updateField, true));
        	
        	apicenter_log($projectId, 'importarticles', 'Could not update product '.$productExists['product_code'].'. Result: '.var_export($result, true), true);
        }
    }

    private function createProduct($article, $projectId) {
        $product_params['product']          = $article['name'];
        $product_params['category_ids']     = $article['categories_ids'];
        $product_params['price']            = $article['price'];
        $product_params['full_description'] = $article['description'];
        $product_params['product_code']     = $article['model'];
        if (isset($article['quantity'])) $product_params['amount'] = $article['quantity'];

        if (isset($article['image'])) {
            $product_params['main_pair'] = [
                "detailed" => [
                    "object_type" => "product",
                    "type" => "M",
                    "image_path"=> $article['image']['url'],
                    "alt" => "",
                    "image_x" => "500",
                    "image_y" => "500",
                    "http_image_path" => $article['image']['url'],
                    "https_image_path" => $article['image']['url'],
                    "absolute_path" => $article['image']['path']
                ]
            ];
        }
        $ch = $this->requestInit($projectId, 'POST', "api/2.0/products", $product_params);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        if(isset($result['product_id']) && $result['product_id'] > 0) {
            apicenter_log($projectId, 'importarticles', 'Created product '.$article['model'], false);
        } else {
            apicenter_log($projectId, 'importarticles', 'Could not create product '.$article['model'] . ' categorie-info: ' . $product_params['category_ids'] . '. Result: '.var_export($result, true), true);
        }
        return $result;
    }

    public function createCustomer ($projectId, $customerData) {
        $customer_params = [];
        $customer_params["company_id"]  = $this->Projects_model->getValue('company_id', $projectId);
        $customer_params["user_type"]   = "C";
        $customer_params["firstname"]   = isset($customerData["first_name"]) ? trim($customerData["first_name"]) : " ";
        $customer_params["lastname"]    = isset($customerData["last_name"]) ? trim($customerData["last_name"]) : " ";
        $customer_params["email"]       = isset($customerData["email"]) ? trim($customerData["email"]) : " ";
        $customer_params['b_firstname'] = isset($customerData['address_book_first_name_1']) ? trim($customerData["address_book_first_name_1"]) : " ";
        $customer_params['b_lastname']  = isset($customerData['address_book_last_name_1']) ? trim($customerData["address_book_last_name_1"]) : " ";
        $customer_params['b_address']   = isset($customerData['address_book_address1_1']) ? trim($customerData["address_book_address1_1"]) : " ";
        $customer_params['b_country']   = isset($customerData['address_book_country_1']) ? trim($customerData["address_book_country_1"]) : " ";
        $customer_params['b_zipcode']   = isset($customerData['address_book_postcode_1']) ? trim($customerData["address_book_postcode_1"]) : " ";
        $customer_params['b_phone']     = isset($customerData['address_book_phone_1']) ? trim($customerData["address_book_phone_1"]) : " ";
        $customer_params['b_city']      = isset($customerData["address_book_city_1"]) ? trim($customerData["address_book_city_1"]) : " ";
        
        $ch = $this->requestInit($projectId, 'POST', "api/2.0/users", $customer_params);
        $result = curl_exec($ch);
        $result = json_decode($result, true);

        if(isset($result['user_id']) && $result['user_id'] > 0){
            apicenter_log($projectId, 'importcustomers', 'Created customer '.$customerData['email'], false);
        } else {
            apicenter_log($projectId, 'importcustomers', 'Could not created customer '.$customerData['email'].'. Result: '.print_r($result, true), true);
        }
        return $result;
    }

    public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc') {
        $page = ($offset == 0) ? 1 : ($offset / $amount);
        $reauetParams = "api/orders/?page=" .$page. "&items_per_page=" . $amount . "&sort_order=" . $sortOrder;
        // $reauetParams = "api/2.0/orders/?items_per_page=" . $amount;
        $ch = $this->requestInit($projectId, 'GET', $reauetParams);
        $result = curl_exec($ch);
        $result = json_decode($result, true);
        $project = $this->db->get_where('projects', array('id' => $projectId))->row_array();

        if (isset($result['orders']) && count($result['orders'])) {
            foreach ($result['orders'] as $data) {
                
                $ch         = $this->requestInit($projectId, 'GET', "api/2.0/orders/" . $data['order_id']);
                $order      = curl_exec($ch);
                $order      = json_decode($order, true);       
                
                if ($projectId == 53) { 
                    log_message('debug', 'ORDER-BEFORE ' . var_export($order, true)); 
                    
                    if(isset($order['b_address']) && !empty($order['b_address'])) {
                        $ib_name = explode(' ', $order['b_firstname']);
                        
                        $ib_firstname = isset($ib_name[0]) ? $ib_name[0] : '';
                        
                        $removed = array_shift($ib_name);
                        $ib_lastname = implode(' ', $ib_name);
                        
                        $ib_company = $order['b_lastname'];
                    }
                    
                    if(isset($order['s_address']) && !empty($order['s_address'])) {
                        $is_name = explode(' ', $order['s_firstname']);
                        
                        $is_firstname = isset($is_name[0]) ? $is_name[0] : '';
                        
                        $removed = array_shift($is_name);
                        $is_lastname = implode(' ', $is_name);

                        $is_company = $order['s_lastname'];
                    }
                }
                else {
                    if(isset($order['b_address']) && !empty($order['b_address'])) {
                        $ib_firstname = $order['b_firstname'];
                        $ib_lastname = $order['b_lastname'];
                        $ib_company = isset($order['company_id']) ? $order['company_id'] : '';
                    }
                    
                    if(isset($order['s_address']) && !empty($order['s_address'])) {
                        $is_firstname = $order['s_firstname'];
                        $is_lastname = $order['s_lastname'];
                        $is_company = isset($order['company_id']) ? $order['company_id'] : '';
                    }
                }
                
                
                $appendItem = array(
                    'id' => $project['erp_system'] == 'afas' ? $order['order_id'] : '',//$order['increment_id'],
                    'order_id' => $order['order_id'],
                    'store_id' => $order['company_id'],
                    'state' => $order['b_state'],
                    'status' => isset($this->orderStatus[$order['status']]) ? $this->orderStatus[$order['status']] : $order['status'],
                    'customer' => array(
                        'id' => isset($order['user_id']) ? $order['user_id'] : '',
                        'email' => $order['email'],
                        'first_name' => isset($order['firstname']) ? $order['firstname'] : '',
                        'last_name' => isset($order['lastname']) ? $order['lastname'] : ''
                    ),
                    //fields aren't in cs-cart api
                    'create_at' => date('Y-m-d', $order['timestamp']),
                    'modified_at' => isset($order['updated_at']) ? $order['updated_at'] : '',
                    //-----------------------------------//
                    'currency' => isset($order['currency']) ? $order['currency'] : $order['secondary_currency'],
                    'totals' => array(
                        'total' => $order['total'],
                        'subtotal' => $order['subtotal'],
                        'shipping' => $order['shipping_cost'],
                        'tax' => $this->searchData($order['taxes'],'tax_subtotal'),
                        'discount' => $order['discount'],
                        'amount_paid' => isset($order['total_paid']) ? $order['total_paid'] : 0
                    )
                );
                if(isset($order['b_address']) && !empty($order['b_address'])) {
                    $appendItem['billing_address'] = array(
                        'id' => '',
                        'type' => 'billing',
                        'first_name' => $ib_firstname,
                        'last_name' => $ib_lastname,
                        'postcode' => $order['b_zipcode'],
                        'address1' => $order['b_address'],
                        'address2' => $order['b_address_2'] != "" ? $order['b_address_2'] : '',
                        'phone' => $order['b_phone'],
                        'city' => $order['b_city'],
                        'country' => $order['b_country'],
                        'state' => isset($order['b_state']) ? $order['b_state'] : '',
                        'company' => $ib_company,
						'company_name' => isset($order['company']) ? $order['company'] : '',
                        'gender' => isset($order['gender']) ? $order['gender'] : '',
                    );
                }
                if(isset($order['s_address']) && !empty($order['s_address'])) {
                    $appendItem['shipping_address'] = array(
                        'id' => '',
                        'type' => 'shipping',
                        'first_name' => $is_firstname,
                        'last_name' => $is_lastname,
                        'postcode' => $order['s_zipcode'],
                        'address1' => $order['s_address'],
                        'address2' => $order['s_address_2'] != "" ? $order['s_address_2'] : '',
                        'phone' => $order['s_phone'],
                        'city' => $order['s_city'],
                        'country' => $order['s_country'],
                        'state' => isset($order['s_state']) ? $order['s_state'] : '',
                        'company' => $is_company,
						'company_name' => isset($order['company']) ? $order['company'] : '',
                        'gender' => isset($order['gender']) ? $order['gender'] : '',
                    );
                    
                    if(isset($order['need_shipping']) && $order['need_shipping']){
                        $appendItem['shipping_method'] = $this->searchData($order["shipping"],'shipping');
                    }
                    if(isset($order['payment_method']) && !empty($order['payment_method'])){
                        $appendItem['payment_method'] = $this->searchData($order["payment_method"],'payment');
                    }
                    
                    
                    if(isset($order['products']) && !empty($order['products'])){
                        $appendItem['order_products'] = array();
                        foreach($order['products'] as $item){
                            
                            $sModel = '';
                            $sModel = $item['product_code'];
                            
                            $appendItem['order_products'][] = array(
                                'product_id' => $item['product_id'],
                                'order_product_id' => $item['product_id'],
                                'model' => $sModel,
                                'name' => $item['product'],
                                'price' => $item['price'],
                                'discount_amount' => isset($item['discount']) ? $item['discount'] : 0,
                                'quantity' => $item['amount'],
                                'total_price' => $item['display_subtotal'],
                                'total_price_incl_tax' => 0,
                                'tax_percent' => isset($item['tax_percent']) ? $item['tax_percent'] : 0,
                                'tax_value' => isset($item['tax_value']) ? $item['tax_value'] : 0,
                                'variant_id' => ''
                            );
                        }
                    }
                    if(isset($order['notes']) && !empty($order['notes'])){
                        $appendItem['comment'] = $order['notes'];
                    }
                    
                    // Load project specific data
                    $projectModel = 'Project'.$projectId.'_model';
                    if(file_exists(APPPATH."models/".$projectModel.".php")){
                        $this->load->model($projectModel);
                        if(method_exists($this->$projectModel, 'loadCustomOrderAttributes')){
                            $appendItem = $this->$projectModel->loadCustomOrderAttributes($appendItem, $order, $projectId);
                        }
                    }
                    
                    if($appendItem != false){
                        $finalOrders[] = $appendItem;
                    }
                }
            }
			//log_message('debug', 'ORDER-AFTER ' . $projectId . var_export($finalOrders, true));
            // $this->dd($finalOrders);exit;
            return $finalOrders;
        }
        return false;
    }

    private function loggError ($projectId, $error) {
        log_message('error', 'Curl error for project '.$projectId.': ' . $error);
    }

    private function searchData($data, $key) {
        $result =  array_column($data, $key);
        if (!empty($result)) {
            return $result[0];
        }

        return " ";
    }

    private function GUID(){
        if (function_exists('com_create_guid') === true)
        {
            return trim(com_create_guid(), '{}');
        }

        return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535),
            mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
        //return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), 
    }

    public function getArticles($projectId, $offset = 0, $amount = 10) {
        $amount = 10;
        $page = ($offset == 0) ? 1 : ($offset / $amount);
        $ch = $this->requestInit($projectId, 'GET', "api/2.0/products?page=" .$page. "&items_per_page=" . $amount);
        $result = curl_exec($ch);
        $result = json_decode($result, true);

        if (isset($result['products']) && count($result['products'])) {
            return $result['products'];
        }

        return false;
    }

    private function dd($data) {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }
}