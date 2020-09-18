<?php
class Project138_model extends CI_Model {

	public $projectId;

    public function __construct()
    {
        parent::__construct();
        $this->projectId = 138;
    }

	public function xml2array( $xmlObject, $out = array () ){
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	}

	public function customCronjob(){
	    
	    //log_message('debug', 'CustomCron PID 138 ' );
	     
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Woocommerce_model');
		$this->load->model('Cms_model');
		
		$project = $this->db->get_where('projects', array('id' => 138))->row_array();

		// Check if enabled
		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			return;
		}
		
		$this->processEvents();
		$this->processOrders();
	}

	public function processEvents(){
		$events = $this->getEvents();
		if(!empty($events)){
// 			echo '<pre>';print_r($events);exit;
			$this->Woocommerce_model->updateArticles($this->projectId, $events);
		}
	}
	
	public function getEvents(){
		$projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$offset = $this->Projects_model->getValue('events_offset', $projectId) ? $this->Projects_model->getValue('events_offset', $projectId) : 0;
		$connector = 'Evenement_App';
		$amount = 5;
		
		$filtersXML = '';
		$indexXml = '<Index><Field FieldId="Code" OperatorType="1" /></Index>';
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connector;
		//$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);
		$numberOfResults = count($data->$connector);
		if($numberOfResults < 1){
			$this->Projects_model->saveValue('events_offset', 0, $projectId);
		} else {
			$this->Projects_model->saveValue('events_offset', $offset + $amount, $projectId);
		}

// 		echo '<pre>';print_r($data);exit;
		
		$events = array();
		foreach($data->$connector as $item){
			$item = $this->xml2array($item);
			
			$finalArticleData = array();
			$finalArticleData['model'] = $item['Code'];
			$finalArticleData['name'] = (string)$item['Omschrijving'].' '.date('d-m-Y', strtotime($item['Cursusdatum']));
			$finalArticleData['description'] = (string)$item['Omschrijving'];
			$finalArticleData['quantity'] = floatval($item['Maximumaantal_deelnemers']) - (isset($item['Aantal_deelnemers']) ? floatval($item['Aantal_deelnemers']) : 0);
			$finalArticleData['stock'] = floatval($item['Maximumaantal_deelnemers']) - (isset($item['Aantal_deelnemers']) ? floatval($item['Aantal_deelnemers']) : 0);
			$finalArticleData['manage_stock'] = 1;
			$finalArticleData['price'] = 10;
			$events[] = $finalArticleData;
		}
		return $events;
	}

	public function processOrders(){
		$orders = $this->getOrders();
		if(!empty($orders)){
			foreach($orders as $order){
				if($this->Afas_model->sendOrder($this->projectId, $order)){
					$count = 1;
					foreach($order['order_products'] as $item){
						//$item['orig_model'] = '0000000'.$count;
						if($item['orig_model'] != ''){
							// Now update attendees
							$this->addEventMembers($item, $order);
						}
						$count++;
					}
				}
				
				//echo '<pre>';print_r($order);exit;
			}
		}
	}
	
	public function getOrders(){
		$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $this->projectId) ? $this->Projects_model->getValue('orders_offset', $this->projectId) : 0;
		$orderAmount = $this->Projects_model->getValue('orders_amount', $this->projectId);
/*
$currentOrderOffset = 2;
$orderAmount = 1;
*/

		$result = $this->Woocommerce_model->getOrdersWithFilters($this->projectId, array('per_page' => $orderAmount, 'offset' => $currentOrderOffset, 'orderby' => 'date', 'order' => 'asc'));
		//echo '<pre>';print_r($result);exit;
		if(!is_array($result) || empty($result)){
			return array();
		}
		
		if(isset($result['count'])){
			$getOrderAmount = $result['count'];
			$orders = $result['orders'];
		} else {
			$orders = $result;
			$getOrderAmount = count($orders);
		}
		if($getOrderAmount > 0){
			$this->Projects_model->saveValue('orders_offset', $currentOrderOffset + $getOrderAmount, $this->projectId);
		}
		
		$finalOrders = array();
		foreach($result as $order){
			$order = $this->xml2array($order);
			$appendItem = array(
				'id' => $order['number'],
				'order_id' => $order['number'],
				'state' => $order['status'],
				'status' => $order['status'],
				'customer' => array(
					'id' => isset($order['customer_id']) ? $order['customer_id'] : '',
					'email' => $order['billing']['email'],
					'first_name' => $order['billing']['first_name'],
					'last_name' => $order['billing']['last_name']
				),
				'create_at' => $order['date_created'],
				'modified_at' => $order['updated_at'],
				'currency' => $order['order_currency_code'],
				'totals' => array(
					'total' => $order['total'],
					'subtotal' => $order['total'] - $order['total_tax'],
					'shipping' => $order['shipping_total'],
					'tax' => $order['total_tax'],
					'discount' => $order['discount_total'],
				)
			);
			if(isset($order['billing']) && !empty($order['billing'])){
				$appendItem['billing_address'] = array(
					'type' => 'billing',
					'first_name' => $order['billing']['first_name'],
					'last_name' => $order['billing']['last_name'],
					'postcode' => $order['billing']['postcode'],
					'address1' => $order['billing']['address_1'],
					'address2' => $order['billing']['address_2'],
					'phone' => isset($order['billing']['phone']) ? $order['billing']['phone'] : '',
					'city' => $order['billing']['city'],
					'country' => $order['billing']['country'],
					'state' => isset($order['billing']['state']) ? $order['billing']['state'] : '',
					'company' => isset($order['billing']['company']) ? $order['billing']['company'] : '',
				);
			}
			if(isset($order['shipping'])){
				$appendItem['shipping_address'] = array(
					'type' => 'shipping',
					'first_name' => $order['shipping']['first_name'],
					'last_name' => $order['shipping']['last_name'],
					'postcode' => $order['shipping']['postcode'],
					'address1' => $order['shipping']['address_1'],
					'address2' => $order['shipping']['address_2'],
					'phone' => isset($order['shipping']['phone']) ? $order['shipping']['phone'] : '',
					'city' => $order['shipping']['city'],
					'country' => $order['shipping']['country'],
					'state' => isset($order['shipping']['state']) ? $order['shipping']['state'] : '',
					'company' => isset($order['shipping']['company']) ? $order['shipping']['company'] : '',
				);
			}
			if(isset($order['shipping_lines']) && !empty($order['shipping_lines'])){
				$appendItem['shipping_method'] = $order['shipping_lines'][0]->method_title;
			}
			if(isset($order['payment_method_title'])){
				$appendItem['payment_method'] = $order['payment_method_title'];
			}
			if(isset($order['line_items']) && !empty($order['line_items'])){
				$appendItem['order_products'] = array();
				foreach($order['line_items'] as $item){
					$item = $this->xml2array($item);
					$appendItem['order_products'][] = array(
						'product_id' => $item['product_id'],
						'order_product_id' => $item['product_id'],
						'orig_model' => $item['sku'],
						'model' => 'EVNMT_BOOK',
						'name' => $item['name'],
						'price' => $item['price'],
						'discount_amount' => isset($item['discount_amount']) ? $item['discount_amount'] : 0,
						'quantity' => $item['quantity'],
						'total_price' => $item['total'],
						'total_price_incl_tax' => $item['total'] + $item['total_tax'],
						'tax_value' => isset($item['total_tax']) ? $item['total_tax'] : 0
					);
				}
			}
			if(isset($order['customer_note']) && $order['customer_note'] != ''){
				$appendItem['comment'] = $order['customer_note'];
			}
			
			if($appendItem != false){
				$finalOrders[] = $appendItem;
			}

		}
		return $finalOrders;
	}
	
	public function setOrderProductParams($fields, $item){
		if(isset($item['name'])){
			$fields->Ds = $item['name'];
		}
	}
	
	public function getEventBySku($sku){
		$this->projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $this->projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $this->projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $this->projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $this->projectId);
		$connector = 'Evenement_App';
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Code" OperatorType="1">'.$sku.'</Field></Filter></Filters>';
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);

// 		echo '<pre>';print_r($data);exit;
		
		$events = array();
		foreach($data->$connector as $item){
			$item = $this->xml2array($item);
			return $item;
		}
		return false;
	}
	
	public function addEventMembers($item, $order){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $this->projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $this->projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $this->projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $this->projectId);
		$afasConnector = 'KnCourseMember';
		
		$billingData = $order['billing_address'];
		$customerData = $order['customer'];
		$customerData = array_merge($customerData, $billingData);
		$orgPerId = $this->getOrgPer($customerData);
		$debtorId = $this->Afas_model->checkAfasCustomer($this->projectId, $customerData, 'email');
		if($orgPerId == false || $debtorId == false){
			return false;
		}
		$contactId = $this->getContact($orgPerId);
		if($contactId == false){
			$contactId = $orgPerId;
		}
		
		$eventData = $this->getEventBySku($item['orig_model']);
// 		echo '<pre>';print_r($eventData);
// 		echo '<pre>';print_r($order);exit;
		if($eventData){
			$xmlEvent = new SimpleXMLElement("<".$afasConnector."></".$afasConnector.">");
			$orderElement = $xmlEvent->addChild('Element');
			$orderElement->addAttribute('CrId', $eventData['Evenement_Id']);
			$orderElement->addAttribute('CdId', $contactId);
			$fields = $orderElement->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
	
			$fields->BcCo = $orgPerId;
			$fields->DeId = $debtorId;
			$fields->SuDa = date('Y-m-d', strtotime($order['create_at']));
			$fields->Invo = 0;
			$fields->Rm = 'Webshop order '.$order['id'];
			$fields->CuId = 'EUR';
			
// 			echo '<pre>';print_r($xmlEvent);exit;
			
			$data = $xmlEvent->asXML();
			$data = str_replace('<?xml version="1.0"?>', '', $data);
			$data = str_replace("
	", '', $data);
			
			$this->load->helper('NuSOAP/nusoap');
			
			$client = new nusoap_client($afasUpdateUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();
			
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorType'] = $afasConnector;
			$xml_array['connectorVersion'] = 1;
			$xml_array['dataXml'] = $data;
			$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
			
			if((isset($result['faultcode']) && $result['faultcode'] != '') || $result === false){
		        api2cart_log($this->projectId, 'exportorders', 'Could not add member to event '.$item['orig_model'].', order '.$order['id'].' in AFAS. Error: '.$result['faultstring']);
				return false;
			} else {
				api2cart_log($this->projectId, 'exportorders', 'Added member to event '.$item['orig_model'].', order '.$order['id'].' to AFAS.');
			}
			return true;
		}
		return false;
	}
	
	public function getOrgPer($customerData, $orgPerType = 'Organisatie'){
		$projectId = $this->projectId;
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="MailWork" OperatorType="1">'.$customerData['email'].'</Field></Filter></Filters>';
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$orgPerConnector = $this->Projects_model->getValue('afas_orgper_connector', $projectId);
		if($orgPerConnector == ''){
			$orgPerConnector = 'Profit_OrgPer';
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $orgPerConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take><Index><Field FieldId="BcCo" OperatorType="0" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		
		$data = simplexml_load_string($resultData);
		if(isset($data->$orgPerConnector) && count($data->$orgPerConnector) > 0){
			$afasPersonData = $data->$orgPerConnector;
			$afasPersonId = $afasPersonData->BcCo;
			return (string)$afasPersonId;
		}
		return false;
	}
	
	public function getContact($orgPerId){
		$projectId = $this->projectId;
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrgNumber" OperatorType="1">'.$orgPerId.'</Field></Filter></Filters>';
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_Contacts_App';
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		
		$data = simplexml_load_string($resultData);
		if(isset($data->$connector) && count($data->$connector) > 0){
			$contactData = $data->$connector;
			$contactId = $contactData->ContactId;
			return (string)$contactId;
		} else {
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="PerNumber" OperatorType="1">'.$orgPerId.'</Field></Filter></Filters>';
			
			$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
			$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
			$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
			$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
			$connector = 'Profit_Contacts_App';
			
			$this->load->helper('NuSOAP/nusoap');
			
			$client = new nusoap_client($afasGetUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();
			
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorId'] = $connector;
			$xml_array['filtersXml'] = $filtersXML;
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
			
			$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			
			$data = simplexml_load_string($resultData);
			if(isset($data->$connector) && count($data->$connector) > 0){
				$contactData = $data->$connector;
				$contactId = $contactData->ContactId;
				return (string)$contactId;
			}
		}
		return false;
	}
}