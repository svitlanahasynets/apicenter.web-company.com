<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Shopware_restapi {

    public  $params;
	public  $shopware;

    public $discount;

    public function __construct(){
    }

    private function getdata($orders){
        return $orders = $orders['data'];
    }

    public function getOrder($config, $orderId){
        include_once ('Shopware_apiclient.php');
        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );

        $order = $this->getdata($client->get('orders/'.$orderId));
        return $order;
    }
    
    public function getOrders($config, $filter = []){
        include_once ('Shopware_apiclient.php');
        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );

        $orders = $this->getdata($client->get('orders', $filter));
        $finalorders = array();

        foreach ($orders as $order) {
            $order = $this->getdata($client->get('orders/'.$order['id']));
			if ($order == NULL) {
                continue;
            }
            array_push($finalorders, $order);
        }
        return $finalorders;
    }
    
    public function getCustomers($config){
        include_once ('Shopware_apiclient.php');

        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );
        
        $customers = $this->getdata($client->get('customers'));
        
        //log_message('debug', 'customers '. var_export($customers, true));
        
        $finalData = array();
        foreach ($customers as $cus){
            $cus = $this->getdata($client->get('customers/' . $cus['id']));
            array_push($finalData, $cus);
        }
        return $finalData;
    }
    
    public function getCountries($config){
        include_once ('Shopware_apiclient.php');

        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );
        
        $countries = $this->getdata($client->get('countries'));
        
        //log_message('debug', 'countries '. var_export($countries, true));
        
        $finalData = array();
        foreach ($countries as $country){
            $country = $this->getdata($client->get('countries/'.$country['id']));
            array_push($finalData, $country);
        }
        return $finalData;
    }
    
    public function getProductBySku($config, $sku) {
        include_once ('Shopware_apiclient.php');

        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );
        $params = [
            'useNumberAsId' => true
        ];

        $sku = str_replace(' ', '', $sku);
        $product = $this->getdata($client->get('articles/'.$sku, $params));

        if (isset($product['id'])) {
            return $product;
        }

        return false;
    }

    public function getclientconnection($config){

        include_once ('Shopware_apiclient.php');

        $client = new Shopware_apiclient(
            $config['ShopUrl'],
            $config['Username'],
            $config['ApiKey']
        );

        return $client;
    } 

}