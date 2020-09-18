<?php
class Exactonline_model extends CI_Model {

	// https://github.com/picqer/exact-php-client

	private $projectId;
	private $redirectUrl;
	private $clientId;
	private $clientSecret;
	private $connection;

    public function __construct()
    {
        parent::__construct();
    }

	public function xml2array ( $xmlObject, $out = array () )
	{
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;

		return $out;
	}

    public function setData($data){
	    foreach($data as $key => $value){
		    $this->$key = $value;
	    }
    }

	/**
	 * Function to retrieve persisted data for the example
	 * @param string $key
	 * @return null|string
	 */
	public function getValue($key)
	{
		if(isset($this->projectId) && $this->projectId > 0){
			$this->load->model('Projects_model');
			return $this->Projects_model->getValue($key, $this->projectId);
		}
		return null;
	}

	/**
	 * Function to persist some data for the example
	 * @param string $key
	 * @param string $value
	 */
	public function setValue($key, $value)
	{
		if(isset($this->projectId) && $this->projectId > 0){
			$this->load->model('Projects_model');
			$this->Projects_model->saveValue($key, $value, $this->projectId);
			
			if($this->projectId == 72 && $key == 'exact_refreshtoken') {
			    $this->db->insert('exact_tokens',
                    [
                        'project' => $this->projectId,
                        'type' => $key,
                        'value' => $value
                    ]
                );
            }
		}
	}

	/**
	 * Function to authorize with Exact, this redirects to Exact login promt and retrieves authorization code
	 * to set up requests for oAuth tokens
	 */
	public function authorize()
	{
	    $connection = new \Picqer\Financials\Exact\Connection();
	    $baseUrl = $this->Projects_model->getValue('exactonline_base_url', $this->projectId);
	    if($baseUrl != ''){
		    $connection->setBaseUrl($baseUrl);
		}
	    $connection->setRedirectUrl($this->redirectUrl);
	    $connection->setExactClientId($this->clientId);
	    $connection->setExactClientSecret($this->clientSecret);
	    $connection->redirectForAuthorization();
	}

	/**
	 * Function to connect to Exact, this creates the client and automatically retrieves oAuth tokens if needed
	 *
	 * @return \Picqer\Financials\Exact\Connection
	 * @throws Exception
	 */
	public function connect($authorizationCode = '')
	{
		//log_message('debug', 'Project ID = ' . $this->projectId . ' Auth key = ' . $authorizationCode);
	    $connection = new \Picqer\Financials\Exact\Connection();
	    $baseUrl = $this->Projects_model->getValue('exactonline_base_url', $this->projectId);
	    if($baseUrl != ''){
		    $connection->setBaseUrl($baseUrl);
		}
	    $connection->setRedirectUrl($this->redirectUrl);
	    $connection->setExactClientId($this->clientId);
	    $connection->setExactClientSecret($this->clientSecret);
		$connection->setProjectId($this->projectId);

	    // Retrieves exact_authorizationcode from database
	    if ($this->getValue('exact_authorizationcode') != '') {
	        $connection->setAuthorizationCode($this->getValue('exact_authorizationcode'));
	    }
	    if($authorizationCode != ''){
	        $connection->setAuthorizationCode($authorizationCode);
	    }

	    // Retrieves exact_accesstoken from database
	    if ($this->getValue('exact_accesstoken') != '') {
	        $connection->setAccessToken($this->getValue('exact_accesstoken'));
	    }

	    // Retrieves exact_refreshtoken from database
	    if ($this->getValue('exact_refreshtoken') != '') {
	        $connection->setRefreshToken($this->getValue('exact_refreshtoken'));
	    }

	    // Retrieves expires timestamp from database
	    if ($this->getValue('exact_expires_in') != '') {
	        $connection->setTokenExpires($this->getValue('exact_expires_in'));
	    }
		//log_message('debug', 'Before connect Project ID = ' . $this->projectId . ' Auth key = ' . $authorizationCode);
		// Make the client connect and exchange tokens

	    try {
	        $connection->connect();
		} catch (RequestException $e) {
			api2cart_log($this->projectId, 'exact_setup', 'Could not connect to Exact. Trying to reconnect by authorization code. '.$e->getMessage());
			$connection->setAccessToken(null);
			$connection->setRefreshToken(null);
			$connection->setTokenExpires(null);
			$connection->connect();
	    } catch (\Exception $e) {
			api2cart_log($this->projectId, 'exact_setup', 'Could not connect to Exact. '.$e->getMessage());
		    return false;
	        throw new Exception('Could not connect to Exact: ' . $e->getMessage());
	    }

	    return $connection;
	}

	public function extConnect($authorizationCode = '') {
        $connection = new \Picqer_ext\ExtConnection();
        $baseUrl = $this->Projects_model->getValue('exactonline_base_url', $this->projectId);
        if($baseUrl != ''){
            $connection->setBaseUrl($baseUrl);
        }
        $connection->setRedirectUrl($this->redirectUrl);
        $connection->setExactClientId($this->clientId);
        $connection->setExactClientSecret($this->clientSecret);
        $connection->setProjectId($this->projectId);

        // Retrieves exact_authorizationcode from database
        if ($this->getValue('exact_authorizationcode') != '') {
            $connection->setAuthorizationCode($this->getValue('exact_authorizationcode'));
        }
        if($authorizationCode != ''){
            $connection->setAuthorizationCode($authorizationCode);
        }

        // Retrieves exact_accesstoken from database
        if ($this->getValue('exact_accesstoken') != '') {
            $connection->setAccessToken($this->getValue('exact_accesstoken'));
        }

        // Retrieves exact_refreshtoken from database
        if ($this->getValue('exact_refreshtoken') != '') {
            $connection->setRefreshToken($this->getValue('exact_refreshtoken'));
        }

        // Retrieves expires timestamp from database
        if ($this->getValue('exact_expires_in') != '') {
            $connection->setTokenExpires($this->getValue('exact_expires_in'));
        }
        exact_log($this->projectId, 'connection_request', json_encode($connection));
        // Make the client connect and exchange tokens
        try {
            $connection->connect();
        } catch (\Exception $e) {
            api2cart_log($this->projectId, 'exact_setup', 'Could not connect to Exact. '.$e->getMessage());
            return false;
            throw new Exception('Could not connect to Exact: ' . $e->getMessage());
        }
        exact_log($this->projectId, 'connection_response_ext', json_encode($connection));
        return $connection;
    }

	public function makeConnection($projectId = '', $authorizationCode = ''){
		$this->projectId = $projectId;
		// If authorization code is returned from Exact, save this to use for token request
		//log_message('debug', 'Project id = ' . $projectId . 'auth code = ' . $_GET['code']);
		exact_log($projectId, 'make_conn_get', $_GET['code']);
		
		if (isset($_GET['code']) && is_null($this->getValue('exact_authorizationcode'))) {
		    $this->setValue('exact_authorizationcode', $_GET['code']);
			//log_message('debug', 'Project id111111111111111 = ' . $projectId . 'auth code = ' . $_GET['code']);
		}

		// If we do not have a authorization code, authorize first to setup tokens
		if ($this->getValue('exact_authorizationcode') === null && $authorizationCode == '') {
		    $this->authorize();
		}

		// Create the Exact client
		if($projectId == 72) {
            $connection = $this->extConnect($authorizationCode);
        } else {
            $connection = $this->connect($authorizationCode);
        }
        exact_log($this->projectId, 'conn_check', json_encode($connection));
		if(!$connection){ return false; }
		
		$administrationId = $this->Projects_model->getValue('exactonline_administration_id', $projectId);
		//log_message('debug', 'Project id = ' . $projectId);
		//log_message('debug', 'AdministrationId id = ' . $administrationId);
		if($administrationId != '' && $administrationId != 'default'){
			$connection->setDivision($administrationId);
		}

		return $connection;
	}

	public function getArticles($connection, $itemId = null, $offset = '', $amount = 10, $connectionType = false){
		$this->connection = $connection;
	    $items = new \Picqer\Financials\Exact\Item($connection);
	    $result = array();
	    if($itemId != ''){
		    $result = $items->filter("ID eq guid'".$itemId."'");
	    } else {
		    if($this->Projects_model->getValue('exactonline_article_offset', $this->projectId) != ''){
			    $result = $items->get($amount, "guid'".$this->Projects_model->getValue('exactonline_article_offset', $this->projectId)."'");
		    } else {
			    $result = $items->get($amount);
		    }

			if(count($result) == 0){
				if($this->projectId != 2){ // niet voor werkkleding.nl
					$this->Projects_model->saveValue('exactonline_import_all_products', '0', $this->projectId);
				}
				$this->Projects_model->saveValue('exactonline_article_offset', '', $this->projectId);
				return array();
			} else {
				// Update skip value
				$lastItem = end($result);
				$this->Projects_model->saveValue('exactonline_article_offset', $lastItem->ID, $this->projectId);
			}
	    }
		$finalResult = array();
		
	    if(!empty($result)){
		    foreach($result as $item){
				$finalArticleData = array();
				$itemData = $item->attributes();
				if ($this->projectId == 79) {
					//log_message('debug', 'shop itemData ' . var_export($itemData, true));
				}
			    if(!$itemData['IsWebshopItem']){
				    continue;
				}
			    if(!$itemData['IsSalesItem']){
					$finalArticleData['available_for_view'] = 'False';
					$finalArticleData['available_for_sale'] = 'False';
				}
				if($itemData['PictureUrl'] != '' && $itemData['PictureUrl'] != 'https://start.exactonline.nl//docs/images/placeholder_item.png'){
					$imageData = $connection->getImage($itemData['PictureUrl']);
					$imageName = $itemData['PictureName'];
					$imageLocation = save_image_string($this->projectId, $imageName, $imageData, false);
					$finalArticleData['image'] = $imageLocation;
				}
				
				if ($this->projectId == 79){
				    if ($itemData['Class_02'] != ''){
				        $finalArticleData['suppliercodetx'] = $itemData['Class_02'];
				    }
				    if ($itemData['ItemGroupDescription'] != ''){
				        if ($itemData['ItemGroupDescription'] == "Ringen") $finalArticleData['mappedcategory'] = 19;
				        if ($itemData['ItemGroupDescription'] == "Oorbellen") $finalArticleData['mappedcategory'] = 18;
				        if ($itemData['ItemGroupDescription'] == "Armbanden") $finalArticleData['mappedcategory'] = 20;
				        if ($itemData['ItemGroupDescription'] == "Colliers") $finalArticleData['mappedcategory'] = 21;
				        if ($itemData['ItemGroupDescription'] == "outlet") $finalArticleData['mappedcategory'] = 56;
				    }
				}
				
				$finalArticleData['model'] = $itemData['Code'];
				$finalArticleData['name'] = $itemData['Description'];
				$finalArticleData['description'] = $itemData['Description'];
				$finalArticleData['tax_class_id'] = $itemData['SalesVatCode'];
				$finalArticleData['id'] = $itemData['ID'];
				$itemPriceObject = new \Picqer\Financials\Exact\SalesItemPrice($connection);
				$itemPriceObject = $itemPriceObject->filter("ItemCode eq '".$itemData['Code']."'", '', '', ['$top'=> 1]);
				$itemPriceData = $itemPriceObject[0]->attributes();
				
//				if ($this->projectId == 79) {
//				    $temp = isset($itemPriceData['Price']) ? str_replace(',', '', $itemPriceData['Price']) : '';
//				    $temp2 = $temp * 1.21;
//				    $temp3 = number_format(round( $temp2 ,2),2) ;
//				    
//				    $finalArticleData['price'] = $temp3;
				    //$finalArticleData['price'] = number_format(round( ((isset($itemPriceData['Price']) ? str_replace(',', '', $itemPriceData['Price']) : '')*1,21),2),2) ;
//				}
//				else {
				    $finalArticleData['price'] = isset($itemPriceData['Price']) ? str_replace(',', '', $itemPriceData['Price']) : '';
//				}
				
				$itemStockObject = new \Picqer\Financials\Exact\SalesItemPrice($connection);
				$itemStockObject = $itemStockObject->filter("ItemCode eq '".$itemData['Code']."'", '', '', ['$top'=> 1]);
				
				if(isset($itemData['Stock'])){
					$finalArticleData['quantity'] = $itemData['Stock'];
				}
				if ($this->projectId == 53) {
					$StockPosition = new \Picqer\Financials\Exact\StockPosition($connection);
					$finalArticleDataID = $finalArticleData['id'];
					$StockPosition = $StockPosition->filter([], '', '', ['itemId' => "guid'{$finalArticleDataID}'"]);
					$StockPosition = $StockPosition[0]->attributes();
					// $StockPosition = $StockPosition->filter("ItemId eq '" . $finalArticleData['id'] . "'");
					$finalArticleData['quantity'] =  $StockPosition['InStock'] + $StockPosition['PlanningIn'] - $StockPosition['PlanningOut'];
				} 
				else if ($this->projectId == 110)
				{
					$StockPosition = new \Picqer\Financials\Exact\StockPosition($connection);
					$finalArticleDataID = $finalArticleData['id'];
					$StockPosition = $StockPosition->filter([], '', '', ['itemId' => "guid'{$finalArticleDataID}'"]);
					$StockPosition = $StockPosition[0]->attributes();
					// $StockPosition = $StockPosition->filter("ItemId eq '" . $finalArticleData['id'] . "'");
					$finalArticleData['quantity'] =  $StockPosition['InStock'] - $StockPosition['PlanningIn'];
				}
				
				

				if(isset($itemData['ItemGroupDescription']) && $itemData['ItemGroupDescription'] != '' && $connectionType != 2){
					$categoryName = $itemData['ItemGroupDescription'];
					// if($categoryName != ''){
					// 	$storeCategory = $this->Cms_model->findCategory($this->projectId, $categoryName);
					// 	$categoryId = false;
					// 	if($storeCategory && isset($storeCategory->category) && !empty($storeCategory->category)){
					// 		$storeCategory = $storeCategory->category;
					// 		$categoryId = $storeCategory[0]->id;
					// 	} elseif(!$storeCategory){
					// 		// Create category
					// 		$category = $this->Cms_model->createCategory($this->projectId, $categoryName);
					// 		if(isset($category->category_id) && $category->category_id > 0){
					// 			$categoryId = $category->category_id;
					// 		}
					// 		sleep(1);
					// 	}
					// 	if($categoryId){
					// 		$finalArticleData['categories_ids'] = $categoryId;
					// 	}
					// }

                    //Added fields for Optiply
                    if(isset($itemData['Created'])) {
                        $finalArticleData['created'] = $itemData['Created'];
                    }

                    if(isset($itemData['Modified'])) {
                        $finalArticleData['modified'] = $itemData['Modified'];
                    }

					if($categoryName != ''){
						$categoryId = $this->Cms_model->findCategory($this->projectId, $categoryName);
						//log_message('debug', var_export($categoryId, true));
						if(!$categoryId){
							//log_message('debug', 'before create category');
							$categoryId = $this->Cms_model->createCategory($this->projectId, $categoryName);
							//log_message('debug', 'after create category' . var_export($categoryId, true));
							sleep(1);
						}
						if($categoryId){
							$finalArticleData['categories_ids'] = $categoryId;
						}
					}
				}

				// Load mapped attributes data
				$finalArticleData = $this->Cms_model->applyMappedAttributes($this->projectId, $itemData, $finalArticleData);

				$projectModel = 'Project'.$this->projectId.'_model';
				if(file_exists(APPPATH."models/".$projectModel.".php")){
					$this->load->model($projectModel);
					if(method_exists($this->$projectModel, 'loadCustomArticleAttributes')){
						$appendItem = $this->$projectModel->loadCustomArticleAttributes($this->projectId);
					}
				}
				$finalResult[] = $finalArticleData;
				if ($this->projectId == 79) {
					//log_message('debug', 'shop finalArticleData ' . var_export($finalArticleData, true));
				}
		    }
	    }
		return $finalResult;
	}

	public function getExactUpdatedArticle($connection, $offset = '', $amount=60, $itemCode='', $lastUpdateDate=''){

		$this->connection = $connection;
	    $items = new \Picqer\Financials\Exact\Item($connection);
	    $result = array();
	    if($itemCode !=''){
		    $result = $items->filter("Code eq '".$itemCode."'");
	    } else {
    		if($offset!=''){
	    		$offset = "guid'".$offset."'";
	    		$datetime = "datetime'".$lastUpdateDate."'";
		      	$result = $items->filter("Modified gt ". $datetime. " and IsWebshopItem eq 1", '', '', null, $amount, $offset);
	    	} else {
	    		$datetime = "datetime'".$lastUpdateDate."'";
		        $result = $items->filter("Modified gt ".$datetime. " and IsWebshopItem eq 1", '', '', null, $amount);
		    }
	    }
	    $finalResult = array();
	    if(!empty($result)){
		    foreach($result as $item){
				$finalArticleData = array();
			  	$itemData 		  = $item->attributes();
			  	if($itemData['IsWebshopItem']!=1)
			  		continue;
				$finalArticleData['Id'] 					= $itemData['ID'];
				$finalResult[] = $finalArticleData;
		    }
	    }
		return $finalResult;
	}

	public function sendOrder($connection, $projectId, $orderData){
		$this->connection = $connection;	
		
		if (is_object($orderData)) {
			$orderData = $this->object2array($orderData);
		}
		
		//log_message('debug', 'order info  after' .$projectId .'  / ' . var_export($orderData, true));
		
		if(!is_array($orderData)) {
			$orderData = $this->xml2array($orderData);

			$billingData = $this->xml2array($orderData['billing_address']);
			$billingData = $billingData[0];
			$customerData = $this->xml2array($orderData['customer']);
			$customerData = $customerData[0];
		} else {
			$billingData = $orderData['billing_address'];
			$customerData = $orderData['customer'];
		}
		if($projectId == 58 || $projectId == 54){
			$billingData = $orderData['billing_address'];
			$customerData = $orderData['customer'];

		}
		$customerData = array_merge($customerData, $billingData);
		$orderDate = '';
		if(!empty($orderData['status']) && isset($orderData['status'][0]->history[0])){
			$orderDate = explode(' ', $orderData['status'][0]->history[0]->history[0]->modified_time);
			$orderDate = $orderDate[0];
		}
		if($orderDate == ''){
			$orderDate = $orderData['create_at'];
		}

		if(!$debtorId = $this->checkExactCustomerExists($projectId, $customerData)){
			return false;
		}

		//if ($projectId == 53) {
		//	log_message('debug', 'Project id = ' . $projectId . 'СustomerData = ' . var_export($customerData, true));
		//	log_message('debug', 'Project id = ' . $projectId . ' debtorId = ' . $debtorId);
		//}

		$order = new \Picqer\Financials\Exact\SalesOrder($connection);
		$order = $order->filter("Description eq '".$orderData['order_id']."'");
		if(!empty($order)){
			return false;
		}

		$order = new \Picqer\Financials\Exact\SalesOrder($connection);
		$order->DeliverTo = $debtorId;
		$order->Description = $orderData['order_id'];
		$order->InvoiceTo = $debtorId;
		$order->OrderDate = $orderDate;
		$order->OrderedBy = $debtorId;
//		$order->OrderNumber = $orderData['order_id'];
		$comment = $orderData['status'][0]->history[0]->history[0]->comment;
		if($comment != ''){
			$order->Remarks = $comment;
		}

		$warehouses = new \Picqer\Financials\Exact\Warehouse($connection);
		$warehouses = $warehouses->get();
		if(!empty($warehouses)){
			$warehouse = $warehouses[0];
			
			//log_message('debug', 'Project 34 Warehouse: ' . var_export($warehouses, true));
			
			if($projectId == 34) {
			    foreach ($warehouses as $WID) {
			        //log_message('debug', 'Project 34 Warehouse: ' . var_export($WID, true));
			        
			    }
			    $warehouse = '';
			}
			$order->WarehouseID = $warehouse->ID;
		}

		// Delivery address
		if($projectId == 53){
			$deliveryAddress = $this->addDeliveryAddress($orderData, $debtorId, $projectId);
			if($deliveryAddress != false){
				$order->DeliveryAddress = $deliveryAddress;
			}
		}

		$orderLines = array();
		if(!is_array($orderData)) {
			$products = $this->xml2array($orderData);
			$products = $this->xml2array($products['order_products'][0]->product);
		} else {
			$products = $orderData['order_products'];
		}

		foreach($products as $item){
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'setOrderProductParamsBefore')){
					$item = $this->$projectModel->setOrderProductParamsBefore($item);
				}
			}
			
			$product = new \Picqer\Financials\Exact\Item($connection);
			$product = $product->filter("Code eq '".$item['model']."'");
			if(empty($product) && $projectId == 121) {
				$this->sendSalesEntry($connection, $projectId, $orderData);
				continue;
			}
			if(empty($product)){
				continue;
			}

			$product = new \Picqer\Financials\Exact\Item($connection);
			$product = $product->filter("Code eq '".$item['model']."'");

			$productAttributes = $product[0]->attributes();
			$market_place = $this->Projects_model->getValue('market_place', $projectId);
            if($market_place == 'bol'){
				$default_prices = $item['price']/$item['quantity'];
				$item_prices  	= $item['price'];
			}
			
			//if ($projectId == 54 || $projectId == 58) log_message('error', 'total tax'. var_export($orderData['totals']['tax'], true));
			
			if ($projectId == 54 || $projectId == 58) {
			    if ($orderData['totals']['tax'] <= 0) {
    			    $orderLine = array(
    				'Description' => $productAttributes['Description'],
    				'Item' => $product[0]->ID,
    				//'UnitPrice' => $item['price'],
    				'NetPrice' => $item['price'],
    				'Quantity' => $item['quantity'],
    				'VATCode' => '7'
    			);
                }
                else {
                    $orderLine = array(
    				'Description' => $productAttributes['Description'],
    				'Item' => $product[0]->ID,
    				'UnitPrice' => $item['price'],
    				'Quantity' => $item['quantity']
    				);
                }
			}
			else {
    			$orderLine = array(
    				'Description' => $productAttributes['Description'],
    				'Item' => $product[0]->ID,
    				'UnitPrice' => $item['price'],
    				'Quantity' => $item['quantity']
    			);
			}

			if (!empty($item_prices)) {
				$orderLine['NetPrice'] = $item_prices;
			}
			
			// Load project specific data
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'setOrderProductParams')){
					$orderLine = $this->$projectModel->setOrderProductParams($orderLine, $item);
				}
			}
			
			//if ($projectId == 54 || $projectId == 58) log_message('error', 'order line '. var_export($orderLine, true));
			$orderLines[] = $orderLine;
		}
		
		$totals = $orderData['totals'];
		if(isset($totals['shipping']) && $this->Projects_model->getValue('afas_shipping_sku', $projectId) != ''){
			$shippingAmount = $totals['shipping'] ? $totals['shipping'] : 0;
			if($shippingAmount != 0){
				if ( ($projectId == 54 || $projectId == 58) && ($orderData['totals']['tax'] <= 0) )
				{
				    $orderLine = array(
    					'Description' => 'Verzendkosten',
    					'Item' => $this->Projects_model->getValue('afas_shipping_sku', $projectId),
    					//'UnitPrice' => $shippingAmount,
    					'NetPrice' => $shippingAmount,
    					'Quantity' => 1,
    					'VATCode' => '7'
    				);
				}
				else
				{
    				$orderLine = array(
    					'Description' => 'Verzendkosten',
    					'Item' => $this->Projects_model->getValue('afas_shipping_sku', $projectId),
    					'UnitPrice' => $shippingAmount,
    					'Quantity' => 1
    				);
				}
				$orderLines[] = $orderLine;
			}
		}
		//if ($projectId == 58) log_message('debug', 'Shippingcost'. var_export($orderLines, true));
		
		$order->SalesOrderLines = $orderLines;
		
		try{
		    if ($projectId == 68 && ($orderData['status'] != 'canceled' && $orderData['status'] != 'pending_payment' && $orderData['status'] != 'pending') )
		    {
		        //log_message('debug', 'status 68'. var_export($orderData, true));
		        $result = $order->save();
		        api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['order_id'].' to Exact Online.');
		    }
		    else if($projectId != 68)
		    {
				$result = $order->save();
				//if ($projectId == 58) log_message('debug', 'order result exact'. var_export($result, true));
		        api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['order_id'].' to Exact Online.');
		    }
		} catch(Exception $e){
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['order_id'].' to Exact Online. Error: '.$e->getMessage());
			return false;
		}
		return true;
	}

	public function checkExactCustomerExists($projectId, $customerData){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$finalDebtorId = false;
		if ($cms == 'cscart') {
			if($debtorId = $this->checkExactCustomer($projectId, $customerData, 'email')){
				$finalDebtorId = $debtorId;
			}
		} else {
			if($debtorId = $this->checkExactCustomer($projectId, $customerData, 'email')){
				$finalDebtorId = $debtorId;
			} else {
				if($debtorId = $this->checkExactCustomer($projectId, $customerData, 'zipcode_streetnumber')){
					$finalDebtorId = $debtorId;
				} else {
					if($debtorId = $this->checkExactCustomer($projectId, $customerData, 'lastname_firstname')){
						$finalDebtorId = $debtorId;
					}
				}
			}
		}

		if(!$finalDebtorId){
			if($this->createExactCustomer($projectId, $customerData)){
				$finalDebtorId = $this->checkExactCustomer($projectId, $customerData, 'email');
			}
		}
		return $finalDebtorId;
	}

	public function checkExactCustomer($projectId, $customerData, $type){
		$customer = new \Picqer\Financials\Exact\Account($this->connection);
		
		if($type == 'email'){
		    $result = $customer->filter("Email eq '".$customerData['email']."'");
		} elseif($type == 'zipcode_streetnumber'){
		    $result = $customer->filter("City eq '".$customerData['city']."' and AddressLine1 eq '".$customerData['address1']."' and Postcode eq '".$customerData['postcode']."'");
		} elseif($type == 'lastname_firstname'){
		    $result = $customer->filter("Name eq '".$customerData['company']."'");
		} else {
			return false;
		}

		if(empty($result)){
			return false;
		}
		return $result[0]->ID;
	}

	public function createExactCustomer($projectId, $customerData){
	    $cms = $this->Projects_model->getValue('cms', $projectId);
		// Create a new account
		$account = new \Picqer\Financials\Exact\Account($this->connection);
		$account->AddressLine1 = $customerData['address1'];
		$account->AddressLine2 = $customerData['address2'];
		$account->City = $customerData['city'];
		// $account->Country = $customerData['country'][0]->code2;
		if (is_object($customerData['country'][0])) {
			$account->Country = $customerData['country'][0]->code2;
		} else {
			$account->Country = isset($customerData['country']) ? $customerData['country'] : '';
		}
		$account->IsSales = 'true';
		
		
		
		
		if($customerData['company'] != '') {
			if ($cms == 'cscart' && $projectId != 53) {
				$account->Name = $customerData['first_name'].' '.$customerData['last_name'];
			}
			else {
				$account->Name = $customerData['company'];
			}
		} else {
			$account->Name = $customerData['first_name'].' '.$customerData['last_name'];
		}
		
		
		$account->Postcode = $customerData['postcode'];
		$account->Email = $customerData['email'];
		$account->Phone = $customerData['phone'];
		$account->Fax = $customerData['fax'];
		$account->Website = $customerData['website'];
		if(!empty($customerData['state'])){
			$account->State = $customerData['state'][0]->code;
		}
		$account->Status = 'C';
		//if ($projectId == 78) log_message('debug', 'Project 78 Customer '.$projectId.': ' . var_export($account, true));

		try{
			$account->save();
			return true;
		} catch(Exception $e) {
			api2cart_log($projectId, 'exportorders', 'Could not add customer '.$customerData['first_name'].' '.$customerData['last_name'].' to Exact Online. Debug Error: '.$e->getMessage());
		}
		return false;
	}

	public function getDebtors($connection, $projectId, $itemId = null, $offset = '', $amount = 10){
		$this->connection = $connection;
	    $items = new \Picqer\Financials\Exact\Account($connection);

	    if($itemId != ''){
		    $result = $items->filter("IsSales eq true and ID eq guid'".$itemId."'");
	    } else {
		    if($this->Projects_model->getValue('exactonline_customers_offset', $projectId) != ''){
			    $result = $items->filter("IsSales eq true", '', '', null, $amount, "guid'".$this->Projects_model->getValue('exactonline_customers_offset', $projectId)."'");
		    } else {
			    $result = $items->filter("IsSales eq true", '', '', null, $amount);
		    }

			if(count($result) == 0){
				$this->Projects_model->saveValue('exactonline_import_all_customers', '0', $projectId);
				$this->Projects_model->saveValue('exactonline_customers_offset', '', $projectId);
				return array();
			} else {
				// Update skip value
				$lastItem = end($result);
				$this->Projects_model->saveValue('exactonline_customers_offset', $lastItem->ID, $projectId);
			}
	    }

	    $finalResult = array();
	    if(!empty($result)){
		    foreach($result as $item){
			    $itemData = $item->attributes();
			    $finalResult[] = $itemData;
			}
		}
		$data = $finalResult;

		$counter = 0;
		if(count($data) > 0){
			$results = array();
			foreach($data as $customer){
				$counter++;

				$customerName = explode(' ', $customer['Name']);
				$customerFirstName = $customerName[0];
				unset($customerName[0]);
				$customerLastName = implode(' ', $customerName);
				if($customerLastName == ''){
					$customerLastName = ' ';
				}
				$postcode = $customer['Postcode'];
				$city = $customer['City'];
				$country = trim($customer['Country']);
				$email = isset($customer['Email']) ? $customer['Email'] : '';
				$phone = isset($customer['Phone']) ? $customer['Phone'] : '';

				$customerData = array(
					'email' => $email,
					'first_name' => $customerFirstName,
					'last_name' => $customerLastName,
					'address_book_type_1' => 'billing',
					'address_book_first_name_1' => $customerFirstName,
					'address_book_last_name_1' => $customerLastName,
					'address_book_address1_1' => $customer['AddressLine1'],
					'address_book_country_1' => $country,
					'address_book_postcode_1' => $postcode,
				);
				if($phone != ''){
					$customerData['phone'] = $phone;
					$customerData['address_book_phone_1'] = $phone;
				}
				if($city != ''){
					$customerData['address_book_city_1'] = $city;
				}

				$this->Cms_model->createCustomer($projectId, $customerData);
			}
		}
		return $counter;
	}

	public function sendWebhook($connection, $projectId){
		$this->connection = $connection;
		$getWebhook = new \Picqer\Financials\Exact\WebhookSubscription($connection);
		$url 		= site_url().'/exactwebhook/itemsCallbackFromExact';
		$result 	= array();
		$callbackexists = false;
		try{
			$result = $getWebhook->get();
		} catch(Exception $e) {
			return false;
		}
		if(!empty($result)){
		    foreach($result as $webhook){
				$webhookData = $webhook->attributes();
				if($webhookData['CallbackURL']==$url){
					$callbackexists = true;
				}
			}
		}
		if(!$callbackexists){
			$webhook = new \Picqer\Financials\Exact\WebhookSubscription($connection);
			$webhook->CallbackURL = $url;
			$webhook->Topic = 'Items';
			try{
				$result = $webhook->save();
				print_r($result);
				$callbackexists = true;
			} catch(Exception $e) {
				print_r($e);
				return false;
			}
		}
		return $callbackexists;
	}

	public function getSalesOrders($connection, $amount, $offset, $fromDate = '') {

        $orders = new \Picqer\Financials\Exact\SalesOrder($connection);

         $filters = '';
        if($fromDate != '')
            $filters = "Created ge datetime'".$fromDate."'";

        if($offset != '' ) {
            $orders = $orders->filter($filters, '', 'OrderID, Created, DeliveryDate, AmountDC, OrderedByContactPerson', null, $amount, "guid'".$offset."'");
        } else {
            $orders = $orders->filter($filters, '', 'OrderID, Created, DeliveryDate, AmountDC, OrderedByContactPerson', null, $amount);
        }

        $ordersArray = [];

        foreach ($orders as $order) {
            $order = $order->attributes();
            $placed = substr($order['Created'], 6, 10);
            $placed = date('Y-m-d', $placed).'T'.date('H:i:s.Z', $placed).'Z';
            $completed = substr($order['DeliveryDate'], 6, 10);
            $completed = date('Y-m-d', $completed).'T'.date('H:i:s.Z', $completed).'Z';
            $orderArray = [
				'id' => $order['OrderID'],
                'created' => $placed,
                'completed' => $completed,
                'amount' => $order['AmountDC'],
                'orderedBy' => $order['OrderedByContactPerson'],
                'date' => substr($placed, 0, 19)
            ];

			$order = new \Picqer\Financials\Exact\SalesOrder($connection);
			$order = $order->filter("OrderID eq guid'".$orderArray['id']."'");

			if (empty($order))  continue;

			$invoiceStatus = $order[0]->attributes()['InvoiceStatus'];
			$orderArray['status'] = $invoiceStatus;
			
			$this->getSalesOrderLines($connection, $orderArray);

            $ordersArray[] = $orderArray;
        }

        return $ordersArray;
    }

    public function getSalesOrderLines($connection, &$orderArray) {

        $line = new \Picqer\Financials\Exact\SalesOrderLine($connection);
        $orderLines = $line->filter("OrderID eq guid'".$orderArray['id']."'", '', 'AmountDC, Item, ItemDescription, Quantity, UnitPrice, ItemCode');

        foreach ($orderLines as $orderLine) {
            $lineData = $orderLine->attributes();

            $lineArray = [
                'amount' => $lineData['AmountDC'],
                'id' => $lineData['Item'],
                'name' => $lineData['ItemDescription'],
                'quantity' => $lineData['Quantity'],
                'unitPrice' => $lineData['UnitPrice'],
                'code' => $lineData['ItemCode']
            ];

            $lineArray['product'] = $this->getItem($connection, $lineData['Item'], 'id');

            $orderArray['lines'][] = $lineArray;
        }
    }

    public function searchItemBySKU($connection, $item, $itemType = '') {
        $itemObj = new \Picqer\Financials\Exact\Item($connection);
        exact_log($this->projectId, 'search_item_req', json_encode($item));
        $itemData = $itemObj->filter("Code eq '" .$item['skuCode']. "'", '', '', ['$top' => 1]);

        if(isset($itemType)) {
            if($itemData[0]->attributes()[$itemType] != true) {
                return false;
            }
        }

        $itemArr = $itemData[0]->attributes();
        exact_log($this->projectId, 'search_item_resp', json_encode($itemArr));
        if( !empty($itemArr)) {

            $itemData = [
                'CostPriceStandard' => $itemArr['CostPriceStandard'],
                'ID' => $itemArr['ID'],
                'startDate' => $itemArr['StartDate'],
                'endDate' => $itemArr['EndDate'],
            ];
        }

        return $itemData;
    }

    public function getItem($connection, $searchData, $field, $itemType = null, $checkActive = null) {

	    $itemObj = new \Picqer\Financials\Exact\Item($connection);

	    if($field == 'id')
	        $item = $itemObj->find($searchData)->attributes();

	    if($field == 'desc') {
            $itemData = $itemObj->filter("Description eq '" .$searchData. "'", '', '', ['$top' => 1]);

            //Check item type
            if(isset($itemType)) {
                if($itemData[0]->attributes()[$itemType] != true) {
                    return false;
                }
            }

            //Check item is active
            if(isset($checkActive) && $checkActive) {
                $endDate = $itemData[0]->attributes()['EndDate'];
                $end = strtotime($endDate);
                if(isset($endDate) && $end && $end < time()) {
                	return false;
                }
            }

            $item = [];
            $itemArr = $itemData[0]->attributes();
            if( !empty($itemArr)) {

                $item = [
                    'CostPriceStandard' => $itemArr['CostPriceStandard'],
                    'ID' => $itemArr['ID'],
                    'startDate' => $itemArr['StartDate'],
                    'endDate' => $itemArr['EndDate'],
                ];
            }

            return $item;
        }

        $lineArray = [
            'code' => $item['Code'],
            'barcode' => $item['Barcode'],
            'stock' => $item['Stock'],
            'name' => $item['Description'],
            'isSales' => $item['IsSalesItem'],
            'IsPurchaseItem' => $item['IsPurchaseItem'],
            'id' => $searchData,
            'price' => $item['CostPriceStandard'],
            'status' => 'ENABLED'
        ];

        //Check item is active
        if(isset($checkActive) && $checkActive) {
            $endDate = $item['EndDate'];
            $end = strtotime($endDate);
            if(isset($endDate) && $end && $end < time()) {
            	exact_log(72, 'status_dis', $item['EndDate'].','.$end);
                $lineArray['status'] = 'DISABLED';
            }
        }

        return $lineArray;
    }

    public function getSuppliersWithItems($connection, $amount = 10, $offset = '') {

	    $suppliersObject = new \Picqer\Financials\Exact\Account($connection);
		
	    if($offset != '') {
            $suppliers = $suppliersObject->filter('IsSupplier eq true', '', '', null, $amount, "guid'".$offset."'");
        } else {
            $suppliers = $suppliersObject->filter('IsSupplier eq true', '', '', null, $amount);
        }
		exact_log($this->projectId, 'suppliers_response', json_encode($suppliers));
	    $suppliersData = [];
        //log_message('debug', 'Suppliers response: '. json_encode($suppliers));
	    foreach ($suppliers as $supplier) {

	        $fields = $supplier->attributes();
	        //log_message('debug', 'Suppliers data batch: '. json_encode($fields));
	        $email = isset($fields['Email']) ? $fields['Email'] : '';

	        $supplierData = [
	            'id' => $fields['ID'],
                'name' => $fields['Name'],
                'email' => $email
            ];

	        $supplierData['items'] = $this->getSuppliersItems($connection, $fields['ID']);

	        $suppliersData[] = $supplierData;
        }

	    return $suppliersData;
    }

    public function getSuppliersItems($connection, $supplierId) {

        $supplierItemObject = new \Picqer_ext\SupplierItem($connection);

        $items = $supplierItemObject->filter("Supplier eq guid'".$supplierId."'");
        //log_message('debug', 'Suppliers items response: '. json_encode($items));
        exact_log($this->projectId, 'suppliers_items_response', json_encode($items));
        $supplierItems = [];

        foreach ($items as $item) {

            $itemData = $item->attributes();

            $itemArray = [
                'id' => $itemData['Item'],
                'name' => $itemData['ItemDescription'],
                'price' => $itemData['PurchasePrice'],
                'minQuantity' => $itemData['MinimumQuantity'],
                'supplierItemCode' => $itemData['SupplierItemCode'],
                'lotSize' => $itemData['PurchaseUnitFactor']
            ];

            $itemDataAdditional = $this->getItem($connection, $itemData['Item'], 'id', null, true);

            $itemArray['stock'] = $itemDataAdditional['stock'];
            $itemArray['barcode'] = $itemDataAdditional['barcode'];
            $itemArray['priceStandart'] = $this->getSalesPrice($connection, $itemData['Item']);
            $itemArray['code'] = $itemDataAdditional['code'];
            $itemArray['status'] = $itemDataAdditional['status'];

            $supplierItems[] = $itemArray;
        }

        return $supplierItems;
    }

    public function getSalesPrice($connection, $itemId) {
        $itemPriceObject = new \Picqer\Financials\Exact\SalesItemPrice($connection);
        $itemPriceObject = $itemPriceObject->filter("Item eq  guid'".$itemId."'", '', '', ['$top'=> 1]);
        $itemPriceData = $itemPriceObject[0]->attributes();

        if(isset($itemPriceData['Price']))
            return $itemPriceData['Price'];

        return null;
    }

    public function getSupplierItemById($connection, $itemId) {
        
        $supplierItemObject = new \Picqer_ext\SupplierItem($connection);

        $item = $supplierItemObject->filter("Item eq guid'" . $itemId . "'", '', '', ['$top' => 1]);
        exact_log('0', 'item_debug', json_encode($item));

        if(empty($item)) {
        	exact_log('0', 'item_debug_empty', json_encode($item));
        	return [];
        }
        
        $itemData = $item[0]->attributes();
        exact_log('0', 'item_debug2', json_encode($itemData));       

        $itemArray = [
            'id' => $itemData['Item'],
            'name' => $itemData['ItemDescription'],
            'price' => $itemData['PurchasePrice'],
            'minQuantity' => $itemData['MinimumQuantity'],
            'supplierItemCode' => $itemData['SupplierItemCode'],
            'lotSize' => $itemData['PurchaseUnitFactor']
        ];

        return $itemArray;
    }

    public function getBuyOrderData($connection, $amount, $offset, $onlyActive = '0') {

        $orders = $this->getBuyOrders($connection, $amount, $offset, $onlyActive);

        return $orders;
    }

    public function getBuyOrders($connection, $amount = 10, $offset = '', $onlyActive) {

        $orderModel = new \Picqer_ext\PurchaseOrder($connection);

        $lastDate = $this->Projects_model->getValue('buyorders_last_date', $this->projectId);

        $filters = '';
        if($onlyActive != '0' && $offset != '') {
            $filters = "OrderStatus eq 10";
        } elseif ($onlyActive != '0' && $offset == '' && $lastDate != '') {
            $filters = "OrderStatus eq 10 and Created ge datetime'".$lastDate."'";
        } elseif ($onlyActive == '0' && $offset == '' && $lastDate != '') {
            $filters = "Created ge datetime'".$lastDate."'";
        }

        if($offset != '') {
            $orders = $orderModel->filter($filters, '', 'PurchaseOrderID, AmountDC, Created, PurchaseOrderLines, Supplier, SupplierName, ReceiptDate, InvoiceStatus', null, $amount, "guid'".$offset."'");
        } else {
            $orders = $orderModel->filter($filters, '', 'PurchaseOrderID, AmountDC, Created, PurchaseOrderLines, Supplier, SupplierName, ReceiptDate, InvoiceStatus', null, $amount);
		}
		
        exact_log($this->projectId, 'orders_resp', json_encode($orders));
        $ordersArray = [];
        foreach ($orders as $order) {
            $orderData = $order->attributes();

            $placed = substr($orderData['Created'], 6, 10);
            $placed = date('Y-m-d', $placed).'T'.date('H:i:s.Z', $placed).'Z';
            $completed = substr($orderData['ReceiptDate'], 6, 10);
            $completed = date('Y-m-d', $completed).'T'.date('H:i:s.Z', $completed).'Z';
            
            // if(isset($orderData['OrderStatus']) && $orderData['OrderStatus'] != 10) {
            // 	exact_log('0', 'skip_buy_order', json_encode($orderData));
            // 	continue;
			// }

            $orderArray = [
                'id' => $orderData['PurchaseOrderID'],
				'amount' => $orderData['AmountDC'],
				'created' => $placed,
				'completed' => $completed,
				'name' => $orderData['SupplierName'],
				'suplierId' => $orderData['Supplier'],
				'date' => substr($placed, 0, 19),
				'status' => $orderData['InvoiceStatus']
			];
			
            $lineObject = new \Picqer_ext\PurchaseOrderLine($connection);
            $linesData = $lineObject->filter("PurchaseOrderID eq guid'".$orderData['PurchaseOrderID']."'", '',
                'ID, AmountDC, Item, InStock, Quantity, Created, ItemCode, ItemDescription');

            foreach ($linesData as $line) {
                $lineArray = $line->attributes();
                exact_log($this->projectId, 'lines', json_encode($lineArray));
                $orderArray['lines'][] = [
                    'id' => $lineArray['Item'],
                    'stock' => $lineArray['InStock'],
                    'amount' => $lineArray['AmountDC'],
                    'quantity' => $lineArray['Quantity'],
                    'created' => $lineArray['Created'],
                    'code' => $lineArray['ItemCode'],
                    'name' => $lineArray['ItemDescription'],
                    'line_id' => $lineArray['ID'],
                ];
            }

            $ordersArray[] = $orderArray;
        }

        return $ordersArray;
    }

    public function getSupplierByItem($connection, $id)
    {
        $supplierItemObject = new \Picqer_ext\SupplierItem($connection);
        $item = $supplierItemObject->filter("Item eq guid'".$id."'", '', 'Supplier', ['$top'=> 1]);

        if(!empty($item[0]) ) {
            $supplierId = $item[0]->attributes()['Supplier'];
            exact_log('0', 'get_sup_id', $supplierId);

            $supplierObject = new \Picqer\Financials\Exact\Account($connection);
            $supplier = $supplierObject->find($supplierId)->attributes();
            exact_log('0', 'get_sup_name', json_encode($supplier));

            return $supplier;
        }

        return false;
    }

    public function getSupplierIdByItem($connection, $id)
    {
        $supplierItemObject = new \Picqer_ext\SupplierItem($connection);
        $item = $supplierItemObject->filter("Item eq guid'".$id."'", '', 'Supplier', ['$top'=> 1]);

        if(!empty($item[0]) ) {
            $supplierId = $item[0]->attributes()['Supplier'];

            return $supplierId;
        }

        return false;
    }

    public function getBuyOrder($connection, $id) {
        $orderModel = new \Picqer_ext\PurchaseOrder($connection);
		$orders = $orderModel->find($id)->attributes();
        $created = substr($orders['Created'], 6, 10);
        $created = date('Y-m-d', $created).'T'.date('H:i:s.Z', $created).'Z';
		
        $orderArray = [
            'amount' => $orders['AmountDC'],
            'created' => $orders['Created'],
            'completed' => $orders['ReceiptDate'],
            'name' => $orders['SupplierName'],
            'suplierId' => $orders['Supplier'],
            'status' => $orders['InvoiceStatus'],
            'date' => $created,
            'supplier' => $orders['SupplierName'],
        ];

        $lineObject = new \Picqer_ext\PurchaseOrderLine($connection);
        $lineData = $lineObject->filter("PurchaseOrderID eq guid'".$id."'", '', 'ID, AmountDC, Item, InStock, Quantity, Created, ItemCode');

        foreach ($lineData as $line) {

            $lineArray = $line->attributes();

            $orderArray['lines'][] = [
                'id' => $lineArray['Item'],
                'stock' => $lineArray['InStock'],
                'amount' => $lineArray['AmountDC'],
                'quantity' => $lineArray['Quantity'],
                'created' => $lineArray['Created'],
                'code' => $lineArray['ItemCode'],
                'lineId' => $lineArray['ID'],
            ];

        }

        return $orderArray;
    }

    public function updateBuyOrders($connection, $orders, $projectId) {

	    $existedOrders = [];
        foreach ($orders as $order) {

            if($this->checkOrderExists($order['id'], $projectId)) {
                $existedOrders[] = $order['id'];
                continue;
            }

            $lines = [];

            $warehouseId = $this->getWarehouseId($connection, $order['warehouse']['id']);
            $line = [];
            foreach ($order['lines'] as $line) {
                $item = $this->searchItemBySKU($connection, $line['item'], 'IsPurchaseItem');
                $itemId = isset($item['ID']) ? $item['ID'] : false;

                if(!$itemId) {
                    exact_log($this->projectId, 'create_item', json_encode($line));
                    $itemId = $this->createItem($connection, $line, $order['placed']);
                }

                $supplierId = $this->getSupplierIdByItem($connection, $itemId);
                if(!$supplierId){
                    $supplierId = $this->createSupplier($connection, $order['supplier']);
                }

                $this->linkItemWarhouse($connection, $itemId, $warehouseId);
                $this->linkItemSupplier($connection, $itemId, $line['item']['price'], $supplierId);

                $line = $this->fillPurchaseOrderLine($connection, $line, $itemId);

                $lines[] = $line;
            }
            if(count($lines) < 1) {
                continue;
            }

            $orderId = $this->createPurchaseOrder($connection, $order, $supplierId, $lines, $warehouseId);
            api2cart_log($projectId, 'exact_buy_orders', 'Order '.$orderId.' imported to Exact');
            $data = [
                'project_id' => $projectId,
                'optiply_id' => $order['id'],
                'order_id' => $orderId,
                'date' => date("Y-m-d H:i:s")
            ];
            $this->db->insert('optiply_orders', $data);
	    }
        exact_log($projectId, 'order_ex_ex', json_encode($existedOrders));
    }

    public function createItem($connection, $data, $placed) {
        $item = new \Picqer\Financials\Exact\Item($connection);

        $item->Code = $data['item']['skuCode'];
        $item->CostPriceStandard = $data['item']['price'];
        $item->Description = $data['item']['name'];
        $item->IsPurchaseItem = true;
        $item->StartDate = $placed;

        try {
            $item->save();
            $item = $item->attributes();

        } catch (Exception $e) {
            exact_log($this->projectId, 'item_search2', json_encode($data));
            $item = $this->getItem($connection, $data['item']['name'], 'desc', 'IsPurchaseItem', true);
//              TODO: update item
//            if(isset($item['startDate'])) {
//                $placed = strtotime($placed);
//                $start = substr($item['startDate'], 0, 9);
//                if($start > $placed) {
//                    $this->updateItem($connection, $item['ID']);
//                }
//            }
        }
        return $item['ID'];
    }

    public function linkItemWarhouse($connection, $item, $warehouse) {
	    $iwObject = new \Picqer_ext\ItemWarehouse($connection);

	    $iwObject->Item = $item;
	    $iwObject->Warehouse = $warehouse;
	    try {
            $iwObject->save();
        } catch (Exception $e) {
	        return false;
        }
    }

    public function linkItemSupplier($connection, $itemId, $price, $supplier) {
        $siObject = new \Picqer_ext\SupplierItem($connection);

        //$siObject->ID = $item['ID'];
        $siObject->Item = $itemId;
        $siObject->PurchasePrice = $price;
        $siObject->PurchaseUnit = '';
        $siObject->Supplier = $supplier;
        try {
            $siObject->save();
        } catch (Exception $e) {
            return false;
        }
    }

    public function fillPurchaseOrderLine($connection, $data, $itemId) {

	    $orderLine = new \Picqer_ext\PurchaseOrderLine($connection);

	    $orderLine->Item = $itemId;
	    $orderLine->QuantityInPurchaseUnits = $data['quantity'];
	    $orderLine->UnitPrice = $data['item']['price'];

	    return $orderLine;
    }

    public function createPurchaseOrder($connection, $data, $supplier, $lines, $warehouseId) {
        $this->load->helper('file');
        $order = new \Picqer_ext\PurchaseOrder($connection);

        $placed = substr($data['placed'], 0, strpos($data['placed'], 'T'));
        $order->Supplier = $supplier;
        $order->OrderDate = $placed;
        $order->PurchaseOrderLines = $lines;
        $order->Warehouse = $warehouseId;

        try {
            $order->save();
        } catch (Exception $e) {
            exact_log(72, 'create_pur_order', json_encode($order->attributes()));
            exact_log(72, 'create_pur_order_err', json_encode($e->getMessage()));
            return false;
        }
        return $order->attributes()['PurchaseOrderID'];
    }

    public function createSupplier($connection, $data) {

        $account = new \Picqer\Financials\Exact\Account($connection);

        $account->Name = $data['name'];
        $account->IsSupplier = true;
        try {
            $account->save();
        } catch (Exception $e) {
            return false;
        }

        return $account->attributes()['ID'];
    }

	
    public function getWarehouseId($connection, $code) {
	    $warehouses = new \Picqer_ext\Werehouse($connection);
		$warehouses = $warehouses->get();
		
		if(!empty($warehouses)){
			$warehouse = $warehouses[0];
			return $warehouse->attributes()['ID'];
		}
		
		return 1;
		
        //$warehouse->Code = $code;
        //$warehouse->Description = 'default warehouse';
        //try {
        //    $warehouse->save();
        //} catch (Exception $e) {
        //    $warehouse = new \Picqer_ext\Werehouse($connection);
        //    $warData = $warehouse->get()[0]->attributes();
        //    return $warData['ID'];
        //}

        //return $warehouse->attributes()['ID'];
    }

    public function subscribeToWebhook($connection, $action, $topic) {
        $this->load->helper('url');
        $callbackUrl = site_url($action.'?project_id='.$this->projectId);
        $webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
            'CallbackURL' => $callbackUrl.$action.'?project_id='.$this->projectId,
            //'CallbackURL' => 'https://f48d5320.ngrok.io/index.php'.$action.'?project_id='.$this->projectId,
            'Topic' => $topic
        ));
        try{
            $res = $webHook->save();
            exact_log(72, 'webhook_subscribed', $topic);
        } catch(Picqer\Financials\Exact\ApiException $e){
            exact_log(72, 'webhook_stock_err', $e->getMessage().':'.$topic);
        }
    }

    public function getSupplier($connection, $id) {

        $account = new \Picqer\Financials\Exact\Account($connection);
        $data = $account->find($id)->attributes();

        return $data;
    }

    public function getSalesOrder($connection, $id) {

        $orderObj = new \Picqer\Financials\Exact\SalesOrder($connection);
        $order = $orderObj->find($id)->attributes();

        $orderArray = [
            'id' => $order['OrderID'],
            'created' => $order['Created'],
            'completed' => $order['DeliveryDate'],
            'amount' => $order['AmountDC'],
            'orderedBy' => $order['OrderedByContactPerson']
        ];

        $this->getSalesOrderLines($connection, $orderArray);

        return $orderArray;
    }

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
	
	public function object2array($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->object2array($value);
			}
			return $result;
		}
		return $data;
	}
	###############################################################################################################
	# 		       Function is used to import orders from Bol.com as salesinvoices in exactonline 				  #
	###############################################################################################################
	public function sendInvoice($connection, $projectId, $orderData){
		// if ($projectId == 78) { 
		// 	log_message('debug', 'Order data info Bol.com' . var_export($orderData, true));
		// }
		//if ($projectId == 78) { 
			//log_message('debug', 'Invoice order data info ' . var_export($orderData, true));
		//}
		$this->load->model('Projects_model');
		$this->connection 	= $connection;
		$billing 		= $orderData['billing_address'];
		$shipping		= $orderData['shipping_address'];
		//log_message('debug', 'shipping adress ' . var_export($billing, true));
		//log_message('debug', 'billing adress ' . var_export($shipping, true));
		$exportOrders 	= $this->Projects_model->getValue('send_orders_as', $projectId);
 		$orderDate 		= $orderData['create_at'];
		$check_order 	= $this->checkImportedOrderInvoice($orderData['order_id'], 'invoices');
		if($check_order){
			$message = " invoice id ".$orderData['order_id'].' alreday imported';
			return ['status'=>0, 'message'=>$message];
		} 
		$customerInfo = isset($orderData['customer']) ? $orderData['customer'] : '';
		// $customerInfo = array_merge($customerInfo, $customerData);
		$shippingCustomerInfo = array_merge($customerInfo, $shipping);
		$billingCustomerInfo  = array_merge($customerInfo, $billing);
		//$customerInfo = array_merge($customerInfo, $customerData);

 		if(!$debtorId = $this->checkExactCustomerExists($projectId, $billingCustomerInfo)){
			$message = " unable to import or find customer ".$customerInfo['email'];
			project_error_log($projectId, 'importInvoices'," Failed : ".$message);
			return ['status'=>0, 'message'=>$message];
		} 

	 	if(!$DeliverTo = $this->checkExactDeliverToCustomerExists($projectId, $shippingCustomerInfo)){
			$message = " unable to import or find customer DeliverTo :".$customerInfo['first_name'].' '.$customerInfo['last_name'];
			project_error_log($projectId, 'importInvoices'," Failed : ".$message);
			return ['status'=>0, 'message'=>$message];
		}
		$invoice = new \Picqer\Financials\Exact\SalesInvoice($connection);
		// log_message('debug', 'Invoice all info before' . var_export($invoice, true));
		$invoice->DeliverTo 	= $DeliverTo; //'73b7ce84-4c9b-4f61-bf1d-c73697226e69';
		$invoice->Description   = $orderData['order_id'];
		$invoice->InvoiceTo 	= $debtorId; //'d123513b-e377-4ac9-a5ca-114c88f39748';
		$invoice->OrderDate 	= $orderDate;

		if ($projectId == 78) {
			$invoice->PaymentCondition = 33;
		}

		if($orderData['end_time']!='' && $orderData['create_at']!=''){
			$invoice->StartTime 	= $orderData['create_at'];
			$invoice->EndTime 		= $orderData['end_time'];
		}

		$invoice->OrderedBy 	        = $debtorId;
		$invoice->YourRef 				= $orderData['order_id'];

		$orderLines = array();
		$products   = $orderData['order_products'];
		$market_place = $this->Projects_model->getValue('market_place', $projectId);
		// log_message('debug', 'Invoice all info ' . var_export($invoice, true));
		foreach($products as $item){
			$product = new \Picqer\Financials\Exact\Item($connection);
			if ($market_place == 'bol') {
				$product = $product->filter("Barcode eq '".$item['model']."'");
			} else {
				$product = $product->filter("Code eq '".$item['model']."'");
			}
			//if ($projectId == 78) { 
			//	log_message('debug', 'product product' . $item['model']);
			//	log_message('debug', 'product info' . var_export($product, true));
			//}
		   if(empty($product)){
			   $total_invoice_import_error = $this->Projects_model->getValue('total_invoice_import_error', $projectId)?$this->Projects_model->getValue('total_invoice_import_error', $projectId):0;
			   $total_invoice_import_error = $total_invoice_import_error +1;
			   $this->Projects_model->saveValue('total_invoice_import_error', $total_invoice_import_error, $projectId);
			   $message = 'Could not find product , SKU : '.$item['model'].' Invoice id :'.$orderData['order_id'].' to Exact Online. Error: ';
				 project_error_log($projectId, 'importInvoices'," Failed : ".$message);
			   continue;
		   }

		   $productAttributes = $product[0]->attributes();

		   $market_place = $this->Projects_model->getValue('market_place', $projectId);

            if($market_place == 'bol'){
				$default_prices = $item['total_price']/$item['quantity'];
				$item_prices  	= $item['total_price'];
			}
			if ($projectId == 78) { 
				//log_message('debug', "Product info ". var_export($item, true));
			}
			$orderLine = array(
				'Description' => $productAttributes['Description'],
				'Item' => $product[0]->ID,
				'UnitPrice' 	=> $default_prices,
				'Quantity' => $item['quantity'],
				'NetPrice' 	=> $default_prices,//$item_prices
			);

			if($market_place == 'bol'){ 
				$orderLine['VATCode'] = 4;
			}
			//if ($projectId == 78) { 
			//	log_message('debug', 'Product info Bol.com' . var_export($orderLine, true));
			//}
		   $orderLines[] = $orderLine; 
		   if (isset($item['fulfilment_method'])) {
				$fulfilment_method = $item['fulfilment_method'];
		   }
	   }

		$invoice->SalesInvoiceLines = $orderLines;
		if ($exportOrders == "direct-sales-invoice") {
			$invoice->Type = 8023;
		}
		$warehouses = new \Picqer\Financials\Exact\Warehouse($connection);
		$warehouses = $warehouses->get();
		if(!empty($warehouses)){
			if ($projectId == 78) {
				foreach ($warehouses as $warehouse) {
					if ($fulfilment_method == 'FBB') {
						if ($warehouse->Description == 'BOL.COM') {
							$warehouseId = $warehouse->ID;
						}
					}

					if ($fulfilment_method == 'FBR') {
						if ($warehouse->Description == 'HELMOND') {
							$warehouseId = $warehouse->ID;
						}
					}
				}

				if (empty($warehouseId)) {
					$warehouseId = $warehouses[0]->ID;
				}
				$invoice->Warehouse = $warehouseId;
			} else {
				$warehouse = $warehouses[0];
				$invoice->Warehouse = $warehouse->ID;
			}
		}
		// log_message('debug', 'Products ALL info Bol.com' . var_export($invoice, true));
		// log_message('debug', 'Products VATCode Bol.com' . var_export($invoice->VATCode, true));

		try{
			$result  = $invoice->save();
			$message = 'Exported invoice '.$orderData['order_id'].' to Exact Online.';
			api2cart_log($projectId, 'exportorders', $message);
			return ['status'=>1, 'message'=>$message];
		} catch(Exception $e){
			$message  = 'Could not export invoice '.$orderData['order_id'].' to Exact Online. Error: '.$e->getMessage();
			api2cart_log($projectId, 'exportorders', $message);
			return ['status'=>0, 'message'=>$message];
		}
		return ['status'=>2];
	}

	#############################################################################################################
	# 		 Function is used to check if the customer exist in exact or not call exact api to check			#
	#############################################################################################################
	public function checkExactCustomerForBol($projectId, $customerData, $type){
	    $customer = new \Picqer\Financials\Exact\Account($this->connection);

		if($type == 'email'){
		    $result = $customer->filter("Email eq '".$customerData['email']."'");
		} elseif($type == 'lastname_firstname'){
			if($customerData['company'] != ''){
				$Name 		= $customerData['company'];
			} else {
				$Name 		= $customerData['first_name'].' '.$customerData['last_name'];
			}

			$Name       =  str_replace("'","`",$Name);
//"' and AddressLine1 eq '".$customerData['address1']
			$result = $customer->filter(' City eq \''.str_replace("'","`",$customerData['city']).'\' and Postcode eq \''.trim(str_replace("'","`",$customerData['postcode'])).'\' and Name eq \''.$Name.'\'');
 		} else {
			return false;
		}

		if(empty($result)){
			return false;
		}
		return $result[0]->ID;
	}

	##############################################################################################################
	# 		 Function is used to check if the orders or invoice is already imported or not in exactonline 		 #
	##############################################################################################################
	public function checkImportedOrderInvoice($ordedId, $option='orders'){
		if($option=='orders')
			$product = new \Picqer\Financials\Exact\SalesOrder($this->connection);
		else
			$product = new \Picqer\Financials\Exact\SalesInvoice($this->connection);
		$product = $product->filter("YourRef eq '".$ordedId."'");

		if(!empty($product)){
			return true;
		}
		return false;
	}

	#############################################################################################################
	#   Function is used to check if the customer exist in exact or not if not let it cretae in exactonline		#
	#############################################################################################################
	public function checkExactDeliverToCustomerExists($projectId, $deliverToCustomer){
		$DeliverTo 			= false;
		if($debtorId 		= $this->checkExactCustomerForBol($projectId, $deliverToCustomer, 'lastname_firstname')){
			$DeliverTo  	= $debtorId;
		}
		if(!$DeliverTo){
			if($this->createExactCustomer($projectId, $deliverToCustomer)){
				$DeliverTo = $this->checkExactCustomerForBol($projectId, $deliverToCustomer, 'lastname_firstname');
			}
		}
		return $DeliverTo;
	}

	/**
	*	Get order data to find an order into Optiply
	*/
	public function getBuyOrderSearchData($connection, $projectId, $orderId) {

        $order = $this->getBuyOrder($connection, $orderId);

        exact_log($projectId, 'get_order_status', json_encode($order));

        exact_log($projectId, 'get_order_status_err', json_encode($order));
        return false;
    }
    
    public function getOpenedBuyOrders($connection) {

	    $orders = $this->getBuyOrderData($connection, 50, '', '1');

	    if(count($orders) >= 50) {

            $end = end($orders);
            $offset = $end['id'];

            $ordersRes = $this->getBuyOrderData($connection, 50, $offset, '1');

            if(!empty($ordersRes)) {
                $orders = array_merge($ordersRes, $orders);
            }
        }

	    return $orders;
    }
    
    public function getArticleExtraField($connection, $projectId, $itemId = ''){
		$this->connection = $connection;
//	    $items = new \Picqer\Financials\Exact\Item($connection);
	    $items = new \Picqer\Financials\Exact\ItemExtraField($connection);
	    $itemId = '{c0b87341-9527-468f-b5d1-fda6e74b5e56}';
		$result = $items->filter("ItemID eq guid'".$itemId."'");
	    //$result = $items->get(5);
	    echo '<pre>';print_r($result);exit;
	}
	
	public function sendSalesEntry($connection, $projectId, $orderData) {
		if (empty($this->connection)) {
			$this->connection = $connection;
		}

		if(!is_array($orderData)) {
			$orderData 		= $this->xml2array($orderData);
			$billingData 	= $this->xml2array($orderData['billing_address']);
			$billingData 	= $billingData[0];
			$customerData 	= $this->xml2array($orderData['customer']);
			$customerData 	= $customerData[0];
		} else {
			$billingData 	= $orderData['billing_address'];
			$customerData 	= $orderData['customer'];
		}

		$customerData = array_merge($customerData, $billingData);
		$orderDate = '';

		if(isset($orderData['create_at']) && !empty($orderData['create_at'])) {
			$orderDate = $orderData['create_at'];
		}

		if(!$debtorId = $this->checkExactCustomerExists($projectId, $customerData)){
			return false;
		}

		$entry = new \Picqer\Financials\Exact\SalesEntry($this->connection);

		$entry->Type = 20;
		$entry->TypeDescription = 'Sales entry';
		$entry->Created         = $orderDate;
		$entry->Customer        = $debtorId;
		$entry->YourRef         = $orderData['invoice_no'];

		if(isset($invoices['invoicedate']) &&  $invoices['invoicedate'] != ''){
			$entry->EntryDate       = $invoices['invoicedate'];
		}

		if(isset( $invoices['duedate']) &&  $invoices['duedate'] != ''){
			$entry->DueDate         = $invoices['duedate'];
		}
		$journal = $this->Projects_model->getValue('vtiger_journal', $projectId)?$this->Projects_model->getValue('journal', $projectId):70;
		$totals = $orderData['totals'];
		$entry->Journal  = $journal;
		$entry->AmountFC = $totals['total'];
		$entryLines   	 = array();
		$products 		 = $orderData['order_products'];
      	// $VATCode      = $this->Projects_model->getValue('vtiger_exact_vatcode', $projectId)?$this->Projects_model->getValue('vtiger_exact_vatcode', $projectId):'';
		// echo "<pre>";
		foreach($products as $item){
			$item_id = $item['model'];
			$amount  = $item['total_price']*$item['quantity'];

			$discount  = 0.0;
			$discount_percent  = 0.0;
			// if($item['discount_percent']!=''){
			// 	$discount_percent = $item['discount_percent'];
			// 	$discount = ($amount*$discount_percent)/100;
			// }
			// if($item['discount_amount']!=''){
			// 	$discount = $item['discount_amount'];
			// }
			$amount = $amount - $discount;
			$GLAccount = new \Picqer\Financials\Exact\GLAccount($connection);
			$GLAccount = $GLAccount->get();
			// echo "<pre>";
			// var_dump($GLAccount);exit;
			$entryLine    = array(
				'Description' => $item['name'],
				'GLAccount'   => "f6491a6f-c705-415d-b6d3-899ed2e07991",//$GLAccount[1]->ID,//1000,
				'AmountFC'    => $amount,
				'Quantity'    => $item['quantity'],
				'VATCode'     => 2
			);
			$entryLines[] = $entryLine;
		}

		$entry->SalesEntryLines = $entryLines;

		try{
		  $result = $entry->save();
		  $message = 'Exported Sales Entry '.$orderData['order_id'].' to Exact Online.';
		  api2cart_log($projectId, 'exportorders', $message);
		  exit;
		  return ['status'=>1, 'message'=>$message];
		} catch(Exception $e){
		  api2cart_log($projectId, 'exportorders', 'Could not Sales Entry '.$orderData['order_id'].' to Exact Online. Error: '.$e->getMessage());
		  exit;
		  return ['status'=>0, 'message'=>$message];
		}
		//return true;
	}

	public function getGoodsReceipts($projectId, $connection, $ordersList) {

        $receiptInfo = [];

        foreach ($ordersList as $orderId) {
            $orderLinesIds = $this->getExactOrderLinesIds($orderId);
            exact_log($projectId, 'receipt_line_ids', json_encode($orderLinesIds));

            foreach ($orderLinesIds as $orderLineId) {

                $receiptModel = new \Picqer_ext\GoodsReceiptLines($connection);
                $receiptLine = $receiptModel->filter("PurchaseOrderLineID eq guid'".$orderLineId['exact_id']."'", '', '', ['$top'=> 1]);

                if(empty($receiptLine)) {
                    exact_log($projectId, 'no_receipt_line', json_encode($receiptLine));
                    continue;
                }
                $lineData = $receiptLine[0]->attributes();

                if(empty($lineData)) {
                    exact_log($projectId, 'empty_receipt', $orderLineId['exact_id']);
                    continue;
                }
                $receiptInfo[$orderId][] = [
                    'ItemCode' => $lineData['ItemCode'],
                    'SupplierItemCode' => $lineData['SupplierItemCode'],
                    'name' => $lineData['ItemDescription'],
                    'ordered' => $lineData['QuantityOrdered'],
                    'quantity' => $lineData['QuantityReceived'],
                    'lineId' => $lineData['PurchaseOrderLineID'],
                    'optiply_id' => $orderLineId['optiply_id'],
                    'receiptDate' => $lineData['Created']
                ];

            }
        }
        exact_log($projectId, 'receipts', json_encode($receiptInfo));
        return $receiptInfo;
    }

    public function getExactOrderLinesIds($id) {
	    $eId = $this->db->get_where('purchase_order_lines', ['optiply_order_id' => $id])->result_array();

	    if(!empty($eId)) {
	        return ($eId);
        }

	    return [];
    }

    public function getLinesToUpdate($connection, $projectId)
    {
        $lines = $this->db
            ->where('status', 0)
            ->where('project_id', $projectId)
            ->where('type', 'update')
            ->get('exact_order_line_updates')
            ->result();

        $mapped = [];

        foreach ($lines as $line) {
            $lineObject = new \Picqer_ext\PurchaseOrderLine($connection);
            $lineData = $lineObject->find($line->item_id)->attributes();

            $mapped[] = [
                'id' => $lineData['Item'],
                'stock' => $lineData['InStock'],
                'amount' => $lineData['AmountDC'],
                'quantity' => $lineData['Quantity'],
                'created' => $lineData['Created'],
                'code' => $lineData['ItemCode'],
                'name' => $lineData['ItemDescription'],
                'line_id' => $lineData['ID'],
                'db_id' => $line->id,
            ];
        }

        return $mapped;
    }

    public function checkOrdersToComplete($connection, $projectId, $orders)
    {
        $receipts = $this->getGoodsReceipts($projectId, $connection, $orders['orders']);
        exact_log($projectId, 'receipts_upd', json_encode($receipts));
        $ordersToComplete = [];

        foreach ($receipts as $order_id => $rec) {

            $received = 0;
            foreach ($rec as $item) {
                if($item['ordered'] == $item['quantity']) {
                    $received++;
                }
            }

            if(count($rec) == $orders['counts'][$order_id] && count($rec) == $received) {
                $ordersToComplete[] = $order_id;
            }
        }
        exact_log($projectId, 'ordersToComplete', json_encode($ordersToComplete));
        return $ordersToComplete;
    }





	function addDeliveryAddress($orderData, $exactCustomerId, $projectId){
		$orderShippingAddress = $orderData['shipping_address'];
		$company = isset($orderShippingAddress['company']) ? $orderShippingAddress['company'] : '';
		
		$street = $orderShippingAddress['address1'];
		$zipCode = str_replace(' ', '', $orderShippingAddress['postcode']);
		$magentoCountryId = $orderShippingAddress['country'];
		
		// Get current delivery addresses first
		$deliveryAddresses = $this->getDeliveryAddresses($exactCustomerId, $projectId);
		foreach($deliveryAddresses as $address){
			if($address['AddressLine1'] == $street && str_replace(' ', '', $address['Postcode']) == $zipCode){
				return (string)$address['ID'];
			}
		}
		
		$connection = $this->connect();
		$address = new \Picqer\Financials\Exact\Address($connection);
		$address->Account = $exactCustomerId;
		$address->Type = 4;
		$address->AddressLine1 = $orderShippingAddress['address1'];
		$address->AddressLine2 = $orderShippingAddress['address2'];
		$address->City = $orderShippingAddress['city'];
		$address->Postcode = $orderShippingAddress['postcode'];
		if (is_object($orderShippingAddress['country'][0])) {
			$address->Country = $orderShippingAddress['country'][0]->code2;
		} else {
			$address->Country = isset($orderShippingAddress['country']) ? $orderShippingAddress['country'] : '';
		}
        $result = $address->save();
        if(!empty($result)){
	        $address = $result->attributes();
	        if(isset($address['ID']) && $address['ID'] != ''){
		        return $address['ID'];
	        }
        }
	}
	
	function getDeliveryAddresses($exactCustomerId, $projectId){
		$connection = $this->connect();
		$addresses = new \Picqer\Financials\Exact\Address($connection);
	    $addresses = $addresses->filter("Account eq guid'".$exactCustomerId."' and Type eq 4");
	    $result = array();
	    foreach($addresses as $address){
			$result[] = $address->attributes();
	    }
		return $result;
	}
}