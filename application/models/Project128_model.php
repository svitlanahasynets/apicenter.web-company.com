<?php
class Project128_model extends CI_Model {

    public $projectId;
    
    function __construct()
    {
        parent::__construct();
        $this->projectId = 128;
    }

    public function getArticleData($product, $productData){
	    $productData['enabled'] = false;
		if(isset($product['attributes']) && !empty($product['attributes'])){
			foreach($product['attributes'] as $attribute){
				if($attribute['id'] == 'E1WEBSHOP' && ($attribute['value'] === true || strtolower($attribute['value']) == 'true' || $attribute['value'] == '1')){
					$productData['enabled'] = true;
					
					// Get categories
					$categories = array();
					foreach($product['attributes'] as $attribute){
						if($attribute['id'] == 'E1HEADCAT'){
							$categoryId = $this->Cms_model->findCategory($this->projectId, $attribute['description']);
							if(!$categoryId){
								$categoryId = $this->Cms_model->createCategory($this->projectId, $attribute['description']);
							}
							$categories[0] = $categoryId;
						}
						if($attribute['id'] == 'E1SUBCAT'){
							$categoryId = $this->Cms_model->findCategory($this->projectId, $attribute['description']);
							if(!$categoryId){
								$categoryId = $this->Cms_model->createCategory($this->projectId, $attribute['description'], $categories[0]);
							}
							$categories[1] = $categoryId;
						}
					}
					$productData['categories_ids'] = implode(',', array_unique($categories));
				}
			}
		}
        return $productData;
    }
} 