<?php
class Boldotcom_model extends CI_Model {

	// https://github.com/picqer/exact-php-client
	/**
	* @author manish
	* @return array
	*/

	private $projectId;
	private $redirectUrl;
	private $clientId;
	private $clientSecret;
	private $connection;

	public function __construct(){
	    parent::__construct();
	}

	public function upsertOffer($projectId, $product_data, $debug = false){
        $this->load->model('Magentobol_model');
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
		$bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443, 'test'=>$debug]);
		$custom_attributes 	= isset($product_data['custom_attributes'])?$product_data['custom_attributes']:'';

        $ean_code = $this->Projects_model->getValue('bol_ean_code', $projectId)?$this->Projects_model->getValue('bol_ean_code', $projectId):'sku';
        $product_condition_code = $this->Projects_model->getValue('product_condition_code', $projectId)?$this->Projects_model->getValue('product_condition_code', $projectId):'';
        $product_description = $this->Projects_model->getValue('product_description', $projectId)?$this->Projects_model->getValue('product_description', $projectId):'';
        $price_code = $this->Projects_model->getValue('price_code', $projectId)?$this->Projects_model->getValue('price_code', $projectId):'price';
        $product_delivery_code = $this->Projects_model->getValue('product_delivery_code', $projectId)?$this->Projects_model->getValue('product_delivery_code', $projectId):'';
        $quantity_code = $this->Projects_model->getValue('quantity_code', $projectId)?$this->Projects_model->getValue('quantity_code', $projectId):'quantity';
        $product_as_publish = $this->Projects_model->getValue('product_as_publish', $projectId)?$this->Projects_model->getValue('product_as_publish', $projectId):'false';
        $fulfilment_status  = $this->Projects_model->getValue('fulfilment_status', $projectId)?$this->Projects_model->getValue('fulfilment_status', $projectId):0;
        $reference_code  = $this->Projects_model->getValue('reference_code', $projectId)?$this->Projects_model->getValue('reference_code', $projectId):'';
        $bol_export  = $this->Projects_model->getValue('export_product_in_bol', $projectId)?$this->Projects_model->getValue('export_product_in_bol', $projectId):'';
        $bol_export_val = 0;
       
		$offer_data    	= array();
		$bol_ean 		= '';
		$bol_price 		= '';
		$bol_condition  = '';
		$bol_delivery 	= '';
		$bol_quantity 	= 0;
		$bol_publish 	= 'false';
 		$bol_fulfillment = 'FBR'; 
		$bol_reference   = '';
		$bol_description = '';

		if($product_as_publish==0)
        	$bol_publish = 'false';
        else
        	$bol_publish = 'true';

        if($fulfilment_status==0)
        	$bol_fulfillment = 'FBR';
        else
        	$bol_fulfillment = 'FBB';

		if($ean_code=='sku')
			$bol_ean 					= $product_data['sku'];
		if($price_code=='price')
			$bol_price        	        = $product_data['price'];
		if($quantity_code=='quantity'){
			$stock 			= isset($product_data['extension_attributes'])?$product_data['extension_attributes']:'';
			if($stock!='')
				$stock      = isset($product_data['extension_attributes']['stock_item'])?$product_data['extension_attributes']['stock_item']:'';
			if($stock!='')
				$bol_quantity = isset($stock['qty'])?$stock['qty']:0;
		}
		$magento_condition = '';
		$magento_delivery  = '';
		foreach ($custom_attributes as $cus_atr_key => $cus_atr_value) {
			if($ean_code!=='sku'){
				if($cus_atr_value['attribute_code'] == $ean_code){
					$bol_ean    		= $cus_atr_value['value'];
					continue;
				}
			}
			if($price_code!=='price'){
				if($cus_atr_value['attribute_code'] == $price_code){
					$bol_price    		= $cus_atr_value['value'];
					continue;
				}
			}
			if($quantity_code!=='quantity'){
				if($cus_atr_value['attribute_code'] == $quantity_code){
					$bol_quantity    	= $cus_atr_value['value'];
					continue;
				}
			}
			if($cus_atr_value['attribute_code'] == $product_condition_code){
				$magento_condition    		= $cus_atr_value['value'];
				continue;
			}

			if($cus_atr_value['attribute_code'] == $product_delivery_code){
				$magento_delivery    		= $cus_atr_value['value'];
				continue;
			}
			if($reference_code!=''){
				if($cus_atr_value['attribute_code'] == $reference_code){
					$bol_reference    		= $cus_atr_value['value'];
					continue;
				}
			}
			if($product_description!=''){
				if($cus_atr_value['attribute_code'] == $product_description){
					$bol_description    		= $cus_atr_value['value'];
					continue;
				}
			}
			if($bol_export!=''){
				if($cus_atr_value['attribute_code'] == $bol_export){
					$bol_export_val   		= $cus_atr_value['value'];
					continue;
				}
			}
		}
		// product will not imported to bol.com
		if($bol_export_val==0)
			return false;

		if($magento_condition!=''){
			$bol_condition = $this->Magentobol_model->getMagentoAttributeLebel($projectId, $product_condition_code, $magento_condition);
			if(!$bol_condition)
				$bol_condition = '';
		}
		if($magento_delivery!=''){
			$bol_delivery = $this->Magentobol_model->getMagentoAttributeLebel($projectId, $product_delivery_code, $magento_delivery);
			if(!$bol_delivery)
				$bol_delivery = '';
		}

		$offer_data['EAN'] 				= $bol_ean;
		$offer_data['Price'] 			= $bol_price;
		$offer_data['Condition'] 		= $bol_condition;
		$offer_data['DeliveryCode']     = $bol_delivery;
		$offer_data['QuantityInStock'] 	= $bol_quantity;
		$offer_data['Publish'] 			= $bol_publish;
		$offer_data['FulfillmentMethod']= $bol_fulfillment;
		$offer_data['ReferenceCode'] 	= $product_data['sku'];
		$offer_data['Title'] 			= $product_data['name'];
		// if($bol_description!='')
		// 	$offer_data['Description'] 	= $bol_description;
		// else
		$offer_data['Description'] 	= $product_data['name'];
		$this->load->library('Bolplaza');
		return $this->bolplaza->upsertOffer($offer_data, $bol_params);
	}

	public function upsertFormatOffer($projectId, $product_data){

		$this->load->model('Projects_model');
        $ean_code = $this->Projects_model->getValue('bol_ean_code', $projectId)?$this->Projects_model->getValue('bol_ean_code', $projectId):'EAN';
        $condition_code = $this->Projects_model->getValue('product_condition_code', $projectId)?$this->Projects_model->getValue('product_condition_code', $projectId):'Conditie';
        $description = $this->Projects_model->getValue('product_description', $projectId)?$this->Projects_model->getValue('product_description', $projectId):'Description';
        $price_code = $this->Projects_model->getValue('price_code', $projectId)?$this->Projects_model->getValue('price_code', $projectId):'BasicSalesPrice';
        $delivery_code = $this->Projects_model->getValue('product_delivery_code', $projectId)?$this->Projects_model->getValue('product_delivery_code', $projectId):'BezorgCode';
        $quantity_code = $this->Projects_model->getValue('quantity_code', $projectId)?$this->Projects_model->getValue('quantity_code', $projectId):'quantity';
        $as_publish1 = $this->Projects_model->getValue('product_as_publish', $projectId)?$this->Projects_model->getValue('product_as_publish', $projectId):'false';
        $fulfilment_status1  = $this->Projects_model->getValue('fulfilment_status', $projectId)?$this->Projects_model->getValue('fulfilment_status', $projectId):0;
        $reference_code  = $this->Projects_model->getValue('reference_code', $projectId)?$this->Projects_model->getValue('reference_code', $projectId):'ItemCode';
        $bol_publish1 	= 'false';
 		$bol_fulfillment1 = 'FBR'; 
		if($as_publish1==0)
        	$bol_publish1 = 'false';
        else
        	$bol_publish1 = 'true';

        if($fulfilment_status1==0)
        	$bol_fulfillment1 = 'FBR';
        else
        	$bol_fulfillment1 = 'FBB';

		$offer_data    	= array();
		$bol_ean 		= isset($product_data[$ean_code])?$product_data[$ean_code]:'';
		$bol_price 		= isset($product_data[$price_code])?$product_data[$price_code]:'';
		$bol_condition  = isset($product_data[$condition_code])?$product_data[$condition_code]:'';
		$bol_delivery 	= isset($product_data[$delivery_code])?$product_data[$delivery_code]:'';
		$bol_quantity 	= isset($product_data[$quantity_code])?$product_data[$quantity_code]:'';
		$bol_publish 	= isset($product_data['Publiseren'])?$product_data['Publiseren']:$bol_publish1;
 		$bol_fulfillment= isset($product_data['BezorgMethode'])?$product_data['BezorgMethode']:$fulfilment_status1;
		$bol_reference  = isset($product_data[$reference_code])?$product_data[$reference_code]:'';;
		$bol_description= isset($product_data[$description])?$product_data[$description]:'';;

		if($bol_ean !='' && $bol_price !='' && $bol_condition !='' && $bol_delivery !='' && $bol_quantity !='' && $bol_publish !='' && $bol_fulfillment !='' && $bol_reference !='' && $bol_description !='' ){
			$offer_data['EAN'] 				= $bol_ean;
			$offer_data['Price'] 			= $bol_price;
			$offer_data['Condition'] 		= $bol_condition;
			$offer_data['DeliveryCode']     = $bol_delivery;
			$offer_data['QuantityInStock'] 	= $bol_quantity;
			$offer_data['Publish'] 			= $bol_publish;
			$offer_data['FulfillmentMethod']= $bol_fulfillment;
			$offer_data['ReferenceCode'] 	= $bol_reference;
			$offer_data['Description'] 		= $bol_description;
			return ['status'=>1, 'result'=>$offer_data];
		}
		$message = 'Failed All Filelds required : EAN-'.$bol_ean.', Price-'.$bol_price.', Condition-'.$bol_condition.', DeliveryCode-'.$bol_delivery.', QuantityInStock-'.$bol_quantity.', FulfillmentMethod-'.$bol_fulfillment.', ReferenceCode-'.$bol_reference.', Description-'.$bol_description ;
		return ['status'=>0, 'result'=>$message];
	}

	public function callUpserOffer($projectId, $formated_data){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
        $bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);

        $this->load->library('Bolplaza');
		return $this->bolplaza->upsertOffer($formated_data, $bol_params);
	}
		
	public function getOrders($projectId, $debug = false, $formated_data = false){
	//public function getOrders($projectId, $debug = false){
		if ($projectId == 78) {
			$this->projectId = $projectId;
			$this->load->helper('boldotcom/bol');
			$ordersData = get_orders($projectId, $debug);
		} else {
			$public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        	$private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        	if($public_key == '' || $private_key== ''){
        		return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        	}
			$bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443, 'test'=>$debug]);
			$this->load->library('Bolplaza');

			//ADDED Bol.com - Exact 26 Feb 2019 - LCB
			$ordersData = $this->bolplaza->getOrders('', $bol_params);
		}

		if ($formated_data) {
			$ordersData = $this->formated_data($ordersData);
		}

		return $ordersData;

		//return $this->bolplaza->getOrders('', $bol_params);
	}

	public function getSingleOffers(){
		$bol_params = array(['public_key'=>'SKOzQHzOlnXbrrbTxIRvnozMJGoCSFQb', 'private_key'=>'isHZjnaGGUxCvvYnsyyFgESfvYxQfhRnYAkQkJXzHZhRxDmwPobpXhJgQxYvLFyJkcxQecimkHluyHsdWqMnliutOYLBYvnjsYCqpUZKTJixRjVJqvVqGQMoplCqUbHhUoaRoAEVmzixJkGBjfDJQHqRXyBgtulNhGtychncsCkjGNIIyHThIuOmENdeOgIvsowoNDoLFxikMMXmsNkggkDhNTepfKOMjOAntrFLadsmbwNHnhqpOKhlqbcqNvCp','port'=>443,'test'=>false]);		
		$this->load->library('Bolplaza');
		$get_single_offers = $this->bolplaza->getSingleOffers('9789026327346',['condition'=>'NEW'], $bol_params);
		print_r($get_single_offers);
	}

	public function updateArticles($projectId, $articles){

		if($projectId == 161){
			log_message('debug', 'AFASBOL - Update: ');
		}
		
		$this->load->model('Projects_model');
		
		$public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
		$private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
		if($public_key == '' || $private_key== ''){
			//log_message('error', 'Exception !!!!!!!'.$projectId);
			return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
		}

		if($projectId == 161){
			log_message('debug', 'AFASBOL - LoginSuccess: ');
		}
		
		$bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443, 'test'=>false]);//'test'=>$debug]);
	
		$this->load->library('Bolplaza');

		if($projectId == 161){
			log_message('debug', 'AFASBOL - Plazaload: ');
		}

		$ean_code = $this->Projects_model->getValue('bol_ean_code', $projectId)?$this->Projects_model->getValue('bol_ean_code', $projectId):'sku';
        $price_code = $this->Projects_model->getValue('price_code', $projectId)?$this->Projects_model->getValue('price_code', $projectId):'price';
        $quantity_code = $this->Projects_model->getValue('quantity_code', $projectId)?$this->Projects_model->getValue('quantity_code', $projectId):'quantity';
        $fulfilment_status  = $this->Projects_model->getValue('fulfilment_status', $projectId)?$this->Projects_model->getValue('fulfilment_status', $projectId):0;
        $product_condition_code = $this->Projects_model->getValue('product_condition_code', $projectId)?$this->Projects_model->getValue('product_condition_code', $projectId):'';
        $product_description = $this->Projects_model->getValue('product_description', $projectId)?$this->Projects_model->getValue('product_description', $projectId):'';
        $product_delivery_code = $this->Projects_model->getValue('product_delivery_code', $projectId)?$this->Projects_model->getValue('product_delivery_code', $projectId):'';
        $product_as_publish = $this->Projects_model->getValue('product_as_publish', $projectId)?$this->Projects_model->getValue('product_as_publish', $projectId):'false';
        $reference_code  = $this->Projects_model->getValue('reference_code', $projectId)?$this->Projects_model->getValue('reference_code', $projectId):'';

        $bol_export  = $this->Projects_model->getValue('export_product_in_bol', $projectId)?$this->Projects_model->getValue('export_product_in_bol', $projectId):'';
        $bol_export_val = 0;
       
		if($projectId == 161){
			log_message('debug', 'AFASBOL - Get Variables: ');
		}

		$offer_data    	 = array();
		$bol_ean 		 = '';
		$bol_price 		 = '';
		$bol_condition   = '';
		$bol_delivery 	 = '';
		$bol_quantity 	 = 0;
		$bol_publish 	 = 'false';
 		$bol_fulfillment = 'FBR'; 
		$bol_reference   = '';
		$bol_description = '';

		// if($product_as_publish==0)
        // 	$bol_publish = 'false';
        // else
        // 	$bol_publish = 'true';

        if($fulfilment_status==0)
        	$bol_fulfillment = 'FBR';
        else
        	$bol_fulfillment = 'FBB';

		if($projectId == 161){
			log_message('debug', 'AFASBOL - SetVariables: ');
		}

		$final_result = [];
		foreach($articles as $article) 
		{
			if($projectId == 161){
			    log_message('debug', 'AFASBOL - Pre-conversion: '. var_export($article, true));
			}
			
			//if ($this->Projects_model->getValue('erp_system', $projectId) == 'afas') {
			if($projectId == 27) {
				//AFAS Extra fields
				if (isset($article['custom_attributes']['EAN'])){ 
					$ean = $article['custom_attributes']['EAN'];
					$bol_ean = $article['model'];
				}
				if (isset($article['custom_attributes']['BOL_Price'])){
				    $bol_price = str_replace(',','.',$article['custom_attributes']['BOL_Price']);
					//$bol_price = $article['custom_attributes']['BOL_Price'];
				}
				if (isset($article['custom_attributes']['BezorgCode'])){ 
					$bol_delivery = $article['custom_attributes']['BezorgCode'];
				}
				if (isset($article['custom_attributes']['Publiseren'])){ 
					$bol_publish = $article['custom_attributes']['Publiseren'];
				}
				$stock  	 = isset($article['quantity']) ? $article['quantity'] : 0;
				
				if($projectId == 27){
					//('debug', 'AFASBOL - conversion: '. var_export($ean, true));
				}
			} else if($projectId == 161){
			
				if (isset($article['custom_attributes']['EAN'])){  
			        $ean = $article['custom_attributes']['EAN'];
					$bol_ean = $article['model'];
			    }
				
				$bol_price_tmp = $article['custom_attributes']['BOL_Price'];
			    $bol_price_tmp = 1.21 * $bol_price_tmp;
			    $bol_price = number_format(round( $bol_price_tmp, 2), 2);
			    
			    $stock  	 = isset($article['quantity']) ? $article['quantity'] : 0;
			    $bol_delivery = '1-2d';
			    $bol_publish = 'true';
			} 
			else {
				if($ean_code == 'sku') 			{ $bol_ean	 = $article['model']; }
				if($price_code == 'price')		{ $bol_price = $article['price']; }
				if($quantity_code == 'quantity'){ $stock  	 = isset($article['quantity']) ? $article['quantity'] : 0; }

				if(isset($article['barcode']) && !empty($article['barcode'])) {
					$ean = $article['barcode'];
				} else {
					//log_message('error', 'Barcode is empty'.$projectId);
					continue;
				}
				$bol_delivery = '1-2d';
			}

			$exact_condition = '';
			$exact_delivery  = '';
			if ($projectId ==  64) {
				$bol_publish = 'false';
			}
			$offer_data['EAN'] 				= $ean;
			$offer_data['Price'] 			= $bol_price;
			$offer_data['Condition'] 		= 'NEW';
			$offer_data['DeliveryCode']     = $bol_delivery;
			$offer_data['QuantityInStock'] 	= $stock;
			$offer_data['Publish'] 			= $bol_publish;
			$offer_data['FulfillmentMethod']= $bol_fulfillment;
			$offer_data['ReferenceCode'] 	= $bol_ean;
			$offer_data['Title'] 			= $article['name'];
			$offer_data['Description'] 		= $article['description'];

			$final_result[] = $offer_data;

			if($projectId == 161){
				log_message('debug', 'AFASBOL - Converted to BOL: '. var_export($offer_data, true));
			}

			$res = $this->bolplaza->upsertOffer($offer_data, $bol_params);
			
			if($projectId == 161){
				log_message('debug', 'AFASBOL - BOL response: '. var_export($res, true));
			}

			if(isset($res['code']) && ($res['code'] == 202 || $res['code'] == 200)) {
				apicenter_logs($projectId, 'importarticles', 'Import product '.$ean, false);
			} else {
				$message = isset($res['result']) ? $res['result']['ValidationErrors'] : [];
				$message = isset($message['ValidationError']) ? $message['ValidationError'] : [];
				if (isset($message['Value'])) {
					$messages = $message['ErrorMessage'] . ' EAN: ' . $message['Value'];
					apicenter_logs($projectId, 'importarticles', 'Could not update product '.$ean.'. Result: '.$messages, true);
				} else {
					apicenter_logs($projectId, 'importarticles', 'Could not update product '.$ean.'. Result: '.print_r($res, true), true);
				}
			}
		}
	}

	protected function formated_data($orders){
		if ($this->projectId == 78) {
			foreach ($orders as $order) {
				$billingDetails  = $order->get('CustomerDetails')->get('BillingDetails');
				$shippingDetails = $order->get('CustomerDetails')->get('ShipmentDetails');
				$date = (array) $order->get('DateTimeCustomer');
				$appendItem = array(
					'id' => '',//$order['increment_id'],
					'order_id' => $order->get('OrderId'),
					'store_id' => '',
					'state' => '',
					'status' => '',
					'customer' => array(
						'id' => '',
						'email' => $billingDetails->get('Email'),
						'first_name' => $billingDetails->get('Firstname'),
						'last_name' => $billingDetails->get('Surname')
					),
					//fields aren't in cs-cart api
					'create_at' => isset($date['date']) ? $date['date'] : '',
					'modified_at' => '',
					//-----------------------------------//
					'currency' => '',
					'totals' => array(
						'total' => '',
						'subtotal' => '',
						'shipping' => '',
						'tax' => '',
						'discount' => '',
						'amount_paid' => 0
					)
				);
				
				$appendItem['billing_address'] = $this->getBillingShippingInfo($billingDetails, 1);
				$appendItem['shipping_address'] = $this->getBillingShippingInfo($shippingDetails, 0);

				$appendItem['order_products'] = array();

				foreach($order->get('OrderItems') as $item){
					$appendItem['order_products'][] = array(
						'product_id' 			=> $item->get('OfferReference'),
						'order_product_id' 		=> $item->get('OrderItemId'),
						'model' 				=> $item->get('EAN'),
						'name' 					=> $item->get('Title'),
						'quantity' 				=> $item->get('Quantity'),
						'total_price' 			=> $item->get('OfferPrice'),
						'total_price_incl_tax' 	=> 0,
						'tax_percent' 			=> 0,
						'tax_value' 			=> 0,
						'variant_id' 			=> '',
						'fulfilment_method'		=> $item->get('FulfilmentMethod'),
					);
				}

				if($appendItem != false){
					$finalOrders[] = $appendItem;
				}
			}
			return $finalOrders;
		} else {
			if ($orders['code']==200) {
				$result = $orders['result'];
				if (isset($result['Order']) && !isset($result['Order'][0])) {
					$orders_list[] = isset($result['Order'])?$result['Order']:array();
				} elseif(isset($result['Order'])) {
					$orders_list = isset($result['Order'])?$result['Order']:array();
				}
	            foreach ($orders_list as $order) {
	                $appendItem = array(
	                    'id' => 'B' . $order['OrderId'], //$order['increment_id'],
	                    'order_id' => $order['OrderId'],
	                    'store_id' => '',
	                    'state' => '',
	                    'status' => '',
	                    'customer' => array(
	                        'id' => '',
	                        'email' => isset($order['CustomerDetails']['BillingDetails']) ? $order['CustomerDetails']['BillingDetails']['Email'] : '',
	                        'first_name' => isset($order['CustomerDetails']['BillingDetails']) ? $order['CustomerDetails']['BillingDetails']['Firstname'] : '',
	                        'last_name' => isset($order['CustomerDetails']['BillingDetails']) ? $order['CustomerDetails']['BillingDetails']['Surname'] : ''
	                    ),
	                    //fields aren't in cs-cart api
	                    'create_at' => $order['DateTimeCustomer'],
	                    'modified_at' => '',
	                    //-----------------------------------//
	                    'currency' => '',
	                    'totals' => array(
	                        'total' => '',
	                        'subtotal' => '',
	                        'shipping' => '',
	                        'tax' => '',
	                        'discount' => '',
	                        'amount_paid' => 0
	                    )
	                );
	                if(isset($order['CustomerDetails']) && !empty($order['CustomerDetails'])) {
	                    $appendItem['billing_address'] = isset($order['CustomerDetails']['BillingDetails']) ? $this->getBillingShippingInfo($order['CustomerDetails']['BillingDetails'], 1) : [];
	                }
	                if(isset($order['CustomerDetails']) && !empty($order['CustomerDetails'])) {
	                    $appendItem['shipping_address'] = isset($order['CustomerDetails']['ShipmentDetails']) ? $this->getBillingShippingInfo($order['CustomerDetails']['ShipmentDetails'], 0) : [];
					}
					if(isset($order['OrderItems']) && !empty($order['OrderItems'])){
						$appendItem['order_products'] = array();
	
						log_message('debug', 'Orders items in Boldotcom'. var_export($order['OrderItems'], true));
	
						foreach($order['OrderItems'] as $item){
							if (isset($item[0])) {
								$i = 0;
	
								foreach ($item as $product) {
	                                   
	                                $itemPrice      = floatval($product['OfferPrice']);
	                                $itemQuantity   = intval($product['Quantity']);
	                                
	                                if ($itemQuantity == 1) {
	                                    $itemIndPrice = $itemPrice;
	                                } else {
	                                    $itemIndPrice = $itemPrice / $itemQuantity;
	                                }
	                                
	                                $removeTax = $this->Projects_model->getValue('tax_setting', $projectId);
	                                /*if ($removeTax == '1')*/ $itemIndPrice = $itemIndPrice / 1.21;
	                                
									$appendItem['order_products'][] = array(
										'product_id' 			=> $product['OfferReference'],
										'order_product_id' 		=> $product['OrderItemId'],
										'model' 				=> $product['EAN'], //$i ? 'IND300654' : 'IND300655',
										'name' 					=> $product['Title'],
										'quantity' 				=> $product['Quantity'],
										'total_price' 			=> $product['OfferPrice'],
										'price' 				=> $itemIndPrice,
										'tax_percent' 			=> 0,
										'tax_value' 			=> 0,
										'variant_id' 			=> ''
									);
									log_message('debug', 'Orders items - appenditem'. var_export($appendItem['order_products'], true));
	
									$i++;
								}
							} else {
								$itemPrice      = floatval($item['OfferPrice']);
	                            $itemQuantity   = intval($item['Quantity']);
	                            
	                            if ($itemQuantity == 1) {
	                                $itemIndPrice = $itemPrice;
	                            } else {
	                                $itemIndPrice = $itemPrice / $itemQuantity;
	                            }
	                            
	                            $removeTax = $this->Projects_model->getValue('tax_setting', $projectId);
	                            /*if ($removeTax == '1')*/ $itemIndPrice = $itemIndPrice / 1.21;
	
								$appendItem['order_products'][] = array(
									'product_id' 			=> $item['OfferReference'],
									'order_product_id' 		=> $item['OrderItemId'],
									'model' 				=> $item['EAN'],
									'name' 					=> $item['Title'],
									'quantity' 				=> $item['Quantity'],
									'total_price' 			=> $item['OfferPrice'],
									'price'				 	=> $itemIndPrice,
									'tax_percent' 			=> 0,
									'tax_value' 			=> 0,
									'variant_id' 			=> ''
								);
								log_message('debug', 'Orders items - appenditem2'. var_export($appendItem['order_products'], true));
							}
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
						$finalOrders[] = $appendItem;
					}
				}
	            return $finalOrders;
        	}
		}
        return false;
	}

	private function getBillingShippingInfo($data, $flag){
		$result = [];
		$type   = $flag ? 'billing' : 'shipping';

		if ($this->projectId == 78) {
			$result = array(
				'id' => '',
				'type' => $type,
				'first_name' => $data->get('Firstname'),
				'last_name'  => $data->get('Surname'),
				'postcode'   => $data->get('ZipCode'),
				'address1'   => $data->get('Streetname') .' '. $data->get('Housenumber'),
				'address2'   => '',
				'phone' 	 => '',
				'city' 		 => $data->get('City'),
				'country' 	 => $data->get('CountryCode'),
				'state' 	 => '',
				'company' 	 => $data->get('Company'),
				'gender' 	 => '',
			);
		} else {
			$houseNumber = isset($data['Housenumber']) ? $data['Housenumber'] : '';
			$houseNumberExtend = isset($data['HousenumberExtended']) ? $data['HousenumberExtended'] : '';
			if (!empty($data)) {
				$result = array(
					'id' => '',
					'type' => $flag ? 'billing' : 'shipping',
					'first_name' => $data['Firstname'],
					'last_name'  => $data['Surname'],
					'postcode'   => $data['ZipCode'],
					'address1'   => $data['Streetname'] ." ". $houseNumber ." ". $houseNumberExtend,
					'address2'   => '',
					'phone' 	 => $data['DeliveryPhoneNumber'],
					'city' 		 => $data['City'],
					'country' 	 => $data['CountryCode'],
					'state' 	 => '',
					'company' 	 => $data['Company'],
					'gender' 	 => '',
					'email' 	 =>$data['Email']
				);
			}
		}
		
		return $result;
	}

	public function getAllOffers($projectId){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
		$bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>true]);

		$this->load->library('Bolplaza');
		return $this->bolplaza->getAllOffers($bol_params);
	}
	
	public function getAllOffersDown($projectId, $file_name){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
		$bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);
		$this->load->library('Bolplaza');
		return $this->bolplaza->getAllOffersDown($file_name, $bol_params);
	}

	public function getSingleOrder($projectId, $orderId, $bol_live_mode){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
        $bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);
        
        $this->load->library('Bolplaza');
		return $this->bolplaza->getSingleOrder($orderId, $bol_params);
	}

	public function cancelOrder($projectId, $order_data, $bol_live_mode, $updated_at){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
        $bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);
		$this->load->library('Bolplaza');
		return $this->bolplaza->cancelOrder($order_data, $updated_at, $bol_params);
	}

	public function shipmentOrder($projectId, $orderId, $bol_live_mode, $entity_id, $trace_and_trac=''){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        $bol_transporters_code = $this->Projects_model->getValue('bol_transporters_code', $projectId)?$this->Projects_model->getValue('bol_transporters_code', $projectId):'OTHER';
        if($trace_and_trac=='')
        	$bol_transporters_code = 'OTHER';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
        $bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);
		$this->load->library('Bolplaza');
		return $this->bolplaza->shipmentOrder($orderId, $entity_id, $bol_transporters_code, $trace_and_trac, $bol_params);
	}

	public function processStatus($projectId, $process_status_id, $bol_live_mode){
		$this->load->model('Projects_model');
        $public_key  = $this->Projects_model->getValue('bol_public_key', $projectId)?$this->Projects_model->getValue('bol_public_key', $projectId):'';
        $private_key = $this->Projects_model->getValue('bol_private_key', $projectId)?$this->Projects_model->getValue('bol_private_key', $projectId):'';
        if($public_key == '' || $private_key== ''){
        	return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
        $bol_params = array(['public_key'=>$public_key, 'private_key'=>$private_key,'port'=>443,'test'=>false]);
		$this->load->library('Bolplaza');
		return $this->bolplaza->processStatus($process_status_id, $bol_params);
	}
}