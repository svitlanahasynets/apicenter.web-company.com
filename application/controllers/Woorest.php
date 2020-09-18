<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Woorest extends CI_Controller {

    /**
    * @author Manish
    * @return NULL
    */
    public function __construct(){
        parent::__construct();
        $this->load->helper('tools');
        $this->load->helper('constants');
    }

    #########################################################################################################
    #                 function is used to get authorised for ExactOnline                                    #
    #########################################################################################################
    public function index(){
        if(isset($_GET['project_id']) && $_GET['project_id'] > 0){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $projectId = intval($_GET['project_id']);

            $this->Exactonline_model->setData(
                array(
                    'projectId' => $projectId,
                    'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                    'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                    'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                )
            );
            $this->session->set_userdata('projectId',$projectId);
            $connection = $this->Exactonline_model->makeConnection($projectId);
            return $connection;
        }
    }

    #######################################################################################
    #             function call back after authorised from exact and set token            #
    #######################################################################################
    public function importOrders(){
        $projectId = $this->session->userdata('projectId');
        if($projectId && $projectId > 0){
            $this->session->unset_userdata('projectId',false);
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Exactonline_model');
            $this->load->model('Projects_model');
            $this->Exactonline_model->setData(
                array(
                    'projectId' => $projectId,
                    'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                    'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                    'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                )
            );
            $connection = $this->Exactonline_model->makeConnection($projectId);
            if($connection){
              set_success_message('connection authorised successfully');
              redirect('/login/');
            }
        }
    }

    ######################################################################################
    # function get called by woocommerce webhook whenever an order get created or updated#
    ######################################################################################
    public function webhookOrders(){
        $this->load->model('Projects_model');
        $this->load->model('Woocommerce_model');
        $projectId  = $_REQUEST['q'] ;
        $projectId  = explode('^^', base64_decode($projectId))[1];
        $headers    = $this->input->request_headers();
        $data       = json_decode(file_get_contents('php://input'),true);
		//log_message('error', 'data received from woocommerce webhook');
		//log_message('error', var_export($data, true));
        if(!$data){
            if(isset($_REQUEST['webhook_id']) && $_REQUEST['webhook_id']!='')
                $this->Projects_model->saveValue('webhookid_order_update',$_REQUEST['webhook_id'] , $projectId);
        } else{
            $woocommerce_connection_status = $this->Projects_model->getValue('enabled', $projectId)?$this->Projects_model->getValue('enabled', $projectId):'';
            if($woocommerce_connection_status=='1'){
                $woocommerce_order_status = $this->Projects_model->getValue('woocommerce_order_status', $projectId)?$this->Projects_model->getValue('woocommerce_order_status', $projectId):'';
                $check_order_status = true;
                if($woocommerce_order_status!=''){
                    if($woocommerce_order_status!= $data['status'])
                        $check_order_status = false;
                }
                if($data['status'] == 'cancelled' || $data['status'] == 'failed'){
                    $check_order_status = false;
                }
                if($check_order_status){
                    $projects = $this->db->get_where('projects', array('id' => $projectId ))->result_array();
                    if($projects[0]['erp_system']=='exactonline'){
                        $this->load->helper('ExactOnline/vendor/autoload');
                        $this->load->model('Exactonline_model');
                        $this->load->model('Woocommerce_exactonline_model');
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
                        $import_option = $this->Projects_model->getValue('woocommerce_import_option', $projectId)?$this->Projects_model->getValue('woocommerce_import_option', $projectId):'orders';
                        if($import_option=='orders'){
                            $sendOrder  = $this->Woocommerce_exactonline_model->sendOrder($connection, $projectId, $data);
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
                        } else{
                            $sendInvoice  = $this->Woocommerce_exactonline_model->sendInvoice($connection, $projectId, $data);
                            $totalInvoiceImportSuccess = $this->Projects_model->getValue('total_invoice_import_success', $projectId)?$this->Projects_model->getValue('total_invoice_import_success', $projectId):0;
                            $totalInvoiceImportError = $this->Projects_model->getValue('total_invoice_import_error', $projectId)?$this->Projects_model->getValue('total_invoice_import_error', $projectId):0;
                            if($sendInvoice['status']==0){
                                $totalInvoiceImportError++;
                            } else{
                                $totalInvoiceImportSuccess++;
                            }
                            $this->Projects_model->saveValue('total_invoice_import_success', $totalInvoiceImportSuccess, $projectId);
                            $this->Projects_model->saveValue('total_invoice_import_error', $totalInvoiceImportError, $projectId);
                            project_error_log($projectId, 'importInvoices',$sendInvoice['message']);
                        }
                    } else if($projects[0]['erp_system']=='afas'){
                        $this->load->model('Woocommerce_afas_model');
                        $this->load->model('Afas_common_model');
                        $import_option = $this->Projects_model->getValue('woocommerce_import_option', $projectId)?$this->Projects_model->getValue('woocommerce_import_option', $projectId):'orders';
                        if($import_option == 'orders')
                            $this->Afas_common_model->sendOrder($projectId, $data);
                        else if($import_option == 'invoices')
                            $this->Afas_common_model->sendInvoice($projectId, $data);
                    }
                }
            }
        }
    }

    #########################################################################################
    #    function get called by cron job schedule from directadmin to execute woocommerce   #
    #########################################################################################
    /*
    public function woorestCronJob(){
        $this->load->model('Projects_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3])->get()->result_array();
        $this->updateArticleInWoocommerce();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($projectId == 85){
	                continue;
                }
                if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce')
                    continue;
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                // check if the last execution time is satisfy the time checking.
                if($enabled == '1'){
                    if($p_value['erp_system'] == 'exactonline') {
                        $this->importArticleFromExact($projectId);
                        $this->importCustomerFromExact($projectId);
                    } else if($p_value['erp_system'] == 'afas') {
                        // $this->importArticleFromAfas($projectId);
                        // $this->importCustomerFromAfas($projectId);
                    } 
                }
            }
        } 
    }
	*/
    #########################################################################################################
    #      function is used to import or update article and article group in WooCommerce from ExactOnline.  #
    #########################################################################################################
    public function importArticleFromExact($projectId=''){
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Woocommerce_exactonline_model');
            $this->load->model('Woocommerce_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce' || $p_value['erp_system']!='exactonline')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                    $enabled_con        = $this->Projects_model->getValue('enabled', $projectId);
                    $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 
                    $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:''; 
                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled_con == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $this->Projects_model->saveValue('articles_last_execution', time(), $projectId);
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
                        $is_published = $this->Projects_model->getValue('import_as_published', $projectId);
                        $impost_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
                        $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
                        $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
                        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
                        $import_option_array = ['import_exact_description'=>$import_exact_description, 'import_exact_extra_description'=>$import_exact_extra_description, 'woocommerce_stock_options'=>$woocommerce_stock_options];
                        // ------- get article from exactonline based on amount and offset ----------------       //
                        $items = $this->Woocommerce_exactonline_model->getExactArticle($connection, $itemId, $offset, $amount, $is_published,$itemCode, $impost_exact_image, $import_option_array, $projectId);
                        // ------ call Woocommerce_model to create and update article in WooCommerce ------       //
                        if(!empty($items))
                            $items = $this->Woocommerce_model->importArticleInWoocommerce($items, $projectId);
                    }
                }
            }
        }
    }

    #########################################################################################################
    #             function is used to import or update customer in WooCommerce from ExactOnline.            #
    #########################################################################################################
    public function importCustomerFromExact($projectId=''){
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Woocommerce_exactonline_model');
            $this->load->model('Woocommerce_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce' || $p_value['erp_system']!='exactonline')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('customers_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('customers_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('customers_enabled', $projectId);
                    $enabled_con        = $this->Projects_model->getValue('enabled', $projectId);

                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled_con == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $this->Projects_model->saveValue('customers_last_execution', time(), $projectId);
                        // get the offset and amount to import customers. 
                        $offset =  $this->Projects_model->getValue('exactonline_customers_offset', $projectId) ? $this->Projects_model->getValue('exactonline_customers_offset', $projectId) : NULL;
                        $amount = $this->Projects_model->getValue('customers_amount', $projectId) ? $this->Projects_model->getValue('customers_amount', $projectId) : 10;
                        $customerId             = isset($_GET['customerId'])?$_GET['customerId']:'';
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
                        // ------- get customer from exactonline based on amount and offset ----------------       //
                        $customers = $this->Woocommerce_exactonline_model->getExactCustomers($connection, $customerId, $offset, $amount);
                        // -----  call Woocommerce_model to create and update customer in WooCommerce -----------  //
                        if($customers['counter']>0){
                            $items = $this->Woocommerce_model->importCustomersInWoocommerce($customers['data'], $projectId);
                        }
                        if($customers['exact_last_cus']===1){
                          $this->Projects_model->saveValue('exactonline_customers_offset', null, $projectId);
                        }
                    }
                }
            }
        }
    }

    #########################################################################################################
    #      function is used to import or update article and article group in WooCommerce from ExactOnline.  #
    #########################################################################################################
    public function importArticleFromAfas($projectId=''){
        if($projectId!=''){
            $this->load->model('Woocommerce_afas_model');
            $this->load->model('Projects_model');
            $this->load->model('Woocommerce_model');
            $this->load->model('Afas_common_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    /*if($projectId!=30)
                        continue;*/
                    if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                    $articles_enabled   = $this->Projects_model->getValue('articles_enabled', $projectId);
                    $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:''; 
                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($articles_enabled == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $time_u = $lastExecution + ($customersInterval * 60);
                        $this->Projects_model->saveValue('articles_last_execution', $time_u, $projectId);
                        // get the offset and amount to import customers. 
                        $offset                 =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : NULL;
                        $amount                 = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
                        // ------- get article from afas based on amount and offset ----------------       //
                        $items = $this->Afas_common_model->getArticles($projectId, $itemCode, $offset, $amount, '', '');
                        // ------ call Woocommerce_model to create and update article in WooCommerce ------       //
                        if(!empty($items)){
                            $formated_items = $this->Woocommerce_afas_model->formatArticleFromAfasWoocommerce($projectId, $items);
                            $this->Woocommerce_model->importArticleInWoocommerce($formated_items, $projectId);
                        } 
                    }
                }
            }
        }
    }

    #########################################################################################################
    #             function is used to import or update customer in WooCommerce from Afas.                   #
    #########################################################################################################
    public function importCustomerFromAfas($projectId=''){
        if($projectId!=''){
            $this->load->model('Projects_model');
            $this->load->model('Afas_common_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('customers_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('customers_interval', $projectId);
                    $enabled_cus        = $this->Projects_model->getValue('customers_enabled', $projectId);
                    $enabled            = $this->Projects_model->getValue('enabled', $projectId);

                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled_cus == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $time_u = $lastExecution + ($customersInterval * 60);
                        $this->Projects_model->saveValue('customers_last_execution', $time_u, $projectId);
                        // get the offset and amount to import customers. 
                        $offset =  $this->Projects_model->getValue('customers_offset', $projectId) ? $this->Projects_model->getValue('customers_offset', $projectId) : 0;
                        $amount = $this->Projects_model->getValue('customers_amount', $projectId) ? $this->Projects_model->getValue('customers_amount', $projectId) : 10;
                        $customerId = isset($_GET['customerId'])?$_GET['customerId']:'';
                        // ------- get customer from Afas based on amount and offset ----------------       //
                        $customers = $this->Afas_common_model->getDebtors($projectId, $offset, $amount, $customerId);
                        // -----  call Woocommerce_model to create and update customer in WooCommerce -----------  //
                        if($customers==0){
                          $this->Projects_model->saveValue('customers_offset', null, $projectId);
                        }
                    }
                }
            } 
        }
    }

    #########################################################################################################
    #       function is used to import or update articles stocks  in WooCommerce from erp system .          #
    #########################################################################################################
    public function updateArticleInWoocommerce(){
        $this->load->model('Projects_model');
        $projectId           = isset($_GET['projectId'])?$_GET['projectId']:'';
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3]);
        if($projectId!='')
            $projects = $projects->where('id',$projectId);
        $projects = $projects->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId              = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)=='WooCommerce'){
                    $lastExecution      = $this->Projects_model->getValue('article_update_execution', $projectId);
                    $stocksInterval     = $this->Projects_model->getValue('article_update_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                    $stock_enabled      = $this->Projects_model->getValue('articles_update_enabled', $projectId);
                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 

                    // check if the last execution time is satisfy the time checking. customers_amount
                    if($enabled == '1' && $stock_enabled =='1' && ($lastExecution == '' || ($lastExecution + ($stocksInterval * 60) <= time()))){
                        //reset last execution time
                        $time_u = $lastExecution + ($stocksInterval * 60);
                        $this->Projects_model->saveValue('article_update_execution', $time_u, $projectId);
                        if($p_value['connection_type']==1){
                            if($p_value['erp_system'] == 'afas'){
                               // $this->updateArticleFromAfas($projectId, $itemId);
                            } else  if($p_value['erp_system'] == 'exactonline'){
                                $this->updateArticleFromExact($projectId, $itemId);
                            }
                        }
                    }
                }
            }
        }
    }

    #########################################################################################################
    #       function is used to import or update articles stocks  in WooCommerce from erp system afas.      #
    #########################################################################################################
    public function updateArticleFromAfas($projectId= '', $itemCode=''){
        if($projectId!=''){
            $this->load->model('Woocommerce_model');
            $this->load->model('Afas_common_model');
            $this->load->model('Woocommerce_afas_model');
            $this->load->model('Projects_model');
            // get the offset and amount to update stocks. 
            $lastUpdateDate = $this->Projects_model->getValue('article_last_update_date', $projectId)?$this->Projects_model->getValue('article_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate = date("Y-m-d\TH:i:00", strtotime($lastUpdateDate));
            $currentdatetime = date("Y-m-d\TH:i:00");
            $import_option_array = [ 'lastUpdateDate'=>$lastUpdateDate];
            // get the offset and amount to update aticle. 
            $offset =  $this->Projects_model->getValue('article_update_offset', $projectId) ? $this->Projects_model->getValue('article_update_offset', $projectId) : '';
            $amount = $this->Projects_model->getValue('article_update_amount', $projectId) ? $this->Projects_model->getValue('article_update_amount', $projectId) : 10;
            $products_stock_list = $this->Afas_common_model->getArticlesStock($projectId, $itemCode, $offset, $amount, '', $import_option_array);
            if($products_stock_list['numberOfResults']>0){
                $formated_items = $this->Woocommerce_afas_model->formatArticleFromAfasWoocommerce($projectId, $products_stock_list, 'article_update');
                $this->Woocommerce_model->importArticleInWoocommerce($formated_items, $projectId, 'stock_update');
                if(count($formated_items)<$amount){
                    $this->Projects_model->saveValue('article_update_offset', null, $projectId);
                    $this->Projects_model->saveValue('article_last_update_date', $currentdatetime, $projectId);
                }
            } else{
                $this->Projects_model->saveValue('article_last_update_date', $currentdatetime, $projectId);
                $this->Projects_model->saveValue('article_update_offset', null, $projectId);
            }
        }
    }

    #########################################################################################################
    #       function is used to import or update articles stocks  in WooCommerce from erp system exact.     #
    #########################################################################################################
    public function updateArticleFromExact($projectId = '', $itemCode=''){
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Woocommerce_exactonline_model');
            $this->load->model('Woocommerce_model');      
            $is_published = $this->Projects_model->getValue('import_as_published', $projectId);
            $import_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
            $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
            $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
            $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
            $lastUpdateDate = $this->Projects_model->getValue('exact_article_last_update_date', $projectId)?$this->Projects_model->getValue('exact_article_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate  = str_replace('+00:00', '.000Z', gmdate('c', strtotime($lastUpdateDate)));
            $currentdatetime = str_replace('+00:00', '.000Z', gmdate('c', strtotime(date("Y-m-d H:i:00"))));
            $import_option_array = [
                'lastUpdateDate'=>$lastUpdateDate,
                'is_published'=>$is_published,
                'import_exact_image'=>$import_exact_image, 
                'import_exact_description'=>$import_exact_description, 
                'import_exact_extra_description'=>$import_exact_extra_description, 
                'woocommerce_stock_options'=>$woocommerce_stock_options
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
            $items = $this->Woocommerce_exactonline_model->getExactArticleStocks($connection, $offset, $amount, $itemCode, $import_option_array, $projectId);
            if(!empty($items)){
                $this->Woocommerce_model->importArticleInWoocommerce($items, $projectId, 'stock_update');
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

    




    // public function woorestCronJobtest(){
    //     $this->load->model('Projects_model');
    //     $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3])->get()->result_array();
    //     if(!empty($projects)){
    //         foreach ($projects as $p_key => $p_value) {
    //             $projectId          = $p_value['id'];
    //             if($projects!=25)
    //                 continue;
    //             if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce')
    //                 continue;
    //             $enabled            = $this->Projects_model->getValue('enabled', $projectId);
    //             // check if the last execution time is satisfy the time checking.
    //             if($enabled == '1'){
    //                 if($p_value['erp_system'] == 'exactonline') {
    //                    // $this->importArticleFromExact($projectId);
    //                     $this->importCustomerFromExact($projectId);
    //                 } else if($p_value['erp_system'] == 'afas') {
    //                     $this->importArticleFromAfas($projectId);
    //                     //$this->importCustomerFromAfas($projectId);
    //                 } 
    //             }
    //         }
    //     } 
    //     //$this->updateArticleInWoocommerce();


    // }
    
    // please remove this (webhookOrdersTest) method if it exists. Thanks
    public function webhookOrdersTest(){
        $this->load->model('Projects_model');
        $projectId  = 15;
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Woocommerce_exactonline_model');
        $this->load->model('Woocommerce_model');
        $this->load->model('Exactonline_model');
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
        $getOrder   = $this->Woocommerce_model->getWooCommerceOrders($projectId, 31590);
        $getOrder   = json_decode(json_encode($getOrder), true);
        // print_r($getOrder);
        // exit();
        $import_option = $this->Projects_model->getValue('woocommerce_import_option', $projectId)?$this->Projects_model->getValue('woocommerce_import_option', $projectId):'orders';
        if($import_option=='orders'){
            $sendOrder  = $this->Woocommerce_exactonline_model->sendOrder($connection, $projectId, $getOrder);
            $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
            $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
            print_r($sendOrder);
            if($sendOrder['status']==0){
                $totalOrderImportError++;
            } else{
                $totalOrderImportSuccess++;
            }
            $this->Projects_model->saveValue('total_orders_import_success', $totalOrderImportSuccess, $projectId);
            $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
            project_error_log($projectId, 'exportorders',$sendOrder['message']);
         
        }
        //else
          //$sendOrder  = $this->Woocommerce_exactonline_model->sendInvoice($connection, $projectId, $getOrder);
       print_r($sendOrder);
    }


    public function getSalesTax(){
        $this->load->model('Projects_model');
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Woocommerce_exactonline_model');
        $this->load->model('Exactonline_model');
        $projectId  = 9;
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
        $getWebhook  = $this->Woocommerce_exactonline_model->getSalesTax($connection, $projectId);
        print_r($getWebhook);
    }


    public function getSalesOrders(){
        $this->load->model('Projects_model');
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Woocommerce_exactonline_model');
        $this->load->model('Exactonline_model');
        $projectId  = 34;
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
        $getWebhook  = $this->Woocommerce_exactonline_model->getSalesOrders($connection, $projectId);
        print_r($getWebhook);
    }


     // --------------------- test order export 
    public function exportOrdersAfas(){
        if(isset($_GET['order_id']) && $_GET['order_id'] > 0){
            $this->load->model('Woocommerce_afas_model');
            $this->load->model('Projects_model');
            $this->load->model('Afas_common_model'); 
            $this->load->model('Woocommerce_model');
            $orderData      = $this->Woocommerce_model->getWooCommerceOrders(25,$_GET['order_id']);
            $orderData      = json_decode(json_encode($orderData), true);
            $this->Afas_common_model->sendInvoice(25, $orderData);
        }
    }

    public function importArticleFromExacttest(){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Woocommerce_exactonline_test_model');
        $this->load->model('Woocommerce_model');
        // get all projects having erp system exact  with webshop woocommerce.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline','id'=>34))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='WooCommerce')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; // d5df511a-eb03-4b30-a1b6-1f1526a93383
                $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:'JD-4X140'; 
                // check if the last execution time is satisfy the time checking. customers_amount
                //if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('articles_last_execution', time(), $projectId);
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
                    $is_published = $this->Projects_model->getValue('import_as_published', $projectId) ? $this->Projects_model->getValue('import_as_published', $projectId) : 1;
                    $impost_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
                    $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
                    $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
                    $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
                    $import_option_array = ['import_exact_description'=>$import_exact_description, 'import_exact_extra_description'=>$import_exact_extra_description, 'woocommerce_stock_options'=>$woocommerce_stock_options];
                    // ------- get article from exactonline based on amount and offset ----------------       //
                    $items = $this->Woocommerce_exactonline_test_model->getExactArticle($connection, $itemId, '', 2, $is_published,$itemCode, $impost_exact_image, $import_option_array, $projectId);
                    // print_r($items);
                    // exit();
                    // print_r($items);
                    // exit();
                    // $items = array();
                    // $items_deatils = array();
                    // $items_deatils['sku'] = 'manual_test_option';
                    // $items_deatils['Barcode'] = 'manual_testoption';
                    // $items_deatils['status'] = 'publish';
                    // $items_deatils['name'] = 'manual_test name';
                    // //$items_deatils['short_description'] = 'manual_test short_description';
                    // //$items_deatils['description'] = 'manual_test description';
                    // $items_deatils['StartDate'] = date('Y-m-d');
                    // $items_deatils['type'] ='simple';
                    // $items_deatils['manage_stock'] = 0;
                    // $items_deatils['on_sale'] = 1;
                    // $items_deatils['purchasable'] = 1;
                    // $items_deatils['price'] = 1.69;
                    // $items_deatils['NumberOfItemsPerUnit'] = 5;
                    // $items_deatils['Quantity'] = 5;
                    // $items_deatils['ItemGroupCode'] = 'Indoor';
                    // $items_deatils['ItemGroupDescription'] = 'Indoor Sports';
                    // $items_deatils['stock'] = 53;
                    // $items[0] = $items_deatils;
                    $items2 = $this->Woocommerce_model->importArticleInWoocommerce($items, $projectId);
                    print_r($items2);
                    exit();
                    foreach ($items as $key => $value) {
                        print_r($value);
                        //$this->Projects_model->saveValue('article_offset', $value['Id'], $projectId);
                    } 
                    echo count($items);
                    //print_r($items);
                    // ------ call Woocommerce_model to create and update article in WooCommerce ------       //
                    //if(!empty($items))
                        //$items = $this->Woocommerce_model->importArticleInWoocommerce($items, $projectId);
                   // else
                       // $this->Projects_model->saveValue('article_offset', null, $projectId);
               // }
            }
        }
    }

    public function test(){
        $this->load->model('Projects_model');
        $this->load->model('Woocommerce_model');
        $projectId  = 34;
        // $this->load->helper('ExactOnline/vendor/autoload');
        // $this->load->model('Woocommerce_exactonline_model');
        // $this->load->model('Exactonline_model');
        //--------------- make exact connection ----------------------------------//
        // $this->Exactonline_model->setData(
        //     array(
        //         'projectId'     => $projectId,
        //         'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
        //         'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
        //         'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
        //     )
        // );
        // $connection = $this->Exactonline_model->makeConnection($projectId);
        // print_r($connection);
        // exit();

        //$sendWebhook  = $this->Woocommerce_exactonline_model->sendWebhook($connection, $projectId);
        //$getWebhook  = $this->Woocommerce_exactonline_model->getWebhook($connection, $projectId);
        $this->Woocommerce_model->tester($projectId);
        print_r($getWebhook);
    }

    public function testwebhook(){
        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true);
        $file = fopen('testwebhooksubs.txt','a+');
        fwrite($file, print_r($content, true));
        fclose($file);
        $file = fopen('testwebhooksubs.txt','a+');
        fwrite($file, 'print_r($content, true)');
        fclose($file);
    }

}

/* End of file woorest.php */
/* Location: ./application/controllers/woorest.php */