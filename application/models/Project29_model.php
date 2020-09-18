<?php
class Project29_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 29;
    }
    
	public function getArticleData($articleData, $finalArticleData)
	{
		$finalData = $finalArticleData;
		
		//VATOmschrijving
		if(isset($articleData['VATOmschrijving']) && $articleData['VATOmschrijving'] != '' && $articleData['VATOmschrijving'] != null)
		{
		    $VatOmschrijving = "";
		    if($articleData['VATOmschrijving'] == "Hoog") $VatOmschrijving = "Hoog BTW";
		    else if($articleData['VATOmschrijving'] == "Laag") $VatOmschrijving = "Laag BTW";
		    else $VatOmschrijving = "Geen BTW";
		    
		    $finalData['custom_attributes']['tax_class_id'] = array(
		        'type' => 'dropdown',
	            'value' => $VatOmschrijving
		    );
		}
		
		if(isset($articleData['Maat']) && $articleData['Maat'] != '' && $articleData['Maat'] != null)
		{
		    $finalData['custom_attributes']['maat'] = array(
		        'type' => 'dropdown',
		        'value' => $articleData['Maat']
		    );
		}
		
		if(isset($articleData['Afmeting_2']) && $articleData['Afmeting_2'] != '' && $articleData['Afmeting_2'] != null)
		{
		    $finalData['custom_attributes']['afmeting_dropdown'] = array(
		       'type' => 'dropdown',
		       'value' => $articleData['Afmeting_2']
		    );
		}
		
		//Manufacturer
		if(isset($articleData['Merk']) && $articleData['Merk'] != '' && $articleData['Merk'] != null)
		{
			$finalData['custom_attributes']['manufacturer'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Merk']
			);
		}
		
		//Kleur
		if(isset($articleData['Kleur']) && $articleData['Kleur'] != '' && $articleData['Kleur'] != null)
		{
			$finalData['custom_attributes']['color'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Kleur']
			);
		}
		
		//extra_verzending
		if(isset($articleData['Verzending']) && $articleData['Verzending'] != '')
		{
			if($articleData['Verzending'] == "false")
			{
				$finalData['custom_attributes']['extra_verzending'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['extra_verzending'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//Opmerking
		if(isset($articleData['Opmerking']) && $articleData['Opmerking'] != '' && $articleData['Opmerking'] != null)
		{
			$finalData['custom_attributes']['description'] = array(
				'type' => 'text',
				'value' => $articleData['Opmerking']
			);
		}	
		
		//Minimum_verkoopaantal
		if(isset($articleData['Minimum_verkoopaantal']) && $articleData['Minimum_verkoopaantal'] != '' && $articleData['Minimum_verkoopaantal'] != null)
		{
			$finalData['custom_attributes']['min_cart_qty'] = array(
				'type' => 'text',
				'value' => $articleData['Minimum_verkoopaantal']
			);
		}
		
		//Nettogewicht
		if(isset($articleData['Nettogewicht']) && $articleData['Nettogewicht'] != '' && $articleData['Nettogewicht'] != null)
		{
			$finalData['custom_attributes']['weight'] = array(
				'type' => 'text',
				'value' => $articleData['Nettogewicht']
			);
		}
		
		//Doeldier
		if(isset($articleData['Doeldier']) && $articleData['Doeldier'] != '' && $articleData['Doeldier'] != null)
		{
		    $temp = explode(";", $articleData['Doeldier']);
		    
			$finalData['custom_attributes']['animals'] = array(
				'type' => 'multiselect',
				'value' => $temp
			);
		}
		
		//Indicatie
		if(isset($articleData['Indicatie']) && $articleData['Indicatie'] != '' && $articleData['Indicatie'] != null)
		{
			$finalData['custom_attributes']['indicatie'] = array(
				'type' => 'text',
				'value' => $articleData['Indicatie']
			);
		}
		
		//Toedieningsvorm
		if(isset($articleData['Toedieningsvorm']) && $articleData['Toedieningsvorm'] != '' && $articleData['Toedieningsvorm'] != null)
		{
			$finalData['custom_attributes']['toedieningsvorm'] = array(
				'type' => 'text',
				'value' => $articleData['Toedieningsvorm']
			);
		}
		
		//Werkzamestoffen
		if(isset($articleData['Werkzamestoffen']) && $articleData['Werkzamestoffen'] != '' && $articleData['Werkzamestoffen'] != null)
		{
			$finalData['custom_attributes']['werkzamestoffen'] = array(
				'type' => 'text',
				'value' => $articleData['Werkzamestoffen']
			);
		}
		
		//Verpakkings_eenheid
		if(isset($articleData['Verpakkings_eenheid']) && $articleData['Verpakkings_eenheid'] != '' && $articleData['Verpakkings_eenheid'] != null)
		{
			$finalData['custom_attributes']['verpakkingseenheid'] = array(
				'type' => 'text',
				'value' => $articleData['Verpakkings_eenheid']
			);
		}
		
		//Wachttijd_melk
		if(isset($articleData['Wachttijd_melk']) && $articleData['Wachttijd_melk'] != '' && $articleData['Wachttijd_melk'] != null)
		{
			$finalData['custom_attributes']['wachttijd_melk'] = array(
				'type' => 'text',
				'value' => $articleData['Wachttijd_melk']
			);
		}
		//Wachttijd_vlees
		if(isset($articleData['Wachttijd_vlees']) && $articleData['Wachttijd_vlees'] != '' && $articleData['Wachttijd_vlees'] != null)
		{
			$finalData['custom_attributes']['wachttijd_vlees'] = array(
				'type' => 'text',
				'value' => $articleData['Wachttijd_vlees']
			);
		}
		//Dosering__
		if(isset($articleData['Dosering__']) && $articleData['Dosering__'] != '' && $articleData['Dosering__'] != null)
		{
			$finalData['custom_attributes']['dosering'] = array(
				'type' => 'text',
				'value' => $articleData['Dosering__']
			);
		}
		//Toelating
		if(isset($articleData['Toelating']) && $articleData['Toelating'] != '' && $articleData['Toelating'] != null)
		{
			$finalData['custom_attributes']['toelating'] = array(
				'type' => 'text',
				'value' => $articleData['Toelating']
			);
		}
		//Gevaren_Klasse
		if(isset($articleData['Gevaren_Klasse']) && $articleData['Gevaren_Klasse'] != '' && $articleData['Gevaren_Klasse'] != null)
		{
			$finalData['custom_attributes']['gevarenklasse'] = array(
				'type' => 'text',
				'value' => $articleData['Gevaren_Klasse']
			);
		}
		//Stof_gevarenklasse
		if(isset($articleData['Stof_gevarenklasse']) && $articleData['Stof_gevarenklasse'] != '' && $articleData['Stof_gevarenklasse'] != null)
		{
			$finalData['custom_attributes']['gevarenklasse_stof'] = array(
				'type' => 'text',
				'value' => $articleData['Stof_gevarenklasse']
			);
		}
		//Technischespecific.
		if(isset($articleData['Technischespecific.']) && $articleData['Technischespecific.'] != '' && $articleData['Technischespecific.'] != null)
		{
			$finalData['custom_attributes']['technische_specificaties'] = array(
				'type' => 'text',
				'value' => $articleData['Technischespecific.']
			);
		}
		//Status_artikel
		if(isset($articleData['Status_artikel']) && $articleData['Status_artikel'] != '' && $articleData['Status_artikel'] != null)
		{
			$finalData['custom_attributes']['status_artikel'] = array(
				'type' => 'text',
				'value' => $articleData['Status_artikel']
			);
		}
		//Barcode__Opgeschoonde_barcode_
		if(isset($articleData['Barcode__Opgeschoonde_barcode_']) && $articleData['Barcode__Opgeschoonde_barcode_'] != '' && $articleData['Barcode__Opgeschoonde_barcode_'] != null)
		{
			$finalData['custom_attributes']['eancode'] = array(
				'type' => 'text',
				'value' => $articleData['Barcode__Opgeschoonde_barcode_']
			);
		}
		//UN_nummer
		if(isset($articleData['UN_nummer']) && $articleData['UN_nummer'] != '' && $articleData['UN_nummer'] != null)
		{
			$finalData['custom_attributes']['un_nummer'] = array(
				'type' => 'text',
				'value' => $articleData['UN_nummer']
			);
		}
		//un_nummer_omschrijving
		if(isset($articleData['UN-no']) && $articleData['UN-no'] != '' && $articleData['UN-no'] != null)
		{
			$finalData['custom_attributes']['un_nummer_omschrijving'] = array(
				'type' => 'text',
				'value' => $articleData['UN-no']
			);
		}
		/////// Ja / Nee Veld
		if(isset($articleData['Uitlopend']) && $articleData['Uitlopend'] != '')
		{
			if($articleData['Uitlopend'] == "false")
			{
				$finalData['custom_attributes']['uitlopend'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['uitlopend'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}	
		/////// Ja / Nee Veld
		if(isset($articleData['GMP_']) && $articleData['GMP_'] != '')
		{
			if($articleData['GMP_'] == "false")
			{
				$finalData['custom_attributes']['gmp'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['gmp'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}	

		//Codering_Biocide
		if(isset($articleData['Codering_Biocide']) && $articleData['Codering_Biocide'] != '' && $articleData['Codering_Biocide'] != null)
		{
			$finalData['custom_attributes']['pt_codering_biocide'] = array(
				'type' => 'text',
				'value' => $articleData['Codering_Biocide']
			);
		}
		
		//UnitId
		if(isset($articleData['UnitId']) && $articleData['UnitId'] != '' && $articleData['UnitId'] != null)
		{
			$finalData['custom_attributes']['basiseenheid'] = array(
				'type' => 'multiselect',
				'value' => $articleData['UnitId']
			);
		}		
		
		//Extra_omschrijving
		if(isset($articleData['Extra_omschrijving']) && $articleData['Extra_omschrijving'] != '' && $articleData['Extra_omschrijving'] != null)
		{
			$finalData['custom_attributes']['extra_desciption'] = array(
				'type' => 'text',
				'value' => $articleData['Extra_omschrijving']
			);
			//$finalData['enabled'] = 'false';
		}
		
		// Blocked
		$finalData['tmp_attributes']['blocked'] = isset($articleData['Blocked']) ? $articleData['Blocked'] : "";
		if($articleData['Webshop_Tonen'] == false || $articleData['Webshop_Tonen'] == 'false'){
			$finalData['tmp_attributes']['blocked'] = 'true';
		}
		
		// Check for assortiment
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $this->projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $this->projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $this->projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $this->projectId);
		$afasArticleConnector = 'iPublications_Assortiment';
		
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$articleData['ItemCode'].'</Field></Filter></Filters>';
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1000</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("\n", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		
		$assortimenten = array();
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			foreach($data->$afasArticleConnector as $article){
				$assortimenten[] = (string)$article->Assortiment;
			}
		}
// 		echo '<pre>';print_r($assortimenten);exit;

		if(!empty($assortimenten))
		{
			$finalData['custom_attributes']['assortimenten'] = array(
				'type' => 'multiselect',
				'value' => $assortimenten
			);
		}
		
		$finalData['custom_attributes']['dealer_price'] = array(
			'type' => 'text',
			'value' => $finalData['price'] ? $finalData['price'] : 0
		);
//		echo '<pre>';print_r($finalData);exit;
		
		return $finalData;
	}

	public function checkConfigurable($saveData, $productData, $projectId, $type = '')
	{	
		if(isset($productData['tmp_attributes']['blocked']) && $productData['tmp_attributes']['blocked'] == 'true'){
			$saveData['product']['status'] = 2;
		} else {
			$saveData['product']['status'] = 1;
		}

		return $saveData;
	}
	
	public function loadCategories($finalArticleData, $article, $projectId)
	{
		if($article['ArtGroup'] != ''){
			$artGroups = $this->getAfasCategory($projectId, $article['ArtGroup']);
			ksort($artGroups);
			
			$finalCategories = array();
			$parentId = '';
			foreach($artGroups as $categoryName){
				$categoryId = $this->Cms_model->findCategory($projectId, $categoryName);
				if(!$categoryId){
					$categoryId = $this->Cms_model->createCategory($projectId, $categoryName, $parentId);
				}
				$finalCategories[] = $categoryId;
				$parentId = $categoryId;
			}
			$finalArticleData['categories_ids'] = implode(',', $finalCategories);
		}
		return $finalArticleData;
	}
	
	public function getAfasCategory($projectId, $artGroupId = '', $artGroupName = '', $categoryArray = array())
	{
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
		
		if($artGroupId != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Artikelgroep" OperatorType="1">'.$artGroupId.'</Field></Filter></Filters>';
		}
		if($artGroupName != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="description" OperatorType="1">'.$artGroupName.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_ArticleGroups_Magento";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_ArticleGroups_Magento) && count($data->Profit_ArticleGroups_Magento) > 0){
			$articleGroup = $this->Afas_model->xml2array($data->Profit_ArticleGroups_Magento);
			if(!empty($articleGroup)){
				if(isset($articleGroup['parent']) && $articleGroup['parent'] != ''){
					$level = $articleGroup['level'];
					$categoryArray[$level] = $articleGroup['description'];
					$categoryArray = $this->getAfasCategory($projectId, '', $articleGroup['parent'], $categoryArray);
				} else {
					$level = $articleGroup['level'];
					$categoryArray[$level] = $articleGroup['description'];
				}
				return $categoryArray;
			}
		}
		return $categoryArray;
	}

	public function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Magento2_model');
		
		$project = $this->db->get_where('projects', array('id' => 29))->row_array();
		// Check if enabled
		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			return;
		}
		
		// Send orders
		$lastExecution = $this->Projects_model->getValue('orders_last_execution_customcron', $project['id']);
		$interval = 5;
		$enabled = $this->Projects_model->getValue('orders_enabled', $project['id']);
		if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$orderAmount = 25;
			
			$orders = $this->getAfasOrders(29, 0, $orderAmount);
			if(isset($orders->Apicenter_Salesorders) && !empty($orders->Apicenter_Salesorders)){
				foreach($orders->Apicenter_Salesorders as $order){
					$orderNumber = (string)$order->OrderNumber;
					$orderStatus = (string)$order->Status;
					$magentoOrder = $this->getMagentoOrder(29, $orderNumber);
					if(($magentoOrder['state'] == 'processing' || $magentoOrder['state'] == 'pending_payment' || $magentoOrder['state'] == 'new') && $magentoOrder['status'] != 'verzonden'){
						// Change order status
						if($orderStatus == 'Geleverd'){
							$magentoOrder['status'] = 'verzonden';
							$this->updateMagentoOrderStatus($magentoOrder);
						} elseif($orderStatus == 'Deellevering' && $magentoOrder['status'] != 'deellevering'){
							$magentoOrder['status'] = 'deellevering';
							$this->updateMagentoOrderStatus($magentoOrder);
						}
// 						echo '<pre>';print_r($magentoOrder);exit;
					}
				}
				$this->Projects_model->saveValue('orders_last_execution_customcron', time(), $project['id']);
			}
		}
	}
	
	public function getMagentoOrder($projectId, $orderNumber){
    	$this->load->model('Projects_model');
    	$this->load->model('Magento2_model');
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->Magento2_model->getToken($projectId);
		
		$filterData = array(
			'search_criteria' => array(
				'filter_groups' => array(
					array(
						'filters' => array(
							array(
								'field' => 'increment_id',
								'value' => $orderNumber,
// 								'condition_type' => 'gteq'
							)
						)
					)
				),
				'page_size' => 1,
			)
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($storeUrl."/rest/V1/orders?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		 
		$result = curl_exec($ch);
		$orders = json_decode($result, true);
		if(isset($orders['items'][0])){
			return $orders['items'][0];
		}
		return false;
	}
	
	public function getAfasOrders($projectId, $offset=0, $amount=10){
    	$this->load->model('Projects_model');
		$afasEnvironment 	= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 	= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 			= $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl 		= $this->Projects_model->getValue('afas_get_url', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

		$dateFilter = date('Y-m-d', strtotime('-15 minutes')).'T'.date('H:i:s', strtotime('-15 minutes'));
// $dateFilter = '2019-02-14T00:00:00';
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrderNumber" OperatorType="6">000%</Field><Field FieldId="Gewijzigd_op" OperatorType="2">'.$dateFilter.'</Field></Filter></Filters>';
	 	
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] 		= $afasToken;
		$xml_array['connectorId'] 	= 'Apicenter_Salesorders';
		$xml_array['filtersXml'] 	= $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="Gewijzigd_op" OperatorType="0" /></Index></options>';

		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data 		= simplexml_load_string($resultData);
		return $data;
	}

	public function updateMagentoOrderStatus($order){
		$project = $this->db->get_where('projects', array('id' => 29))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->Magento2_model->getToken(29);
	
		$saveData = array(
			'entity' => array(
				'entity_id' => $order['entity_id'],
				'increment_id' => $order['increment_id'],
				'status' => $order['status']
			)
		);
		
		$saveData = json_encode($saveData);
	
		$ch = curl_init($storeUrl."/rest/V1/orders/");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		 
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		if(isset($result['status']) && $result['status'] == $order['status']){
			api2cart_log($this->projectId, 'exportorders', 'Updated order status for order #'.$order['id']);
		} else {
			api2cart_log($this->projectId, 'exportorders', 'Could not update order status for order #'.$order['id'].'. Result: '.print_r($result, true));
		}
		return $result;
	}
	
	public function getCustomerDataAfter($customerData, $afasCustomerData, $projectId){
    	$this->load->model('Projects_model');
		$afasEnvironment 	= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 	= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 			= $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl 		= $this->Projects_model->getValue('afas_get_url', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrgNumber" OperatorType="1">'.$afasCustomerData['BcCo'].'</Field><Field FieldId="Type" OperatorType="1">PRS</Field></Filter></Filters>';
	 	
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] 		= $afasToken;
		$xml_array['connectorId'] 	= 'Profit_Contacts_App';
		$xml_array['filtersXml'] 	= $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';

		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_Contacts_App) && count($data->Profit_Contacts_App) > 0){
			$contact = $data->Profit_Contacts_App;
			$name = (string)$contact->FullName;
			$firstName = (string)$contact->Voornaam;
			$lastName = (string)$contact->Achternaam;
			if($firstName != '' && $lastName){
				$customerData['first_name'] = $firstName;
				$customerData['last_name'] = $lastName;
				$customerData['company'] = $afasCustomerData['DebtorName'];
			}
// 			echo '<pre>';print_r($data);print_r($customerData);print_r($afasCustomerData);exit;
		}
		return $customerData;
	}


}