<?php //APICDEV

class Afas_model extends CI_Model {
    function __construct(){
        parent::__construct();
    }
    
	function xml2array( $xmlObject, $out = array () ){
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	}
	
	function getArticles($projectId, $offset = 0, $amount = 10, $debug = false){
		/*** added Optiply 20-06 ***/
		//$offset = 0;
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
		$filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$wms = $this->Projects_model->getValue('wms', $projectId);
		$pim = $this->Projects_model->getValue('pim', $projectId);
		$this->load->model('Akeneo_model');
        
        if($cms == 'optiply')
            $lastUpdateDate = empty($lastUpdateDate) ? date('c') : $lastUpdateDate;
		
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
			if($projectId == 2){
				$indexXml = '<Index><Field FieldId="DateModified" OperatorType="1" /></Index>';
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
// 		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		
		//if( $projectId == 131) { log_message('debug', 'ProductData 131AFAS - XML ' . var_export($xml_array, true)); }
		
		$err = $client->getError();
		if ($err) {
		    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    exit();
		}
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		
/* REMOVED DUE TO CHARACTER SUPPORT */		
//		$resultData = $this->replaceSpecialChars($resultData);
//		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
//		$resultData = $this->unReplaceSpecialChars($resultData);
		
		if($debug == true){
			echo $filtersXML;
			echo '<pre>';
			print_r($result);
			return;
		}

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
/*
				if(!isset($article['Description'])){
					continue;
				}
*/
				$finalArticleData = array();
				if($article['Blocked'] != 'false'){
					$finalArticleData['available_for_view'] = 'false';
					$finalArticleData['available_for_sale'] = 'false';
				}
				$finalArticleData['model'] = $article['ItemCode'];
				$finalArticleData['name'] = (string)$article['Description'];
				
				
				if(isset($article['LargeDesc'])){
				    $finalArticleData['long_description'] = str_replace('|br|', '<br />', (string)$article['LargeDesc']);
				}
				if(isset($article['ShortDesc'])){
				    $finalArticleData['short_description'] = str_replace('|br|', '<br />', (string)$article['ShortDesc']);
				}
				
				if(isset($article['ExtraDescription'])){
					$finalArticleData['description'] = str_replace('|br|', '<br />', (string)$article['ExtraDescription']);
				} else {
					$finalArticleData['description'] = str_replace('|br|', '<br />', (string)$article['Description']);
				}

				$finalArticleData['tax_class_id'] = $article['VATgroup'];
				$finalArticleData['price'] = isset($article['BasicSalesPrice']) ? $article['BasicSalesPrice'] : '';
				if(isset($article['StockActual'])){
					$finalArticleData['quantity'] = $article['StockActual'];
				}
                //New fields for Optiply 
				/*** added Optiply 20-06 ***/
                if ($cms == 'optiply' || $wms == 'optiply') $finalArticleData['Articlecode_intern'] = isset($article['Articlecode_intern']) ? $article['Articlecode_intern'] : null;
                if ($cms == 'optiply' || $wms == 'optiply') $finalArticleData['ArtGroup'] = isset($article['ArtGroup']) ? $article['ArtGroup'] : null;
                if ($cms == 'optiply' || $wms == 'optiply') $finalArticleData['UnitId'] = isset($article['UnitId']) ? $article['UnitId'] : null;
                if ($cms == 'optiply' || $wms == 'optiply') $finalArticleData['InkSerialNumber'] = isset($article['InkSerialNumber']) ? $article['InkSerialNumber'] : null;
				
				if($this->Projects_model->getValue('enable_custom_category_logic', $projectId) == '1'){
					if(!empty(json_decode($this->Projects_model->getValue('custom_category_logic', $projectId), true))){
						$categoryRows = json_decode($this->Projects_model->getValue('custom_category_logic', $projectId), true);
						$levels = $categoryRows['level'];
						asort($levels);

						$parent = '';
						$categories = array();
						foreach($levels as $index => $level){
							$code = $categoryRows['code'][$index];
							if($level == 0){
								$connector = isset($categoryRows['connector'][$index]) ? $categoryRows['connector'][$index] : '';
								$category = $this->getCustomCategory($connector, $code, $article['ArtGroup'], $projectId);
							} else {
								$connector = isset($categoryRows['connector'][$index]) ? $categoryRows['connector'][$index] : '';
								$category = $this->getCustomCategory($connector, $code, $parent, $projectId);
							}
							$parent = $category->parent;
							$categories[] = $category;
						}
						arsort($categories);
						$categoryIds = array();
						$parent = '';
						foreach($categories as $category){
							$storeCategory = $this->Cms_model->findCategory($projectId, (string)$category->description);
							$categoryId = false;
							if($storeCategory && isset($storeCategory->category) && !empty($storeCategory->category)){
								$storeCategory = $storeCategory->category;
								$categoryId = $storeCategory[0]->id;
							} elseif(!$storeCategory){
								// Create category
								$image = '';
								if(isset($category->image)){
									$image = (string)$category->image;
								}
								$storeCategory = $this->Cms_model->createCategory($projectId, (string)$category->description, $parent, $image);
								if(isset($storeCategory->category_id) && $storeCategory->category_id > 0){
									$categoryId = $storeCategory->category_id;
								}
							}
							if($categoryId){
								$parent = $categoryId;
								$categoryIds[] = $categoryId;
							}
						}
						$finalArticleData['categories_ids'] = implode(',', array_unique($categoryIds));
					}
				} elseif($this->Projects_model->getValue('enable_category_conversion_table', $projectId) == '1'){
					if(isset($article['ArtGroup']) && $article['ArtGroup'] != ''){
						$articleGroup = $article['ArtGroup'];
						$categoryConversions = json_decode($this->Projects_model->getValue('category_conversion_table', $projectId), true);
						foreach($categoryConversions['afas_id'] as $index => $conversionItem){
							if($conversionItem == $articleGroup){
								$finalArticleData['categories_ids'] = $categoryConversions['shop_id'][$index];
							}
						}
					}
				} elseif($this->Projects_model->getValue('enable_project_category_logic', $projectId) == '1'){
					// Load project specific data
					$projectModel = 'Project'.$projectId.'_model';
					if(file_exists(APPPATH."models/".$projectModel.".php")){
						$this->load->model($projectModel);
						if(method_exists($this->$projectModel, 'loadCategories')){
							$finalArticleData = $this->$projectModel->loadCategories($finalArticleData, $article, $projectId);
						}
					}
				} else {
					if (isset($article['ArtGroup']) && $article['ArtGroup'] != '') {
						if ($pim == 'akeneo') {
							$category_id = isset($article['ArtGroupID']) ? $article['ArtGroupID'] : $article['ArtGroup'];
							$categoryName = $this->getAfasProductCategoryName($projectId, $category_id);
						} else {
							$categoryName = $this->getAfasProductCategoryName($projectId, $article['ArtGroup']);
						}
                        if ($categoryName != '') {
                            if (!$cms && $pim == 'akeneo') {
                                $categoryId = $this->Akeneo_model->findCategory($projectId, $category_id);
                            } else {
                                $categoryId = $this->Cms_model->findCategory($projectId, $categoryName);
                            }
                            if (!$categoryId) {
                                if (!$cms && $pim == 'akeneo') {
                                    $categoryId = $this->Akeneo_model->createCategory($projectId, $category_id, $categoryName);
                                } else {
                                    $categoryId = $this->Cms_model->createCategory($projectId, $categoryName);
                                }
                            }
                            if ($categoryId) {
                                $finalArticleData['categories_ids'] = $categoryId;
                            }
                        }
                    }
				}
				
				if($this->Projects_model->getValue('enable_attribute_set_conversion_table', $projectId) == '1'){
					//$article['attribute_set'] = 5;
					if(isset($article['attribute_set']) && $article['attribute_set'] > 0){
						$attributeSet = $article['attribute_set'];
						$attributeSetConversions = json_decode($this->Projects_model->getValue('attribute_set_conversion_table', $projectId), true);
						foreach($attributeSetConversions['afas_id'] as $index => $conversionItem){
							if($conversionItem == $attributeSet){
								$finalArticleData['attribute_set_name'] = $attributeSetConversions['shop_id'][$index];
							}
						}
					}
				}
/*	Revised Images download - untested			
				foreach ($article as $key => $value) {
                    if (stripos($key, 'Afbeelding') !== FALSE) {
                        $imgNumber = preg_replace('~[\D]+~','',$key);
                        $imgKey = "";
                        if ($imgNumber) {
                            $imgKey = "_" . $imgNumber;
						}
						if(isset($article[$key])){
                            $imageName = $article['Bestandsnaam' . $imgKey];
                            $imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article[$key]));
                            $finalArticleData['image' . $imgKey] = $imageLocation;
                        }
					}
				}
*/			
				if(isset($article['Afbeelding'])){
					$imageName = $article['Bestandsnaam'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding']));
					$finalArticleData['image'] = $imageLocation;
				}
				if(isset($article['Afbeelding_1'])){
					$imageName = $article['Bestandsnaam_1'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_1']));
					$finalArticleData['image_1'] = $imageLocation;
				}
				if(isset($article['Afbeelding_2'])){
					$imageName = $article['Bestandsnaam_2'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_2']));
					$finalArticleData['image_2'] = $imageLocation;
				}
				if(isset($article['Afbeelding_3'])){
					$imageName = $article['Bestandsnaam_3'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_3']));
					$finalArticleData['image_3'] = $imageLocation;
				}
				if(isset($article['Afbeelding_4'])){
					$imageName = $article['Bestandsnaam_4'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_4']));
					$finalArticleData['image_4'] = $imageLocation;
				}
				if(isset($article['Afbeelding_5'])){
					$imageName = $article['Bestandsnaam_5'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_5']));
					$finalArticleData['image_5'] = $imageLocation;
				}
				if(isset($article['Afbeelding_6'])){
					$imageName = $article['Bestandsnaam_6'];
					$imageLocation = save_image_string($projectId, $imageName, str_replace('|br|', '', $article['Afbeelding_6']));
					$finalArticleData['image_6'] = $imageLocation;
				}

				// Load mapped attributes data
				$finalArticleData = $this->Cms_model->applyMappedAttributes($projectId, $article, $finalArticleData);
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'getArticleData')){
						$finalArticleData = $this->$projectModel->getArticleData($article, $finalArticleData);
					}
				}

				if(isset($finalArticleData['enabled']) && ($finalArticleData['enabled'] == false || $finalArticleData['enabled'] == 'false')){
					$removeResults[] = $article['ItemCode'];
					continue;
				}

				$results[] = $finalArticleData;
			}
			
			// if( $projectId == 84) { log_message('debug', 'ProductData 84 ' . var_export($results, true)); }
			
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
	
	function getStockArticles($projectId, $offset = 0, $amount = 10, $debug = false){
	    
	    log_message('debug', 'ProductStock  ' . $projectId . ' Result:' . var_export($projectId, true));
	    
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		/*** Replaced Optiply 20-06 ***/
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		/*** added Optiply 20-06 ***/
		$afasStockConnector = $this->Projects_model->getValue('afas_stock_con', $projectId);
		
		if ($afasStockConnector == "") $afasStockConnector = $afasArticleConnector;
		
		$lastUpdateDate = $this->Projects_model->getValue('afas_stock_last_update_date', $projectId);
		$filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);
		$cms = $this->Projects_model->getValue('cms', $projectId);
		
		/*** added Optiply 20-06 ***/
        //if($cms == 'optiply')
        //    $lastUpdateDate = empty($lastUpdateDate) ? date('c') : $lastUpdateDate;
		
		$filtersXML = '';
		if($filterEnabled == '1'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
		}

		/*** added Optiply 20-06 ***/		
		if($lastUpdateDate != ''){
            //$lastUpdateDateFilter = substr($lastUpdateDate, 0, strpos($lastUpdateDate, '+'));
            $lastUpdateDateFilter = $lastUpdateDate.'T00:00:00';

            if($filterEnabled == '1') {
                $filtersXML .= '<Filters><Filter FilterId="Filter2"><Field FieldId="LastStockUpdate" OperatorType="2">' . $lastUpdateDateFilter . '</Field></Filter></Filters>';
            } else {
                $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="LastStockUpdate" OperatorType="2">' . $lastUpdateDateFilter . '</Field></Filter></Filters>';
            }
        }
		/*** added Optiply 20-06 end***/

/*
		if($lastUpdateDate != ''){
			$lastUpdateDateFilter = $lastUpdateDate.'T00:00:00';
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field></Filter></Filters>';
			if($filterEnabled == '1'){
				$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
			}
		}
*/
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		
		/*** adjusted Optiply 20-06 ***/
		$xml_array['connectorId'] = $afasStockConnector;
		$xml_array['filtersXml'] = $filtersXML;
		//$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>' . $offset . '</Skip><Take>' . $amount . '</Take><Index><Field FieldId="ItemCodeId" OperatorType="1" /></Index></options>';
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);
		
		if($debug == true){
			echo $filtersXML;
			echo '<pre>';
			print_r($result);
			return;
		}
		
		//log_message('debug', 'ProductStock - xml  ' . $projectId . ' Result:' . var_export($resultData, true));
		
		/*** adjusted Optiply 20-06 afasArticleConnector --> afasStockConnector ***/
		$data = simplexml_load_string($resultData);
        $numberOfResults = count($data->$afasStockConnector);
        if(isset($data->$afasStockConnector) && count($data->$afasStockConnector) > 0){
            $results = array();
            foreach ($data->$afasStockConnector as $article) {
                $article = $this->xml2array($article);
                
                $finalStockData = array();
                $finalStockData['model'] = $article['ItemCode'];
                //$finalStockData['name'] = $article['Description'];

                if (isset($article['StockActual'])) {
                    if ($projectId == 35) {
                        $finalStockData['quantity'] = ( $article['StockActual'] - 20 ) > 0 ? ($article['StockActual'] - 20) : 0;
                    }
                    else {
                        $finalStockData['quantity'] = $article['StockActual'];
                    }
                }

                // Load project specific data
                $projectModel = 'Project' . $projectId . '_model';
                if (file_exists(APPPATH . "models/" . $projectModel . ".php")) {
                    $this->load->model($projectModel);
                    if (method_exists($this->$projectModel, 'getStockArticleData')) {
                       $finalStockData = $this->$projectModel->getStockArticleData($article, $finalStockData);
                    }
                }

                $results[] = $finalStockData;
            }
            return array(
                'results' => $results,
                'numberOfResults' => $numberOfResults
            );
        }
        return array(
            'results' => array(),
            'numberOfResults' => 0
        );
    }

	function getAfasProductCategoryName($projectId, $artGroupId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasArticleGroupsConnector = $this->Projects_model->getValue('afas_article_groups_connector', $projectId);
		if($afasArticleGroupsConnector == ''){
			$afasArticleGroupsConnector = 'Profit_ArticleGroups';
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Artikelgroep" OperatorType="1">'.$artGroupId.'</Field></Filter></Filters>';
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleGroupsConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->$afasArticleGroupsConnector) && count($data->$afasArticleGroupsConnector) > 0){
			$articleGroup = $this->xml2array($data->$afasArticleGroupsConnector);
			if(!empty($articleGroup)){
				return $articleGroup['Omschrijving'];
			}
		}
		return false;
	}
	
	function sendOrder($projectId, $orderData){
		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);
		
		// Load project specific data
		$debtorId = false;
		$customAfasCustomerCheck = false;
		$orderContactPerson = '';
		
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkAfasCustomerExists')){
				$customAfasCustomerCheck = true;
				$debtor = $this->$projectModel->checkAfasCustomerExists($projectId, $customerData, $orderData['id'], $orderData);
				if(isset($debtor['debtor_id']) && $debtor['debtor_id'] != '' && $debtor['debtor_id'] != false){
					$debtorId = $debtor['debtor_id'];
				}
				if(isset($debtor['contact_person_id']) && $debtor['contact_person_id'] != '' && $debtor['contact_person_id'] != false){
					$orderContactPerson = $debtor['contact_person_id'];
				}
			}
		}
		if(!$customAfasCustomerCheck){
			$debtorId = $this->checkAfasCustomerExists($projectId, $customerData, $orderData['id'], $orderData);
		}
		
		if(!$debtorId){
			apicenter_logs($projectId, 'exportorders', 'Could not find/add customer to AFAS', true);
			return false;
		}
		
		
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasOrderType = $this->Projects_model->getValue('orders_type', $projectId);
		if($afasOrderType == '' || $afasOrderType == null || $afasOrderType == ' '){
			$afasOrderType = 'FbSales';
		}
		
		$xmlOrder = new SimpleXMLElement("<".$afasOrderType."></".$afasOrderType.">");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$fields->DbId = $debtorId;
		// Afwijkende contactpersoon
		if($orderContactPerson > 0){
			$fields->CtI1 = $orderContactPerson;
		}
		// Selecteer magazijn in order kop
		$fields->War = '*****';
		$fields->OrNu = $orderData['id'];
		
		$fields->OrDa = $orderData['create_at'];
		
		$comment = isset($orderData['comment']) ? $orderData['comment'] : '';
		if($comment != ''){
			$fields->Re = $comment;
		}
		
		// Administratie
		if($this->Projects_model->getValue('afas_orders_administration', $projectId) != '' && $this->Projects_model->getValue('afas_orders_administration', $projectId) != 'default'){
			$administration = $this->Projects_model->getValue('afas_orders_administration', $projectId);
			$fields->Unit = $administration;
		}
		
		// Delivery address
		if($afasOrderType != 'FbDirectInvoice'){
			$deliveryAddress = $this->addDeliveryAddress($orderData, $debtorId, $projectId);
			if($deliveryAddress != false){
				$fields->DlAd = $deliveryAddress;
			}
		}
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setOrderParams')){
				$this->$projectModel->setOrderParams($fields, $orderData);
			}
		}
		
		$objectsElement = $orderElement->addChild('Objects');
		if($afasOrderType == 'FbDirectInvoice'){
			$FbSalesLines = $objectsElement->addChild('FbDirectInvoiceLines');
		} else {
			$FbSalesLines = $objectsElement->addChild('FbSalesLines');
		}
		
		// Add items
		$products = $orderData['order_products'];
		foreach($products as $item){
			$product = $item;

			$element = $FbSalesLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			
			if ( ( substr($product['model'], 0, 2) == "ST" ) && $projectId == 85 ) {
			    $fields->VaIt = 7; //Samenstelling
			}
			else {
			    $fields->VaIt = 2;
			}
			
			$fields->ItCd = $product['model'];
			//$fields->BiUn = 'stk';
			$fields->QuUn = floatval($product['quantity']);
			// Set price
			$price = $product['price'];
			$fields->Upri = round($price, 2);
			
			
			//$fields->War = '*****';
			// Discount
			if(isset($product['discount_amount']) && $product['discount_amount'] > 0){
				$fields->ARDc = $product['discount_amount'];
			}
			
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'setOrderProductParams')){
					$this->$projectModel->setOrderProductParams($fields, $item);
				}
			}
		}
		
		// Shipping
		$shippingSku = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if($shippingSku != '' && $shippingSku != ' '){
			if(isset($orderData['totals']) && isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0){
				$element = $FbSalesLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
				$fields->VaIt = 2;
				$fields->ItCd = $shippingSku;
				//$fields->BiUn = 'stk';
				$fields->QuUn = 1;
				// Set price
				$price = $orderData['totals']['shipping'];
				$fields->Upri = round($price, 2);
				
				//$fields->War = '*****';
			}
		}
		
		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $afasOrderType;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;

		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if((isset($result['faultcode']) && $result['faultcode'] != '') || $result === false){
		    if (stristr($result['faultstring'], 'Nummer pakbon') === FALSE)
		        apicenter_logs($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to AFAS. Error: '.$result['faultstring'], true);
			return false;
		} else {
			apicenter_logs($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to AFAS.', false);
			
			// Send order success project custom callback
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'afterOrderSubmit')){
					$this->$projectModel->afterOrderSubmit($orderData);
				}
			}
		}
		return true;
	}
	
	function addDeliveryAddress($orderData, $afasCustomerId, $projectId){
		$orderShippingAddress = $orderData['shipping_address'];

		if( $this->Projects_model->getValue('martketplace', $projectId) == 'bol' ) {
		    $company == '';
		}
		else {
			$company = isset($orderShippingAddress['company']) ? $orderShippingAddress['company'] : '';
		}

		if($company != ''){
			$connector = 'KnOrganisation';
		} else {
			$connector = 'KnPerson';
		}
		
		$street = $orderShippingAddress['address1'];
		$street = str_replace("
", ' ', $street);
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		if(!is_numeric($homeNumber)){
			$matches = array();
			preg_match('/([0-9]+)([^0-9]+)/',$homeNumber,$matches);
			$homeNumber = isset($matches[1]) ? $matches[1] : '';
			$homeNumberAddition = isset($matches[2]) ? $matches[2] : '';
		}

/*
		$homeNumber = explode('-', $homeNumber);
		$homeNumber = $homeNumber[0];
*/
		$street = implode(' ', $street);
		$zipCode = str_replace(' ', '', $orderShippingAddress['postcode']);
		$magentoCountryId = $orderShippingAddress['country'];
		
		// Get current delivery addresses first
		$deliveryAddresses = $this->getDeliveryAddresses($afasCustomerId, $projectId);
		$organisationId = '';
		foreach($deliveryAddresses as $address){
			$organisationId = $address->OrgId;
			if($address->A_Straat == $street && $address->A_Huisnummer == $homeNumber && str_replace(' ', '', $address->A_Postcode) == $zipCode){
				return (string)$address->Adres_ID;
			}
		}
		if(!$organisationId > 0){
			$debtor = $this->loadCustomer($afasCustomerId, $projectId);
			if(isset($debtor->BcCo) && $debtor->BcCo != ''){
				$organisationId = $debtor->BcCo;
			} else {
				return false;
			}
		}
		
		// Create delivery address
		$xmlOrganisation = new SimpleXMLElement("<".$connector."></".$connector.">");
		$xmlOrganisation->addAttribute("xsi:somename", "somevalue", 'http://www.w3.org/2001/XMLSchema-instance');
		$knOrganisationElement = $xmlOrganisation->addChild('Element');
		$fields = $knOrganisationElement->addChild('Fields');
		$fields->addAttribute('Action', 'update');
		if($company != ''){
			$fields->MatchOga = 0;
		} else {
			$fields->MatchPer = 0;
		}
		$fields->BcCo = $organisationId;
		
		$knOrganisationObjects = $knOrganisationElement->addChild('Objects');
		$knContact = $knOrganisationObjects->addChild('KnContact');
		$knContactElement = $knContact->addChild('Element');
		$knContactElementFields = $knContactElement->addChild('Fields');
		$knContactElementFields->addAttribute('Action', 'insert');
		$knContactElementFields->ViKc = 'AFL';
		$knContactElementFields->ExAd = '';
		$knContactElementFields->PadAdr = 0;
		$knContactObjects = $knContactElement->addChild('Objects');
		
		$countryAfasCodes = array("AT" => "A", "AE" => "AE", "AF" => "AFG", "AG" => "AG", "AI" => "AIA", 
		"AL" => "AL", "AM" => "AM", "AO" => "AN", "AD" => "AND", "SA" => "AS", "AS" => "ASM", "AQ" => "ATA", 
		"TF" => "ATF", "AU" => "AUS", "AW" => "AW", "AX" => "AX", "AZ" => "AZ", "BE" => "B", "BA" => "BA", 
		"BD" => "BD", "BB" => "BDS", "BG" => "BG", "BZ" => "BH", "BL" => "BL", "BM" => "BM", "BO" => "BOL", 
		"BQ" => "BQ", "BR" => "BR", "BH" => "BRN", "BN" => "BRU", "BS" => "BS", "BT" => "BT", "BF" => "BU", 
		"MM" => "BUR", "BV" => "BVT", "BY" => "BY", "CU" => "C", "CC" => "CCK", "CA" => "CDN", "CH" => "CH", 
		"CI" => "CI", "LK" => "CL", "CN" => "CN", "CO" => "CO", "CK" => "COK", "CR" => "CR", "CV" => "CV", 
		"CW" => "CW", "CX" => "CXR", "CY" => "CY", "KY" => "CYM", "CZ" => "CZ", "DE" => "D", "DJ" => "DJI", 
		"DK" => "DK", "DO" => "DOM", "BJ" => "DY", "DZ" => "DZ", "ES" => "E", "KE" => "EAK", "TZ" => "EAT", 
		"UG" => "EAU", "EC" => "EC", "EE" => "EE", "SV" => "EL", "GQ" => "EQ", "ER" => "ERI", "EH" => "ESH", 
		"EG" => "ET", "ET" => "ETH", "FR" => "F", "FI" => "FIN", "FJ" => "FJI", "LI" => "FL", "FK" => "FLK", 
		"FO" => "FRO", "GA" => "GA", "GB" => "GB", "GT" => "GCA", "GE" => "GE", "GF" => "GF", "GG" => "GG", 
		"GH" => "GH", "GI" => "GIB", "GN" => "GN", "GP" => "GP", "GR" => "GR", "GL" => "GRO", "GU" => "GUM", 
		"GY" => "GUY", "GW" => "GW", "HU" => "H", "HK" => "HK", "JO" => "HKJ", "HM" => "HMD", "HN" => "HON", 
		"HR" => "HR", "IT" => "I", "IL" => "IL", "IM" => "IM", "IN" => "IND", "IO" => "IOT", "IR" => "IR", 
		"IE" => "IRL", "IQ" => "IRQ", "IS" => "IS", "JP" => "J", "JM" => "JA", "JE" => "JE", "KH" => "K", 
		"KG" => "KG", "KI" => "KIR", "KM" => "KM", "KN" => "KN", "KP" => "KO", "KW" => "KWT", "KZ" => "KZ", 
		"LU" => "L", "LA" => "LAO", "LY" => "LAR", "LR" => "LB", "LS" => "LS", "LT" => "LT", 
		"LV" => "LV", "MT" => "M", "MA" => "MA", "MY" => "MAL", "MH" => "MAR", "MC" => "MC", "MD" => "MD",
		"MX" => "MEX", "MF" => "MF", "FM" => "MIC", "MK" => "MK", "ME" => "MNE", "MP" => "MNP", 
		"MO" => "MO", "MZ" => "MOC", "MN" => "MON", "MQ" => "MQ", "MU" => "MS", "MS" => "MSR", "MV" => "MV", 
		"MW" => "MW", "YT" => "MYT", "NO" => "N", "AN" => "NA", "NC" => "NCL", "NF" => "NFK", "NI" => "NIC", 
		"NU" => "NIU", "NL" => "NL", "NP" => "NPL", "NR" => "NR", "NZ" => "NZ", "UZ" => "OEZ", "OM" => "OMA", 
		"PT" => "P", "PA" => "PA", "PN" => "PCN", "PE" => "PE", "PK" => "PK", "PL" => "PL", "PW" => "PLW", "PG" => "PNG", 
		"PR" => "PR", "PS" => "PSE", "PY" => "PY", "PF" => "PYF", "QA" => "QA", "AR" => "RA", "BW" => "RB", "TW" => "RC", 
		"CF" => "RCA", "CG" => "RCB", "CL" => "RCH", "RE" => "REU", "HT" => "RH", "ID" => "RI", "MR" => "RIM", "LB" => "RL", 
		"MG" => "RM", "ML" => "RMM", "NE" => "RN", "RO" => "RO", "KR" => "ROK", "UY" => "ROU", "PH" => "RP", "SM" => "RSM", 
		"BI" => "RU", "RU" => "RUS", "RW" => "RWA", "SE" => "S", "SB" => "SB", "SZ" => "SD", "SG" => "SGP", "GS" => "SGS", 
		"SH" => "SHN", "SJ" => "SJM", "SK" => "SK", "SI" => "SLO", "SR" => "SME", "SN" => "SN", "SO" => "SP", "PM" => "SPM", 
		"RS" => "SRB", "SS" => "SS", "ST" => "ST", "SD" => "SUD", "NA" => "SWA", "SX" => "SX", "SC" => "SY", "SY" => "SYR", 
		"TH" => "T", "TJ" => "TAD", "CM" => "TC", "TC" => "TCA", "TG" => "TG", "TK" => "TKL", "TL" => "TLS", "TM" => "TMN", 
		"TN" => "TN", "TO" => "TO", "TR" => "TR", "TD" => "TS", "TT" => "TT", "TV" => "TV", "UA" => "UA", "UM" => "UMI", 
		"US" => "USA", "VA" => "VAT", "VG" => "VGB", "VI" => "VIR", "VN" => "VN", "VU" => "VU", "GM" => "WAG", "SL" => "WAL", 
		"NG" => "WAN", "DM" => "WD", "GD" => "WG", "LC" => "WL", "WF" => "WLF", "WS" => "WSM", "VC" => "WV", "XK" => "XK", 
		"YE" => "YMN", "YU" => "YU", "VE" => "YV", "ZM" => "Z", "ZA" => "ZA", "CD" => "ZRE", "ZW" => "ZW");
		
		$countryAfasCode = $orderShippingAddress['country'];
		if(isset($countryAfasCodes[$countryAfasCode])){
			$countryAfasCode = $countryAfasCodes[$countryAfasCode];
		}
		$knBasicAddress = $knContactObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $orderShippingAddress['postcode']);
		$knBasicAddressElementFields->Rs = $orderShippingAddress['city'];
		$knBasicAddressElementFields->ResZip = 0;
		
		$knBasicAddress = $knContactObjects->addChild('KnBasicAddressPad');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $orderShippingAddress['postcode']);
		$knBasicAddressElementFields->Rs = $orderShippingAddress['city'];
		$knBasicAddressElementFields->ResZip = 0;
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setDeliveryAddressData')){
				$xmlOrganisation = $this->$projectModel->setDeliveryAddressData($orderData, $xmlOrganisation);
			}
		}

		$data = $xmlOrganisation->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
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
		//print_r($result);exit;
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			apicenter_logs($projectId, 'exportorders', 'Could not add delivery address in AFAS for order '.$orderData['id'].'. Error: '.$result['faultstring'], false);
			return false;
		}
		
		$deliveryAddresses = $this->getDeliveryAddresses($afasCustomerId, $projectId);
		foreach($deliveryAddresses as $address){
			if($address->A_Straat == $street && $address->A_Huisnummer == $homeNumber && str_replace(' ', '', $address->A_Postcode) == $zipCode){
				return (string)$address->Adres_ID;
			}
		}
		return false;
	}
	
	function getDeliveryAddresses($afasCustomerId, $projectId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasDeliveryAddressConnector = $this->Projects_model->getValue('afas_delivery_address_connector', $projectId);
		if($afasDeliveryAddressConnector == ''){
			$afasDeliveryAddressConnector = 'Profit_Deliveryaddress';
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DebiteurId" OperatorType="1">'.$afasCustomerId.'</Field></Filter></Filters>';
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasDeliveryAddressConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>10000</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);

		$data = simplexml_load_string($resultData);
		$addresses = array();
		if(isset($data->$afasDeliveryAddressConnector) && count($data->$afasDeliveryAddressConnector) > 0){
			foreach($data->$afasDeliveryAddressConnector as $address){
				$addresses[] = $address;
			}
		}
		return $addresses;
	}
	
	function loadCustomer($afasCustomerId, $projectId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasDebtorConnector = $this->Projects_model->getValue('afas_customers_connector', $projectId);
		if($afasDebtorConnector == ''){
			$afasDebtorConnector = 'Profit_Debtor';
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DebtorId" OperatorType="1">'.$afasCustomerId.'</Field></Filter></Filters>';
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasDebtorConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);

		$data = simplexml_load_string($resultData);
		$debtors = array();
		if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
			foreach($data->$afasDebtorConnector as $debtor){
				$debtors[] = $debtor;
			}
		}
		return $debtors[0];
	}
	
	function checkAfasCustomerExists($projectId, $customerData, $ordernumber = "", $orderData = array()){
		$finalDebtorId = false;
		
		if($projectId != 141) $debtorId = $this->checkAfasCustomer($projectId, $customerData, 'email');
		
		$admin_debugging = $this->Projects_model->getValue('admin_logs', $projectId);
		if ($admin_debugging == '1'){
			apicenter_logs($projectId, "projectcontrol", $debtorId, false);
		}
		
		
		if($projectId == 141){
			if ($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'customerid')){
		   		$finalDebtorId = $debtorId;
			}    
		}
		else{
			if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'email')){
		    	$finalDebtorId = $debtorId;
			}
			//else {
				//if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'zipcode_streetnumber')){
				//	$finalDebtorId = $debtorId;
				//} else {
					/*
					if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'lastname_firstname')){
						$finalDebtorId = $debtorId;
					}
					*/
			//}
		}

		if($finalDebtorId == false){
			if($this->createAfasCustomer($projectId, $customerData, $ordernumber, $orderData)){
				$finalDebtorId = $this->checkAfasCustomer($projectId, $customerData, 'email');
			}
		}
		return $finalDebtorId;
	}

	function checkAfasCustomer($projectId, $customerData, $type, $orgPerType = 'Organisatie'){
		if($type == 'email'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Type" OperatorType="1">'.$orgPerType.'</Field><Field FieldId="MailWork" OperatorType="1">'.$customerData['email'].'</Field></Filter></Filters>';
		} 
		elseif($type == 'zipcode_streetnumber'){
			$postCode = preg_replace('/(?<=[a-z])(?=\d)|(?<=\d)(?=[a-z])/i', ' ', strtoupper($customerData['postcode']));
			$city = strtoupper($customerData['city']);
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="AdressLine1" OperatorType="1">'.str_replace(',', '', $customerData['address1']).'</Field><Field FieldId="AdressLine3" OperatorType="1">'.$customerData['postcode'].'  '.$customerData['city'].'</Field></Filter></Filters>';
		} 
		elseif($type == 'lastname_firstname'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Name" OperatorType="6">%'.$customerData['first_name'].' '.$customerData['last_name'].'%</Field></Filter></Filters>';
		} 
		elseif($type == 'customerid'){
		    $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Webshop_code" OperatorType="1">' . $customerData['id'] . '</Field></Filter></Filters>';
		}
		else {
			return false;
		}
		
		
		
		
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$orgPerConnector = $this->Projects_model->getValue('afas_orgper_connector', $projectId);
		if($orgPerConnector == ''){
			$orgPerConnector = 'Profit_OrgPer';
		}
		$afasDebtorConnector = $this->Projects_model->getValue('afas_customers_connector', $projectId);
		if($afasDebtorConnector == ''){
			$afasDebtorConnector = 'Profit_Debtor';
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		
		if( $projectId != 141) $xml_array['connectorId'] = $orgPerConnector;
		else $xml_array['connectorId'] = $afasDebtorConnector;
		
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take><Index><Field FieldId="BcCo" OperatorType="0" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);
		
		$data = simplexml_load_string($resultData);
		
		$admin_debugging = $this->Projects_model->getValue('admin_logs', $projectId);
		
		if ($admin_debugging == '1'){
			apicenter_logs($projectId, "projectcontrol", var_export($xml_array, true), false);
			apicenter_logs($projectId, "projectcontrol", var_export($data, true), false);
		}
		
		
		if ($projectId == 141) {
		    if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
				$debtorData = $data->$afasDebtorConnector;
				$debtorId = $debtorData->DebtorId;
				if($debtorId != ''){
					return (string)$debtorId;
				}
			}
			return false;
		}

		if(isset($data->$orgPerConnector) && count($data->$orgPerConnector) > 0){
			
			$afasPersonData = $data->$orgPerConnector;
			$afasPersonId = $afasPersonData->BcCo;
						
			if ($admin_debugging == '1'){
				apicenter_logs($projectId, "projectcontrol", $afasPersonId, false);
			}
			
			if($afasPersonId != ''){
				// Get sales relation ID
				$debtorFiltersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="BcCo" OperatorType="1">'.$afasPersonId.'</Field></Filter></Filters>';
				
				$client = new nusoap_client($afasGetUrl, true);
				$client->setUseCurl(true);
				$client->useHTTPPersistentConnection();
				
				$xml_array['environmentId'] = $afasEnvironmentId;
				$xml_array['token'] = $afasToken;
				$xml_array['connectorId'] = $afasDebtorConnector;
				$xml_array['filtersXml'] = $debtorFiltersXML;
				$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
				
				$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
				$resultData = $result["GetDataWithOptionsResult"];
				$resultData = $this->replaceSpecialChars($resultData);
				$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
				$resultData = $this->unReplaceSpecialChars($resultData);
				
				$data = simplexml_load_string($resultData);
				
				if ($admin_debugging == '1'){
					apicenter_logs($projectId, "projectcontrol", "Person - Debtor: ". var_export($xml_array, true), false);
					apicenter_logs($projectId, "projectcontrol", "Person - Debtor - data: ". var_export($data, true), false);
				}
				
				if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
					$debtorData = $data->$afasDebtorConnector;
					$debtorId = $debtorData->DebtorId;
					if($debtorId != ''){
						return (string)$debtorId;
					}
				} 
				elseif($type == 'email' && $orgPerType == 'Organisatie'){
					return $this->checkAfasCustomer($projectId, $customerData, $type, 'Persoon');
				}
			}
		} elseif($type == 'email' && $orgPerType == 'Organisatie'){
			return $this->checkAfasCustomer($projectId, $customerData, $type, 'Persoon');
		}
		return false;
	}
	
	function createAfasCustomer($projectId, $customerData, $ordernumber = "", $orderData = array()){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		
		if($customerData['company'] != '' && $cms != 'cscart'){
			return $this->createAfasCustomerOrg($projectId, $customerData, $ordernumber, $orderData);
		} else {
			return $this->createAfasCustomerPerson($projectId, $customerData, $ordernumber, $orderData);
		}
		return false;
	}
	
	function split_street($streetStr) {
		$aMatch         = array();
		$pattern        = '#^([\w[:punct:] ]+) ([0-9]{1,5})([\w[:punct:]\-/]*)$#';
		$matchResult    = preg_match($pattern, $streetStr, $aMatch);
		 
		$street         = (isset($aMatch[1])) ? $aMatch[1] : '';
		$number         = (isset($aMatch[2])) ? $aMatch[2] : '';
		$numberAddition = (isset($aMatch[3])) ? $aMatch[3] : '';
		
		return array('street' => $street, 'number' => $number, 'numberAddition' => $numberAddition);
	}
	
	function createAfasCustomerPerson($projectId, $customerData, $ordernumber = "", $orderData = array()){
		$xmlCustomer = new SimpleXMLElement("<KnSalesRelationPer></KnSalesRelationPer>");
		$xmlCustomer->addAttribute("xsi:somename", "somevalue", 'http://www.w3.org/2001/XMLSchema-instance');
		$salesRelationElement = $xmlCustomer->addChild('Element');
		$salesRelationElement->addAttribute('DbId', '');
		$fields = $salesRelationElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		
		$paymentCondition = 14;
		if($this->Projects_model->getValue('afas_customers_payment_condition', $projectId) != ''){
			$paymentCondition = $this->Projects_model->getValue('afas_customers_payment_condition', $projectId);
		}
		
		$controlAccount = 1400;
		if($this->Projects_model->getValue('afas_customers_control_account', $projectId) != ''){
			$controlAccount = $this->Projects_model->getValue('afas_customers_control_account', $projectId);
		}
		
		if(!empty(json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true))){
			$defaultFields = json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true);
			$codes = $defaultFields['code'];
			foreach($codes as $index => $code){
				$value = $defaultFields['waarde'][$index];
				if($value != ''){
					$fields->$code = $value;
				}
			}
		}
		
		$fields->CuId = 'EUR';
		$fields->InPv = 'A';
		$fields->DeCo = '0';
		$fields->IsDb = 1;
		$fields->PaCd = $paymentCondition;
		$fields->ColA = $controlAccount;

		if(isset($customerData['vat_number']) && $customerData['vat_number'] != ''){
			$fields->VaId = $customerData['vat_number'];
		}
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setCustomerParams')){
				$this->$projectModel->setCustomerParams($fields, $customerData, $ordernumber, $orderData);
			}
		}
		
		$objectsElement = $salesRelationElement->addChild('Objects');
		$knPerson = $objectsElement->addChild('KnPerson');
		$knPersonElement = $knPerson->addChild('Element');
		$knPersonFields = $knPersonElement->addChild('Fields');
		$knPersonFields->addAttribute('Action', 'insert');
		$knPersonFields->PadAdr = 1;
		$knPersonFields->AutoNum = 1;
// 		$knPersonFields->MatchPer = 6;
		$knPersonFields->MatchPer = 7;
		$knPersonFields->SeNm = substr($customerData['first_name'].' '.$customerData['last_name'], 0, 9);
		$knPersonFields->FiNm = $customerData['first_name'];
		$knPersonFields->LaNm = $customerData['last_name'];
		$knPersonFields->EmA2 = $customerData['email'];
		$knPersonFields->EmAd = $customerData['email'];
		$knPersonFields->TeNr = $customerData['phone'];
		$knPersonFields->Corr = 1;
		$knPersonFields->AddToPortal = 0;
		$knPersonFields->ViGe = 'O';

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setCustomerOrganisationParams')){
				$this->$projectModel->setCustomerOrganisationParams($knPersonFields, $customerData, $ordernumber, $orderData);
			}
		}
		
		// Billing address
		/*
		$street = $customerData['address1'];
		$street = str_replace("
", ' ', $street);
		$street = str_replace("ß", 'ss', $street);
		$streetData = $this->split_street($street);
		$street = $streetData['street'];
		$homeNumber = $streetData['number'];
		$homeNumberAddition = $streetData['numberAddition'];
		*/
		
		$street = $customerData['address1'];
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		if(!is_numeric($homeNumber)){
			$matches = array();
			preg_match('/([0-9]+)([^0-9]+)/',$homeNumber,$matches);
			$homeNumber = isset($matches[1]) ? $matches[1] : '';
			$homeNumberAddition = isset($matches[2]) ? $matches[2] : '';
		}
/*
		$homeNumber = explode('-', $homeNumber);
		$homeNumber = $homeNumber[0];
*/
		$street = implode(' ', $street);
		$street = str_replace(',', '', $street);

		$magentoCountryId = $customerData['country'];
		$countryAfasCodes = array("AT" => "A", "AE" => "AE", "AF" => "AFG", "AG" => "AG", "AI" => "AIA", "AL" => "AL", "AM" => "AM", 
		"AO" => "AN", "AD" => "AND", "SA" => "AS", "AS" => "ASM", "AQ" => "ATA", "TF" => "ATF", "AU" => "AUS", "AW" => "AW", "AX" => "AX", "AZ" => "AZ", "BE" => "B", "BA" => "BA", "BD" => "BD", "BB" => "BDS", "BG" => "BG", "BZ" => "BH", "BL" => "BL", "BM" => "BM", "BO" => "BOL", "BQ" => "BQ", "BR" => "BR", "BH" => "BRN", "BN" => "BRU", "BS" => "BS", "BT" => "BT", "BF" => "BU", "MM" => "BUR", "BV" => "BVT", "BY" => "BY", "CU" => "C", "CC" => "CCK", "CA" => "CDN", "CH" => "CH", "CI" => "CI", "LK" => "CL", "CN" => "CN", "CO" => "CO", "CK" => "COK", "CR" => "CR", "CV" => "CV", "CW" => "CW", "CX" => "CXR", "CY" => "CY", "KY" => "CYM", "CZ" => "CZ", "DE" => "D", "DJ" => "DJI", "DK" => "DK", "DO" => "DOM", "BJ" => "DY", "DZ" => "DZ", "ES" => "E", "KE" => "EAK", "TZ" => "EAT", "UG" => "EAU", "EC" => "EC", "EE" => "EE", "SV" => "EL", "GQ" => "EQ", "ER" => "ERI", "EH" => "ESH", "EG" => "ET", "ET" => "ETH", "FR" => "F", "FI" => "FIN", "FJ" => "FJI", "LI" => "FL", "FK" => "FLK", "FO" => "FRO", "GA" => "GA", "GB" => "GB", "GT" => "GCA", "GE" => "GE", "GF" => "GF", "GG" => "GG", "GH" => "GH", "GI" => "GIB", "GN" => "GN", "GP" => "GP", "GR" => "GR", "GL" => "GRO", "GU" => "GUM", "GY" => "GUY", "GW" => "GW", "HU" => "H", "HK" => "HK", "JO" => "HKJ", "HM" => "HMD", "HN" => "HON", "HR" => "HR", "IT" => "I", "IL" => "IL", "IM" => "IM", "IN" => "IND", "IO" => "IOT", "IR" => "IR", "IE" => "IRL", "IQ" => "IRQ", "IS" => "IS", "JP" => "J", "JM" => "JA", "JE" => "JE", "KH" => "K", "KG" => "KG", "KI" => "KIR", "KM" => "KM", "KN" => "KN", "KP" => "KO", "KW" => "KWT", "KZ" => "KZ", "LU" => "L", "LA" => "LAO", "LY" => "LAR", "LR" => "LB", "LS" => "LS", "LT" => "LT", "LV" => "LV", "MT" => "M", "MA" => "MA", "MY" => "MAL", "MH" => "MAR", "MC" => "MC", "MD" => "MD", "MX" => "MEX", "MF" => "MF", "FM" => "MIC", "MK" => "MK", "ME" => "MNE", "MP" => "MNP", "MO" => "MO", "MZ" => "MOC", "MN" => "MON", "MQ" => "MQ", "MU" => "MS", "MS" => "MSR", "MV" => "MV", "MW" => "MW", "YT" => "MYT", "NO" => "N", "AN" => "NA", "NC" => "NCL", "NF" => "NFK", "NI" => "NIC", "NU" => "NIU", "NL" => "NL", "NP" => "NPL", "NR" => "NR", "NZ" => "NZ", "UZ" => "OEZ", "OM" => "OMA", "PT" => "P", "PA" => "PA", "PN" => "PCN", "PE" => "PE", "PK" => "PK", "PL" => "PL", "PW" => "PLW", "PG" => "PNG", "PR" => "PR", "PS" => "PSE", "PY" => "PY", "PF" => "PYF", "QA" => "QA", "AR" => "RA", "BW" => "RB", "TW" => "RC", "CF" => "RCA", "CG" => "RCB", "CL" => "RCH", "RE" => "REU", "HT" => "RH", "ID" => "RI", "MR" => "RIM", "LB" => "RL", "MG" => "RM", "ML" => "RMM", "NE" => "RN", "RO" => "RO", "KR" => "ROK", "UY" => "ROU", "PH" => "RP", "SM" => "RSM", "BI" => "RU", "RU" => "RUS", "RW" => "RWA", "SE" => "S", "SB" => "SB", "SZ" => "SD", "SG" => "SGP", "GS" => "SGS", "SH" => "SHN", "SJ" => "SJM", "SK" => "SK", "SI" => "SLO", "SR" => "SME", "SN" => "SN", "SO" => "SP", "PM" => "SPM", "RS" => "SRB", "SS" => "SS", "ST" => "ST", "SD" => "SUD", "NA" => "SWA", "SX" => "SX", "SC" => "SY", "SY" => "SYR", "TH" => "T", "TJ" => "TAD", "CM" => "TC", "TC" => "TCA", "TG" => "TG", "TK" => "TKL", "TL" => "TLS", "TM" => "TMN", "TN" => "TN", "TO" => "TO", "TR" => "TR", "TD" => "TS", "TT" => "TT", "TV" => "TV", "UA" => "UA", "UM" => "UMI", "US" => "USA", "VA" => "VAT", "VG" => "VGB", "VI" => "VIR", "VN" => "VN", "VU" => "VU", "GM" => "WAG", "SL" => "WAL", "NG" => "WAN", "DM" => "WD", "GD" => "WG", "LC" => "WL", "WF" => "WLF", "WS" => "WSM", "VC" => "WV", "XK" => "XK", "YE" => "YMN", "YU" => "YU", "VE" => "YV", "ZM" => "Z", "ZA" => "ZA", "CD" => "ZRE", "ZW" => "ZW");
		
		$countryAfasCode = $customerData['country'];
		if(isset($countryAfasCodes[$magentoCountryId])){
			$countryAfasCode = $countryAfasCodes[$magentoCountryId];
		}
		$knPersonObjects = $knPersonElement->addChild('Objects');
		$knBasicAddress = $knPersonObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $customerData['postcode']);
		$knBasicAddressElementFields->Rs = $customerData['city'];
		$knBasicAddressElementFields->ResZip = 0;

		$contactObjectsElement = $knPersonElement->addChild('Objects');
		$knContact = $contactObjectsElement->addChild('KnContact');
		$knContactElement = $knContact->addChild('Element');
		$knContactFields = $knContactElement->addChild('Fields');
		$knContactFields->addAttribute('Action', 'insert');
		$knContactFields->PadAdr = 1;
// 		$knContactFields->ViKc = 3;
		$knContactFields->ViKc = 'AFL';
		$knContactFields->EmAd = $customerData['email'];
		$knContactFields->TeNr = $customerData['phone'];
		
		$data = $xmlCustomer->asXML();
		
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		apicenter_logs($projectId, 'exportorders', 'Create customer with data '.$data, false);
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "KnSalesRelationPer";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			apicenter_logs($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in AFAS. Error: '.$result['faultstring'], true);
			
			return false;
		}
		return true;
	}
	
	function createAfasCustomerOrg($projectId, $customerData, $ordernumber = "", $orderData = array()){
		$xmlCustomer = new SimpleXMLElement("<KnSalesRelationOrg></KnSalesRelationOrg>");
		$salesRelationElement = $xmlCustomer->addChild('Element');
		$salesRelationElement->addAttribute('DbId', '');
		$fields = $salesRelationElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$paymentCondition = 14;
		if($this->Projects_model->getValue('afas_customers_payment_condition', $projectId) != ''){
			$paymentCondition = $this->Projects_model->getValue('afas_customers_payment_condition', $projectId);
		}
		
		$controlAccount = 1400;
		if($this->Projects_model->getValue('afas_customers_control_account', $projectId) != ''){
			$controlAccount = $this->Projects_model->getValue('afas_customers_control_account', $projectId);
		}
		
		if(!empty(json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true))){
			$defaultFields = json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true);
			$codes = $defaultFields['code'];
			foreach($codes as $index => $code){
				$value = $defaultFields['waarde'][$index];
				if($value != ''){
					$fields->$code = $value;
				}
			}
		}
		
		$fields->CuId = 'EUR';
		$fields->InPv = 'A';
		$fields->DeCo = '0';
		$fields->IsDb = 1;
		$fields->PaCd = $paymentCondition;
 		$fields->ColA = $controlAccount;

		if(isset($customerData['vat_number']) && $customerData['vat_number'] != ''){
			$fields->VaId = $customerData['vat_number'];
		}
 		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setCustomerParams')){
				$this->$projectModel->setCustomerParams($fields, $customerData, $ordernumber, $orderData);
			}
		}
		
		$objectsElement = $salesRelationElement->addChild('Objects');
		$knPerson = $objectsElement->addChild('KnOrganisation');
		$knPersonElement = $knPerson->addChild('Element');
		$knPersonFields = $knPersonElement->addChild('Fields');
		$knPersonFields->addAttribute('Action', 'insert');
		$knPersonFields->PadAdr = 1;
		$knPersonFields->AutoNum = 1;
// 		$knPersonFields->MatchOga = 3;
		$knPersonFields->MatchOga = 6;
		$knPersonFields->SeNm = substr($customerData['company'], 0, 9);
		$knPersonFields->Nm = $customerData['company'];
		$knPersonFields->EmAd = $customerData['email'];
		$knPersonFields->TeNr = $customerData['phone'];
		$knPersonFields->Corr = 1;
		$knPersonFields->AddToPortal = 0;

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setCustomerOrganisationParams')){
				$this->$projectModel->setCustomerOrganisationParams($knPersonFields, $customerData, $ordernumber, $orderData);
			}
		}

		/*
		$street = $customerData['address1'];
		$street = str_replace("
", ' ', $street);
		$street = str_replace("ß", 'ss', $street);
		$streetData = $this->split_street($street);
		$street = $streetData['street'];
		$homeNumber = $streetData['number'];
		$homeNumberAddition = $streetData['numberAddition'];
		*/
		
		$street = $customerData['address1'];
		$street = str_replace("
", ' ', $street);
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		if(!is_numeric($homeNumber)){
			$matches = array();
			preg_match('/([0-9]+)([^0-9]+)/',$homeNumber,$matches);
			$homeNumber = $matches[1];
			$homeNumberAddition = $matches[2];
		}
/*
		$homeNumber = explode('-', $homeNumber);
		$homeNumber = $homeNumber[0];
*/
		$street = implode(' ', $street);
		$street = str_replace(',', '', $street);

		$magentoCountryId = $customerData['country'];
		$countryAfasCodes = array("AT" => "A", "AE" => "AE", "AF" => "AFG", "AG" => "AG", "AI" => "AIA", "AL" => "AL", "AM" => "AM", 
		"AO" => "AN", "AD" => "AND", "SA" => "AS", "AS" => "ASM", "AQ" => "ATA", "TF" => "ATF", "AU" => "AUS", "AW" => "AW", "AX" => "AX", "AZ" => "AZ", "BE" => "B", "BA" => "BA", "BD" => "BD", "BB" => "BDS", "BG" => "BG", "BZ" => "BH", "BL" => "BL", "BM" => "BM", "BO" => "BOL", "BQ" => "BQ", "BR" => "BR", "BH" => "BRN", "BN" => "BRU", "BS" => "BS", "BT" => "BT", "BF" => "BU", "MM" => "BUR", "BV" => "BVT", "BY" => "BY", "CU" => "C", "CC" => "CCK", "CA" => "CDN", "CH" => "CH", "CI" => "CI", "LK" => "CL", "CN" => "CN", "CO" => "CO", "CK" => "COK", "CR" => "CR", "CV" => "CV", "CW" => "CW", "CX" => "CXR", "CY" => "CY", "KY" => "CYM", "CZ" => "CZ", "DE" => "D", "DJ" => "DJI", "DK" => "DK", "DO" => "DOM", "BJ" => "DY", "DZ" => "DZ", "ES" => "E", "KE" => "EAK", "TZ" => "EAT", "UG" => "EAU", "EC" => "EC", "EE" => "EE", "SV" => "EL", "GQ" => "EQ", "ER" => "ERI", "EH" => "ESH", "EG" => "ET", "ET" => "ETH", "FR" => "F", "FI" => "FIN", "FJ" => "FJI", "LI" => "FL", "FK" => "FLK", "FO" => "FRO", "GA" => "GA", "GB" => "GB", "GT" => "GCA", "GE" => "GE", "GF" => "GF", "GG" => "GG", "GH" => "GH", "GI" => "GIB", "GN" => "GN", "GP" => "GP", "GR" => "GR", "GL" => "GRO", "GU" => "GUM", "GY" => "GUY", "GW" => "GW", "HU" => "H", "HK" => "HK", "JO" => "HKJ", "HM" => "HMD", "HN" => "HON", "HR" => "HR", "IT" => "I", "IL" => "IL", "IM" => "IM", "IN" => "IND", "IO" => "IOT", "IR" => "IR", "IE" => "IRL", "IQ" => "IRQ", "IS" => "IS", "JP" => "J", "JM" => "JA", "JE" => "JE", "KH" => "K", "KG" => "KG", "KI" => "KIR", "KM" => "KM", "KN" => "KN", "KP" => "KO", "KW" => "KWT", "KZ" => "KZ", "LU" => "L", "LA" => "LAO", "LY" => "LAR", "LR" => "LB", "LS" => "LS", "LT" => "LT", "LV" => "LV", "MT" => "M", "MA" => "MA", "MY" => "MAL", "MH" => "MAR", "MC" => "MC", "MD" => "MD", "MX" => "MEX", "MF" => "MF", "FM" => "MIC", "MK" => "MK", "ME" => "MNE", "MP" => "MNP", "MO" => "MO", "MZ" => "MOC", "MN" => "MON", "MQ" => "MQ", "MU" => "MS", "MS" => "MSR", "MV" => "MV", "MW" => "MW", "YT" => "MYT", "NO" => "N", "AN" => "NA", "NC" => "NCL", "NF" => "NFK", "NI" => "NIC", "NU" => "NIU", "NL" => "NL", "NP" => "NPL", "NR" => "NR", "NZ" => "NZ", "UZ" => "OEZ", "OM" => "OMA", "PT" => "P", "PA" => "PA", "PN" => "PCN", "PE" => "PE", "PK" => "PK", "PL" => "PL", "PW" => "PLW", "PG" => "PNG", "PR" => "PR", "PS" => "PSE", "PY" => "PY", "PF" => "PYF", "QA" => "QA", "AR" => "RA", "BW" => "RB", "TW" => "RC", "CF" => "RCA", "CG" => "RCB", "CL" => "RCH", "RE" => "REU", "HT" => "RH", "ID" => "RI", "MR" => "RIM", "LB" => "RL", "MG" => "RM", "ML" => "RMM", "NE" => "RN", "RO" => "RO", "KR" => "ROK", "UY" => "ROU", "PH" => "RP", "SM" => "RSM", "BI" => "RU", "RU" => "RUS", "RW" => "RWA", "SE" => "S", "SB" => "SB", "SZ" => "SD", "SG" => "SGP", "GS" => "SGS", "SH" => "SHN", "SJ" => "SJM", "SK" => "SK", "SI" => "SLO", "SR" => "SME", "SN" => "SN", "SO" => "SP", "PM" => "SPM", "RS" => "SRB", "SS" => "SS", "ST" => "ST", "SD" => "SUD", "NA" => "SWA", "SX" => "SX", "SC" => "SY", "SY" => "SYR", "TH" => "T", "TJ" => "TAD", "CM" => "TC", "TC" => "TCA", "TG" => "TG", "TK" => "TKL", "TL" => "TLS", "TM" => "TMN", "TN" => "TN", "TO" => "TO", "TR" => "TR", "TD" => "TS", "TT" => "TT", "TV" => "TV", "UA" => "UA", "UM" => "UMI", "US" => "USA", "VA" => "VAT", "VG" => "VGB", "VI" => "VIR", "VN" => "VN", "VU" => "VU", "GM" => "WAG", "SL" => "WAL", "NG" => "WAN", "DM" => "WD", "GD" => "WG", "LC" => "WL", "WF" => "WLF", "WS" => "WSM", "VC" => "WV", "XK" => "XK", "YE" => "YMN", "YU" => "YU", "VE" => "YV", "ZM" => "Z", "ZA" => "ZA", "CD" => "ZRE", "ZW" => "ZW");
		$countryAfasCode = $customerData['country'];
		if(isset($countryAfasCodes[$magentoCountryId])){
			$countryAfasCode = $countryAfasCodes[$magentoCountryId];
		}
		$knPersonObjects = $knPersonElement->addChild('Objects');
		$knBasicAddress = $knPersonObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $customerData['postcode']);
		$knBasicAddressElementFields->Rs = $customerData['city'];
		$knBasicAddressElementFields->ResZip = 0;
		
		$contactObjectsElement = $knPersonElement->addChild('Objects');
		$knContact = $contactObjectsElement->addChild('KnContact');
		$knContactElement = $knContact->addChild('Element');
		$knContactFields = $knContactElement->addChild('Fields');
		$knContactFields->addAttribute('Action', 'insert');
		$knContactFields->PadAdr = 1;
// 		$knContactFields->ViKc = 3;
		$knContactFields->ViKc = 'PRS';
		
		$contactPersonObjectsElement = $knContactElement->addChild('Objects');
		$knContact = $contactPersonObjectsElement->addChild('KnPerson');
		$knContactElement = $knContact->addChild('Element');
		$knContactFields = $knContactElement->addChild('Fields');
		$knContactFields->addAttribute('Action', 'insert');
		$knContactFields->PadAdr = 1;
		$knContactFields->AutoNum = 1;
// 		$knContactFields->MatchPer = 6;
		$knContactFields->MatchPer = 0;
		$knContactFields->SeNm = substr($customerData['first_name'].' '.$customerData['last_name'], 0, 9);
		$knContactFields->FiNm = $customerData['first_name'];
		$knContactFields->LaNm = $customerData['last_name'];
		$knContactFields->EmA2 = $customerData['email'];
		$knContactFields->EmAd = $customerData['email'];
		$knContactFields->TeNr = $customerData['phone'];
		$knContactFields->Corr = 1;
		$knContactFields->AddToPortal = 0;
		$knContactFields->ViGe = 'O';
		
		$data = $xmlCustomer->asXML();
		
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		apicenter_logs($projectId, 'exportorders', 'Create customer with data '.$data, false);
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "KnSalesRelationOrg";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			apicenter_logs($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in AFAS. Error: '.$result['faultstring'], true);
			return false;
		}
		return true;	
	}
	
	function getDebtors($projectId, $offset = 0, $amount = 10){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasDebtorConnector = $this->Projects_model->getValue('afas_customers_connector', $projectId);
		/*** added Optiply 20-06 ***/
		$cms = $this->Projects_model->getValue('cms', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasDebtorConnector;
		$xml_array['filtersXml'] = "";
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="DebtorId" OperatorType="0" /></Index></options>';
		
		$err = $client->getError();
		if ($err) {
		    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    exit();
		}
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);
		
		//if ($projectId == 85) log_message('debug', 'ProductData-org2 85'  . var_export($resultData, true)) ;

		$data = simplexml_load_string($resultData);
		$counter = 0;
		
		if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
		    
		    
		    
			$results = array();
			foreach($data->$afasDebtorConnector as $customer){
				$counter++;
				$afasCustomerData = $this->xml2array($customer);
				
				 //if ($projectId == 85) log_message('debug', 'ProductData 85 ' . var_export($afasCustomerData, true));
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'getCustomerData')){
						$afasCustomerData = $this->$projectModel->getCustomerData($afasCustomerData);
					}
				}
				if(isset($afasCustomerData['Blocked']) && $afasCustomerData['Blocked'] == true && $afasCustomerData['Blocked'] != 'false'){
					continue;
				}

				$customerName = explode(' ', $afasCustomerData['DebtorName']);
				$customerFirstName = $customerName[0];
				unset($customerName[0]);
				$customerLastName = implode(' ', $customerName);
				if($customerLastName == ''){
					$customerLastName = '_';
				}
				$address = explode('  ', $afasCustomerData['AdressLine3']);
				$postcode = $address[0];
				$city = isset($address[1]) ? $address[1] : '';
				if(!isset($afasCustomerData['AdressLine4'])){
					$country = 'NL';
				} else {
					$country = trim($afasCustomerData['AdressLine4']);
					if($country == 'België'){
						$country = 'BE';
					}
				}
				$email = isset($afasCustomerData['Email']) ? $afasCustomerData['Email'] : '';
				$phone = isset($afasCustomerData['TelNr']) ? $afasCustomerData['TelNr'] : '';
				
				$customerData = array(
					'email' => $email,
					'first_name' => $customerFirstName,
					'last_name' => $customerLastName,
					'address' => $afasCustomerData['AdressLine1'],
					'country' => $country,
					'postcode' => $postcode,
				);
				if($phone != ''){
					$customerData['phone'] = $phone;
				}
				if($city != ''){
					$customerData['city'] = $city;
				}
				if($email == ''){
					continue;
				}
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'getCustomerDataAfter')){
						$customerData = $this->$projectModel->getCustomerDataAfter($customerData, $afasCustomerData, $projectId);
					}
				}
				
				$this->Cms_model->createCustomer($projectId, $customerData);
			}
		}
		return $counter;
	}
	
	function getCustomCategory($connector, $code, $parent = '', $projectId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connector;
		$xml_array['filtersXml'] = "";
		if($parent != ''){
			$xml_array['filtersXml'] = '<Filters><Filter FilterId="Filter1"><Field FieldId="ID" OperatorType="1">'.$parent.'</Field></Filter></Filters>';
		}
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$err = $client->getError();
		if ($err) {
		    echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		    echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    exit();
		}
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);

		$data = simplexml_load_string($resultData);
		return $data->$connector;
	}

	// this function is used to create contacts in afas called through webhooks.
	function postOrPatchOrgCustomer($data, $projectId){
		if($projectId!=''){
        	$this->load->model('Mailchimp_model');
			$customerData = $data['data'];
			//log_message('debug', 'Update mailchimp');
			//log_message('debug', var_export($data, true));
			if($data['type']=='subscribe'){
				// called if the contact is created in mailchimp
				// $result = $this->postAfasKnOrg($projectId, $customerData);
				// if($result){
					//$this->Mailchimp_model->updateIntoMailchimp($result, $projectId);
					return true;
				//}
				//return false;
			} else if($data['type']=='profile'){
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
                    $this->load->model($projectModel);
                    $customerData = $this->$projectModel->getcustomerData($customerData);
                }
				// called if contact is modified in mailchimp.
				$this->patchOrgContact($projectId,$customerData);
				// $this->patchAfasKnPer($projectId,$customerData);
				// $this->patchAfasKnOrg($projectId,$customerData);
				$updated_org = $this->getCustomer($projectId, 0, 10, $customerData['merges']['ORGNUMBER']);
				if($updated_org['numberOfResults']>0){
					$this->Mailchimp_model->importIntoMailchimp($updated_org['customerData'], $projectId, 'update');
				}
				return true;
			} else if($data['type']=='unsubscribe'){
				if($customerData['action']=='delete'){ 
					return true;
				}
			}
		}
		return false;
	}

	// this method is used for updating the afas address 
	function patchOrgContact($projectId, $customerData){
    	$this->load->model('Projects_model');
    	$contact_data 			= $customerData['merges'];
		$afasEnvironment 		= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 		= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 				= $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl 			= $this->Projects_model->getValue('afas_update_url', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$xmlCustomer			= new SimpleXMLElement("<KnContact></KnContact>");
		$knOrganisationElement 	= $xmlCustomer->addChild('Element');
		$knOrganisationFields 	= $knOrganisationElement->addChild('Fields');
		$knOrganisationFields->addAttribute('Action', 'update');

		$knOrganisationFields->BcCoOga = $contact_data['ORGNUMBER'];
        $knOrganisationFields->CdId = $contact_data['CONTACTID'];
        $knOrganisationFields->ViKc = $contact_data['TYPE'];
        $knOrganisationFields->PadAdr = 1;
		$knOrganisationFields->EmAd = $customerData['email'];
		
		if (isset($contact_data['DIGITALE_K']) && !empty($contact_data['DIGITALE_K']['key'])) {
            $knOrganisationFields->$contact_data['DIGITALE_K']['key'] = $contact_data['DIGITALE_K']['val'];
        }

        if (isset($contact_data['HR_NIEUWSB']) && !empty($contact_data['HR_NIEUWSB']['key'])) {
            $knOrganisationFields->$contact_data['HR_NIEUWSB']['key'] = $contact_data['HR_NIEUWSB']['val'];
        }

        if (isset($contact_data['OR_NIEUWSB']) && !empty($contact_data['OR_NIEUWSB']['key'])) {
            $knOrganisationFields->$contact_data['OR_NIEUWSB']['key'] = $contact_data['OR_NIEUWSB']['val'];
        }

        if (isset($contact_data['OVERIGE_NI']) && !empty($contact_data['OVERIGE_NI']['key'])) {
            $knOrganisationFields->$contact_data['OVERIGE_NI']['key'] = $contact_data['OVERIGE_NI']['val'];
        }

        if (isset($contact_data['EOR_NIEUWS']) && !empty($contact_data['EOR_NIEUWS']['key'])) {
            $knOrganisationFields->$contact_data['EOR_NIEUWS']['key'] = $contact_data['EOR_NIEUWS']['val'];
        }

        if (isset($contact_data['RABOBANK_N']) && !empty($contact_data['RABOBANK_N']['key'])) {
            $knOrganisationFields->$contact_data['RABOBANK_N']['key'] = $contact_data['RABOBANK_N']['val'];
        }

		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] 	= $afasEnvironmentId;
		$xml_array['token'] 			= $afasToken;
		$xml_array['connectorType'] 	= "KnContact";
		$xml_array['connectorVersion'] 	= 1;
		$xml_array['dataXml'] 			= $data;
	
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);

		//log_message('debug', 'AFAS updated is true resutl');
		//log_message('debug', 'result'. var_export($result, true));
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
  			project_error_log($projectId, 'importcustomers', $result['faultcode'] . " : ".$result['faultstring'] );
			return false;
		}
		//log_message('debug', 'AFAS updated is true');
		apicenter_logs($projectId, 'importcustomers', 'Success:  Customer  and ContactId '.$contact_data['CONTACTID'].' updated successfully. ', false);
		
		return true;	
	}

	function patchAfasKnPer($projectId, $customerData){
		$this->load->model('Projects_model');
		$afasEnvironment 		= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 		= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 				= $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl 			= $this->Projects_model->getValue('afas_update_url', $projectId);

		$contact_data 			= $customerData['merges'];
		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$xmlCustomer			= new SimpleXMLElement("<KnPerson></KnPerson>");
		$knOrganisationElement 	= $xmlCustomer->addChild('Element');
		$knOrganisationFields 	= $knOrganisationElement->addChild('Fields');
		$knOrganisationFields->addAttribute('Action', 'update');

		$knOrganisationFields->BcCo = $contact_data['PERNUMBER'];
		$knOrganisationFields->FiNm = $contact_data['FIRSTNAME'];
		$knOrganisationFields->LaNm = $contact_data['LASTNAME'];
		$knOrganisationFields->MatchPer = 0;
		
		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] 	= $afasEnvironmentId;
		$xml_array['token'] 			= $afasToken;
		$xml_array['connectorType'] 	= "KnPerson";
		$xml_array['connectorVersion'] 	= 1;
		$xml_array['dataXml'] 			= $data;

		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode']) && $result['faultcode'] != ''){
  			project_error_log($projectId, 'importcustomers', $result['faultcode'] . " : ".$result['faultstring'] );
			return false;
		}
		return true;
	}

	function patchAfasKnOrg($projectId, $customerData){
		$this->load->model('Projects_model');
		$afasEnvironment 		= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 		= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 				= $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl 			= $this->Projects_model->getValue('afas_update_url', $projectId);

		$contact_data = $customerData['merges'];
		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$xmlCustomer			= new SimpleXMLElement("<KnOrganisation></KnOrganisation>");
		$knOrganisationElement 	= $xmlCustomer->addChild('Element');
		$knOrganisationFields 	= $knOrganisationElement->addChild('Fields');
		$knOrganisationFields->addAttribute('Action', 'update');

		$knOrganisationFields->PadAdr 	= 1;
		$knOrganisationFields->MatchOga = 0;
		$knOrganisationFields->BcCo 	= $contact_data['ORGNUMBER'];
		$knOrganisationFields->Nm 		= $contact_data['NAME'];
	
		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] 	= $afasEnvironmentId;
		$xml_array['token'] 			= $afasToken;
		$xml_array['connectorType'] 	= "KnOrganisation";
		$xml_array['connectorVersion'] 	= 1;
		$xml_array['dataXml'] 			= $data;

		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
  			project_error_log($projectId, 'importcustomers', $result['faultcode'] . " : ".$result['faultstring'] );
			return false;
		} 
		return true;	
	}

	// this method is used to fetch contacts data from afas for mailchimp
	function getCustomer($projectId, $offset=0, $amount=10,$filtersXML=''){
		// get and set all the token for creating afas connection.
    	$this->load->model('Projects_model');
		$afasEnvironment 	= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 	= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasCustomersConnector = $this->Projects_model->getValue('afas_customers_connector', $projectId);
		$afasToken 			= $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl 		= $this->Projects_model->getValue('afas_get_url', $projectId);
		$contact_type 		= $this->Projects_model->getValue('contact_type', $projectId)?$this->Projects_model->getValue('contact_type', $projectId):'contacts'; 

		$totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
		$totalCustomerImportError 	= $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;
		$cms = $this->Projects_model->getValue('cms', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');

		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
	 	if($filtersXML!=''){
	 		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="OrgNumber" OperatorType="1">'.$filtersXML.'</Field></Filter></Filters>';
	 		$offset=-1;
			$amount=-1;
	 	} 
	 	
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] 		= $afasToken;
		$xml_array['connectorId'] 	= $afasCustomersConnector;  //'Profit_Contacts_App';
		$xml_array['filtersXml'] 	= $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take></options>';
		$err = $client->getError();
		if ($err) {
		    project_error_log($projectId, 'importcustomers', htmlspecialchars($client->getDebug(), ENT_QUOTES) );
            $totalCustomerImportError++;
            $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
            return false;
		}
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])){
            project_error_log($projectId, 'importcustomers', $result['faultcode'] . " : ".$result['faultstring'] );
            $totalCustomerImportError++;
            $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
            return false;
		}
		else {
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = $this->replaceSpecialChars($resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData = $this->unReplaceSpecialChars($resultData);
			$data 		= simplexml_load_string($resultData);
			$customer_data = array();
			// create data as per required in mailchimp.
			if(!empty($data)){
				$all_count  = count($data);
				if ($cms != 'mailchimp') {
					$customer_data = $this->formCustomerFormatForMailchimp($data, $contact_type);
				} else {
					$customer_data = $data;
				}
			} else{
				$all_count = 0;
			}
			// return with data.
			return array('numberOfResults' => $all_count, 'customerData' => $customer_data );
		}
	}

	function formCustomerFormatForMailchimp($data, $contact_type = 'contacts'){
		$customer_data = array();

		if($contact_type=='contacts'){
			foreach ($data as $key => $value) {
				$each_data 	= array();
				$value 		= json_decode( json_encode($value) , 1);
				$TYPE 		= isset($value['Type'])?$value['Type']:'';
				$CONTACTID  = isset($value['ContactId'])?$value['ContactId']:'';
				$NAME  		= isset($value['Name'])?$value['Name']:'';
				$ORGNUMBER 	= isset($value['OrgNumber'])?$value['OrgNumber']:'';
				$PERNUMBER 	= isset($value['PerNumber'])?$value['PerNumber']:'';
				$MAILWORK 	= isset($value['MailWork'])?$value['MailWork']:'';

				$name   			= '';
				$first_name 		= '';
				$last_name 			= '';
				if($NAME!=''){
					$name_array = explode(' - ', $NAME);
					if(isset($name_array['0']))
						$name       = $name_array['0'];
					else
						$name 		= '';
					$name_array 		 = array_reverse($name_array);
					if(isset($name_array['0']))
						$full_name  = $name_array['0'];
					else
						$full_name  = '';
					if($full_name!=''){
						$full_name_array	= explode(' ', $full_name);
						if(isset($full_name_array['0'])){
							$first_name      = $full_name_array['0'];
							unset($full_name_array['0']);
							$rev_array 		 = array_reverse($full_name_array);
							if(isset($rev_array['0']))
								$last_name = $rev_array['0'];
						}
					}
				}
				$email_address = $MAILWORK;
				if($email_address== '' || $CONTACTID == '' || $ORGNUMBER == '' || $PERNUMBER == '')
					continue;
				$each_data['merge_fields'] 	= ['TYPE'=>$TYPE, 'CONTACTID'=>$CONTACTID,'NAME'=>$name,'FIRSTNAME'=>$first_name,'LASTNAME'=>$last_name,'ORGNUMBER'=>$ORGNUMBER,'PERNUMBER'=>$PERNUMBER];
				$each_data['email_address'] = $email_address;
				$each_data['status']  		= 'subscribed';
				$customer_data[] 			= $each_data;
			}
		} else{
			foreach ($data as $key => $value) {
				$each_data 	= array();
				$value 		= json_decode( json_encode($value) , 1);
				$DEBTORID   = isset($value['DebtorId'])?$value['DebtorId']:'';
				$DEBTORNAME = isset($value['DebtorName'])?$value['DebtorName']:'';
				$BCCO  		= isset($value['BcCo'])?$value['BcCo']:'';
				$CREATEDATE = isset($value['CreateDate'])?$value['CreateDate']:'';
				$MODIFIEDDATE 	= isset($value['ModifiedDate'])?$value['ModifiedDate']:'';
				$EMAIL 			= isset($value['Email'])?$value['Email']:'';
				$STATUSRELATIE 	= isset($value['Status_relatie'])?$value['Status_relatie']:'';
 
				$email_address = $EMAIL;
				if($email_address== '')
					continue;
				$each_data['merge_fields'] 	= ['DEBTORID'=>$DEBTORID, 'DEBTORNAME'=>$DEBTORNAME,'BCCO'=>$BCCO,'CREATEDATE'=>$CREATEDATE,'MODIFIDATE'=>$MODIFIEDDATE,'STATSRELAT'=>$STATUSRELATIE];
				$each_data['email_address'] = $email_address;
				$each_data['status']  		= 'subscribed';
				$customer_data[] 			= $each_data;
			}
		}
		return $customer_data;
	}
	
	
	public function addFiEntries($projectId, $data, $mainData = array()){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$xmlOrder = new SimpleXMLElement("<FiEntryPar></FiEntryPar>");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$fields->Year = date('Y');
		$fields->Perio = intval(date('m'));
		// Administratiecode
		$fields->UnId = 7;
		$fields->JoCo = 80;
		// $fields->JoCo = 74;

		if(!empty($mainData)){
			foreach($mainData as $code => $value){
				$fields->$code = $value;
			}
		}
		
		$objectsElement = $orderElement->addChild('Objects');
		$FbSalesLines = $objectsElement->addChild('FiEntries');
		
		// Add items
		foreach($data as $item){
			$element = $FbSalesLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			$fields->VaAs = 1;
			$fields->AcNr = $item['grootboek'];
			$fields->EnDa = date('Y-m-d');
			$fields->BpDa = date('Y-m-d');
			if(isset($item['omschrijving'])){
				$fields->Ds = $item['omschrijving'];
			}
			if(isset($item['afletterReferentie'])){
				$fields->InId = $item['afletterReferentie'];
				$fields->Mref = $item['afletterReferentie'];
			} else {
				//$fields->InId = 'KBM-'.date('Ymd');
			}
			if(isset($item['btwCode'])){
				$fields->VaId = $item['btwCode'];
			}
			$fields->AmCr = isset($item['credit']) ? $item['credit'] : 0;
			$fields->AmDe = isset($item['debet']) ? $item['debet'] : 0;
// 			$fields->BpNr = 'KBM-'.date('Ymd');
			if(isset($item['verbijzonderingsCode'])){
				$objects = $element->addChild('Objects');
				$FiDimEntries = $objects->addChild('FiDimEntries');
				$FiDimElement = $FiDimEntries->addChild('Element');
				$FiDimFields = $FiDimElement->addChild('Fields');
				$FiDimFields->addAttribute('Action', 'insert');
				$FiDimFields->DiC1 = $item['verbijzonderingsCode'];
				$FiDimFields->AmCr = isset($item['credit']) ? $item['credit'] : 0;
				$FiDimFields->AmDe = isset($item['debet']) ? $item['debet'] : 0;
			}
			
			if(isset($item['customFields']) && !empty($item['customFields'])){
				foreach($item['customFields'] as $code => $value){
					$fields->$code = $value;
				}
			}
		}
		
		$data = $xmlOrder->asXML();
		
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
	
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "FiEntries";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		//echo '<pre>';print_r($result);exit;
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			apicenter_logs($projectId, 'exportorders', 'Could not export orders for date '.date('Y-m-d').' to AFAS. Error: '.$result['faultstring'], true);
			return false;
		} else {
			apicenter_logs($projectId, 'exportorders', 'Exported orders for date '.date('Y-m-d').' to AFAS.', false);
		}
		return true;
	}

	public function addDirectInvoice($projectId, $orderData){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);

		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);
// echo '<pre>';print_r($customerData);exit;
		
		if(!$debtorId = $this->checkAfasCustomerExists($projectId, $customerData, $orderData['id'])){
			apicenter_logs($projectId, 'exportorders', 'Could not find/add customer to AFAS', true);
			return false;
		}
		
		$xmlOrder = new SimpleXMLElement("<FbDirectInvoice></FbDirectInvoice>");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$fields->DbId = $debtorId;
		$fields->RfCs = 'Order '.$orderData['id'];
		$fields->War = '*****';
		
		if(isset($orderData['PaTp']) && $orderData['PaTp'] != ''){
			$fields->PaTp = $orderData['PaTp'];
		}
		if(isset($orderData['PaCd']) && $orderData['PaCd'] != ''){
			$fields->PaCd = $orderData['PaCd'];
		}

		$deliveryAddress = $this->addDeliveryAddress($orderData, $debtorId, $projectId);
		if($deliveryAddress != false){
			$fields->DlAd = $deliveryAddress;
		}
		
		$objectsElement = $orderElement->addChild('Objects');
		$FbSalesLines = $objectsElement->addChild('FbDirectInvoiceLines');
		
		// Add items
		foreach($orderData['order_products'] as $item){
			$element = $FbSalesLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			$fields->VaIt = 2;
			$fields->ItCd = $item['model'];
			$fields->QuUn = $item['quantity'];
			$price = $item['total_price'];
			$fields->Upri = round($price, 2);
			if(isset($item['vat_group']) && $item['vat_group'] != ''){
				$fields->VaRc = $item['vat_group'];
			}
// 			$fields->InVa = 1; // Prijs is incl BTW
		}

		// Shipping
		$shippingSku = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if($shippingSku != '' && $shippingSku != ' '){
			if(isset($orderData['totals']) && isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0){
				$element = $FbSalesLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
				$fields->VaIt = 2;
				$fields->ItCd = $shippingSku;
				$fields->QuUn = 1;
				$price = $orderData['totals']['shipping'];
				$fields->Upri = round($price, 2);
			}
		}
		
		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "FbDirectInvoice";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
// 		echo '<pre>';print_r($result);exit;
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			apicenter_logs($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to AFAS. Error: '.$result['faultstring'], true);
			return false;
		} else {
			apicenter_logs($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to AFAS.', false);
		}
		return true;
	}

	public function postOrPatchOrgDebtor($data, $projectId){
		if($projectId!=''){
        	$this->load->model('Mailchimp_model');
			$customerData = $data['data'];			
			if($data['type']=='subscribe'){
					return true;
			} else if($data['type']=='profile'){  
				// called if contact is modified in mailchimp.
				$this->patchOrgDebtor($projectId,$customerData);
				// $updated_org = $this->getCustomer($projectId, 0, 10, $customerData['merges']['ORGNUMBER']);
				// if($updated_org['numberOfResults']>0){
				// 	$this->Mailchimp_model->importIntoMailchimp($updated_org['customerData'], $projectId, 'update');
				// }
				return true;
			} else if($data['type']=='unsubscribe'){
				if($customerData['action']=='delete'){ 
					return true;
				}
			}
		}
		return false;
	}

	// this method is used for updating the afas address 
	public function patchOrgDebtor($projectId, $customerData){

    	$this->load->model('Projects_model');
    	$contact_data 			= $customerData['merges'];
		$afasEnvironment 		= $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId 		= $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken 				= $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl 			= $this->Projects_model->getValue('afas_update_url', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$this->load->helper('tools');
		$this->load->helper('constants');
		$xmlCustomer				= new SimpleXMLElement("<KnSalesRelationOrg></KnSalesRelationOrg>");
		$KnSalesRelationOrgElement 	= $xmlCustomer->addChild('Element');
		$KnSalesRelationOrgElement->addAttribute('DbId',$customerData['merges']['DEBTORID']);
		$KnSalesRelationOrgFields 	= $KnSalesRelationOrgElement->addChild('Fields');
		$KnSalesRelationOrgFields->addAttribute('Action', 'update');
		$objectsElement = $KnSalesRelationOrgElement->addChild('Objects');
		$knOrganisation = $objectsElement->addChild('KnOrganisation');
		$knOrganisationElement 	= $knOrganisation->addChild('Element');
		$knOrganisationElementFields 	= $knOrganisationElement->addChild('Fields');
		$knOrganisationElementFields->addAttribute('Action', 'update');
		$knOrganisationElementFields->BcCo = $customerData['merges']['BCCO'];
		$knOrganisationElementFields->PadAdr 	= 1;
		$knOrganisationElementFields->MatchOga = 0;
		$knOrganisationElementFields->Nm 		= $customerData['merges']['DEBTORNAME'];
		$knOrganisationElementFields->EmAd 		= $customerData['merges']['EMAIL'];
		// $knOrganisationObjects = $knOrganisation->addChild('Objects');
		// $knOrganisationObjectsKnContact = $knOrganisationObjects->addChild('KnContact');
		// $knOrganisationObjectsKnContactElement = $knOrganisationObjectsKnContact->addChild('Element');


		// $knOrganisationFields->CdId 	= $contact_data['CONTACTID'];
		// $knOrganisationFields->ViKc 	= 'PRS';
		// $knOrganisationFields->PadAdr   = 1;
		// $knOrganisationFields->EmAd 	= $customerData['email'];
		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] 	= $afasEnvironmentId;
		$xml_array['token'] 			= $afasToken;
		$xml_array['connectorType'] 	= "KnSalesRelationOrg";
		$xml_array['connectorVersion'] 	= 1;
		$xml_array['dataXml'] 			= $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
  			project_error_log($projectId, 'importcustomers', $result['faultcode'] . " : ".$result['faultstring'] );
			return false;
		}
		
		apicenter_logs($projectId, 'importcustomers', 'Updated customesr ' . $result['faultcode'], false);
		
		return true;	
	}
	
	public function replaceSpecialChars($string){
		$string = utf8_encode($string);
		$string = str_replace('ø', 'o???', $string);
		$string = str_replace('Ø', 'O???', $string);
		$string = str_replace('ô', 'oo???', $string);
		$string = str_replace('Ô', 'OO???', $string);
		$string = str_replace('å', 'ao???', $string);
		$string = str_replace('Å', 'AO???', $string);
		$string = str_replace('æ', 'a???', $string);
		$string = str_replace('Æ', 'A???', $string);
		$string = str_replace('ë', 'ee???', $string);
		$string = str_replace('Ë', 'EE???', $string);
		return $string;
	}
	
	public function unReplaceSpecialChars($string){
		$string = str_replace('oo???', 'ô', $string);
		$string = str_replace('OO???', 'Ô', $string);
		$string = str_replace('ao???', 'å', $string);
		$string = str_replace('AO???', 'Å', $string);
		$string = str_replace('a???', 'æ', $string);
		$string = str_replace('A???', 'Æ', $string);
		$string = str_replace('o???', 'ø', $string);
		$string = str_replace('O???', 'Ø', $string);
		$string = str_replace('ee???', 'ë', $string);
		$string = str_replace('EE???', 'Ë', $string);
		return $string;
	}
	/*** added Optiply 20-06 ***/

	public function getSuppliersWithItems($projectId, $amount, $offset)
    {
        $METRIC_starttime_AFASsupplier = microtime(true);
        apicenter_logs($projectId, 'projectcontrol', 'Start AFAS supplierItem ' . $METRIC_starttime_AFASsupplier, false);
        $afasCreditorConnector = $this->Projects_model->getValue('afas_creditors_conector_id', $projectId);
        $articleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
        $defaultSupplier = $this->Projects_model->getValue('default_supplier', $projectId);
        $amount = $amount == '' ? 100 : $amount;
		
		apicenter_logs($projectId, 'projectcontrol', 'AFAS supplierItem ' . 'Amount: ' . $amount . '. Offset: ' . $offset, false);

		$filters = array();
		$dateFilter = $this->Projects_model->getValue('customers_date_filter', $projectId);
		if($dateFilter != ''){
			$filters[] = array(
				'field' => 'DateModified',
				'type' => 2,
				'val' => $dateFilter.'T00:00:00'
			);
		}
	
        $data = $this->getData($projectId, $amount, $offset, $articleConnector, 'ItemCode', $filters[0]);
        
        if(count($data) < $amount && $data !== false){
			$this->Projects_model->saveValue('customers_offset', 0, $projectId);
			$this->Projects_model->saveValue('customers_date_filter', date('Y-m-d'), $projectId);
			$this->Projects_model->saveValue('import_finished', 1, $projectId);
        }

        //log_message('debug', '106 - Optiply Supplier Ping '. var_export($data, true));

        $products = [];

        foreach ($data->$articleConnector as $conItem) {
            $conItem = $this->xml2array($conItem);

            $status = $conItem['Blocked'] == 'false' ? 'ENABLED' : 'DISABLED';

            $itemData = [
                'id' => $conItem['ItemCode'],
                'name' => $conItem['Description'],
                'price' => $conItem['PurchasePriceSuppliers'],
                'priceStandart' => $conItem['BasicSalesPrice'],
                'minQuantity' => $conItem['MinPurchaseQty'],
                'supplierItemCode' => $conItem['InkSerialNumber'],
                'lotSize' => 0,
                'stock' => $conItem['StockActual'],
                'artCode' => $conItem['Articlecode_intern'],
                'code' => $conItem['ItemCode'],
                'barcode' => $conItem['Barcode'],
                'status' => $status
            ];

            if(!isset($conItem['SupplierCode'])) {
                $conItem['SupplierCode'] = $defaultSupplier;
            }

            $products[$conItem['SupplierCode']][] = $itemData;
        }

        $finalData = [];
        foreach ($products as $creditorId => $productsArray) {

            $supplier = [
                'name' => 'default',
                'email' => '',
                'id' => $defaultSupplier,
                'items' => $productsArray
            ];

            if($creditorId != $defaultSupplier) {

                $creditorData = $this->getData($projectId, $amount, $offset, $afasCreditorConnector, 'CreditorId',
                    ['field' => 'CreditorId', 'val' => $creditorId]);
                $creditorData = $creditorData->$afasCreditorConnector;
                $creditorData = $this->xml2array($creditorData);

                $supplier['name'] = (string)$creditorData['CreditorName'];
                $supplier['email'] = (string)$creditorData['Email'];
                $supplier['id'] = $creditorData['CreditorId'];
            }

            $finalData[] = $supplier;
        }
        
        //log_message('debug', '106 - Optiply Supplier Ping22 '. var_export($finalData, true));
        
        return $finalData;
    }
	/**
     * Get suppliers with linked products
     * @param $projectId
     * @param $amount
     * @param $offset
     * @return array
     */
	public function getCreditors($projectId, $amount, $offset) {

        $afasCreditorConnector = $this->Projects_model->getValue('afas_creditors_conector_id', $projectId);
        $lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);

        $data = $this->getData($projectId, $amount, $offset, $afasCreditorConnector, 'CreditorId');

        $results = [];

        $numberOfResults = count($data->$afasCreditorConnector);
        if(isset($data->$afasCreditorConnector) && count($data->$afasCreditorConnector) > 0){
            $results = [];

            foreach($data->$afasCreditorConnector as $creditor){
                $creditor = $this->xml2array($creditor);
				afas_log($projectId, 'supl', json_encode($creditor));
                $finalData = [];
                if(isset($creditor['Blocked']) && $creditor['Blocked'] == 'true'){
                    continue;
                }

                $finalData['name'] = (string)$creditor['CreditorName'];
                $finalData['email'] = (string)$creditor['Email'];
                $finalData['id'] = $creditor['CreditorId'];
                
                $finalData['items'] = $this->getCreditorArticles($creditor['CreditorId'], $projectId);

                $results[] = $finalData;
            }

        }

        afas_log($projectId, 'suppliers', json_encode($results));
        return $results;
    }
	
	/**
     * Get all products by supplier
     * @param $creditorId
     * @param $projectId
     * @param int $offset
     * @return array
     */
    public function getCreditorArticles($creditorId, $projectId, $offset = 0) {

        $articleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
        $amount = 100;

        $data = $this->getData($projectId, $amount, $offset, $articleConnector, 'ItemCode',
            ['field' => 'SupplierCode', 'val' => $creditorId]);

        $products = [];

        foreach ($data->$articleConnector as $conItem) {
            $conItem = $this->xml2array($conItem);

            $status = $conItem['Blocked'] == 'false' ? 'ENABLED' : 'DISABLED';

            $itemData = [
                'id' => $conItem['ItemCode'],
                'name' => $conItem['Description'],
                'price' => $conItem['PurchasePriceSuppliers'],
                'priceStandart' => $conItem['BasicSalesPrice'],
                'minQuantity' => $conItem['MinPurchaseQty'],
                'supplierItemCode' => $conItem['InkSerialNumber'],
                'lotSize' => 0,
                'stock' => $conItem['StockActual'],
                'artCode' => $conItem['Articlecode_intern'],
                'code' => $conItem['ItemCode'],
                'barcode' => $conItem['Barcode'],
                'status' => $status
            ];

            $products[] = $itemData;
        }

        $numberOfResults = count($data->$articleConnector);
        if($numberOfResults >= $amount) {
            $offset = $offset + 100;
            $products = array_merge($products, $this->getCreditorArticles($creditorId, $projectId, $offset));
        }

        return $products;
    }
	/**
     * Get sell orders
     * @param $projectId
     * @param $amount
     * @param $offset
     * @param string $fromDate
     * @return array
     */
    public function getSalesOrders($projectId, $amount, $offset, $fromDate = '') {
        $afasOrderConnector = $this->Projects_model->getValue('afas_orders_connector', $projectId);

        //$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
        $filters = [];
        if($fromDate != '') {
            $filters = [
                'field' => 'Datum',
                'val'=> $fromDate,
                'type' => '2'
            ];
        }

        $data = $this->getData($projectId, $amount, $offset, $afasOrderConnector, 'OrderNumber', $filters);

        $numberOfResults = count($data->$afasOrderConnector);
        if(isset($data->$afasOrderConnector) && count($data->$afasOrderConnector) > 0){
            $results = array();
            foreach($data->$afasOrderConnector as $order){
                $order = $this->xml2array($order);
                afas_log($projectId, 'input_order_data', json_encode($order));
                $created = strtotime($order['OrderDate']);
                $created = date('Y-m-d', $created).'T'.date('H:i:s.Z', $created).'Z';

                $finalArticleData = [];
                $finalArticleData['id'] = $order['OrderNumber'];
                $finalArticleData['created'] = $created;
                $finalArticleData['completed'] = $created;
                $finalArticleData['amount'] = $order['TotalAmount'];

                $finalArticleData['lines'] =
                    $this->getSellOrderLines($projectId, 50, 0, $finalArticleData['id']);

                $results[] = $finalArticleData;
            }

        }
		afas_log($projectId, 'sell_orders', json_encode($results));
        return $results;
    }
	/**
     * Get sell orders lines
     * @param $projectId
     * @param $amount
     * @param $offset
     * @param $orderId
     * @return array
     */
    public function getSellOrderLines($projectId, $amount, $offset, $orderId) {

        $afasOrderLineConnector = $this->Projects_model->getValue('afas_order_line_con', $projectId);

        $data = $this->getData($projectId, $amount, $offset, $afasOrderLineConnector, 'OrderNumber',
            ['field' => "OrderNumber", 'val' => $orderId]);

        $results = [];
        $numberOfResults = count($data->$afasOrderLineConnector);
        if(isset($data->$afasOrderLineConnector) && count($data->$afasOrderLineConnector) > 0){
            $results = [];
            foreach($data->$afasOrderLineConnector as $line){
                $order = $this->xml2array($line);

                $finalArticleData = [];
                $finalArticleData['id'] = $order['ItemCodeId'];
                $finalArticleData['name'] = $order['Description'];
                $finalArticleData['quantity'] = $order['PiecePerUnit'];
                $finalArticleData['unitPrice'] = $order['PricePerUnit'];

                $results[] = $finalArticleData;
            }
        }

        return $results;
    }
	/**
     * Get buy orders
     * @param $projectId
     * @param $amount
     * @param $offset
     * @return array
     */
    public function getBuyOrders($projectId, $amount, $offset) {

        $afasOrderConnector = $this->Projects_model->getValue('afas_buyorder_con', $projectId);

        $lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
        $filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);

        $data = $this->getData($projectId, $amount, $offset, $afasOrderConnector, 'Nummer');
		$results = [];
	
        if(isset($data->$afasOrderConnector) && count($data->$afasOrderConnector) > 0){
            
            foreach($data->$afasOrderConnector as $order){
                $order = $this->xml2array($order);

                $created = strtotime($order['Datum']);
                $created = date('Y-m-d', $created).'T'.date('H:i:s.Z', $created).'Z';

                $finalArticleData = [];
                $finalArticleData['id'] = $order['Nummer'];
                $finalArticleData['created'] = $created;
                $finalArticleData['completed'] = $created;
                $finalArticleData['amount'] = $order['Totaalbedrag'];
                $finalArticleData['name'] = $order['Crediteurnaam'];

                $finalArticleData['lines'] =
                    $this->getBuyOrderLines($projectId, $finalArticleData['id']);

                $results[] = $finalArticleData;
            }

        }
        afas_log($projectId, 'but_ord', json_encode($results));
        return $results;
    }
	/**
     * Get buy orders lines by order ID
     * @param $projectId
     * @param $amount
     * @param $offset
     * @param $orderId
     * @return array
     */
    public function getBuyOrderLines($projectId, $orderId) {

        $afasOrderLineConnector = $this->Projects_model->getValue('afas_buyorder_line_con', $projectId);

        $data = $this->getData($projectId, 50, 0, $afasOrderLineConnector, 'purchaseorder',
            ['field' => "purchaseorder", 'val' => $orderId]);

        afas_log($projectId, 'purch_line_resp', json_encode($data));
        afas_log($projectId, 'purch_line_resp', json_encode($data->$afasOrderLineConnector));

        $results = [];

        if(isset($data->$afasOrderLineConnector) && count($data->$afasOrderLineConnector) > 0){
            $results = [];
            foreach($data->$afasOrderLineConnector as $line){
                $order = $this->xml2array($line);

                $finalArticleData = [];
                $finalArticleData['id'] = $order['item'];
                $finalArticleData['name'] = $order['description'];
                $finalArticleData['quantity'] = $order['quantity_delivered'];
                $finalArticleData['unitPrice'] = $order['Prijs_per_eenheid'];
                $finalArticleData['amount'] = $order['Prijs_per_eenheid'];

                $results[] = $finalArticleData;
            }
        }

        return $results;
    }
    /**
     * API request method. Retrieve data from AFAS API
     * @param $projectId
     * @param $amount
     * @param $offset
     * @param $connector
     * @param $fieldId
     * @param null $filters
     * @return SimpleXMLElement
     */
    public function getData($projectId, $amount, $offset, $connector, $fieldId, $filters = null) {

        $afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
        $afasToken = $this->Projects_model->getValue('afas_token', $projectId);
        $afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);

        $lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);

        $filtersXML = '';
        if(!empty($filters)){
            $filters['type'] = isset($filters['type']) ? $filters['type'] : '1';

            $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="'.$filters['field'].'" OperatorType="'
                .$filters['type'].'">'.$filters['val'].'</Field></Filter></Filters>';
        }

        /* Commented while not using
        if($lastUpdateDate != '' && $lastUpdateDate != ' '){
            $lastUpdateDateFilter = $lastUpdateDate.'T00:00:00';
            $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field></Filter></Filters>';
            if(!empty($filters)){
                $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field><Field FieldId="'.$fieldId.'" OperatorType="1">true</Field></Filter></Filters>';
            }
        }
        */

        if($amount == '')
            $amount = 10;

        if($offset == '')
            $offset = 0;

        $this->load->helper('NuSOAP/nusoap');

        $client = new nusoap_client($afasGetUrl, true, false, false, false, false, 0, 300);
        $client->setUseCurl(true);
        $client->useHTTPPersistentConnection();
		/* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;

        $xml_array['environmentId'] = $afasEnvironmentId;
        $xml_array['token'] = $afasToken;
        $xml_array['connectorId'] = $connector;
        $xml_array['filtersXml'] = $filtersXML;
        $xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="'.$fieldId.'" OperatorType="1" /></Index></options>';

        afas_log($projectId, 'request', json_encode($xml_array));
    	
        $err = $client->getError();
        if ($err) {
            afas_log($projectId, 'err', json_encode($err));
            echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
            echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
            exit();
        }

        $result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
        if($result === false){
        	apicenter_logs($projectId, 'projectcontrol', 'Optiply: $result was false for offset '.$offset.', connector '.$connector, false);
            return false;
        }
        afas_log($projectId, 'result', json_encode($result));

        $resultData = $result["GetDataWithOptionsResult"];
        $resultData = str_replace("
", '|br|', $resultData);
        $resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
        $resultData = $this->replaceSpecialChars($resultData);
        $resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
        $resultData = $this->unReplaceSpecialChars($resultData);

        $data = simplexml_load_string($resultData);

		if($data == false){
			return array();
		}
        return $data;
    }
	/**
     * Check if order lines not empty and push order to AFAS
     * @param $projectId
     * @param $orders
     */
    public function pushAllPurchaseOrders($projectId, $orders) {

        foreach ($orders as $order) {
            if(isset($order['lines']) && !empty($order['lines']))
                $this->pushPurchaseOrder($projectId, $order);
        }

    }
	/**
     * Import Purchase order to AFAS
     * @param $projectId
     * @param $order
     * @return bool|void
     */
    public function pushPurchaseOrder($projectId, $order) {
        afas_log($projectId, 'p_order_input', json_encode($order));

        if($this->checkOrderExists($order['id'], $projectId)) {
            afas_log($projectId, 'p_order_exists', $order['id']);
            return;
        }
		afas_log($projectId, 'after_check', 's');
        $supplier = $order['supplier'];
        $creditorId = $this->checkAfasSupplier($projectId, $supplier['name'], 'name');

        if(!$creditorId){
            apicenter_logs($projectId, 'exact_buy_orders', 'Could not find/add customer to AFAS', true);
            return false;
        }
        afas_log($projectId, 'after_check_creditor', 's');

        $afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
        $afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
        $afasToken = $this->Projects_model->getValue('afas_token', $projectId);
        $afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
        $afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
        $afasOrderType = 'FbPurch';

        $xmlOrder = new SimpleXMLElement("<".$afasOrderType."></".$afasOrderType.">");
        $orderElement = $xmlOrder->addChild('Element');
        $fields = $orderElement->addChild('Fields');
        $fields->addAttribute('Action', 'insert');

        $fields->OrNu = $order['id'];
        $fields->Orda = substr($order['placed'], 0, strpos($order['placed'], 'T'));
        $fields->CrId = $creditorId;
        $fields->DaDe = substr($order['completed'], 0, strpos($order['completed'], 'T'));
        $fields->CuId = 'EUR';
        //$fields->War = '01';

        $objectsElement = $orderElement->addChild('Objects');
        $FbPurchLines = $objectsElement->addChild('FbPurchLines');
		afas_log($projectId, 'after_data', 's');
        // Add order lines
        foreach($order['lines'] as $line){
            $itemCode = $this->getItemIdByName($projectId, $line['item']['name']);

            if(!$itemCode) {
                afas_log($projectId, 'item_not_found', $line['name']);
                apicenter_logs($projectId, 'exact_buy_orders', 'Item not found '.$line['name'], true);
                continue;
            }

            $element = $FbPurchLines->addChild('Element');
            $fields = $element->addChild('Fields');
            $fields->addAttribute('Action', 'insert');

            $fields->VaIt = 2;
            $fields->ItCd = $itemCode;
            $fields->BiId = $itemCode;
            $fields->QuUn = floatval($line['quantity']);
            // Set price
            $fields->Upri = round($line['item']['price'], 2);
        }

        $data = $xmlOrder->asXML();
        $data = str_replace('<?xml version="1.0"?>', '', $data);
        $data = str_replace("
", '', $data);

        $this->load->helper('NuSOAP/nusoap');

        $client = new nusoap_client($afasUpdateUrl, true);
        $client->setUseCurl(true);
        $client->useHTTPPersistentConnection();
        afas_log($projectId, 'after_client', 's');

        $xml_array['environmentId'] = $afasEnvironmentId;
        $xml_array['token'] = $afasToken;
        $xml_array['connectorType'] = $afasOrderType;
        $xml_array['connectorVersion'] = 1;
        $xml_array['dataXml'] = $data;
		afas_log($projectId, 'xml', json_encode($data));
        $result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
        afas_log($projectId, 'purch_res', json_encode($result));
        if(isset($result['faultcode']) && $result['faultcode'] != ''){
            apicenter_logs($projectId, 'exportorders', 'Could not export order '.$order['id'].' to AFAS. Error: '.$result['faultstring'], true);
            return false;
        } else {
            apicenter_logs($projectId, 'exportorders', 'Exported order '.$order['id'].' to AFAS.', false);
        }

        $resultData = $result["ExecuteResult"];

        $data = simplexml_load_string($resultData);
        $data = $this->xml2array($data);

        if(!isset($data["FbPurch"]["OrNu"])){
            afas_log($projectId, 'p_order_not_pushed', json_encode($order));
            apicenter_logs($projectId, 'exportorders', 'Not Exported order '.$order['id'].' to AFAS.', true);
            return;
        }

        $orderId = $data["FbPurch"]["OrNu"];

        $data = [
            'project_id' => $projectId,
            'order_id' => $orderId,
            'optiply_id' => $order['id'],
            'date' => date("Y-m-d H:i:s")
        ];
        $this->db->insert('optiply_orders', $data);

        return true;
    }
	/**
     * Get Creditor(Supplier) id
     * @param int $projectId
     * @param $supplierData array
     * @param $type string
     * @return bool|mixed
     */
    function checkAfasSupplier($projectId, $supplierData, $type){

        if($type == 'name'){
            $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="CreditorName" OperatorType="6">%'.$supplierData.'%</Field></Filter></Filters>';
        } else {
            return false;
        }

        $afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
        $afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
        $afasToken = $this->Projects_model->getValue('afas_token', $projectId);
        $afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
        $afasCredConnector = $this->Projects_model->getValue('afas_creditors_conector_id', $projectId);

        $this->load->helper('NuSOAP/nusoap');

        $client = new nusoap_client($afasGetUrl, true);
        $client->setUseCurl(true);
        $client->useHTTPPersistentConnection();


        $xml_array['environmentId'] = $afasEnvironmentId;
        $xml_array['token'] = $afasToken;
        $xml_array['connectorId'] = $afasCredConnector;
        $xml_array['filtersXml'] = $filtersXML;
        $xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';

        $result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
        afas_log($projectId, 'search_supplier_r', json_encode($result));

        $resultData = $result["GetDataWithOptionsResult"];
        $resultData = $this->replaceSpecialChars($resultData);
        $resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
        $resultData = $this->unReplaceSpecialChars($resultData);

        $data = simplexml_load_string($resultData);
        $data = $this->xml2array($data->$afasCredConnector);
        afas_log($projectId, 'search_supplier', json_encode($data));

        if(isset($data['CreditorId']))
            return $data['CreditorId'];

        return false;
    }
	/**
     * Get item ID by item name
     * @param $projectId
     * @param $name
     * @return bool|mixed
     */
    public function getItemIdByName($projectId, $name){

        $connector = $this->Projects_model->getValue('afas_article_connector', $projectId);

         $filters = [
            'field' => 'Description',
            'val' => $name,
            'type' => '1'
        ];

        $result = $this->getData($projectId, 1, 0, $connector, 'Itemcode', $filters);
        afas_log($projectId, 'get_item_by_name', json_encode($result));

        $data = $result->$connector;
        $data = $this->xml2array($data);
        afas_log($projectId, 'get_item_by_name_parsed', json_encode($data));

        if(isset($data['ItemCode'])) {
            return $data['ItemCode'];
        }

        return false;
    }

    /**
     * Get item data by item name
     * @param $projectId
     * @param $name
     * @return bool|mixed
     */
    public function getItemByName($projectId, $name) {

        $connector = $this->Projects_model->getValue('afas_article_connector', $projectId);

        $filters = [
            'field' => 'Description',
            'val' => $name,
            'type' => '1'
        ];

        $result = $this->getData($projectId, 1, 0, $connector, 'Itemcode', $filters);
        afas_log($projectId, 'get_item_by_name', json_encode($result));

        $data = $result->$connector;
        $data = $this->xml2array($data);
        afas_log($projectId, 'get_item_by_name_parsed', json_encode($data));

        if(isset($data['ItemCode'])) {
            $status = $data['Blocked'] == 'false' ? 'ENABLED' : 'DISABLED';

            $itemData = [
                'id' => $data['ItemCode'],
                'name' => $data['Description'],
                'price' => $data['PurchasePriceSuppliers'],
                'priceStandart' => $data['BasicSalesPrice'],
                'minQuantity' => $data['MinPurchaseQty'],
                'supplierItemCode' => $data['InkSerialNumber'],
                'lotSize' => 0,
                'stock' => $data['StockActual'],
                'artCode' => $data['Articlecode_intern'],
                'code' => $data['ItemCode'],
                'barcode' => $data['Barcode'],
                'status' => $status
            ];

            return $itemData;
        }

        return false;
    }

	/**
     * Get supplier name by product ID
     * @param int $projectId
     * @param string $itemId
     * @return bool|mixed
     */
    public function getSupplierByItem($projectId, $itemId) {

        $artConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);

        $filters = [
            'field' => 'Itemcode',
            'val' => $itemId,
            'type' => '1'
        ];

        $result = $this->getData($projectId, 1, 0, $artConnector, 'Itemcode', $filters);
        afas_log($projectId, 'get_sup_by_item', json_encode($result));

        $data = $result->$artConnector;
        $data = $this->xml2array($data);
        afas_log($projectId, 'get_sup_by_item_', json_encode($data));

        if(isset($data['Suppliername'])) {
            return $data['Suppliername'];
        }

        return false;
    }
	/**
     * Check if order was already imported
     * @param string $id order ID
     * @param int $projectId
     * @return boolean true if order exists
    */
    public function checkOrderExists($id, $projectId) {

        $query = $this->db->get_where('optiply_orders',
            [
                'project_id' => $projectId,
                'optiply_id' => $id,
            ]);

        $order = $query->row_array();

        if(empty($order))
            return false;

        return true;
    }
	public function getCourses($projectId, $offset = 0, $amount = 10, $debug = false){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$filtersXML = '';
				
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="Itemcode" OperatorType="1" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("
", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$resultData = $this->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->unReplaceSpecialChars($resultData);

		$data = simplexml_load_string($resultData);
// 		echo '<pre>';print_r($data);exit;
		$numberOfResults = count($data->$afasArticleConnector);
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$results = array();
			$removeResults = array();
			foreach($data->$afasArticleConnector as $course){
				$course = $this->xml2array($course);
				$finalCourseData = array();
				$finalCourseData['code'] = $course['Itemcode'];
				$finalCourseData['name'] = (string)$course['Omschrijving'];
				$finalCourseData['location'] = $course['Locatie'];
				$finalCourseData['number'] = $course['Nummer'];
				$finalCourseData['min_attendees'] = $course['Minimumaantal_deelnemers'];
				$finalCourseData['max_attendees'] = $course['Maximumaantal_deelnemers'];
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'getCourseData')){
						$finalCourseData = $this->$projectModel->getCourseData($course, $finalCourseData);
					}
				}				
				$results[] = $finalCourseData;
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

	public function sendArticle($projectId, $articles) {
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasArticleConnector = 'FbItemArticle';
		
		foreach($articles as $article){
			$xmlArticle = new SimpleXMLElement("<FbItemArticle></FbItemArticle>");
			$wheight 	= $article['weight'];
			$articleElement = $xmlArticle->addChild('Element');

			$fields = $articleElement->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			$fields->ItCd = $article['product_code'];
			$fields->Ds   = mb_strimwidth($article['product'], 0, 100, "...");
			$fields->Grp  = 100;
			$fields->BiUn = 'st.';
			$fields->VaRc = 2;
			$fields->VaTp = 0;
			$fields->SaPrice = $article['price'];
			$fields->CBSQuUn = $article['amount'];
			$fields->VaWt = 4;
			$fields->NeWe = $wheight;
			$fields->GrWe = $wheight;

			$data = $xmlArticle->asXML();
			$data = str_replace('<?xml version="1.0"?>', '', $data);
			$data = str_replace("", '', $data);
			//if( $projectId == 87) { log_message('debug', 'ProductData 87 ' . var_export($data, true)); }
			$this->load->helper('NuSOAP/nusoap');
		
			$client = new nusoap_client($afasUpdateUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();
			
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorType'] = 'FbItemArticle';
			$xml_array['connectorVersion'] = 1;
			$xml_array['dataXml'] = $data;

			$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
			if (isset($result['faultcode'])) {
				// apicenter_logs($projectId, 'importarticles', 'Could not create product '.$article['product_code'].'. Result: '.var_export($result['faultstring'], true), true);
				$this->updateArticle($projectId, $article);
			} else {
				apicenter_logs($projectId, 'importarticles', 'Created product '.$article['product_code'], false);
			}
		}
	}

	public function findCategory ($projectId, $article = '') {
		//foreach ($article['category_names'] as $catName) {
			$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
			$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
			$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
			$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
			$afasArticleGroupsConnector = $this->Projects_model->getValue('afas_article_groups_connector', $projectId);
			if($afasArticleGroupsConnector == ''){
				$afasArticleGroupsConnector = 'Profit_ArticleGroups';
			}
			
			$this->load->helper('NuSOAP/nusoap');
			
			$client = new nusoap_client($afasGetUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();
			$name = 'Motoren';
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorId'] = $afasArticleGroupsConnector;
			$xml_array['filtersXml'] = '<Filters><Filter FilterId="Filter1"><Field FieldId="Omschrijving" OperatorType="1">'.$name.'</Field></Filter></Filters>';
			//$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
	
			$err = $client->getError();
			if ($err) {
				echo '<h2>Constructor error</h2><pre>' . $err . '</pre>';
				echo '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
				exit();
			}
			
			$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
			// echo "<pre>";
			// var_dump($result);exit;
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = $this->replaceSpecialChars($resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData = $this->unReplaceSpecialChars($resultData);

			$data = simplexml_load_string($resultData);
		//}
	}

	public function updateArticle($projectId, $article)
	{
		/*
		
			<FbItemArticle xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
			<Element>
			<Fields Action="update">
			<ItCd>1</ItCd>
			<Ds>Gewijzigde omschrijving</Ds>
			</Fields>
			</Element>
			</FbItemArticle>
		*/
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasArticleConnector = 'FbItemArticle';
		
		$xmlArticle = new SimpleXMLElement("<FbItemArticle></FbItemArticle>");
		$articleElement = $xmlArticle->addChild('Element');
		$fields = $articleElement->addChild('Fields');
		$fields->addAttribute('Action', 'update');
		$fields->ItCd = $article['product_code'];
		$fields->Ds   = $article['product'];

		$data = $xmlArticle->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("", '', $data);
		
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $afasArticleConnector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if (isset($result['faultcode'])) {
			apicenter_logs($projectId, 'importarticles', 'Could not create product '.$article['product_code'].'. Result: '.var_export($result['faultstring'], true), true);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Updated product '.$article['product_code'], false);
		}
	}

	public function checkProductsExist($projectId, $products) {

        $toUpdate = [];

        foreach ($products as $product) {
            $item = $this->getItemIdByName($projectId, $product['name']);

            if($item) {
                continue;
            }

            $toUpdate[] = $item;
        }

        return $toUpdate;
	}

	public function getPriceChangesList($projectId)
	{
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$date = date("Y-m-d").'T00:00:00';
		$amount = 1000000;
		$offset = 0;
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Begindatum" OperatorType="1">'.$date.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="ItemCode" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "PrijsWijzigingen_Actueel_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode'])) {
            return false;
		} else {
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = $this->replaceSpecialChars($resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData = $this->unReplaceSpecialChars($resultData);
			$data 		= simplexml_load_string($resultData);
			$data 		= (array) $data;
			$price_list = array();
			// create data as per required in mailchimp.
			if(!empty($data)){
				$all_count  = count($data['PrijsWijzigingen_Actueel_App']);
				$price_list = $data['PrijsWijzigingen_Actueel_App'];
			} else{
				$all_count = 0;
			}

			return array('numberOfResults' => $all_count, 'priceData' => $price_list );
		}
	}

	public function getMultiLanguageArticles($projectId, $storeCode, $sku) 
	{
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasArticleConnector = $afasArticleConnector .'_' . $storeCode;
		$offset = 0;
		$amount = 10;
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		// $sku = "001J023208778";
		
		/* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
        $client->soap_defencoding = 'UTF-8';
        $client->decode_utf8 = false;
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$sku.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="ItemCode" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])) {
			return false;
		} else {
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = str_replace("
			", '|br|', $resultData);
			$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
			$data = simplexml_load_string($resultData);
			$numberOfResults = count($data->$afasArticleConnector);
			if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0) {
			    
			    //if( $projectId == 131) { log_message('debug', 'ProductData_ML 131 ' . var_export($this->xml2array($data->$afasArticleConnector), true)); }
				
				return $this->xml2array($data->$afasArticleConnector);
			}
		}

		return false;
	}

	public function sendDeliveryNote($projectId, $orderData)
	{
	   
	//TEMPORARY    
	if ($projectId == 131 && $orderData['status'] == 'processing')
	{   
		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);

		if(!$debtorId = $this->checkAfasCustomerExists($projectId, $customerData, $orderData['id'])) {
			apicenter_logs($projectId, 'exportorders', 'Could not find/add customer to AFAS', true);
			return false;
		}
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$afasOrderType = "FbDeliveryNote";

		$xmlOrder = new SimpleXMLElement("<".$afasOrderType."></".$afasOrderType.">");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');

		$fields->DbId = $debtorId;
		$fields->War = '*****';
		if ($projectId == 131) {
			$fields->War = 'O';
		}
		$fields->OrNu = $orderData['id'];
		
		$fields->OrDa = $orderData['create_at'];
		
		$comment = isset($orderData['comment']) ? $orderData['comment'] : '';
		if($comment != ''){
			$fields->Re = $comment;
		}

		$fields->CuId = $orderData['currency'];
		
		// Administratie
		if($this->Projects_model->getValue('afas_orders_administration', $projectId) != '' && $this->Projects_model->getValue('afas_orders_administration', $projectId) != 'default'){
			$administration = $this->Projects_model->getValue('afas_orders_administration', $projectId);
			$fields->Unit = $administration;
		}
        
        // Delivery address
		$deliveryAddress = $this->addDeliveryAddress($orderData, $debtorId, $projectId);
		if($deliveryAddress != false){
			$fields->DlAd = $deliveryAddress;
		}

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setOrderParams')){
				$this->$projectModel->setOrderParams($fields, $orderData);
			}
		}

		$objectsElement = $orderElement->addChild('Objects');
		$FbSalesLines = $objectsElement->addChild('FbDeliveryNoteLines');

		// Add items
		$products = $orderData['order_products'];
		foreach($products as $item) {
			$product = $item;

			$element = $FbSalesLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');

			$fields->VaIt = 2;
			$fields->ItCd = $product['model'];
			$fields->BiUn = 'stk';
			$fields->QuUn = floatval($product['quantity']);
			// Set price
			$price = $product['price'];
			$fields->Upri = round($price, 2);

			//$fields->War = '*****';
			// Discount
			if(isset($product['discount_amount']) && $product['discount_amount'] > 0){
				$fields->ARDc = $product['discount_amount'];
			}
			
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'setOrderProductParams')){
					$this->$projectModel->setOrderProductParams($fields, $item);
				}
			}
		}

		// Shipping
		$shippingSku = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if($shippingSku != '' && $shippingSku != ' '){
			if(isset($orderData['totals']) && isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0){
				$element = $FbSalesLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
				$fields->VaIt = 2;
				$fields->ItCd = $shippingSku;
				//$fields->BiUn = 'stk';2
				$fields->QuUn = 1;
				// Set price
				$price = $orderData['totals']['shipping'];
				$fields->Upri = round($price, 2);
				
				//$fields->War = '*****';
			}
		}

		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("
", '', $data);

		$this->load->helper('NuSOAP/nusoap');
				
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $afasOrderType;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;

		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			if (stristr($result['faultstring'], 'Nummer pakbon') === FALSE)
				apicenter_logs($projectId, 'exportorders', 'Could not create packing slip '.$orderData['id'].' to AFAS. Error: '.$result['faultstring'], true);
			return false;
		} else {
			apicenter_logs($projectId, 'exportorders', 'Created packing slip '.$orderData['id'].' to AFAS.', false);
						
			// Send order success project custom callback
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'afterOrderSubmit')){
					$this->$projectModel->afterOrderSubmit($orderData);
				}
			}
		}
    
		return true;
	}

	}

	public function checkPackingNotes($projectId, $orderId)
	{
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$amount = 1000000;
		$offset = 0;
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Nummer_pakbon" OperatorType="1">'.$orderId.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="Nummer_pakbon" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Pakbonnen_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode'])) {
            return false;
		} else {
			$resultData 		= $result["GetDataWithOptionsResult"];
			$resultData 		= $this->replaceSpecialChars($resultData);
			$resultData 		= preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData 		= $this->unReplaceSpecialChars($resultData);
			$data 				= simplexml_load_string($resultData);
			$data 				= (array) $data;
			$delivery_notes_app = array();

			if(!empty($data)){
				$all_count  = 1;
				$delivery_notes_app = (array) $data['Pakbonnen_App'];
			} else{
				$all_count = 0;
			}

			return array('numberOfResults' => $all_count, 'pakbonnenData' => $delivery_notes_app);
		}
	}

	public function getStockBysku($projectId, $sku)
	{
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$amount = 1000000;
		$offset = 0;
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Item_code" OperatorType="1">'.$sku.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="Item_code" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_Stock_Cuma_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode'])) {
			return array('faultcode' => $result['faultcode']);
		} else {
			$resultData 		= $result["GetDataWithOptionsResult"];
			$resultData 		= $this->replaceSpecialChars($resultData);
			$resultData 		= preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData 		= $this->unReplaceSpecialChars($resultData);
			$data 				= simplexml_load_string($resultData);
			$data 				= (array) $data;

			$product_stock = array();

			if(!empty($data)){
				$all_count  = 1;
				$delivery_notes_app = (array) $data['Profit_Stock_Cuma_App'];
			} else{
				$all_count = 0;
				$delivery_notes_app = [];
			}

			return array('numberOfResults' => $all_count, 'stockData' => $delivery_notes_app);
		}

	}

	public function getCredit($projectId, $offset = 0, $amount = 10)
	{
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connectorId = "CreditMemo_App";
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		// $filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Nummer_pakbon" OperatorType="1">'.$orderId.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="Nummer_pakbon" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connectorId;
		// $xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		
		if(isset($result['faultcode'])) {
			return array('faultcode' => $result['faultcode']);
		} else {
			$credits 	= $result["GetDataWithOptionsResult"];
			$resultData = $this->replaceSpecialChars($credits);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData = $this->unReplaceSpecialChars($resultData);
			$data 		= simplexml_load_string($resultData);
			$data 		= (array) $data;
			if(!empty($data)) {
				$finalLine = [];
				$сredit_memo = (array) $data['CreditMemo_App'];
				$all_count = count($сredit_memo);

				if ($all_count > 1) {
					foreach ($сredit_memo as $val) {
						$val = (array) $val;
						$tmpLine = [
							'increment_id' => $val['Opdrachtnummer_referentie'],
							'qty' => $val['Totaalaantal'],
							'date' => $val['Datum'],
							'status' => $val['Status'],
						];

						$creditDataLine = $this->getDeliveryNoteLines($projectId, $val);

						if (isset($creditDataLine['deliveryData'])) {
							$creditDataLine = $creditDataLine['deliveryData'];
		
							$tmpLine['order_item_id'] = $creditDataLine['Volgnummer'];
							$tmpLine['item_id'] = $creditDataLine['Itemcode'];
						}

						$finalLine[] = $tmpLine;
					}
				} else {
					$finalLine = [
						'increment_id' => $сredit_memo['Opdrachtnummer_referentie'],
						'qty' => $сredit_memo['Totaalaantal'],
						'date' => $сredit_memo['Datum'],
						'status' => $сredit_memo['Status'],
					];
	
					$creditDataLine = $this->getDeliveryNoteLines($projectId, $сredit_memo);
	
					if (isset($creditDataLine['deliveryData'])) {
						$creditDataLine = $creditDataLine['deliveryData'];
	
						$finalLine['order_item_id'] = $creditDataLine['Volgnummer'];
						$finalLine['item_id'] = $creditDataLine['Itemcode'];
					}
				}

			} else{
				$all_count = 0;
				$finalLine = [];
			}

			return array('numberOfResults' => $all_count, 'сreditData' => $finalLine);
		}

	}

	public function getDeliveryNoteLines($projectId, $data)
	{
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$amount = 1000000;
		$offset = 0;
		$itemId = $data['Nummer_pakbon'];
		$this->load->helper('NuSOAP/nusoap');

		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();

		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Nummer_pakbon" OperatorType="1">'.$itemId.'</Field></Filter></Filters>';
		$indexXml = '<Index><Field FieldId="Nummer_pakbon" OperatorType="1" /></Index>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "DeliveryNote_Lines_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take>'.$indexXml.'</options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);

		if(isset($result['faultcode'])) {
			return array('faultcode' => $result['faultcode']);
		} else {
			$resultData 		= $result["GetDataWithOptionsResult"];
			$resultData 		= $this->replaceSpecialChars($resultData);
			$resultData 		= preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData 		= $this->unReplaceSpecialChars($resultData);
			$data 				= simplexml_load_string($resultData);
			$data 				= (array) $data;

			if(!empty($data)){
				$all_count  = 1;
				$delivery_notes_lines = (array) $data['DeliveryNote_Lines_App'];
			} else{
				return false;
			}

			return array('numberOfResults' => $all_count, 'deliveryData' => $delivery_notes_lines);
		}
	}

}