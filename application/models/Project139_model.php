<?php
class Project139_model extends CI_Model {

	public $projectId;

    public function __construct()
    {
        parent::__construct();
        $this->projectId = 139;
    }

	public function xml2array( $xmlObject, $out = array () ){
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	}

	public function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Cms_model');
		$this->load->model('Lightspeed_model');
		$projectId = $this->projectId;
        
        log_message('debug', "Custom 139 start " . var_export($projectId, true));
        
		// Check if enabled
		//if($this->Projects_model->getValue('enabled', $projectId) != '1'){
		//	return;
		//}
		
		log_message('debug', "Custom 139 end ". var_export($projectId, true));
		
		$this->processArticles();
		$this->processImportArticleImages();
		$this->processOrderStatus();
		$this->processLightspeedReturns();
		$this->processReturnStatus();
		$this->processLightspeedInvoices();
	}
	
	public function processArticles(){
	    
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processArticles = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processArticles ' . $METRIC_starttime_processArticles);
	    
		
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();

		$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
		$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
/*
$currentArticleOffset = 0;
$articleAmount = 1;
*/
		$result = $this->getArticles($project['id'], $currentArticleOffset, $articleAmount);
		if ($result['numberOfResults']>0) {
			$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
			$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
		}
		$articles = $result['results'];
							
		//$removeArticles = $result['removeResults'];
		//if(!empty($removeArticles)){
		//	$this->Cms_model->removeArticles($project['id'], $removeArticles);
		//}
		
		if($result['numberOfResults'] < 1){
			$this->Projects_model->saveValue('article_offset', 0, $project['id']);
			$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $project['id']);
			
			if($lastUpdateDate != '' && $lastUpdateDate != ' '){
				$this->Projects_model->saveValue('afas_last_update_date', date('Y-m-d'), $project['id']);
			}
		}
		
		//log_message('debug', 'ProductData 139AFAS - XML ' . var_export($articles, true));
		
		$processedMainSkus = array();
		$processedSkus = array();
		foreach($articles as $article){
			$firstArticle = $article[0];
			if(in_array($firstArticle['sku'], $processedMainSkus)){
				continue;
			}
			$processedMainSkus[] = $firstArticle['sku'];
			$this->Cms_model->updateArticles($projectId, array($firstArticle));
			if(count($article) > 0){
				$product = $this->Lightspeed_model->checkProductExists($firstArticle, $projectId);
				if(isset($product[0]) && isset($product[0]['id'])){
					$productId = $product[0]['product']['resource']['id'];
					//unset($article[0]);
					foreach($article as $variant){
						if(in_array($variant['sku'], $processedSkus)){
							continue;
						}
						$processedSkus[] = $variant['sku'];
						$variantProduct = $this->Lightspeed_model->checkProductExists(array('model' => $variant['sku']), $projectId);
						$saveData = array(
							'sku' => $variant['sku'],
							'ean' => $variant['sku'],
							'articleCode' => $variant['sku'],
							'title' => $variant['size'], //$variant['name'],
							'fulltitle' => $variant['fulltitle'],
							'description' => '',
							'content' => $variant['description'],
							'priceExcl' => $variant['price'] ? $variant['price'] : 0,
							'unitUnit' => 'Piece',
							'isVisible' => false,
							'visibility' => 'hidden',
							'stockLevel' => isset($variant['quantity']) ? $variant['quantity'] : 0,
							'sortOrder' => $variant['sortOrder']
						);
						$client = $this->Lightspeed_model->getClient($projectId);
						if(empty($variantProduct)){
							// Now create variant
							$saveData['product'] = $productId;
							$saveData['isDefault'] = false;
							$result = $client->variants->create($saveData);
						} else {
							$variantId = isset($variantProduct[0]) ? $variantProduct[0]['id'] : null;
							// Now update variant
							$saveData['product'] = $productResult['id'];
							$saveData['isDefault'] = true;
							$result = $client->variants->update($variantId, $saveData);

						}
					}
				}
			}
		}
	}
	
	public function loadCategories($finalArticleData, $article, $projectId){
		$this->load->model('Cms_model');
		$parentId = '';
		$finalCategories = array();
		if(isset($article['MainArtGroup']) && $article['MainArtGroup'] != ''){
			// Main category
			$storeCategory = $this->Cms_model->findCategory($projectId, (string)$article['MainArtGroup']);
			if(!$storeCategory){
				$storeCategory = $this->Cms_model->createCategory($projectId, (string)$article['MainArtGroup']);
			}
			$parentId = $storeCategory;
			$finalCategories[] = $storeCategory;
		}
		if(isset($article['ArtGroup']) && $article['ArtGroup'] != ''){
			// Sub category
			$storeCategory = $this->Cms_model->findCategory($projectId, (string)$article['ArtGroup'], $parentId);
			if(!$storeCategory){
				$storeCategory = $this->Cms_model->createCategory($projectId, (string)$article['ArtGroup'], $parentId);
			}
			$parentId = $storeCategory;
			$finalCategories[] = $storeCategory;
		}
		if(isset($article['Brand']) && $article['Brand'] != ''){
			// Brand main category
			$storeCategory = $this->Cms_model->findCategory($projectId, (string)$article['Brand']);
			if(!$storeCategory){
// 				$storeCategory = $this->Cms_model->createCategory($projectId, (string)$article['Brand'], $parentId);
				$storeCategory = $this->Cms_model->createCategory($projectId, (string)$article['Brand']);
			}
			$parentId = $storeCategory;
			$finalCategories[] = $storeCategory;
		}
		if(isset($article['ArtGroup']) && $article['ArtGroup'] != ''){
			// Brand sub category
			$storeCategory = $this->Cms_model->findCategory($projectId, (string)$article['ArtGroup'], $parentId);
			if(!$storeCategory){
				$storeCategory = $this->Cms_model->createCategory($projectId, (string)$article['ArtGroup'], $parentId);
			}
			$parentId = $storeCategory;
			$finalCategories[] = $storeCategory;
		}
		$finalArticleData['categories_ids'] = implode(',', array_filter(array_unique($finalCategories)));
		return $finalArticleData;
	}

	public function getArticles($projectId, $offset = 0, $amount = 10){
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
		$filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);
		
		$filtersXML = '';
		if($filterEnabled == '1'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
		}
		$indexXml = '<Index><Field FieldId="ItemCode" OperatorType="1" /></Index>';
		if($lastUpdateDate != '' && $lastUpdateDate != ' '){
			$lastUpdateDateFilter = $lastUpdateDate.'T00:00:00';
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field></Filter></Filters>';
			if($filterEnabled == '1'){
				$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
			}
		}
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
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$data = simplexml_load_string($resultData);

		$numberOfResults = count($data->$afasArticleConnector);
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$results = array();
			$removeResults = array();
			foreach($data->$afasArticleConnector as $article){
				$article = $this->xml2array($article);
				if(isset($article['enabled']) && ($article['enabled'] == false || $article['enabled'] == 'false')){
					$removeResults[] = $article['ItemCode'];
					continue;
				}

				$finalArticleData = array();
				if($article['Blocked'] != 'false'){
					$finalArticleData['available_for_view'] = 'false';
					$finalArticleData['available_for_sale'] = 'false';
				}
				$finalArticleData['model'] = $article['ItemCode'];
				$finalArticleData['name'] = (string)$article['Description'];
				if(isset($article['ExtraDescription'])){
					$finalArticleData['description'] = str_replace('|br|', '<br />', (string)$article['ExtraDescription']);
				} else {
					$finalArticleData['description'] = str_replace('|br|', '<br />', (string)$article['Description']);
				}
				$finalArticleData['description'] = ' ';
				$finalArticleData['fulltitle'] = (string)$article['Description'];
				$finalArticleData['tax_class_id'] = $article['VATgroup'];
				$finalArticleData['price'] = isset($article['BasicSalesPrice']) ? $article['BasicSalesPrice'] : '';
				if(isset($article['StockActual'])){
					$finalArticleData['quantity'] = $article['StockActual'];
				}
				if(isset($article['Brand'])){
					$finalArticleData['brand'] = $article['Brand'];
				}
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'loadCategories')){
						$finalArticleData = $this->$projectModel->loadCategories($finalArticleData, $article, $projectId);
					}
				}

				// Load mapped attributes data
				$finalArticleData = $this->Cms_model->applyMappedAttributes($projectId, $article, $finalArticleData);
				
				// Load project specific data
				$finalArticleData = $this->getArticleData($article, $finalArticleData);

				$results[] = $finalArticleData;
			}
			
			return array(
				'results' => $results,
				'removeResults' => $removeResults,
				'numberOfResults' => $numberOfResults
			);
		}
		return array(
			'results' => array(),
			'removeResults' => array(),
			'numberOfResults' => 0
		);
	}
	
	public function getArticleData($origArticle, $finalArticleData){
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
		$filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Description" OperatorType="1">'.$origArticle['Description'].'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="Maat" OperatorType="1" /></Index>';

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
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>100</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$data = simplexml_load_string($resultData);

		$results = array();
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$count = 0;
			foreach($data->$afasArticleConnector as $subArticle){
				$subArticle = $this->xml2array($subArticle);
				$addResult = $finalArticleData;
				if(isset($subArticle['Maat']) && $subArticle['Maat'] != ''){
					$addResult['size'] = $subArticle['Maat'];
					$addResult['sku'] = $subArticle['ItemCode'];
				}
				$addResult['quantity'] = $subArticle['StockActual'];
				$addResult['MainArtGroup'] = $origArticle['MainArtGroup'];
				$addResult['ArtGroup'] = $origArticle['ArtGroup'];
				$addResult['Brand'] = $origArticle['Brand'];
				$addResult['Kleur'] = $origArticle['Kleur'];
				
				$sortOrder = $count;
				if($subArticle['Maat'] == 'Small'){
					$sortOrder = 0;
				} elseif($subArticle['Maat'] == 'Medium'){
					$sortOrder = 1;
				} elseif($subArticle['Maat'] == 'Large'){
					$sortOrder = 2;
				} elseif($subArticle['Maat'] == 'XLarge'){
					$sortOrder = 3;
				}
				$addResult['sortOrder'] = $sortOrder;
				$results[] = $addResult;
				$count++;
			}
		}
		return $results;
	}

	public function processImportArticleImages(){
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processImportArticleImages = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processImportArticleImages ' . $METRIC_starttime_processImportArticleImages);
	    
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();

		$currentPage = $this->Projects_model->getValue('custom_image_article_page', $project['id']) ? $this->Projects_model->getValue('custom_image_article_page', $project['id']) : 1;
		$client = $this->Lightspeed_model->getClient($projectId);

		$products = $client->products->get(null, array('limit' => 5, 'page' => $currentPage));
		foreach($products as $product){
			if(isset($product['image']) && isset($product['image']['src'])){
				$variants = $client->variants->get(null, array(
					'product' => $product['id']
				));
				foreach($variants as $variant){
					// Update image in AFAS
					$this->updateImageInAfas($variant['sku'], $product['image']);
				}
			}
		}
		
/*
		$products = $client->variants->get(null, array('limit' => 10, 'page' => $currentPage));
		foreach($products as $product){
			if(isset($product['image']) && isset($product['image']['src'])){
				// Update image in AFAS
				$this->updateImageInAfas($product['sku'], $product['image']);
			}
		}
*/
		if (count($products) > 0) {
			$this->Projects_model->saveValue('custom_image_article_page', $currentPage + 1, $project['id']);
		}
		
		if(count($products) < 1){
			$this->Projects_model->saveValue('custom_image_article_page', 1, $project['id']);
		}
	}
	
	public function updateImageInAfas($sku, $image){
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
		$connector = 'FbItemArticle';
		$xmlProduct = new SimpleXMLElement("<".$connector."></".$connector.">");
		$orderElement = $xmlProduct->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'update');

		$fields->ItCd = $sku;
		if(isset($image['src'])){
			$fields->FileName = basename($image['title']).'.'.$image['extension'];
			$fields->FileStream = base64_encode(file_get_contents($image['src']));
			$fields->U95F07A65407429CF32C73AB7A66FEAC6 = $image['src'];
		}

		$data = $xmlProduct->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $connector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			api2cart_log($projectId, 'importarticles', 'Could not export product image '.$$sku.' to AFAS. Error: '.$result['faultstring']);
			return false;
		} else {
			api2cart_log($projectId, 'importarticles', 'Exported product image '.$sku.' to AFAS.');
		}
	}
	
	public function processOrderStatus(){
	    
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processOrderStatus = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processOrderStatus ' . $METRIC_starttime_processOrderStatus);
	    
		// Get all orders from Lightspeed. When order status still not updated after 14 days, write order ID to file for manual check
		$orders = $this->getAfasOrders();
		foreach($orders as $order){
			if($order['Status'] == 'Geleverd' && $order['Creditorder'] != 'true'){
				$this->updateOrderStatusLightspeed($order);
			}
		}
	}
	
	public function getAfasOrders(){
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();
		
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_Salesorders_App';
		$lastUpdateDate = $this->Projects_model->getValue('afas_custom_order_last_update_date', $projectId);
		$amount = 10;
		$offset = $this->Projects_model->getValue('afas_custom_order_offset', $projectId) ? $this->Projects_model->getValue('afas_custom_order_offset', $projectId) : 0;
		//$offset = 0;
		//$lastUpdateDate = '2019-11-26T00:00:00';
		
		$filtersXML = '';
		if($lastUpdateDate != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDate.'</Field></Filter></Filters>';
		}
		$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';

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
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$data = simplexml_load_string($resultData);

		$results = array();
		if(isset($data->$connector) && count($data->$connector) > 0){
			foreach($data->$connector as $order){
				$order = $this->xml2array($order);
				$results[] = $order;
			}
			$this->Projects_model->saveValue('afas_custom_order_offset', $offset + $amount, $projectId);
		} else {
			$this->Projects_model->saveValue('afas_custom_order_offset', 0, $projectId);
			$this->Projects_model->saveValue('afas_custom_order_last_update_date', date('Y-m-d H:i:s'), $projectId);
		}
		return $results;
	}

	public function updateOrderStatusLightspeed($order){
		$projectId = $this->projectId;
		$client = $this->Lightspeed_model->getClient($projectId);

		// Set order status in Lightspeed to 'shipped'
		$saveData = array(
			'shipmentStatus' => 'shipped'
		);
		$lightspeedOrder = $client->orders->get(null, array(
			'number' => $order['OrderNumber'],
		));
		if(!empty($lightspeedOrder)){
			$lightspeedOrder = $lightspeedOrder[0];
			if(isset($lightspeedOrder['id'])){
				try{
					$result = $client->orders->update($lightspeedOrder['id'], $saveData);
					
					if($order['TrackTrace'] != ''){
						$shipments = $client->shipments->get(null, array(
							'order' => $lightspeedOrder['id']
						));
						foreach($shipments as $shipment){
							$client->shipments->update($shipment['id'], array(
								'trackingCode' => $order['TrackTrace']
							));
						}
					}
				} catch(Exception $e){
					api2cart_log($projectId, 'exportorders', 'Could not update order status for order '.$order['OrderNumber'].'. Result: '.$e->getMessage());
					return false;
				}
				if(isset($result['id']) && $result['id'] > 0){
					api2cart_log($projectId, 'exportorders', 'Updated order status for order '.$order['OrderNumber'].' to "shipped"');
				} else {
					api2cart_log($projectId, 'exportorders', 'Could not update order status for order '.$order['OrderNumber'].'. Result: '.print_r($result, true));
				}
			}
		}
	}

	public function processLightspeedReturns(){
	    
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processLightspeedReturns = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processLightspeedReturns ' . $METRIC_starttime_processLightspeedReturns);
	    
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();

		$client = $this->Lightspeed_model->getClient($projectId);
		$timeFilter = $this->Projects_model->getValue('custom_lightspeed_returns_created_at', $projectId);
		$returns = $client->returns->get(null, array(
			'limit' => 10,
			'created_at_min' => $timeFilter ? $timeFilter : '2019-01-01T00:00:00+00:00'
		));
		foreach($returns as $return){
			if(!isset($return['createdAt'])){
				continue;
			}
			$this->Projects_model->saveValue('custom_lightspeed_returns_created_at', $return['createdAt'], $projectId);
			
			// Add return as negative sales order in AFAS
			// Get order number from LightSpeed
			$order = $client->orders->get($return['orderId']);
			if(isset($order['number'])){
				$orderNumber = $order['number'];
				
				// Now create negative sales order
				$afasOrder = $this->getAfasOrderByLightspeedNumber($orderNumber);
				if($afasOrder){
					$result = $this->createReturnInAfas($return, $order, $afasOrder);
					if($result){
						// Set return status in Lightspeed to 'in progress'
						$saveData = array(
							'status' => 'pending'
						);
			
						try{
							$result = $client->returns->update($return['id'], $saveData);
						} catch(Exception $e){
							api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$return['id'].'. Result: '.$e->getMessage());
							return false;
						}
						if(isset($result['id']) && $result['id'] > 0){
							api2cart_log($projectId, 'exportorders', 'Updated return status for return '.$return['id'].' to "in progress"');
						} else {
							api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$return['id'].'. Result: '.print_r($result, true));
						}
					}
				}
			}
		}
	}

	public function getAfasOrderByLightspeedNumber($orderNumber){
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();
		
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_Salesorders_App';
		$amount = 1;
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrderNumber" OperatorType="1">'.$orderNumber.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';

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
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);

		$result = array();
		if(isset($data->$connector) && count($data->$connector) > 0){
			foreach($data->$connector as $order){
				$order = $this->xml2array($order);
				$result = $order;
			}
		}
		return $result;
	}

	public function getAfasOrderLines($orderNumber){
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();
		
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_SalesorderLines_App';
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrderNumber" OperatorType="1">'.$orderNumber.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';

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
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1000</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);

		$result = array();
		if(isset($data->$connector) && count($data->$connector) > 0){
			foreach($data->$connector as $orderLine){
				$orderLine = $this->xml2array($orderLine);
				$result[] = $orderLine;
			}
		}
		return $result;
	}

	public function getAfasInvoice($invoiceNumber){
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => 139))->row_array();
		
		$projectId = $this->projectId;
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_DirectInvoices_App';
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Bijbehorende_factuur" OperatorType="1">'.$invoiceNumber.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';

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
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);

		$result = array();
		if(isset($data->$connector) && count($data->$connector) > 0){
			foreach($data->$connector as $invoice){
				$invoice = $this->xml2array($invoice);
				$result = $invoice;
			}
		}
		return $result;
	}
	
	public function createReturnInAfas($return, $order, $afasOrder){
		$projectId = $this->projectId;
		$client = $this->Lightspeed_model->getClient($projectId);
		
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
		$connector = 'FbSales';
		$xmlOrder = new SimpleXMLElement("<".$connector."></".$connector.">");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$fields->DbId = $afasOrder['DebtorId'];
		$fields->RfCs = 'WS: '.$order['number'];
		$fields->Re = $return['id'].' - '.$order['number'];
		$fields->CrOr = true;
		$fields->U33642D854BFBB7D51F1404B04C542DA5 = $return['returnReason']; // Reden
		$fields->U6263C8FD4D3F7C5ACFF32596C795DE43 = $return['id'];

		$objectsElement = $orderElement->addChild('Objects');
		$FbSalesLines = $objectsElement->addChild('FbSalesLines');

		$orderProducts = $client->ordersProducts->get($return['orderId']);
		foreach($return['orderProducts'] as $product){
			$productData = array();
			foreach($orderProducts as $orderProduct){
				if($orderProduct['id'] == $product['id']){
					$productData = $orderProduct;
				}
			}
			if(!empty($productData)){
				$element = $FbSalesLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
			    $fields->VaIt = 2;
				$fields->ItCd = $productData['articleCode'] ? $productData['articleCode'] : $productData['sku'];
				$fields->QuUn = -1 * floatval($product['quantity']);
	
				// Set price
				$price = $productData['priceExcl'];
				$fields->Upri = round($price, 2);
			}
		}

		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $connector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
	        api2cart_log($projectId, 'exportorders', 'Could not export return for order '.$afasOrder['OrderNumber'].' to AFAS. Error: '.$result['faultstring']);
			return false;
		} else {
			api2cart_log($projectId, 'exportorders', 'Exported return for order '.$afasOrder['OrderNumber'].' to AFAS.');
			return true;
		}
	}
	
	public function processReturnStatus(){
	    
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processReturnStatus = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processReturnStatus ' . $METRIC_starttime_processReturnStatus);
	    
		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_Salesorders_App';
		$lastUpdateDate = $this->Projects_model->getValue('afas_custom_return_last_update_date', $projectId);
		$amount = 10;
		$offset = $this->Projects_model->getValue('afas_custom_return_offset', $projectId) ? $this->Projects_model->getValue('afas_custom_return_offset', $projectId) : 0;
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Creditorder" OperatorType="1">true</Field></Filter></Filters>';
		if($lastUpdateDate != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDate.'</Field><Field FieldId="Creditorder" OperatorType="1">true</Field></Filter></Filters>';
		}
		$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';

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
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$data = simplexml_load_string($resultData);

		$client = $this->Lightspeed_model->getClient($projectId);

		$results = array();
		if(isset($data->$connector) && count($data->$connector) > 0){
			foreach($data->$connector as $order){
				$order = $this->xml2array($order);
				if($order['Status'] == 'Geleverd' && $order['RetourNummer'] != ''){
					if(($order['Twijfel'] == 'false' && $order['Afgekeurd'] == 'false') || $order['Goedgekeurd'] == 'true'){
						// Goedgekeurd
						// Update status in Lightspeed
						$saveData = array(
							'status' => 'authorized'
						);
			
						try{
							$result = $client->returns->update($order['RetourNummer'], $saveData);
							// Check for existing invoice
							$existingInvoice = $this->getAfasInvoice('RET'.$order['RetourNummer']);
							if(empty($existingInvoice)){
								$this->createReturnInvoiceInAfas($order);
							}
						} catch(Exception $e){
							api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$order['RetourNummer'].'. Result: '.$e->getMessage());
							return false;
						}
						if(empty($existingInvoice)){
							if(isset($result['id']) && $result['id'] > 0){
								api2cart_log($projectId, 'exportorders', 'Updated return status for return '.$order['RetourNummer'].' to "authorized"');
							} else {
								api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$order['RetourNummer'].'. Result: '.print_r($result, true));
							}
						}
					} elseif($order['Afgekeurd'] == 'true') {
						// Afgekeurd
						// Update status in Lightspeed
						$saveData = array(
							'status' => 'rejected'
						);
			
						try{
							$result = $client->returns->update($order['RetourNummer'], $saveData);
						} catch(Exception $e){
							api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$return['id'].'. Result: '.$e->getMessage());
							return false;
						}
						if(isset($result['id']) && $result['id'] > 0){
							api2cart_log($projectId, 'exportorders', 'Updated return status for return '.$return['id'].' to "rejected"');
						} else {
							api2cart_log($projectId, 'exportorders', 'Could not update return status for return '.$return['id'].'. Result: '.print_r($result, true));
						}
					}
				}
			}
			$this->Projects_model->saveValue('afas_custom_return_offset', $offset + $amount, $projectId);
		} else {
			$this->Projects_model->saveValue('afas_custom_return_offset', 0, $projectId);
			$this->Projects_model->saveValue('afas_custom_return_last_update_date', date('Y-m-d H:i:s'), $projectId);
		}
	}

	public function processLightspeedInvoices(){
	    
	    $projectId = $this->projectId;
	    
	    $METRIC_starttime_processLightspeedInvoices = microtime(true);
        api2cart_log($projectId, 'projectcontrol', 'Start AFAS function processLightspeedInvoices ' . $METRIC_starttime_processLightspeedInvoices);
	    
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Cms_model');
		$this->load->model('Lightspeed_model');

		$projectId = $this->projectId;
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();

		$client = $this->Lightspeed_model->getClient($projectId);
		$timeFilter = $this->Projects_model->getValue('custom_lightspeed_invoices_created_at', $projectId);
		$invoices = $client->invoices->get(null, array(
			'limit' => 5,
			'created_at_min' => $timeFilter ? $timeFilter : '2019-01-01T00:00:00+00:00'
		));
		
		foreach($invoices as $invoice){
			if(!isset($invoice['createdAt'])){
				continue;
			}
			$this->Projects_model->saveValue('custom_lightspeed_invoices_created_at', $invoice['createdAt'], $projectId);
			
			// Add invoice in AFAS
			// Get order number from LightSpeed
			$order = $client->orders->get($invoice['order']['resource']['id']);
			if(isset($order['number'])){
				$orderNumber = $order['number'];
				
				// Now create invoice in AFAS
				$afasOrder = $this->getAfasOrderByLightspeedNumber($orderNumber);
				if($afasOrder){
					$result = $this->createInvoiceInAfas($invoice, $order, $afasOrder);
				}
			}
		}
	}

	public function createInvoiceInAfas($invoice, $order, $afasOrder){
		$projectId = $this->projectId;
		$client = $this->Lightspeed_model->getClient($projectId);
		
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
		$connector = 'FbDirectInvoice';
		$xmlInvoice = new SimpleXMLElement("<".$connector."></".$connector.">");
		$invoiceElement = $xmlInvoice->addChild('Element');
		$fields = $invoiceElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		
		$fields->SinOrNu = 'INV' . substr( $order['number'], 4);
	    $fields->U608C892C44389B088275AB8F8485A91C = $order['discountCouponCode'];

		$fields->DbId = $afasOrder['DebtorId'];
		$fields->RfCs = 'WS: '.$order['number'];
		$paymentIssuer = strtolower($order['paymentId']);
		$paymentMethod = strtolower($order['paymentData']['method']);
		$finalPaymentMethod = '00';
		if(strpos($paymentIssuer, 'mollie') !== false && strpos($paymentMethod, 'paypal') !== false){
			$finalPaymentMethod = '07';
		} elseif(strpos($paymentIssuer, 'mollie') !== false && strpos($paymentMethod, 'klarnapaylater') !== false){
			$finalPaymentMethod = '06';
		} elseif(strpos($paymentIssuer, 'mollie') !== false && strpos($paymentMethod, 'visa') !== false){
			$finalPaymentMethod = '04';
		} elseif(strpos($paymentIssuer, 'mollie') !== false && strpos($paymentMethod, 'mastercard') !== false){
			$finalPaymentMethod = '04';
		} elseif(strpos($paymentIssuer, 'mollie') !== false){
			$finalPaymentMethod = '05';
		}
		$fields->PaTp = $finalPaymentMethod;
		$fields->War = 'A';

		$objectsElement = $invoiceElement->addChild('Objects');
		$FbDirectInvoiceLines = $objectsElement->addChild('FbDirectInvoiceLines');

		$orderProducts = $client->invoicesItems->get($invoice['id']);
		foreach($orderProducts as $productData){
			if(!empty($productData) && $productData['type'] == 'product'){
				$element = $FbDirectInvoiceLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
			    $fields->VaIt = 2;
				$fields->ItCd = $productData['articleCode'] ? $productData['articleCode'] : $productData['sku'];
				$fields->QuUn = floatval($productData['quantity']);
	
				// Set price
				$price = $productData['priceExcl'];
				$fields->Upri = round($price, 2);
			} elseif(!empty($productData) && $productData['type'] == 'shipment'){
				$element = $FbDirectInvoiceLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
			    $fields->VaIt = 2;
				$fields->ItCd = 'VERZEND';
				$fields->QuUn = 1;
	
				// Set price
				$price = $productData['priceExcl'];
				$fields->Upri = round($price, 2);
			} elseif(!empty($productData) && $productData['type'] == 'payment'){
				$element = $FbDirectInvoiceLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
			    $fields->VaIt = 2;
				$fields->ItCd = 'BETAAL';
				$fields->QuUn = 1;
	
				// Set price
				$price = $productData['priceExcl'];
				$fields->Upri = round($price, 2);
			}
		}

		$data = $xmlInvoice->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $connector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
	        api2cart_log($projectId, 'exportorders', 'Could not export invoice for order '.$afasOrder['OrderNumber'].' to AFAS. Error: '.$result['faultstring']);
			return false;
		} else {
			api2cart_log($projectId, 'exportorders', 'Exported invoice for order '.$afasOrder['OrderNumber'].' to AFAS.');
			return true;
		}
	}

	public function createReturnInvoiceInAfas($afasOrder){
		// Get return lines from AFAS return order
		$orderNumber = $afasOrder['OrderNumber'];
		$afasOrder = $this->getAfasOrderByLightspeedNumber($orderNumber);
		
		if($afasOrder){
			$orderLines = $this->getAfasOrderLines($orderNumber);
		}
		// Add return invoice in AFAS
		$projectId = $this->projectId;
		$client = $this->Lightspeed_model->getClient($projectId);
		
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
		$connector = 'FbDirectInvoice';
		$xmlInvoice = new SimpleXMLElement("<".$connector."></".$connector.">");
		$invoiceElement = $xmlInvoice->addChild('Element');
		$fields = $invoiceElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		
		$fields->SinOrNu = 'RET' . $afasOrder['RetourNummer'];
		$fields->DbId = $afasOrder['DebtorId'];
		$fields->RfCs = $afasOrder['CustomerReference'];
		$fields->War = 'A';

		$objectsElement = $invoiceElement->addChild('Objects');
		$FbDirectInvoiceLines = $objectsElement->addChild('FbDirectInvoiceLines');

		foreach($orderLines as $orderLine){
			if(!empty($orderLine)){
				$element = $FbDirectInvoiceLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
			    $fields->VaIt = 2;
				$fields->ItCd = $orderLine['ItemCodeId'];
				$fields->QuUn = floatval($orderLine['PiecePerUnit']);
	
				// Set price
				$price = $orderLine['PricePerUnit'];
				$fields->Upri = round($price, 2);
			}
		}

		$data = $xmlInvoice->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $connector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
	        api2cart_log($projectId, 'exportorders', 'Could not export invoice for return '.$afasOrder['RetourNummer'].' to AFAS. Error: '.$result['faultstring']);
			return false;
		} else {
			api2cart_log($projectId, 'exportorders', 'Exported invoice for return '.$afasOrder['RetourNummer'].' to AFAS.');
			return true;
		}
	}

	public function loadCustomOrderAttributes($appendItem, $order, $projectId){
		$appendItem['companyVatNumber'] = $order['companyVatNumber'];
		$appendItem['discountCouponCode'] = $order['discountCouponCode'];
		$appendItem['shipmentId'] = $order['shipmentId'];
		return $appendItem;
	}
	
	public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
		if(isset($orderData['companyVatNumber']) && $orderData['companyVatNumber'] != ''){
			$fields->VaDu = 6;
		}
	}
	
	public function setOrderParams($fields, $orderData){
	    $fields->Re = $orderData['number'];
	    $fields->War = 'A';
	    $fields->U608C892C44389B088275AB8F8485A91C = $orderData['discountCouponCode'];
	    $fields->U425B0C674E2E4BE4E386CAA6678BB9C2 = $orderData['shipmentId'];
	}

	public function checkConfigurable($saveData, $productData, $projectId, $type = 'create'){
		// Brand
		if($productData['Brand'] != ''){
			$this->load->model('Lightspeed_model');
			$client = $this->Lightspeed_model->getClient($projectId);
			
			$allBrands = $client->brands->get();
			$finalBrandId = '';
			foreach($allBrands as $brand){
				if($brand['title'] == $productData['Brand']){
					$finalBrandId = $brand['id'];
				}
			}
			
			if($finalBrandId == ''){
				// Create brand
				try{
					$result = $client->brands->create(array(
						'title' => $productData['Brand']
					));
					$finalBrandId = $result['id'];
				} catch(Exception $e){
					
				}
			}
			
			if($finalBrandId != ''){
				$saveData['brand'] = $finalBrandId;
			}
		}
		
		if($type == 'update'){
			unset($saveData['description']);
			unset($saveData['content']);
		}
		return $saveData;
	}
	
	public function afterProductUpdate($saveData, $productData, $projectId, $productResult){
		$this->load->model('Lightspeed_model');
		$client = $this->Lightspeed_model->getClient($projectId);
		
		$productId = $productResult['id'];
		
		$filters = $client->filters->get();
		$categoryFilterId = '';
		$colorFilterId = '';
		foreach($filters as $filter){
			if($filter['title'] == 'Categorie'){
				$categoryFilterId = $filter['id'];
			} elseif($filter['title'] == 'Kleur'){
				$colorFilterId = $filter['id'];
			}
		}
		
		$allCategoryFilterValues = $client->filtersValues->get($categoryFilterId);
		$allColorFilterValues = $client->filtersValues->get($colorFilterId);
		$allCategoryFilterValuesFinal = array();
		$allColorFilterValuesFinal = array();
		foreach($allCategoryFilterValues as $value){
			$title = $value['title'];
			$allCategoryFilterValuesFinal[$title] = $value['id'];
		}
		foreach($allColorFilterValues as $value){
			$title = $value['title'];
			$allColorFilterValuesFinal[$title] = $value['id'];
		}
		
		// Create category filter values and get active product filter values ids
		$categoryFilters = array_filter(array($productData['ArtGroup']));
		$activeProductCategoryFilters = array();
		foreach($categoryFilters as $categoryFilterValue){
			// Search for filter value
			if(!isset($allCategoryFilterValuesFinal[$categoryFilterValue])){
				// Create filter value
				try{
					$result = $client->filtersValues->create($categoryFilterId, array('title' => $categoryFilterValue));
				} catch(Exception $e){
					
				}
				$activeProductCategoryFilters[] = $result['id'];
			} else {
				$activeProductCategoryFilters[] = $allCategoryFilterValuesFinal[$categoryFilterValue];
			}
		}
		$activeProductCategoryFilters = array_filter($activeProductCategoryFilters);

		// Create color filter values and get active product filter values ids
		$colorFilters = array_filter(array($productData['Kleur']));
		$activeProductColorFilters = array();
		foreach($colorFilters as $colorFilterValue){
			// Search for filter value
			if(!isset($allColorFilterValuesFinal[$colorFilterValue])){
				// Create filter value
				try{
					$result = $client->filtersValues->create($colorFilterId, array('title' => $colorFilterValue));
				} catch(Exception $e){
					
				}
				$activeProductColorFilters[] = $result['id'];
			} else {
				$activeProductColorFilters[] = $allColorFilterValuesFinal[$colorFilterValue];
			}
		}
		$activeProductColorFilters = array_filter($activeProductColorFilters);
		
		$productFilterValues = $client->productsFiltervalues->get($productId);
		$removeProductFilterValues = array();
		$currentProductFilterValues = array();
		foreach($productFilterValues as $value){
			$filterId = $value['filter']['resource']['id'];
			$valueId = $value['filtervalue']['resource']['id'];
			if($filterId == $categoryFilterId){
				if(!in_array($valueId, $activeProductCategoryFilters)){
					$removeProductFilterValues[] = $value['id'];
				}
				$currentProductFilterValues[] = $valueId;
			} elseif($filterId == $colorFilterId){
				if(!in_array($valueId, $activeProductColorFilters)){
					$removeProductFilterValues[] = $value['id'];
				}
				$currentProductFilterValues[] = $valueId;
			}
		}
		
		// Add items
		foreach($activeProductCategoryFilters as $valueId){
			if(!in_array($valueId, $currentProductFilterValues)){
				// Create connection
				try{
					$result = $client->productsFiltervalues->create($productId, array('filter' => $categoryFilterId, 'filtervalue' => $valueId));
				} catch(Exception $e){
					
				}
			}
		}
		foreach($activeProductColorFilters as $valueId){
			if(!in_array($valueId, $currentProductFilterValues)){
				// Create connection
				try{
					$result = $client->productsFiltervalues->create($productId, array('filter' => $colorFilterId, 'filtervalue' => $valueId));
				} catch(Exception $e){
					
				}
			}
		}
		
		$removeProductFilterValues = array_unique(array_filter($removeProductFilterValues));
		foreach($removeProductFilterValues as $valueId){
			try{
				$result = $client->productsFiltervalues->delete($productId, $valueId);
			} catch(Exception $e){
				
			}
		}

	}
}