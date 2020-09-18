<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cronjobtest extends MY_Controller {
	
	
	public function index(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Cms_model');
		
		$projects = $this->db->get('projects')->result_array();
		foreach($projects as $project){
			// Check if enabled
			// if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			// 	continue;
			// }
			if($this->input->get('project') == '' || $this->input->get('project') != $project['id']){
				continue;
			}
			// Get credentials
			$storeUrl = $project['store_url'];
			$apiKey = $project['api_key'];
			$pluginKey = $project['plugin_key'];
			$storeKey = $project['store_key'];
			$erpSystem = $project['erp_system'];
			$projectId = $project['id'];

			if($erpSystem == 'exactonline'){
				$this->Exactonline_model->setData(
					array(
						'projectId' => $project['id'],
						'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $project['id']).'/?project_id='.$project['id'],
						'clientId' => $this->Projects_model->getValue('exactonline_client_id', $project['id']),
						'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $project['id']),
					)
				);
				$connection = $this->Exactonline_model->makeConnection($project['id']);
				if(!$connection){ continue; }
			}
			//exit();

			// update exat article ifwebhook not created for projects
            $lastExecution      = $this->Projects_model->getValue('article_update_execution', $project['id']);
            $stocksInterval     = $this->Projects_model->getValue('article_update_interval', $project['id']);
            $enabled            = $this->Projects_model->getValue('enabled', $project['id']);
            $stock_enabled      = $this->Projects_model->getValue('articles_update_enabled', $project['id']);
            // check if the last execution time is satisfy the time checking. for update article
           // if($enabled == '1' && $stock_enabled =='1' && ($lastExecution == '' || ($lastExecution + ($stocksInterval * 60) <= time()))){
                //reset last execution time
                $time_u = $lastExecution + ($stocksInterval * 60);
                $this->Projects_model->saveValue('article_update_execution', $time_u, $projectId);

		        $lastUpdateDate = $this->Projects_model->getValue('exact_article_last_update_date', $projectId)?$this->Projects_model->getValue('exact_article_last_update_date', $projectId):date("Y-m-d 00:00:00");
	            $lastUpdateDate  = str_replace('+00:00', '.000Z', gmdate('c', strtotime($lastUpdateDate)));
	            $currentdatetime = str_replace('+00:00', '.000Z', gmdate('c', strtotime(date("Y-m-d H:i:00"))));
		            // get the offset and amount to update stocks. 
		        $offset =  $this->Projects_model->getValue('article_update_offset', $projectId) ? $this->Projects_model->getValue('article_update_offset', $projectId) : '';
		        $amount = $this->Projects_model->getValue('article_update_amount', $projectId) ? $this->Projects_model->getValue('article_update_amount', $projectId) : 10;
		        $items = $this->Exactonline_model->getExactUpdatedArticle($connection, $offset, $amount, '', $lastUpdateDate);
		        print_r($items);
	            if(!empty($items)){
	            	foreach ($items as $key => $value) {
						$articles = $this->Exactonline_model->getArticles($connection, $value['Id'], '', '');
						$this->Cms_model->updateArticles($project['id'], $articles);
	            	}
	                if(count($items)<$amount){
	                    $this->Projects_model->saveValue('article_update_offset', null, $projectId);
	                    $this->Projects_model->saveValue('exact_article_last_update_date', $currentdatetime, $projectId);
	                }else{
	                    $this->Projects_model->saveValue('article_update_offset', $value['Id'], $projectId);
	                }
	            }
	            else{
	                $this->Projects_model->saveValue('exact_article_last_update_date', $currentdatetime, $projectId);
	                $this->Projects_model->saveValue('article_update_offset', null, $projectId);
	            }
            //}
       
            exit();


			// elseif($erpSystem == 'exactonline') {
			// 		if($this->Projects_model->getValue('exactonline_import_all_products', $project['id']) == '1'){
			// 			$articles = $this->Exactonline_model->getArticles($connection);
			// 		} else{
			// 			$articles = $this->Exactonline_model->getArticles($connection, '', '', $articleAmount);
			// 		}
			// 	}

			// 	$this->Cms_model->updateArticles($project['id'], $articles);




			// Get articles
			$lastExecution = $this->Projects_model->getValue('article_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('article_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('articles_enabled', $project['id']);
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time())){
				$articles = array();
				if($erpSystem == 'afas'){
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
					
					$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					
					$result = $this->Afas_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					$removeArticles = $result['removeResults'];
					$this->Cms_model->removeArticles($project['id'], $removeArticles);
					if($result['numberOfResults'] < 1){
						log_message('error', 'Article offset reset to zero for project '.$project['id']);
						log_message('error', $currentArticleOffset);
						log_message('error', print_r($result, true));
						$this->Projects_model->saveValue('article_offset', 0, $project['id']);
						$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('afas_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} elseif($erpSystem == 'exactonline') {
					if($this->Projects_model->getValue('exactonline_import_all_products', $project['id']) == '1'){
						$articles = $this->Exactonline_model->getArticles($connection);
					} else{
						$articles = $this->Exactonline_model->getArticles($connection, '', '', $articleAmount);
					}
				}

				$this->Cms_model->updateArticles($project['id'], $articles);
			//}

			exit();
			
			// Get stock
			$lastExecution = $this->Projects_model->getValue('stock_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('stock_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('stock_enabled', $project['id']);
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time())){
				$articles = array();
				if($erpSystem == 'afas'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Afas_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						$this->Projects_model->saveValue('afas_last_update_date', date('Y-m-d'), $project['id']);
					}
				} 
				$this->Cms_model->updateStockArticles($project['id'], $articles);
			//}

				exit();

				// Get customers
			$lastExecution = $this->Projects_model->getValue('customers_last_execution', $project['id']);
			$customerInterval = $this->Projects_model->getValue('customers_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('customers_enabled', $project['id']);
			if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($customerInterval * 60) <= time())){
				if($erpSystem == 'afas'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Afas_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				} 
				if($customersAmount == 0){
					$this->Projects_model->saveValue('customers_offset', 0, $project['id']);
					$this->Projects_model->saveValue('exactonline_import_all_customers', '0', $project['id']);
				}
			}		

			// Execute custom cronjobs
			$projectModel = 'Project'.$project['id'].'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'customCronjob')){
					$this->$projectModel->customCronjob();
				}
			}
		}
	}
	
	

	public function test(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Exactonline_model');
        $this->load->model('Afas_common_model');
        $this->load->model('Woocommerce_exactonline_model');
        $this->load->model('Woocommerce_model');

		// $projectId = isset($this->input->get('project'))?$this->input->get('project'):'';
		// if($projectId!=''){
		// 	$projects = $this->db->get('projects')->where('id', $projectId)->result_array();
		// 	foreach($projects as $project){
		// 		// Check if enabled
		// 		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
		// 			continue;
		// 		}
		// 		// Get credentials
		// 		$storeUrl = $project['store_url'];
		// 		$erpSystem = $project['erp_system'];
		// 		$cms = $this->Projects_model->getValue('cms', $project['id']);

		// 		if($erpSystem == 'exactonline') {
		// 			$this->Exactonline_model->setData(
		// 				array(
		// 					'projectId' => $project['id'],
		// 					'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $project['id']).'/?project_id='.$project['id'],
		// 					'clientId' => $this->Projects_model->getValue('exactonline_client_id', $project['id']),
		// 					'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $project['id']),
		// 				)
		// 			);
		// 			$connection = $this->Exactonline_model->makeConnection($project['id']);
		// 			if(!$connection){ continue; }
		// 		}

		// 		// Get articles
		// 		$lastExecution = $this->Projects_model->getValue('article_last_execution', $project['id']);
		// 		$articleInterval = $this->Projects_model->getValue('article_interval', $project['id']);
  //               $articles_enabled = $this->Projects_model->getValue('articles_enabled', $projectId);
  //               //reset last execution time
  //               $time_u = $lastExecution + ($articleInterval * 60);
  //               $this->Projects_model->saveValue('articles_last_execution', $time_u, $projectId);

		// 		if($articles_enabled == '1' && $lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time()){
		// 			$articles = array();
		// 			if($erpSystem == 'afas'){
		// 				$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
		// 				$articleAmount = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
  //                       $items = $this->Afas_common_model->getArticles($projectId, $itemCode, $offset, $amount, '', '');
		// 			} elseif($erpSystem == 'exactonline') {
	 //                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 
		// 				$articles = $this->Exactonline_model->getArticles($connection, $itemId , $offset, $amount);
		// 			} 
		// 			$this->Cms_model->updateArticles($project['id'], $articles);
		// 		}

		// 		// Get customers
		// 		$lastExecution = $this->Projects_model->getValue('customers_last_execution', $project['id']);
		// 		$customerInterval = $this->Projects_model->getValue('customers_interval', $project['id']);
		// 		$enabled = $this->Projects_model->getValue('customers_enabled', $project['id']);
		// 		if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($customerInterval * 60) <= time())){
		// 			if($erpSystem == 'afas'){
		// 				$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
		// 				$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);					
		// 				$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
		// 				$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
		// 				$customersAmount = $this->Afas_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
		// 			} elseif($erpSystem == 'exactonline'){
		// 				$customersAmount = $this->Exactonline_model->getDebtors($connection, $project['id']);
		// 			} 
		// 			if($customersAmount == 0){
		// 				$this->Projects_model->saveValue('customers_offset', 0, $project['id']);
		// 				$this->Projects_model->saveValue('exactonline_import_all_customers', '0', $project['id']);
		// 			}
		// 		}
		// 	}
		// }
		
	}
	
}

/* End of file cronjob.php */
/* Location: ./application/controllers/cronjob.php */