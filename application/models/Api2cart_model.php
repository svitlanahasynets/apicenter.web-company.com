<?php
class Api2cart_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }
    
	/**
	* Validation input method name
	*
	* @return array
	*
	* @throws Exception
	*/
	protected function _validateMethodName($api, $method = array())
	{	
		if(!isset($method[0]) || empty($method[0]) || !isset($method[1]) || empty($method[1])) {
			throw new Exception('Method ' . implode('.', $method) . ' not found', 1);
		}
		
		if(!method_exists($api, $method[0])) {
			throw new Exception('Method ' . implode('.', $method) . ' not found', 1);
		}
		
		$section = $method[0];
		unset($method[0]);
		
		$methodName = $this->_buildMethodName($method);
		
		$object = $api->$section();
		
		if(!method_exists($object, $methodName)) {
			throw new Exception('Method ' . $section .'.' . implode('.', $method) . ' not found', 1);
		}
		
		return array(
			'section' => $section,
			'method'  => $methodName
		);
	}
	
	/**
	* Generates function's name from method's name
	*
	* Example
	* product.option.value.add => apiOptionValueAdd
	*
	* @param array $method
	*
	* @return string
	*/
	protected function _buildMethodName($method)
	{
		$methodName = 'api';	
		foreach($method as $name) {
			$methodName .= ucfirst($name);
		}
		return $methodName;
	}				
	
	function updateArticles($projectId, $articles){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		foreach($articles as $articleData){
			try {
				$findMethod = $this->_validateMethodName($api, array('product', 'find'));
				$addMethod = $this->_validateMethodName($api, array('product', 'add'));
				$updateMethod = $this->_validateMethodName($api, array('product', 'update'));
				$imageMethod = $this->_validateMethodName($api, array('product', 'image', 'add'));
				
				// Check for force in stock
				if($this->Projects_model->getValue('force_in_stock', $projectId) == '1'){
					$articleData['in_stock'] = 'true';
					$articleData['quantity'] = 10000;
				}
				
				$findParams = array(
					'find_value' => $articleData['model'],
					'find_where' => 'model'
				);
				try {
					// Check if product already exists
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$findResult = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$findMethod['section']
								)
							),
							$findMethod['method']
						),
						$findParams
					);

					if(!empty($findResult->product)){
						$articleData['id'] = $findResult->product[0]->id;
					}
					
					// Update product
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$result = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$updateMethod['section']
								)
							),
							$updateMethod['method']
						),
						$articleData
					);
					
					if(isset($findResult->product[0]->id) && $findResult->product[0]->id > 0){
						// Add images
						if(isset($articleData['image']) && !empty($articleData['image'])){
							file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
							$params = array(
								'product_id' => $findResult->product[0]->id,
								'type' => 'base,small,thumbnail',
								'url' => $articleData['image']['url'],
								'image_name' => $articleData['image']['image_name']
							);
							//file_put_contents('productimage.log', var_export($params, true) . PHP_EOL, FILE_APPEND);
							$result = call_user_func(
								array(
									call_user_func(
										array(
											$api,
											$imageMethod['section']
										)
									),
									$imageMethod['method']
								),
								$params
							);
							unlink($articleData['image']['path']);
						}
					}
				} catch (Exception $e) {
					if($e->getCode() != '112'){
						api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to find/update article '.$articleData['model']);
					}
					// Add product
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$result = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$addMethod['section']
								)
							),
							$addMethod['method']
						),
						$articleData
					);
					
					if(isset($result->product_id) && $result->product_id > 0){
						// Add images
						if(isset($articleData['image']) && !empty($articleData['image'])){
							file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
							$params = array(
								'product_id' => $result->product_id,
								'type' => 'base,small,thumbnail',
								'url' => $articleData['image']['url'],
								'image_name' => $articleData['image']['image_name']
							);
							//file_put_contents('productimage.log', var_export($params, true) . PHP_EOL, FILE_APPEND);
							$result = call_user_func(
								array(
									call_user_func(
										array(
											$api,
											$imageMethod['section']
										)
									),
									$imageMethod['method']
								),
								$params
							);
							unlink($articleData['image']['path']);
						}
					}
				}
			} catch (Exception $e) {
				api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to add article '.$articleData['model']);
				//echo "#" . $e->getCode() . " " . $e->getMessage() . "\n";
			}
		}
	}
	
	function updateStockArticles($projectId, $articles){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		foreach($articles as $articleData){
			try {
				$findMethod = $this->_validateMethodName($api, array('product', 'find'));
				$updateMethod = $this->_validateMethodName($api, array('product', 'update'));
				
				$findParams = array(
					'find_value' => $articleData['model'],
					'find_where' => 'model'
				);
				try {
					// Check if product already exists
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$findResult = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$findMethod['section']
								)
							),
							$findMethod['method']
						),
						$findParams
					);

					if(!empty($findResult->product)){
						$articleData['id'] = $findResult->product[0]->id;
					}
					
					// Update product
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$result = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$updateMethod['section']
								)
							),
							$updateMethod['method']
						),
						$articleData
					);
				} catch (Exception $e) {
					if($e->getCode() != '112'){
						api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to find/update stock article '.$articleData['model']);
					}
				}
			} catch (Exception $e) {
				api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to add stock article '.$articleData['model']);
				//echo "#" . $e->getCode() . " " . $e->getMessage() . "\n";
			}
		}
	}
	
	function removeArticles($projectId, $articles){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		foreach($articles as $itemCode){
			try {
				api2cart_log($projectId, 'importarticles', 'Try to remove article '.$itemCode);
				$findMethod = $this->_validateMethodName($api, array('product', 'find'));
				$removeMethod = $this->_validateMethodName($api, array('product', 'delete'));
				
				$findParams = array(
					'find_value' => $itemCode,
					'find_where' => 'model'
				);
				try {
					// Check if product already exists
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$findResult = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$findMethod['section']
								)
							),
							$findMethod['method']
						),
						$findParams
					);

					$articleData = array();
					if(!empty($findResult->product)){
						$articleData['id'] = $findResult->product[0]->id;
					}
					
					// Update product
					file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
					$result = call_user_func(
						array(
							call_user_func(
								array(
									$api,
									$removeMethod['section']
								)
							),
							$removeMethod['method']
						),
						$articleData
					);
					api2cart_log($projectId, 'importarticles', 'Removed article '.$itemCode);
				} catch (Exception $e) {
					if($e->getCode() != '112'){
						api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to find/remove article '.$itemCode);
					}
				}
			} catch (Exception $e) {
				api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to remove article '.$itemCode);
			}
		}
	}
	
	function getOrders($projectId, $offset = 0, $amount = 10){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		try {
			$listOrdersMethod = $this->_validateMethodName($api, array('order', 'list'));
			
			file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
			$result = call_user_func(
				array(
					call_user_func(
						array(
							$api,
							$listOrdersMethod['section']
						)
					),
					$listOrdersMethod['method']
				),
				array('start' => $offset, 'count' => $amount, 'params' => 'force_all')
			);
			return $result;
		} catch (Exception $e) {
			api2cart_log($projectId, 'exportorders', $e->getCode() . " " . $e->getMessage());
			//echo "#" . $e->getCode() . " " . $e->getMessage() . "\n";
			return false;
		}
	}
	
	function findCategory($projectId, $categoryName){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		try {
			$findCategoryMethod = $this->_validateMethodName($api, array('category', 'find'));
			//$data = TestData::data($listOrdersMethod);
			
			file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
			$result = call_user_func(
				array(
					call_user_func(
						array(
							$api,
							$findCategoryMethod['section']
						)
					),
					$findCategoryMethod['method']
				),
				array(
					'find_value' => $categoryName,
					'find_where' => 'name'
				)
			);
			return $result;
		} catch (Exception $e) {
			api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to find category '.$categoryName);
			//echo "#" . $e->getCode() . " " . $e->getMessage() . "\n";
			return false;
		}
	}
	
	function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		try {
			$addCategoryMethod = $this->_validateMethodName($api, array('category', 'add'));
			$addCategoryImageMethod = $this->_validateMethodName($api, array('category', 'image', 'add'));
			//$data = TestData::data($listOrdersMethod);
			
			$params = array(
				'name' => $categoryName,
				'avail' => 'true'
			);
			if($parentId != ''){
				$params['parent_id'] = $parentId;
			}
			
			file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
			$result = call_user_func(
				array(
					call_user_func(
						array(
							$api,
							$addCategoryMethod['section']
						)
					),
					$addCategoryMethod['method']
				),
				$params
			);
			
			if($image != ''){
				$ext = pathinfo($image, PATHINFO_EXTENSION);
				$params = array(
					'category_id' => $result->category_id,
					'image_name' => str_replace(',', '', $categoryName).'.'.$ext,//'.jpg',
					'url' => $image
				);
				file_put_contents('category.log', var_export($params, true) . PHP_EOL, FILE_APPEND);
				file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
				$imageResult = call_user_func(
					array(
						call_user_func(
							array(
								$api,
								$addCategoryImageMethod['section']
							)
						),
						$addCategoryImageMethod['method']
					),
					$params
				);
			}
			return $result;
		} catch (Exception $e) {
			api2cart_log($projectId, 'importarticles', $e->getCode() . " " . $e->getMessage().' Tried to add category '.$categoryName);
			//echo "#" . $e->getCode() . " " . $e->getMessage() . "\n";
			return false;
		}
	}
	
	function createCustomer($projectId, $customerData){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		try {
			$addCustomerMethod = $this->_validateMethodName($api, array('customer', 'add'));

			file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
			$result = call_user_func(
				array(
					call_user_func(
						array(
							$api,
							$addCustomerMethod['section']
						)
					),
					$addCustomerMethod['method']
				),
				$customerData
			);
			return $result;
		} catch (Exception $e) {
			if($e->getCode() == '113' || $e->getCode() == 113){
				api2cart_log($projectId, 'importcustomers', $e->getCode() . " " . $e->getMessage().' Tried to add customer '.$customerData['first_name'].' '.$customerData['last_name'].'. Try to update customer now.');
				return $this->updateCustomer($projectId, $customerData);
			}
			api2cart_log($projectId, 'importcustomers', $e->getCode() . " " . $e->getMessage().' Tried to add customer '.$customerData['first_name'].' '.$customerData['last_name']);
			return false;
		}
	}
	
	function updateCustomer($projectId, $customerData){
		$this->load->helper('Api2Cart/Api');
		
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$apiKey = $project['api_key'];
		$storeKey = $project['store_key'];
		$api = new Api($apiKey, $storeKey);
		
		try {
			$updateCustomerMethod = $this->_validateMethodName($api, array('customer', 'update'));

			file_put_contents('apilog.log', date('d-m-Y').' Api call for project '.$projectId . PHP_EOL, FILE_APPEND);
			$result = call_user_func(
				array(
					call_user_func(
						array(
							$api,
							$updateCustomerMethod['section']
						)
					),
					$updateCustomerMethod['method']
				),
				$customerData
			);
			return $result;
		} catch (Exception $e) {
			api2cart_log($projectId, 'importcustomers', $e->getCode() . " " . $e->getMessage().' Tried to update customer '.$customerData['first_name'].' '.$customerData['last_name']);
			return false;
		}
	}

}