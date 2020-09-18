<?php
class Shopify_model extends CI_Model {

    function __construct(){
        parent::__construct();

    }



    ####################################################################################
    #    Function is used to create webhook for the particular project create if not.  #
    ####################################################################################
    /*
    public function putShopifyWebhook($projectId){
        $this->load->library('Shopify_restapi');
    	$this->load->model('Projects_model');
    	$config      = $this->shopifyConnectionParams($projectId);
        $address_url = base_url()."index.php/shopify/webhookOrders?q=".base64_encode('updated^^'.$projectId);
        $params = array(
            "topic"   => "orders/updated",
            "address" => $address_url,
            "format"  => "json"
        );
        $webhook = $this->shopify_restapi->createWebhooks($config , $params);
    }
	*/

    #####################################################################################
    # Function check is webhook already created for the particular project create if not#
    #####################################################################################
    /*
    public function putPostShopifyWebhook($projectId){
        $this->load->library('Shopify_restapi');
        $config      = $this->shopifyConnectionParams($projectId);
        $address_url = base_url()."index.php/shopify/webhookOrders?q=".base64_encode('updated^^'.$projectId);
        $webhook     = $this->shopify_restapi->listWebhooks($config);
        $test_new    = 1;
        if ($webhook['status']==1) {
            foreach ($webhook['data'] as $dataValue) {
                if ($dataValue['address']==$address_url) {
                    $test_new =0;
                }
            }
        }
        if($test_new){
            $createWebhook = $this->putShopifyWebhook($projectId);
        }
    }
	*/

    #####################################################################################
    #    Function is used for delete existing all webhook for the particuler project    #
    #####################################################################################
    /*
    public function deleteShopifyWebhook($projectId){
        $this->load->library('Shopify_restapi');
        $config      = $this->shopifyConnectionParams($projectId);
        $webhook     = $this->shopify_restapi->listWebhooks($config);

        foreach ($webhook['data'] as $dataValue) {
            $webhookId = $dataValue['id'];
            $deleteWebhook = $this->shopify_restapi->deleteWebhook($config,$webhookId);
        }
    }
	*/
    ########################################################################################
    #     Function is used to set all required params to make shopify api connection.      #
    ########################################################################################
    public function shopifyConnectionParams($projectId){

        $this->load->model('Projects_model');
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $ShopUrl    = '';
        if(!empty($project)){
            $store_url  = $project['store_url'];
        }

        $shopify_api_key       = $this->Projects_model->getValue('shopify_api_key', $projectId)?$this->Projects_model->getValue('shopify_api_key', $projectId):'';
        $shopify_password       = $this->Projects_model->getValue('shopify_password', $projectId)?$this->Projects_model->getValue('shopify_password', $projectId):'';

        $params = array(
            'ShopUrl'  => $store_url,
            'ApiKey'   => $shopify_api_key,
            'Password' => $shopify_password,
        );

        return $params;
    }


    ########################################################################################
    # Used to import products to shopify from exactonline by calling createShopifyArticle  #
    ########################################################################################
    public function importArticleInShopify($items, $projectId, $method ='post', $connection){
        //echo "dddddddd".$method;die();
        $this->load->model('Projects_model');
        $this->load->model('Shopify_exact_model');
        $this->load->library('Shopify_restapi');
        $config          = $this->shopifyConnectionParams($projectId);
        $offset          = '';
        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;

        foreach ($items as $i_key => $i_value) {

            if($i_value['FreeTextField_07']!=''){
                $checkArticle = $this->checkShopifyArticle($config,$i_value['FreeTextField_07']);
                if ($checkArticle['status']==1) {
                    $createArticle = $this->updateShopifyArticle($i_value,$config, $projectId);
                }else{
                    $createArticle = $this->createShopifyArticle($i_value,$config, $projectId);
                    
                    if ($createArticle['status']==1) {
                        $itemId = $createArticle['data']['id'];
                        $updateItems = $this->Shopify_exact_model->updateArticleInExact($projectId,$i_value['exactItem'],$connection,$itemId);
                        if ($updateItems['status']==0) {
                            $message = " Failed to update article: ".$i_value['variants'][0]['sku'];
                            project_error_log($projectId, 'importarticles'," Error :: ".$message." , resource_id- ".$i_value['exactItem']['ID']);
                        }
                    }
                }
            }else{
                $createArticle = $this->createShopifyArticle($i_value, $config, $projectId);
                if ($createArticle['status']==1) {
                    $itemId = $createArticle['data']['id'];
                    $updateItems = $this->Shopify_exact_model->updateArticleInExact($projectId,$i_value['exactItem'],$connection,$itemId);
                    if ($updateItems['status']==0) {
                        $message = " Failed to update article: ".$i_value['variants'][0]['sku'];
                        project_error_log($projectId, 'importarticles'," Error ::".$message." , resource_id- ".$i_value['exactItem']['ID']);
                    }
                }
            }

            $offset    = $i_value['Id'];
            if(isset($createArticle['status']) && $createArticle['status']==1){

                $totalArticleImportSuccess++;
                if($createArticle['action']=='add'){
                    project_error_log($projectId, 'importarticles'," Success :: ".$i_value['variants'][0]['sku'] ." imported successfully , resource_id- ".$createArticle['data']['id']);
                }
                else{
                    project_error_log($projectId, 'importarticles'," Success :: ".$i_value['variants'][0]['sku'] ." updated successfully , resource_id- ".$createArticle['data']['id']);
                }
            } else{
                $totalArticleImportError++;
                $message = " Failed to import article: ".$i_value['variants'][0]['sku'];
                if(isset($createArticle['message']))
                    $message = $message.' '.$createArticle['message'];
                project_error_log($projectId, 'importarticles', " Error ::".$message);
            }
        }
                
        if ($method == 'post' || $method == ''){
            $this->Projects_model->saveValue('article_offset', $offset, $projectId);
        }else {
            $this->Projects_model->saveValue('article_update_offset', $offset, $projectId);
        }
        $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
    }


    #######################################################################################
    #                   Function is used to check  article  in shopify.                   #
    #######################################################################################
    public function checkShopifyArticle($config, $id){
        return $this->shopify_restapi->checkProductById($config, $id);
    }

    #######################################################################################
    #  Function is used to create or update  article  and article category in shopify.    #
    #######################################################################################
    public function createShopifyArticle($item_details, $config, $projectId){
        $this->load->model('Projects_model');
        $product_details = array();
        $published_scope = $this->Projects_model->getValue('shopify_published_scope', $projectId);
        $published_product =  $this->Projects_model->getValue('shopify_published_product', $projectId) ? $this->Projects_model->getValue('shopify_published_product', $projectId) : '0';
         
        $product_details['title']           = $item_details['title'];
        if (isset($item_details['body_html'])) {
            $product_details['body_html']       = $item_details['body_html'];
        }
        $product_details['product_type']    = $item_details['product_type'];
        if ($published_product==0) {
           $product_details['published_at'] = null;
        }
        $product_details['published_scope'] = $published_scope;
        $product_details['variants']        = $item_details['variants'];
        if (isset($item_details['images'])) {
           $product_details['images']    = $item_details['images'];
        }else{
            $product_details['images']    = array();
        }
        return $this->shopify_restapi->postNewProduct($config, $product_details);
    }


    #######################################################################################
    #                   Function is used to update  article in shopify.                   #
    #######################################################################################
    function updateShopifyArticle($item_details, $config, $projectId){
        $this->load->model('Projects_model');
        $product_details = array();
        $published_scope = $this->Projects_model->getValue('shopify_published_scope', $projectId);
        $published_product =  $this->Projects_model->getValue('shopify_published_product', $projectId) ? $this->Projects_model->getValue('shopify_published_product', $projectId) : '0';

        $product_details['title']           = $item_details['title'];
        if (isset($item_details['body_html'])) {
            $product_details['body_html']       = $item_details['body_html'];
        }        
        $product_details['product_type']    = $item_details['product_type'];
        if ($published_product==0) {
           $product_details['published_at'] = null;
        }
        $product_details['published_scope'] = $published_scope;
        $product_details['variants']        = $item_details['variants'];
        if (isset($item_details['images'])) {
           $product_details['images']    = $item_details['images'];
        }else{
            $product_details['images']    = array();
        }
        return $this->shopify_restapi->updateExistProduct($config, $item_details['FreeTextField_07'], $product_details);
    }


    #######################################################################################
    #                 Function is used to create   customer in Shopify.                   #
    #######################################################################################
    public function importCustomersInShopify($customers, $projectId, $offset_key = ''){
        $this->load->model('Projects_model');
        $this->load->library('Shopify_restapi');
        $config             = $this->shopifyConnectionParams($projectId);
        $offset             = '';

        $totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
        $totalCustomerImportError = $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;
        $already_exist      = array();

        
        foreach ($customers as $c_key => $c_value) {
            
            $createCustomer = $this->shopify_restapi->postCreateCustomer($config,$c_value['customerDetails']);
                 
            $offset    = $c_value['id'];
            if($createCustomer['status']==1){
                $totalCustomerImportSuccess++;
                if($createCustomer['action']=='add')
                    project_error_log($projectId, 'importcustomers'," Success :: ".$c_value['customerDetails']['email']." imported successfully , resource_id -> ".$createCustomer['data']['id']);
            } else{
                if(isset($createCustomer['message']) && ($createCustomer['message'] == 'Error! email - has already been taken' || $createCustomer['message'] == 'Error! phone - has already been taken' )){
                    $already_exist[] = $c_value;
                    continue;
                }
                $totalCustomerImportError++;
                if(isset($createCustomer['message'])){
                    $message  =  "Error :: Failed to import customer email - ".$c_value['customerDetails']['email']." Error -> ".$createCustomer['message'].' - Phone number ->'.$c_value['customerDetails']['phone'];
                }
                project_error_log($projectId, 'importcustomers',$message);
            }
            sleep(1);        // sleep for 1 sec as one api call in 1 sec
        }


        $this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
        if($offset !='' && $offset_key != '')
            $this->Projects_model->saveValue($offset_key, $offset, $projectId);
        else if($offset!='')
            $this->Projects_model->saveValue('exactonline_customers_offset', $offset, $projectId);
        if(!empty($already_exist)){
            $this->updateCustomersInShopify($already_exist, $projectId);
        }
    }
    
    ######################################################################################
    #             Function is used to   update  customer in Shopify.                     #
    ######################################################################################
    public function updateCustomersInShopify($customers, $projectId){
        $this->load->model('Projects_model');
        $config             = $this->shopifyConnectionParams($projectId);
        $totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
        $totalCustomerImportError = $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0; 

        foreach ($customers as $c_key => $c_value) {
            $email = $c_value['customerDetails']['email'];
            $data  = 'email:'.$c_value['customerDetails']['email'];
            $getCustomer  = $this->shopify_restapi->getCustomer($config,$data);
            

            sleep(1);        // sleep for 1 sec as one api call in 1 sec
            $customer_id  = '';
            if($getCustomer['status']==1){
                $customer_id  = isset($getCustomer['data'][0]['id'])?$getCustomer['data'][0]['id']:'';
            }
            
            if($customer_id!=''){
                unset($c_value['customerDetails']['email']);
                $updateCustomer = $this->shopify_restapi->putUpdateCustomer($customer_id, $c_value['customerDetails'], $config);

                if ($updateCustomer['status']==1) {
                    $totalCustomerImportSuccess++;
                    project_error_log($projectId, 'importcustomers'," Success :: ".$email." Updated successfully , resource_id -> ".$updateCustomer['data']['id']);
                }else{
                    $totalCustomerImportError++;
                     
                    if(isset($updateCustomer['message']))
                         $message =  "Error :: Failed to Update customer - ".$email." Error -> ".$updateCustomer['message'];

                    project_error_log($projectId, 'importcustomers',$message);
                }
                sleep(1);        // sleep for 1 sec as one api call in 1 sec
            }
        }
      
        $this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
    }


    ######################################################################################
    #             Function is used to get orders from Shopify.                           #
    ######################################################################################
    public function getOrders($projectId, $filters = array()){
        $this->load->model('Projects_model');
        $this->load->library('Shopify_restapi');
        $config = $this->shopifyConnectionParams($projectId);
        $orders = $this->shopify_restapi->getOrders($config, $filters);
		$finalOrders = array();
		if(!empty($orders)){
			foreach($orders as $order){
				$discount = 0;
				if(isset($order['discount_codes']) && !empty($order['discount_codes'])){
					foreach($order['discount_codes'] as $code){
						$discount += $code['amount'];
					}
				}
				$appendItem = array(
					'id' => $order['name'],
					'order_id' => $order['id'],
					'state' => $order['financial_status'],
					'customer' => array(
						'id' => isset($order['customer']['id']) ? $order['customer']['id'] : '',
						'email' => $order['customer']['email'],
						'first_name' => isset($order['customer']['first_name']) ? $order['customer']['first_name'] : '',
						'last_name' => isset($order['customer']['last_name']) ? $order['customer']['last_name'] : ''
					),
					'create_at' => $order['created_at'],
					'modified_at' => $order['updated_at'],
					'currency' => $order['currency'],
					'totals' => array(
						'total' => $order['total_price'],
						'subtotal' => $order['subtotal_price'],
						'shipping' => isset($order['shipping_lines'][0]) ? $order['shipping_lines'][0]['price'] : 0,
						'tax' => $order['total_tax'],
						'discount' => $discount,
					)
				);
	
				$appendItem['billing_address'] = array(
					'type' => 'billing',
					'first_name' => $order['billing_address']['first_name'],
					'last_name' => $order['billing_address']['last_name'],
					'postcode' => $order['billing_address']['zip'],
					'address1' => $order['billing_address']['address1'],
					'address2' => $order['billing_address']['address2'],
					'phone' => $order['billing_address']['phone'],
					'city' => $order['billing_address']['city'],
					'country' => $order['billing_address']['country_code'],
					'state' => isset($order['billing_address']['province']) ? $order['billing_address']['province'] : '',
					'company' => isset($order['billing_address']['company']) ? $order['billing_address']['company'] : '',
				);
				if($order['billing_address']['address2'] != ''){
					$appendItem['billing_address']['address1'] .= ' '.$order['billing_address']['address2'];
					$appendItem['billing_address']['address2'] = '';
				}
	
				$appendItem['shipping_address'] = array(
					'type' => 'shipping',
					'first_name' => $order['shipping_address']['first_name'],
					'last_name' => $order['shipping_address']['last_name'],
					'postcode' => $order['shipping_address']['zip'],
					'address1' => $order['shipping_address']['address1'],
					'address2' => $order['billing_address']['address2'],
					'phone' => $order['shipping_address']['phone'],
					'city' => $order['shipping_address']['city'],
					'country' => $order['shipping_address']['country_code'],
					'state' => isset($order['shipping_address']['province']) ? $order['shipping_address']['province'] : '',
					'company' => isset($order['shipping_address']['company']) ? $order['shipping_address']['company'] : '',
				);
				if($order['shipping_address']['address2'] != ''){
					$appendItem['shipping_address']['address1'] .= ' '.$order['shipping_address']['address2'];
					$appendItem['shipping_address']['address2'] = '';
				}
	
				if(isset($order['shipping_lines'][0])){
					$appendItem['shipping_method'] = $order['shipping_lines'][0]['code'];
				}
				if(isset($order['payment_gateway_names'][0])){
					$appendItem['payment_method'] = $order['payment_gateway_names'][0];
				}
				if(isset($order['line_items']) && !empty($order['line_items'])){
					$appendItem['order_products'] = array();
					foreach($order['line_items'] as $item){
						$itemPrice = ($item['total_discount'] > 0) ? ($item['price'] - ($item['total_discount'] / $item['quantity'])) : $item['price'];
						$appendItem['order_products'][] = array(
							'product_id' => $item['product_id'],
							'order_product_id' => $item['product_id'],
							'model' => $item['sku'],
							'name' => $item['title'],
							'price' => $itemPrice,
							'discount_amount' => isset($item['total_discount']) ? ($item['total_discount'] - ($item['total_discount'] / (1 + $item['tax_lines'][0]['rate']))) : 0,
							'quantity' => $item['quantity'],
							'total_price' => ($itemPrice * $item['quantity']),
							'total_price_incl_tax' => ($itemPrice * $item['quantity']) + $item['tax_lines'][0]['price'],
							'tax_percent' => $item['tax_lines'][0]['rate'] * 100,
							'tax_value' => $item['tax_lines'][0]['price'],
							'variant_id' => $item['variant_id']
						);
					}
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
		return $finalOrders;
    }
   
}
