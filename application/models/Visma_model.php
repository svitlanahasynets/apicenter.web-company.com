<?php
class Visma_model extends CI_Model {
	
	public $apiURL;

    function __construct()
    {
        parent::__construct();
        $this->apiURL = 'https://integration.visma.net/API/controller/api/v1';
		//ClientId: web-company-jjl34
		//ClientSecret: ec0a52f2-2443-4be3-bfd2-0224e60b03cf
    }
    
	function xml2array ( $xmlObject, $out = array () )
	{
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	}
	
	function getToken($projectId){
// Set project id fixed to 44
//$projectId = 44;
		$defaultProjectId = 44;
		$authorizeCode = $this->Projects_model->getValue('visma_authorize_code', $projectId);
		$existingToken = $this->Projects_model->getValue('visma_token', $projectId);
		if($existingToken != ''){
			return $existingToken;
		}
		$clientId = $this->Projects_model->getValue('visma_client_id', $defaultProjectId);
		$clientSecret = $this->Projects_model->getValue('visma_secret_key', $defaultProjectId);
		
		$data = array(
			'grant_type' => 'authorization_code',
			'code' => $authorizeCode,
			'redirect_uri' => 'https://apicenterdev.web-company.nl/index.php/visma/index/project/'. $defaultProjectId,
		);
		$get_params = http_build_query($data);
		
		$token = base64_encode($clientId.':'.$clientSecret);
		//log_message('debug', 'get_params = ' . $get_params);
		//log_message('debug', 'token = ' . $token);
		$ch = curl_init('https://integration.visma.net/API/security/api/v2/token');
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $get_params);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/x-www-form-urlencoded", "Authorization: Basic " . $token));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		//log_message('debug', 'project id = ' . $projectId);
		//log_message('debug', 'token info ' . var_export($result, true));
		if(isset($result['token'])){
			$this->Projects_model->saveValue('visma_token', $result['token'], $projectId);
			return $result['token'];
		}
		return false;
	}
	
	function getArticles($projectId, $offset = 0, $amount = 10, $debug = false){
	    
	    //log_message('debug', 'VISMA GetArticle = ' . $projectId . ' ');
	    
		$lastUpdateDate = $this->Projects_model->getValue('visma_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		//log_message('debug', 'Project id = ' . $projectId . ' token = ' . $token);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'pageNumber' => $pageNumber,
			'pageSize' => $amount,
			//'lastModifiedDateTime' => $date
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/inventory?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		
		//if($projectId == 128){
		    //log_message('debug', 'conn = ' . var_export($result), true);
		//}
	
		$result = json_decode($result, true);
		

		//if($projectId == 128){
		//   log_message('debug', 'product = ' . var_export($result), true);
		//}

		
		//log_message('debug', 'Project id = ' . $projectId);
		//log_message('debug', 'product count = ' . var_export(count($result), true));
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		foreach($result as $product){
			
			if($product['type'] != 'FinishedGoodItem' && $product['itemClass']['type'] != 'NonStockItem'){
				//log_message('debug', 'continue!!!!!!!!!!!!!!');
				continue;
			}
			$productData = array();
			if($product['status'] != 'Active'){
				$productData['available_for_view'] = 'false';
				$productData['available_for_sale'] = 'false';
			}
			$productData['name'] = $product['description'];
			$productData['model'] = $product['inventoryNumber'];
			$productData['description'] = $product['description'];
			$qty = 0;
			if(isset($product['warehouseDetails'])){
				foreach($product['warehouseDetails'] as $warehouseDetail){
					$qty += $warehouseDetail['availableForShipment'];
				}
			}
			$productData['tax_class_id'] = 2;
			$productData['quantity'] = $qty;
			$productData['price'] = $product['defaultPrice'];
			
			if($this->Projects_model->getValue('enable_attribute_set_conversion_table', $projectId) == '1'){
				//$article['attribute_set'] = 5;
				if(isset($product['attribute_set']) && $product['attribute_set'] > 0){
					$attributeSet = $product['attribute_set'];
					$attributeSetConversions = json_decode($this->Projects_model->getValue('attribute_set_conversion_table', $projectId), true);
					foreach($attributeSetConversions['afas_id'] as $index => $conversionItem){
						if($conversionItem == $attributeSet){
							$productData['attribute_set_name'] = $attributeSetConversions['shop_id'][$index];
						}
					}
				}
			}
			
			if(isset($product['attachments'])){
				$attachment = $product['attachments'][0];
				$imageData = $this->getAttachment($projectId, $attachment['id']);
				$imageName = $attachment['name'];
				$extensions = array('jpg', 'jpeg', 'png');
				if($imageName != ''){
					$ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
					if(in_array($ext, $extensions)){
						$imageLocation = save_image_string($projectId, $imageName, $imageData, false);
						if(filesize($imageLocation['path']) > 50){
							$productData['image'] = $imageLocation;
						}
					}
				}
			}
			
			// Load categories
			if(isset($product['attributes']) && !empty($product['attributes'])){
				$categories = array();
				foreach($product['attributes'] as $attribute){
					if($attribute['id'] == 'CATEGORY0'){
						$categoryId = $this->Cms_model->findCategory($projectId, $attribute['description']);
						if(!$categoryId){
							$categoryId = $this->Cms_model->createCategory($projectId, $attribute['description']);
						}
						$categories[0] = $categoryId;
					}
					if($attribute['id'] == 'CATEGORY1'){
						$categoryId = $this->Cms_model->findCategory($projectId, $attribute['description']);
						if(!$categoryId){
							$categoryId = $this->Cms_model->createCategory($projectId, $attribute['description'], $categories[0]);
						}
						$categories[1] = $categoryId;
					}
					if($attribute['id'] == 'CATEGORY2'){
						$categoryId = $this->Cms_model->findCategory($projectId, $attribute['description']);
						if(!$categoryId){
							$categoryId = $this->Cms_model->createCategory($projectId, $attribute['description'], $categories[1]);
						}
						$categories[2] = $categoryId;
					}
					if($attribute['id'] == 'CATEGORY3'){
						$categoryId = $this->Cms_model->findCategory($projectId, $attribute['description']);
						if(!$categoryId){
							$categoryId = $this->Cms_model->createCategory($projectId, $attribute['description'], $categories[2]);
						}
						$categories[3] = $categoryId;
					}
				}
				$productData['categories_ids'] = implode(',', array_unique($categories));
			}
			
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'getArticleData')){
					$productData = $this->$projectModel->getArticleData($product, $productData);
				}
			}
			
			if(!isset($productData['enabled']) || $productData['enabled'] != false){
				unset($productData['enabled']);
				$results[] = $productData;
			} else {
				$removeResults[] = $product['inventoryNumber'];
			}
		}
        
        //log_message('debug', 'VISMA GetArticle END = ' . $projectId . ' ' . var_export($productData, true));
        
        
		return array(
			'results' => $results,
			'removeResults' => $removeResults,
			'numberOfResults' => $numberOfResults
		);
	}
	
	function getAttachment($projectId, $attachmentId){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		$ch = curl_init($this->apiURL."/attachment/".$attachmentId);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}
	
	function getStockArticles($projectId, $offset = 0, $amount = 10, $debug = false){
		$lastUpdateDate = $this->Projects_model->getValue('stock_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'pageNumber' => $pageNumber,
			'pageSize' => $amount,
			//'lastModifiedDateTime' => $date
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/inventory?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		foreach($result as $product){
			if($product['type'] != 'FinishedGoodItem'){
				continue;
			}
			$productData = array();
			if($product['status'] != 'Active'){
				continue;
			}
			$productData['model'] = $product['inventoryNumber'];
			$productData['name'] = $product['description'];
			$qty = 0;
			if(isset($product['warehouseDetails'])){
				foreach($product['warehouseDetails'] as $warehouseDetail){
					$qty += $warehouseDetail['availableForShipment'];
				}
			}
			$productData['quantity'] = $qty;
			$results[] = $productData;
		}
		
		return array(
			'results' => $results,
			'numberOfResults' => $numberOfResults
		);
	}
	
/*
	function checkOrderExists($projectId, $orderId){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		$filterData = array(
			'customerRefNo' => $orderId
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/salesorder/?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		echo '<pre>';print_r($result);exit;
	}
*/
	
	function sendOrder($projectId, $orderData){
	    
	    //log_message('debug', 'VISMA SendOrder Start = ' . $projectId . ' ' . var_export($orderData, true));
	    
	    // Check if order exists already
	    //$this->checkOrderExists($projectId, $orderData['id']);
	    
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);
		
		if(!$customerId = $this->checkVismaCustomerExists($projectId, $customerData)){
			return false;
		}
		
		$description = $orderData['id'];
		if($customerData['company'] != ''){
			$description .= ' '.$customerData['company'];
		} else {
			$description .= ' '.$customerData['first_name'].' '.$customerData['last_name'];
		}
		$saveData = array(
			'orderType' => array('value' => $this->Projects_model->getValue('order_type_visma', $projectId) ? $this->Projects_model->getValue('order_type_visma', $projectId) : 'WO'),
			'orderNumber' => array('value' => $orderData['id']),
			'customer' => array('value' => $customerId),
			'currency' => array('value' => $orderData['currency']),
			'customerRefNo' => array('value' => $orderData['id']),
			'hold' => array('value' => false),
			'description' => $description
		);
		
		// Branch number
		$branchNumber = $this->Projects_model->getValue('visma_order_branch', $projectId);
		if($branchNumber != ''){
			$saveData['branchNumber'] = array('value' => $branchNumber);
		}
		
		// Add items
		$products = $orderData['order_products'];
		foreach($products as $item){
			$itemData = $this->getItemData($projectId, $item);
// 			echo '<pre>';print_r($itemData);exit;
			$warehouseId = $itemData['defaultWarehouse']['id'] ? $itemData['defaultWarehouse']['id'] : 1;
			if(isset($itemData['warehouseDetails']) && !empty($itemData['warehouseDetails'])){
				foreach($itemData['warehouseDetails'] as $warehouse){
					if($warehouse['available'] > floatval($item['quantity'])){
						$warehouseId = $warehouse['warehouse'];
					}
				}
			}
			
			$PriceCalc = 0;
			if($projectId == 82){
			    $PriceCalc = array('value' => round($item['price'], 2)); //Prices are always EXCL TAX
			}
			else{
			    $PriceCalc = array('value' => round($item['price'] - $item['tax_value'], 2)); 
			}
			
			$product = array(
				'operation' => 1,
				'inventoryNumber' => array('value' => $item['model']),
				'quantity' => array('value' => floatval($item['quantity'])),
// 				'unitPrice' => array('value' => round($item['price'], 2)),
				'unitPrice' => $PriceCalc,
				'UOM' => array('value' => isset($itemData['baseUnit']) ? $itemData['baseUnit'] : 'STUK'),
				'Warehouse' => array('value' => $warehouseId),
				'discountAmount' => array('value' => round(($item['discount_amount'] / floatval($item['quantity'])), 2)),
/*
				'discountPercent' => array('value' => ''),
				'discountAmount' => 0,
				'lineDescription' => 'Test artikel'
*/
			);
			
			// Branch number
			$branchNumber = $this->Projects_model->getValue('visma_order_branch', $projectId);
			if($branchNumber != ''){
				$product['branchNumber'] = array('value' => $branchNumber);
			}
			
			$saveData['lines'][] = $product;
		}
		
		$shippingItemCode = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if(isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0 && $shippingItemCode != ''){
			$item = array(
				'model' => $shippingItemCode
			);
			$itemData = $this->getItemData($projectId, $item);
			$warehouseId = $itemData['defaultWarehouse']['id'] ? $itemData['defaultWarehouse']['id'] : 1;
			if(isset($itemData['warehouseDetails']) && !empty($itemData['warehouseDetails'])){
				foreach($itemData['warehouseDetails'] as $warehouse){
					if($warehouse['available'] > floatval($item['quantity'])){
						$warehouseId = $warehouse['warehouse'];
					}
				}
			}
			$product = array(
				'operation' => 1,
				'inventoryNumber' => array('value' => $item['model']),
				'quantity' => array('value' => 1),
				'unitPrice' => array('value' => round($orderData['totals']['shipping'], 2)),
				'UOM' => array('value' => isset($itemData['baseUnit']) ? $itemData['baseUnit'] : 'STUK'),
				'Warehouse' => array('value' => $warehouseId)
			);
			// Branch number
			$branchNumber = $this->Projects_model->getValue('visma_order_branch', $projectId);
			if($branchNumber != ''){
				$product['branchNumber'] = array('value' => $branchNumber);
			}
			$saveData['lines'][] = $product;
		}
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setOrderParams')){
				$saveData = $this->$projectModel->setOrderParams($fields, $saveData);
			}
		}
		
		$saveData = json_encode($saveData);
		
		//log_message('debug', 'VISMA SendOrder Start = ' . $projectId . ' ' . var_export($saveData, true));

		$ch = curl_init($this->apiURL."/salesorder");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);
		
		log_message('debug', 'VISMA SendOrder END = ' . $projectId . ' ' . var_export($result, true));
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma.');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma. Error: '.curl_error($ch));
			return false;
		}
	}
	
	function getItemData($projectId, $item){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		$ch = curl_init($this->apiURL."/inventory/".$item['model']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		return $result;
	}
	
	function checkVismaCustomerExists($projectId, $customerData){
		$finalDebtorId = false;
		if($debtorId = $this->checkVismaCustomer($projectId, $customerData, 'email')){
			$finalDebtorId = $debtorId;
		} else {
			if($debtorId = $this->checkVismaCustomer($projectId, $customerData, 'lastname_firstname')){
				$finalDebtorId = $debtorId;
			}
		}
		if(!$finalDebtorId){
			if($this->createVismaCustomer($projectId, $customerData)){
				$finalDebtorId = $this->checkVismaCustomer($projectId, $customerData, 'email');
			}
		}
		return $finalDebtorId;
	}

	function checkVismaCustomer($projectId, $customerData, $type){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		if($type == 'email'){
			$filterData = array(
				'email' => $customerData['email'],
			);
		} elseif($type == 'lastname_firstname'){
			$filterData = array(
				'name' => $customerData['first_name'].' '.$customerData['last_name'],
			);
		} else {
			return false;
		}
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/customer?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		log_message('debug', 'VISMA CheckCustomer END = ' . $projectId . ' ' . var_export($result, true));
		
		if(empty($result)){
			return false;
		}
		$customer = $result[0];
		if(isset($customer['number'])){
			return $customer['number'];
		}
		return false;
	}
	
	function createVismaCustomer($projectId, $customerData){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		if($customerData['company'] != ''){
			$name = $customerData['company'];
		} else {
			$name = $customerData['first_name'].' '.$customerData['last_name'];
		}
		
		$saveData = array(
		    'customer_id' => array('value' => rand(0, 100000)),
			'name' => array('value' => $name),
			'status' => array('value' => 'Active'),
			'currency' => array('value' => 'EUR'),
//			'customerClassId' => array('value' => '1'),
			'mainAddress' => array(
				'value' => array(
					'addressLine1' => array('value' => $customerData['address1']),
					'postalCode' => array('value' => $customerData['postcode']),
					'city' => array('value' => $customerData['city']),
					'countryId' => array('value' => $customerData['country'])
				)
			),
			'mainContact' => array(
				'value' => array(
					'name' => array('value' => $customerData['first_name'].' '.$customerData['last_name']),
					'email' => array('value' => $customerData['email']),
					'phone1' => array('value' => $customerData['phone'])
				)
			)
		);

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'createVismaCustomerBeforeSave')){
				$saveData = $this->$projectModel->createVismaCustomerBeforeSave($customerData, $saveData);
			}
		}
		
		$saveData = json_encode($saveData);
        
        log_message('debug', 'VISMA CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));
        
        
		$ch = curl_init($this->apiURL."/customer");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);
		//echo '<pre>';print_r($result);exit;
		
		log_message('debug', 'VISMA CreateDebtor END = ' . $projectId . ' ' . var_export($result, true));
		
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in Visma');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported customer '.$customerData['first_name'].' '.$customerData['last_name'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in Visma. Error: '.curl_error($ch));
			return false;
		}
	}
	
	function getDebtors($projectId, $offset = 0, $amount = 10){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('visma_company_id', $projectId);
		
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'pageNumber' => $pageNumber,
			'pageSize' => $amount
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/customer?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		//log_message('debug', 'customesr info pr id = ' . $projectId);
		//log_message('debug', 'customesr info' . var_export($result, true));
		$result = json_decode($result, true);
		
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		$counter = 0;
		foreach($result as $customer){
			if($customer['status'] != 'Active'){
				continue;
			}
			$counter++;
			$customerName = explode(' ', $customer['name']);
			$customerFirstName = $customerName[0];
			unset($customerName[0]);
			$customerLastName = implode(' ', $customerName);
			if($customerLastName == ''){
				$customerLastName = ' ';
			}
			
			$email = isset($customer['invoiceContact']['email']) ? $customer['invoiceContact']['email'] : '';
			$phone = isset($customer['invoiceContact']['phone']) ? $customer['invoiceContact']['phone'] : '';
			
			$customerData = array(
				'email' => $email,
				'first_name' => $customerFirstName,
				'last_name' => $customerLastName,
				'address' => $customer['invoiceAddress']['addressLine1'],
				'country' => $customer['invoiceAddress']['country']['id'],
				'postcode' => $customer['invoiceAddress']['postalCode'],
				'city' => $customer['invoiceAddress']['city'],
				'company' => $customer['invoiceContact']['name']
			);
			if($phone != ''){
				$customerData['phone'] = $phone;
			}
            
            log_message('debug', 'VISMA CreateDebtor END = ' . $projectId . ' ' . var_export($customerData, true));
			$this->Cms_model->createCustomer($projectId, $customerData);
		}
		
		return $counter;
	}
}