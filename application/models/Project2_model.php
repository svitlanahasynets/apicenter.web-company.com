<?php
class Project2_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 2;
    }
	
	public function getArticleData($articleData, $finalArticleData){
		$finalData = $finalArticleData;
		$finalData['is_configurable'] = isset($articleData['is_configurable']) ? $articleData['is_configurable'] : false;
		$finalData['configurable_article'] = isset($articleData['configurable_article']) ? $articleData['configurable_article'] : false;
		$finalData['configurable_attributes'][] = 'color';
		$finalData['configurable_attributes'][] = 'size';

		/////////////////////// Dropdown Text field
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '' && $articleData['AFAS_ATTRIBUUT_CODE'] != null)
		// {
			// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'dropdown',
				// 'value' => $articleData['AFAS_ATTRIBUUT_CODE']
			// );
		// }
		
		/////////////////////// Normal Text field
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '' && $articleData['AFAS_ATTRIBUUT_CODE'] != null)
		// {
			// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'text',
				// 'value' => $articleData['AFAS_ATTRIBUUT_CODE']
			// );
		// }
		/////////////////////// Datum veld
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '')
		// {
			// $date = explode('T', $articleData['AFAS_ATTRIBUUT_CODE']);
			// $date = $date[0];
			
			// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'text',
				// 'value' => $date
			// );
		// }
		////////////////////// Ja / Nee Veld
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '')
		// {
			// if($articleData['AFAS_ATTRIBUUT_CODE'] == "false")
			// {
				// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'text',
				// 'value' => 0
				// );
			// }
			// else
			// {
				// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'text',
				// 'value' => 1
				// );
			// }
		// }
				
		if(isset($articleData['Normeringen']) && $articleData['Normeringen'] != '' && $articleData['Normeringen'] != null)
		{
			$finalData['custom_attributes']['normeringen'] = array(
				'type' => 'text',
				'value' => $articleData['Normeringen']
			);
		}
		
		if(isset($articleData['Eigenschappen']) && $articleData['Eigenschappen'] != '' && $articleData['Eigenschappen'] != null)
		{
			$finalData['custom_attributes']['eigenschappen'] = array(
				'type' => 'text',
				'value' => $articleData['Eigenschappen']
			);
		}
		
		if(isset($articleData['Sluiting']) && $articleData['Sluiting'] != '' && $articleData['Sluiting'] != null)
		{
			$finalData['custom_attributes']['sluiting'] = array(
				'type' => 'text',
				'value' => $articleData['Sluiting']
			);
		}
		
		if(isset($articleData['Veilighiedszool']) && $articleData['Veilighiedszool'] != '' && $articleData['Veilighiedszool'] != null)
		{
			$finalData['custom_attributes']['veiligheidszool'] = array(
				'type' => 'text',
				'value' => $articleData['Veilighiedszool']
			);
		}
		
		if(isset($articleData['Neusbescherming']) && $articleData['Neusbescherming'] != '' && $articleData['Neusbescherming'] != null)
		{
			$finalData['custom_attributes']['neusbescherming'] = array(
				'type' => 'text',
				'value' => $articleData['Neusbescherming']
			);
		}

		if(isset($articleData['Maattabel']) && $articleData['Maattabel'] != '')
		{
			$finalData['custom_attributes']['maattabel'] = array(
			'type' => 'text',
			'value' => $articleData['Maattabel']
			);
		}
		##################Seizoenen
		if(isset($articleData['Lente']) && $articleData['Lente'] != '')
		{
			if($articleData['Lente'] == "false")
			{
				$finalData['custom_attributes']['lente'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['lente'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		if(isset($articleData['Zomer']) && $articleData['Zomer'] != '')
		{
			if($articleData['Zomer'] == "false")
			{
				$finalData['custom_attributes']['zomer'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['zomer'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		if(isset($articleData['Herfst']) && $articleData['Herfst'] != '')
		{
			if($articleData['Herfst'] == "false")
			{
				$finalData['custom_attributes']['herfst'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['herfst'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		if(isset($articleData['Winter']) && $articleData['Winter'] != '')
		{
			if($articleData['Winter'] == "false")
			{
				$finalData['custom_attributes']['winter'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['winter'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		if(isset($articleData['Levertijd']) && $articleData['Levertijd'] != '')
		{
			$finalData['custom_attributes']['levertijd'] = array(
			'type' => 'dropdown',
			'value' => $articleData['Levertijd']
			);
		}
		
		if(isset($articleData['size']) && $articleData['size'] != '' && $articleData['size'] != null && $articleData['is_configurable'] == true){
			$finalData['custom_attributes']['size'] = array(
				'type' => 'dropdown',
				'value' => isset($articleData['size']) ? $articleData['size'] : false
			);
		}
		if(isset($articleData['color']) && $articleData['color'] != '' && $articleData['color'] != null && $articleData['is_configurable'] == true){
			$finalData['custom_attributes']['color'] = array(
				'type' => 'dropdown',
				'value' => isset($articleData['color']) ? $articleData['color'] : false
			);
		}
		// if(isset($articleData['brand']) && $articleData['brand'] != '' && $articleData['brand'] != null){
			// $finalData['custom_attributes']['brand'] = array(
				// 'type' => 'dropdown',
				// 'value' => isset($articleData['brand']) ? $articleData['brand'] : false
			// );
		// }
		
		if(isset($articleData['brand']) && $articleData['brand'] != ''){
			$finalData['custom_attributes']['brand2'] = array(
				'type' => 'dropdown',
				'value' => $articleData['brand']
			);
		}
		
		//New arrival van
		if(isset($articleData['NewFrom']) && $articleData['NewFrom'] != '')
		{
			$date = explode('T', $articleData['NewFrom']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_from_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}
		
		//New arrival t/m
		if(isset($articleData['NewTo']) && $articleData['NewTo'] != '')
		{
			$date = explode('T', $articleData['NewTo']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_to_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}
		
		//Geslacht
		if(isset($articleData['Geslacht']) && $articleData['Geslacht'] != '' && $articleData['Geslacht'] != null)
		{
			$finalData['custom_attributes']['geslacht'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Geslacht']
			);
		}
		//Materiaal
		if(isset($articleData['Materiaal']) && $articleData['Materiaal'] != '' && $articleData['Materiaal'] != null)
		{
			$finalData['custom_attributes']['materiaal'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Materiaal']
			);
		}
		//Veiligheidsklasse
		if(isset($articleData['Veiligheidsklasse']) && $articleData['Veiligheidsklasse'] != '' && $articleData['Veiligheidsklasse'] != null)
		{
			$finalData['custom_attributes']['veiligheidsklasse'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Veiligheidsklasse']
			);
		}
		//Model
		if(isset($articleData['Model']) && $articleData['Model'] != '' && $articleData['Model'] != null)
		{
			$finalData['custom_attributes']['model'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Model']
			);
		}
		//Mouwlengte
		if(isset($articleData['Mouwlengte']) && $articleData['Mouwlengte'] != '' && $articleData['Mouwlengte'] != null)
		{
			$finalData['custom_attributes']['mouwlengte'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Mouwlengte']
			);
		}
		//Afvalverwerking
		if(isset($articleData['Afvalverwerking']) && $articleData['Afvalverwerking'] != '')
		{
			if($articleData['Afvalverwerking'] == "false")
			{
				$finalData['custom_attributes']['afvalverwerking'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['afvalverwerking'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Bouw
		if(isset($articleData['Bouw']) && $articleData['Bouw'] != '')
		{
			if($articleData['Bouw'] == "false")
			{
				$finalData['custom_attributes']['bouw'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['bouw'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Chemie
		if(isset($articleData['Chemie']) && $articleData['Chemie'] != '')
		{
			if($articleData['Chemie'] == "false")
			{
				$finalData['custom_attributes']['chemie'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['chemie'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Facilitair
		if(isset($articleData['Facilitair']) && $articleData['Facilitair'] != '')
		{
			if($articleData['Facilitair'] == "false")
			{
				$finalData['custom_attributes']['facilitair_beheer'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['facilitair_beheer'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Infra
		if(isset($articleData['Infra']) && $articleData['Infra'] != '')
		{
			if($articleData['Infra'] == "false")
			{
				$finalData['custom_attributes']['infra'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['infra'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Installatietechniek
		if(isset($articleData['Installatietechniek']) && $articleData['Installatietechniek'] != '')
		{
			if($articleData['Installatietechniek'] == "false")
			{
				$finalData['custom_attributes']['installatietechniek'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['installatietechniek'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Logistiek
		if(isset($articleData['Logistiek']) && $articleData['Logistiek'] != '')
		{
			if($articleData['Logistiek'] == "false")
			{
				$finalData['custom_attributes']['logistiek'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['logistiek'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Metaalindustrie
		if(isset($articleData['Metaalindustrie']) && $articleData['Metaalindustrie'] != '')
		{
			if($articleData['Metaalindustrie'] == "false")
			{
				$finalData['custom_attributes']['metaalindustrie'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['metaalindustrie'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Monteur
		if(isset($articleData['Monteur']) && $articleData['Monteur'] != '')
		{
			if($articleData['Monteur'] == "false")
			{
				$finalData['custom_attributes']['monteur'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['monteur'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Schilders
		if(isset($articleData['Schilders']) && $articleData['Schilders'] != '')
		{
			if($articleData['Schilders'] == "false")
			{
				$finalData['custom_attributes']['schilders'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['schilders'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Zorg
		if(isset($articleData['Zorg']) && $articleData['Zorg'] != '')
		{
			if($articleData['Zorg'] == "false")
			{
				$finalData['custom_attributes']['zorg'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['zorg'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Horeca
		if(isset($articleData['Horeca']) && $articleData['Horeca'] != '')
		{
			if($articleData['Horeca'] == "false")
			{
				$finalData['custom_attributes']['horeca'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['horeca'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Industrie
		if(isset($articleData['Industrie']) && $articleData['Industrie'] != '')
		{
			if($articleData['Industrie'] == "false")
			{
				$finalData['custom_attributes']['industrie'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['industrie'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		//Land__tuin__bosbouw
		if(isset($articleData['Land__tuin__bosbouw']) && $articleData['Land__tuin__bosbouw'] != '')
		{
			if($articleData['Land__tuin__bosbouw'] == "false")
			{
				$finalData['custom_attributes']['landbouw'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['landbouw'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		
		//EAN_code
		if(isset($articleData['EAN_code']) && $articleData['EAN_code'] != '' && $articleData['EAN_code'] != null){
			$finalData['custom_attributes']['ean'] = array(
				'type' => 'dropdown',
				'value' => $articleData['EAN_code']
			);
		}
		if(isset($articleData['EAN_code']) && $articleData['EAN_code'] != '' && $articleData['EAN_code'] != null){
			$finalData['custom_attributes']['itemcode_inkooprelatie'] = array(
				'type' => 'dropdown',
				'value' => $articleData['EAN_code']
			);
		}
		
		//Koppeling tussen InShop
		if(isset($articleData['Niet_in_shop']) && $articleData['Niet_in_shop'] != '')
		{
		    //log_message('debug', 'Werkkleding param: '. var_export($articleData, true));
        
			if($articleData['Niet_in_shop'] == 'true')
			{
    			$finalData['tmp']['status'] = 2;
    			//log_message('debug', 'Werkkleding TRUE: '. $articleData['Niet_in_shop']);
			} else {
				$finalData['tmp']['status'] = 1;
				//log_message('debug', 'Werkkleding FALSE: '. $articleData['Niet_in_shop']);
			}
		}
		
		
		
		
		
		
		$finalData['description'] = isset($articleData['Opmerking']) ? str_replace('|br|', '<br />', $articleData['Opmerking']) : $finalData['description'];
		
// 		echo '<pre>';print_r($finalData);exit;
		
		return $finalData;
	}
	
	public function checkConfigurable($saveData, $productData, $projectId, $type = ''){
		//http://devdocs.magento.com/guides/v2.2/rest/tutorials/configurable-product/define-config-product-options.html
		//https://www.zexperto.com/magento2x/magento2-create-configurable-product-via-rest-php
		
		// Configurable products
		if($productData['is_configurable'] == true && $productData['is_configurable'] != 'false'){
			$saveData['product']['type_id'] = 'configurable';
			
			$childProducts = $this->getChildProductsAfas($saveData, $projectId);
			$colorOptions = array();
			$sizeOptions = array();
			$childProductIds = array();
			foreach($childProducts as $childProduct){
				$productExists = $this->Magento2_model->checkProductExists($childProduct, $projectId);
				if($productExists != false && isset($productExists['items']) && !empty($productExists['items'])){
					$childProductIds[] = $productExists['items'][0]['id'];
					if($childProduct['color'] != ''){
						$attributeOptionId = $this->Magento2_model->createAttributeValue('color', $childProduct['color'], $projectId);
						if($attributeOptionId){
							$colorOptions[] = $attributeOptionId;
						}
					}
					if($childProduct['size'] != ''){
						$attributeOptionId = $this->Magento2_model->createAttributeValue('size', $childProduct['size'], $projectId);
						if($attributeOptionId){
							$sizeOptions[] = $attributeOptionId;
						}
					}
				}
			}
			$colorOptions = array_unique($colorOptions);
			$sizeOptions = array_unique($sizeOptions);
			
			$finalColorOptions = array();
			$finalSizeOptions = array();
			foreach($colorOptions as $colorOptionId){
				$finalColorOptions[] = array('value_index' => $colorOptionId);
			}
			foreach($sizeOptions as $sizeOptionId){
				$finalSizeOptions[] = array('value_index' => $sizeOptionId);
			}
			
			$configurableOptions = array();
			if(!empty($finalColorOptions)){
				$configurableOptions[] = array(
					'attribute_id' => 93,
					'label'=> 'Color',
					'position' => 0,
					'values' => $finalColorOptions
				);
			}
			if(!empty($finalSizeOptions)){
				$configurableOptions[] = array(
					'attribute_id' => 152,
					'label'=> 'Size',
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
		if(isset($productData['configurable_article']) && $productData['configurable_article'] != ''){
			$saveData['product']['visibility'] = 1;
		}
		
		$saveData['product']['custom_attributes'][] = array(
			'attribute_code' => 'cross_domain_url',
			'value' => 'http://werkkleding.web-company.nl/'.uniqid()
		);
		
		if(isset($productData['tmp']) && isset($productData['tmp']['status']))
		{
   			$saveData['product']['status'] = $productData['tmp']['status'];
		}
		unset($productData['tmp']);
		
		return $saveData;
	}
	
	public function getChildProductsAfas($saveData, $projectId){
		$mainProductSku = $saveData['product']['sku'];

		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);

		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="configurable_article" OperatorType="1">'.$mainProductSku.'</Field></Filter></Filters>';
		
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
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		$childProducts = array();
		$numberOfResults = count($data->$afasArticleConnector);
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$results = array();
			foreach($data->$afasArticleConnector as $article){
				$childProducts[] = array(
					'model' => (string)$article->ItemCode,
					'color' => (string)$article->color,
					'size' => (string)$article->size
				);
			}
		}
		return $childProducts;
	}
	
	public function loadCategories($finalArticleData, $article, $projectId){
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
	
	public function getAfasCategory($projectId, $artGroupId = '', $artGroupName = '', $categoryArray = array()){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
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
	
	public function setOrderParams($fields, $orderData){
		//log_message('debug', 'Werkkleding Order param: '. var_export($fields, true));
		//'OrNu' => 'WK100000558',
		
		$fields->SaCh = 2;

		// Werkkleding.nl: JA
		$fields->U481D206042BF478B35CE3CB4F4FF9020 = 1;
		
		
		if($orderData['totals']['amount_paid'] >= $orderData['totals']['total']){
			$fields->PaTp = '04';
			$fields->PaCd = 'Web';
		} else {
			$fields->PaTp = '00';
			$fields->PaCd = '30';
		}
	}
	
	public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
	
	    if ($ordernumber != "")
	    {
    	    if(stristr($ordernumber, 'WK') === FALSE) 
    	    {
    	        //WK niet gevonden dus metaalunie
    	        $fields->U5A1E6303488EECD696665EBE77FF1134 = 1;
    	        $fields->U4F8341D44B8CCD4DAD22389E2F196A6D = 0;
    	    }
    	    else
    	    {
    	        $fields->U5A1E6303488EECD696665EBE77FF1134 = 0;
    	        $fields->U4F8341D44B8CCD4DAD22389E2F196A6D = 1;
    	    }
    	}

	    // Werkkleding.nl: JA
		//$fields->U4F8341D44B8CCD4DAD22389E2F196A6D = 1;
		//Metaalunie: JA
		//$fields->U5A1E6303488EECD696665EBE77FF1134 = 1;

		$fields->VeId = 50;
		$fields->DsId = 2;
		$fields->PfId = 'Werkkleding.nl';
		log_message('debug', 'Werkkleding customer param: ' . $ordernumber . ' --> ' . var_export($fields));
	}
	
	
	public function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Magento2_model');
		
		$project = $this->db->get_where('projects', array('id' => 2))->row_array();
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
			
			$orders = $this->Cms_model->getOrders($project['id'], 0, $orderAmount, 'desc');
			$orders = isset($orders['orders']) ? $orders['orders'] : array();
// 			echo '<pre>';print_r($orders);exit;

			if($orders != false && !empty($orders)){				
				$this->Projects_model->saveValue('orders_last_execution_customcron', time(), $project['id']);
				
				foreach($orders as $order){
					// Check if AFAS order already exists
					if(($order['state'] == 'processing' || $order['status'] == 'bankoverschrijving') && $order['status'] != 'exportafas'){
						$result = $this->Afas_model->sendOrder($project['id'], $order);
						if($result == true){
							$order['status'] = 'exportafas';
							$this->updateMagentoOrderStatus($order);
						}
					}
				}
			}
		}
	}
	
	public function afterOrderSubmit($order){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Magento2_model');
		if(($order['state'] == 'processing' || $order['status'] == 'bankoverschrijving') && $order['status'] != 'exportafas'){
			$order['status'] = 'exportafas';
			$this->updateMagentoOrderStatus($order);
		}
	}
	
	public function loadCustomOrderAttributes($appendItem, $order, $projectId){
		if(($order['state'] != 'processing' && $order['status'] != 'bankoverschrijving') || $order['status'] == 'exportafas'){
			return false;
		}
		return $appendItem;
	}
	
	public function updateMagentoOrderStatus($order){
		$project = $this->db->get_where('projects', array('id' => $this->projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->Magento2_model->getToken($this->projectId);
	
		$saveData = array(
			'entity' => array(
				'entity_id' => $order['order_id'],
				'increment_id' => $order['id'],
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
}