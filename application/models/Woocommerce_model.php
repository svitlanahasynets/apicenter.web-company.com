<?php
class Woocommerce_model extends CI_Model {
    public $apiURL;
    public $projectId;

    function __construct(){
        parent::__construct();
    }

    #############################################################################################
    #           Function is used to set all required params to make woocommerce api connection. #
    #############################################################################################
    public function woocommerceConnectionParams($projectId){

        $this->load->model('Projects_model');
        $project        = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $store_url      = '';
        if(!empty($project)){
            $store_url  = $project['store_url'];
        }
        $woocommerce_api_consumer_key       = $this->Projects_model->getValue('woocommerce_api_consumer_key', $projectId)?$this->Projects_model->getValue('woocommerce_api_consumer_key', $projectId):'';
        $woocommerce_api_consumer_secret    = $this->Projects_model->getValue('woocommerce_api_consumer_secret', $projectId)?$this->Projects_model->getValue('woocommerce_api_consumer_secret', $projectId):'';
        $woocommerce_version    = $this->Projects_model->getValue('woocommerce_version', $projectId)?$this->Projects_model->getValue('woocommerce_version', $projectId):2.6;

        $store_url              = str_replace('/index.php/', '', $store_url);// rtrim($store_url,"/index.php/");
        $store_url              = str_replace('/index.php', '', $store_url);//rtrim($store_url,"/index.php");
		if(substr($store_url, -1) == '/') {
		    $store_url = substr($store_url, 0, -1);
		}
//         $store_url              = rtrim($store_url,"/");
        $store_url              = $store_url.'/index.php';
        $params                 = array();
        $params['url']          = $store_url;
        $params['customer_key'] = $woocommerce_api_consumer_key;
        $params['customer_sec'] = $woocommerce_api_consumer_secret;
        if($woocommerce_version!=2.6){
            $params['wp_api']   = false;
            $params['version']  = 'v3';
        } else{
            $params['wp_api']   = true;
            $params['version']  = 'wc/v2';
        }

        return $params;
    }

    ####################################################################################################
    #   Used to import products to woocommerce from exactonline by aclling createWoocommerceArticle  . #
    ###################################################################################################
    public function importArticleInWoocommerce($items, $projectId, $method = 'post'){
        
        $this->load->model('Projects_model');
        $this->load->library('Woo_restapi');
        $params          = $this->woocommerceConnectionParams($projectId);
        $offset          = '';
        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
        
        foreach ($items as $i_key => $i_value) {
            if ($method == 'stock_update') {
                $checkArticle  = $this->checkWoocommerceArticle($i_value, $params);
                if($checkArticle)
                    $createArticle = $this->updateWoocommerceStock($i_value, $params, $checkArticle);
                else
                    $createArticle = $this->createWoocommerceArticle($i_value, $params);
            } else if ($method = 'post'){
                $createArticle = $this->createWoocommerceArticle($i_value, $params);
            }
            $offset    = $i_value['Id'];
            if(isset($createArticle['status']) && $createArticle['status']==1){
                $totalArticleImportSuccess++;
                if($createArticle['action']=='add'){
                    apicenter_logs($projectId, 'importarticles',"Success :: Article  : ".$i_value['model'] ." imported successfully , resource_id- ".$createArticle['id'], false);
                } else {
                    apicenter_logs($projectId, 'importarticles',"Success :: Article  : ".$i_value['model'] ." updated successfully , resource_id- ".$createArticle['id'], false);
                }
            } else if(isset($createArticle['status']) && $createArticle['status']==2){
                continue;
            } else{
                $totalArticleImportError++;
				//log_message('error', 'Failed to import article from function importArticleInWoocommerce for project '.$projectId);
				//log_message('error', 'Product: '.var_export($i_value, true));
				//log_message('error', 'Result: '.var_export($createArticle, true));
                
                $message = " Failed to import article: ".$i_value['model'];
                if(isset($createArticle['result']->message))
                    $message = $message.' Error Message : '.$createArticle['result']->message;
                apicenter_logs($projectId, 'importarticles', "Error :: ".$message, true);
            }
        }
        if ($method == 'post')
            $this->Projects_model->saveValue('article_offset', $offset, $projectId);
        else 
            $this->Projects_model->saveValue('article_update_offset', $offset, $projectId);
        $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
    }

    public function formatARticleForWoocommerce($projectId, $items){
        $formated_item = $items;
        $is_published = $this->Projects_model->getValue('import_as_published', $projectId);
        $impost_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
        $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
        $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
        if($is_published)
            $formated_item['status']             = 'publish';
        else
            $formated_item['status']             = 'pending';
        if($import_exact_description==1){
            if(isset($items['ExtraDescription']) && $items['ExtraDescription']!='')
                $formated_item['short_description']  = $items['ExtraDescription'];
            else
                $formated_item['short_description']  = $items['Description'];
        }
        if($import_exact_extra_description==1)
            $formated_item['description']        = isset($items['Notes'])?$items['Notes']:'' ;

        if($woocommerce_stock_options==1)
            $formated_item['manage_stock']       = 1;
        else
            $formated_item['manage_stock']       = 0;
        $formated_item['stock']                  = isset($items['quantity'])?$items['quantity']:0;
        $formated_item['type']                   = 'simple';
        
        if ($projectId == 36)
        {
            //log_message('error', 'WOO ARRAY PID 36: '.var_export($items, true));
            
            //if (isset($items['custom_attributes']['Verzendklasse'])){
                
              //  $formated_item['Verzendklasse'] = 
            //$formated_item['custom_attributes']['Verzendklasse'] $finalData['custom_attributes']['Verzendklasse'] = array(
                
            //}
        }
        
        return $formated_item;
    }

    ####################################################################################################
    # used to import products to woocommerce from exactonline by aclling createWoocommerceArticle  . #
    #####################################################################################################
    public function updateArticles($projectId, $items){
        $this->load->model('Projects_model');
        $this->load->library('Woo_restapi');
        $params          = $this->woocommerceConnectionParams($projectId);
        $offset          = '';
        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
        
        // if ($projectId == 85) {log_message('debug', "articles 85 in exportWoo\n". var_export($items, true));}
        
        
        foreach ($items as $i_key => $i_value) {
            // if ($projectId == 85) {log_message('debug', "articles 85 in exportCHECKSKU_PREV\n". var_export($i_value, true));}
            
            $checkArticle  = $this->checkWoocommerceArticle($i_value, $params);
            
            // if ($projectId == 85) {log_message('debug', "articles 85 in exportCHECKSKU\n". var_export($checkArticle, true));}
            
            $article = $this->formatARticleForWoocommerce($projectId, $i_value);
            // if ($projectId == 85) {log_message('debug', "articles 85 in exportFORMAT\n". var_export($article, true));}
            
            $WooCommerceType = 0;
            
            if($checkArticle){
                $createArticle = $this->updateWoocommerceArticle($article, $params , $checkArticle, $projectId);
                $WooCommerceType = 2;
                
                // if ($projectId == 85) {log_message('debug', "articles 85 in exportType2\n". var_export($createArticle, true));}
                
            } else{
                $createArticle = $this->createWoocommerceArticle($article, $params, $projectId);
                // if ($projectId == 85) {log_message('debug', "articles 85 in exportType1\n". var_export($createArticle, true));}
                
                $WooCommerceType = 1;
            }
            
            // if ($projectId == 85) {log_message('debug', "articles 85 result ". var_export($createArticle, true));}
            
            
            if(isset($createArticle['status']) && $createArticle['status']==1)
            {
                $totalArticleImportSuccess++;
                if($createArticle['action']=='add' || $WooCommerceType == 1)
                {
                    apicenter_logs($projectId, 'importarticles',"Success :: Article  : ".$i_value['model'] ." imported successfully , resource_id- ".$createArticle['id'], false);
					
					// Load project specific data
					$projectModel = 'Project'.$projectId.'_model';
					if(file_exists(APPPATH."models/".$projectModel.".php")){
						$this->load->model($projectModel);
						if(method_exists($this->$projectModel, 'createProductAfter')){
							$saveData = $this->$projectModel->createProductAfter($createArticle, $article, $projectId);
						}
					}
                }
                else
                {
                    apicenter_logs($projectId, 'importarticles',"Success :: Article  : ".$i_value['model'] ." updated successfully , resource_id- ".$createArticle['id'], false);
					
					// Load project specific data
					$projectModel = 'Project'.$projectId.'_model';
					if(file_exists(APPPATH."models/".$projectModel.".php")){
						$this->load->model($projectModel);
						if(method_exists($this->$projectModel, 'updateProductAfter')){
							$saveData = $this->$projectModel->updateProductAfter($createArticle, $article, $projectId);
						}
					}
                }
            } 
            else if(isset($createArticle['status']) && $createArticle['status']==2){
                continue;
            } 
            else
            {
                $totalArticleImportError++;
                $message = " Failed to import article: ".$i_value['model'];
                if(isset($createArticle['result']->message))
                    $message = $message.' Error Message : '.$createArticle['result']->message;
                apicenter_logs($projectId, 'importarticles', "Error :: ".$message, true);
            }
        }
        $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
    }

    ################################################################################################
    # Used to import products to woocommerce from exactonline by aclling createWoocommerceArticle .#
    ################################################################################################
    public function updateStockArticles($projectId, $items){

        $this->load->model('Projects_model');
        $this->load->library('Woo_restapi');
        $params          = $this->woocommerceConnectionParams($projectId);
        $offset          = '';
        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;

		//log_message('debug', 'Array Stock? ' . var_export($items, true));
        
        foreach ($items as $item){
            //log_message('debug', 'Array Stock? -NewFor: ' . var_export($item, true));
            
            $checkArticle = $this->checkWoocommerceArticle($item, $params);
            //log_message('debug', 'Array Stock? -NewFor - check: ' . var_export($checkArticle, true));
            
            $createArticle = $this->updateWoocommerceStock($item, $params, $checkArticle);
            //log_message('debug', 'Array Stock? -NewFor - create: ' . var_export($createArticle, true));
            
            if( isset($createArticle['status']) && $createArticle['status'] == 1 ) {
                api2cart_log($projectId, 'importarticles',"Success :: Stock of Article  : ".$item['model'] ." stock updated successfully , resource_id- ".$createArticle['id']);
            }
			
			//if($checkArticle){
                //$createArticle = $this->updateWoocommerceStock($item, $params, $checkArticle);
                
                //log_message('debug', 'Array Stock? -NewFor - create: ' . var_export($createArticle, true));
                
                //if( isset($createArticle['status']) && $createArticle['status'] == 1 ){
                    //$totalArticleImportSuccess++;
                    //if( $createArticle['action'] == 'add' ) {
                    //    project_error_log($projectId, 'importarticles',"Success :: Stock of Article  : ".$item['model'] ." imported successfully , resource_id- ".$createArticle['id']);
                    //}
                    //else {
                        //project_error_log($projectId, 'importarticles',"Success :: Stock of Article  : ".$item['model'] ." stock updated successfully , resource_id- ".$createArticle['id']);
                    //}
                //}
                //else if(isset($createArticle['status']) && $createArticle['status'] == 2) {
                //    continue;
                //} else {
                //    $totalArticleImportError++;
					//log_message('error', 'Failed to import article from function updateStockArticles for project '.$projectId);
					//log_message('error', 'Product: '.var_export($i_value, true));
					//log_message('error', 'Result: '.var_export($createArticle, true));
                //    $message = " Failed to import article: ".$item['model'];
                //    if(isset($createArticle['result']->message))
                //        $message = $message.' Error Message : '.$createArticle['result']->message;
                //    project_error_log($projectId, 'importarticles', "Error :: ".$message);
                //}
            //}
        }

		$this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
        

        /*foreach ($items as $i_key => $i_value) 
        {
            $checkArticle  = $this->checkWoocommerceArticle($i_value, $params);
            
            //log_message('debug', 'Array Stock? -checkArticle: ' . $i_value['model'] . ' = ' . var_export($checkArticle, true));
                        
            if($checkArticle)
            {
                $createArticle = $this->updateWoocommerceStock($i_value, $params , $checkArticle);
                
                //log_message('debug', 'Array Stock? -UpdateStockResponse: ' . $i_value['model'] . ' = ' . var_export($createArticle, true));
                                
                if(isset($createArticle['status']) && $createArticle['status']==1)
                {
                    $totalArticleImportSuccess++;
                    if($createArticle['action']=='add')
                    {
                        apicenter_logs($projectId, 'importarticles',"Success :: Stock of Article  : ".$i_value['model'] ." imported successfully , resource_id- ".$createArticle['id'], false);
					}
                    else
                    {
                        apicenter_logs($projectId, 'importarticles',"Success :: Stock of Article  : ".$i_value['model'] ." stock updated successfully , resource_id- ".$createArticle['id'], false);
                    }
                }
            	/* 
                else if(isset($createArticle['status']) && $createArticle['status']==2)
                {
                    continue;
                } else{
                    $totalArticleImportError++;
					//log_message('error', 'Failed to import article from function updateStockArticles for project '.$projectId);
					//log_message('error', 'Product: '.var_export($i_value, true));
					//log_message('error', 'Result: '.var_export($createArticle, true));
                    $message = " Failed to import article: ".$i_value['model'];
                    if(isset($createArticle['result']->message))
                        $message = $message.' Error Message : '.$createArticle['result']->message;
                    apicenter_logs($projectId, 'importarticles', "Error :: ".$message, true);
                }
            	*/
            //}
        //}
    }

    ###############################################################################################
    # Function is used to create or update  article  and article category in woocommerce. #
    ##############################################################################################
    public function createWoocommerceArticle($item_details, $params, $projectId = ''){
        $item_detail                            = array();
        $item_detail['name']                    = $item_details['name'];
        $item_detail['type']                    = $item_details['type'];
        $item_detail['status']                  = isset($item_details['status'])?$item_details['status']:'pending';
        $item_detail['manage_stock']            = $item_details['manage_stock'];
        if($item_details['manage_stock']==0)
            $item_detail['in_stock']            = 1;

        $item_detail['sku']                     = $item_details['model'];
        $item_detail['price']                   = "'".$item_details['price']."'";
        $item_detail['regular_price']           = "'".$item_details['price']."'";
        if(isset($item_details['description']) && $item_details['description'] !='')
            $item_detail['description']         = $item_details['description'];
        if(isset($item_details['short_description']) && $item_details['short_description'] != '')
            $item_detail['short_description']   = $item_details['short_description'];
        if($item_details['manage_stock']==1)
            $item_detail['stock_quantity']      = $item_details['stock'];
        if (isset($item_details['on_sale']) && $item_details['on_sale'] != '') {
            $item_detail['on_sale']                 = $item_details['on_sale'];
        }
        if (isset($item_details['purchasable']) && $item_details['purchasable'] != '') {
            $item_detail['purchasable']                 = $item_details['purchasable'];
        }
        $images = array();
        if (isset($item_details['image']) && $item_details['image'] != '') {
            $image = [
                        //'id'        => ($item_detail['sku'] . "0"),
                        'src'       => $item_details['image']['url'],
                        'name'      => $item_details['image']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image;
        }
        if (isset($item_details['image_1']) && $item_details['image_1'] != '') {
            $image_1 = [
                        //'id'        => ($item_detail['sku'] . "1"),
                        'src'       => $item_details['image_1']['url'],
                        'name'      => $item_details['image_1']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_1;
        }
        if (isset($item_details['image_2']) && $item_details['image_2'] != '') {
            $image_2 = [
                        //'id'        => ($item_detail['sku'] . "2"),
                        'src'       => $item_details['image_2']['url'],
                        'name'      => $item_details['image_2']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_2;
        }
        if (isset($item_details['image_3']) && $item_details['image_3'] != '') {
            $image_3 = [
                        //'id'        => ($item_detail['sku'] . "3"),
                        'src'       => $item_details['image_3']['url'],
                        'name'      => $item_details['image_3']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_3;
        }
        if (isset($item_details['image_4']) && $item_details['image_4'] != '') {
            $image_4 = [
                        //'id'        => ($item_detail['sku'] . "4"),
                        'src'       => $item_details['image_4']['url'],
                        'name'      => $item_details['image_4']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_4;
        }
        if (isset($item_details['image_5']) && $item_details['image_5'] != '') {
            $image_5 = [
                        //'id'        => ($item_detail['sku'] . "5"),
                        'src'       => $item_details['image_5']['url'],
                        'name'      => $item_details['image_5']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_5;
        }
        if (isset($item_details['image_6']) && $item_details['image_6'] != '') {
            $image_6 = [
                        //'id'        => ($item_detail['sku'] . "6"),
                        'src'       => $item_details['image_6']['url'],
                        'name'      => $item_details['image_6']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_6;
        }

        if(!empty($images))
            $item_detail['images'] = $images;

        if(isset($item_details['categories_ids']) && $item_details['categories_ids'] != ''){
            $categories                         = [['id'=>$item_details['categories_ids']]];
            $item_detail['categories']          = $categories;
        }
        if(isset($item_details['attributes']) && $item_details['attributes']!='')
            $item_detail['attributes'] = $item_details['attributes'];

		if(isset($item_details['custom_attributes'])){
			foreach($item_details['custom_attributes'] as $attributeCode => $attribute){
				$item_detail[$attributeCode] = $attribute['value'];
			}
		}

		// Load project specific data
		if($projectId > 0){
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'checkConfigurable')){
					$item_detail = $this->$projectModel->checkConfigurable($item_detail, $item_details, $projectId, 'create');
				}
			}
		}

        return $this->woo_restapi->postProduct($item_detail, $params);
    }

    public function updateWoocommerceArticle($item_details, $params, $articleId, $projectId = ''){
		
		//log_message('error', 'Update WC article with details: ' . $projectId . '-->\n ' . var_export($item_details, true));
		
        $item_detail                            = array();
        $item_detail['name']                    = $item_details['name'];
        $item_detail['status']                  = isset($item_details['status'])?$item_details['status']:'pending';
        $item_detail['manage_stock']            = $item_details['manage_stock'];
        if($item_details['manage_stock']==0)
            $item_detail['in_stock']            = 1;
        $item_detail['sku']                     = $item_details['model'];
        $item_detail['price']                   = "'".$item_details['price']."'";
        $item_detail['regular_price']           = "'".$item_details['price']."'";
        if(isset($item_details['description']) && $item_details['description'] !='')
            $item_detail['description']         = $item_details['description'];
        if(isset($item_details['short_description']) && $item_details['short_description'] != '')
            $item_detail['short_description']   = $item_details['short_description'];
        if($item_details['manage_stock']==1)
            $item_detail['stock_quantity']      = $item_details['stock'];
        if (isset($item_details['on_sale']) && $item_details['on_sale'] != '') {
            $item_detail['on_sale']                 = $item_details['on_sale'];
        }
        if (isset($item_details['purchasable']) && $item_details['purchasable'] != '') {
            $item_detail['purchasable']                 = $item_details['purchasable'];
        }
        $images = array();
        if (isset($item_details['image']) && $item_details['image'] != '') {
            $image = [
                        //'id'        => ($item_detail['sku'] . "0"),
                        'src'       => $item_details['image']['url'],
                        'name'      => $item_details['image']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image;
        }
        if (isset($item_details['image_1']) && $item_details['image_1'] != '') {
            $image_1 = [
                        //'id'        => ($item_detail['sku'] . "1"), 
                        'src'       => $item_details['image_1']['url'],
                        'name'      => $item_details['image_1']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_1;
        }
        if (isset($item_details['image_2']) && $item_details['image_2'] != '') {
            $image_2 = [
                        //'id'        => ($item_detail['sku'] . "2"),
                        'src'       => $item_details['image_2']['url'],
                        'name'      => $item_details['image_2']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_2;
        }
        if (isset($item_details['image_3']) && $item_details['image_3'] != '') {
            $image_3 = [
                        //'id'        => ($item_detail['sku'] . "3"),
                        'src'       => $item_details['image_3']['url'],
                        'name'      => $item_details['image_3']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_3;
        }
        if (isset($item_details['image_4']) && $item_details['image_4'] != '') {
            $image_4 = [
                        //'id'        => ($item_detail['sku'] . "4"),
                        'src'       => $item_details['image_4']['url'],
                        'name'      => $item_details['image_4']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_4;
        }
        if (isset($item_details['image_5']) && $item_details['image_5'] != '') {
            $image_5 = [
                        //'id'        => ($item_detail['sku'] . "5"),
                        'src'       => $item_details['image_5']['url'],
                        'name'      => $item_details['image_5']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_5;
        }
        if (isset($item_details['image_6']) && $item_details['image_6'] != '') {
            $image_6 = [
                        //'id'        => ($item_detail['sku'] . "6"),
                        'src'       => $item_details['image_6']['url'],
                        'name'      => $item_details['image_6']['image_name'],
                        //'position'  => 0
                    ];
            $images[] = $image_6;
        }

        if(!empty($images))
            $item_detail['images'] = $images;

        if(isset($item_details['categories_ids']) && $item_details['categories_ids'] != ''){
            $categories                         = [['id'=>$item_details['categories_ids']]];
            $item_detail['categories']          = $categories;
        }
        if(isset($item_details['attributes']) && $item_details['attributes']!='')
            $item_detail['attributes'] = $item_details['attributes'];

		if(isset($item_details['custom_attributes'])){
			foreach($item_details['custom_attributes'] as $attributeCode => $attribute){
				$item_detail[$attributeCode] = $attribute['value'];
			}
		}

		// Load project specific data
		if($projectId > 0){
			$projectModel = 'Project'.$projectId.'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'checkConfigurable')){
					$item_detail = $this->$projectModel->checkConfigurable($item_detail, $item_details, $projectId, 'create');
				}
			}
		}

		//log_message('error', 'Update WC article with processed details: '.var_export($item_detail, true));
        return $this->woo_restapi->putProduct($item_detail, $articleId, $params);
    }

    public function updateWoocommerceStock($item_details,$params, $articleId){

		//log_message('debug', 'ProductStockData AFAS - XML ' . var_export($item_details, true));
        
        if($articleId>0){
            $item_detail = array();
            $item_detail['sku']                 = $item_details['model']; 
            //if($item_details['stock']>0 || $item_details['manage_stock']==0) {
                $item_detail['in_stock']        = 1;
            //} 
            $item_detail['manage_stock']        = 1; 
            //if($item_details['manage_stock']==1)
                $item_detail['stock_quantity']      = $item_details['quantity'];
            //$item_detail['price']                   = "'".$item_details['price']."'";
            //$item_detail['regular_price']           = "'".$item_details['price']."'";
            return $this->woo_restapi->putProductStock($item_detail, $params, $articleId);
        } else
            return false;        
    }

    public function checkWoocommerceArticle($item_detail, $params){
        return $this->woo_restapi->checkProductSku($item_detail, $params);
    }

    public function removeArticles(){
        return true;
    }

    ###########################################################################################
    #        Function is used to create   customer in woocommerce.                         #
    ###########################################################################################
    public function createCustomer($projectId, $customers,  $offset_key = ''){
        $this->load->model('Projects_model');
        $this->load->library('Woo_restapi');
        $params             = $this->woocommerceConnectionParams($projectId);
        $offset             = '';
        $totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
        $totalCustomerImportError = $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;
        $already_exist      = array();

        $data = ['email'=>$customers['email']];
        $customerData = $this->formatCustomerFromAfas($customers);
        $getCustomer  = $this->woo_restapi->getCustomer($data, $params);
        if(isset($getCustomer->customers)){
	        $getCustomer = $getCustomer->customers;
        }
        sleep(1);
        if($getCustomer){
            $customer_id  = '';
            if($getCustomer){
                $customer_id  = (isset($getCustomer[0]) && isset($getCustomer[0]->id))?$getCustomer[0]->id:'';
            }
            if($customer_id!=''){
                $updateCustomer = $this->woo_restapi->putCustomer($customer_id, $customerData, $params);
                if($updateCustomer['status']!=1){
                    $totalCustomerImportError++;
                    $message = " Failed to import customer email -: ".$customers['email'];
                    if(isset($updateCustomer['result']->message))
                        $message = $message.', Error Message : '.$customers['result']->message;
                    apicenter_logs($projectId, 'importcustomers',$message, true);
                    //apicenter_logs($projectId, 'importcustomers',var_export($updateCustomer, true), true);
                } else{
                    $totalCustomerImportSuccess++;
                    apicenter_logs($projectId, 'importcustomers',"customer -: ".$customers['email'].' updated successfully , resource_id - '.$updateCustomer['id'], false);
                }
            }
        } else{
            $createCustomer = $this->woo_restapi->postCustomer($customerData, $params);
            if($createCustomer['status']==1){
                $totalCustomerImportSuccess++;
                if($createCustomer['action']=='add')
                    apicenter_logs($projectId, 'importcustomers',"customer -: ".$customers['email'].' imported successfully , resource_id - '.$createCustomer['id'], false);
            } else{
              
                $totalCustomerImportError++;
                $message = " Failed to import customer email -: ".$customers['email'];
                if(isset($createCustomer['result']->message))
                    $message = $message.', Error Message : '.$createCustomer['result']->message;
                apicenter_logs($projectId, 'importcustomers',$message, true);
                //apicenter_logs($projectId, 'importcustomers',var_export($createCustomer, true), true);
            }
        }
        $this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
    }

    public function formatCustomerFromAfas($afasCustomerData){
        $customerData = array(
            'email'         => $afasCustomerData['email'],
            'first_name'    => $afasCustomerData['first_name'],
            'last_name'     => $afasCustomerData['last_name'],
            'role'          => 'customer',
            'password'      => 'P!O@I#U$',
            'username'      => $afasCustomerData['email']
        );

        $billing_address = array(
            "first_name"    => $afasCustomerData['first_name'],
            "last_name"     => $afasCustomerData['last_name'],
            //"company"       => "",
            "address_1"     => $afasCustomerData['address'] ? $afasCustomerData['address'] : 'Geen adres',
            "country"       => $afasCustomerData['country'],
            "email"         => $afasCustomerData['email']
        );
        $shipping_address = array(
            "first_name"    => $afasCustomerData['first_name'],
            "last_name"     => $afasCustomerData['last_name'],
            //"company"       => "",
            "address_1"     => $afasCustomerData['address'] ? $afasCustomerData['address'] : 'Geen adres',
            "country"       => $afasCustomerData['country']
        );
        if(isset($afasCustomerData['city']) && $afasCustomerData['city'] != '')
            $billing_address['city']    = $afasCustomerData['city'];
        if(isset($afasCustomerData['postcode']) && $afasCustomerData['postcode'] != '')
            $billing_address['postcode']= $afasCustomerData['postcode'];
        if(isset($afasCustomerData['phone']) && $afasCustomerData['phone'] != '')
            $billing_address['phone']   = $afasCustomerData['phone'];
        $customerData['shipping']        = $shipping_address;
        $customerData['billing']       = $billing_address;
//         echo '<pre>';print_r($afasCustomerData);exit;
        return $customerData;
    }


    ######################################################################################
    #        Function is used to create   customer in woocommerce.                       #
    #######################################################################################
    public function importCustomersInWoocommerce($customers, $projectId, $offset_key = ''){
        $this->load->model('Projects_model');
        $this->load->library('Woo_restapi');
        $params             = $this->woocommerceConnectionParams($projectId);
        $offset             = '';
        $totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
        $totalCustomerImportError = $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;
        $already_exist      = array();
        foreach ($customers as $c_key => $c_value) {
            $createCustomer = $this->woo_restapi->postCustomer($c_value['customerDetails'], $params);
            $offset    = $c_value['id'];
            if($createCustomer['status']==1){
                $totalCustomerImportSuccess++;
                if($createCustomer['action']=='add')
                    apicenter_logs($projectId, 'importcustomers',"customer -: ".$c_value['customerDetails']['email'].' imported successfully , resource_id - '.$createCustomer['id'], false);
            } else{
                if(isset($createCustomer['result']->code) && $createCustomer['result']->code == 'registration-error-email-exists'){
                  $already_exist[] = $c_value;
                  continue;
                }
                $data = ['email'=>$c_value['customerDetails']['email']];
                $getCustomer  = $this->woo_restapi->getCustomer($data, $params);
                if($getCustomer){
                    if(!isset($getCustomer->message) && isset($getCustomer[0]->id) && $getCustomer[0]->id!=''){
                        $totalCustomerImportSuccess++;
                          apicenter_logs($projectId, 'importcustomers',"customer -: ".$c_value['customerDetails']['email'].' Updated successfully , resource_id - '.$getCustomer[0]->id, false);
                        continue;
                    }
                }
                $totalCustomerImportError++;
                $message = " Failed to import customer email -: ".$c_value['customerDetails']['email'];
                if(isset($createCustomer['result']->message))
                    $message = $message.', Error Message : '.$createCustomer['result']->message;
                apicenter_logs($projectId, 'importcustomers',$message, true);
            }
            sleep(1);        // sleep for 1 sec as one api call in 1 sec
        }
        $this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
        if($offset !='' && $offset_key != '')
            $this->Projects_model->saveValue($offset_key, $offset, $projectId);
        else if($offset!='')
            $this->Projects_model->saveValue('exactonline_customers_offset', $offset, $projectId);
        if(!empty($already_exist)){
            $this->updateCustomersInWoocommerce($already_exist, $projectId);
        }
    }
    
    #######################################################################################
    #        Function is used to   update  customer in woocommerce.                       #
    #######################################################################################
    public function updateCustomersInWoocommerce($customers, $projectId){
        $this->load->model('Projects_model');
        $params             = $this->woocommerceConnectionParams($projectId);
        $totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
        $totalCustomerImportError = $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;

        foreach ($customers as $c_key => $c_value) {
            $data = ['email'=>$c_value['customerDetails']['email']];
            $getCustomer  = $this->woo_restapi->getCustomer($data, $params);
            sleep(1);        // sleep for 1 sec as one api call in 1 sec
            $customer_id  = '';
            if($getCustomer){
                $customer_id  = isset($getCustomer[0]->id)?$getCustomer[0]->id:'';
            }
            if($customer_id != ''){
                unset($c_value['customerDetails']['password']);
                $updateCustomer = $this->woo_restapi->putCustomer($customer_id, $c_value['customerDetails'], $params);
                if($updateCustomer['status']!=1){
                    $totalCustomerImportError++;
                    $message = " Failed to import customer email -: ".$c_value['customerDetails']['email'];
                    if(isset($updateCustomer['result']->message))
                        $message = $message.', Error Message : '.$updateCustomer['result']->message;
                    apicenter_logs($projectId, 'importcustomers',$message, true);
                }
                sleep(1);        // sleep for 1 sec as one api call in 1 sec
            }
        }
        $this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
    }

    /// test functions please remove these if not reqired

    ############################################################################################################
    #        Function is used to get the woocommerce order details if order id provided or list the orders.    #
    ############################################################################################################
    public function getWooCommerceOrders($projectId, $orderId){
        $params = $this->woocommerceConnectionParams($projectId);
        $this->load->library('Woo_restapi');
        $result = $this->woo_restapi->getOrders($orderId, $params);
        return $result;
    }

    ############################################################################################################
    #        Function is used to get the woocommerce orders list.                                              #
    ############################################################################################################
    public function getOrders($projectId, $offset, $amount=10, $sortOrder='asc'){
        $params = $this->woocommerceConnectionParams($projectId);

        $page = $amount == 0 ? 1 : $offset/$amount;
        $page = $offset == 0 ? 1 : $offset;

        $this->load->library('Woo_restapi');

		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$erpSystem = $project['erp_system'];
		
        $filter = [
            'page' => $page,
            'per_page' => $amount
        ];

        $orders = $this->woo_restapi->getOrdersList($params, $filter);
		
        if (is_object($orders) && property_exists($orders,'message')) {
            apicenter_logs($projectId, 'exportorders', 'Could not get orders ' , true);
            return [];
        }
				
        $resultLine = [];
		
        foreach ($orders as $orderData) {
			if($erpSystem == 'afas'){
				$orderData = $this->ToAFASStructure($orderData);
			}
			else {
				$orderData = $this->object2array($orderData);
			}
			
            $resultLine[] = $orderData;
        }

        return $resultLine;
    }
    
    
    //public function getOrders($projectId, $offset, $amount, $sortOrder){
    //    $this->getOrdersWithFilters($projectId);
    //}
    
    public function getOrdersWithFilters($projectId, $filters = array()){
        $this->load->model('Projects_model');
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        $store_url  = '';
        if(!empty($project)){
            $store_url = $project['store_url'];
        }
        $woocommerce_api_consumer_key   = $this->Projects_model->getValue('woocommerce_api_consumer_key', $projectId)?$this->Projects_model->getValue('woocommerce_api_consumer_key', $projectId):'';
        $woocommerce_api_consumer_secret= $this->Projects_model->getValue('woocommerce_api_consumer_secret', $projectId)?$this->Projects_model->getValue('woocommerce_api_consumer_secret', $projectId):'';
        $store_url           = rtrim($store_url,"/index.php/");
        $store_url           = rtrim($store_url,"/index.php");
        $store_url           = rtrim($store_url,"/");
        $store_url           = $store_url;
        $params                 = array();
        $params['url']          = $store_url;
        $params['customer_key'] = $woocommerce_api_consumer_key;
        $params['customer_sec'] = $woocommerce_api_consumer_secret;
        $params['wp_api']       = true;
        $params['version']      = 'wc/v2';
        $params['verify_ssl']   = false;
        $this->load->library('Woo_restapi',$params);
        $result = $this->woo_restapi->getOrdersWithFilters($filters);
        return $result;
    }
    

    public function tester($projectId){
        $params = $this->woocommerceConnectionParams($projectId);
        $this->load->library('Woo_restapi',$params);
        $data = array();
        $result = $this->woo_restapi->getProducts();
        print_r($result);
    }
    
    
    public function findCategory($projectId, $categoryName){
        $params = $this->woocommerceConnectionParams($projectId);
        $this->load->library('Woo_restapi');
        $result = $this->woo_restapi->getCategories($categoryName, $params);
        $finalResult = array();
        foreach($result as $category){
	        $finalResult['items'][] = array(
		        'id' => $category->id,
		        'name' => $category->name,
		        'slug' => $category->slug,
		        'parent' => $category->parent
	        );
        }
        return $finalResult;
    }
    
    public function createCategory($projectId, $categoryName, $parentId = '', $image = ''){
        $params = $this->woocommerceConnectionParams($projectId);
		$category = array(
			'name' => $categoryName
		);
		if($parentId != ''){
			$category['parent'] = $parentId;
		}
		if($image != ''){
			$category['image']['src'] = $image;
		}
        $result = $this->woo_restapi->createCategory($category, $params);
        return $result;
    }

	public function getAttributesForMappingTable($projectId){
		$this->load->model('Projects_model');
		$currentAttributes = $this->Projects_model->getValue('product_attributes_for_mapping', $projectId);
		if($this->input->get('refresh_attribute_mapping') == 'true'){
			$currentAttributes = '';
		}
		if($currentAttributes != '' && $currentAttributes != '[]'){
			$currentAttributes = json_decode($currentAttributes, true);
			return $currentAttributes;
		} else {
	        $params = $this->woocommerceConnectionParams($projectId);
	        $this->load->library('Woo_restapi');
	        $result = $this->woo_restapi->getAttributesForMappingTable($params);
	        if(is_array($result) && !empty($result)){
		        $finalItems = array();
		        foreach($result as $item){
			        if(!isset($item->slug) || !isset($item->name)){
				        continue;
			        }
			        $finalItems[] = array(
	// 			        'code' => $item->slug,
				        'code' => $item->name,
				        'label' => $item->name,
				        'type' => $item->type
			        );
		        }
				$this->Projects_model->saveValue('product_attributes_for_mapping', json_encode($finalItems), $projectId);
		        return $finalItems;
	        }
		}
        return array();
	}

	public function applyMappedAttributes($projectId, $articleData, $finalArticleData){
		$this->load->model('Projects_model');
		$cmsAttributes = $this->Projects_model->getValue('product_attributes_for_mapping', $projectId);
		$currentAttributes = $this->Projects_model->getValue('product_attributes_mapping', $projectId);
		if($currentAttributes == '' || $currentAttributes == '[]'){
			return $finalArticleData;
		}
		$cmsAttributes = json_decode($cmsAttributes, true);
		$currentAttributes = json_decode($currentAttributes, true);
		if(is_array($currentAttributes)){
			foreach($currentAttributes['cms_attribute'] as $index => $cmsCode){
				$type = 'text';
				foreach($cmsAttributes as $cmsAttribute){
					if($cmsAttribute['code'] == $cmsCode){
						$type = $cmsAttribute['type'];
					}
				}
				$erpCode = $currentAttributes['erp_attribute'][$index];
				if(isset($articleData[$erpCode]) && $articleData[$erpCode] != ''){
					$finalArticleData['custom_attributes'][$cmsCode] = array('attribute_code' => $cmsCode, 'value' => $articleData[$erpCode], 'type' => $type);
				}
			}
		}
		return $finalArticleData;
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
	
	public function ToAFASStructure($WooOrder) 
	{  
	    $finalOrder = '';
	    $finalOrder = array(
	        'id'        => $WooOrder->id,
	        'order_id'  => $WooOrder->number,
	        'status'    => $WooOrder->status,
	        'customer'  => array(
	            'id'            => isset($WooOrder->customer_id) ? $WooOrder->customer_id : '',
	            'email'         => isset($WooOrder->billing->email) ? $WooOrder->billing->email : '',
	            'first_name'    => isset($WooOrder->billing->first_name) ? $WooOrder->billing->first_name : '',
	            'last_name'     => $WooOrder->billing->last_name,
	        ),
	        'create_at'     => $WooOrder->date_created,
	        'modified_at'   => $WooOrder->date_modified,
	        'currency'      => $WooOrder->currency,
	        'totals'        => array(
	            'total'         => $WooOrder->total,
	            'shipping'      => $WooOrder->shipping_total,
	            'tax'           => $WooOrder->total_tax,
	            'discount'      => $WooOrder->discount_total,
	        ),
	        'billing_address' => array(
	            'first_name'    => $WooOrder->billing->first_name,
	            'last_name'     => $WooOrder->billing->last_name,
	            'postcode'      => $WooOrder->billing->postcode,
	            'address1'      => $WooOrder->billing->address_1,
	            'address2'      => isset($WooOrder->billing->address_2) ? $WooOrder->billing->address_2 : '',
	            'phone'         => $WooOrder->billing->phone,
	            'city'          => $WooOrder->billing->city,
	            'country'       => $WooOrder->billing->country,
                'state'         => isset($WooOrder->billing->state) ? $WooOrder->billing->state : '',
                'company'       => isset($WooOrder->billing->company) ? $WooOrder->billing->company : '',
	        ),
	        'shipping_address' => array(
	            'first_name'    => $WooOrder->shipping->first_name,
                'last_name'     => $WooOrder->shipping->last_name,
                'postcode'      => $WooOrder->shipping->postcode,
                'address1'      => $WooOrder->shipping->address_1,
                'address2'      => isset($WooOrder->shipping->address_2) ? $WooOrder->shipping->address_2 : '',
                'city'          => $WooOrder->shipping->city,
                'country'       => $WooOrder->shipping->country,
                'state'         => isset($WooOrder->shipping->state) ? $WooOrder->shipping->state : '',
                'company'       => isset($WooOrder->shipping->company) ? $WooOrder->shipping->company : '',
            ),
            'payment_method' => $WooOrder->payment_method_title,
            'comment'        => $WooOrder->customer_note,
	    );
	    
	    $finalOrder['order_products'] = array();
	    $arrLines = $WooOrder->line_items;
	    
	    foreach($arrLines as $item) {
	        $finalOrder['order_products'][] = array(
	            'product_id'            => $item->product_id,
	            'order_product_id'      => $item->product_id,
	            'model'                 => $item->sku,
	            'name'                  => $item->name,
	            'price'                 => $item->price,
	            'quantity'              => $item->quantity,
	            'total_price'           => $item->total - $item->total_tax,
	            'total_price_incl_tax'  => $item->total,
	            'tax_value'             => $item->total_tax,
	        );
	    }
	    
	    return $finalOrder;
	}
}