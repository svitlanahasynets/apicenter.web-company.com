<?php
class Lightspeed_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
    public function getClient($projectId, $language = 'nl'){
		$apiKey = $this->Projects_model->getValue('lightspeed_key', $projectId);
		$apiSecret = $this->Projects_model->getValue('lightspeed_secret', $projectId);
		$apiUrl = 'https://'.$apiKey.':'.$apiSecret.'@'.$this->Projects_model->getValue('lightspeed_apiurl', $projectId);
		
		if(!isset($this->WebshopappApiClient)){
			$this->load->library('WebshopappApiClient', array('apiServer' => 'live', 'apiKey' => $apiKey, 'apiSecret' => $apiSecret, 'apiLanguage' => 'nl'), 'WebshopappApiClient');
		}
        $this->WebshopappApiClient->setApiServer('live');
        $this->WebshopappApiClient->setApiKey($apiKey);
        $this->WebshopappApiClient->setApiSecret($apiSecret);
        $this->WebshopappApiClient->setApiLanguage($language);
		return $this->WebshopappApiClient;
    }

    /* PRODUCTS */
	public function updateArticles($projectId, $articles){
		foreach($articles as $article){
			$productExists = $this->checkProductExists($article, $projectId);
// 			echo '<pre>';print_r($productExists);exit;
			if($productExists != false && !empty($productExists)){
				// Update product
				$this->updateProduct($productExists[0]['product']['resource']['id'], $article, $projectId);
			} else {
				// Create product
				$product = $this->createProduct($article, $projectId);
				$this->updateProduct($product['product']['resource']['id'], $article, $projectId);
			}
		}
	}
	
	public function checkProductExists($productData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		$products = $client->variants->get(null, array('sku' => $productData['model']));
		return $products;
	}
	
	public function createProduct($productData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		
		if($productData['description'] == ''){
			return false;
		}
		 
		if ($projectId == 139){
			$saveData = array(
				'sku' => $productData['model'],
				'title' => $productData['brand'] . ' ' . $productData['name'],
    			'fulltitle' => $productData['brand'] . ' ' . $productData['fulltitle'],
				'description' => $productData['description'],
				'content' => $productData['description'],
				'priceExcl' => $productData['price'] ? $productData['price'] : 0,
				'unitUnit' => 'Piece',
				'isVisible' => true,
				'visibility' => 'hidden',
				'stockLevel' => isset($productData['quantity']) ? $productData['quantity'] : 0,
			);
		} else {
    		$saveData = array(
    			'sku' => $productData['model'],
    			'title' => $productData['brand'] . ' ' . $productData['name'],
    			'fulltitle' => $productData['brand'] . ' ' . $productData['fulltitle'],
    			'description' => $productData['description'],
    			'content' => $productData['description'],
    			'priceExcl' => $productData['price'] ? $productData['price'] : 0,
    			'unitUnit' => 'Piece',
    			'isVisible' => false,
    			'visibility' => 'hidden',
    			'stockLevel' => isset($productData['quantity']) ? $productData['quantity'] : 0,
    		);		
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
					foreach($attribute['value'] as $value){
						$attributeOptionId = $this->createAttributeValue($attributeCode, $value, $projectId);
						if(!$attributeOptionId){
							continue;
						}
						$valueArray[] = $attributeOptionId;
					}
					$attribute['value'] = implode(',', $valueArray);
				}
				$saveData[$attributeCode] = $attribute['value'];
			}
		}
		
		//log_message('debug', 'ProductData 139AFAS - XML - LS - Create ' . var_export($saveData, true));

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'create');
			}
		}

		$productResult = $client->products->create($saveData);

		// Set category
		$categories = array();
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
			foreach($categoryIds as $id){
				$categories[] = array(
					'category' => $id,
					'product' => $productResult['id']
				);
			}
		}
		if(!empty($categories)){
			try{
				foreach($categories as $category){
					$categoryResult = $client->categoriesProducts->create($category);
				}
			} catch(Exception $e){
// 				echo '<pre>';print_r($e);exit;
			}
		}
		
		// Now create variant
		$saveData['product'] = $productResult['id'];
		$saveData['isDefault'] = true;
		$result = $client->variants->create($saveData);

		$variants = $client->variants->get(null, array('product' => $productResult['id']));
		if(isset($variants[1]) && $variants[1]['title'] == 'Default'){
			$client->variants->delete($variants[1]['id']);
		}

// 		echo '<pre>';print_r($result);exit;
		if(isset($productResult['id']) && $productResult['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Created product '.$productData['model'], false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not create product '.$productData['model'].'. Result: '.print_r($productResult, true), true);
		}
		return $result;
	}
	
	public function updateProduct($productId, $productData, $projectId){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		
		if($productData['description'] == ''){
			return false;
		}

		$saveData = array(
			'sku' => $productData['model'],
			'title' => $productData['brand'] . ' ' . $productData['name'],
			'fulltitle' => $productData['brand'] . ' ' . $productData['fulltitle'],
			'description' => $productData['description'],
			'content' => $productData['description'],
			'priceExcl' => $productData['price'] ? $productData['price'] : 0,
			'unitUnit' => 'Piece',
			'isVisible' => false,
			'visibility' => 'hidden',
			'stockLevel' => isset($productData['quantity']) ? $productData['quantity'] : 0,
		);
		
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
					foreach($attribute['value'] as $value){
						$attributeOptionId = $this->createAttributeValue($attributeCode, $value, $projectId);
						if(!$attributeOptionId){
							continue;
						}
						$valueArray[] = $attributeOptionId;
					}
					$attribute['value'] = implode(',', $valueArray);
				}
				$saveData[$attributeCode] = $attribute['value'];
			}
		}
		
		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'update');
			}
		}

		$productResult = $client->products->update($productId, $saveData);

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'afterProductUpdate')){
				$this->$projectModel->afterProductUpdate($saveData, $productData, $projectId, $productResult);
			}
		}
		// Set category
		$categories = array();
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
			foreach($categoryIds as $index => $id){
				$categories[] = array(
					'sortOrder' => $index,
					'category' => $id,
					'product' => $productResult['id']
				);
			}
		}
		if(!empty($categories)){
			try{
				foreach($categories as $category){
					$categoryResult = $client->categoriesProducts->create($category);
				}
			} catch(Exception $e){
// 				echo '<pre>';print_r($e);exit;
			}
		}
		
		// Get variants data
		$variant = $client->variants->get(null, array(
			'product' => $productResult['id'],
			'sku' => $productData['model']
		));
		$variantId = isset($variant[0]) ? $variant[0]['id'] : null;
		
		// Now create variant
		$saveData['product'] = $productResult['id'];
		$saveData['isDefault'] = true;
		$result = $client->variants->update($variantId, $saveData);
		
		$images = array();
		if(isset($productData['image']) && !empty($productData['image'])){
			$image = $productData['image'];
			$base64Data = base64_encode(file_get_contents($image['path']));
			$imageName = $image['image_name'];
			$imageName = str_replace('(', '', $imageName);
			$imageName = str_replace(')', '', $imageName);
			if($base64Data != ''){
				$images[] = array(
			        "filename" => $imageName,
					"attachment" => $base64Data
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
			        "filename" => $imageName,
					"attachment" => $base64Data
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
			        "filename" => $imageName,
					"attachment" => $base64Data
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
			        "filename" => $imageName,
					"attachment" => $base64Data
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
			        "filename" => $imageName,
					"attachment" => $base64Data
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
			        "filename" => $imageName,
					"attachment" => $base64Data
				);
			}
		}
				
		// Create images
		if(!empty($images)){
			foreach($images as $image){
				$resultImage = $client->productsImages->create($productId, $image);
			}
// 			echo '<pre>';print_r($resultImages);exit;
			// array(1) { ["error"]=> array(4) { ["code"]=> int(400) ["method"]=> string(6) "CREATE" ["request"]=> string(33) "/nl/products/84137615/images.json" ["message"]=> string(19) "Invalid data input." } }
		}
		
		if(isset($productResult['id']) && $productResult['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Updated product '.$productData['model'], false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not update product '.$productData['model'].'. Result: '.print_r($productResult, true), true);
		}
		return $productResult;
	}
	
	public function removeArticles($projectId, $articles){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);

		foreach($articles as $itemCode){
			$product = $this->checkProductExists(array('model' => $itemCode), $projectId);
			if($product != false && !empty($product)){
				$result = $client->products->delete($product[0]['product']['resource']['id']);
				if(isset($result['id']) && $result['id'] > 0){
					apicenter_logs($projectId, 'importarticles', 'Removed product '.$itemCode, false);
				} else {
					apicenter_logs($projectId, 'importarticles', 'Could not remove product '.$itemCode.'. Result: '.print_r($result, true), true);
				}
			}
		}
	}
	
	public function findCategory($projectId, $categoryName, $parentId = ''){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);

		$categories = $client->categories->get(null, array(
			'title' => $categoryName
		));
		if(isset($categories[0])){
			foreach($categories as $category){
				if($category['title'] == $categoryName){
					$result = array('items' => array($category));
					if($parentId != ''){
						if(isset($category['parent']['resource']['id']) && $category['parent']['resource']['id'] != ''){
							if($parentId != $category['parent']['resource']['id']){
								continue;
							}
						} else {
							continue;
						}
					}
					return $result;
				}
			}
		}
		return false;
	}
	
	function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		
		$saveData = array(
			'type' => 'category',
			'title' => $categoryName,
			'fulltitle' => $categoryName,
			'isVisible' => true,
		);
		if($parentId != ''){
			$saveData['parent'] = $parentId;
		}

		$result = $client->categories->create($saveData);

		if(isset($result['id']) && $result['id'] > 0){
			apicenter_logs($projectId, 'importarticles', 'Created category '.$categoryName, false);
		} else {
			apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.print_r($result, true), true);
		}
		return $result;
	}
	
	public function updateStockArticles($projectId, $articles){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		
		foreach($articles as $productData){
			
			$productExists = $this->checkProductExists($productData, $projectId);
			if($productExists != false && isset($productExists[0]) && !empty($productExists[0])){
		
				$saveData = array(
					'stockLevel' => isset($productData['quantity']) ? $productData['quantity'] : 0,
				);
		
				$result = $client->products->update($productExists[0]['id'], $saveData);
		
				if(isset($result[0]['id']) && $result[0]['id'] > 0){
					apicenter_logs($projectId, 'importarticles', 'Updated product stock for product '.$productData['model'], false);
				} else {
					apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$productData['model'].'. Result: '.print_r($result, true), true);
				}
			}
		}
	}
	
    public function formatUrlKey($str)
    {
        $urlKey = preg_replace('#[^0-9a-z]+#i', '-', $this->format($str));
        $urlKey = strtolower($urlKey);
        $urlKey = trim($urlKey, '-');

        return $urlKey;
    }

    public function format($string)
    {
		$_convertTable = json_decode('{"&":"and","@":"at","\u00a9":"c","\u00ae":"r","\u00c0":"a","\u00c1":"a","\u00c2":"a","\u00c4":"a","\u00c5":"a","\u00c6":"ae","\u00c7":"c","\u00c8":"e","\u00c9":"e","\u00cb":"e","\u00cc":"i","\u00cd":"i","\u00ce":"i","\u00cf":"i","\u00d2":"o","\u00d3":"o","\u00d4":"o","\u00d5":"o","\u00d6":"o","\u00d8":"o","\u00d9":"u","\u00da":"u","\u00db":"u","\u00dc":"u","\u00dd":"y","\u00df":"ss","\u00e0":"a","\u00e1":"a","\u00e2":"a","\u00e4":"a","\u00e5":"a","\u00e6":"ae","\u00e7":"c","\u00e8":"e","\u00e9":"e","\u00ea":"e","\u00eb":"e","\u00ec":"i","\u00ed":"i","\u00ee":"i","\u00ef":"i","\u00f2":"o","\u00f3":"o","\u00f4":"o","\u00f5":"o","\u00f6":"o","\u00f8":"o","\u00f9":"u","\u00fa":"u","\u00fb":"u","\u00fc":"u","\u00fd":"y","\u00fe":"p","\u00ff":"y","\u0100":"a","\u0101":"a","\u0102":"a","\u0103":"a","\u0104":"a","\u0105":"a","\u0106":"c","\u0107":"c","\u0108":"c","\u0109":"c","\u010a":"c","\u010b":"c","\u010c":"c","\u010d":"c","\u010e":"d","\u010f":"d","\u0110":"d","\u0111":"d","\u0112":"e","\u0113":"e","\u0114":"e","\u0115":"e","\u0116":"e","\u0117":"e","\u0118":"e","\u0119":"e","\u011a":"e","\u011b":"e","\u011c":"g","\u011d":"g","\u011e":"g","\u011f":"g","\u0120":"g","\u0121":"g","\u0122":"g","\u0123":"g","\u0124":"h","\u0125":"h","\u0126":"h","\u0127":"h","\u0128":"i","\u0129":"i","\u012a":"i","\u012b":"i","\u012c":"i","\u012d":"i","\u012e":"i","\u012f":"i","\u0130":"i","\u0131":"i","\u0132":"ij","\u0133":"ij","\u0134":"j","\u0135":"j","\u0136":"k","\u0137":"k","\u0138":"k","\u0139":"l","\u013a":"l","\u013b":"l","\u013c":"l","\u013d":"l","\u013e":"l","\u013f":"l","\u0140":"l","\u0141":"l","\u0142":"l","\u0143":"n","\u0144":"n","\u0145":"n","\u0146":"n","\u0147":"n","\u0148":"n","\u0149":"n","\u014a":"n","\u014b":"n","\u014c":"o","\u014d":"o","\u014e":"o","\u014f":"o","\u0150":"o","\u0151":"o","\u0152":"oe","\u0153":"oe","\u0154":"r","\u0155":"r","\u0156":"r","\u0157":"r","\u0158":"r","\u0159":"r","\u015a":"s","\u015b":"s","\u015c":"s","\u015d":"s","\u015e":"s","\u015f":"s","\u0160":"s","\u0161":"s","\u0162":"t","\u0163":"t","\u0164":"t","\u0165":"t","\u0166":"t","\u0167":"t","\u0168":"u","\u0169":"u","\u016a":"u","\u016b":"u","\u016c":"u","\u016d":"u","\u016e":"u","\u016f":"u","\u0170":"u","\u0171":"u","\u0172":"u","\u0173":"u","\u0174":"w","\u0175":"w","\u0176":"y","\u0177":"y","\u0178":"y","\u0179":"z","\u017a":"z","\u017b":"z","\u017c":"z","\u017d":"z","\u017e":"z","\u017f":"z","\u018f":"e","\u0192":"f","\u01a0":"o","\u01a1":"o","\u01af":"u","\u01b0":"u","\u01cd":"a","\u01ce":"a","\u01cf":"i","\u01d0":"i","\u01d1":"o","\u01d2":"o","\u01d3":"u","\u01d4":"u","\u01d5":"u","\u01d6":"u","\u01d7":"u","\u01d8":"u","\u01d9":"u","\u01da":"u","\u01db":"u","\u01dc":"u","\u01fa":"a","\u01fb":"a","\u01fc":"ae","\u01fd":"ae","\u01fe":"o","\u01ff":"o","\u0259":"e","\u0401":"jo","\u0404":"e","\u0406":"i","\u0407":"i","\u0410":"a","\u0411":"b","\u0412":"v","\u0413":"g","\u0414":"d","\u0415":"e","\u0416":"zh","\u0417":"z","\u0418":"i","\u0419":"j","\u041a":"k","\u041b":"l","\u041c":"m","\u041d":"n","\u041e":"o","\u041f":"p","\u0420":"r","\u0421":"s","\u0422":"t","\u0423":"u","\u0424":"f","\u0425":"h","\u0426":"c","\u0427":"ch","\u0428":"sh","\u0429":"sch","\u042a":"-","\u042b":"y","\u042c":"-","\u042d":"je","\u042e":"ju","\u042f":"ja","\u0430":"a","\u0431":"b","\u0432":"v","\u0433":"g","\u0434":"d","\u0435":"e","\u0436":"zh","\u0437":"z","\u0438":"i","\u0439":"j","\u043a":"k","\u043b":"l","\u043c":"m","\u043d":"n","\u043e":"o","\u043f":"p","\u0440":"r","\u0441":"s","\u0442":"t","\u0443":"u","\u0444":"f","\u0445":"h","\u0446":"c","\u0447":"ch","\u0448":"sh","\u0449":"sch","\u044a":"-","\u044b":"y","\u044c":"-","\u044d":"je","\u044e":"ju","\u044f":"ja","\u0451":"jo","\u0454":"e","\u0456":"i","\u0457":"i","\u0490":"g","\u0491":"g","\u05d0":"a","\u05d1":"b","\u05d2":"g","\u05d3":"d","\u05d4":"h","\u05d5":"v","\u05d6":"z","\u05d7":"h","\u05d8":"t","\u05d9":"i","\u05da":"k","\u05db":"k","\u05dc":"l","\u05dd":"m","\u05de":"m","\u05df":"n","\u05e0":"n","\u05e1":"s","\u05e2":"e","\u05e3":"p","\u05e4":"p","\u05e5":"C","\u05e6":"c","\u05e7":"q","\u05e8":"r","\u05e9":"w","\u05ea":"t","\u2122":"tm"}', true);
        return strtr($string, $_convertTable);
    }
	
	
	
	
	
	
    /* CUSTOMERS */
	public function createCustomer($projectId, $customerData){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$erpSystem = $project['erp_system'];

		$customerExists = $this->checkCustomerExists($customerData, $projectId);
		if($customerExists != false && !empty($customerExists)){
			if($erpSystem == 'exactonline'){
				$saveData = array(
					'email' => $customerData['email'],
					'firstname' => $customerData['first_name'],
					'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'isConfirmed' => true,
					'addressBillingCountry' => $customerData['address_book_country_1'],
					'addressBillingStreet' => $customerData['address_book_address1_1'],
					'addressBillingZipcode' => $customerData['address_book_postcode_1'],
					'addressBillingCity' => $customerData['address_book_city_1'],
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
				}
			} else {
				$saveData = array(
					'email' => $customerData['email'],
					'firstname' => $customerData['first_name'],
					'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'isConfirmed' => true,
					'addressBillingCountry' => $customerData['country'],
					'addressBillingStreet' => $customerData['address'],
					'addressBillingZipcode' => $customerData['postcode'],
					'addressBillingCity' => $customerData['city'],
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
				}
			}
			$saveData['doNotifyRegistered'] = false;
			$saveData['doNotifyConfirmed'] = false;
			$saveData['doNotifyPassword'] = false;

			try{
				$result = $client->customers->update($customerExists[0]['id'], $saveData);
			} catch(Exception $e){
				apicenter_logs($projectId, 'importcustomers', 'Could not update customer '.$customerData['email'].'. Result: '.$e->getMessage(), true);
				return false;
			}
			if(isset($result['id']) && $result['id'] > 0){
				apicenter_logs($projectId, 'importcustomers', 'Updated customer '.$customerData['email'], false);
			} else {
				apicenter_logs($projectId, 'importcustomers', 'Could not update customer '.$customerData['email'].'. Result: '.print_r($result, true), true);
			}
			return $result;
		} else {
			// Create customer
			if($erpSystem == 'exactonline'){
				$saveData = array(
					'email' => $customerData['email'],
					'firstname' => $customerData['first_name'],
					'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'isConfirmed' => true,
					'addressBillingCountry' => $customerData['address_book_country_1'],
					'addressBillingStreet' => $customerData['address_book_address1_1'],
					'addressBillingZipcode' => $customerData['address_book_postcode_1'],
					'addressBillingCity' => $customerData['address_book_city_1'],
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
				}
			} else {
				$saveData = array(
					'email' => $customerData['email'],
					'firstname' => $customerData['first_name'],
					'lastname' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'isConfirmed' => true,
					'addressBillingCountry' => $customerData['country'],
					'addressBillingStreet' => $customerData['address'],
					'addressBillingZipcode' => $customerData['postcode'],
					'addressBillingCity' => $customerData['city'],
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
				}
			}
			$saveData['doNotifyRegistered'] = false;
			$saveData['doNotifyConfirmed'] = false;
			$saveData['doNotifyPassword'] = false;

			try{
				$result = $client->customers->create($saveData);
			} catch(Exception $e){
				apicenter_logs($projectId, 'importcustomers', 'Could not create customer '.$customerData['email'].'. Result: '.$e->getMessage(), true);
				return false;
			}
			if(isset($result['id']) && $result['id'] > 0){
				apicenter_logs($projectId, 'importcustomers', 'Created customer '.$customerData['email'], false);
			} else {
				apicenter_logs($projectId, 'importcustomers', 'Could not create customer '.$customerData['email'].'. Result: '.print_r($result, true), true);
			}
			return $result;
		}
		
	}
	
	public function checkCustomerExists($customerData, $projectId){
		$client = $this->getClient($projectId);
		$customers = $client->customers->get(null, array('email' => $customerData['email']));
		return $customers;
	}
	
	
	
	
	
	
    /* ORDERS */
	public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc'){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$client = $this->getClient($projectId);

		$timeFilter = $this->Projects_model->getValue('lightspeed_last_order_get', $projectId);
		$this->Projects_model->saveValue('lightspeed_last_order_get', date('Y-m-d H:i:s'), $projectId);
		$orders = $client->orders->get(null, array(
/*
			'limit' => $amount,
			'page' => ($offset / $amount) + 1,
*/
			'created_at_min' => $timeFilter
		));
// 		echo '<pre>';print_r($orders);exit;

		if(!empty($orders)){
			$finalOrders = array();
			foreach($orders as $order){
				$appendItem = array(
					'id' => $order['number'],
					'order_id' => $order['id'],
					'status' => $order['status'],
					'customer' => array(
						'id' => isset($order['customer']) ? $order['customer']['resource']['id'] : '',
						'email' => $order['email'],
						'first_name' => isset($order['firstname']) ? $order['firstname'] : '',
						'last_name' => isset($order['lastname']) ? $order['lastname'] : ''
					),
					'create_at' => $order['createdAt'],
					'modified_at' => $order['updatedAt'],
					'currency' => 'EUR',
					'totals' => array(
						'total' => $order['priceIncl'],
						'subtotal' => $order['priceExcl'] - $order['shipmentBasePriceExcl'],
						'shipping' => $order['shipmentBasePriceExcl'],
						'tax' => $order['priceIncl'] - $order['priceExcl'],
						'discount' => $order['discountAmount'],
					)
				);
				// Billing
				$name = explode(' ', $order['addressBillingName']);
				$firstName = $name[0];
				unset($name[0]);
				$lastName = implode(' ', $name);
				$appendItem['billing_address'] = array(
					'type' => 'billing',
					'first_name' => $firstName,
					'last_name' => $lastName,
					'postcode' => $order['addressBillingZipcode'],
					'address1' => $order['addressBillingStreet'].' '.$order['addressBillingNumber'].$order['addressBillingExtension'],
					'address2' => isset($order['addressBillingStreet2']) ? $order['addressBillingStreet2'] : '',
					'phone' => $order['phone'],
					'city' => $order['addressBillingCity'],
					'country' => strtoupper($order['addressBillingCountry']['code']),
					'company' => isset($order['companyName']) ? $order['companyName'] : '',
				);

				// Shipping
				$name = explode(' ', $order['addressShippingName']);
				$firstName = $name[0];
				unset($name[0]);
				$lastName = implode(' ', $name);
				$appendItem['shipping_address'] = array(
					'type' => 'shipping',
					'first_name' => $firstName,
					'last_name' => $lastName,
					'postcode' => $order['addressShippingZipcode'],
					'address1' => $order['addressShippingStreet'].' '.$order['addressShippingNumber'].$order['addressShippingExtension'],
					'address2' => isset($order['addressShippingStreet2']) ? $order['addressShippingStreet2'] : '',
					'phone' => $order['phone'],
					'city' => $order['addressShippingCity'],
					'country' => strtoupper($order['addressShippingCountry']['code']),
					'company' => isset($order['addressShippingCompany']) ? $order['addressShippingCompany'] : '',
				);

				if(isset($order['shipmentData']) && !empty($order['shipmentData'])){
					$appendItem['shipping_method'] = $order['shipmentData']['method'];
				}
				if(isset($order['paymentData']) && !empty($order['paymentData'])){
					$appendItem['payment_method'] = $order['paymentData']['method'];
				}
				
				//EDIT: 'model' => $item['sku'],
				$orderItems = $client->ordersProducts->get($order['id']);
				if(isset($orderItems) && !empty($orderItems)){
					$appendItem['order_products'] = array();
					foreach($orderItems as $item){
						$appendItem['order_products'][] = array(
							'product_id' => $item['product']['resource']['id'],
							'order_product_id' => $item['id'],
							'model' => $item['articleCode'] ? $item['articleCode'] : $item['sku'],
							'name' => $item['productTitle'],
							'price' => $item['basePriceExcl'],
							'discount_amount' => isset($item['discountExcl']) ? $item['discountExcl'] : 0,
							'quantity' => $item['quantityOrdered'],
							'total_price' => $item['priceExcl'],
							'total_price_incl_tax' => isset($item['priceIncl']) ? $item['priceIncl'] : 0,
							'tax_percent' => isset($item['taxRates']) ? ($item['taxRates'][0]['rate'] * 100) : 0,
							'tax_value' => $item['priceIncl'] - $item['priceExcl'],
						);
					}
				}
				if(isset($order['comment']) && $order['comment'] != ''){
					$appendItem['comment'] = $order['comment'];
				}
				if(isset($order['memo']) && $order['memo'] != ''){
					$appendItem['comment'] = $appendItem['comment']."\n".$order['memo'];
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
			}
			return $finalOrders;
		}
		return false;
	}

}