<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/woorestapi/vendor/autoload.php';

use Automattic\WooCommerce\Client;


class Woo_restapi {

    public  $params;
	public  $woocommerce;

    public function __construct($params = ''){
        if($params!='')
    	   $this->params 		= $params; // ,'query_string_auth' => true
    }

    public function restClientCall(){
        $this->woocommerce  = new Client($this->params['url'],$this->params['customer_key'],$this->params['customer_sec'],['wp_api'=>$this->params['wp_api'],'version'=>$this->params['version']]);
    }

    // public function postWebhooks($data){
        // $this->restClientCall();
        // $result = $this->woocommerce->post('webhooks', $data);
        // return $result;
    // }

    public function getCustomerById($customerId){
        $this->restClientCall();
        $result = $this->woocommerce->get("customers/$customerId");
        return $result;
    }

    // public function deleteWebhooks($id){
        // $this->restClientCall();
        // $result = $this->woocommerce->delete("webhooks/$id");
        // return $result;
    // }

    public function postCategory($category, $params=''){
		// Check if category exists
/*
		if(!is_numeric($category['name'])){
			$findCategory = $this->getCategories($category['name']);
			if($findCategory != false){
				$result_return = array();
                $result_return['result']    = $findCategory;
                $result_return['status']    = 1;
                return $result_return;
			}
		}
*/
		
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result       = $woocommerce->post('products/categories', $category);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->post('products/categories', $category);
        }
        sleep(1);                               // sleep for 1 sec as one api call in 1 sec
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result->id))
            $result_return['id']        = $result->id;
        else if(isset($result->data->status) && $result->data->status == 400){
            $result_return['id']        = $result->data->resource_id;
        } else if(isset($result->code) && $result->code == 400){
            $data = ['search'=>$category['name']];
            if($params!=''){
                $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
                $result1       = $woocommerce->get('products/categories',$data);
            } else{
                $this->restClientCall();
                $result1 = $this->woocommerce->get('products/categories',$data);
            }
            if(isset($result1[0]->id)){
                $id = '';
                foreach ($result1 as $key => $value) {
                    if(strtolower($category['name']) == strtolower($value->name))
                        $id = $value->id;
                }
                if($id!='')
                    $result_return['id'] = $id;
            } else{
                $result_return['result']    = $result;
                $result_return['status']    = 0;
            }
        } else{
            $result_return['result']    = $result;
            $result_return['status']    = 0;
        }
        return $result_return;
    }

    public function postProduct($product, $params=''){

        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result       = $woocommerce->post('products', $product);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->post('products', $product);
        }
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result->id)){
            $result_return['id']        = $result->id;
            $result_return['action']    = 'add';
        } else if(isset($result->code) && $result->code == 'product_invalid_sku'){
            sleep(1);
            $result_return['id']        = $result->data->resource_id;
            $result_return['action']    = 'update';
            $updateArticle              = $this->putProduct($product, $result->data->resource_id, $params);
            $result_return['status']    = $updateArticle['status'];
            $result_return['result']    = $updateArticle['result'];
        } else if(isset($result->code) && $result->code == 400){
            $data = ['per_page'=>50, 'sku'=>$product['sku']];
            if($params!=''){
                $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
                $result1       = $woocommerce->get('products', $data);
            } else{
                $this->restClientCall();
                $result1 = $this->woocommerce->get('products', $data);
            }
            if(isset($result1[0]) && isset($result1[0]->id)){
                $id = '';
                foreach ($result1 as $key => $value) {
                    if($product['sku'] == $value->sku)
                        $id = $value->id;
                }
                if($id!=''){
                    $result_return['id'] = $id;
                    $result_return['action']    = 'update';
                    $updateArticle              = $this->putProduct($product, $id, $params);
                    $result_return['status']    = $updateArticle['status'];
                    $result_return['result']    = $updateArticle['result'];
                } else{
                    $result_return['status']    = 0;
                }
            } else{
                $result_return['result']    = $result1;
                $result_return['status']    = 0;
            }
        } else {
            $result_return['result']    = $result;
            $result_return['status']    = 0;
        }
        return $result_return;
    }

    public function putProductStock($product, $params='', $articleId ){

        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result1 = $woocommerce->post("products/$articleId", $product);
        } else{
            $this->restClientCall();
            $result1 = $this->woocommerce->post("products/$articleId", $product);
        }
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result1->id)){
            $result_return['id']        = $result1->id;
            $result_return['result']    = 'Product updated successfully';
        }
        else {
            $result_return['result']    = $result1->message;
            $result_return['status']    = 0;
        }
        return $result_return;
    }

    public function putProduct($product, $productId, $params=''){
        if(isset($product['type']))
            unset($product['type']);
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result1 = $woocommerce->post("products/$productId", $product);
        } else{
            $this->restClientCall();
            $result1 = $this->woocommerce->post("products/$productId", $product);
        }
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result1->id)){
            $result_return['id']        = $result1->id;
            $result_return['result']    = 'Product updated successfully';
        }
        else {
            $result_return['result']    = $result1->message;
            $result_return['status']    = 0;
        }
        return $result_return;
    }

    public function checkProductSku($product,$params=''){
        if(isset($product['model']))
            $data = ['per_page'=>50, 'sku'=>$product['model']];
        else
            $data = ['per_page'=>50, 'search'=>$product['name']];
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result1       = $woocommerce->get('products',$data);
        } else{
            $this->restClientCall();
            $result1 = $this->woocommerce->get('products',$data);
        }
        sleep(1);
        if(is_array($result1) && isset($result1[0]->id)){
            return $result1[0]->id;
        } else{
			log_message('debug', "error checkProductSku: ".var_export($result1, true));
			log_message('debug', "data checkProductSku: ".var_export($data, true));
            return false;
        }
        return false;
    }

	public function getCustomer($data, $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result       = $woocommerce->get('customers', $data);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get('customers', $data);
        }
		return $result;
	}

    public function postCustomer($customer, $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result                     = $woocommerce->post('customers', $customer);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->post('customers', $customer);
        }
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result->id)){
            $result_return['id']        = $result->id;
            $result_return['action']    = 'add';
        } else {
            $result_return['result']    = $result->message;
            $result_return['status']    = 0;
        }
        return $result_return;
    }

	public function putCustomer($customerId, $customer, $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result                     = $woocommerce->post("customers/$customerId", $customer);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->post("customers/$customerId", $customer);
        }
		$result_return                  = array();
		$result_return['status']        = 1;
		if(isset($result->id)){
			$result_return['id']        = $result->id;
			$result_return['action']    = 'update';
		} else {
			$result_return['result']    = $result->message;
			$result_return['status']    = 0;
		}
		return $result_return;
	}


    public function getProducts( $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result = $woocommerce->get("orders");
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get("orders");
        }
        return $result;
    }


    public function getOrders($orderId, $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result = $woocommerce->get("orders");
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get("orders");
        }
        return $result;
    }
    
    
    public function getCategories($categoryName, $params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result = $woocommerce->get("products/categories", array('search' => $categoryName));
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get("products/categories", array('search' => $categoryName));
        }
        return $result;
    }
    
    
    public function createCategory($category, $params=''){

        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result       = $woocommerce->post('products/categories', $category);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->post('products/categories', $category);
        }
        $result_return                  = array();
        $result_return['status']        = 1;
        if(isset($result->id)){
            $result_return['id']        = $result->id;
            $result_return['action']    = 'add';
        } else {
            $result_return['result']    = $result;
            $result_return['status']    = 0;
        }
        return $result_return;
    }
    

    public function getOrdersWithFilters($filters = array()){
        $this->restClientCall();
        $params = array(
	        'consumer_key' => $this->params['customer_key'],
	        'consumer_secret' => $this->params['customer_sec']
        );
        $params = array_merge($params, $filters);
        $result = $this->woocommerce->get("orders", $params);
        return $result;
    }


    // public function getWebhooks($dataq=''){
        // $this->restClientCall();
        // // $data = ['status' => 'active'];
        // // $result = $this->woocommerce->post('webhooks', $dataq);
        // // print_r($result);
        // print_r($this->woocommerce);
        // $resultq = $this->woocommerce->get("webhooks");
        // //$result = $this->woocommerce->delete('webhooks/3712');
        // return $resultq;
    // }

    public function getAttributesForMappingTable($params=''){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result = $woocommerce->get("products/attributes");
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get("products/attributes");
        }
        //echo '<pre>';print_r($result);echo '</pre>';exit;
        return $result;
    }

    public function getOrdersList($params='', $filters=[]){
        if($params!=''){
            $woocommerce  = new Client($params['url'],$params['customer_key'],$params['customer_sec'],['wp_api'=>$params['wp_api'],'version'=>$params['version']]);
            $result = $woocommerce->get("orders", $filters);
        } else{
            $this->restClientCall();
            $result = $this->woocommerce->get("orders", $filters);
        }
        return $result;
    }
}
