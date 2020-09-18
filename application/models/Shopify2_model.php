<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/shopifySDK/vendor/autoload.php';
use PHPShopify\ShopifySDK as Shopify;

class Shopify2_model extends CI_Model {

    function __construct(){
        parent::__construct();
    }

    public function getConnection($projectId){
        $this->load->model('Projects_model');
        $project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $store_url = '';
        if(!empty($project)){
            $store_url = $project['store_url'];
        }

        $shopify_api_key = $this->Projects_model->getValue('shopify_api_key', $projectId)?$this->Projects_model->getValue('shopify_api_key', $projectId):'';
        $shopify_password = $this->Projects_model->getValue('shopify_password', $projectId)?$this->Projects_model->getValue('shopify_password', $projectId):'';

        $params = array(
            'ShopUrl' => $store_url,
            'ApiKey' => $shopify_api_key,
            'Password' => $shopify_password,
        );

        $shopify = Shopify::config($params);

        return $shopify;
    }

    /* PRODUCTS */
	public function updateArticles($projectId, $articles){
		foreach($articles as $article){
			$productExists = $this->checkProductExists($article, $projectId);
			if($productExists != false && !empty($productExists)){
				// Update product
				$this->updateProduct($article, $productExists['id'], $projectId);
			} else {
				// Create product
				$product = $this->createProduct($article, $projectId);
				
				// Save sku
/*
				$folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';
				if(!file_exists($folder)){
					mkdir($folder, 0777, true);
				}
				$file = $folder.'skus.txt';
				if(file_exists($file)){
					$skus = json_decode(file_get_contents($file), true);
				} else {
					$skus = array();
				}
*/
				$skus = $this->Projects_model->getProjectData($projectId, 'skus', true);
				$sku = $article['model'];
				$skus[$sku] = $product['id'];
				$this->Projects_model->saveProjectData($projectId, 'skus', $skus, true);
// 				file_put_contents($file, json_encode($skus));
				
				// Update product
				if($product && !empty($product)){
					$this->updateProduct($article, $product['id'], $projectId);
				}
			}
		}
	}
	
	public function checkProductExists($productData, $projectId){
        $shopify = $this->getConnection($projectId);
		$skus = $this->Projects_model->getProjectData($projectId, 'skus', true);
/*
		$folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';
		if(!file_exists($folder)){
			mkdir($folder, 0777, true);
		}
		$file = $folder.'skus.txt';
*/
		
		//if(file_exists($file)){
			//$skus = json_decode(file_get_contents($file), true);
			$sku = $productData['model'];
			if(isset($skus[$sku]) && $skus[$sku] != ''){
		        try{
			        $product = $shopify->Product($skus[$sku])->get();
			        if(!empty($product)){
				        return $product;
			        }
			    } catch(Exception $e){
				    return false;
			    }
			}
		//}
        $params = array(
	        'title' => $productData['name']
        );
        try{
	        $product = $shopify->Product->get($params);
	    } catch(Exception $e){
		    return false;
	    }
        if(!empty($product)){
	        return $product;
        }
        return false;
	}
	
	public function createProduct($productData, $projectId){
        $shopify = $this->getConnection($projectId);
		$saveData = array(
			'variants' => array(
				array(
					'sku' => $productData['model'],
					'inventory_quantity' => $productData['quantity'] ? $productData['quantity'] : 0,
					'price' => $productData['price'] ? $productData['price'] : 0,
				)
			),
			'title' => $productData['name'],
			'price' => $productData['price'] ? $productData['price'] : 0,
			'body_html' => $productData['description'],
			'images' => array()
		);

		// Set category
		$categoryIds = array();
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
		}

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'create');
			}
		}
		
		try{
	        $product = $shopify->Product->post($saveData);
	        $this->removeProductFromCollections($projectId, $product['id']);
	        $this->addProductToCollections($projectId, $product['id'], $categoryIds);
			apicenter_logs($projectId, 'importarticles', 'Created product '.$productData['model'], false);
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not create product '.$productData['model'], true);
		    return false;
	    }
		return $product;
	}
	
	public function updateProduct($productData, $productId, $projectId){
        $shopify = $this->getConnection($projectId);
        
		$images = array();
		$baseImage = '';
		if(isset($productData['image']) && !empty($productData['image'])){
			$image = $productData['image'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_1']) && !empty($productData['image_1'])){
			$image = $productData['image_1'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_2']) && !empty($productData['image_2'])){
			$image = $productData['image_2'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_3']) && !empty($productData['image_3'])){
			$image = $productData['image_3'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_4']) && !empty($productData['image_4'])){
			$image = $productData['image_4'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_5']) && !empty($productData['image_5'])){
			$image = $productData['image_5'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
		if(isset($productData['image_6']) && !empty($productData['image_6'])){
			$image = $productData['image_6'];
			if($image['url'] != ''){
				$base64Data = base64_encode(file_get_contents($image['path']));
				$images[] = array(
			        "attachment" => $base64Data,
				);
			}
		}
        
		$saveData = array(
			'variants' => array(
				array(
					'sku' => $productData['model'],
					'inventory_quantity' => $productData['quantity'] ? $productData['quantity'] : 0,
					'price' => $productData['price'] ? $productData['price'] : 0,
				)
			),
			'sku' => $productData['model'],
			'title' => $productData['name'],
			'price' => $productData['price'] ? $productData['price'] : 0,
			'body_html' => $productData['description'],
			'images' => array()
		);

		// Set category
		$categoryIds = array();
		if(isset($productData['categories_ids']) && $productData['categories_ids'] != ''){
			$categoryIds = explode(',', $productData['categories_ids']);
		}

		// Load project specific data
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'checkConfigurable')){
				$saveData = $this->$projectModel->checkConfigurable($saveData, $productData, $projectId, 'create');
			}
		}
		
		try{
	        $product = $shopify->Product($productId)->put($saveData);
	        $this->addImagesToProduct($projectId, $productId, $images);
	        $this->removeProductFromCollections($projectId, $productId);
	        $this->addProductToCollections($projectId, $productId, $categoryIds);
			apicenter_logs($projectId, 'importarticles', 'Updated product '.$productData['model'], false);
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not update product '.$productData['model'], true);
		    return false;
	    }
		return $product;
	}
	
	public function addImagesToProduct($projectId, $productId, $images){
        $shopify = $this->getConnection($projectId);
        foreach($images as $image){
			try{
		        $image = $shopify->Product($productId)->Image->post($image);
		    } catch(Exception $e){
			    
		    }
		}
	}

	public function removeProductFromCollections($projectId, $productId){
        $shopify = $this->getConnection($projectId);
		try{
	        $categories = $shopify->CustomCollection->get(array(
		        'product_id' => $productId
	        ));
	        if(!empty($categories)){
		        foreach($categories as $category){
			        $collect = $shopify->Collect->get(array(
				        'product_id' => $productId,
				        'collection_id' => $category['id']
			        ));
			        if(!empty($collect)){
				        $shopify->Collect($collect['id'])->delete();
			        }
		        }
	        }
	    } catch(Exception $e){

	    }
	}

	public function addProductToCollections($projectId, $productId, $categoryIds){
        $shopify = $this->getConnection($projectId);
        foreach($categoryIds as $categoryId){
			try{
		        $category = $shopify->CustomCollection($categoryId)->get();
		        if(!empty($category)){
			        $saveData = array(
				        'collects' => array(
							array(
								'product_id' => $productId
							)
				        )
			        );
			        $shopify->CustomCollection($categoryId)->put($saveData);
					$category = $shopify->CustomCollection($categoryId)->get();
		        }
		    } catch(Exception $e){
			    
		    }
		}
	}
	
	public function removeArticles($projectId, $articles){
        $shopify = $this->getConnection($projectId);
		$skus = $this->Projects_model->getProjectData($projectId, 'skus', true);
/*
		$folder = DATA_DIRECTORY.'/projects_files/'.$projectId.'/';
		if(!file_exists($folder)){
			mkdir($folder, 0777, true);
		}
		$file = $folder.'skus.txt';
*/
		
		//if(file_exists($file)){
			//$skus = json_decode(file_get_contents($file), true);
			foreach($articles as $itemCode){
				if(isset($skus[$itemCode]) && $skus[$itemCode] != ''){
			        try{
				        $shopify->Product($skus[$itemCode])->delete();
				    } catch(Exception $e){
					    return false;
				    }
				}
			}
		//}
	}

	public function findCategory($projectId, $categoryName){
        $shopify = $this->getConnection($projectId);
		try{
			$params = array(
				'title' => $categoryName
			);
	        $category = $shopify->CustomCollection->get($params);
	        if(!empty($category)){
				return array(
					'items' => $category
				);
	        }
	    } catch(Exception $e){
			return false;
	    }
		return false;
	}


	function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
        $shopify = $this->getConnection($projectId);
		$saveData = array(
			'title' => $categoryName
		);
		if($image != ''){
			$saveData['image'] = array(
				'attachement' => $image
			);
		}
		
		try{
	        $category = $shopify->CustomCollection->post($saveData);
	        if(!empty($category)){
				apicenter_logs($projectId, 'importarticles', 'Created category '.$categoryName, false);
		        return $category;
	        }
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.$e->getMessage(), true);
			return false;
	    }
		apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName, true);
		return false;
	}
	
	public function updateStockArticles($projectId, $articles){
        $shopify = $this->getConnection($projectId);
        foreach($articles as $productData){
			$saveData = array(
				'variants' => array(
					array(
						'sku' => $productData['model'],
						'inventory_quantity' => $productData['quantity'] ? $productData['quantity'] : 0,
					)
				),
			);
			
			try{
		        $product = $this->checkProductExists($productData, $projectId);
		        $product = $shopify->Product($product[0]['id'])->put($saveData);

				log_message('debug', 'ProductStock - Shopify  ' . $projectId . ' Result:' . var_export($product, true));

				apicenter_logs($projectId, 'importarticles', 'Updated product stock for product '.$productData['model'], false);
		    } catch(Exception $e){
				apicenter_logs($projectId, 'importarticles', 'Could not update product stock for product '.$productData['model'].'. Result: '.$e->getMessage(), true);
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
        $shopify = $this->getConnection($projectId);
		$customerExists = $this->checkCustomerExists($customerData, $projectId);
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$erpSystem = $project['erp_system'];
		if($customerExists != false && !empty($customerExists)){
			if($erpSystem == 'exactonline'){
				$saveData = array(
					'email' => $customerData['email'],
					'first_name' => $customerData['first_name'],
					'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'verified_email' => true,
					'addresses' => array(
						array(
							'country' => $customerData['address_book_country_1'],
							'address1' => $customerData['address_book_address1_1'],
							'zip' => $customerData['address_book_postcode_1'],
							'city' => $customerData['address_book_city_1'],
							'first_name' => $customerData['first_name'],
							'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						)
					)
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
					$saveData['addresses'][0]['phone'] = $customerData['phone'];
				}
			} else {
				$saveData = array(
					'email' => $customerData['email'],
					'first_name' => $customerData['first_name'],
					'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'verified_email' => true,
					'addresses' => array(
						array(
							'country' => $customerData['country'],
							'address1' => $customerData['address'],
							'zip' => $customerData['postcode'],
							'city' => $customerData['city'],
							'first_name' => $customerData['first_name'],
							'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						)
					)
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
					$saveData['addresses'][0]['phone'] = $customerData['phone'];
				}
			}
			
			try{
		        $customer = $shopify->Customer($customerExists[0]['id'])->put($saveData);
				apicenter_logs($projectId, 'importcustomers', 'Updated customer '.$customerData['email'], false);
				return $customer;
		    } catch(Exception $e){
				apicenter_logs($projectId, 'importcustomers', 'Could not update customer '.$customerData['email'].'. Result: '.$e->getMessage(), true);
		    }
			return false;
		} else {
			// Create customer
			if($erpSystem == 'exactonline'){
				$saveData = array(
					'email' => $customerData['email'],
					'first_name' => $customerData['first_name'],
					'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'verified_email' => true,
					'addresses' => array(
						array(
							'country' => $customerData['address_book_country_1'],
							'address1' => $customerData['address_book_address1_1'],
							'zip' => $customerData['address_book_postcode_1'],
							'city' => $customerData['address_book_city_1'],
							'first_name' => $customerData['first_name'],
							'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						)
					)
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
					$saveData['addresses'][0]['phone'] = $customerData['phone'];
				}
			} else {
				$saveData = array(
					'email' => $customerData['email'],
					'first_name' => $customerData['first_name'],
					'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
					'verified_email' => true,
					'addresses' => array(
						array(
							'country' => $customerData['country'],
							'address1' => $customerData['address'],
							'phone' => isset($customerData['phone']) ? $customerData['phone'] : '',
							'zip' => $customerData['postcode'],
							'city' => $customerData['city'],
							'first_name' => $customerData['first_name'],
							'last_name' => ($customerData['last_name'] != '' && $customerData['last_name'] != ' ') ? $customerData['last_name'] : '_',
						)
					)
				);
				if(isset($customerData['phone']) && $customerData['phone'] != ''){
					$saveData['phone'] = $customerData['phone'];
					$saveData['addresses'][0]['phone'] = $customerData['phone'];
				}
			}
// 			echo '<pre>';print_r($saveData);exit;

			try{
		        $customer = $shopify->Customer->post($saveData);
				apicenter_logs($projectId, 'importcustomers', 'Created customer '.$customerData['email'], false);
				return $customer;
		    } catch(Exception $e){
				apicenter_logs($projectId, 'importcustomers', 'Could not create customer '.$customerData['email'].'. Result: '.$e->getMessage(), true);
		    }
			return false;
		}
		
	}
	
	public function checkCustomerExists($customerData, $projectId){
        $shopify = $this->getConnection($projectId);
        if($customerData['email'] == ''){
	        return false;
        }
        try{
	        $customer = $shopify->Customer->search('email:'.$customerData['email']);
	    } catch(Exception $e){
		    return false;
	    }
        if(!empty($customer)){
	        return $customer;
        }
        return false;
	}



    public function shopifyConnectionParams($projectId){

        $this->load->model('Projects_model');
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $ShopUrl    = '';
        if(!empty($project)){
            $store_url  = $project['store_url'];
        }

        $shopify_api_key       = $this->Projects_model->getValue('shopify_api_key', $projectId)?$this->Projects_model->getValue('shopify_api_key', $projectId):'';
        $shopify_password       = $this->Projects_model->getValue('shopify_password', $projectId)?$this->Projects_model->getValue('shopify_password', $projectId):'';

        $params = array(
            'ShopUrl'  => $store_url,
            'ApiKey'   => $shopify_api_key,
            'Password' => $shopify_password,
        );
        
       //log_message('error', 'Shopify Params'. var_export($params, true));
        

        return $params;
    }

    public function getOrders($projectId, $offset, $amount, $sortOrder = 'asc'){
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$erpSystem = $project['erp_system'];
		
        $this->load->model('Projects_model');
        $this->load->library('Shopify_restapi');
		$config = $this->shopifyConnectionParams($projectId);
		/*$params = [
			'financial_status' => 'any',
			'fulfillment_status' => 'unfulfilled',
			'any' => 'any'
		];*/
		
		// log_message('error', 'Params !!!!!123'. var_export($params, true));
		// log_message('error', 'Params !!!!!124'. print_r($params, true));
		
		$filters = array(
		    'status' => 'any',
		    'fulfillment_status' => 'any',
		    'since_id' => $offset,
		    'limit' => $amount
		);

        $shopify = $this->getConnection($projectId);
		try{
	        $orders = $shopify->Order->get($filters);
	    } catch(Exception $e){
			return false;
	    }
	    
		//$orders = $this->shopify_restapi->getOrders($config, $filters);
		
		$params2 = count($orders);
		
		log_message('debug', 'Total_orders '. var_export($params2, true));
		
		
        
		$finalOrders = array();
		if(!empty($orders)){
			foreach($orders as $order){
				// Load project specific data
				$projectModel = 'Project'.$projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'orderBeforeSend')){
						$order = $this->$projectModel->orderBeforeSend($order);
					}
				}

				$discount = 0;
				if(isset($order['discount_codes']) && !empty($order['discount_codes'])){
					foreach($order['discount_codes'] as $code){
						$discount += $code['amount'];
					}
				}
				
				log_message('error', 'Customer'. var_export($order['customer']['first_name'], true));
				
				log_message('error', 'Customer Data Shopify'. var_export($order));
				
				//log_message('error', 'Exact - Customer0 '.var_dump($order['customer']));
				
				$appendItem = array(
					'since_id' => $order['id'],
					'id' => $order['name'],
//					'order_id' => $order['id'],
                    'order_id' => $order['order_number'],
					'state' => $order['financial_status'],
					'customer' => array(
						'id' => isset($order['customer']['id']) ? $order['customer']['id'] : '',
						'email' => $order['customer']['email'],
						'first_name' => isset($order['customer']['first_name']) ? $order['customer']['first_name'] : '',
						'last_name' => isset($order['customer']['last_name']) ? $order['customer']['last_name'] : ''
					),
					'create_at' => $order['created_at'],
					'modified_at' => $order['updated_at'],
					'currency' => $order['currency'],
					'totals' => array(
						'total' => $order['total_price'],
						'subtotal' => $order['subtotal_price'],
						'shipping' => isset($order['shipping_lines'][0]) ? $order['shipping_lines'][0]['price'] : 0,
						'tax' => $order['total_tax'],
						'discount' => $discount,
					)
				);
	
				$appendItem['billing_address'] = array(
					'type' => 'billing',
					'first_name' => $order['billing_address']['first_name'],
					'last_name' => $order['billing_address']['last_name'],
					'postcode' => $order['billing_address']['zip'],
					'address1' => $order['billing_address']['address1'],
					'address2' => $order['billing_address']['address2'],
					'phone' => $order['billing_address']['phone'],
					'city' => $order['billing_address']['city'],
					'country' => $order['billing_address']['country_code'],
					'state' => isset($order['billing_address']['province']) ? $order['billing_address']['province'] : '',
					'company' => isset($order['billing_address']['company']) ? $order['billing_address']['company'] : '',
				);
				if($order['billing_address']['address2'] != ''){
					$appendItem['billing_address']['address1'] .= ' '.$order['billing_address']['address2'];
					$appendItem['billing_address']['address2'] = '';
				}
	
				$appendItem['shipping_address'] = array(
					'type' => 'shipping',
					'first_name' => $order['shipping_address']['first_name'],
					'last_name' => $order['shipping_address']['last_name'],
					'postcode' => $order['shipping_address']['zip'],
					'address1' => $order['shipping_address']['address1'],
					'address2' => $order['billing_address']['address2'],
					'phone' => $order['shipping_address']['phone'],
					'city' => $order['shipping_address']['city'],
					'country' => $order['shipping_address']['country_code'],
					'state' => isset($order['shipping_address']['province']) ? $order['shipping_address']['province'] : '',
					'company' => isset($order['shipping_address']['company']) ? $order['shipping_address']['company'] : '',
				);
				if($order['shipping_address']['address2'] != ''){
					$appendItem['shipping_address']['address1'] .= ' '.$order['shipping_address']['address2'];
					$appendItem['shipping_address']['address2'] = '';
				}
	
				if(isset($order['shipping_lines'][0])){
					$appendItem['shipping_method'] = $order['shipping_lines'][0]['code'];
				}
				if(isset($order['payment_gateway_names'][0])){
					$appendItem['payment_method'] = $order['payment_gateway_names'][0];
				}
				if(isset($order['line_items']) && !empty($order['line_items'])){
					$appendItem['order_products'] = array();
					foreach($order['line_items'] as $item){
						$itemPrice = ($item['total_discount'] > 0) ? ($item['price'] - ($item['total_discount'] / $item['quantity'])) : $item['price'];
						$appendItem['order_products'][] = array(
							'product_id' => $item['product_id'],
							'order_product_id' => $item['product_id'],
							'model' => $item['sku'],
							'name' => $item['title'],
							'price' => $itemPrice,
							'discount_amount' => isset($item['total_discount']) ? ($item['total_discount'] - ($item['total_discount'] / (1 + $item['tax_lines'][0]['rate']))) : 0,
							'quantity' => $item['quantity'],
							'total_price' => ($itemPrice * $item['quantity']),
							'total_price_incl_tax' => ($itemPrice * $item['quantity']) + $item['tax_lines'][0]['price'],
							'tax_percent' => $item['tax_lines'][0]['rate'] * 100,
							'tax_value' => $item['tax_lines'][0]['price'],
							'variant_id' => $item['variant_id']
						);
					}
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
					if($erpSystem == 'exactonline'){
						$appendItem = json_decode(json_encode($appendItem));
					}
					$finalOrders[] = $appendItem;
				}
			}
		}
		//log_message('error', 'Ordesr shopify unfulfilled'. var_export($finalOrders ,true));
		return $finalOrders;
    }
   
}