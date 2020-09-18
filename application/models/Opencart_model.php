<?php
class Opencart_model extends CI_Model 
{
    function __construct() {
        parent::__construct();
        $this->load->model('Projects_model');
        $this->load->helper('tools_helper');
        $this->load->helpers('tools');
        $this->load->helpers('constants');
    }

    private function initClient($projectId) {
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();

        $params = [
            'url'      => $project['store_url'],
            'username' => $this->Projects_model->getValue('opencart_user', $projectId),
            'key'      => $this->Projects_model->getValue('opencart_api_key', $projectId),
            'login'    => $this->Projects_model->getValue('opencart_admin', $projectId),
            'password' => $this->Projects_model->getValue('opencart_password', $projectId),
        ];

        $this->load->library('opencart_restapi', $params);
    }

    public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc', $orderId = '') {
        if (!class_exists('opencart_restapi')) {
            $this->initClient($projectId);
        }

        if ($orderId) {
            $data = $this->opencart_restapi->getOrder($orderId);
        } else {
            $data = $this->opencart_restapi->getOrders();
        }

        $project = $this->db->get_where('projects', array(
            'id' => $projectId
        ))->row_array();
        
        if ($project['erp_system'] == 'exactonline') {
            $data = $this->format_data($projectId, $data);
        }

        return $data;
    }   

    public function getStock($projectId, $sku) {
        if (!class_exists('opencart_restapi')) {
            $this->initClient($projectId);
        }

        $data   = $this->getProductBySku($projectId, $sku);
        $result = [];

        if ($data) {
            $result['qty']        = $data['quantity'];
            $result['product_id'] = $data['id'];
        }

        return $result;
    }

    public function getProductBySku($projectId, $sku) {
        $data = $this->opencart_restapi->getProductBySku($sku);

        if ($data) {
            return $data;
        }

        return false;
    }

    public function updateProductQuantity($projectId, $productId, $qty) {
        if (!class_exists('opencart_restapi')) {
            $this->initClient($projectId);
        }

        if ($this->opencart_restapi->updateProductQuantity($productId, $qty)) {
            api2cart_log($projectId, 'importarticles', 'Product quantity was updated, product_id = ' . $productId);
        }
    }

    public function format_data($projectId, $orders) {
        foreach ($orders as $order) {
			$data = $this->opencart_restapi->getOrder($order['order_id']);
            $appendItem = array(
                'id' => '',//$order['increment_id'],
				'order_id' => $data['order_id'],
				'invoice_no' => isset($data['invoice_no']) ? $data['invoice_no'] : '', 
                'store_id' => '',
                'state' => '',
                'status' => '',
                'customer' => array(
                    'id' => '',
                    'email'         => $data['email'],
                    'first_name'    => $data['firstname'],
                    'last_name'     => $data['lastname']
                ),
                //date("Y-m-d", strtotime($order['PurchaseDate']))
                'create_at' => isset($order['date_added']) ? date("Y-m-d", strtotime($order['date_added'])) : '',
                'modified_at' => '',
                //-----------------------------------//
                'currency' => isset($data['currency_code']) ? $data['currency_code'] : '',
                'totals' => array(
                    'total' => isset($data['total']) ? $data['total'] : '',
                    'subtotal' => '',
                    'shipping' => '',
                    'tax' => '',
                    'discount' => '',
                    'amount_paid' => 0
                )
            );
            
            $appendItem['billing_address'] = array(
                'id' => '',
                'type' => 'billing',
                'first_name' => $data['payment_firstname'], //$data->get('Firstname'),
                'last_name'  => $data['payment_lastname'],  //$data->get('Surname'),
                'postcode'   => $data['payment_postcode'],  //$data->get('ZipCode'),
                'address1'   => $data['payment_address_1'], //$data->get('Streetname') .' '. $data->get('Housenumber'),
                'address2'   => isset($data['payment_address_2']) ? $data['payment_address_2'] : '',
                'phone' 	 => '',
                'city' 		 => $data['payment_city'],//$data->get('City'),
                'country' 	 => $data['payment_country'],//$data->get('CountryCode'),
                'state' 	 => '',
                'company' 	 => '',
                'gender' 	 => '',
            );
            $appendItem['shipping_address'] = array(
                'id' => '',
                'type' => 'billing',
                'first_name' => $data['shipping_firstname'],//$data->get('Firstname'),
                'last_name'  => $data['shipping_lastname'],//$data->get('Surname'),
                'postcode'   => $data['shipping_postcode'],//$data->get('ZipCode'),
                'address1'   => $data['shipping_address_1'],//$data->get('Streetname') .' '. $data->get('Housenumber'),
                'address2'   => isset($data['shipping_address_2']) ? $data['shipping_address_2'] : '',
                'phone' 	 => '',
                'city' 		 => $data['shipping_city'],//$data->get('City'),
                'country' 	 => $data['shipping_country'],//$data->get('CountryCode'),
                'state' 	 => '',
                'company' 	 => '',
                'gender' 	 => '',
            );

            $appendItem['order_products'] = array();
                
            foreach($data['products'] as $item) {
                $appendItem['order_products'][] = array(
                    'product_id' 			=> $item['name'],
                    'order_product_id' 		=> $item['order_product_id'],
                    'model' 				=> $item['sku'],
                    'name' 					=> $item['name'],
                    'quantity' 				=> $item['quantity'],
                    'total_price' 			=> $item['total'],
                    'total_price_incl_tax' 	=> 0,
                    'tax_percent' 			=> 0,
                    'tax_value' 			=> 0,
                    'variant_id' 			=> ''
                );
            }

            if($appendItem != false){
                $finalOrders[] = $appendItem;
            }
        }

        return $finalOrders;
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
							'variant_id' 			=> ''
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
						'id' => '',//$order['increment_id'],
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
						// log_message('error', 'Orders items in Boldotcom'. var_export($order['OrderItems'], true));
						foreach($order['OrderItems'] as $item){
							if (isset($item[0])) {
								$i = 0;
								foreach ($item as $product) {
									$appendItem['order_products'][] = array(
										'product_id' 			=> $product['OfferReference'],
										'order_product_id' 		=> $product['OrderItemId'],
										'model' 				=> $product['EAN'], //$i ? 'IND300654' : 'IND300655',
										'name' 					=> $product['Title'],
										'quantity' 				=> $product['Quantity'],
										'total_price' 			=> $product['OfferPrice'],
										'total_price_incl_tax' 	=> 0,
										'tax_percent' 			=> 0,
										'tax_value' 			=> 0,
										'variant_id' 			=> ''
									);
									$i++;
								}
							} else {
								$appendItem['order_products'][] = array(
									'product_id' 			=> $item['OfferReference'],
									'order_product_id' 		=> $item['OrderItemId'],
									'model' 				=> $item['EAN'],
									'name' 					=> $item['Title'],
									'quantity' 				=> $item['Quantity'],
									'total_price' 			=> $item['OfferPrice'],
									'total_price_incl_tax' 	=> 0,
									'tax_percent' 			=> 0,
									'tax_value' 			=> 0,
									'variant_id' 			=> ''
								);
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
				'company' 	 => '',
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
					'company' 	 => '',
					'gender' 	 => '',
					'email' 	 =>$data['Email']
				);
			}
		}
		
		return $result;
	}
}