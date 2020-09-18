<?php
class Project47_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 47;
    }
	
	function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Shopify_model');
		
		$project = $this->db->get_where('projects', array('id' => 47))->row_array();

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
		$interval = 10;
$interval = 0;
		if(($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$currentOrderOffset = 0;//$this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
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
			
			$filters = array(
				//'financial_status' => 'paid',
				'limit' => $orderAmount,
				//'page' => ($currentOrderOffset / $orderAmount) + 1
				
			);
			$orders = $this->Shopify_model->getOrders($project['id'], $filters);
// 			echo '<pre>';print_r($orders);exit;
			if($orders != false && !empty($orders)){
				foreach($orders as $order){
					if(in_array($order['id'], $savedOrders)){
						continue;
					}
					$savedOrders[] = $order['id'];
					
					$this->Afas_model->addDirectInvoice($project['id'], $order);
// 					api2cart_log($project['id'], 'orders', 'Added order: '.var_export($order, true));
					$this->Projects_model->saveProjectData($project['id'], 'saved_orders', $savedOrders, true);
// 					file_put_contents($savedOrdersLocation, json_encode($savedOrders));
				}
			}
			$this->Projects_model->saveValue('orders_financialbooking_last_execution', time(), $project['id']);
		}
		//echo 'finished';exit;
	}
	
	function loadCustomOrderAttributes($appendItem, $order, $projectId){
		$subtotal = 0;
		$totalTax = 0;
		foreach($appendItem['order_products'] as $index => $item){
			$subtotal += $item['total_price'];
			$totalTax += $item['tax_value'];
		}
		$subtotal = $subtotal - $appendItem['totals']['discount'];
		
		if(isset($appendItem['totals']) && isset($appendItem['totals']['shipping']) && $appendItem['totals']['shipping'] > 0){
			$subtotal = $subtotal + $appendItem['totals']['shipping'];
			unset($appendItem['totals']['shipping']);
		}
		
		$product = 'WEBS';
		$vatGroup = '';
		if(strpos($appendItem['id'], 'BSBE') != false || $appendItem['billing_address']['country_code'] == 'BE'){
			$product = 'WEBSB';
			$vatGroup = 'G';
		}
		$appendItem['order_products'] = array(
			array(
				'model' => $product,
				'name' => 'Webshop order',
				'price' => $subtotal,
				'quantity' => 1,
				'total_price' => $subtotal,
				'total_price_incl_tax' => $subtotal + $totalTax,
				'tax_value' => $totalTax,
				'vat_group' => $vatGroup
			)
		);
		$appendItem['PaTp'] = '00';
		if(isset($appendItem['payment_method']) && $appendItem['payment_method'] == 'mollie_ideal'){
			$appendItem['PaTp'] = 'Mol';
		}
/*
		if(isset($appendItem['payment_method']) && $appendItem['payment_method'] == 'mollie_ideal'){
			$appendItem['PaCd'] = 'Contant';
		}
*/
		return $appendItem;
	}
} 