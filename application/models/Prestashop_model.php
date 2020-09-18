<?php

class Prestashop_model extends CI_Model
{
    function __construct()
    {   
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        parent::__construct();
        $this->load->model('Projects_model');
        $this->load->helpers('prestashop/prestashop_api');
    }

    public function init_helper($projectId)
    {
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $shop_uri   = $project['store_url'];
        $api_key    = $this->Projects_model->getValue('prestashop_key', $projectId);

        return PrestashopApi::getInstance($shop_uri, $api_key);
    }

    public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc')
    {
        $sdk = $this->init_helper($projectId);
        $finalLIne = [];
        $response  = $sdk->get_orders($offset, $amount);
        // var_dump($response);
        if (!$response['status']) {
            api2cart_log($projectId, 'exportorders', 'Could not get order. Error: ' . $response['data']);
            return;
        }

        if (!$response['data'] && count($response['data']) == 0) return false;

        foreach ($response['data'] as $order) {
            // if ($order['id'] != 3) continue;
            // $info = $this->getInfo(5, 'order_details');
            $order    = $this->getOrder($order['id'], $projectId);
            $status   = $this->getInfo($order['current_state'], 'order_states', $projectId);
            $customer = $this->getInfo($order['id_customer'], 'customer', $projectId);
            $currency = $this->getInfo($order['id_currency'], 'currencies', $projectId);

            $appendItem = array(
                'id' => $order['invoice_number'],
                'order_id' => $order['id'],
                'store_id' => $order['id_shop'],
                'state' => $order['current_state'],
                'status' => array_shift($status['language']),
                'customer' => array(
                    'id' => $customer['id'],
                    'email' => $customer['email'],
                    'first_name' => isset($customer['firstname']) ? $customer['firstname'] : '',
                    'last_name' => isset($customer['lastname']) ? $customer['lastname'] : ''
                ),
                'create_at' => isset($order['date_add']) ? $order['date_add'] : '',//date('Y-m-d', $order['date_add']),
                'modified_at' => isset($order['date_upd']) ? $order['date_upd'] : '',
                'currency' => isset($currency['iso_code']) ? $currency['iso_code'] : $currency['iso_code'],
                'totals' => array(
                    'total' => $order['total_paid'],
                    'subtotal' => $order['total_paid_tax_excl'] - $order['total_shipping_tax_excl'],
                    'shipping' => $order['total_shipping'],
                    'tax' => $order['carrier_tax_rate'],
                    'discount' => $order['total_discounts'],
                   //'amount_paid' => isset($order['total_paid']) ? $order['total_paid'] : 0
                )
            );
            if(isset($order['id_address_invoice']) && !empty($order['id_address_invoice'])) {
                $billing_address = $this->getInfo($order['id_address_invoice'], 'addresses', $projectId);
                $country = $this->getInfo($billing_address['id_country'], 'countries', $projectId);
                $appendItem['billing_address'] = array(
                    'id' => '',
                    'type' => 'billing',
                    'first_name' => $billing_address['firstname'],
                    'last_name' => $billing_address['lastname'],
                    'postcode' => $billing_address['postcode'],
                    'address1' => $billing_address['address1'],
                    'address2' => $billing_address['address2'] != "" ? $billing_address['address2'] : '',
                    'phone' => $billing_address['phone'],
                    'city' => $billing_address['city'],
                    'country' => $country['iso_code'],
                    // 'state' => isset($billing_address['b_state']) ? $billing_address['b_state'] : '',
                    'company' => isset($billing_address['company']) ? $billing_address['company'] : '',
                    'gender' => '',
                );
            }
            if(isset($order['id_address_delivery']) && !empty($order['id_address_delivery'])) {
                $shipping_address = $this->getInfo($order['id_address_delivery'], 'addresses', $projectId);
                $country = $this->getInfo($shipping_address['id_country'], 'countries', $projectId);
                $appendItem['shipping_address'] = array(
                    'id' => '',
                    'type' => 'shipping',
                    'first_name' => $shipping_address['firstname'],
                    'last_name' => $shipping_address['lastname'],
                    'postcode' => $shipping_address['postcode'],
                    'address1' => $shipping_address['address1'],
                    'address2' => $shipping_address['address2'] != "" ? $billing_address['address2'] : '',
                    'phone' => $shipping_address['phone'],
                    'city' => $shipping_address['city'],
                    'country' => $country['iso_code'],
                    // 'state' => isset($billing_address['b_state']) ? $billing_address['b_state'] : '',
                    'company' => isset($shipping_address['company']) ? $shipping_address['company'] : '',
                    'gender' => '',
                );
            }
            if(isset($order['id_carrier']) && $order['id_carrier']){
                $carrier = $this->getInfo($order['id_carrier'], 'carriers', $projectId);
                $appendItem['shipping_method'] = $carrier['name'];
            }
            if(isset($order['payment']) && !empty($order['payment'])){
                $appendItem['payment_method'] = $order['payment'];
            }
            $products = (array) $order['associations'];
            $products = isset($products['order_rows']) ? (array) $products['order_rows'] : [];
            if(isset($products['order_row']) && count($products['order_row'])){
                $appendItem['order_products'] = array();
                if (is_array($products['order_row'])) {
                    foreach($products['order_row'] as $item){
                        $item = (array) $item;
                        $appendItem['order_products'][] = array(
                            'product_id' => $item['product_id'],
                            'order_product_id' => $item['product_id'],
                            'model' => $item['product_reference'],
                            'name' => $item['product_name'],
                            'price' => $item['product_price'],
                            'discount_amount' => isset($item['product_discount']) ? $item['product_discount'] : 0,
                            'quantity' => $item['product_quantity'],
                            'total_price' => $item['unit_price_tax_excl'],
                            'total_price_incl_tax' => $item['unit_price_tax_incl'],
                            'tax_percent' => 0,
                            'tax_value' => $item['unit_price_tax_incl'] - $item['unit_price_tax_excl'],
                            'variant_id' => ''
                        );
                    }
                } else {
                    $item = (array) $products['order_row'];
                    $appendItem['order_products'][] = array(
                        'product_id' => $item['product_id'],
                        'order_product_id' => $item['product_id'],
                        'model' => $item['product_reference'],
                        'name' => $item['product_name'],
                        'price' => $item['product_price'],
                        'discount_amount' => isset($item['product_discount']) ? $item['product_discount'] : 0,
                        'quantity' => $item['product_quantity'],
                        'total_price' => $item['unit_price_tax_excl'],
                        'total_price_incl_tax' => $item['unit_price_tax_incl'],
                        'tax_percent' => 0,
                        'tax_value' => $item['unit_price_tax_incl'] - $item['unit_price_tax_excl'],
                        'variant_id' => ''
                    );
                }
            }

            if($appendItem != false){
                $finalOrders[] = $appendItem;
            }
        }

        return $finalOrders;
    }

    public function getInfo($id, $key, $projectId) {
        $sdk   = $this->init_helper($projectId);
        $funcName = 'get_' . $key;
        $order = $sdk->$funcName($id);

        if (!$order['status']) {
            log_message('debug', 'Could not get ' . $key . ' Error: ' . var_export($order['data'], true));
            return;
        }

        return $order['data'];
    }

    public function getOrder($orderId, $projectId)
    {
        $sdk   = $this->init_helper($projectId);
        $order = $sdk->get_order($orderId);

        if (!$order['status']) {
            api2cart_log($projectId, 'exportorders', 'Could not get order. Error: ' . $order['data']);
            return;
        }

        return $order['data'];
    }

    public function updateArticles($projectId, $articles) {
        foreach($articles as $article) {
            $productExists = $this->checkProductExists($article, $projectId);
            if($productExists === false) {
                $this->createProduct($article, $projectId);
            } else {
                $this->updateProduct($article, $productExists, $projectId);
            }
        }
    }

    public function checkProductExists($article, $projectId) {
        return $this->getInfo($article['model'], 'products', $projectId);
    }

    public function updateProduct($article, $productExists, $projectId) {
        $sdk = $this->init_helper($projectId);
        $product = $sdk->update_product($article, $productExists);

        if(isset($product['status'])) {
            api2cart_log($projectId, 'importarticles', 'Updated product '.$article['model']);
        } else {
            api2cart_log($projectId, 'importarticles', 'Could not update product '.$article['model'].'. Result: '.print_r($product['data'], true));
        }
    }

    public function createProduct($article, $projectId) {
        $sdk = $this->init_helper($projectId);
        $product = $sdk->create_product($article);

        if(isset($product['status'])) {
            if (isset($product['stock'])) {
                api2cart_log($projectId, 'importarticles', 'Created product '.$article['model']);
            } else {
                api2cart_log($projectId, 'importarticles', 'Created product '.$article['model'] . ', stock is null');
            }
        } else {
            api2cart_log($projectId, 'importarticles', 'Could not create product '.$article['model'].'. Result: '.print_r($product['data'], true));
        }
    }

    public function findCategory($projectId, $categoryName) {
        $response = $this->getInfo($categoryName, 'categories', $projectId);
        $cat      = [];

        if ($response === false || $response === NULL) return $cat;

        $cat['items'][0] = ['id' => $response];

        return $cat;
    }

    public function createCategory($projectId, $categoryName)
    {
        $category['id'] = [];
        $sdk            = $this->init_helper($projectId);
        $response       = $sdk->create_category($categoryName);
        if ($response['status']) {
            $category['id'] = $response['data'][0];
            api2cart_log($projectId, 'importarticles', 'Created category '.$categoryName);
        } else {
            api2cart_log($projectId, 'importarticles', 'Could not create category '.$categoryName.'. Result: '.print_r($response['data'], true));
        }

        return $category;
    }

    public function createCustomer($projectId, $customer)
    {
        $sdk = $this->init_helper($projectId);
        $customer = $sdk->create_customer($customer);

        if(isset($customer['status'])){
            api2cart_log($projectId, 'importcustomers', 'Created customer '.$customer['email']);
        } else {
            api2cart_log($projectId, 'importcustomers', 'Could not created customer '.$customer['email'].'. Result: '.print_r($result['data'], true));
        }
    }

    public function addImage($projectId, $article)
    {
        $sdk = $this->init_helper($projectId);
        $image = $sdk->add_image($projectId, $article[0]['image']);

    }

    public function getProduct($projectId, $article)
    {
        $sdk = $this->init_helper($projectId);
        $image = $sdk->get_product($projectId, 12);

    }

    public function updateStockArticles($projectId, $articles)
    {
        $sdk = $this->init_helper($projectId);

        foreach($articles as $article) {
            $productExists = $this->checkProductExists($article, $projectId);
            if($productExists !== false) {
                $stock = $sdk->set_stock($productExists, $article);
                $this->dd($stock, 1);
                if(isset($stock['status'])) {
					api2cart_log($projectId, 'importarticles', 'Updated product stock for product '.$article['model']);
				} else {
					api2cart_log($projectId, 'importarticles', 'Could not update product stock for product '.$article['model'].'. Result: '.print_r($result['data'], true));
				}
            }
        }
    }

    private function dd($data, $is_exit) {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
        if ($is_exit) exit;
    }

}