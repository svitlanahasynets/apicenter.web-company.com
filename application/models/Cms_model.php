<?php
class Cms_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }				
	
	function updatePrice($projectId, $priceList) {
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		}

		if(!$model){ return; }
		$model->updatePrice($projectId, $priceList);
		return;
	}

	function updateArticles($projectId, $articles){
		$cms = $this->Projects_model->getValue('cms', $projectId);

		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'cscart') {
			$this->load->model('Cscart_model');
			$model = $this->Cscart_model;
		} else if($cms == 'moodle') {
			$this->load->model('Moodle_model');
			$model = $this->Moodle_model;
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'shoptrader') {
			$this->load->model('Shoptrader_model');
			$model = $this->Shoptrader_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		}

		if(!$model){ return; }
		$model->updateArticles($projectId, $articles);
		return;
	}
	
	function updateStockArticles($projectId, $articles){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'shoptrader') {
			$this->load->model('Shoptrader_model');
			$model = $this->Shoptrader_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		}

		if(!$model){ return; }
		$model->updateStockArticles($projectId, $articles);
		return;
	}
	
	function removeArticles($projectId, $articles){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		/*
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'moodle') {
			$this->load->model('Moodle_model');
			$model = $this->Moodle_model;
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		}
		*/
		if(!$model){ return; }
		$model->removeArticles($projectId, $articles);
		return;
	}
	
	function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc'){
		$cms = $this->Projects_model->getValue('cms', $projectId);

		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'cscart') {
			$this->load->model('Cscart_model');
			$model = $this->Cscart_model;
		} else if($cms == 'shopware') {
			$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		} else if($cms == 'opencart') {
		    $this->load->model('Opencart_model');
			$model = $this->Opencart_model;
        } else if($cms == 'shoptrader') {
		    $this->load->model('Shoptrader_model');
			$model = $this->Shoptrader_model;
        } else if ($cms == 'WooCommerce') {
			$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
        } else if($cms == 'nopcommerce') {
		    $this->load->model('Nopcommerce_model');
			$model = $this->Nopcommerce_model;
        }
		//else if($cms == 'WooCommerce' && ($projectId == 83) ){
		//   $this->load->model('Woocommerce_model');
		//    $model = $this->Woocommerce_model;
		//}
		
        log_message('debug', 'CMS,GetOrder Ping '. var_export($projectId, true));
        
		if(!$model){
			return false;
		}
		$orders = $model->getOrders($projectId, $offset, $amount, $sortOrder);
// 		echo '<pre>';print_r($orders);exit;
		return $orders;
	}

	function updateCourses($projectId, $courses){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'moodle') {
			$this->load->model('Moodle_model');
			$model = $this->Moodle_model;
		}
		
		if(!$model){ return; }
		$model->updateCourses($projectId, $courses);
		return;
	}
	
	function findCategory($projectId, $categoryName){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} elseif($cms == 'WooCommerce'){
			$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'cscart') {
			$this->load->model('Cscart_model');
			$model = $this->Cscart_model;
		} else if($cms == 'optiply') {
		    return '1111'; //Hardcoded as Optiply has no categories.
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'shoptrader') {
			$this->load->model('Shoptrader_model');
			$model = $this->Shoptrader_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		}

		$category = $model->findCategory($projectId, $categoryName);

		if(!empty($category['items'])){
			return $category['items'][0]['id'];
		}
		return false;
	}
	
	function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} elseif($cms == 'WooCommerce'){
			$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'cscart') {
			$this->load->model('Cscart_model');
			$model = $this->Cscart_model;
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'shoptrader') {
			$this->load->model('Shoptrader_model');
			$model = $this->Shoptrader_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		}

		$category = $model->createCategory($projectId, $categoryName, $parentId, $image);
		if(!empty($category) && isset($category['id'])){
			return $category['id'];
		}
		return false;
	}
	
	public function createCustomer($projectId, $customerData){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		} else if($cms == 'lightspeed'){
        	$this->load->model('Lightspeed_model');
			$model = $this->Lightspeed_model;
		} else if($cms == 'shopify'){
        	$this->load->model('Shopify2_model');
			$model = $this->Shopify2_model;
		} else if($cms == 'cscart') {
			$this->load->model('Cscart_model');
			$model = $this->Cscart_model;
		} else if($cms == 'moodle') {
			$this->load->model('Moodle_model');
			$model = $this->Moodle_model;
		} else if($cms == 'shopware'){
        	$this->load->model('Shopware_model');
			$model = $this->Shopware_model;
		} else if($cms == 'prestashop') {
			$this->load->model('Prestashop_model');
			$model = $this->Prestashop_model;
		}
        
        log_message('debug', 'CMS,GetCustoemr Ping '. var_export($projectId, true));
		$model->createCustomer($projectId, $customerData);
		
		return;
	}
	
	public function updateSuppliers($projectId, $suppliersData) {
        $cms = $this->Projects_model->getValue('cms', $projectId);
        $wms = $this->Projects_model->getValue('wms', $projectId);
        $model = false;

        if($cms == 'optiply' || $wms == 'optiply'){
            $this->load->model('Optiply_model');
            $model = $this->Optiply_model;
        } else {
            return;
        }

        $model->updateSuppliers($projectId, $suppliersData);
        return;
    }

    
    public function updateOrders($projectId, $orderData) {
	    $cms = $this->Projects_model->getValue('cms', $projectId);
	    $wms = $this->Projects_model->getValue('wms', $projectId);
	    $model = false;
	    
        if($cms == 'optiply' || $wms == 'optiply'){
	       $this->load->model('Optiply_model');
	       $model = $this->Optiply_model;
	    } else {
	       return;
	    }
        $model->updateOrders($projectId, $orderData);
	
        return;
    }
    
    public function updateBuyOrders($projectId, $orderData, $updateBuyOrders = '0') {
        $cms = $this->Projects_model->getValue('cms', $projectId);
        $wms = $this->Projects_model->getValue('wms', $projectId);
        $model = false;

        if($cms == 'optiply' || $wms == 'optiply'){
            $this->load->model('Optiply_model');
            $model = $this->Optiply_model;
        } else {
            return;
        }

        $model->updateBuyOrders($projectId, $orderData, $updateBuyOrders = '0');
        return;
    }

	public function checkLoginCredentials($cms = '', $data = array()){
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} elseif ($cms == 'optiply') {
            $this->load->model('Optiply_model');
            $model = $this->Optiply_model;
        }
		return $model->checkLoginCredentials($data);
	}
	
	public function getAttributesForMappingTable($projectId){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		}
		
		if(!$model){ return array(); }
		return $model->getAttributesForMappingTable($projectId);
	}
	
	public function applyMappedAttributes($projectId, $articleData, $finalArticleData){
		$cms = $this->Projects_model->getValue('cms', $projectId);
		$model = false;
		if($cms == 'magento2'){
			$this->load->model('Magento2_model');
			$model = $this->Magento2_model;
		} else if($cms == 'WooCommerce'){
        	$this->load->model('Woocommerce_model');
			$model = $this->Woocommerce_model;
		}
		
		if(!$model){ return $finalArticleData; }
		return $model->applyMappedAttributes($projectId, $articleData, $finalArticleData);
	}

}