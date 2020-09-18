<?php
class Project1_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 1;
    }
	
	public function getArticleData($articleData, $finalArticleData){
		$finalData = $finalArticleData;
		$finalData['is_configurable'] = isset($articleData['is_configurable']) ? $articleData['is_configurable'] : false;
		$finalData['configurable_article'] = isset($articleData['configurable_article']) ? $articleData['configurable_article'] : false;
		$finalData['configurable_attributes'] = array(
			'size',
			'color'
		);
		$finalData['custom_attributes'] = array(
			'size' => array(
				'type' => 'dropdown',
				'value' => isset($articleData['size']) ? $articleData['size'] : false
			),
/*
			'brand' => array(
				'type' => 'text',
				'value' => $articleData['brand']
			),
*/
			'color' => array(
				'type' => 'dropdown',
				'value' => isset($articleData['color']) ? $articleData['color'] : false
			)
		);
		$finalData['description'] = isset($articleData['Opmerking']) ? str_replace('|br|', '<br />', $articleData['Opmerking']) : $finalData['description'];
		
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
					$attributeOptionId = $this->Magento2_model->createAttributeValue('color', $childProduct['color'], $projectId);
					if($attributeOptionId){
						$colorOptions[] = $attributeOptionId;
					}
					$attributeOptionId = $this->Magento2_model->createAttributeValue('size', $childProduct['size'], $projectId);
					if($attributeOptionId){
						$sizeOptions[] = $attributeOptionId;
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
			
			$saveData['product']['extension_attributes']['configurable_product_options'] = array(
				array(
					'attribute_id' => 93,
					'label'=> 'Color',
					'position' => 0,
					'values' => $finalColorOptions
				),
				array(
					'attribute_id' => 141,
					'label'=> 'Size',
					'position' => 0,
					'values' => $finalSizeOptions
				),
			);
			
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
		}
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
		$resultData = str_replace("\n", '|br|', $resultData);
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
		$fields->SaCh = 2;
		if($orderData['totals']['amount_paid'] >= $orderData['totals']['total']){
			$fields->PaTp = '04';
			$fields->PaCd = 'Web';
		} else {
			$fields->PaTp = '00';
			$fields->PaCd = '30';
		}
	}
}