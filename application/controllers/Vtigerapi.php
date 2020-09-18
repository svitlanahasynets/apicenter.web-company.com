<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Vtigerapi extends CI_Controller {

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

    #########################################################################################################
    #                 function call back after authorised from exact and set token                          #
    #########################################################################################################
    public function authoriseExact(){
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

    #########################################################################################################
    #     function call fron cron job of apicenter to execute vtiger import export operation                #
    #########################################################################################################
    public function vtigerCronJob(){
        $this->load->model('Projects_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3])->get()->result_array();
         if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='vtiger')
                    continue;
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                // check if the last execution time is satisfy the time checking.
                if($enabled == '1'){
                    $import_option = $this->Projects_model->getValue('vtiger_import_option', $projectId)?$this->Projects_model->getValue('vtiger_import_option', $projectId):'';
                    if($import_option == 'invoices'){
                        $this->createInvoice($projectId);
                    } else if($import_option == 'salesentry'){
                        $this->createSalesEntry($projectId);
                    }
                }
            }
        } 
    }

	#########################################################################################################
	#                 function used to export invoioce from vtiger and import to ExactOnline                #
	#########################################################################################################
	public function createInvoice($projectId = ''){
        if($projectId!=''){
    	    $this->load->helper('ExactOnline/vendor/autoload');
    		$this->load->model('Vtigerexactonline_model');
    		$this->load->model('Exactonline_model');
    		$this->load->model('Projects_model');
    		// get all projects having erp system afas with mailchimp.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
            	foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='vtiger')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('invoice_export_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                    $import_option      = $this->Projects_model->getValue('vtiger_import_option', $projectId)?$this->Projects_model->getValue('vtiger_import_option', $projectId):'';
                    // check if the last execution time is satisfy the time checking.
                    if($import_option == 'invoices' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $this->Projects_model->saveValue('invoice_export_last_execution', time(), $projectId);
                        // get the offset and amount to import customers. 
                        $currentOrdersOffset = $this->Projects_model->getValue('invoice_export_vtiger_offset', $projectId) ? $this->Projects_model->getValue('invoice_export_vtiger_offset', $projectId) : '';
                        $ordersAmount=  $this->Projects_model->getValue('orders_amount', $projectId) ? $this->Projects_model->getValue('orders_amount', $projectId) : 10;
                        $invoiceId 	= isset($_GET['invoiceId'])?$_GET['invoiceId']:null; 
                        //--------------- make exact connection ----------------------------------//
    					$this->Exactonline_model->setData(
    						array(
    							'projectId'     => $projectId,
    							'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
    							'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
    							'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
    						)
    					);
    					$connection  = $this->Exactonline_model->makeConnection($projectId);
    					// get invoice from exactonline
    					$getVtigerInvoice = $this->Vtigerexactonline_model->getVtigerInvoice($projectId, $currentOrdersOffset , $ordersAmount);
    					if(!empty($getVtigerInvoice)){
    						foreach ($getVtigerInvoice as $inv_key => $inv_value) {
    							$getRequestedInvoice    = $this->Vtigerexactonline_model->getRequestedInvoice($projectId, $inv_value['id']);
    							if($getRequestedInvoice){
                                    $currentOrdersOffset = $currentOrdersOffset + count($getVtigerInvoice);
                                    $this->Projects_model->saveValue('invoice_export_vtiger_offset', $currentOrdersOffset, $projectId);
                                    $totalInvoiceImportSuccess = $this->Projects_model->getValue('total_invoice_import_success', $projectId)?$this->Projects_model->getValue('total_invoice_import_success', $projectId):0;
                                    $totalInvoiceImportError = $this->Projects_model->getValue('total_invoice_import_error', $projectId)?$this->Projects_model->getValue('total_invoice_import_error', $projectId):0;
    								// import invoice in exactonline
    								$sendInvoice = $this->Vtigerexactonline_model->importInvoicesToExact($connection, $projectId, $getRequestedInvoice);
                                    if($sendInvoice['status']==2){
                                       continue;
                                    } else if($sendInvoice['status']==0){
                                        $totalInvoiceImportError++;
                                    } else{
                                        $totalInvoiceImportSuccess++;
                                    }
                                    $this->Projects_model->saveValue('total_invoice_import_success', $totalInvoiceImportSuccess, $projectId);
                                    $this->Projects_model->saveValue('total_invoice_import_error', $totalInvoiceImportError, $projectId);
    								project_error_log($projectId, 'importInvoices',$sendInvoice['message']);
    							}
    						}
    					} else{
                            $this->Projects_model->saveValue('invoice_export_vtiger_offset', null, $projectId);
    					}                  
                    }
                }
            } 
        }
        $this->updateInvoicesToVtiger();
	}

    #########################################################################################################
    #                 function used to import invoioce to vtiger from  ExactOnline                          #
    #########################################################################################################
    public function createSalesEntry($projectId = ''){
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Vtigerexactonline_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Projects_model');
            // get all projects having erp system afas with mailchimp.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    $import_option = $this->Projects_model->getValue('vtiger_import_option', $projectId)?$this->Projects_model->getValue('vtiger_import_option', $projectId):'';
                    if($this->Projects_model->getValue('cms', $projectId)!='vtiger' && $import_option != 'salesentry')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('sales_entry_export_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('sales_entry_interval', $projectId);
                    $enabled            = $this->Projects_model->getValue('sales_entry_enabled', $projectId);
                    $vtiger_journal      = $this->Projects_model->getValue('vtiger_journal', $projectId)?$this->Projects_model->getValue('vtiger_journal', $projectId):'';
                    if($vtiger_journal==''){
                        continue;
                    }
                    $import_option      = $this->Projects_model->getValue('vtiger_import_option', $projectId)?$this->Projects_model->getValue('vtiger_import_option', $projectId):'';
                    // $invoice_idd = isset($_GET['record_id'])?$_GET['record_id']:'251';
                    // $invoice_idd = '7x'.$invoice_idd;
                    // check if the last execution time is satisfy the time checking.
                    if($import_option = 'salesentry' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $this->Projects_model->saveValue('sales_entry_export_last_execution', time(), $projectId);
                        // get the offset and amount to import customers. 
                        $currentOrdersOffset = $this->Projects_model->getValue('sales_entry_export_vtiger_offset', $projectId) ? $this->Projects_model->getValue('sales_entry_export_vtiger_offset', $projectId) : 0;
                        $ordersAmount=  $this->Projects_model->getValue('sales_entry_amount', $projectId) ? $this->Projects_model->getValue('sales_entry_amount', $projectId) : 10;
                        $invoiceId  = isset($_GET['invoiceId'])?$_GET['invoiceId']:null; 
                        //--------------- make exact connection ----------------------------------//
                        $this->Exactonline_model->setData(
                            array(
                                'projectId'     => $projectId,
                                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                            )
                        );
                        
                        $connection  = $this->Exactonline_model->makeConnection($projectId);
                        // get invoice from exactonline
                        $getVtigerInvoice = $this->Vtigerexactonline_model->getVtigerInvoice($projectId, $currentOrdersOffset , $ordersAmount);
                        if(!empty($getVtigerInvoice)){
                            $currentOrdersOffset = $currentOrdersOffset + count($getVtigerInvoice);
                            foreach ($getVtigerInvoice as $inv_key => $inv_value) {
                                $invoice_idd = $inv_value['id'];
                                $getRequestedInvoice  = $this->Vtigerexactonline_model->getRequestedInvoice($projectId, $invoice_idd);
                                if($getRequestedInvoice){
                                   // import invoice in exactonline
                                    $sendSalesEntry = $this->Vtigerexactonline_model->sendSalesEntry($connection, $projectId, $getRequestedInvoice, $vtiger_journal);
                                    $totalInvoiceImportSuccess = $this->Projects_model->getValue('total_sales_entry_import_success', $projectId)?$this->Projects_model->getValue('total_sales_entry_import_success', $projectId):0;
                                    $totalInvoiceImportError = $this->Projects_model->getValue('total_sales_entry_import_error', $projectId)?$this->Projects_model->getValue('total_sales_entry_import_error', $projectId):0;
                                    if($sendSalesEntry['status']==2){
                                        continue;
                                    } else if($sendSalesEntry['status']==0){
                                        $totalInvoiceImportError++;
                                    } else{
                                        $totalInvoiceImportSuccess++;
                                    }
                                    $this->Projects_model->saveValue('total_sales_entry_import_success', $totalInvoiceImportSuccess, $projectId);
                                    $this->Projects_model->saveValue('total_sales_entry_import_error', $totalInvoiceImportError, $projectId);
                                    project_error_log($projectId, 'importSalesEntry',$sendSalesEntry['message']);
                                }
                            }
                            $this->Projects_model->saveValue('sales_entry_export_vtiger_offset', $currentOrdersOffset, $projectId);

                        } else{
                            $this->Projects_model->saveValue('sales_entry_export_vtiger_offset', 0, $projectId);
                        }               
                    }
                }
            } 
        }
    }

    #########################################################################################################
    #                 function used to import invoioce to vtiger from  ExactOnline                          #
    #########################################################################################################
    public function updateInvoicesToVtiger(){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Vtigerexactonline_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        // get all projects having erp system afas with mailchimp.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline'))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='vtiger')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('orders_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                // check if the last execution time is satisfy the time checking.
                if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('orders_last_execution', time(), $projectId);
                    // get the offset and amount to import customers. 
                    $currentOrdersOffset = $this->Projects_model->getValue('orders_update_vtiger_offset', $projectId) ? $this->Projects_model->getValue('orders_update_vtiger_offset', $projectId) : '';
                    $ordersAmount=  $this->Projects_model->getValue('orders_amount', $projectId) ? $this->Projects_model->getValue('orders_amount', $projectId) : 10;
                    $invoiceId  = isset($_GET['invoiceId'])?$_GET['invoiceId']:null; 
                    //--------------- make exact connection ----------------------------------//
                    $this->Exactonline_model->setData(
                        array(
                            'projectId'     => $projectId,
                            'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                            'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                            'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                        )
                    );
                    $connection  = $this->Exactonline_model->makeConnection($projectId);
                    // get invoice from exactonline
                    $getExactPaidInvoice = $this->Vtigerexactonline_model->getExactPaidInvoice($connection, $invoiceId, $currentOrdersOffset , $ordersAmount);

                    if (!empty($getExactPaidInvoice)) {
                        $exportExactPaidInvoice = $this->Vtigerexactonline_model->exportExactPaidInvoice($projectId, $getExactPaidInvoice);
                    } else{
                        echo "string";
                       // $this->Projects_model->saveValue('orders_update_vtiger_offset', null, $projectId);
                    }
                }
            }
        }
    }



    ///--------------------------------------------------------------------------

    public function updateAfasInvoice(){
        $this->load->model('Vtigerexactonline_model');
        if (isset($_GET['record_id'])) {
            $this->Vtigerexactonline_model->updateAfasInvoice(14,'7x'.$_GET['record_id']);
        }
    }

	#########################################################################################################
	#                 function used to import products to vtiger from  ExactOnline                          #
	#########################################################################################################
	// public function importProductsToVtiger(){
	// 		$projectId = 14;
	// 		$this->load->helper('ExactOnline/vendor/autoload');
	// 		$this->load->model('Vtigerexactonline_model');
	// 		$this->load->model('Exactonline_model');
	// 		$this->load->model('Projects_model');
	// 		//--------------- make exact connection ----------------------------------//
	// 		$this->Exactonline_model->setData(
	// 				array(
	// 						'projectId'     => $projectId,
	// 						'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
	// 						'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
	// 						'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
	// 				)
	// 		);
	// 		$connection  = $this->Exactonline_model->makeConnection($projectId);
	// 		$amount 	 = 10;
	// 		// import article in vtiger from exactonline
	// 		$getExactArticle = $this->Vtigerexactonline_model->getExactArticle($connection, '', '', $amount);
	// 		// print_r($getExactArticle);
	// 		// exit;

	// 		if($getExactArticle){
	// 			$importExactProduct = $this->Vtigerexactonline_model->importExactProductInVtiger(14, $getExactArticle);
	// 		}
	// }

    public function createSalesEntryTest(){

        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Vtigerexactonline_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        // get all projects having erp system afas with mailchimp.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline', 'id'=>14))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                $import_option = $this->Projects_model->getValue('vtiger_import_option', $projectId)?$this->Projects_model->getValue('vtiger_import_option', $projectId):'';
                if($this->Projects_model->getValue('cms', $projectId)!='vtiger' && $import_option != 'salesentry')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('sales_entry_export_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('sales_entry_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('sales_entry_enabled', $projectId);
                $vtiger_journal     = $this->Projects_model->getValue('vtiger_journal', $projectId)?$this->Projects_model->getValue('vtiger_journal', $projectId):'';
                if($vtiger_journal==''){
                    continue;
                }

                $invoice_idd = isset($_GET['record_id'])?$_GET['record_id']:'680';
                $invoice_idd = '7x'.$invoice_idd;
                // check if the last execution time is satisfy the time checking.
                $this->Projects_model->saveValue('sales_entry_export_last_execution', time(), $projectId);
                // get the offset and amount to import customers. 
                $currentOrdersOffset = $this->Projects_model->getValue('sales_entry_export_vtiger_offset', $projectId) ? $this->Projects_model->getValue('sales_entry_export_vtiger_offset', $projectId) : 0;
                $ordersAmount=  $this->Projects_model->getValue('sales_entry_amount', $projectId) ? $this->Projects_model->getValue('sales_entry_amount', $projectId) : 10;
                $invoiceId  = isset($_GET['invoiceId'])?$_GET['invoiceId']:null; 
                //--------------- make exact connection ----------------------------------//
                $this->Exactonline_model->setData(
                    array(
                        'projectId'     => $projectId,
                        'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                        'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                        'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                    )
                );
                $connection  = $this->Exactonline_model->makeConnection($projectId);
                // $getVtigerInvoice = $this->Vtigerexactonline_model->getVtigerInvoice($projectId, 0 , 100);
                // print_r($getVtigerInvoice);
                // exit();
               // invoice_idd
                $getRequestedInvoice  = $this->Vtigerexactonline_model->getRequestedInvoice($projectId, $invoice_idd);
                if($getRequestedInvoice){
                   // import invoice in exactonline
                    $sendSalesEntry = $this->Vtigerexactonline_model->sendSalesEntry($connection, $projectId, $getRequestedInvoice, $vtiger_journal);
                    print_r($sendSalesEntry);
                    exit();
                    // $totalInvoiceImportSuccess = $this->Projects_model->getValue('total_sales_entry_import_success', $projectId)?$this->Projects_model->getValue('total_sales_entry_import_success', $projectId):0;
                    // $totalInvoiceImportError = $this->Projects_model->getValue('total_sales_entry_import_error', $projectId)?$this->Projects_model->getValue('total_sales_entry_import_error', $projectId):0;
                    // if($sendSalesEntry['status']==0){
                    //     $totalInvoiceImportError++;
                    // } else{
                    //     $totalInvoiceImportSuccess++;
                    // }
                    // $this->Projects_model->saveValue('total_sales_entry_import_success', $totalInvoiceImportSuccess, $projectId);
                    // $this->Projects_model->saveValue('total_sales_entry_import_error', $totalInvoiceImportError, $projectId);
                    // project_error_log($projectId, 'importSalesEntry',$sendSalesEntry['message']);
                }
            }
        } 
    }

    public function createDocument(){

        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Vtigerexactonline_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Projects_model');
        // get all projects having erp system afas with mailchimp.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline','id'=>12))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                //--------------- make exact connection ----------------------------------//
                $this->Exactonline_model->setData(
                    array(
                        'projectId'     => $projectId,
                        'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                        'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                        'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                    )
                );
                $connection     = $this->Exactonline_model->makeConnection($projectId);
                $sendSalesEntry = $this->Vtigerexactonline_model->createDocument($connection, $projectId);
            }
        } 
    }
}
