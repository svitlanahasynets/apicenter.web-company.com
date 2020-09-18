<?php

// use function GuzzleHttp\json_encode;

class Magento2_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
    /* TOKEN */
    public function getToken($projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$user = $this->Projects_model->getValue('user', $projectId);
		$pass = $this->Projects_model->getValue('password', $projectId);
		$userData = array("username" => $user, "password" => $pass);

       //if ($projectId == 8) log_message('debug', 'UserData ' . var_export($userData, true));

		$ch = curl_init($storeUrl."/rest/V1/integration/admin/token");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen(json_encode($userData))));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		$token = curl_exec($ch);

		if(curl_errno($ch))
		{
		    magento_log($projectId, 'token_err', curl_error($ch));
			//log_message('error', 'Curl error for project '.$projectId.': ' . curl_error($ch));
		}

		$token = json_decode($token);
		if(is_string($token)){
			return $token;
		} else {
			return false;
		}
    }
    
    public function checkLoginCredentials($data = array()){
	    if($data['store_url'] != ''){
			$storeUrl = $data['store_url'];
			$user = $data['username'];
			$pass = $data['password'];
			$userData = array("username" => $user, "password" => $pass);
	
			$ch = curl_init($storeUrl."/rest/V1/integration/admin/token");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
			curl_setopt($ch, CURLOPT_TIMEOUT, 10);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Length: " . strlen(json_encode($userData))));
			
			if($projectId == 8 || $projectId == 84){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			}
			$token = curl_exec($ch);
	
			$token = json_decode($token);
			if(is_string($token)){
				return true;
			} else {
				return false;
			}
		}
		return false;
    }
	
	
	
	
	
	
    /* PRODUCTS */
	public function updateArticles($projectId, $articles){
		$token = $this->getToken($projectId);
		
		
		
		$METRIC_starttime_getArticles = microtime(true);
		apicenter_logs($projectId, 'projectcontrol', 'Initiate GetArticles - Magento - Time: ' . $METRIC_starttime_getArticles, false);
		
		//if ($projectId == 131) {
		    //log_message('debug', 'Articles = '. var_export($articles, true));
		//}
		
		
		foreach($articles as $article){
			$productExists = $this->checkProductExists($article, $projectId);
			if($productExists != false && isset($productExists['items']) && !empty($productExists['items'])){
				// Update product
				$this->updateProduct($article, $projectId);
			} else {
				// Create product
				$this->createProduct($article, $projectId);
				$this->updateProduct($article, $projectId);
			}
		}
	}
	
	public function checkProductExists($productData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		 
		$filterData = array(
			'search_criteria' => array(
				'filter_groups' => array(
					array(
						'filters' => array(
							array(
								'field' => 'sku',
								'value' => $productData['model'],
								'condition_type' => 'like'
							)
						)
					)
				)
			)
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		
		$ch = curl_init($storeUrl."/rest/V1/products?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);

		return json_decode($result, true);
	}
	
	public function getAttributeSets($productData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		 
		$filterData = array(
			'search_criteria' => 0
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($storeUrl."/rest/V1/products/attribute-sets/sets/list?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		return json_decode($result, true);
	}
	
	public function getDefaultAttributeSet($productData, $projectId){
		$attributeSets = $this->getAttributeSets($productData, $projectId);
		if($attributeSets != false && isset($attributeSets['items']) && !empty($attributeSets['items'])){
			$defaultSet = false;
			foreach($attributeSets['items'] as $set){
				if($set['attribute_set_name'] == 'Default'){
					$defaultSet = $set;
				}
			}
			if(!$defaultSet || $defaultSet == '' || empty($defaultSet)){
				$defaultSet = $attributeSets['items'][0];
			}
			return $defaultSet['attribute_set_id'] ? $defaultSet['attribute_set_id'] : 4;
		} else {
			return 4;
		}
		return false;
	}
	
	public function createAttributeValue($attributeCode, $attributeValue, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);

		$ch = curl_init($storeUrl."/rest/V1/products/attributes/".$attributeCode."");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		
		unset($result['is_used_in_grid']);
		unset($result['is_visible_in_grid']);
		unset($result['is_filterable_in_grid']);
		unset($result['options'][0]);
		
		$optionId = false;
		if(isset($result['options'])){
			foreach($result['options'] as $option){
				if($option['label'] == $attributeValue){
					$optionId = $option['value'];
				}
			}
		}
		
		if($optionId == false && $attributeValue != ''){
			$result['options'][] = array(
				'label' => $attributeValue,
				//'value' => $attributeValue,
			);
			$saveData = array('attribute' => $result);
			$saveData = json_encode($saveData);
	
			$token = $this->getToken($projectId);
			$ch = curl_init($storeUrl."/rest/all/V1/products/attributes");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			
			if($projectId == 8 || $projectId == 84){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			}
			
			$result = curl_exec($ch);
			$result = json_decode($result, true);
			if(isset($result['options'])){
				foreach($result['options'] as $option){
					if($option['label'] == $attributeValue){
						$optionId = $option['value'];
					}
				}
			}
		}
		return $optionId;
	}
	
	public function createProduct($productData, $projectId){
	    
	    //if( $projectId == 160) { log_message('debug', 'CreateProduct160' . var_export($productData, true)); }
	    
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		
		// Check for force in stock
		$isInStock = isset($productData['quantity']) ? 1 : 0;
		if($this->Projects_model->getValue('force_in_stock', $projectId) == '1'){
			$isInStock = true;
		}
		
		if($productData['description'] == ''){
			return false;
		}
		
		if ($projectId == 131){
		    //log_message('debug', 'product data = ' . var_export($productData, true));
		    //$sWeight = 0;
		    $sWeight = isset($productData['custom_attributes']['weight']) ? floatval($productData['custom_attributes']['weight']) / 1000 : 0; //Gram --> Kg
		    //log_message('debug', 'sWeight = ' . $sWeight);
		} else {
		    $sWeight = 0.0;
		}
		
		if ($projectId == 131){
		    $sURLgen = $productData['name'];
		}
		else {
		    $sURLgen = $productData['name'].' '.$productData['model'];
		}
		
		$shortDesc = '';
		$longDesc = '';
		
		if ( isset($productData['short_description']) ){
		    $shortDesc = $productData['short_description'];
		}
		else {
		    $shortDesc = $productData['description'];
		}
		if ( isset($productData['long_description']) ){
		    $longDesc = $productData['long_description'];
		}
		else {
		    $longDesc = $productData['description'];
		}
		
		$saveData = array(
			'product' => array(
				'sku' => $productData['model'],
				'name' => $productData['name'],
				'price' => $productData['price'] ? $productData['price'] : 0,
				'status' => 1,
				'type_id' => 'simple',
				'attribute_set_id' => $this->getDefaultAttributeSet($productData, $projectId),
				//'weight' => isset($productData['custom_attributes']['weight']) ? $productData['custom_attributes']['weight'] : 0,
				
				'weight' => $sWeight,
				
				
				'custom_attributes' => array(
					array('attribute_code' => 'description', 'value' => $longDesc),
					array('attribute_code' => 'short_description', 'value' => $shortDesc),
					array('attribute_code' => 'url_key', 'value' => $this->formatUrlKey($sURLgen)),
					array('attribute_code' => 'quantity_and_stock_status', 'value' => array(
						'qty' => isset($productData['quantity']) ? $productData['quantity'] : 0,
						'is_in_stock' => $isInStock
					))
				),
				'extension_attributes' => array(
					'stock_item' => array(
						'qty' => isset($productData['quantity']) ? $productData['quantity'] : 0,
						'is_in_stock' => $isInStock
					)
				)
			)
		);
		
		if(isset($productData['custom_attributes'])){
			foreach($productData['custom_attributes'] as $attributeCode => $attribute){
				if($attribute['type'] == 'dropdown' || $attribute['type'] == 'select'){
					$attributeOptionId = $this->createAttributeValue($attributeCode, $attribute['value'], $projectId);
					if(!$attributeOptionId){
						continue;
					}
					$attribute['value'] = $attributeOptionId;
				} elseif($attribute['type'] == 'multiselect'){
					$valueArray = array();
					if(is_array($attribute['value']) && !empty($attribute['value'])){
						foreach($attribute['value'] as $value){
							$attributeOptionId = $this->createAttributeValue($attributeCode, $value, $projectId);
							if(!$attributeOptionId){
								continue;
							}
							$valueArray[] = $attributeOptionId;
						}
					}
					$attribute['value'] = implode(',', $valueArray);
				}
				$saveData['product']['custom_attributes'][] = array('attribute_code' => $attributeCode, 'value' => $attribute['value']);
			}
		}
		
		// Set category
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
			$saveData['product']['custom_attributes'][] = array( 'attribute_code' => 'category_ids', 'value' => $categoryIds );
		}
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'create');
			}
		}
		
// 		echo '<pre>';print_r($saveData);exit;
		if ($projectId == 4) {
			//log_message('debug', 'project id = ' . $projectId);
			//log_message('debug', 'product data = ' . var_export($productData, true));
			//log_message('debug', 'save data = ' . var_export($saveData, true));
		}
		
		// if($projectId == 84) log_message('debug', 'ProductData Mag ' . var_export($saveData, true));
		
		$saveData = json_encode($saveData);
        
        //if( $projectId == 160) { log_message('debug', 'AFTER_CreateProduct160' . var_export($saveData, true)); }
        
        
		$ch = curl_init($storeUrl."/rest/V1/products/");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		//if ($projectId == 160) log_message('debug', 'PID 160 Temp: '.var_export($result, true));
		$result = json_decode($result, true);
//		echo '<pre>';print_r($result);exit;

        //if ($projectId == 35) log_message('debug', 'PID 35 Temp: '.var_export($productData, true));

		if(isset($result['id']) && $result['id'] > 0){
			if ($projectId == 35){
				apicenter_logs($projectId, 'importarticles', 'Created product '.$productData['model'], false);
			}else {
				apicenter_logs($projectId, 'importarticles', 'Created product '.$productData['model'], false);
			}
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'createProductAfter')){
					$saveData = $this->$projectModel->createProductAfter($result, $productData, $projectId);
				}
			}
			
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not create product '.$productData['model'].'. Result: '.print_r($result, true), true);
		}
		return $result;
	}
	
	public function updateProduct($productData, $projectId){
	    
	    //if( $projectId == 131) { log_message('debug', 'UpdateProduct131' . var_export($productData, true)); }
	    
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		
		$images = array();
		$baseImage = '';
		if(isset($productData['image']) && !empty($productData['image'])){
			$image = $productData['image'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array('image','small_image','thumbnail'),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
			$firstLetter = substr($imageName, 0, 1);
			$secondLetter = substr($imageName, 1, 1);
			$baseImage = '/'.$firstLetter.'/'.$secondLetter.'/'.$imageName;
		}
		
		if ($projectId == 131){
		    $TypeArray = array('image','hover');
		}
		else { $TypeArray = array(); }
		
		if(isset($productData['image_1']) && !empty($productData['image_1'])){
			$image = $productData['image_1'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => $TypeArray,
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		

		
		if(isset($productData['image_2']) && !empty($productData['image_2'])){
			$image = $productData['image_2'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array(),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		if(isset($productData['image_3']) && !empty($productData['image_3'])){
			$image = $productData['image_3'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array(),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		if(isset($productData['image_4']) && !empty($productData['image_4'])){
			$image = $productData['image_4'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array(),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		if(isset($productData['image_5']) && !empty($productData['image_5'])){
			$image = $productData['image_5'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array(),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		if(isset($productData['image_6']) && !empty($productData['image_6'])){
			$image = $productData['image_6'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "media_type" => "image",
			        "label" => $imageName,
			        "position" => 0,
			        "disabled" => false,
			        'types' => array(),
			        "content" => array(
						"base64_encoded_data" => $base64Data,
						"type" => mime_content_type($image['path']),
						"name" => $imageName
			        )
				);
			}
		}
		
		// Check for force in stock
		$isInStock = isset($productData['quantity']) ? 1 : 0;
		if($this->Projects_model->getValue('force_in_stock', $projectId) == '1'){
			$isInStock = true;
		}
        
        if ($projectId == 131){
		    $sURLgen = $productData['name'];
		}
		else {
		    $sURLgen = $productData['name'].' '.$productData['model'];
		}
        
        $sWeight = 0;
        if ($projectId == 131){
		    $sWeight = isset($productData['custom_attributes']['weight']) ? floatval($productData['custom_attributes']['weight']) / 1000 : 0; //Gram --> Kg
		    //log_message('debug', 'sWeight = ' . $sWeight);
		} else {
		    $sWeight = 0.0;
		}
        
        $shortDesc = '';
		$longDesc = '';
		
		if ( isset($productData['short_description']) && $productData['short_description'] != '' ){
		    $shortDesc = $productData['short_description'];
		}
		else {
		    $shortDesc = $productData['description'];
		}
		if ( isset($productData['long_description']) && $productData['long_description'] != '' ) {
		    $longDesc = $productData['long_description'];
		}
		else {
		    $longDesc = $productData['description'];
		}
        
		$saveData = array(
			"saveOptions" => true,
			'product' => array(
				'sku' => $productData['model'],
				'name' => $productData['name'],
				'price' => $productData['price'] ? $productData['price'] : 0,
				'status' => 1,
				'type_id' => 'simple',
				'attribute_set_id' => $this->getDefaultAttributeSet($productData, $projectId),
				
				'weight' => $sWeight,
				
				'custom_attributes' => array(
					array('attribute_code' => 'description', 'value' => $longDesc),
					array('attribute_code' => 'short_description', 'value' => $shortDesc),
					array('attribute_code' => 'url_key', 'value' => $this->formatUrlKey($sURLgen)),
					array('attribute_code' => 'quantity_and_stock_status', 'value' => array(
						'qty' => isset($productData['quantity']) ? $productData['quantity'] : 0,
						'is_in_stock' => $isInStock
					))
				),
				'extension_attributes' => array(
					'stock_item' => array(
						'qty' => isset($productData['quantity']) ? $productData['quantity'] : 0,
						'is_in_stock' => $isInStock
					)
				)
			)
		);
		if(!empty($images)){
			$saveData['product']['media_gallery_entries'] = $images;
		}
		if($baseImage != ''){
			$saveData['product']['custom_attributes'][] = array('attribute_code' => 'image', 'value' => $baseImage);
		}
		
		if(isset($productData['custom_attributes'])){
			foreach($productData['custom_attributes'] as $attributeCode => $attribute){
				if($attribute['type'] == 'dropdown'){
					$attributeOptionId = $this->createAttributeValue($attributeCode, $attribute['value'], $projectId);
					if(!$attributeOptionId){
						continue;
					}
					$attribute['value'] = $attributeOptionId;
				} elseif($attribute['type'] == 'multiselect'){
					$valueArray = array();
					if(is_array($attribute['value']) && !empty($attribute['value'])){
						foreach($attribute['value'] as $value){
							$attributeOptionId = $this->createAttributeValue($attributeCode, $value, $projectId);
							if(!$attributeOptionId){
								continue;
							}
							$valueArray[] = $attributeOptionId;
						}
					}
					$attribute['value'] = implode(',', $valueArray);
				}
				$saveData['product']['custom_attributes'][] = array('attribute_code' => $attributeCode, 'value' => $attribute['value']);
			}
		}
		
		// Set category
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
			$saveData['product']['custom_attributes'][] = array( 'attribute_code' => 'category_ids', 'value' => $categoryIds );
		}
		
		if (isset($productData['multilanguage_attributes'])) {
			$this->updateMultipleProdcut($saveData, $productData, $projectId);
		}
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'update');
			}
		}
//		echo '<pre>';print_r($saveData);exit;
		
		$saveData = json_encode($saveData);
        
       // if( $projectId == 131) { log_message('debug', 'AFTER_UpdateProduct131' . var_export($saveData, true)); }
        
// 		$ch = curl_init($storeUrl."/rest/all/V1/products");
		$ch = curl_init($storeUrl."/rest/V1/products");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		
		//if ($projectId == 35) log_message('debug', 'PID 35 Temp: '.var_export($productData, true));
		
		if(isset($result['id']) && $result['id'] > 0){
			if ($projectId == 35) {
			    $Status = ($productData['tmp']['status'] == 1) ? "Ingeschakeld" : "Uitgeschakeld";
			    apicenter_logs($projectId, 'importarticles', 'Updated product '.$productData['model'] . ' Status => ' . $Status, false);
			}
			else {
				apicenter_logs($projectId, 'importarticles', 'Updated product '.$productData['model'], false);
			}
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'updateProductAfter')){
					$saveData = $this->$projectModel->updateProductAfter($result, $productData, $projectId);
				}
			}
			
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not update product '.$productData['model'].'. Result: '.print_r($result, true), true);
			if(strpos($result['message'], 'The image content must be valid base64') !== false){
				//log_message('error', 'Could not update product '.$productData['model'].'. Images:');
				//log_message('error', var_export($images, true));
			}
		}
		return $result;
	}
	
	public function removeArticles($projectId, $articles){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];

		foreach($articles as $itemCode){
			$token = $this->getToken($projectId);
			
			$ch = curl_init($storeUrl."/rest/default/V1/products/".$itemCode);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			
			if($projectId == 8 || $projectId == 84){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			}
			
			$result = curl_exec($ch);
			$result = json_decode($result, true);
			if(isset($result['id']) && $result['id'] > 0){
				apicenter_logs($projectId, 'importarticles', 'Removed product '.$itemCode, false);
			} else {
				apicenter_logs($projectId, 'importarticles', 'Could not remove product '.$itemCode.'. Result: '.print_r($result, true), false);
			}
		}
	}
	
	public function findCategory($projectId, $categoryName){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		 
		$filterData = array(
			'search_criteria' => array(
				'filter_groups' => array(
					array(
						'filters' => array(
							array(
								'field' => 'name',
								'value' => $categoryName,
								'condition_type' => 'eq'
							)
						)
					)
				)
			)
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($storeUrl."/rest/V1/categories/list?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		return json_decode($result, true);
	}
	
	function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		
		$saveData = array(
			'category' => array(
				'name' => $categoryName,
				'is_active' => true,
				'include_in_menu' => true,
				'custom_attributes' => array(
					array('attribute_code' => 'url_key', 'value' => $this->formatUrlKey($categoryName)),
				),
			)
		);
		if($parentId != ''){
			$saveData['category']['parent_id'] = $parentId;
		}
		$saveData = json_encode($saveData);
		 
		$ch = curl_init($storeUrl."/rest/V1/categories/");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);
		if(isset($result['id']) && $result['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Created category '.$categoryName, false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.print_r($result, true), false);
		}
		return $result;
	}
	
	public function updateStockArticles($projectId, $articles){
	    
	    //log_message('debug', 'ProductStock - Magento2  ' . $projectId . ' Result:' . var_export($projectId, true));
	    
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		
		//log_message('debug', 'ProductStock - Magento2 - 2 ' . $projectId . ' Result:' . var_export($articles, true));
		
		foreach($articles as $productData){
			$token = $this->getToken($projectId);
			
			$productExists = $this->checkProductExists($productData, $projectId);
			
			//log_message('debug', 'ProductStock - Magento2 - 3 ' . $projectId . ' Result:' . var_export($productExists, true));
			
			if($productExists != false && isset($productExists['items']) && !empty($productExists['items'])){
		
				$saveData = array(
					'product' => array(
						'sku' => $productData['model'],
						'extensionAttributes' => array(
							'stockItem' => array(
								'stockId' => 1,
								'qty' => $productData['quantity'],
								'isInStock' => true
							)
						)
					)
				);
		
				$ch = curl_init($storeUrl."/rest/V1/products/");
				curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
				curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
				
				if($projectId == 8 || $projectId == 84){
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				}
				
				$result = curl_exec($ch);
				$result = json_decode($result, true);
				if(isset($result['id']) && $result['id'] > 0){
					apicenter_logs($projectId, 'importarticles', 'Updated product stock for product '.$productData['model'], false);
				} else {
					apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$productData['model'].'. Result: '.print_r($result, true), true);
				}
			}
		}
	}
	
    public function formatUrlKey($str)
    {
	    $str = $this->clearUTF($str);
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $this->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

	public function clearUTF($s)
	{
	    $r = '';
	    $s1 = iconv('UTF-8', 'ASCII//TRANSLIT', $s);
	    for ($i = 0; $i < strlen($s1); $i++)
	    {
	        $ch1 = $s1[$i];
	        $ch2 = mb_substr($s, $i, 1);
	
	        $r .= $ch1=='?'?$ch2:$ch1;
	    }
	    return $r;
	}

    public function format($string)
    {
		$_convertTable = json_decode('{"&":"and","@":"at","\u00a9":"c","\u00ae":"r","\u00c0":"a","\u00c1":"a","\u00c2":"a","\u00c4":"a","\u00c5":"a","\u00c6":"ae","\u00c7":"c","\u00c8":"e","\u00c9":"e","\u00cb":"e","\u00cc":"i","\u00cd":"i","\u00ce":"i","\u00cf":"i","\u00d2":"o","\u00d3":"o","\u00d4":"o","\u00d5":"o","\u00d6":"o","\u00d8":"o","\u00d9":"u","\u00da":"u","\u00db":"u","\u00dc":"u","\u00dd":"y","\u00df":"ss","\u00e0":"a","\u00e1":"a","\u00e2":"a","\u00e4":"a","\u00e5":"a","\u00e6":"ae","\u00e7":"c","\u00e8":"e","\u00e9":"e","\u00ea":"e","\u00eb":"e","\u00ec":"i","\u00ed":"i","\u00ee":"i","\u00ef":"i","\u00f2":"o","\u00f3":"o","\u00f4":"o","\u00f5":"o","\u00f6":"o","\u00f8":"o","\u00f9":"u","\u00fa":"u","\u00fb":"u","\u00fc":"u","\u00fd":"y","\u00fe":"p","\u00ff":"y","\u0100":"a","\u0101":"a","\u0102":"a","\u0103":"a","\u0104":"a","\u0105":"a","\u0106":"c","\u0107":"c","\u0108":"c","\u0109":"c","\u010a":"c","\u010b":"c","\u010c":"c","\u010d":"c","\u010e":"d","\u010f":"d","\u0110":"d","\u0111":"d","\u0112":"e","\u0113":"e","\u0114":"e","\u0115":"e","\u0116":"e","\u0117":"e","\u0118":"e","\u0119":"e","\u011a":"e","\u011b":"e","\u011c":"g","\u011d":"g","\u011e":"g","\u011f":"g","\u0120":"g","\u0121":"g","\u0122":"g","\u0123":"g","\u0124":"h","\u0125":"h","\u0126":"h","\u0127":"h","\u0128":"i","\u0129":"i","\u012a":"i","\u012b":"i","\u012c":"i","\u012d":"i","\u012e":"i","\u012f":"i","\u0130":"i","\u0131":"i","\u0132":"ij","\u0133":"ij","\u0134":"j","\u0135":"j","\u0136":"k","\u0137":"k","\u0138":"k","\u0139":"l","\u013a":"l","\u013b":"l","\u013c":"l","\u013d":"l","\u013e":"l","\u013f":"l","\u0140":"l","\u0141":"l","\u0142":"l","\u0143":"n","\u0144":"n","\u0145":"n","\u0146":"n","\u0147":"n","\u0148":"n","\u0149":"n","\u014a":"n","\u014b":"n","\u014c":"o","\u014d":"o","\u014e":"o","\u014f":"o","\u0150":"o","\u0151":"o","\u0152":"oe","\u0153":"oe","\u0154":"r","\u0155":"r","\u0156":"r","\u0157":"r","\u0158":"r","\u0159":"r","\u015a":"s","\u015b":"s","\u015c":"s","\u015d":"s","\u015e":"s","\u015f":"s","\u0160":"s","\u0161":"s","\u0162":"t","\u0163":"t","\u0164":"t","\u0165":"t","\u0166":"t","\u0167":"t","\u0168":"u","\u0169":"u","\u016a":"u","\u016b":"u","\u016c":"u","\u016d":"u","\u016e":"u","\u016f":"u","\u0170":"u","\u0171":"u","\u0172":"u","\u0173":"u","\u0174":"w","\u0175":"w","\u0176":"y","\u0177":"y","\u0178":"y","\u0179":"z","\u017a":"z","\u017b":"z","\u017c":"z","\u017d":"z","\u017e":"z","\u017f":"z","\u018f":"e","\u0192":"f","\u01a0":"o","\u01a1":"o","\u01af":"u","\u01b0":"u","\u01cd":"a","\u01ce":"a","\u01cf":"i","\u01d0":"i","\u01d1":"o","\u01d2":"o","\u01d3":"u","\u01d4":"u","\u01d5":"u","\u01d6":"u","\u01d7":"u","\u01d8":"u","\u01d9":"u","\u01da":"u","\u01db":"u","\u01dc":"u","\u01fa":"a","\u01fb":"a","\u01fc":"ae","\u01fd":"ae","\u01fe":"o","\u01ff":"o","\u0259":"e","\u0401":"jo","\u0404":"e","\u0406":"i","\u0407":"i","\u0410":"a","\u0411":"b","\u0412":"v","\u0413":"g","\u0414":"d","\u0415":"e","\u0416":"zh","\u0417":"z","\u0418":"i","\u0419":"j","\u041a":"k","\u041b":"l","\u041c":"m","\u041d":"n","\u041e":"o","\u041f":"p","\u0420":"r","\u0421":"s","\u0422":"t","\u0423":"u","\u0424":"f","\u0425":"h","\u0426":"c","\u0427":"ch","\u0428":"sh","\u0429":"sch","\u042a":"-","\u042b":"y","\u042c":"-","\u042d":"je","\u042e":"ju","\u042f":"ja","\u0430":"a","\u0431":"b","\u0432":"v","\u0433":"g","\u0434":"d","\u0435":"e","\u0436":"zh","\u0437":"z","\u0438":"i","\u0439":"j","\u043a":"k","\u043b":"l","\u043c":"m","\u043d":"n","\u043e":"o","\u043f":"p","\u0440":"r","\u0441":"s","\u0442":"t","\u0443":"u","\u0444":"f","\u0445":"h","\u0446":"c","\u0447":"ch","\u0448":"sh","\u0449":"sch","\u044a":"-","\u044b":"y","\u044c":"-","\u044d":"je","\u044e":"ju","\u044f":"ja","\u0451":"jo","\u0454":"e","\u0456":"i","\u0457":"i","\u0490":"g","\u0491":"g","\u05d0":"a","\u05d1":"b","\u05d2":"g","\u05d3":"d","\u05d4":"h","\u05d5":"v","\u05d6":"z","\u05d7":"h","\u05d8":"t","\u05d9":"i","\u05da":"k","\u05db":"k","\u05dc":"l","\u05dd":"m","\u05de":"m","\u05df":"n","\u05e0":"n","\u05e1":"s","\u05e2":"e","\u05e3":"p","\u05e4":"p","\u05e5":"C","\u05e6":"c","\u05e7":"q","\u05e8":"r","\u05e9":"w","\u05ea":"t","\u2122":"tm"}', true);
        return strtr($string, $_convertTable);
    }
	
	
    /* CUSTOMERS */
	public function createCustomer($projectId, $customerData){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
        $erpSystem = $project['erp_system'];

		$customerExists = $this->checkCustomerExists($customerData, $projectId);
		if($customerExists != false && isset($customerExists['items']) && !empty($customerExists['items'])){
		
			$token = $this->getToken($projectId);
	
			$saveData = array(
				'customer' => array(
					'id' => $customerExists['items'][0]['id'],
					'store_id' => 1,
					'website_id' => 1,
					'email' => $customerData['email'],
					'firstname' => $customerData['first_name'],
					'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'addresses' => array(
						array(
							'country_id' => $customerData['country'],
							'street' => array($customerData['address']),
							'telephone' => isset($customerData['phone']) ? $customerData['phone'] : '0',
							'postcode' => $customerData['postcode'],
							'city' => $customerData['city'],
							'firstname' => $customerData['first_name'],
							'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
							'default_shipping' => true,
							'default_billing' => true,
						)
					)
				)
			);
			
			$saveData = json_encode($saveData);
	
			$ch = curl_init($storeUrl."/rest/V1/customers/".$customerExists['items'][0]['id']);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			
			if($projectId == 8 || $projectId == 84){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			}
			
			$result = curl_exec($ch);
			$result = json_decode($result, true);
			if(isset($result['id']) && $result['id'] > 0){
				apicenter_logs($projectId, 'importcustomers', 'Updated customer '.$customerData['email'], false);
			} else {
				apicenter_logs($projectId, 'importcustomers', 'Could not update customer '.$customerData['email'].'. Result: '.print_r($result, true), true);
			}
			return $result;
		} else {
			// Create customer
			$token = $this->getToken($projectId);
			//if ($projectId == 4 || $projectId == 44 ) {
			//	log_message('debug', 'mg2 token = '. var_export($token));
			//}
            if ($erpSystem  == 'exactonline') {
				$saveData = array(
					'customer' => array(
						'store_id' => 1,
						'website_id' => 1,
						'email' => $customerData['email'],
						'firstname' => $customerData['first_name'],
						'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						'addresses' => array(
							array(
								'country_id' => $customerData['address_book_country_1'],
								'street' => array($customerData['address_book_address1_1']),
								'telephone' => isset($customerData['address_book_phone_1']) ? $customerData['address_book_phone_1'] : '0',
								'postcode' => $customerData['address_book_postcode_1'],
								'city' => $customerData['address_book_city_1'],
								'firstname' => $customerData['first_name'],
								'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
								'company' => ($customerData['company'] != '' && $customerData['company'] != ' ') ? $customerData['company'] : '',
								'default_shipping' => true,
								'default_billing' => true,
							)
					    )
				    )
			    );
            } else {
				$saveData = array(
					'customer' => array(
						'store_id' => 1,
						'website_id' => 1,
						'email' => $customerData['email'],
						'firstname' => $customerData['first_name'],
						'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						'addresses' => array(
							array(
								'country_id' => $customerData['country'],
								'street' => array($customerData['address']),
								'telephone' => isset($customerData['phone']) ? $customerData['phone'] : '0',
								'postcode' => $customerData['postcode'],
								'city' => $customerData['city'],
								'firstname' => $customerData['first_name'],
								'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
								'company' => ($customerData['company'] != '' && $customerData['company'] != ' ') ? $customerData['company'] : '',
								'default_shipping' => true,
								'default_billing' => true,
								)
								)
								)
							);
						}
						//if ($projectId == 4 ) {
							//log_message('debug', 'customer data = ' . var_export($saveData, true));
							//log_message('debug', 'customer info = ' . var_export($customerData, true));
						//}
						
						/*
						array (
							'email' => 'jelle@web-company.nl2',
							'first_name' => 'Test',
							'last_name' => ' ',
							'address' => 'Havendijk 22',
							'country' => 'NL',
							'postcode' => '4201XA',
							'city' => 'Gorinchem',
							'company' => 'Test asdf',
						)
						$saveData = json_encode($saveData);
						
						$ch = curl_init($storeUrl."/rest/all/V1/customers");
						curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			 
			$result = curl_exec($ch);
			$result = json_decode($result, true);
			if(isset($result['id']) && $result['id'] > 0){
				api2cart_log($projectId, 'importcustomers', 'Created customer '.$customerData['email']);
			} else {
				api2cart_log($projectId, 'importcustomers', 'Could not create customer '.$customerData['email'].'. Result: '.print_r($result, true));
			}
			return $result;
			*/
			//if ($projectId == 4 || $projectId ==44 || $projectId == 57) {
			//	log_message('debug', 'user info' . var_dump($customerData, true));
			//}
			if (
				$customerData['email'] != '' && 
				($customerData['postcode'] || $customerData['address_book_postcode_1']) && 
				($customerData['countryId'] || $customerData['country'] || $customerData['address_book_country_1']) && 
				($customerData['street'] || $customerData['address'] || $customerData['address_book_address1_1'])
			) {
				$customerData['address_book_postcode_1'] = '';
			// if($customerData['email'] != '' && $customerData['postcode'] != '' && $customerData['countryId'] != '' && $customerData['street'] != ''){
                $saveData = json_encode($saveData);
				//if ($projectId == 4 || $projectId ==44 || $projectId == 57) {
				//	log_message('debug', 'user info json' . var_export($saveData, true));
				//}
                $ch = curl_init($storeUrl."/rest/all/V1/customers");
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
                curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
				
				if($projectId == 8 || $projectId == 84){
					curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
					curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
				}
				
                $result = curl_exec($ch);
				$result = json_decode($result, true);

                if(isset($result['id']) && $result['id'] > 0){
                    apicenter_logs($projectId, 'importcustomers', 'Created customer '.$customerData['email'], false);
                } else {
                    apicenter_logs($projectId, 'importcustomers', '   Could not create customer '.$customerData['email'].'. Result: '.print_r($result, true), true);
                }
                return $result;
            }
            else {
                apicenter_logs($projectId, 'importcustomers', ' cant create customer there is missing info:'. print_r($customerData, true), true);
            }
		}
	}
	
	public function checkCustomerExists($customerData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		 
		$filterData = array(
			'search_criteria' => array(
				'filter_groups' => array(
					array(
						'filters' => array(
							array(
								'field' => 'email',
								'value' => $customerData['email'],
								'condition_type' => 'eq'
							)
						)
					)
				)
			)
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($storeUrl."/rest/V1/customers/search?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		return json_decode($result, true);
	}
	
	
	
	
	
	
    /* ORDERS */
	public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc'){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		
		$filterData = array(
			'search_criteria' => array(
				'filter_groups' => array(
					array(
						'filters' => array(
							array(
								'field' => 'entity_id',
								'value' => $offset,
								'condition_type' => 'gteq'
							)
						)
					)
				),
				'page_size' => $amount,
				//'current_page' => ($offset / $amount) + 1,
				'sort_orders' => array(
					array(
						'field' => 'entity_id',
						'direction' => $sortOrder
					)
				)
			)
		);
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);
		$ch = curl_init($storeUrl."/rest/V1/orders?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		if($projectId == 8 || $projectId == 84){
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		}
		
		$result = curl_exec($ch);
		$orders = json_decode($result, true);
		$orderCount = 0;
		if(isset($orders['items']) && !empty($orders['items'])){
			$orders = $orders['items'];
			$orderCount = count($orders);
			$finalOrders = array();
			foreach($orders as $order){
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'orderBeforeSend')){
						$order = $this->$projectModel->orderBeforeSend($order);
					}
				}
				
				$appendItem = array(
					'id' => $order['increment_id'],
					'order_id' => $order['entity_id'],
					'store_id' => $order['store_id'],
					'state' => $order['state'],
					'status' => $order['status'],
					'customer' => array(
						'id' => isset($order['customer_id']) ? $order['customer_id'] : '',
						'email' => $order['customer_email'],
						'first_name' => isset($order['customer_firstname']) ? $order['customer_firstname'] : '',
						'last_name' => isset($order['customer_lastname']) ? $order['customer_lastname'] : '',
						'customer_group_id' => isset($order['customer_group_id']) ? $order['customer_group_id'] : '',
					),
					'create_at' => $order['created_at'],
					'modified_at' => $order['updated_at'],
					'currency' => $order['order_currency_code'],
					'totals' => array(
						'total' => $order['grand_total'],
						'subtotal' => $order['subtotal'],
						'shipping' => $order['shipping_amount'],
						'tax' => $order['tax_amount'],
						'discount' => $order['discount_amount'],
						'amount_paid' => isset($order['total_paid']) ? $order['total_paid'] : 0
					)
				);
				if(isset($order['billing_address']) && !empty($order['billing_address'])){
					$appendItem['billing_address'] = array(
						'id' => $order['billing_address']['entity_id'],
						'type' => $order['billing_address']['address_type'],
						'first_name' => $order['billing_address']['firstname'],
						'last_name' => $order['billing_address']['lastname'],
						'postcode' => $order['billing_address']['postcode'],
						'address1' => $order['billing_address']['street'][0],
						'address2' => isset($order['billing_address']['street'][1]) ? $order['billing_address']['street'][1] : '',
						'phone' => $order['billing_address']['telephone'],
						'city' => $order['billing_address']['city'],
						'country' => $order['billing_address']['country_id'],
						'state' => isset($order['billing_address']['region']) ? $order['billing_address']['region'] : '',
						'company' => isset($order['billing_address']['company']) ? $order['billing_address']['company'] : '',
						'gender' => isset($order['customer_gender']) ? $order['customer_gender'] : '',
					);
				}
				if(isset($order['extension_attributes']) && isset($order['extension_attributes']['shipping_assignments']) && isset($order['extension_attributes']['shipping_assignments'][0]) && isset($order['extension_attributes']['shipping_assignments'][0]['shipping']) && isset($order['extension_attributes']['shipping_assignments'][0]['shipping']['address'])){
					$appendItem['shipping_address'] = array(
						'id' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['entity_id'],
						'type' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['address_type'],
						'first_name' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['firstname'],
						'last_name' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['lastname'],
						'postcode' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['postcode'],
						'address1' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][0],
						'address2' => isset($order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][1]) ? $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['street'][1] : '',
						'phone' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['telephone'],
						'city' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['city'],
						'country' => $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['country_id'],
						'state' => isset($order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region']) ? $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['region'] : '',
						'company' => isset($order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['company']) ? $order['extension_attributes']['shipping_assignments'][0]['shipping']['address']['company'] : '',
						'gender' => isset($order['customer_gender']) ? $order['customer_gender'] : '',
					);
				}
				if(isset($order['extension_attributes']) && isset($order['extension_attributes']['shipping_assignments']) && isset($order['extension_attributes']['shipping_assignments'][0]) && isset($order['extension_attributes']['shipping_assignments'][0]['shipping']) && isset($order['extension_attributes']['shipping_assignments'][0]['shipping']['method'])){
					$appendItem['shipping_method'] = $order['extension_attributes']['shipping_assignments'][0]['shipping']['method'];
				}
				if(isset($order['payment']) && !empty($order['payment'])){
					$appendItem['payment_method'] = $order['payment']['method'];
				}
				
				if ($projectId == 131) {
				    
				    $appendItem['billing_address']['address1']  = $appendItem['billing_address']['address1'] . ' ' . $appendItem['billing_address']['address2'];
				    $appendItem['shipping_address']['address1'] = $appendItem['shipping_address']['address1'] . ' ' . $appendItem['shipping_address']['address2'];
				}
				
				
				
				
				if(isset($order['items']) && !empty($order['items'])){
					$appendItem['order_products'] = array();
					foreach($order['items'] as $item){
						if(isset($item['parent_item_id']) && $item['parent_item_id'] > 0){
							continue;
						}
						
						$ExactSKU = $item['sku'];
						if ($projectId == 68)
						{
						    if ($item['sku'] == 'Nieuw Apple AirPods') $ExactSKU = 'airpods';
						    else if ($item['sku'] == 'IPDAIR16SILA') $ExactSKU = 'IPADA16WSIL';
						    else if ($item['sku'] == 'Refurbished Samsung Galaxy Tab A 9.7 White 16GB Wi-fi SM-T550') $ExactSKU = 'SAMTABA16';
						    else if ($item['sku'] == 'Refurbished Apple iPad Air 32GB Space Gray Wi-Fi + Cellular') $ExactSKU = 'IPADA32SG';
						    else if ($item['sku'] == 'Refurbished Apple iPad 3 - 16GB WiFi + 3G-Black-White') $ExactSKU = 'IP316GBmarge';
						    else if ($item['sku'] == 'Refurbished Apple iPad 3 - 16GB WiFi + 3G-Black-Black') $ExactSKU = 'IP316GBmarge';
						    else if ($item['sku'] == 'Refurbished Apple iPad 3 - 32GB WiFi-White-White') $ExactSKU = 'IP332GBmarge';
						    else if ($item['sku'] == 'Refurbished Apple iPad 3 - 32GB WiFi-White-Black') $ExactSKU = 'IP332GBmarge';
						    else if ($item['sku'] == 'apple earpods') $ExactSKU = 'headphones';
						    else if ($item['sku'] == 'Celly® Transparant Siliconenhoesje') $ExactSKU = 'gel case';
						    else if ($item['sku'] == 'Celly - Qi Draadloze Oplader - 10W') $ExactSKU = 'wireless charger';
						    else if ($item['sku'] == 'Screenprotector Gehard Glas') $ExactSKU = 'iphone 6/7/8 tempered glass';
						    else if ($item['sku'] == 'IP7032BLAA') $ExactSKU = 'IP7032BLmarge';
						    else if ($item['sku'] == 'Apple USB kabel naar Lightning - 2m -') $ExactSKU = 'Apple2MeterCable';
						    else if ($item['sku'] == 'IP7P128BLAA') $ExactSKU = 'IP7P128BLmarge';
						    else if ($item['sku'] == 'IP7P032BLAA') $ExactSKU = 'IP7P032BLmarge';
						    else if ($item['sku'] == 'IP7032BLAA') $ExactSKU = 'IP7032BLmarge';
						    else if ($item['sku'] == 'IP8064REDA') $ExactSKU = 'IP8064RDmarge';
						    else if ($item['sku'] == 'earpods35') $ExactSKU = 'Headphones';
						    else if ($item['sku'] == 'IP7128BLAA') $ExactSKU = 'IP7128BLmarge';
						    else if ($item['sku'] == 'IPXR64BLAA') $ExactSKU = 'ipxr64blamarge';
						    else if ($item['sku'] == 'IPXR64BLAB') $ExactSKU = 'ipxr64blamarge';
						    else if ($item['sku'] == 'IPXR64BLUA') $ExactSKU = 'ipxr64bluemarge';
						    else if ($item['sku'] == 'IPXR64BLUB') $ExactSKU = 'ipxr64bluemarge';
						    else if ($item['sku'] == 'ipxr64yelA') $ExactSKU = 'ipxr64yelmarge';
						    else if ($item['sku'] == 'ipxr64yelB') $ExactSKU = 'ipxr64yelmarge';
						    else if ($item['sku'] == 'ipxr64redA') $ExactSKU = 'ipxr64redmarge';
						    else if ($item['sku'] == 'ipxr64redB') $ExactSKU = 'ipxr64redmarge';
						    else if ($item['sku'] == 'ipxr64whiA') $ExactSKU = 'ipxr64whimarge';
						    else if ($item['sku'] == 'ipxr64whiB') $ExactSKU = 'ipxr64whimarge';
						    else if ($item['sku'] == 'ipxr64corA') $ExactSKU = 'ipxr64cormarge';
						    else if ($item['sku'] == 'ipxr64corB') $ExactSKU = 'ipxr64cormarge';
						    else if ($item['sku'] == 'IP7032BLAB') $ExactSKU = 'IP7P032BLmarge';
						    else if ($item['sku'] == 'IP6S64ROGA') $ExactSKU = 'IP6S64ROGmarge';
						    else if ($item['sku'] == 'IP6S64ROGB') $ExactSKU = 'IP6S64ROGmarge';
						    else if ($item['sku'] == 'IPX064SPGB') $ExactSKU = 'IPX064SPGmarge';
						    else if ($item['sku'] == 'IPX064SPGA') $ExactSKU = 'IPX064SPGmarge';
						    else if ($item['sku'] == 'IPSE16ROGB') $ExactSKU = 'IPSE16ROGmarge';
						    else if ($item['sku'] == 'IPSE16ROGA') $ExactSKU = 'IPSE16ROGmarge';
						    else if ($item['sku'] == 'IP7032SILB') $ExactSKU = 'IP7032SILmarge';
						    else if ($item['sku'] == 'IP7032SILA') $ExactSKU = 'IP7032SILmarge';
						    else if ($item['sku'] == 'IP7032ROGB') $ExactSKU = 'IP7032ROGmarge';
						    else if ($item['sku'] == 'IP7032ROGA') $ExactSKU = 'IP7032ROGmarge';
						    else if ($item['sku'] == 'IP7128BLAA') $ExactSKU = 'IP7128BLAA';
						    else if ($item['sku'] == 'IP7128BLAB') $ExactSKU = 'IP7128BLAA';
						    else if ($item['sku'] == 'IP8P064REDB') $ExactSKU = 'IP8P064REDB';
						    else if ($item['sku'] == 'IP8P064REDA') $ExactSKU = 'IP8P064REDB';
						    else if ($item['sku'] == '8021735744153') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735744023') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735730361') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735721383') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735721406') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735073390') $ExactSKU = 'Gelskin Celly';
						    else if ($item['sku'] == '8021735100119') $ExactSKU = 'Apple2MeterCable';
						    else if ($item['sku'] == 'earpods35') $ExactSKU = 'Headphones';
						    else if ($item['sku'] == 'cellyqi10w') $ExactSKU = 'Celly Qi charger';
						    else if ($item['sku'] == 'earpodsnew') $ExactSKU = ' AppleHeadphonesNEW';
							else if ($item['sku'] == 'IP7128BLAB') $ExactSKU = 'IP7128BLmarge';
                            else if ($item['sku'] == 'IP8256GOLA') $ExactSKU = 'IP8256GOLmarge';
                            else if ($item['sku'] == 'IPADM4016SGRB') $ExactSKU = 'IpadMini416gbwifiGrey';
                            else if ($item['sku'] == 'IPXR64BLAA') $ExactSKU = 'IPXR64BLAmarge';
                            else if ($item['sku'] == 'IPXR64BLAB') $ExactSKU = 'IPXR64BLAmarge';
                            else if ($item['sku'] == 'IPXR64REDA') $ExactSKU = 'ipxr64redmarge';
                            else if ($item['sku'] == 'IPXR64REDB') $ExactSKU = 'ipxr64redmarge';
                            else if ($item['sku'] == 'IPXR64WHIA') $ExactSKU = 'ipxr64whimarge';
                            else if ($item['sku'] == 'IPXR64WHIB') $ExactSKU = 'ipxr64whimarge';
                            else if ($item['sku'] == 'IPXR64BLUA') $ExactSKU = 'IPXR64BLUmarge';
                            else if ($item['sku'] == 'IPXR64BLUB') $ExactSKU = 'IPXR64BLUmarge';
                            else if ($item['sku'] == 'IPXR64YELA') $ExactSKU = 'IPXR64YELmargin';
                            else if ($item['sku'] == 'IPXR64YELB') $ExactSKU = 'IPXR64YELmargin';
                            else if ($item['sku'] == 'IPXR64CORA') $ExactSKU = 'ipxr64cormarge';
                            else if ($item['sku'] == 'IPXR64CORB') $ExactSKU = 'ipxr64cormarge';
						    else {
                                $ExactSKU = substr($item['sku'], 0, -1);
						        $ExactSKU = $ExactSKU . 'Marge';
                            }
						}
						// if ($projectId == 131) log_message('debug', 'Project 68 Exact SKU '.$projectId.': ' . var_export($ExactSKU, true));
						
						
						$appendItem['order_products'][] = array(
							'product_id' => $item['product_id'],
							'order_product_id' => $item['product_id'],
							'model' => $ExactSKU,
							'name' => $item['name'],
							'price' => $item['price'],
							'discount_amount' => isset($item['discount_amount']) ? ($item['discount_amount'] - $item['discount_tax_compensation_amount']) : 0,
							'quantity' => $item['qty_ordered'],
							'total_price' => $item['row_total'],
							'total_price_incl_tax' => isset($item['row_total_incl_tax']) ? $item['row_total_incl_tax'] : 0,
							'tax_percent' => isset($item['tax_percent']) ? $item['tax_percent'] : 0,
							'tax_value' => isset($item['tax_amount']) ? $item['tax_amount'] : 0,
							'variant_id' => ''
						);
					}
				}
				if(isset($order['status_histories']) && !empty($order['status_histories'])){
					$appendItem['comment'] = $order['status_histories'][0]['comment'];
				}
				
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'loadCustomOrderAttributes')){
						$appendItem = $this->$projectModel->loadCustomOrderAttributes($appendItem, $order, $projectId);
					}
				}
				
				if($appendItem != false){
					$finalOrders[] = $appendItem;
				}
				//if ($projectId == 131) log_message('debug', 'Orders 131' . var_dump($finalOrders, true));
			}
			return array('count' => $orderCount, 'orders' => $finalOrders);
		}
		return false;
	}

	public function getProducts($projectId, $token, $shopUrl, $amount, $offset = 0, $lastDate = '') {
        $url = $shopUrl."/rest/V1/products?searchCriteria[pageSize]=".$amount;
        if($offset != 0){
            $url .= "&searchCriteria[currentPage]=".$offset;
        }

        if($lastDate != '') {
            $url .= '&searchCriteria[filter_groups][0][filters][0][field]=updated_at&'
                . 'searchCriteria[filter_groups][0][filters][0][value]=' . urlencode($lastDate)
                . '&searchCriteria[filter_groups][0][filters][0][condition_type]=gt';
        }

        magento_log($projectId, 'products_req', $url);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        magento_log($projectId, 'products_resp', $result);
        $result = json_decode($result, true);
        curl_close($ch);

        return $result;
    }

	public function getSuppliersWithItems($projectId, $amount, $offset = 0) {

	    $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);

        $result = $this->getProducts($projectId, $token, $shopUrl, $amount, $offset);
		magento_log($projectId, 'suppliers', json_encode($result));

        $resultData = [];
        $supplierList = $this->getSupplierList($token, $shopUrl);

        if(!isset($result['items'])) {
            return [];
        }

        foreach ($result['items'] as $item) {

            //Search for supplier in custom attributes
            $supplier = 'default';
            foreach ($item['custom_attributes'] as $attribute) {
                if($attribute['attribute_code'] == 'supplier') {
                    $supplier = $supplierList[$attribute['value']];
                }
            }

            $stockData = $this->getProductStock($token, $shopUrl, $item['sku']);
            $status = $item['status'] == 1 ? 'ENABLED' : 'DISABLED';

            $supplierData = [
                'name' => $item['name'],
                'price' => $this->getPurchasePrice($projectId, $token, $shopUrl, $item['sku']),
                'priceStandart' => $item['price'],
                'minQuantity' => $stockData['min_qty'],
                'supplierItemCode' => $item['sku'],
                'lotSize' => 0,
                'stock' => $stockData['qty'],
                'code' => $item['sku'],
                'barcode' => $item['sku'],
                'status' => $status
            ];

            $resultData[$supplier]['items'][] = $supplierData;
        }

        $suppliersArray = [];
        foreach ($resultData as $name => $data) {
            $suppliersArray[] = [
                'name' => $name,
                'items' => $data['items']
            ];
        }

        return $suppliersArray;
    }

    public function getPurchasePrice($projectId, $token, $shopUrl, $sku) {

	    $url = $shopUrl.'/rest/V1/products/cost-information';

	    $data = [
	        'skus' => [$sku]
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        magento_log($projectId, 'get_cost_resp', $result);
        $result = json_decode($result, true);
        curl_close($ch);

        if(empty($result)) {
            return 0;
        }

        return $result[0]['cost'];
    }

    public function getSupplierList($token, $storeUrl) {
        $url = $storeUrl.'/rest/V1/products/attributes/supplier';

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        $result = json_decode($result, true);

        $list = [];

        foreach ($result['options'] as $option) {
            if($option['value'] != '') {
                $list[$option['value']] = $option['label'];
            }
        }

        return $list;
    }

    public function getProductStock($token, $shopUrl, $sku) {

	    $url = $shopUrl.'/rest/V1/stockItems/'.urlencode($sku);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        magento_log(0, 'result_stock', json_encode($result));

        $response['qty'] = $result['qty'];
        $response['min_qty'] = 0;

        if($result['use_config_min_qty'] == true) {
            $response['min_qty'] = $result['min_qty'];
        }

        return $response;
    }

    public function getSellOrders($projectId, $amount, $offset = 0) {

	    $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);
        $orderStatus= $this->Projects_model->getValue('order_status', $projectId);

        $url = $shopUrl."/rest/V1/orders?searchCriteria[pageSize]=".$amount;
        if($offset != 0){
            $url .= "&searchCriteria[currentPage]=".$offset;
        }

        if($orderStatus != 'all' && $orderStatus != '') {
            $url .= "&searchCriteria[filter_groups][0][filters][0][field]=status";
            $url .= "&searchCriteria[filter_groups][0][filters][0][value]=".$orderStatus;
        }

		magento_log($projectId, 'sell_ord_req', $url);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);

        magento_log($projectId, 'sell_ord_resp', $result);
        $result = json_decode($result, true);
        curl_close($ch);

        $resultData = [];
        $openedOrders = 0;

        if(!isset($result['items'])) {
            return [];
        }

        foreach ($result['items'] as $order) {

            $placed = str_replace(' ', 'T', $order['created_at']).'.0000Z';
            $completed = str_replace(' ', 'T', $order['updated_at']).'.0000Z';

            $orderData = [
                'id' => $order['entity_id'],
                'created' => $placed,
                'completed' => $completed,
                'amount' => $order['base_grand_total'],
                //'orderedBy' => $order['OrderedByContactPerson'],
                'date' => $order['created_at'],
            ];

            foreach ($order['items'] as $lineData) {
                $orderData['lines'][] = [
                    'amount' => $order['row_total_incl_tax'],
                    'id' => $lineData['item_id'],
                    'name' => $lineData['name'],
                    'quantity' => $lineData['qty_ordered'],
                    'unitPrice' => $lineData['base_price_incl_tax'],
                    'code' => $lineData['sku'],
                    'product' => [
                        'code' => $lineData['sku'],
                        'barcode' => $lineData['sku'],
                        'stock' => $this->getProductStock($token, $shopUrl, $lineData['sku']),
                        'name' => $lineData['name'],
                        'id' => $lineData['item_id'],
                        'priceStandart' => $lineData['base_price_incl_tax'],
                        'status' => 'ENABLED'
                    ]
                ];
            }

            $resultData[] = $orderData;
        }

        if($openedOrders > 0) {
            apicenter_logs($projectId, 'exact_sell_orders', 'Store has '. $openedOrders . 'not closed orders. It will not be imported', true);
        }

        return $resultData;
    }

    public function updateStockDelivered($items, $projectId) {
        $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);

        foreach ($items as $item) {
        	$product = $this->getProduct($token, $shopUrl, $item['sku']);
            $qty = $product['extension_attributes']['stock_item']['qty'] + $item['qty'];
            $item['qty'] = $qty;

            $this->updateProductStock($projectId, $shopUrl, $token, $item);
        }
    }

    public function updateProductStock($projectId, $shopUrl, $token, $data) {

        $url = $shopUrl."/rest/V1/products/";

        $saveData = array(
            'product' => array(
                'sku' => $data['sku'],
                'extensionAttributes' => array(
                    'stockItem' => array(
                        'stockId' => 1,
                        'qty' => $data['qty'],
                        'isInStock' => true
                    )
                )
            )
        );

        magento_log($projectId, 'update_stock_req', json_encode($saveData));

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        magento_log($projectId, 'update_stock_resp', $result);
        $result = json_decode($result, true);
        curl_close($ch);

        if(isset($result['id']) && $result['id'] > 0){
            apicenter_logs($projectId, 'importarticles', 'Updated product stock for product '.$data['sku'], false);
        } else {
            apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$data['sku'].'. Result: '.print_r($result, true), true);
        }

        return $result;
    }

    public function getProductStatuses($projectId, $amount, $lastDate)  {

        $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);

        if($lastDate == '') {
            $lastDate = date('Y-m-d H:i:s');
        }

        $products = $this->getProducts($projectId, $token, $shopUrl, $amount, 0, $lastDate);
        $statuses = [];

        if(count($products['items']) == 0) {
            return [];
        }

        foreach ($products['items'] as $product) {
            $status = 'ENABLED';
            if($product['status'] != 1) {
                $status = 'DISABLED';
            }

            $item = [
                'name' => $product['name'],
                'sku' => $product['sku'],
                'status' => $status,
                'date' => $product['updated_at']
            ];

            $statuses[$product['name']] = $item;
        }

        return $statuses;
    }


    public function updateProductStatuses($projectId, $magentoStatuses)  {

	    if (count($magentoStatuses) == 0) {
	        return [];
        }

        $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);
        $updated = [];

        $url = $shopUrl."/rest/V1/products";

        foreach ($magentoStatuses as $magentoStatus) {
            $status = 1;
            if($magentoStatus['status'] != 'ENABLED') {
                $status = 2;
            }

            $saveData = [
                'product' => [
                    'sku' => $magentoStatus['sku'],
                    'status' => $status
                ]
            ];

            magento_log($projectId, 'update_status_req', json_encode($saveData));
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

            $result = curl_exec($ch);
            magento_log($projectId, 'update_status_resp', $result);
            $result = json_decode($result, true);
            curl_close($ch);

            if($result) {
                $updated[] = $magentoStatus['name'];
            }
        }

        return $updated;
    }

    public function getProductStatusesInArray($projectId, $data) {

        $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);
        $statuses = [];

        foreach ($data as $name => $item) {
            $product = $this->getProduct($token, $shopUrl, $item['sku']);

            if(isset($product['message'])) {
                continue;
            }

            $status = $product['attributes']['status'] == '1' ? 'ENABLED' : 'DISABLED';

            $itemData = [
                'status' => $status,
                'date' => $product['attributes']['updated_at'],
                'name' => $product['attributes']['name'],
                'sku' => $product['attributes']['sku']
            ];

            $statuses[$product['attributes']['name']] = $itemData;
        }

        return $statuses;
    }

    public function getProduct($token, $shopUrl, $sku) {

	    $url = $shopUrl."/rest/V1/products/".$sku;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);
        magento_log(0, 'prod_resp', json_encode($result));

        return $result;
    }

	public function getAttributesForMappingTable($projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		
		$this->load->model('Projects_model');
		$currentAttributes = $this->Projects_model->getValue('product_attributes_for_mapping', $projectId);
		if($this->input->get('refresh_attribute_mapping') == 'true'){
			$currentAttributes = '';
		}
		if($currentAttributes != '' && $currentAttributes != '[]'){
			$currentAttributes = json_decode($currentAttributes, true);
			return $currentAttributes;
		} else {
			$token = $this->getToken($projectId);
			$filterData = array(
				'search_criteria' => 0
			);
			$j = json_decode(json_encode($filterData));
			$get_params = http_build_query($j);
			$ch = curl_init($storeUrl."/rest/V1/products/attributes?".$get_params);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			
			if($projectId == 8 || $projectId == 84){
				curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
				curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
			}
			
			$result = curl_exec($ch);
			$items = json_decode($result, true);
			if(isset($items['items'])){
				$finalItems = array();
				foreach($items['items'] as $item){
					if($item['attribute_code'] == '' || $item['default_frontend_label'] == '' || $item['default_frontend_label'] == ' '){
						continue;
					}
					$finalItems[] = array(
						'code' => $item['attribute_code'],
						'label' => $item['default_frontend_label'],
						'type' => $item['frontend_input'],
					);
				}
	// 			echo '<pre>';print_r($finalItems);exit;
				$this->Projects_model->saveValue('product_attributes_for_mapping', json_encode($finalItems), $projectId);
				return $finalItems;
			}
		}
		return array();
	}

	public function applyMappedAttributes($projectId, $articleData, $finalArticleData){
		$this->load->model('Projects_model');
		$cmsAttributes = $this->Projects_model->getValue('product_attributes_for_mapping', $projectId);
		$currentAttributes = $this->Projects_model->getValue('product_attributes_mapping', $projectId);
		if($currentAttributes == '' || $currentAttributes == '[]'){
			return $finalArticleData;
		}
		$cmsAttributes = json_decode($cmsAttributes, true);
		$currentAttributes = json_decode($currentAttributes, true);
		if(is_array($currentAttributes)){
			foreach($currentAttributes['cms_attribute'] as $index => $cmsCode){
				$type = 'text';
				foreach($cmsAttributes as $cmsAttribute){
					if($cmsAttribute['code'] == $cmsCode){
						$type = $cmsAttribute['type'];
					}
				}
				$erpCode = $currentAttributes['erp_attribute'][$index];
				if(isset($articleData[$erpCode]) && $articleData[$erpCode] != ''){
					$finalArticleData['custom_attributes'][] = array('attribute_code' => $cmsCode, 'value' => $articleData[$erpCode], 'type' => $type);
				}
			}
		}
		return $finalArticleData;
	}

	public function getStockArticles($projectId, $offset, $amount) {

	    $offset = $offset == '' ? 0 : $offset;
	    $amount = $amount == '' ? 50 : $amount;

        $token = $this->getToken($projectId);
        $shopUrl = $this->Projects_model->getWebshopUrl($projectId);

        $url = $shopUrl."/rest/V1/products?searchCriteria[pageSize]=".$amount;
        if($offset != 0){
            $url .= "&searchCriteria[currentPage]=".$offset;
        }

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
        $result = json_decode($result, true);
        curl_close($ch);

        $resultData = [];

        if(!isset($result['items'])) {
            return [];
        }

        foreach ($result['items'] as $product) {

            $data = $this->getProductStock($token, $shopUrl, $product['sku']);
            $qty = $data['qty'] != NULL ? $data['qty'] : $data['min_qty'];

            $productData = [
                'sku' => $product['sku'],
                'stock' => $qty
            ];

            $resultData[] = $productData;
        }

        return $resultData;
	}
	
	public function updatePrice($projectId, $priceList) {
		
		foreach ($priceList as $value) {
			$model = (array)$value->Itemcode;
			$productData = [
				'model' => $model[0]
			];
			$productExists = $this->checkProductExists($productData, $projectId);
			
			if($productExists != false && isset($productExists['items']) && !empty($productExists['items'])){
				// Update product price
				$this->updateProductPrice($projectId, $productExists, $value);
			}
		}
		
	}

	public function updateProductPrice($projectId, $productExists, $value) {
		$model = (array)$value->Itemcode;
		$price = (array)$value->Verkoopprijs;

		$project  = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token    = $this->getToken($projectId);

		$saveData = array(
			"saveOptions" => true,
			'product' => array(
				'sku' => $model[0],
				'price' => $price[0],
				
			)
		);

		$ch = curl_init($storeUrl."/rest/all/V1/products");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);

		if(isset($result['id']) && $result['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Updated product price ' . $model[0], false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not update product '.$model[0].'. Result: '.print_r($result, true), true);
		}
	}

	public function updateMultipleProdcut($saveData, $productData, $projectId)
	{
		//if( $projectId == 131) { log_message('debug', 'ML_Product131' . var_export($productData, true)); }

		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$storeUrl = $project['store_url'];
		$token = $this->getToken($projectId);
		$mlAttributes = $productData['multilanguage_attributes'];

		foreach ($mlAttributes as $storeCode=>$attributes) {
			
			$saveData = array(
				"saveOptions" => true,
				'product' => array(
					'sku' => $productData['model'],
					'attribute_set_id' => $this->getDefaultAttributeSet($productData, $projectId),
				)
			);
			$store_code = $attributes['store_id'];

			foreach($attributes as $attributeCode => $attribute) {
				if ($attributeCode == 'store_id' || $attributeCode == 'is_configurable') continue;
				if($attribute['type'] == 'dropdown'){
					$attributeOptionId = $this->createAttributeValue($attributeCode, $attribute['value'], $projectId);
					if(!$attributeOptionId){
						continue;
					}
					$attribute['value'] = $attributeOptionId;
				} elseif($attribute['type'] == 'multiselect'){
					$valueArray = array();
					if(is_array($attribute['value']) && !empty($attribute['value'])){
						foreach($attribute['value'] as $value){
							$attributeOptionId = $this->createAttributeValue($attributeCode, $value, $projectId);
							if(!$attributeOptionId){
								continue;
							}
							$valueArray[] = $attributeOptionId;
						}
					}
					$attribute['value'] = implode(',', $valueArray);
				}
				$saveData['product']['custom_attributes'][] = array('attribute_code' => $attributeCode, 'value' => trim($attribute['value']));
				
			}

			$saveData = json_encode($saveData);
			
			//if( $projectId == 131) { log_message('debug', 'MAg_Request ML' . var_export($saveData, true)); }
			
			$ch = curl_init($storeUrl."/rest/".$store_code."/V1/products/".$productData['model']);
			// curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
			
			$result = curl_exec($ch);
			$result = json_decode($result, true);

			if(isset($result['id']) && $result['id'] > 0){
				if ($projectId == 35) {
					$Status = ($productData['tmp']['status'] == 1) ? "Ingeschakeld" : "Uitgeschakeld";
					apicenter_logs($projectId, 'importarticles', 'Updated product  store '.$storeCode.' '.$productData['model'] . ' Status => ' . $Status, false);
				}
				else {
					apicenter_logs($projectId, 'importarticles', 'Updated product, store '.$storeCode.' '.$productData['model'], false);
				}
				
			} else {
				apicenter_logs($projectId, 'importarticles', 'Could not update productstore '.$storeCode.' '.'. Result: ', true);
			}
		}
	}

	public function getProcessingOrders($projectId)
	{
		$token 		   = $this->getToken($projectId);
        $shopUrl 	   = $this->Projects_model->getWebshopUrl($projectId);
        $orderStatus   = "processing";
		$isShipped 	   = false;
		$excludeOrders = $this->getFromFile($projectId) === false ? '' : implode(",", $this->getFromFile($projectId));
		$amount 	   = 100000;

		$url = $shopUrl."/rest/V1/orders?searchCriteria[pageSize]=".$amount;

		if ($excludeOrders != '') {
			$url .= '&searchCriteria[filter_groups][0][filters][0][field]=increment_id';
			$url .= '&searchCriteria[filter_groups][0][filters][0][value]=' . $excludeOrders;
			$url .= '&searchCriteria[filter_groups][0][filters][0][condition_type]=nin';
		}

		$url .= "&searchCriteria[filter_groups][1][filters][0][field]=status";
		$url .= "&searchCriteria[filter_groups][1][filters][0][value]=" . $orderStatus;
		$url .= '&searchCriteria[filter_groups][1][filters][0][condition_type]=eq';

		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
		$result = json_decode($result, true);

		curl_close($ch);

		if(!isset($result['items'])) {
			return [];
		}

		$this->load->model('Afas_model');

		foreach ($result['items'] as $order) {
			$result = $this->Afas_model->checkPackingNotes($projectId, $order['increment_id']);

			if ($result['numberOfResults'] == 0) continue;

			$pakbonnenData = $result['pakbonnenData'];

			if ($pakbonnenData['Gereedgemeld'] === "false") continue;

			$orderData = [
				'order_id' => $order['entity_id'],
				'store_id' => $order['store_id']
			];

			foreach ($order['items'] as $lineData) {
				$isShipped = false;
				
				if ($lineData['qty_shipped'] == $lineData['qty_ordered']) {
					$isShipped = true;
					continue;
				}

				$orderData['items'][] = [
					'order_item_id' => $lineData['item_id'],
					'qty' => $lineData['qty_ordered'],
				];
			}

			if ($isShipped === true) continue;

			$shipID = $this->addTrackingNumber($orderData, $projectId);
			
			if ($shipID !== false) {
				$this->putToFile($projectId, $order['increment_id']);
			}
			
		}
	}

	public function addTrackingNumber($data, $projectId) {
		$storeUrl 	= $this->Projects_model->getWebshopUrl($projectId);
		$token 		= $this->getToken($projectId);
		$order_id 	= $data['order_id'];

		$saveData = array(
			"items" => $data['items']
		);

		$ch = curl_init($storeUrl."/rest/V1/order/".$order_id."/ship");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);

		if (isset($result['message'])) {
			log_message('error', 'Tried to create shipment for order with ID '.$order_id.'. Result: '.var_export($result, true));
			apicenter_logs($projectId, 'tracktrace', 'Could not create shipment for order with ID '.$order_id.'. Result: Shipment ID = ', true);

			return false;
		} else {
			log_message('error', 'Tried to create shipment for order with ID '.$order_id.'. Result: '.var_export($result, true));
			apicenter_logs($projectId, 'tracktrace', 'Created shipment for order with ID '.$order_id.'. Result: Shipment ID = ', false);
		}

		return $result;
	}

	
	private function putToFile($projectId, $id) {
        $folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $file = $folder.'saved_orders.txt';

        if(file_exists($file)){
            $saved_orders = json_decode(file_get_contents($file), true);
        } else {
            $saved_orders = [];
        }

		array_push($saved_orders, $id);

        file_put_contents($file, json_encode($saved_orders));
    }

    private function getFromFile($projectId) {
        $folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';

        if(!file_exists($folder)){
            mkdir($folder, 0777, true);
        }

        $file = $folder.'saved_orders.txt';

        if(file_exists($file)){
            return json_decode(file_get_contents($file), true);
        }

        return false;
	}

	public function createCreditMemo($projectId, $data)
	{
		if ($data['numberOfResults'] == 1 ) {
			$data = $data['сreditData'];
			$orderInfo = $this->getOrderByIncrementId($projectId, $data['increment_id']);

			$saveData = [
				"items" => $orderInfo['items'],
				"notify" => true,
			];

			return $this->sendCreditMemo($projectId, $saveData, $orderInfo['order_id']);
		} 
		foreach ($data['сreditData'] as $val) {
			$orderInfo = $this->getOrderByIncrementId($projectId, $val);
			$saveData = [
				"items" => $orderInfo['items'],
				"notify" => true,
			];

			$this->sendCreditMemo($projectId, $saveData, $orderInfo['order_id']);
		}

	}

	private function sendCreditMemo($projectId, $saveData, $order_id)
	{
		$storeUrl 	= $this->Projects_model->getWebshopUrl($projectId);
		$token 		= $this->getToken($projectId);

		$ch = curl_init($storeUrl."/rest/V1/order/".$order_id."/refund");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($saveData));
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));
		
		$result = curl_exec($ch);
		$result = json_decode($result, true);

		if (isset($result['message'])) {
			log_message('error', 'Tried to create CreditMemo for order with ID '.$order_id.'. Result: '.var_export($result, true));
			apicenter_logs($projectId, 'tracktrace', 'Could not create CreditMemo for order with ID '.$order_id.'. Result: Shipment ID = '.var_export($result, true), true);

		} else {
			log_message('error', 'Tried to create CreditMemo for order with ID '.$order_id.'. Result: '.var_export($result, true));
			apicenter_logs($projectId, 'tracktrace', 'Created CreditMemo for order with ID '.$order_id.'. Result: Shipment ID = '.var_export($result, true), false);
		}
	}

	private function getOrderByIncrementId($projectId, $data)
	{
		$token 		   = $this->getToken($projectId);
        $shopUrl 	   = $this->Projects_model->getWebshopUrl($projectId);
		$amount 	   = 100000;
		$incrementId   = $data['increment_id'];
		$url = $shopUrl."/rest/V1/orders?searchCriteria[pageSize]=".$amount;

		$url .= "&searchCriteria[filter_groups][1][filters][0][field]=increment_id";
		$url .= "&searchCriteria[filter_groups][1][filters][0][value]=" . $incrementId;
		$url .= '&searchCriteria[filter_groups][1][filters][0][condition_type]=eq';

		$ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Authorization: Bearer " . $token));

        $result = curl_exec($ch);
		$result = json_decode($result, true);

		curl_close($ch);
		if(!isset($result['items'])) {
			return [];
		}

		$orderData  = $result['items'][0];
		$resultData = [];

		foreach ($orderData['items'] as $item) {
			if ($data['item_id'] == $item['sku']) {
				$resultData['items'][] = [
					'order_item_id' => $item['item_id'],
					'qty' => abs($data['qty'])
				];
			}
		}
		$resultData['order_id'] = $orderData['entity_id'];

		return $resultData;
	}
}