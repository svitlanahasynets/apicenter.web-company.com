<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/shopifySDK/vendor/autoload.php';

use PHPShopify\ShopifySDK as Shopify;


class Shopify_restapi {

    public  $params;
	public  $shopify;

    public function __construct(){
    }

    public function checkProductById($config, $id){
        $shopify    = Shopify::config($config);
        try {
            $product    = $shopify->Product($id)->get();
            if($product){
                return ['status'=>1, 'data'=>$product, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function getProductList($config) {
        $shopify    = Shopify::config($config);
        try {
            $products   = $shopify->Product->get();
            if($product){
                return ['status'=>1, 'data'=>$product, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function getProductById($config, $id){
        $shopify    = Shopify::config($config);
        $product    = $shopify->Product($id)->get();
        return $product; 
    }

    public function getProductByField($config, $params=[]){
        $shopify    = Shopify::config($config);
        $product    = $shopify->Product->get($params);
        return $product; 
    }

    public function postNewProduct($config, $params= []){
        $shopify    = Shopify::config($config);
        try {
            $product    = $shopify->Product->post($params);
            if($product){
                return ['status'=>1, 'action'=>'add', 'data'=>$product, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'action'=>'add', 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }


    public function updateExistProduct($config, $product_id, $params= []){
        $shopify    = Shopify::config($config);
        try {
            $product    = $shopify->Product($product_id)->put($params);
            if($product){
                return ['status'=>1, 'action'=>'update', 'data'=>$product, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'action'=>'update', 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function createWebhooks($config, $params){
        $shopify = Shopify::config($config);
        try {
            $webhook = $shopify->Webhook->post($params);
            if($webhook){
                return ['status'=>1, 'data'=>$webhook, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
        //return 
    }

    public function listWebhooks($config){
        $shopify = Shopify::config($config);
        try {
            $webhook = $shopify->Webhook->get();
            if($webhook){
                return ['status'=>1, 'data'=>$webhook, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function deleteWebhook($config,$webhookId){
        $shopify = Shopify::config($config);
        try {
            $webhook = $shopify->Webhook($webhookId)->delete();
            if($webhook){
                return ['status'=>1, 'data'=>$webhook, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function getOrderById($config,$orderId){
        $shopify = Shopify::config($config);
        try {
            $order = $shopify->Order($orderId)->get();
            if($order){
                return ['status'=>1, 'data'=>$order, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error!'.$e->getMessage()];
        }
    }

    public function postCreateCustomer($config, $params= []){
        $shopify    = Shopify::config($config);
        try {
            $customer    = $shopify->Customer->post($params);
            if($customer){
                return ['status'=>1, 'action'=>'add', 'data'=>$customer, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'action'=>'add', 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error! '.$e->getMessage()];
        }
    }

    public function putUpdateCustomer($customer_id, $params=[], $config){
        $shopify    = Shopify::config($config);
        try {
            $customer    = $shopify->Customer($customer_id)->put($params);
            if($customer){
                return ['status'=>1, 'action'=>'update', 'data'=>$customer, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'action'=>'update', 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error! '.$e->getMessage()];
        }
    }

    public function getCustomer($config,$searchParams=[]){
        $shopify = Shopify::config($config);
       
        try {
            $customer = $shopify->Customer->search($searchParams);
            if($customer){
                return ['status'=>1, 'data'=>$customer, 'message' => 'Success !'];
            } else{
                return ['status'=>0, 'data'=>[], 'message' => 'Error ! please verify valid ShopUrl, ApiKey  and Password '];
            }
        } catch(\Exception $e) { 
            return ['status'=>0, 'data'=>[], 'message' => 'Error! '.$e->getMessage()];
        }
    }
    
    public function getOrders($config, $filters = array()){
        $shopify = Shopify::config($config);
        $order = $shopify->Order->get($filters);
        return $order;
    }

    public function test(){
        $config = array(
            'ShopUrl' => 'apicenter-test-2.myshopify.com',
            'ApiKey' => '9ba09555deb3a02fba7208d2cd42ca84',
            'Password' => '037c38bad76b335497d754c409f43daf',
        );
        
        $shopify = Shopify::config($config);
        $products = $shopify->Product->get();
        print_r($products);
    }

    public function testGetOrders($config){
        $shopify = Shopify::config($config);
        $order = $shopify->Order->get();
        return $order;
    }


    public function testGetCustomers($config){
        $shopify = Shopify::config($config);
        $customer = $shopify->Customer->get();
        return $customer;
    }
}
