<?php
class Project131_model extends CI_Model
{

	public $projectId;
	public $boolGuest;

	function __construct()
	{
		parent::__construct();
		$this->projectId = 131;
		$this->boolGuest = -1;
	}

	public function getArticleData($articleData, $finalArticleData)
	{
		$finalData = $finalArticleData;
		$finalData['is_configurable'] = isset($articleData['is_configurable']) ? $articleData['is_configurable'] : false;
		$finalData['configurable_article'] = isset($articleData['configurable_article']) ? $articleData['configurable_article'] : false;

		//The variable on which the configurable is based:
		$finalData['configurable_attributes'][] = 'size';


		if (isset($articleData['size']) && $articleData['size'] != '' && $articleData['size'] != null && $articleData['is_configurable'] == true) {
			$finalData['custom_attributes']['size'] = array(
				'type' => 'dropdown',
				'value' => isset($articleData['size']) ? $articleData['size'] : false
			);
		}

		//// Length (mm)
		if (isset($articleData['length']) && $articleData['length'] != '' && $articleData['length'] != null) {
			$finalData['custom_attributes']['btb_length'] = array(
				'type' => 'text',
				'value' => $articleData['length']
			);
		}

		//// Width (mm)
		if (isset($articleData['width']) && $articleData['width'] != '' && $articleData['width'] != null) {
			$finalData['custom_attributes']['btb_width'] = array(
				'type' => 'text',
				'value' => $articleData['width']
			);
		}
		//// Geslacht
		if (isset($articleData['gender']) && $articleData['gender'] != '' && $articleData['gender'] != null) {
			$finalData['custom_attributes']['gender'] = array(
				'type' => 'dropdown',
				'value' => $articleData['gender']
			);
		}

		//// Material
		if (isset($articleData['material']) && $articleData['material'] != '' && $articleData['material'] != null) {
			$finalData['custom_attributes']['btb_material'] = array(
				'type' => 'dropdown',
				'value' => $articleData['material']
			);
		}

		//// Kleur
		if (isset($articleData['colour']) && $articleData['colour'] != '' && $articleData['colour'] != null) {
			$finalData['custom_attributes']['color'] = array(
				'type' => 'dropdown',
				'value' => $articleData['colour']
			);
		}

		//// Gewicht
		if (isset($articleData['Nettogewicht']) && $articleData['Nettogewicht'] != '' && $articleData['Nettogewicht'] != null) {
			$finalData['custom_attributes']['weight'] = array(
				'type' => 'text',
				'value' => $articleData['Nettogewicht']
			);
		}

		//// Slot
		if (isset($articleData['lock']) && $articleData['lock'] != '' && $articleData['lock'] != null) {
			$finalData['custom_attributes']['btb_lock'] = array(
				'type' => 'dropdown',
				'value' => $articleData['lock']
			);
		}

		//// Collectie
		if (isset($articleData['collection']) && $articleData['collection'] != '' && $articleData['collection'] != null) {
			$finalData['custom_attributes']['btb_collection'] = array(
				'type' => 'dropdown',
				'value' => $articleData['collection']
			);
		}
        
        //size_table
        if (isset($articleData['size_table']) && $articleData['size_table'] != '' && $articleData['size_table'] != null) {
			$finalData['custom_attributes']['btb_size_table'] = array(
				'type' => 'dropdown',
				'value' => $articleData['size_table']
			);
		}
        
        
        //MetaTitleDefault
        if (isset($articleData['MetaTitleDefault']) && $articleData['MetaTitleDefault'] != '' && $articleData['MetaTitleDefault'] != null) {
			$finalData['custom_attributes']['meta_title'] = array(
				'type' => 'text',
				'value' => $articleData['MetaTitleDefault']
			);
		}
		
		//MetaTitleDefault --> Keywords
        if (isset($articleData['MetaTitleDefault']) && $articleData['MetaTitleDefault'] != '' && $articleData['MetaTitleDefault'] != null) {
			$finalData['custom_attributes']['meta_keyword'] = array(
				'type' => 'text',
				'value' => $articleData['MetaTitleDefault']
			);
		}
		
		//MetaDescDefault
		if (isset($articleData['MetaDescDefault']) && $articleData['MetaDescDefault'] != '' && $articleData['MetaDescDefault'] != null) {
			$finalData['custom_attributes']['meta_description'] = array(
				'type' => 'text',
				'value' => $articleData['MetaDescDefault']
			);
		}
        
		$multiplStores = $this->Projects_model->getMultiStores($this->projectId);

		if (count($multiplStores)) {
			$finalData['multilanguage_attributes'] = $this->getMultipleArticlesData($multiplStores, $finalData);
		}
        
        $price = $this->getItemPrice($articleData['ItemCode']);
		$finalData['price'] = $price;
		
		//$qty = $this->getItemQty($articleData['ItemCode']);
		//$finalData['quantity'] = $qty;
		
		
		return $finalData;
	}

	public function checkConfigurable($saveData, $productData, $projectId, $type = '')
	{
		// Configurable products
		if ($productData['is_configurable'] == true && $productData['is_configurable'] != 'false') {
			$saveData['product']['type_id'] = 'configurable';

			$childProducts = $this->getChildProductsAfas($saveData, $projectId);

			$sizeOptions = array();
			$childProductIds = array();
			foreach ($childProducts as $childProduct) {
				$productExists = $this->Magento2_model->checkProductExists($childProduct, $projectId);
				if ($productExists != false && isset($productExists['items']) && !empty($productExists['items'])) {

					$childProductIds[] = $productExists['items'][0]['id'];

					if ($childProduct['size'] != '') {
						$attributeOptionId = $this->Magento2_model->createAttributeValue('size', $childProduct['size'], $projectId);
						if ($attributeOptionId) {
							$sizeOptions[] = $attributeOptionId;
						}
					}
				}
			}

			$sizeOptions = array_unique($sizeOptions);
			$finalSizeOptions = array();

			foreach ($sizeOptions as $sizeOptionId) {
				$finalSizeOptions[] = array('value_index' => $sizeOptionId);
			}

			$configurableOptions = array();
			if (!empty($finalSizeOptions)) {
				$configurableOptions[] = array(
					'attribute_id' => 187,
					'label' => 'Size',
					'position' => 0,
					'values' => $finalSizeOptions
				);
			}
			$saveData['product']['extension_attributes']['configurable_product_options'] = $configurableOptions;
            
            // Force in stock
			foreach($saveData['product']['custom_attributes'] as $index => $customAttribute){
				if($customAttribute['attribute_code'] == 'quantity_and_stock_status'){
					$saveData['product']['custom_attributes'][$index] = array(
						'attribute_code' => 'quantity_and_stock_status',
						'value' => array(
							'qty' => 0,
							'is_in_stock' => 1
						)
					);
				}
			}
			$saveData['product']['extension_attributes']['stock_item'] = array(
				'qty' => 0,
				'is_in_stock' => true
			);
            
			$saveData['product']['extension_attributes']['configurable_product_links'] = $childProductIds;
		} else {
			$saveData['product']['type_id'] = 'simple';
		}

		// Set visibility for child products
		if (isset($productData['configurable_article']) && $productData['configurable_article'] != '') {
			$saveData['product']['visibility'] = 1;
		}
		
		
		

		return $saveData;
	}

	public function getChildProductsAfas($saveData, $projectId)
	{
		$mainProductSku = $saveData['product']['sku'];

		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);

		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="configurable_article" OperatorType="1">' . $mainProductSku . '</Field></Filter></Filters>';

		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1000</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';

		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s', '', $resultData);

		$data = simplexml_load_string($resultData);
		$childProducts = array();
		$numberOfResults = count($data->$afasArticleConnector);
		if (isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0) {
			$results = array();
			foreach ($data->$afasArticleConnector as $article) {
				$childProducts[] = array(
					'model' => (string) $article->ItemCode,
					'size' => (string) $article->size
				);
			}
		}
		return $childProducts;
	}

	public function loadCategories($finalArticleData, $article, $projectId)
	{

		if (isset($article['ArtGroup']) && 	$article['ArtGroup'] != '' && $article['ArtGroup'] != NULL) {

			if (!strpos($article['ArtGroup'], ";", 0) === false) {
				$cats = explode(';', $article['ArtGroup']);
				$finalArticleData['categories_ids'] = implode(',', $cats);
			}
			else{
			    $finalArticleData['categories_ids'] = $article['ArtGroup'];
			}
		} else {
			$finalArticleData['categories_ids'] = 94;
		}
        
        //log_message('debug', 'CMS,Category Logic '. var_export($finalArticleData['categories_ids'], true));
        
		return $finalArticleData;
	}

	public function getMultipleArticlesData($multiplStores, $finalArticleData)
	{
		$finalData   = [];

		foreach ($multiplStores as $store) {
			$articleData = $this->Afas_model->getMultiLanguageArticles($this->projectId, $store['code'], $finalArticleData['model']);

			if ($articleData === false) {
				continue;
			}

			/* DE  start*/
			if (isset($articleData['Description_DE']) && $articleData['Description_DE'] != '' && $articleData['Description_DE'] != null) {
				$finalData[$store['code']]['name'] = array(
					'type' => 'text',
					'value' => $articleData['Description_DE']
				);
			}

			if (isset($articleData['ShortDescription_DE']) && $articleData['ShortDescription_DE'] != '' && $articleData['ShortDescription_DE'] != null) {
				$finalData[$store['code']]['short_description'] = array(
					'type' => 'text',
					'value' => $articleData['ShortDescription_DE']
				);
			}

			if (isset($articleData['LDescription_DE']) && $articleData['LDescription_DE'] != '' && $articleData['LDescription_DE'] != null) {
				$finalData[$store['code']]['description'] = array(
					'type' => 'text',
					'value' => $articleData['LDescription_DE']
				);
			}

			if (isset($articleData['Meta_Title_DE']) && $articleData['Meta_Title_DE'] != '' && $articleData['Meta_Title_DE'] != null) {
				$finalData[$store['code']]['meta_title'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_DE']
				);
			}

            if (isset($articleData['Meta_Title_DE']) && $articleData['Meta_Title_DE'] != '' && $articleData['Meta_Title_DE'] != null) {
				$finalData[$store['code']]['meta_keyword'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_DE']
				);
			}
            
			if (isset($articleData['Meta_Description_DE']) && $articleData['Meta_Description_DE'] != '' && $articleData['Meta_Description_DE'] != null) {
				$finalData[$store['code']]['meta_description'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Description_DE']
				);
			}
			/* DE  end*/

			/* NL start*/
			if (isset($articleData['Meta_Title_NL']) && $articleData['Meta_Title_NL'] != '' && $articleData['Meta_Title_NL'] != null) {
				$finalData[$store['code']]['meta_title'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_NL']
				);
			}
			
			if (isset($articleData['Meta_Title_NL']) && $articleData['Meta_Title_NL'] != '' && $articleData['Meta_Title_NL'] != null) {
				$finalData[$store['code']]['meta_keyword'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_NL']
				);
			}


			if (isset($articleData['Meta_Description_NL']) && $articleData['Meta_Description_NL'] != '' && $articleData['Meta_Description_NL'] != null) {
				$finalData[$store['code']]['meta_description'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Description_NL']
				);
			}

			if (isset($articleData['Description_NL']) && $articleData['Description_NL'] != '' && $articleData['Description_NL'] != null) {
				$finalData[$store['code']]['name'] = array(
					'type' => 'text',
					'value' => $articleData['Description_NL']
				);
			}

			if (isset($articleData['ShortDescription_NL']) && $articleData['ShortDescription_NL'] != '' && $articleData['ShortDescription_NL'] != null) {
				$finalData[$store['code']]['short_description'] = array(
					'type' => 'text',
					'value' => $articleData['ShortDescription_NL']
				);
			}

			if (isset($articleData['LDescription_NL']) && $articleData['LDescription_NL'] != '' && $articleData['LDescription_NL'] != null) {
				$finalData[$store['code']]['description'] = array(
					'type' => 'text',
					'value' => $articleData['LDescription_NL']
				);
			}
			/* NL end*/

			/* EN start*/
			if (isset($articleData['Meta_Title_EN']) && $articleData['Meta_Title_EN'] != '' && $articleData['Meta_Title_EN'] != null) {
				$finalData[$store['code']]['meta_title'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_EN']
				);
			}
			
			if (isset($articleData['Meta_Title_EN']) && $articleData['Meta_Title_EN'] != '' && $articleData['Meta_Title_EN'] != null) {
				$finalData[$store['code']]['meta_keyword'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Title_EN']
				);
			}

			if (isset($articleData['Meta_Description_EN']) && $articleData['Meta_Description_EN'] != '' && $articleData['Meta_Description_EN'] != null) {
				$finalData[$store['code']]['meta_description'] = array(
					'type' => 'text',
					'value' => $articleData['Meta_Description_EN']
				);
			}

			if (isset($articleData['Description_EN']) && $articleData['Description_EN'] != '' && $articleData['Description_EN'] != null) {
				$finalData[$store['code']]['name'] = array(
					'type' => 'text',
					'value' => $articleData['Description_EN']
				);
			}

			if (isset($articleData['ShortDescription_EN']) && $articleData['ShortDescription_EN'] != '' && $articleData['ShortDescription_EN'] != null) {
				$finalData[$store['code']]['short_description'] = array(
					'type' => 'text',
					'value' => $articleData['ShortDescription_EN']
				);
			}

			if (isset($articleData['LDescription_EN']) && $articleData['LDescription_EN'] != '' && $articleData['LDescription_EN'] != null) {
			 	$finalData[$store['code']]['description'] = array(
			 		'type' => 'text',
			 		'value' => $articleData['LDescription_EN']
			 	);
			}
			/* EN end*/

			/* Base attributes start*/
			/*
			if (isset($articleData['size']) && $articleData['size'] != '' && $articleData['size'] != null && $articleData['is_configurable'] == true) {
				$finalData[$store['code']]['size'] = array(
					'type' => 'dropdown',
					'value' => isset($articleData['size']) ? $articleData['size'] : false
				);
			}

			if (isset($articleData['length']) && $articleData['length'] != '' && $articleData['length'] != null) {
				$finalData[$store['code']]['btb_length'] = array(
					'type' => 'text',
					'value' => $articleData['length']
				);
			}

			if (isset($articleData['width']) && $articleData['width'] != '' && $articleData['width'] != null) {
				$finalData[$store['code']]['btb_width'] = array(
					'type' => 'text',
					'value' => $articleData['width']
				);
			}

			if (isset($articleData['gender']) && $articleData['gender'] != '' && $articleData['gender'] != null) {
				$finalData[$store['code']]['gender'] = array(
					'type' => 'dropdown',
					'value' => $articleData['gender']
				);
			}

			if (isset($articleData['material']) && $articleData['material'] != '' && $articleData['material'] != null) {
				$finalData[$store['code']]['btb_material'] = array(
					'type' => 'dropdown',
					'value' => $articleData['material']
				);
			}

			if (isset($articleData['colour']) && $articleData['colour'] != '' && $articleData['colour'] != null) {
				$finalData[$store['code']]['color'] = array(
					'type' => 'dropdown',
					'value' => $articleData['colour']
				);
			}

			if (isset($articleData['Nettogewicht']) && $articleData['Nettogewicht'] != '' && $articleData['Nettogewicht'] != null) {
				$finalData[$store['code']]['weight'] = array(
					'type' => 'text',
					'value' => $articleData['Nettogewicht']
				);
			}

			if (isset($articleData['lock']) && $articleData['lock'] != '' && $articleData['lock'] != null) {
				$finalData[$store['code']]['btb_lock'] = array(
					'type' => 'dropdown',
					'value' => $articleData['lock']
				);
			}

			if (isset($articleData['collection']) && $articleData['collection'] != '' && $articleData['collection'] != null) {
				$finalData[$store['code']]['btb_collection'] = array(
					'type' => 'dropdown',
					'value' => $articleData['collection']
				);
			}
*/
			$finalData[$store['code']]['store_id'] = $store['value'];
/*
			$finalData[$store['code']]['is_configurable'] = false;

			if (isset($articleData['is_configurable'])) {
				$finalData[$store['code']]['is_configurable'] = $articleData['is_configurable'];
			}

			if (isset($articleData['configur'])) {
				$finalData[$store['code']]['is_configurable'] = $articleData['configur'];
			}
            */
			/* Base attributes end*/


			// foreach ($data as $key=>$value) {
			//     if (isset($data[$key]) && $data[$key] !== null) {
			//         $type = $this->Afas_model->getAttributesType($this->projectId, $key, $store['code']);
			//         $finalArticleData['multilanguage_data'][$store['code']][$key] = $data[$key];
			//     }
			// }
		}

		return $finalData;
	}

	public function setOrderParams($fields, $orderData) {
	    
	    log_message('debug', 'Orders 131-Param' . var_export($orderData, true));
	    
	    
		if (!isset($orderData["payment_method"])) return false;
		$fields->UC7DDBB6843349A5A75E94A9622AA1F33 = 0;

		if ($orderData["payment_method"] == 'paypal_express' || $orderData["payment_method"] == 'braintree_cc_vault') {
			$fields->UC7DDBB6843349A5A75E94A9622AA1F33 = 1;
		}
		
		//Betaalwijze variable
		//$fields->PaTp = 
		
		//Rapport
		$fields->PrLa = "B36123924356721F658840BD6D58626D";
		//$fields->PrLa = "Verkooppakbon rapport BTB - Webshop 2019";
		
		
		
		$fields->RfCs = "Webshop order #" . $orderData['id'];
	}

	public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
	    
	    if ($this->boolGuest == 0){
	        $fields->U59652AF541A2EA4ADFAF4C97C91578D8 = 1;
	    }
	    else if ($this->boolGuest == 1) {
	        $fields->U59652AF541A2EA4ADFAF4C97C91578D8 = 0;
	    }
	    
	    $fields->UECD0D6D645F76E82AC9C3AA945C5049A = 'w';
	    $fields->PfId = 'Webshop';
	    $fields->PrLs = 'web';
	    $fields->VeId = 'Webshop';
	    $fields->EmId = '2667';
	    $fields->U53FB1A754DE19917846A0B807C526310 = 'WS';
	    $fields->InPv = 'E';
	    
	    
	    log_message('debug', 'Orders 131-Guest: ' . $this->boolGuest . ' WS: ' . $fields->UECD0D6D645F76E82AC9C3AA945C5049A);
	}

	//public function customCronjob(){
	//}

	//public function afterOrderSubmit($order){
	//}

	public function loadCustomOrderAttributes($appendItem, $order, $projectId){
	    
		log_message('debug', 'Orders 131-customOrder' . var_dump($order, true));
		if ($order['customer_is_guest']) {
			$this->boolGuest = 1;
		} else {
			$this->boolGuest = 0;
		}

		return $appendItem;
	    //If order = guest order {
	        //$this->boolGuest = 1;
	    //}
	    //else {
	        //$this->boolGuest = 0;
	    //}
	}

	
	public function getItemPrice($itemCode){
		$projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Itemcode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Verkoopprijs_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
        
		$data = simplexml_load_string($resultData);
		if(isset($data->Verkoopprijs_App) && count($data->Verkoopprijs_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Verkoopprijs_App);
			if(!empty($itemData)){
				return floatval($itemData['Consumentenprijs']);
			}
		}
		return 0;
	}

    /*
    public function getItemQty($itemCode){
		$projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_Stock_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];*/
		//$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
        /*
		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_Stock_App) && count($data->Profit_Stock_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Profit_Stock_App);
			if(!empty($itemData)){
				return intval($itemData['StockActual']);
			}
		}
		return 0;
	}
	*/
    
    
	//public function updateMagentoOrderStatus($order){
	//}

	//public function getMagentoOrder($orderNumber){
	//}

	//public function createOrderInvoice($orderData){
	//}

	//public function createOrderShipment($orderData){
	//}
}