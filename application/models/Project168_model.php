<?php
class Project168_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 168;
    }
	
	function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Woocommerce_model');
		
		$project = $this->db->get_where('projects', array('id' => 168))->row_array();

		// Check if enabled
		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			return;
		}
		
		if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
			return;
		}
		
		// Get credentials
		$storeUrl = $project['store_url'];
		$apiKey = $project['api_key'];
		$pluginKey = $project['plugin_key'];
		$storeKey = $project['store_key'];
		$erpSystem = $project['erp_system'];
		
		// Send orders combined per day
		$lastExecution = $this->Projects_model->getValue('orders_financialbooking_last_execution', $project['id']);
		$interval = 2;
		if(($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
			$orderAmount = $this->Projects_model->getValue('orders_amount', $project['id']);

/*
			if(!file_exists(DATA_DIRECTORY.'/projects_files/'.$project['id'].'/')){
				mkdir(DATA_DIRECTORY.'/projects_files/'.$project['id'].'/', 0777, true);
			}
			$savedOrdersLocation = DATA_DIRECTORY.'/projects_files/'.$project['id'].'/saved_orders.json';
			if(file_exists($savedOrdersLocation)){
				$savedOrders = json_decode(file_get_contents($savedOrdersLocation), true);
			} else {
				$savedOrders = array();
			}
*/
			$savedOrders = $this->Projects_model->getProjectData($project['id'], 'saved_orders', true);
			
			//$filters = array('after' => date('Y-m-d').'T00:00:00');
			$filters = array('exclude' => $savedOrders, 'status' => 'processing');
			$orders = $this->Woocommerce_model->getOrdersWithFilters($project['id'], $filters);
			
			log_message('error', 'WC Order PID 168 '. var_export($orders, true));
			
// 			echo '<pre>';print_r($orders);exit;
			$this->Projects_model->saveValue('orders_financialbooking_last_execution', time(), $project['id']);

			if($orders != false && !empty($orders)){
				
				foreach($orders as $order){
					if(in_array($order->id, $savedOrders)){
						continue;
					}
					
					$addr2 = isset($order->billing->address_2) ? $order->billing->address_2 : '';
					$addr1 = $order->billing->address_1 . ' ' . $addr2;
					
					$customerData = array(
						'email' => $order->billing->email,
						'first_name' => $order->billing->first_name,
						'last_name' => $order->billing->last_name,
						'type' => 'billing',
						'postcode' => $order->billing->postcode,
						'address1' => $addr1, 
						'address2' => isset($order->billing->address_2) ? $order->billing->address_2 : '',
						'phone' => isset($order->billing->phone) ? $order->billing->phone : '',
						'city' => $order->billing->city,
						'country' => $order->billing->country,
						'company' => isset($order->billing->company) ? $order->billing->company : '',
					);
					
					log_message('error', 'WC Order PID 168 '. var_export($customerData, true));
					
					if(!$debtorId = $this->Afas_model->checkAfasCustomerExists($project['id'], $customerData, "", $order->meta_data)){
					    log_message('error', 'WC Order PID 168 '. var_export($debtorId, true));
						api2cart_log($project['id'], 'exportorders', 'Could not find/add customer to AFAS');
						return false;
					}
					
					//$InvoiceNr = isset($order->meta_data[10]->value) ? $order->meta_data[10]->value : '';
					
					$arrMeta = $order->meta_data;
					$invoiceNr = 0;
					foreach($arrMeta as $sElement) {
					    if ($sElement->key == '_wcpdf_invoice_number'){
					       log_message('error', 'INVOICE NUMBER '. var_export($sElement->value, true)); 
					       $invoiceNr = $sElement->value;
					    }
					}
					
					$mainData = array(
						'UnId' => 1,
						'JoCo' => '20',
						'Year' => date('Y'),
						'Perio' => intval(date('m')),
					);
					$orderBookings = array();
					
					$totalInclTax = $order->total;
					$totalTax = $order->total_tax;
					$totalExclTax = $totalInclTax - $totalTax;
					
					$name = $order->billing->first_name.' '.$order->billing->last_name;
					if(isset($order->billing->company) && $order->billing->company != ''){
						$name = $order->billing->company;
					}
					
					
					
					
					///////////////////////////////////////////////////////////Scenario #1: alles met btw.
					
					///////////////////////////////////////////////////////////Scenario #2:
					
					
					//Volledige order bedrag naar Debiteur
					$orderBookings[] = array(
						'grootboek' => $debtorId, // Is in fact debtor !!!!
						'omschrijving' => 'Order ' . $order->number,
						'credit' => round($totalInclTax, 2),
						'afletterReferentie' => $order->number,
						'customFields' => array(
							'BpNr' => $invoiceNr,
							'InId' => $invoiceNr,
							'VaAs' => 2,
							'Fref' => 'Order ' . $order->number,
						),
					);
					
					$totaltax = 0;
					
					foreach($order->line_items as $item){
					    
					    $GBR = explode(',', $item->grootboekrekening);
					    $productExTax = round( ( $item->total ),2);
					    $totaltax = $totaltax + $item->total_tax;
					    
						$orderBookings[] = array(
							'grootboek' => $GBR ? $GBR : '801000',
							'omschrijving' => $item->name,
							'debet' => $productExTax,
							'btwCode' => 1,
							'afletterReferentie' => $order->number,
							'customFields' => array(
								'BpNr' => $invoiceNr,
								'InId' => $invoiceNr,
								'VaAs' => 1,
								'Fref' => 'Order ' . $order->number,
							),
						);
					}
					
					if($order->shipping_total > 0) {
					    $totaltax = $totaltax + $order->shipping_tax;
					
						$orderBookings[] = array(
    						'grootboek' => '740110', // Is in fact debtor !!!!
    						'omschrijving' => 'Order ' . $order->number,
    						'debet' => ($order->shipping_total - $order->shipping_tax),
    						'btwCode' => 1,
    						'afletterReferentie' => $order->number,
    						'customFields' => array(
    							'BpNr' => $invoiceNr,
    							'InId' => $invoiceNr,
    							'VaAs' => 1,
    							'Fref' => 'Order ' . $order->number,
    						),
    					);
					}
					if ($order->total_tax > 0){
    					$orderBookings[] = array(
    						'grootboek' => '152100', // Is in fact debtor !!!!
    						'omschrijving' => 'Order ' . $order->number,
    						'debet' => $totaltax,
    						'btwCode' => 1,
    						'afletterReferentie' => $order->number,
    						'customFields' => array(
    							'BpNr' => $invoiceNr,
    							'InId' => $invoiceNr,
    							'VaAs' => 1,
    							'Fref' => 'Order ' . $order->number,
    						),
    					);
					}
					////////////////////////////////////////////////////////////////////////////
					
					
					
					
					
					
					
					
					
					
					
					/*
					$orderBookings[] = array(
						'grootboek' => 801000, // Is in fact debtor !!!!
						'omschrijving' => 'Order ' . $order->number,
						'debet' => round($totalInclTax, 2),
						'btwCode' => 1,
						'afletterReferentie' => $order->number,
						'customFields' => array(
							'BpNr' => $invoiceNr,
							'InId' => $invoiceNr,
							'VaAs' => 1,
							'Fref' => 'Order ' . $order->number,
						),
					);
					*/
					
					/*
					foreach($order->line_items as $item){
						$orderBookings[] = array(
							'grootboek' => $item->grootboekrekening ? $item->grootboekrekening : '801000',
							'omschrijving' => $item->name,
							'credit' => round($item->total, 2),
							'btwCode' => 1,
							'afletterReferentie' => $order->number,
							'customFields' => array(
								'BpNr' => $invoiceNr,
								'InId' => $invoiceNr,
								'VaAs' => 1,
								'Fref' => 'Order ' . $order->number,
							),
						);
					}
					*/
					
					log_message('error', 'WC Order BOOKING '. var_export($orderBookings, true));
									
					$savedOrders[] = $order->id;
					$this->Projects_model->saveProjectData($project['id'], 'saved_orders', $savedOrders, true);
// 					file_put_contents($savedOrdersLocation, json_encode($savedOrders));
					
					$this->Afas_model->addFiEntries($project['id'], $orderBookings, $mainData);
					api2cart_log($project['id'], 'custom_cronjob', 'Added Financial Entries: '.var_export($orderBookings, true));
			
				}
			}
		}
		//echo 'finished';exit;
	}
	
    public function setCustomerParams($fields, $customerData, $ordernumber = "", $arrMeta){
        $btw = '';
       
        foreach($arrMeta as $sElement) {
		    if ($sElement->key == 'btw'){
		       log_message('error', 'WC ORDER PID 168 btw '. var_export($sElement->value, true)); 
		       $btw = $sElement->value;
		    }
	    }
       
        if ($btw != '') $fields->VaId = $btw;
    }
    
    public function setCustomerOrganisationParams($fields, $customerData, $ordernumber, $arrMeta){
        $kvk = '';
        
        foreach($arrMeta as $sElement) {
		    if ($sElement->key == 'kvk'){
		       log_message('error', 'WC ORDER PID 168 kvk '. var_export($sElement->value, true)); 
		       $kvk = $sElement->value;
		    }
	    }
       
        if ($kvk != '') $fields->CCnR = $kvk;
    }
    
}