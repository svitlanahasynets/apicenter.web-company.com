<?php
class Accountview_model extends CI_Model {
	
	public $apiURL;

    function __construct()
    {
        parent::__construct();
        $this->apiURL = 'https://www.accountview.net/api/v3/accountviewdata';
    }
    	
	function getToken($projectId){
		$clientId = $this->Projects_model->getValue('accountview_client', $projectId);
		$clientSecret = $this->Projects_model->getValue('accountview_secret_key', $projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		
		$authorizeCode = $this->Projects_model->getValue('accountview_authorize_code', $projectId);
		$existingToken = $this->Projects_model->getValue('accountview_token', $projectId);
		$refreshToken = $this->Projects_model->getValue('accountview_refresh_token', $projectId);
		if($refreshToken != ''){
			$data = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken,
				'client_id' => $clientId,
				'client_secret' => $clientSecret
			);
			$get_params = http_build_query($data);
			
			$ch = curl_init('https://www.accountview.net/api/v3/token');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get_params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/x-www-form-urlencoded",
				"Host: www.accountview.net",
			));
			 
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);
			if(isset($result['access_token'])){
				$this->Projects_model->saveValue('accountview_token', $result['access_token'], $projectId);
				$this->Projects_model->saveValue('accountview_refresh_token', $result['refresh_token'], $projectId);
				return $result['access_token'];
			}
		} else {
			$data = array(
				'grant_type' => 'authorization_code',
				'code' => $authorizeCode,
				'redirect_uri' => 'https://apicenterdev.web-company.nl/index.php/accountview/index/project/'.$projectId,
				'client_id' => $clientId,
				'client_secret' => $clientSecret
			);
			$get_params = http_build_query($data);
			
			$ch = curl_init('https://www.accountview.net/api/v3/token');
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get_params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/x-www-form-urlencoded",
				"Host: www.accountview.net",
			));
			 
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);
			if(isset($result['access_token'])){
				$this->Projects_model->saveValue('accountview_token', $result['access_token'], $projectId);
				$this->Projects_model->saveValue('accountview_refresh_token', $result['refresh_token'], $projectId);
				return $result['access_token'];
			}
		}
		return false;
	}
	
	function getArticles($projectId, $offset = 0, $amount = 10, $debug = false){
	    
	    log_message('debug', 'ACCOUNTVIEW GetArticle = ' . $projectId . ' ');
	    
		$lastUpdateDate = $this->Projects_model->getValue('accountview_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		
		//log_message('debug', 'Project id = ' . $projectId . ' token = ' . $token);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'PageNumber' => $pageNumber,
			'PageSize' => $amount,
			'BusinessObject' => 'AK1'
		);
		if($lastUpdateDate != ''){
			$filterData = array(
				'PageNumber' => $pageNumber,
				'PageSize' => $amount,
				'BusinessObject' => 'AK1',
				'FilterControlSource1' => 'CNG_DATE',
				'FilterOperator1' => 'DateOnOrAfterDate',
				'FilterValueType1' => 'T',
				'FilterValue1' => '{^'.$lastUpdateDate.'}',
				'FilterIsListOfValues1' => 'false'
			);
		}
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Accept: application/json",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		$result = $result['ARTICLE'];
		if(isset($result['ART_CODE'])){
			$result = array($result);
		}
		
		//log_message('debug', 'Project id = ' . $projectId);
		//log_message('debug', 'product count = ' . var_export(count($result), true));
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		foreach($result as $product){
			
			$productData = array();
			$productData['name'] = $product['ART_DESC1'];
			$productData['model'] = $product['ART_CODE'];
			$productData['description'] = $product['ART_DESC1'];
			$productData['tax_class_id'] = 2;
			$productData['quantity'] = $product['QTY_AVAIL'] ? $product['QTY_AVAIL'] : 0;
			$productData['price'] = $product['PX_SELL'];
			
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
			
			if(isset($product['IMG_MEDIUM']) && $product['IMG_MEDIUM'] != ''){
				$attachment = $product['IMG_MEDIUM'];
				$imageName = $attachment['name'];
				$extensions = array('jpg', 'jpeg', 'png');
				if($imageName != ''){
					$ext = strtolower(pathinfo($imageName, PATHINFO_EXTENSION));
					if(in_array($ext, $extensions)){
						$imageLocation = save_image_string($projectId, $imageName, $imageData, $product['IMG_M_DATA']);
						if(filesize($imageLocation['path']) > 50){
							$productData['image'] = $imageLocation;
						}
					}
				}
			}
			
			// Load categories
			if(isset($product['AG_CODE']) && $product['AG_CODE'] != ''){
				$categories = array();
				
				// Get group name from code
				$accountViewGroup = $this->getArticleGroup($projectId, $product['AG_CODE']);
				if(isset($accountViewGroup[0]) && isset($accountViewGroup[0]['AG_DESC']) && $accountViewGroup[0]['AG_DESC'] != ''){
					$categoryId = $this->Cms_model->findCategory($projectId, $accountViewGroup[0]['AG_DESC']);
					if(!$categoryId){
						$categoryId = $this->Cms_model->createCategory($projectId, $accountViewGroup[0]['AG_DESC']);
					}
					$categories[0] = $categoryId;
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
				$removeResults[] = $product['ART_CODE'];
			}
		}
		
		if($lastUpdateDate != '' && $numberOfResults == 0){
			$this->Projects_model->saveValue('accountview_last_update_date', date('Y-m-d H:i:s'), $projectId);
			$this->Projects_model->saveValue('article_offset', 0, $projectId);
		}
        
        log_message('debug', 'ACCOUNTVIEW GetArticle END = ' . $projectId . ' ' . var_export($productData, true));
        
        
		return array(
			'results' => $results,
			'removeResults' => $removeResults,
			'numberOfResults' => $numberOfResults
		);
	}
	
	public function getArticleGroup($projectId, $code){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		
		$filterData = array(
			'PageSize' => 1,
			'BusinessObject' => 'AG1',
			'FilterControlSource1' => 'AG_CODE',
			'FilterOperator1' => 'Equal',
			'FilterValueType1' => 'C',
			'FilterValue1' => $code,
			'FilterIsListOfValues1' => 'false'
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		if(isset($result['ART_GRP'])){
			return $result['ART_GRP'];
		}
		return false;
	}
	
	function getStockArticles($projectId, $offset = 0, $amount = 10, $debug = false){
		$lastUpdateDate = $this->Projects_model->getValue('stock_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'PageNumber' => $pageNumber,
			'PageSize' => $amount,
			'BusinessObject' => 'AK1',
			//'lastModifiedDateTime' => $date
		);

		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Accept: application/json",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));

		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		foreach($result as $product){
			$productData['model'] = $product['ART_CODE'];
			$productData['name'] = $product['ART_DESC1'];
			$productData['quantity'] = $product['QTY_AVAIL'] ? $product['QTY_AVAIL'] : 0;
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
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		$filterData = array(
			'customerRefNo' => $orderId
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."/salesorder/?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Accept: application/json",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		echo '<pre>';print_r($result);exit;
	}
*/
	
	function sendOrder($projectId, $orderData){
	    
	    log_message('debug', 'ACCOUNTVIEW SendOrder Start = ' . $projectId . ' ' . var_export($orderData, true));
	    
	    // Check if order exists already
	    //$this->checkOrderExists($projectId, $orderData['id']);
	    
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		
		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);

//$customerData['postcode'] = '9999AB';
//$customerData['address1'] = 'NIET VERZENDEN 1';
//$customerData['city'] = 'DEMO PLAATS';
//$customerData['state'] = '';
		$postCode = $customerData['postcode'];
		if(strlen($postCode) == 6){
			$postCode = substr($postCode, 0, 4).' '.substr($postCode, 4, 6);
		}
		$customerData['postcode'] = $postCode;
		
		if(!$customerId = $this->checkAccountviewCustomerExists($projectId, $customerData)){
			return false;
		}
		
		$description = $orderData['id'];
		if($customerData['company'] != ''){
			$description .= ' '.$customerData['company'];
		} else {
			$description .= ' '.$customerData['first_name'].' '.$customerData['last_name'];
		}
		
		$date = date('Y-m-d').'T'.date('H:i:s').'.000Z';
		$saveData = array(
			'BookDate' => $date,
			'BusinessObject' => 'SO1',
			'Table' => array(
				'Definition' => array(
					'Name' => 'SO_HDR',
					'Fields' => array(
						array(
							'name' => 'RowId',
							'FieldType' => 'C'
						),
						array(
							'name' => 'INV_DATE',
							'FieldType' => 'T'
						),
						array(
							'name' => 'RPL_INV',
							'FieldType' => 'C'
						),
						array(
							'name' => 'DEL_DATE',
							'FieldType' => 'T'
						),
						array(
							'name' => 'COMMENT1',
							'FieldType' => 'C'
						),
						array(
							'name' => 'ORD_DATE',
							'FieldType' => 'T'
						),
						array(
							'name' => 'RPL_DEL',
							'FieldType' => 'C'
						),
						array(
							'name' => 'TO_EMAIL',
							'FieldType' => 'C'
						)
					)
				),
				'DetailDefinitions' => array(
					array(
						'Name' => 'SO_LINE',
						'Fields' => array(
							array(
								'name' => 'RowId',
								'FieldType' => 'C'
							),
							array(
								'name' => 'HeaderId',
								'FieldType' => 'C'
							),
							array(
								'name' => 'DEL_DATE',
								'FieldType' => 'T'
							),
							array(
								'name' => 'ART_CODE',
								'FieldType' => 'C'
							),
							array(
								'name' => 'ORD_QTY',
								'FieldType' => 'N'
							),
							array(
								'name' => 'ART_PX',
								'FieldType' => 'N'
							),
						)
					)
				)
			),
			'TableData' => array(
				'Data' => array(
					'Rows' => array(
						array(
							'Values' => array(
								1, // RowId
								$date, // INV_DATE (factuurdatum)
								$customerId, // RPL_INV (factuurdebiteur)
								$date, // DEL_DATE (leverdatum)
								$orderData['id'], // COMMENT1 (omschrijving verkooporder)
								$date, // ORD_DATE (verkooporderdatum)
								$customerId, // RPL_DEL (verzenddebiteur)
								$customerData['email'], // TO_EMAIL (e-mail)
							)
						)
					)
				),
				'DetailData' => array(
					array(
						'Rows' => array()
					)
				)
			)
		);

		// Add items
		$products = $orderData['order_products'];
		$productCounter = 1;
		foreach($products as $item){
// 			echo '<pre>';print_r($item);exit;			
// 			$itemData = $this->getItemData($projectId, $item);
			$product = array(
				'Values' => array(
					$productCounter, // RowId
					1, // HeaderID
					$date, // DEL_DATE (leverdatum)
					$item['model'], // ART_CODE (artikelcode)
					$item['quantity'], // ORD_QTY (aantal besteld)
					$item['total_price_incl_tax'] / $item['quantity'], // ART_PX (verkoopprijs per eenheid)
				)
			);
			$saveData['TableData']['DetailData'][0]['Rows'][] = $product;
			$productCounter++;
		}

		if(isset($orderData['totals']['discount']) && $orderData['totals']['discount'] > 0){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'PD_AMT',
				'FieldType' => 'N'
			);
			$saveData['TableData']['Data']['Rows'][0]['Values'][] = $orderData['totals']['discount'];
		}

		$shippingSku = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if(isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0 && $shippingSku != ''){
			$product = array(
				'Values' => array(
					$productCounter, // RowId
					1, // HeaderID
					$date, // DEL_DATE (leverdatum)
					$shippingSku, // ART_CODE (artikelcode)
					1, // ORD_QTY (aantal besteld)
					$orderData['totals']['shipping'], // ART_PX (verkoopprijs per eenheid)
				)
			);
			$saveData['TableData']['DetailData'][0]['Rows'][] = $product;
			$productCounter++;
		}

		// Delivery address
		$shippingAddress = $orderData['shipping_address'];
		$deliveryAddresses = $this->getDeliveryAddress($projectId, $shippingAddress, $customerId);
		if($deliveryAddresses != false){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'DEL_CODE',
				'FieldType' => 'C'
			);
			$saveData['TableData']['Data']['Rows'][0]['Values'][] = $deliveryAddresses;
		}
		
//		echo '<pre>';print_r($saveData);exit;
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'setOrderParams')){
				$saveData = $this->$projectModel->setOrderParams($orderData, $saveData);
			}
		}
		
		$saveData = json_encode($saveData);
        
		$ch = curl_init($this->apiURL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json; charset=UTF-8",
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		$result = curl_exec($ch);
		$result = json_decode($result, true);
// 		echo '<pre>';print_r($result);exit;

		log_message('debug', 'ACCOUNTVIEW SendOrder END = ' . $projectId . ' ' . var_export($result, true));
		
		if(isset($result['ErrorMessage']) && $result['ErrorMessage'] != ''){
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Accountview. Error: '.$result['ErrorMessage']);
			return false;
		} else {
			api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to Accountview.');
			return true;
		}
	}
	
	function getItemData($projectId, $item){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);

		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		 
		$filterData = array(
			'PageSize' => 1,
			'BusinessObject' => 'AK1',
			'FilterOperator1' => 'Equal',
			'FilterValueType1' => 'C',
			'FilterIsListOfValues1' => 'false'
		);

		$filterData['FilterControlSource1'] = 'ART_CODE';
		$filterData['FilterValue1'] = $item['model'];
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		if(isset($result['ARTICLE']) && isset($result['ARTICLE'][0])){
			return $result['ARTICLE'][0];
		}
		return false;
	}
	
	function checkAccountviewCustomerExists($projectId, $customerData){
		$finalDebtorId = false;
		if($debtorId = $this->checkAccountviewCustomer($projectId, $customerData, 'email')){
			$finalDebtorId = $debtorId;
		} else {
/*
			if($debtorId = $this->checkAccountviewCustomer($projectId, $customerData, 'lastname_firstname')){
				$finalDebtorId = $debtorId;
			}
*/
		}
		if(!$finalDebtorId){
			if($this->createAccountviewCustomer($projectId, $customerData)){
				$finalDebtorId = $this->checkAccountviewCustomer($projectId, $customerData, 'email');
			}
		}
		return $finalDebtorId;
	}

	function checkAccountviewCustomer($projectId, $customerData, $type){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		 
		$filterData = array(
			'PageSize' => 1,
			'BusinessObject' => 'AR1',
			'FilterOperator1' => 'Equal',
			'FilterValueType1' => 'C',
			'FilterIsListOfValues1' => 'false'
		);

		if($type == 'email'){
			$filterData['FilterControlSource1'] = 'MAIL_BUS';
			$filterData['FilterValue1'] = $customerData['email'];
// 			$filterData['FilterValue1'] = 'jef@antvest.be';
		} elseif($type == 'lastname_firstname'){
			$filterData['FilterControlSource1'] = 'ACCT_NAME';
			$filterData['FilterValue1'] = $customerData['first_name'].' '.$customerData['last_name'];
		} else {
			return false;
		}
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		if(isset($result['CONTACT']) && isset($result['CONTACT'][0])){
			return $result['CONTACT'][0]['SUB_NR'];
		}
		return false;
	}
	
	function createAccountviewCustomer($projectId, $customerData){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		
		if($customerData['company'] != ''){
			$name = $customerData['company'];
		} else {
			$name = $customerData['first_name'].' '.$customerData['last_name'];
		}
		
		$saveData = array(
			'BookDate' => date('Y-m-d').'T'.date('H:i:s').'.000Z',
			'BusinessObject' => 'AR1',
			'Table' => array(
				'Definition' => array(
					'Name' => 'CONTACT',
					'Fields' => array(
						array(
							'name' => 'RowId',
							'FieldType' => 'C'
						),
						array(
							'name' => 'ACCT_NR',
							'FieldType' => 'C'
						),
						array(
							'name' => 'ACCT_NAME',
							'FieldType' => 'C'
						),
						array(
							'name' => 'CNT_CODE',
							'FieldType' => 'C'
						),
						array(
							'name' => 'CITY',
							'FieldType' => 'C'
						),
						array(
							'name' => 'POST_CODE',
							'FieldType' => 'C'
						),
						array(
							'name' => 'ADDRESS1',
							'FieldType' => 'C'
						),
						array(
							'name' => 'SRC_CODE',
							'FieldType' => 'C'
						),
						array(
							'name' => 'MAIL_BUS',
							'FieldType' => 'C'
						),
						array(
							'name' => 'TEL_BUS',
							'FieldType' => 'C'
						),
					)
				)
			),
			'TableData' => array(
				'Data' => array(
					'Rows' => array(
						array(
							'values' => array(
								1, // RowId
								'1210', // ACCT_NR (verzamelrekening)
								$name, // ACCT_NAME (bedrijfsnaam)
								$customerData['country'], // ICNT_CODE (landcode)
								$customerData['city'], // CITY (plaatsnaam)
								$customerData['postcode'], // POST_CODE (postcode)
								$customerData['address1'], // ADDRESS1 (adres)
								'-', // SRC_CODE (zoekcode)
								$customerData['email'], // MAIL_BUS (e-mail)
								$customerData['phone'], // TEL_BUS (telefoon)
							)
						)
					)
				)
			)
		);
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'createAccountviewCustomerBeforeSave')){
				$saveData = $this->$projectModel->createAccountviewCustomerBeforeSave($customerData, $saveData);
			}
		}
		
// 		echo '<pre>';print_r($saveData);exit;
		
		$saveData = json_encode($saveData);
        
        log_message('debug', 'ACCOUNTVIEW CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));
        
        
		$ch = curl_init($this->apiURL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json; charset=UTF-8",
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		//echo '<pre>';print_r($result);exit;
		
		log_message('debug', 'ACCOUNTVIEW CreateDebtor END = ' . $projectId . ' ' . var_export($result, true));
		
		
		if(!curl_errno($ch) && !isset($result['ErrorMessage'])){
			api2cart_log($projectId, 'exportorders', 'Exported customer '.$customerData['first_name'].' '.$customerData['last_name'].' to Accountview.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in Accountview. Error: '.curl_error($ch));
			return false;
		}
	}
	
	function getDebtors($projectId, $offset = 0, $amount = 10){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);
		
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'PageNumber' => $pageNumber,
			'PageSize' => $amount,
			'BusinessObject' => 'AR1',
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Accept: application/json",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		$result = $result['CONTACT'];
// 		echo '<pre>';print_r($result);exit;
		
		$results = array();
		$removeResults = array();
		$numberOfResults = count($result);
		$counter = 0;
		foreach($result as $customer){
			$counter++;
			$customerName = $customer['ACCT_NAME'];
			$customerName = explode(' ', $customerName);
			$customerFirstName = $customerName[0];
			unset($customerName[0]);
			$customerLastName = implode(' ', $customerName);
			if($customerLastName == ''){
				$customerLastName = ' ';
			}
			
			$email = isset($customer['MAIL_BUS']) ? $customer['MAIL_BUS'] : '';
			$phone = isset($customer['TEL_BUS']) ? $customer['TEL_BUS'] : '';
//$email = 'test'.$counter.'@test.nl';
			
			$customerData = array(
				'email' => $email,
				'first_name' => $customerFirstName,
				'last_name' => $customerLastName,
				'address' => $customer['ADDRESS1'],
				'country' => $customer['CNT_CODE'],
				'postcode' => $customer['POST_CODE'],
				'city' => $customer['BOX_CITY']
			);
			if($phone != ''){
				$customerData['phone'] = $phone;
			}
            
            log_message('debug', 'ACCOUNTVIEW CreateDebtor END = ' . $projectId . ' ' . var_export($customerData, true));
// 			echo '<pre>';print_r($customerData);exit;
			if($customerData['email'] == ''){
				continue;
			}
			$this->Cms_model->createCustomer($projectId, $customerData);
		}
		
		return $counter;
	}
	
	public function getDeliveryAddress($projectId, $address, $debtorId){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);

		if($address['company'] != ''){
			$name = $address['company'];
		} else {
			$name = $address['first_name'].' '.$address['last_name'];
		}
		$postCode = $address['postcode'];
		if(strlen($postCode) == 6){
			$postCode = substr($postCode, 0, 4).' '.substr($postCode, 4, 6);
		}
		$address['postcode'] = $postCode;
		
		$filterData = array(
			'PageSize' => 100,
			'BusinessObject' => 'ADDR',
			'FilterControlSource1' => 'SUB_NR',
			'FilterOperator1' => 'Equal',
			'FilterValueType1' => 'C',
			'FilterValue1' => $debtorId,
			'FilterIsListOfValues1' => 'false'
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($this->apiURL."?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: www.accountview.net",
			"Accept: application/json",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		$results = $result['DEL_ADDR'];
		foreach($results as $result){
			if($result['ADDRESS1'] == $address['address1'] && $result['POST_CODE'] == $address['postcode'] && $result['CITY'] == $address['city']){
				return $result['REC_ID'];
			}
		}
		
		// Create delivery address
		$addressId = $this->createAccountviewDeliveryAddress($projectId, $address, $debtorId);
		if($addressId != false){
			return $addressId;
		}
		return false;
		
	}

	function createAccountviewDeliveryAddress($projectId, $address, $debtorId){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('accountview_company_id', $projectId);

		if($address['company'] != ''){
			$name = $address['company'];
		} else {
			$name = $address['first_name'].' '.$address['last_name'];
		}
		
		$saveData = array(
			'BookDate' => date('Y-m-d').'T'.date('H:i:s').'.000Z',
			'BusinessObject' => 'ADDR',
			'Table' => array(
				'Definition' => array(
					'Name' => 'DEL_ADDR',
					'Fields' => array(
						array(
							'name' => 'RowId',
							'FieldType' => 'C'
						),
						array(
							'name' => 'SUB_NR',
							'FieldType' => 'C'
						),
						array(
							'name' => 'DEL_NAME',
							'FieldType' => 'C'
						),
						array(
							'name' => 'ADDRESS1',
							'FieldType' => 'C'
						),
						array(
							'name' => 'POST_CODE',
							'FieldType' => 'C'
						),
						array(
							'name' => 'CITY',
							'FieldType' => 'C'
						),
						array(
							'name' => 'CNT_CODE',
							'FieldType' => 'C'
						),
						array(
							'name' => 'MAIL_BUS',
							'FieldType' => 'C'
						),
						array(
							'name' => 'TEL_BUS',
							'FieldType' => 'C'
						),
					)
				)
			),
			'TableData' => array(
				'Data' => array(
					'Rows' => array(
						array(
							'values' => array(
								1, // RowId
								$debtorId, // SUB_NR (debiteur)
								$name, // DEL_NAME (bedrijfsnaam)
								$address['address1'], // ADDRESS1 (adres)
								$address['postcode'], // POST_CODE (postcode)
								$address['city'], // CITY (plaatsnaam)
								$address['country'], // ICNT_CODE (landcode)
								$address['email'], // MAIL_BUS (e-mail)
								$address['phone'], // TEL_BUS (telefoon)
							)
						)
					)
				)
			)
		);
// 		echo '<pre>';print_r($saveData);exit;
		
		$saveData = json_encode($saveData);
        log_message('debug', 'ACCOUNTVIEW createAccountviewDeliveryAddress = ' . $projectId . ' ' . var_export($saveData, true));
        
		$ch = curl_init($this->apiURL);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json; charset=UTF-8",
			"Host: www.accountview.net",
			"Authorization: Bearer " . $token,
			"x-company: ".$companyId
		));
		$result = curl_exec($ch);
		$result = json_decode($result, true);
// 		echo '<pre>';print_r($result);exit;
		
		log_message('debug', 'ACCOUNTVIEW createAccountviewDeliveryAddress END = ' . $projectId . ' ' . var_export($result, true));
		if(isset($result['DEL_ADDR']) && !empty($result['DEL_ADDR'])){
			return $result['DEL_ADDR'][0]['REC_ID'];
		}
		return false;
	}
}