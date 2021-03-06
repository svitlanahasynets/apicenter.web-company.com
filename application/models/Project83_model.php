<?php
class Project83_model extends CI_Model {

	public $projectId;

    function __construct() {
        parent::__construct();
        $this->projectId = 83;
    }
	
	public function obj2arr($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->obj2arr($value);
			}
			return $result;
		}
		return $data;
	}
	
	public function ToAFASStructure($WooOrder) 
	{
	    //log_message('debug', 'WC Order PID 83 '. var_export($WooOrder, true));
	    
	    $finalOrder = '';
	    $finalOrder = array(
	        'id'        => $WooOrder->id,
	        'order_id'  => $WooOrder->number,
	        'status'    => $WooOrder->status,
	        'customer'  => array(
	            'id'            => isset($WooOrder->customer_id) ? $WooOrder->customer_id : '',
	            'email'         => isset($WooOrder->billing->email) ? $WooOrder->billing->email : '',
	            'first_name'    => isset($WooOrder->billing->first_name) ? $WooOrder->billing->first_name : '',
	            'last_name'     => $WooOrder->billing->last_name,
	        ),
	        'create_at'     => $WooOrder->date_created,
	        'modified_at'   => $WooOrder->date_modified,
	        'currency'      => $WooOrder->currency,
	        'totals'        => array(
	            'total'         => $WooOrder->total,
	            'shipping'      => $WooOrder->shipping_total,
	            'tax'           => $WooOrder->total_tax,
	            'discount'      => $WooOrder->discount_total,
	        ),
	        'billing_address' => array(
	            'first_name'    => $WooOrder->billing->first_name,
	            'last_name'     => $WooOrder->billing->last_name,
	            'postcode'      => $WooOrder->billing->postcode,
	            'address1'      => $WooOrder->billing->address_1,
	            'address2'      => isset($WooOrder->billing->address_2) ? $WooOrder->billing->address_2 : '',
	            'phone'         => $WooOrder->billing->phone,
	            'city'          => $WooOrder->billing->city,
	            'country'       => $WooOrder->billing->country,
                'state'         => isset($WooOrder->billing->state) ? $WooOrder->billing->state : '',
                'company'       => isset($WooOrder->billing->company) ? $WooOrder->billing->company : '',
	        ),
	        'shipping_address' => array(
	            'first_name'    => $WooOrder->shipping->first_name,
                'last_name'     => $WooOrder->shipping->last_name,
                'postcode'      => $WooOrder->shipping->postcode,
                'address1'      => $WooOrder->shipping->address_1,
                'address2'      => isset($WooOrder->shipping->address_2) ? $WooOrder->shipping->address_2 : '',
                'city'          => $WooOrder->shipping->city,
                'country'       => $WooOrder->shipping->country,
                'state'         => isset($WooOrder->shipping->state) ? $WooOrder->shipping->state : '',
                'company'       => isset($WooOrder->shipping->company) ? $WooOrder->shipping->company : '',
            ),
            'payment_method' => $WooOrder->payment_method_title,
            'comment'        => $WooOrder->customer_note,
	    );
	    
	    $finalOrder['order_products'] = array();
	    $arrLines = $WooOrder->line_items;
	    
	    foreach($arrLines as $item) {
	        $finalOrder['order_products'][] = array(
	            'product_id'            => $item->product_id,
	            'order_product_id'      => $item->product_id,
	            'model'                 => $item->sku,
	            'name'                  => $item->name,
	            'price'                 => $item->price,
	            'quantity'              => $item->quantity,
	            'total_price'           => $item->total - $item->total_tax,
	            'total_price_incl_tax'  => $item->total,
	            'tax_value'             => $item->total_tax,
	        );
	    }
	    
	    log_message('debug', 'WC final Order PID 83 '. var_export($finalOrder, true));
	    
	    return $finalOrder;
	}

	function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Woocommerce_model');
		
		$project = $this->db->get_where('projects', array('id' => 83))->row_array();

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
				log_message('debug', 'File contents '. var_export($savedOrders, true));
			} else {
				$savedOrders = array();
			}
*/
			$savedOrders = $this->Projects_model->getProjectData($project['id'], 'saved_orders', true);
			
			
			//$filters = array('after' => date('Y-m-d').'T00:00:00');
			$filters = array('exclude' => $savedOrders, 'status' => 'processing');
			$orders = $this->Woocommerce_model->getOrdersWithFilters($project['id'], $filters);
			
			//$orders = $this->obj2arr($orders);
			
			if($orders != false && !empty($orders)){
			  
			    $this->Projects_model->saveValue('orders_last_execution', time(), $project['id']);
				
				foreach($orders as $order){
				    if($erpSystem == 'afas'){
				        
				        $convert = $this->ToAFASStructure($order);
				        
						//$result = $this->Afas_model->sendOrder($project['id'], $convert);
						
						
						$savedOrders[] = $convert['order_id'];
						$this->Projects_model->saveProjectData($project['id'], 'saved_orders', $savedOrders, true);
						//file_put_contents($savedOrdersLocation, json_encode($savedOrders));
					}
				}
			}
		}
	}
}