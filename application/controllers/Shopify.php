<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Shopify extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
        $this->load->helper('tools');
        $this->load->helper('constants');
	}

	public function index(){
		if(isset($_GET['project_id']) && $_GET['project_id'] > 0){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $projectId = intval($_GET['project_id']);

            $this->Exactonline_model->setData(
                array(
                    'projectId'    => $projectId,
                    'redirectUrl'  => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                    'clientId'     => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                    'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                )
            );
            $this->session->set_userdata('projectId',$projectId);
            $connection = $this->Exactonline_model->makeConnection($projectId);
            return $connection;
        }
	}

	###########################################################################################
    #   function get called by cron job schedule from directadmin to execute shopify conn..   #
    ###########################################################################################
    public function shopifyCronJob(){
        $this->load->model('Projects_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3])->get()->result_array();
        $this->updateArticleInShopify();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='shopify')
                    continue;
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                // check if the last execution time is satisfy the time checking.
                if($enabled == '1'){
                    if($p_value['erp_system'] == 'exactonline') {
                        $this->importArticleFromExact($projectId);
                        $this->importCustomerFromExact($projectId);
                    }
                }
            }
        } 
    }
  
    ###########################################################################################  
    # function is used to import or update article and article group in shopify from Exact. #
    ##########################################################################################
    public function importArticleFromExact($projectId=''){
        //$projectId = 37;
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Shopify_exact_model');
            $this->load->model('Shopify_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){

                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='shopify' || $p_value['erp_system']!='exactonline')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                    $enabled_con        = $this->Projects_model->getValue('enabled', $projectId);
                    $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 
                    $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:''; 
                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled_con == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        $time_u = $lastExecution + ($customersInterval * 60);
                        //reset last execution time
                        $this->Projects_model->saveValue('articles_last_execution', $time_u, $projectId);
                        // get the offset and amount to import customers. 
                        $offset                 =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : NULL;
                        $amount                 = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
                        //--------------- make exact connection ----------------------------------//
                        $this->Exactonline_model->setData(
                            array(
                                'projectId'     => $projectId,
                                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                            )
                        );
                        $connection = $this->Exactonline_model->makeConnection($projectId);
                        if($connection):
                            $publised_scope = $this->Projects_model->getValue('shopify_published_scope', $projectId)?$this->Projects_model->getValue('shopify_published_scope', $projectId):'web';
                            $import_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
                            $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
                            $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
                            $inventory_management =  $this->Projects_model->getValue('shopify_stock_management', $projectId) ? $this->Projects_model->getValue('shopify_stock_management', $projectId) : '';

                            $import_option_array = [
                                'import_exact_description'=>$import_exact_description, 
                                'import_exact_extra_description'=>$import_exact_extra_description, 
                                'publised_scope'=>$publised_scope, 
                                'inventory_management'=>$inventory_management,
                                ];
                            // get article from exactonline based on amount and offset  //
                            $items = $this->Shopify_exact_model->getExactArticle($connection, $itemId, $offset, $amount, true, $itemCode, $import_exact_image, $import_option_array);
                            //call Woocommerce_model to create and update article in WooCommerce//
                            if(!empty($items))
                                $items = $this->Shopify_model->importArticleInShopify($items, $projectId, null, $connection);
                        endif;
                    }
                }
            }
        }
    }

    ##########################################################################################
    #           function is used to update articles stocks in Shopify from ExactOnline           #
    ##########################################################################################
    public function updateArticleInShopify(){
        $this->load->model('Projects_model');
        $projectId           = isset($_GET['projectId'])?$_GET['projectId']:'';
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3]);
        if($projectId!='')
            $projects = $projects->where('id',$projectId);
        $projects = $projects->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId              = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)=='shopify'){
                    $lastExecution      = $this->Projects_model->getValue('article_update_execution', $projectId);
                    $stocksInterval     = $this->Projects_model->getValue('article_update_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                    $stock_enabled      = $this->Projects_model->getValue('articles_update_enabled', $projectId);
                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 
                    $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:'';
                    
                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled == '1' && $stock_enabled =='1' && ($lastExecution == '' || ($lastExecution + ($stocksInterval * 60) <= time()))){
                        //reset last execution time
                        $time_u = $lastExecution + ($stocksInterval * 60);
                        $this->Projects_model->saveValue('article_update_execution', $time_u, $projectId);
                        if($p_value['connection_type']==1){
                            if($p_value['erp_system'] == 'exactonline'){
                                $this->updateArticleFromExact($projectId,$itemId,$itemCode);
                            }
                        }
                    }
                }
            }
        }
    }

    ############################################################################################
    #       function is used to update articles stocks  in Shopify from erp system exact.      #
    ############################################################################################
    public function updateArticleFromExact($projectId = '',$itemId='',$itemCode=''){
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Shopify_exact_model');
            $this->load->model('Shopify_model');      
            $publised_scope = $this->Projects_model->getValue('shopify_published_scope', $projectId)?$this->Projects_model->getValue('shopify_published_scope', $projectId):'web';
            $import_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
            $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
            $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
            
            $lastUpdateDate = $this->Projects_model->getValue('exact_article_last_update_date', $projectId)?$this->Projects_model->getValue('exact_article_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate  = str_replace('+00:00', '.000Z', gmdate('c', strtotime($lastUpdateDate)));
            $currentdatetime = str_replace('+00:00', '.000Z', gmdate('c', strtotime(date("Y-m-d H:i:00"))));
            $inventory_management =  $this->Projects_model->getValue('shopify_stock_management', $projectId) ? $this->Projects_model->getValue('shopify_stock_management', $projectId) : '';
             
            $import_option_array = [
                'lastUpdateDate'=>$lastUpdateDate,
                'publised_scope'=>$publised_scope,
                'import_exact_image'=>$import_exact_image, 
                'import_exact_description'=>$import_exact_description, 
                'import_exact_extra_description'=>$import_exact_extra_description,
                'inventory_management' => $inventory_management,
                 
            ];

            // get the offset and amount to update stocks. 
            $offset =  $this->Projects_model->getValue('article_update_offset', $projectId) ? $this->Projects_model->getValue('article_update_offset', $projectId) : '';
            $amount = $this->Projects_model->getValue('article_update_amount', $projectId) ? $this->Projects_model->getValue('article_update_amount', $projectId) : 10;
            $this->Exactonline_model->setData(
                    array(
                        'projectId'     => $projectId,
                        'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                        'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                        'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                    )
                );
            $connection = $this->Exactonline_model->makeConnection($projectId);
            $items = $this->Shopify_exact_model->getExactArticleStocks($connection, $offset, $amount, $itemCode, $import_option_array);
            if(!empty($items)){
                $this->Shopify_model->importArticleInShopify($items, $projectId, 'stock_update', $connection);
                if(count($items)<$amount){
                    $this->Projects_model->saveValue('article_update_offset', null, $projectId);
                    $this->Projects_model->saveValue('exact_article_last_update_date', $currentdatetime, $projectId);
                }
            }
            else{
                $this->Projects_model->saveValue('exact_article_last_update_date', $currentdatetime, $projectId);
                $this->Projects_model->saveValue('article_update_offset', null, $projectId);
            }
        }
    }

    ####################################################################################
    #  function get called by shopify webhook whenever an order get created or updated #
    ####################################################################################
    public function webhookOrders(){
        $this->load->model('Projects_model');
        $projectId  = $_REQUEST['q'] ;
        $projectId  = explode('^^', base64_decode($projectId))[1];
        $headers    = $this->input->request_headers();
        $data       = json_decode(file_get_contents('php://input'),true);
    
        if(!$data){
            if(isset($_REQUEST['webhook_id']) && $_REQUEST['webhook_id']!='')
                $this->Projects_model->saveValue('webhookid_order_update',$_REQUEST['webhook_id'] , $projectId);
        } else{
            $shopify_enable = $this->Projects_model->getValue('enabled', $projectId)?$this->Projects_model->getValue('enabled', $projectId):'';
            
            if($shopify_enable=='1'){
                
                $projects = $this->db->get_where('projects', array('id' => $projectId ))->result_array();
                if($projects[0]['erp_system']=='exactonline'){
                    $this->load->helper('ExactOnline/vendor/autoload');
                    $this->load->model('Exactonline_model');
                    $this->load->model('Shopify_exact_model');
                    //--------------- make exact connection ----------------------------------//
                    $this->Exactonline_model->setData(
                        array(
                            'projectId'     => $projectId,
                            'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                            'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                            'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                        )
                    );
                    $connection = $this->Exactonline_model->makeConnection($projectId);
                    $sendOrder  = $this->Shopify_exact_model->sendOrder($connection, $projectId, $data);
                    $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
                    $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
                    if($sendOrder['status']==0){
                        $totalOrderImportError++;
                    } else{
                        $totalOrderImportSuccess++;
                    }
                    $this->Projects_model->saveValue('total_orders_import_success', $totalOrderImportSuccess, $projectId);
                    $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
                    project_error_log($projectId, 'exportorders',$sendOrder['message']);
                } 
            }
        }
    }


    ####################################################################################
    #     function is used to import or update customer in Shopify from ExactOnline.   #
    ####################################################################################
    public function importCustomerFromExact($projectId=''){
        
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Shopify_exact_model');
            $this->load->model('Shopify_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='shopify' || $p_value['erp_system']!='exactonline')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('customers_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('customers_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('customers_enabled', $projectId);
                    $enabled_con        = $this->Projects_model->getValue('enabled', $projectId);

                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled_con == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        $time_u = $lastExecution + ($customersInterval * 60);                      
                        //reset last execution time
                        $this->Projects_model->saveValue('customers_last_execution', $time_u, $projectId);
                        // get the offset and amount to import customers. 
                        $offset =  $this->Projects_model->getValue('exactonline_customers_offset', $projectId) ? $this->Projects_model->getValue('exactonline_customers_offset', $projectId) : NULL;
                        $amount = $this->Projects_model->getValue('customers_amount', $projectId) ? $this->Projects_model->getValue('customers_amount', $projectId) : 10;
                        $customerId             = isset($_GET['customerId'])?$_GET['customerId']:'';
                        //------------- make exact connection  --------------------//
                        $this->Exactonline_model->setData(
                            array(
                                'projectId'     => $projectId,
                                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                            )
                        );
                        $connection = $this->Exactonline_model->makeConnection($projectId);
                        // -----get customer from exactonline based on amount and offset ----//
                        $customers = $this->Shopify_exact_model->getExactCustomers($connection, $customerId, $offset, $amount);
                        //---call shopify_model to create and update customer in Shopify ---//
                        if($customers['counter']>0){
                            $items = $this->Shopify_model->importCustomersInShopify($customers['data'], $projectId);
                        }

                        if($customers['exact_last_cus']===1){
                          $this->Projects_model->saveValue('exactonline_customers_offset', null, $projectId);
                        }
                    }
                }
            }
        }
    }




    ####################################################################################
    #        All function is used for Testing Purpose in Shopify and ExactOnline.      #
    ####################################################################################

    /* WebHook Order Test*/
    public function webhookorderTest(){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        $this->load->model('Shopify_exact_model');
        $this->load->library('Shopify_restapi');

       /* $config = array(
            'ShopUrl'  => 'apicenter-test-2.myshopify.com',
            'ApiKey'   => '9ba09555deb3a02fba7208d2cd42ca84',
            'Password' => '037c38bad76b335497d754c409f43daf',
        );*/

        //Client ShopiFy Setting
        $config = array(
            'ShopUrl'  => 'https://exactconn.myshopify.com/',
            'ApiKey'   => '7beb4f06ebfb668b5399f8775b88dcd6',
            'Password' => '3ef3d1b719cdbb10627133d3af34b40c',
        );
        

        //Our Testing ShopiFy Setting
        /*$config = array(
            'ShopUrl'  => 'https://apicenter-test-3.myshopify.com',
            'ApiKey'   => '8686082b5262e11898e22cbb25444fae',
            'Password' => 'd22bb562a0ba2226952db2f2fb4b45de',
        );*/


       /* $config = array(
            'ShopUrl'  => 'https://apicenter-test-4.myshopify.com',
            'ApiKey'   => '9a78f517e2891ecbb279a12e1ba5df38',
            'Password' => '7e87f630f772ca4032571c1098cce187',
        );*/

        // $config = array(
        //     'ShopUrl'  => 'https://lakecycledemo.myshopify.com/',
        //     'ApiKey'   => '92aba655ba402354cce41ca9d8362436',
        //     'Password' => '91f7057b4733c5e2164f9b585ad79e55',
        // );
        
        //$ordersData = $this->shopify_restapi->getOrderById($config,'613396512866');
        $ordersData = $this->shopify_restapi->getOrderById($config,'613396512866');
        // $orders     = $this->shopify_restapi->testGetOrders($config);
        // $customers  = $this->shopify_restapi->testGetCustomers($config);
        // echo "<pre>";
        // print_r($ordersData);
        //print_r($customers);
        if ($ordersData['status']==1) {
            $orderData = $ordersData['data'];
        } else{
            die();
        }
        // echo "<pre>";
        // print_r($ordersData);
        // die();
        $projectId = 37;
        $this->Exactonline_model->setData(
            array(
                'projectId'     => $projectId,
                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );

        $connection = $this->Exactonline_model->makeConnection($projectId);
        if ($connection) {
            if($orderData['financial_status'] == 'paid'){
                $sendOrder = $this->Shopify_exact_model->sendOrder($connection, $projectId, $orderData);
                print_r($sendOrder);
                $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
                $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
                if($sendOrder['status']==0){
                    $totalOrderImportError++;
                } else{
                    $totalOrderImportSuccess++;
                }
                $this->Projects_model->saveValue('total_orders_import_success', $totalOrderImportSuccess, $projectId);
                $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
                project_error_log($projectId, 'exportorders',$sendOrder['message']);
                echo "<pre>";
                print_r($sendOrder);
                echo "string";
                die();
            }
        }
    }

    /* Manusally Impoert Product Test*/
   /* public function importProductTest(){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        $this->load->model('Shopify_exact_model');
        $this->load->library('Shopify_restapi');
        
        $config = array(
            'ShopUrl'  => 'https://apicenter-test-3.myshopify.com',
            'ApiKey'   => '8686082b5262e11898e22cbb25444fae',
            'Password' => 'd22bb562a0ba2226952db2f2fb4b45de',
        );

        $variants = [
                        [
                            'title'               => 'Burton Custom Freestyle 151',
                            'price'               => '85',
                            'sku'                 => 'NewSku123',
                            'fulfillment_service' => 'manual',
                            'barcode'             => '123456',
                            'inventory_quantity'  => '5',
                        ]
                    ];
         
        $item_details = array(
            'title'        => 'Testing New Article',
            'body_html'    => '<strong>Good New Article!</strong>',
            'vendor'       => 'apicenter-test-3',
            'product_type' => 'Snowboard',
            'tags'         => 'Hello',
            'FreeTextField_07' => '1578698604657',
            'variants'     => $variants
        );
    }
    */
	
	public function indextest(){
        /*$this->load->library('Shopify_restapi');
        $config = array(
            'ShopUrl'  => 'https://exactconn.myshopify.com/',
            'ApiKey'   => '7beb4f06ebfb668b5399f8775b88dcd6',
            'Password' => '3ef3d1b719cdbb10627133d3af34b40c',
        );*/

                           /* [first_name] => DeTestRubriek
                            [last_name] =>  
                            [email] => leon@web-company.nl
                            [phone] => 0183201079
                            [verified_email] => 1
                            [addresses] => Array
                                (
                                    [addresses] => Array
                                        (
                                            [address1] => Pelmolenpad 4
                                            [city] => Breda
                                            [province] => NB
                                            [phone] => 0183201079
                                            [zip] => 4811
                                            [last_name] =>  
                                            [first_name] => DeTestRubriek
                                            [country] => NL
                                        )

                                )*/
        /*$addresses  = array(
                        "address1"   => 'Pelmolenpad 4',
                        "city"       => 'Breda',
                        "province"   => 'NB',
                        "phone"      => '+919183201079',
                        "zip"        => '4811',
                        "last_name"  => '',
                        "first_name" => 'DeTestRubriek',
                        "country"    => 'NL',
                    );
        $customerData = array(
            'first_name'    => 'DeTestRubriek',
            'last_name'     => '',
            'email'         => 'leon@web-company.nl',
            'phone'         => '+919183201079',
            'verified_email'=> true,
            'addresses'     => array
                                (
                                    'addresses' =>  $addresses,
                                ),
        );*/
        
        //$this->load->model('Shopify_model');


       /* $variants = [
                        [
                            'title'               => 'Burton Custom Freestyle 151',
                            'price'               => '85',
                            'sku'                 => 'NewSku123',
                            'fulfillment_service' => 'manual',
                            'barcode'             => '123456',
                            'inventory_quantity'  => '5',
                        ]
                    ];*/
         
        /* $item_details = array(
            'title'        => 'Burton Custom Freestyle 151',
            'body_html'    => '<strong>Good snowboard!</strong>',
            'vendor'       => 'apicenter-test-2',
            'product_type' => 'Snowboard',
            'tags'         => 'Barnes',
            'FreeTextField_07' => '1578698604657',
            'variants'     => $variants
        );*/
       
        //$customer = $this->shopify_restapi->postCreateCustomer($config, $customerData);
        /*$projectId = 37;
        $products = $this->Shopify_model->updateShopifyArticleTest($item_details, $config, $projectId);*/
        //$products = $this->shopify_restapi->getProductById($config, '1578698604657');
        
        /*echo "<pre>";
        print_r($customer);exit(); */

        // $products = $this->shopify_restapi->getProductById($config, '1530657636421');
        // print_r($products);
      
          //       $params = array(
        		//     'title' => 'ft',
        		//     'vendor' => 'apicenter-test-2',
        		//     'fields' => 'id,title,vendor,body_html,product_type'
        		// );
          //      $products = $this->shopify_restapi->getProductByField($config, $params);
          //      print_r($products);

        //  $params = array(
	    //    'title'        => 'Burton Custom Freestyle 151',
		//     'body_html'    => '<strong>Good snowboard!</strong>',
		//     'vendor'       => 'apicenter-test-2',
		//     'product_type' => 'Snowboard',
		//     'tags'         => 'Barnes '
		// );
        //  $product = $this->shopify_restapi->postNewProduct($config, $params);

        /*$params = array(
            "topic"=> "orders/updated",
            "address"=> "https://apicenterdev.web-company.nl/index.php/shopify/testweb",
            "format"=> "json"
        );*/
        /*$this->load->model('Shopify_model');  
        $projectId = 37;    
        $webhook = $this->Shopify_model->putPostShopifyWebhook($projectId);*/
	}



    //*******************  Import Customer Test From ExactOnline *****************//
    public function getCustomerTest(){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        $this->load->model('Shopify_exact_model');
        $this->load->library('Shopify_restapi');
 
        $projectId = 38;
        $this->Exactonline_model->setData(
            array(
                'projectId'     => $projectId,
                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );

        $offset =  $this->Projects_model->getValue('exactonline_customers_offset', $projectId) ? $this->Projects_model->getValue('exactonline_customers_offset', $projectId) : NULL;
        $amount = $this->Projects_model->getValue('customers_amount', $projectId) ? $this->Projects_model->getValue('customers_amount', $projectId) : 1000;
        $customerId             = isset($_GET['customerId'])?$_GET['customerId']:'';

        $connection = $this->Exactonline_model->makeConnection($projectId);
        /*echo "<pre>";
        print_r($connection);
        exit();*/
        if ($connection) {
            $customers = $this->Shopify_exact_model->getExactCustomersTest($connection, $customerId, NULL, $amount);
            echo "<pre>";
            print_r($customers);
            exit();
        }
    }




}

/* End of file Shopify.php */
/* Location: ./application/controllers/Shopify.php */