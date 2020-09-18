<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/shopwareSDK/vendor/autoload.php';
use LeadCommerce\Shopware\SDK\ShopwareClient as Shopware;

class Shopware_model extends CI_Model {

    function __construct(){
        parent::__construct();

    }

    ########################################################################################
    #     Function is used to set all required params to make shopware api connection.      #
    ########################################################################################
    public function shopwareConnectionParams($projectId){

        $this->load->model('Projects_model');
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $ShopUrl    = '';
        if(!empty($project)){
            $url = trim($project['store_url'], '/');
            $store_url  = $url.'/api/';
        }

        $shopware_api_key       = $this->Projects_model->getValue('shopware_api_key', $projectId)?$this->Projects_model->getValue('shopware_api_key', $projectId):'';
        $shopware_username       = $this->Projects_model->getValue('shopware_username', $projectId)?$this->Projects_model->getValue('shopware_username', $projectId):'';

        $params = array(
            'ShopUrl'  => $store_url,
            'Username' => $shopware_username,
            'ApiKey'   => $shopware_api_key,
        );

        return $params;
    }

//Shopware get connection
    public function getConnection($projectId){
        $this->load->model('Projects_model');
        $project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $store_url = '';
        if(!empty($project)){
			// $store_url = $project['store_url'];
			$store_url  = $project['store_url'].'/api/';
        }

        $shopware_api_key = $this->Projects_model->getValue('shopware_api_key', $projectId)?$this->Projects_model->getValue('shopware_api_key', $projectId):'';
        $shopware_username = $this->Projects_model->getValue('shopware_username', $projectId)?$this->Projects_model->getValue('shopware_username', $projectId):'';
		
        $params = array(
            'ShopUrl' => $store_url,
            'Username' => $shopware_username,
            'ApiKey' => $shopware_api_key,
        );

		$shopware = new Shopware($store_url, $shopware_username, $shopware_api_key);
        return $shopware;
    }

    //Customer
    /*public function createCustomer($projectId, $rawData)
    {
        log_message('debug', 'Create Customer '. var_export($rawData, true));
        
        if ($rawData['email'] != '') {
            log_message('debug', 'Create Customer - Valid Email '. var_export($rawData['email'], true));
            
            $CCiso = $this->RetrieveCountryCode($rawData['address_book_country_1'], $projectId);
            $Exist = $this->checkCustomerExists($rawData['email'], $projectId);
            
            log_message('debug', 'Create Customer - Country '. var_export($CCiso, true));
            log_message('debug', 'Create Customer - Exist '. var_export($Exist, true));
            
            $saveData = array(
                    'email' => $rawData['email'],
                    'firstname' => $rawData['first_name'],
                    'lastname' => $rawData['last_name'],
                    'salutation' => 'mr',
                    'billing' => array(
                        'firstname' => $rawData['address_book_first_name_1'],
                        'lastname' => $rawData['address_book_last_name_1'],
                        'salutation' => 'mr',
                        'street' => $rawData['address_book_address1_1'],
                        'city' => $rawData['address_book_city_1'],
                        'zipcode' => $rawData['address_book_postcode_1'],
                        'country' => $CCiso,
                        'phone' => $rawData['phone'],
                    ),
                );
            
            if($CCiso == -1){
                api2cart_log($projectId, 'importcustomers', 'Error: customer ' . $rawData['first_name'] . ' ' . $rawData['last_name'] . ' --> Invalid Country Code.');
            }
            else if ($Exist != -1){
                //Existing customer
                //try
                //{
	            //    $customer = $shopware->put('customer/'.$Exist, $saveData);
			    //    api2cart_log($projectId, 'importcustomer', 'Updated customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
	            //}
	            //catch(Exception $e)
	            //{
			  //      api2cart_log($projectId, 'importcustomer', 'Could not update customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
		      //      return false;
	            //}
		        //return $customer;
            }
            else
            {//New customer
                $this->load->library('Shopware_restapi');
                $config = $this->shopwareConnectionParams($projectId);
                $shopware = $this->shopware_restapi->getclientconnection($config);
                
                try {
	                $customer = $shopware->post('customer', $saveData);
			        api2cart_log($projectId, 'importcustomers', 'Imported customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
	            } 
	            catch(Exception $e) {
			        api2cart_log($projectId, 'importcustomers', 'Could not import customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
		            return false;
	            }
		        return $customer;
            }
        } 
        else {
            api2cart_log($projectId, 'importcustomers', 'Error: customer ' . $rawData['first_name'] . ' ' . $rawData['last_name'] . ' --> Invalid emailaddress.');
        }
	}*/
	
	//Customer
    public function createCustomer($projectId, $rawData)
    {
        log_message('debug', 'Create Customer '. var_export($rawData, true));
        
        if ($rawData['email'] != '') {
            
            log_message('debug', 'Create Customer - Valid Email '. var_export($rawData['email'], true));
            $Exist = $this->checkCustomerExists($rawData['email'], $projectId);
            if ($Exist == -1) {
                $CCiso = $this->RetrieveCountryCode($rawData['address_book_country_1'], $projectId);
                
                if($CCiso == -1){
                    apicenter_logs($projectId, 'importcustomers', 'Error: customer ' . $rawData['first_name'] . ' ' . $rawData['last_name'] . ' --> Invalid Country Code.', true);
                    return false;
                }

                $saveData = array(
                    'email' => $rawData['email'],
                    'firstname' => $rawData['first_name'],
                    'lastname' => $rawData['last_name'],
                    'salutation' => 'mr',
                    'billing' => array(
                        'firstname' => $rawData['address_book_first_name_1'],
                        'lastname' => $rawData['address_book_last_name_1'],
                        'salutation' => 'mr',
                        'street' => $rawData['address_book_address1_1'],
                        'city' => $rawData['address_book_city_1'],
                        'zipcode' => $rawData['address_book_postcode_1'],
                        'country' => $CCiso,
                        'phone' => $rawData['phone'],
                    ),
                );

                $this->load->library('Shopware_restapi');
                $config = $this->shopwareConnectionParams($projectId);
                $shopware = $this->shopware_restapi->getclientconnection($config);
                
                try {
	                $customer = $shopware->post('customer', $saveData);
			        apicenter_logs($projectId, 'importcustomers', 'Imported customer ' . $saveData['first_name'] . ' ' . $saveData['last_name'], false);
	            } 
	            catch(Exception $e) {
			        apicenter_logs($projectId, 'importcustomers', 'Could not import customer ' . $saveData['first_name'] . ' ' . $saveData['last_name'], true);
		            return false;
	            }
		        return $customer;
            
            } else {
                //Existing customer
                //try
                //{
	            //    $customer = $shopware->put('customer/'.$Exist, $saveData);
			    //    api2cart_log($projectId, 'importcustomer', 'Updated customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
	            //}
	            //catch(Exception $e)
	            //{
			  //      api2cart_log($projectId, 'importcustomer', 'Could not update customer ' . $saveData['first_name'] . ' ' . $saveData['last_name']);
		      //      return false;
	            //}
		        //return $customer;
            }
        } 
        else {
            apicenter_logs($projectId, 'importcustomers', 'Error: customer ' . $rawData['first_name'] . ' ' . $rawData['last_name'] . ' --> Invalid emailaddress.', true);
        }
    }
    
    //Check if customer already exists
    public function checkCustomerExists($email, $projectId)
    {
        $this->load->library('Shopware_restapi');
        $config = $this->shopwareConnectionParams($projectId);
       
        $getData = '';
        $getData = $this->shopware_restapi->getCustomers($config);

        foreach ($getData as $cus){
            $getEmail = $cus['email'];
            if ($email == $getEmail) return $cus['id'];
        }
        
        return -1;
    }
    
    public function RetrieveCountryCode($CC, $projectId){
        $this->load->library('Shopware_restapi');
        $config = $this->shopwareConnectionParams($projectId);
       
        $getData = '';
        $getData = $this->shopware_restapi->getCountries($config);
        
        foreach ($getData as $country){
            $getCountry = $country['iso'];
            if ($CC == $getCountry) return $country['id'];
        }
        return -1;
    }
    
    
    // Product
	public function updateArticles($projectId, $articles){
		//log_message('debug', 'Start Shopware Product '. var_export($articles, true));

		foreach($articles as $article){
			$productExists = $this->checkProductExists($article, $projectId);
			if($productExists != false && !empty($productExists)){
				// Update product
				$this->updateProduct($article, $productExists['id'], $projectId);
			} else {
				// Create product
				$product = $this->createProduct($article, $projectId);
				// Save sku
				$skus = $this->Projects_model->getProjectData($projectId, 'skus', true);
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
				$sku = $article['model'];
				$skus[$sku] = $product['id'];
				$this->Projects_model->saveProjectData($projectId, 'skus', $skus, true);
//				file_put_contents($file, json_encode($skus));
				
				// Update product
				if($product && !empty($product)){
					$this->updateProduct($article, $product['id'], $projectId);
				}
			}
		}
	}
	
	public function checkProductExists($productData, $projectId){
		$this->load->library('Shopware_restapi');
        $shopware = $this->getConnection($projectId);
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
					$product = $shopware->Product($skus[$sku])->get();
			        if(!empty($product)){
						return $product;
			        }
			    } catch(Exception $e){
					return false;
			    }
			}
		//}
        $params = array(
			'title' => $productData['name'],
        );
        try{
        	$config = $this->shopwareConnectionParams($projectId);
			$product = $this->shopware_restapi->getProductBySku($config, $productData['model']);
			// $product = $shopware->getArticleQuery()->findall($params);
	    } catch(Exception $e){
		    return false;
	    }
        if(!empty($product)){
	        return $product;
        }
        return false;
	}
	
	public function createProduct($productData, $projectId){
        $this->load->library('Shopware_restapi');
        $config = $this->shopwareConnectionParams($projectId);
        $shopware = $this->shopware_restapi->getclientconnection($config);

        $imageURL = $productData['image']['url'];

        $mappedcategory = $productData['mappedcategory'] ? $productData['mappedcategory'] : 3;

		if ($projectId == 175) {
			$mappedcategory = isset($productData['custom_attributes']['EAN']) ? $productData['custom_attributes']['EAN'] : 3;

            $saveData = array(
    		    'name' => $productData['custom_attributes']['Webshop_Titel'],
    		    'description' => $productData['custom_attributes']['ShortDescr'],
    		    'description_long' => $productData['custom_attributes']['LargeDescr'],
    		    'changetime'  => date("Y-m-d H:i:s"),
    		    'active' => true,
    		    'tax' => 21,
    		    'metaTitle' => $productData['custom_attributes']['Meta_Title'],
    		    'supplier' => isset($productData['custom_attributes']['Merk']) ? $productData['custom_attributes']['Merk'] : 'No Exact Data',

    		    'categories' => array(
    		        array('id' => $mappedcategory),
    		    ),
    		    
                'images' => array(
                    array('link' => $imageURL),
                ),
		    
    		    'mainDetail' => array(
    		        'number' => $productData['model'],
    		        'active' => true,
    		        'inStock'   => $productData['quantity'],
    		        'prices' => array(
    		            array(
    		                'customerGroupKey' => 'EK',
    		                'price' => $productData['price'],
    		            ),
    		        ),
    		        'ean' => $productData['custom_attributes']['EAN'],
    		    ),
    		);
        }
        else{
		$saveData = array(
		    'name' => $productData['name'],
		    'description_long' => $productData['description'],
		    'changetime'  => date("Y-m-d H:i:s"),
		    'active' => true,
		    'tax' => 21,
		    'supplier' => $productData['suppliercodetx'] ? $productData['suppliercodetx'] : 'No Exact Data' ,
		    		    
		    'categories' => array(
		        array('id' => $mappedcategory),
		    ),
		    
		    //'categories' => array(
            //    array('id' => 15),
            //    array('id' => 16),
            //),

            'images' => array(
                array('link' => $imageURL),     // allowed link options http, https, file, ftp
                //array('link' => 'http://lorempixel.com/640/480/food/'),     // e.g. file:///var/www/shopware/media/upload/test.jpg
            ),
		    
		    'mainDetail' => array(
		        'number' => $productData['model'],
		        'active' => true,
		        'inStock'   => $productData['quantity'],
		        'prices' => array(
		            array(
		                'customerGroupKey' => 'EK',
		                'price' => $productData['price'],
		            ),
		        )
		    ),
		);
	}

        //'price' => number_format(round( ( $productData['price'] * 1.21 ), 2), 2),   'price' => $productData['price'],
        //log_message('debug', 'SKU = ' . $productData['model'] . ' price = ' . number_format(round( ( $productData['price'] * 1.21 ), 2), 2));
        //log_message('debug', var_export($saveData, true));



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
	        $product = $shopware->post('articles', $saveData);
			apicenter_logs($projectId, 'importarticles', 'Created product '.$productData['model'], false);
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not create product '.$productData['model'], true);
		    return false;
	    }
		return $product;
	}
	
	public function updateProduct($productData, $productId, $projectId){
        $this->load->library('Shopware_restapi');
        $config = $this->shopwareConnectionParams($projectId);
        $shopware = $this->shopware_restapi->getclientconnection($config);
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
        
        //'price' => $productData['price'] ? $productData['price'] : 0,
        //'price' => $productData['price'] ? $productData['price'] : 0,
        //'price' => number_format(round( ( $productData['price'] * 1.21 ), 2), 2),
		
		if ($projectId == 175) {
            $saveData = array(
    		    'name' => $productData['custom_attributes']['Webshop_Titel'],
    		    'description' => $productData['custom_attributes']['ShortDescr'],
    		    'description_long' => $productData['custom_attributes']['LargeDescr'],
    		    'changetime'  => date("Y-m-d H:i:s"),
    		    'active' => true,
    		    'tax' => 21,
    		    'metaTitle' => $productData['custom_attributes']['Meta_Title'],
    		    'supplier' => isset($productData['custom_attributes']['Merk']) ? $productData['custom_attributes']['Merk'] : 'No Exact Data',
				'purchasePrice' => $productData['price'],
    		    // 'categories' => array(
    		    //     array('id' => $mappedcategory),
    		    // ),
    		    
                // 'images' => array(
                //     array('link' => $images),
                // ),
		    
    		    'mainDetail' => array(
    		        'number' => $productData['model'],
    		        'active' => true,
    		        'inStock'   => $productData['quantity'],
    		        'prices' => array(
    		            array(
    		                'customerGroupKey' => 'EK',
    		                'price' => $productData['price'],
    		            ),
    		        ),
    		        'ean' => $productData['custom_attributes']['EAN'],
    		    ),
    		);
        }
        else
        {
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
				'images' => $images//array()
			);
		}

		//log_message('debug', 'Shopware Export '. var_export($saveData, true));


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
	        $product = $shopware->put('articles/' . $productId, $saveData);
	        // $this->addImagesToProduct($projectId, $productId, $images);
	        // $this->removeProductFromCollections($projectId, $productId);
	        // $this->addProductToCollections($projectId, $productId, $categoryIds);
			apicenter_logs($projectId, 'importarticles', 'Updated product '.$productData['model'], false);
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not update product '.$productData['model'], true);
		    return false;
	    }
		return $product;
	}
	
	public function addImagesToProduct($projectId, $productId, $images){
        $shopware = $this->getConnection($projectId);
        foreach($images as $image){
			try{
		        $image = $shopware->Product($productId)->Image->post($image);
		    } catch(Exception $e){
			    
		    }
		}
	}

	public function removeProductFromCollections($projectId, $productId){
        $shopware = $this->getConnection($projectId);
		try{
	        $categories = $shopware->CustomCollection->get(array(
		        'product_id' => $productId
	        ));
	        if(!empty($categories)){
		        foreach($categories as $category){
			        $collect = $shopware->Collect->get(array(
				        'product_id' => $productId,
				        'collection_id' => $category['id']
			        ));
			        if(!empty($collect)){
				        $shopware->Collect($collect['id'])->delete();
			        }
		        }
	        }
	    } catch(Exception $e){

	    }
	}

	public function addProductToCollections($projectId, $productId, $categoryIds){
        $shopware = $this->getConnection($projectId);
        foreach($categoryIds as $categoryId){
			try{
		        $category = $shopware->CustomCollection($categoryId)->get();
		        if(!empty($category)){
			        $saveData = array(
				        'collects' => array(
							array(
								'product_id' => $productId
							)
				        )
			        );
			        $shopware->CustomCollection($categoryId)->put($saveData);
					$category = $shopware->CustomCollection($categoryId)->get();
		        }
		    } catch(Exception $e){
			    
		    }
		}
	}
	
	public function removeArticles($projectId, $articles){
        $shopware = $this->getConnection($projectId);
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
				        $shopware->Product($skus[$itemCode])->delete();
				    } catch(Exception $e){
					    return false;
				    }
				}
			}
		//}
	}

	public function findCategory($projectId, $categoryName){
		$shopware = $this->getConnection($projectId);
		$cat = [];

		try{
			$params = array(
				'title' => $categoryName
			);
			$category = $shopware->getCategoriesQuery()->findall($params);

			if ($projectId == 79 || $projectId == 175) {
				$category = $this->object2array($category);
				$key = $this->searchCategory($categoryName, array_column($category, 'name'));

				if($key !== false){
					$cat = array(
						'items' => [
							0 => $category[$key]
						]
					);
				}
			} else {
				if(!empty($category) && count($category)){
					$cat = array(
					'items' => $category
				);
	        	}
			}
	    } catch(Exception $e){
			return false;
	    }

		return $cat;
	}

	public function searchCategory($categoryName, $data) {
		foreach ($data as $key=>$value) {
			if (strtolower($categoryName) == strtolower($value)) {
				return $key;
			}
		}
		return false;
	}

	public function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
		if ($projectId == 79 ) {
			return false;
		}
	
		$this->load->library('Shopware_restapi');
		$category = [];
		$config = $this->shopwareConnectionParams($projectId);
		$shopware = $this->shopware_restapi->getclientconnection($config);

		$saveData = [
			"name"				=> $categoryName,
			"parentId"			=> 3,
			"active"			=> true,
			"external"			=> "",
			"externalTarget"	=> "",
			"hideFilter"		=> false,
			"hideSortings"		=> false,
			"hideTop"			=> true,
		];

		
		if($parentId != '') {
			$saveData["parent"] = $parentId;
		}
	
        if($image != ''){
			$saveData['media'] = [
				"link" => $image
			];
		}
		
		try{
			$response = $shopware->post('categories', $saveData);

			if ($response['success']) {
				apicenter_logs($projectId, 'importarticles', 'Created category '.$categoryName, false);
				$category['id'] = $response['data']['id'];

		        return $category;
			}
	        if($response['success'] == false){
				apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.$response['message'], true);
		        return $category;
	        }
	    } catch(Exception $e){
			apicenter_logs($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.$e->getMessage(), true);
			return $category;
	    }
	}

	//Orders

    ######################################################################################
    #             Function is used to get orders from Shopware.                          #
    ######################################################################################
    public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc'){
	// public function getOrders($projectId, $filters = array()){

        $this->load->model('Projects_model');
        $this->load->library('Shopware_restapi');
        
		$admin_debugging = $this->Projects_model->getValue('admin_logs', $projectId);
		
		if ($admin_debugging == '1'){
			log_message('debug', 'Shop,GetOrder Ping '. var_export($projectId, true));
		}
        
        $config = $this->shopwareConnectionParams($projectId);
        

		$filter = [
			"limit" => $amount,
			"start" => $offset
		];
		
		if ($admin_debugging == '1'){
			log_message('debug', 'Shop, orderinfo, offset, amount '. var_export($filter, true));
			log_message('debug', 'Shop, orderinfo '. var_export($config, true));
        }
		
		$ordersArray = array();
		if($offset == 0){
			$orders = $this->shopware_restapi->getOrders($config, $filter);
			if(!empty($orders)){
				$offset = $orders[0]['number'];
			}
		}
		$offset = intval($offset);
		for($i=0;$i<$amount;$i++){
			$offset += 1;
			$order = $this->shopware_restapi->getOrder($config, $offset);
			if(!empty($order)){
				$ordersArray[] = $order;
			}
		}
		$orders = $ordersArray;

		//$orders = $this->shopware_restapi->getOrders($config, $filter);
		
		if ($admin_debugging == '1'){
			log_message('debug', 'Shop, orderinfo '. $projectId . ' = ' . var_export($orders, true));
		}
		
        $discount = 0;  
        if(isset($orders['total']) && !empty($orders['total'])){
        	$discount = $orders['total'];
        }
    
    	$orders = isset($orders['data']) ? $orders['data'] : $orders;
    
    	$ifStatusFilter = $this->Projects_model->getValue('orderstate_filter', $projectId) != '' ? '1' : '0';
    
        $finalOrders = array();
		if(!empty($orders)){
			foreach($orders as $order){
				
				if ($admin_debugging == '1'){
					log_message('debug', 'Shop, orderinfo '. $projectId . ' = ' . var_export($order, true));
			    }
				
			    $processOrder = '-1';
			    
			    if ($ifStatusFilter == '1'){
			        if ( $order['paymentStatusId'] == 12 && ( $order['orderStatusId'] == 0 || $order['orderStatusId'] == 1 ) ) {
			            $processOrder = '1';
			        }
			    }
			    else {
			        $processOrder = '1';
			    }
				
			    if ($admin_debugging == '1'){
					log_message('debug', 'Process Order '. $projectId . ' DBStat: ' . $ifStatusFilter . ' Pay: ' . $order['paymentStatusId'] . ' OrdStat: ' . $order['orderStatusId']);
					log_message('debug', 'Process: ' . $processOrder);
				}
				
				if ($processOrder == '1'){
					$appendItem = array(
						'id' => $order['number'],
						'order_id' => $order['id'],
						'state' => $order['billing']['state'],
						'customer' => array(
							'id' => isset($order['customer']['id']) ? $order['customer']['id'] : '',
							'email' => $order['customer']['email'],
							'first_name' => isset($order['customer']['firstname']) ? $order['customer']['firstname'] : '',
							'last_name' => isset($order['customer']['lastname']) ? $order['customer']['lastname'] : ''
						),
						'create_at' => $order['orderTime'],
						'modified_at' => $order['changed'],
						'currency' => $order['currency'],
					    'total' => $order['details'][0]['price'],
						'subtotal' => $order['details'][0]['price'],
						'shipping' => isset($order['dispatch']['shippingFree']) ? $order['dispatch']['shippingFree'] : 0,
						'tax' => $order['dispatch']['taxCalculation'],
						'discount' => $order['details'][0]['quantity'],
					);
				
	                $appendItem['billing_address'] = array(
						'type' => 'billing',
	                    'first_name' => isset($order['billing']['firstName']) ? $order['billing']['firstName'] : '',
						'last_name' => isset($order['billing']['lastName']) ? $order['billing']['lastName'] : '',
						'postcode' => $order['billing']['zipCode'],
						'address1' => $order['billing']['street'] . ' '. $order['billing']['additionalAddressLine1'],
						'address2' => $order['billing']['additionalAddressLine2'],
						'phone' => $order['billing']['phone'],
						'city' => $order['billing']['city'],
						'country' => $order['billing']['country']['iso'],
						'state' => isset($order['billing']['state']) ? $order['billing']['state'] : '',
						'company' => isset($order['billing']['company']) ? $order['billing']['company'] : '',
						'vat_number' => isset($order['billing']['vatId']) ? $order['billing']['vatId'] : '',
					);
				
	                if($order['billing']['additionalAddressLine2'] != ''){
						$appendItem['billing_address']['address1'] .= ' '.$order['billing']['additionalAddressLine2'];
						$appendItem['billing_address']['address2'] = '';
					}

	                $appendItem['shipping_address'] = array(
						'type' => 'shipping',
						'first_name' => isset($order['shipping']['firstName']) ? $order['shipping']['firstName'] : '',
						'last_name' => isset($order['shipping']['lastName']) ? $order['shipping']['lastName'] : '',
						'postcode' => $order['shipping']['zipCode'],
						'address1' => $order['shipping']['street'] . ' '. $order['shipping']['additionalAddressLine1'],
						'address2' => $order['shipping']['additionalAddressLine2'],
						'phone' => $order['shipping']['phone'],
						'city' => $order['shipping']['city'],
						'country' => $order['shipping']['country']['iso'],
						'state' => isset($order['shipping']['state']) ? $order['shipping']['state'] : '',
						'company' => isset($order['shipping']['company']) ? $order['shipping']['company'] : '',
					);
					
					if($order['shipping']['additionalAddressLine2'] != ''){
						$appendItem['shipping_address']['address1'] .= ' '.$order['shipping']['additionalAddressLine2'];
						$appendItem['shipping_address']['address2'] = '';
					}
				

					if(isset($order['dispatch'])){
						$appendItem['shipping_method'] = $order['dispatch']['name'];
					}
					if(isset($order['payment'])){
						$appendItem['payment_method'] = $order['payment']['name'];
					}
					if(isset($order['details']) && !empty($order['details'])){

						$appendItem['order_products'] = array();
						foreach($order['details'] as $item){

							$appendItem['order_products'][] = array(
								'product_id' => $item['id'],
								'order_product_id' => $item['id'],
								'model' => $item['articleNumber'],
								'name' => $item['articleName'],
								'price' => $item['price'],
								//'discount_amount' => isset($item['total_discount']) ? ($item['total_discount'] - ($item['total_discount'] / (1 + $item['tax_lines'][0]['rate']))) : 0,
								'discount_amount' => 0,
								'quantity' => $item['quantity'],
								'total_price' => ($item['price'] * $item['quantity']),
								'total_price_incl_tax' => ($item['price'] * $item['quantity']) + (($item['price'] * $item['quantity']) * ($item['taxRate'] / 100)),
								'tax_percent' => $item['taxRate'],
								'tax_value' => ($item['price'] * $item['quantity']) * ($item['taxRate'] / 100),
								'variant_id' => ''
							);
						}
					}
					if(isset($order['invoiceShippingNet']) && $order['invoiceShippingNet'] > 0){
						$appendItem['totals']['shipping'] = $order['invoiceShippingNet'];
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
			} //end for each
		}
		return $finalOrders;
    }
	
	public function object2array($data)
	{
		if (is_array($data))
		{
			$result = array();
			$tmp = array();
			foreach ($data as $value)
			{
				foreach ((array) $value as $key => $val) {
					if (strpos($key, 'id')) $tmp['id'] = trim($val);
					if (strpos($key, 'name')) $tmp['name'] = trim($val);
				}
				$result[] = $tmp;
			}
			return $result;
		}
		return $data;
	}
}