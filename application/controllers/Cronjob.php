<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); //APICDEV

class Cronjob extends MY_Controller {

	public function index(){
	    set_time_limit(1200);
		//FLOW: Project Iteration --> Cronjob

		$this->load->helper('exactonline/vendor/autoload');
		//$this->load->helpers('akeneo/Akeneo');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Eaccounting_model');
		$this->load->model('Accountview_model');
		$this->load->model('Cms_model');
		$this->load->model('Marketplace_model');
		$this->load->model('Optiply_model');
		//$this->load->model('Mplus_opencart_model');
		$this->load->model('Mailchimp_model');
		$this->load->model('Cscart_model');
		$this->load->model('Magento2_model');
		$this->load->model('Magento1_model');
		$this->load->model('Akeneo_model');
		//$this->load->model('Crm_model');
		//$this->load->model('Salesforce_model');

		$projects = $this->db->get('projects')->result_array();
		
		//Grab all projects listed in APIcenter
		foreach($projects as $project){
			// Check if project is enabled
			if($this->Projects_model->getValue('enabled', $project['id']) != '1'){ 	continue; }
			if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){ continue;	}
			
			$projectId = $project['id'];
			
			//Variable definition --> Class variables
		
		    //Validation entry
		    //ApiCenter field for Test connections / Prod connections
		
			//TODO: Turn into active signal, and display on Dashboard.
			$METRIC_starttime_projectping = microtime(true);
			apicenter_logs($projectId, 'projectcontrol', 'Initiate Project Ping ' . $METRIC_starttime_projectping, false);
			
			$connectionType = $project['connection_type'];

			switch ($connectionType) {
                case 1:
                    // 'ERP systeem & Webshop'
                    
					if ($project['id'] == 999){
                        $this->ArticleImportToERP();
                    }
                    //function-call GetArticles / UpdateArticles
                    //function-call GetCustomers
                    //function-call SendOrders
                    //function-call UpdateStock
                    
                break;
                case 2:
                    // 'ERP systeem & Marketplace'
                    
                    //function-call GetArticles / UpdateArticles
                    //function-call SendOrders
                    //function-call UpdateStock
                    
                break;
                case 3:
                    // 'Marketplace & Webshop'
                    
                    //function-call GetArticles / UpdateArticles
                    //function-call SendOrders
                    //function-call UpdateStock
                    
                break;
                case 4:
                    // 'Webshop (CMS) & Point of Sale (POS)'
                    //echo "i equals 4";
                break;  
                case 5:
                    // 'Webshop & WMS system'
                    //echo "i equals 5";
                break;
                case 6:
                    // 'ERP systeem & WMS system'
                    //echo "i equals 6";
                break;
                case 7:
                    // 'ERP systeem & PIM systeem'
                    //echo "i equals 7";
                break;
                case 8:
                    
                    //echo "i equals 7";
                break;
                case 9:
                    $METRIC_starttime_contacts = microtime(true);
			        apicenter_logs($project['id'], 'projectcontrol', 'ERP - Marketing start ' . $METRIC_starttime_contacts, false);
			        
                    if($erpSystem == 'exactonline'){
					    $customersAmount = 0;
						if ($project['id'] == 172) {
							log_message('debug', 'Ping project 172');
						}
			    		if($this->Projects_model->getValue('exactonline_import_all_customers', $project['id']) == '1'){
		    				$customersAmount = $this->Exactonline_model->getDebtors($connection, $project['id']);
	    				}
    				}
    				
    				$METRIC_endtime_contacts = microtime(true);
			        apicenter_logs($project['id'], 'projectcontrol', 'ERP - Marketing end ' . $METRIC_endtime_contacts, false);
                break;
                default:
                    //echo "connection-typ undefined";
                break;
            }

			// Get credentials
			$storeUrl = $project['store_url'];
			$apiKey = $project['api_key'];
			$pluginKey = $project['plugin_key'];
			$storeKey = $project['store_key'];

			// System definitions
			$erpSystem = $project['erp_system']; //TODO: Extract from project to settings!!
			$cms = $this->Projects_model->getValue('cms', $project['id']);
			$pim = $this->Projects_model->getValue('pim', $project['id']);
			$crm = $this->Projects_model->getValue('crm_system', $project['id']);
			$marketing_system = $this->Projects_model->getValue('marketing_system', $project['id']);
			$management_systems = $this->Projects_model->getValue('management_systems', $project['id']);
			
			$wms 			= $this->Projects_model->getValue('wms', $project['id']);
			if ($wms == '' ) $wms = $cms; //TEMP
			
			//$wms = $project['wms'];
			//$wms 			= $this->Projects_model->getValue('wms', $project['id']);
			$market_place 	= $this->Projects_model->getValue('market_place', $project['id']);
			$posSystem 	= $this->Projects_model->getValue('pos', $project['id']);

			//$fromDate = $this->Projects_model->getDate('exact_article_last_update_date', $project['id']);
			$isRunning = '';
			
			$exportOrders = $this->Projects_model->getValue('send_orders_as', $project['id']);
			$amountSupliers = $this->Projects_model->getValue('customers_amount', $project['id']);
			$offsetSupliers = $this->Projects_model->getValue('customers_offset', $project['id']);		
			$afasOrdersType = $this->Projects_model->getValue('orders_type', $project['id']);		
				
			$fromDate = '';
			$onlyOpen = '';	
			
			//Checking if script is running now
			if($wms == 'optiply') {
			    $METRIC_starttime_optiplystart = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Initiate Optiply Check ' . $METRIC_starttime_optiplystart, false);
			    //log_message('debug', 'Optiply Running Ping '. var_export($project['id'], true));
			    
                $isRunning = $this->Projects_model->getValue('is_running', $project['id']);
                $lastTime = $this->Projects_model->getValue('start_running', $project['id']);
                if(($isRunning == '1'  && time() - $lastTime < 110) || $isRunning == '2') {return;} //was 1200 //was 290-300
                $this->Projects_model->scriptStart($project['id']);
                
                $METRIC_starttime_optiplyend = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Exit Optiply Check ;END; took: ' . ($METRIC_starttime_optiplyend - $METRIC_starttime_optiplystart) . ' sec', false);
				
                //log_message('debug', 'Optiply Running Ping.2 '. var_export($isRunning, true));
            }

/********************* Special Exact variables to setup for connection ************************/
			if($erpSystem == 'exactonline'){
				$fromDate = $this->Projects_model->getDate($project['id']);
				$onlyOpen = $this->Projects_model->getValue('only_open_orders', $project['id']);
				
				$this->Exactonline_model->setData(
					array(
						'projectId' => $project['id'],
						'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $project['id']).'/?project_id='.$project['id'],
						'clientId' => $this->Projects_model->getValue('exactonline_client_id', $project['id']),
						'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $project['id']),
					)
				);

				$connection = $this->Exactonline_model->makeConnection($project['id']);

				if(!$connection){
					//ONLY when OPTIPLY?????
					if ($project['id'] == 134) {
						log_message('debug', $project['id'] . ' do not connection');
					}
					if($wms == 'optiply') {
                        $this->Projects_model->scriptFinish($project['id'], $cms, $isRunning);
                    }
					continue; 
				}
			}
/********************* End Special Exact variables to setup for connection ************************/
            
			
/********************* Start Supplier + Product Import from EXACT to OPTIPLY ************************/
            $suppliers = [];
            $enabledOrders = $this->Projects_model->getValue('orders_enabled', $project['id']);
            $sync = $this->Projects_model->getValue('stock_synchronization', $project['id']);
            $enabled_supplier = $this->Projects_model->getValue('supplierproduct_enabled', $project['id']);

			if($enabled_supplier == '1' && $wms == 'optiply') {
                
                $METRIC_starttime_supplierping = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Initiate Supplier Ping ' . $METRIC_starttime_supplierping, false);
                //log_message('debug', 'Optiply Supplier Ping '. var_export($project['id'], true));
                
                if($erpSystem == 'exactonline') {
                                        
			        $suppliers = $this->Exactonline_model->getSuppliersWithItems($connection, $amountSupliers, $offsetSupliers);
			        exact_log($project['id'], 'suppliers_array', json_encode($suppliers));
                    
                    if(count($suppliers) > 0) {
                        $last = end($suppliers);
                        $this->Projects_model->saveValue('customers_offset', $last['id'], $project['id']);
                    }
                    
					apicenter_logs($projectId, 'projectcontrol', 'Supplier Exact check; suppliercount: ' . count($suppliers), false);
	                
				} elseif ($erpSystem == 'afas') {
				    $suppliers = $this->Afas_model->getSuppliersWithItems($project['id'], $amountSupliers, $offsetSupliers);
					
                    if(count($suppliers[0]['items']) > 0) {
                        $last = count($suppliers[0]['items']);
                        $this->Projects_model->saveValue('customers_offset', $offsetSupliers + $amountSupliers, $project['id']);
                    }

                    $text = 'Supplier AFAS check; suppliercount: (';
                    
                    foreach ($suppliers as $sup) {
                        $ii4 = count($sup['items']);
                        $text .= $ii4 . ', ##';
                    }
                    $text .= ' )';
                                        
					apicenter_logs($projectId, 'projectcontrol', $text, false);
                    
                } elseif ($cms == 'magento2') {

                    $offsetSupliers = $offsetSupliers == '' ? 0: $offsetSupliers;
					$suppliers = $this->Magento2_model->getSuppliersWithItems($project['id'], $amountSupliers, $offsetSupliers);

                    if(count($suppliers) > 0) {
                        $this->Projects_model->saveValue('customers_offset', $offsetSupliers+1, $project['id']);
                    }

                    if(count($suppliers) == 0) {
                        $this->Projects_model->saveValue('import_finished', '1', $project['id']);
                    }
                    apicenter_logs($projectId, 'projectcontrol', 'Supplier Magento2 check; suppliercount: ' . count($suppliers), false);
                    
                } elseif ($cms == 'magento1') {
                    $empty_pages = $this->Projects_model->getValue('empty_pages', $project['id']);
                    
                    $amountSupliers = $amountSupliers == '' ? 10 : $amountSupliers;
                    $offsetSupliers = $this->Magento1_model->checkOffsetProduct($project['id'], $storeUrl, $offsetSupliers);
                    $suppliers = $this->Magento1_model->getSuppliersWithItems($project['id'], $storeUrl, $amountSupliers, $offsetSupliers);

                    if($suppliers['count'] > 0) {
                        $this->Projects_model->saveValue('customers_offset', $offsetSupliers + $amountSupliers, $project['id']);
                    }
                    else if($suppliers['count'] == 0 && $empty_pages <= 10) {
                        //$this->Projects_model->saveValue('customers_offset', 0, $project['id']);
                        $this->Projects_model->saveValue('customers_offset', $offsetSupliers + $amountSupliers, $project['id']);
                        $this->Projects_model->saveValue('empty_pages', $empty_pages++, $project['id']);
                    }
                    else if($suppliers['count'] == 0 && $empty_pages > 10){
                        $this->Projects_model->saveValue('customers_offset', 0, $project['id']);
                        $this->Projects_model->saveValue('empty_pages', 0, $project['id']);
                        $this->Projects_model->saveValue('import_finished', '1', $project['id']);
                    }

                    apicenter_logs($project['id'], 'projectcontrol', 'Supplier Magento1 check; suppliercount: ' . $suppliers['count'] . ' EmptyPage $i: '.$empty_pages, false);

                    unset($suppliers['count']);
                }

                if($cms != 'magento2' && $cms != 'magento1' && count($suppliers) < $amountSupliers){
                    $this->Projects_model->saveValue('import_finished', '1', $project['id']);
                    exact_log($project['id'], 'suppliers_finished', 'Count: '.count($suppliers).' Amount:'.$amountSupliers);
                } 
                        
                $this->Cms_model->updateSuppliers($project['id'], $suppliers);
				
				if ( $posSystem == 'mplus' ) {// echo "123"; exit();
					if ( $enabled == '1' && ( $lastExecution == '' || $lastExecution <= ( time() - $articleInterval * 60) ) ) {
						$this->Mplus_opencart_model->insertOpencartToMPuls($project['id']);
					}
				}

                if($wms == 'optiply' && $this->Projects_model->getValue('import_finished', $project['id']) != '1')
                    $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
                    
                $METRIC_endtime_supplierping = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Exit Supplier Ping ;END; took: ' . ($METRIC_endtime_supplierping - $METRIC_starttime_supplierping) . ' sec', false);
            }
/********************* End Supplier + Product Import from EXACT to OPTIPLY ************************/
            
            
/********************* Start PurchaseOrders Import/Export from EXACT to OPTIPLY ************************/
            //Get Buy Orders from Exactonline OR Optiply
            $buyOrders = [];

            $amountOrders = $this->Projects_model->getValue('orders_amount', $project['id']);
            $offsetOrders = $this->Projects_model->getValue('buyorders_offset', $project['id']);
            $supliersFinished = $this->Projects_model->getValue('import_finished', $project['id']);
            $PurchOrdImport_enabled = $this->Projects_model->getValue('purchaseorder_import_enabled', $project['id']);
            
            if ($PurchOrdImport_enabled == '1' && $supliersFinished == '1' && $wms == 'optiply') {
                
                $METRIC_starttime_buyorderstart = microtime(true);
                apicenter_logs($project['id'], 'projectcontrol', 'Initiate BuyOrder Ping ' . $METRIC_starttime_buyorderstart, false);
                
                if ($erpSystem == 'exactonline') {
                    //From Exact to Optiply
                    $buyOrdersExact = $this->Exactonline_model->getBuyOrderData($connection, $amountOrders, $offsetOrders, $onlyOpen);
                    					
                    exact_log($project['id'], 'buy_orders_exact', json_encode($buyOrdersExact));
                    $upBuyOrders = $this->Cms_model->updateBuyOrders($project['id'], $buyOrdersExact);
                    
                    if(count($buyOrdersExact) > 0) {
                        $last = end($buyOrdersExact);
                        exact_log($project['id'], 'buy_orders_offset', json_encode($buyOrdersExact));
                        $this->Projects_model->saveValue('buyorders_offset', $last['id'], $project['id']);
                        $this->Projects_model->saveValue('buyorders_last_date', $last['date'], $project['id']);
                    } else {
                        exact_log($project['id'], 'buy_orders_empty', '1');
                        $this->Projects_model->saveValue('buyorders_offset', '', $project['id']);
                    }
            
    /********************* Start PurchaseOrders "Export ONLY" from EXACT to OPTIPLY ************************/
                    $PurchOrdExport_enabled = $this->Projects_model->getValue('purchaseorder_export_enabled', $project['id']);
                    
                    if ($PurchOrdExport_enabled == '1'){
                        //From Optiply to Exact
                        $offsetOpt = $this->Projects_model->getValue('optiply_orders_offset', $project['id']);
                        $buyOrdersOpt = $this->Optiply_model->getBuyOrderData($project['id'], $onlyOpen, false, $offsetOpt);
                                              
                        $this->Exactonline_model->updateBuyOrders($connection, $buyOrdersOpt, $project['id']);
	/********************* End PurchaseOrders "Export ONLY" from EXACT to OPTIPLY ************************/
					}
				} elseif ($erpSystem == 'afas') {
					//From AFAS to Optiply
                    $buyOrders = $this->Afas_model->getBuyOrders($project['id'], $amountOrders, $offsetOrders);

                    if(count($buyOrders) > 0) {
                        $last = count($buyOrders);
                        $this->Projects_model->saveValue('buyorders_offset', $offsetOrders+$last, $project['id']);
                        afas_log($project['id'], 'buy_orders_offset', json_encode($buyOrders));
                    }

                    $this->Cms_model->updateBuyOrders($project['id'], $buyOrders);

                    $missedCount = $this->Projects_model->getValue('missed_lines', $project['id']);
                    if($missedCount != '') {
                        $this->Projects_model->importMissedLines($project['id']);
                    }
					
					$PurchOrdExport_enabled = $this->Projects_model->getValue('purchaseorder_export_enabled', $project['id']);
                    
                    if ($PurchOrdExport_enabled == '1'){
						apicenter_logs($project['id'], 'projectcontrol', 'Return BuyOrder Ping: ' . microtime(true), false);
						/* changes 24-02 */
						$offsetOpt = $this->Projects_model->getValue('optiply_orders_offset', $project['id']);
						//From Optiply to AFAS
						$buyOrdersOpt = $this->Optiply_model->getBuyOrderData($project['id'], 0, false, $offsetOpt);
						$this->Afas_model->pushAllPurchaseOrders($project['id'], $buyOrdersOpt);
					}
                }
                
                $METRIC_starttime_buyorderend = microtime(true);
                apicenter_logs($project['id'], 'projectcontrol', 'Exit BuyOrder Ping ;END; took: ' . ($METRIC_starttime_buyorderend - $METRIC_starttime_buyorderstart) . ' sec', false);
            }
/********************* End PurchaseOrders Import/Export from EXACT to OPTIPLY ************************/

/********************* Start Receipt sync *********************/
            if($PurchOrdImport_enabled == '1' && $supliersFinished == '1' && $wms == 'optiply') {
                if ($erpSystem == 'exactonline') {
                    $ordersId = $this->Optiply_model->getAllOpenOrdersId($project['id']);

                    $receipLines = $this->Exactonline_model->getGoodsReceipts($project['id'], $connection, $ordersId);

                    $this->Optiply_model->updateOrdersReceipts($project['id'], $receipLines);
                }
            }
/********************* END Receipt sync *********************/
            
/********************* Start SellOrders Import from EXACT to OPTIPLY ************************/
            //Get Sell Orders from Exactonline
            $getOrders = [];
            $offsetOrders = $this->Projects_model->getValue('orders_offset', $project['id']);
            $SellOrderImport_enabled = $this->Projects_model->getValue('order_import_enabled', $project['id']);

            if ($SellOrderImport_enabled == '1' && $supliersFinished == '1' && $wms == 'optiply') {
                
                $METRIC_starttime_sellorder = microtime(true);
                apicenter_logs($project['id'], 'projectcontrol', 'Initiate SellOrder Ping ' . $METRIC_starttime_sellorder, false);
                
                if ($erpSystem == 'exactonline') {
                    $getOrders = $this->Exactonline_model->getSalesOrders($connection, $amountOrders, $offsetOrders, $fromDate);
                    if( $projectId == 134) { log_message('debug', 'getOrders 134 Exact' . var_export($getOrders, true)); }
                    if( $projectId == 145) { log_message('debug', 'getOrders 145 Exact' . var_export($getOrders, true)); }
                    
                    exact_log($project['id'], 'sell_orders', json_encode($getOrders));
                    $last = end($getOrders);
                    
                    if(count($getOrders) > 0) {
                        
                        exact_log($project['id'], 'sell_orders_offset', $last['id']);
                        $this->Projects_model->saveValue('orders_offset', $last['id'], $project['id']);

                         if(count($getOrders) < $amountOrders) {
                            $this->Projects_model->saveValue('orders_offset', '', $project['id']);
                            $this->Projects_model->saveValue('from_date_changed', '1', $project['id']);
                            $this->Projects_model->saveValue('exact_ord_update_date', $last['date'], $project['id']);
                        }
                    } else {
                        exact_log($project['id'], 'sell_orders_empty', '1');

                        $this->Projects_model->saveValue('orders_offset', '', $project['id']);
                        $this->Projects_model->saveValue('from_date_changed', '1', $project['id']);
                        //$this->Projects_model->saveValue('exact_article_last_update_date', $last['created'], $project['id']);
                    }
				} elseif ($erpSystem == 'afas') {
					$getOrders = $this->Afas_model->getSalesOrders($project['id'], $amountOrders, $offsetOrders, $fromDate);

                    if(count($getOrders) > 0) {
                        $last = count($getOrders);

                        $this->Projects_model->saveValue('orders_offset', $last + $offsetOrders, $project['id']);
                        afas_log($project['id'], 'sell_orders_offset', $last);

                    }
                } elseif ($cms == 'magento2') {

                    $offsetOrders = $offsetOrders == '' ? 0 : $offsetOrders;
                    $getOrders = $this->Magento2_model->getSellOrders($project['id'], $amountOrders, $offsetOrders, $fromDate);

                    if(count($getOrders) > 0 && count($getOrders) > $offsetOrders) {
                        $this->Projects_model->saveValue('orders_offset', $offsetOrders + 1, $project['id']);
                    }
                } elseif ($cms == 'magento1') {
                    
                    
                    $offsetOrders = $this->Magento1_model->checkOrderOffset($project['id'], $storeUrl, $offsetOrders);
                    $getOrders = $this->Magento1_model->getSellOrders($project['id'], $storeUrl, $amountOrders, $offsetOrders, $fromDate);
                    
                    
                    $this->Projects_model->saveValue('orders_offset', $offsetOrders + $amountOrders, $project['id']);
                    
                    
                    //if($getOrders['count'] > 0 && $getOrders['count'] > $offsetOrders) {
                    //    $this->Projects_model->saveValue('orders_offset', $offsetOrders + $getOrders['count'], $project['id']);
                    //}
                    
                    apicenter_logs($project['id'], 'projectcontrol', 'Mag1 SalesOrders ' . 'Amount: ' . $amountOrders . '. Offset: ' . $offsetOrders, false);

                    unset($getOrders['count']);
				}

                $upOrders = $this->Cms_model->updateOrders($project['id'], $getOrders);
                
                $METRIC_endtime_sellorder = microtime(true);
                apicenter_logs($project['id'], 'projectcontrol', 'Exit SellOrder Ping ;END; took: ' . ($METRIC_endtime_sellorder - $METRIC_starttime_sellorder) . ' sec', false);

                if($erpSystem == 'exactonline') {
                    //Update stock
                    $this->Optiply_model->updateStockData($project['id'], $connection);
                    //Add new items
                    $this->Optiply_model->addItems($project['id'], $connection);
                    //Update Purchase order status
                    $ordersToCheck = $this->Optiply_model->updateStatusBuyOrders($project['id'], $connection);echo '<pre>';var_dump($ordersToCheck);
                    $ordersToComplete = $this->Exactonline_model->checkOrdersToComplete($connection, $project['id'], $ordersToCheck);echo '<pre>';var_dump($ordersToComplete);
                    $this->Optiply_model->closeOrders($project['id'], $ordersToComplete);

                    $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
                }
            }
/********************* End SellOrders Import from EXACT to OPTIPLY ************************/

            if ($project['id'] == 87){
                
                $currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
                $articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
    
                $this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
                $this->Projects_model->saveValue('article_last_execution', time(), $project['id']);

                $articles = $this->Cscart_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
                
                $this->Afas_model->sendArticle($project['id'], $articles);
            }

			$admin_debugging = $this->Projects_model->getValue('admin_logs', $project['id']);
			
			// Get articles
			$lastExecution = $this->Projects_model->getValue('article_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('article_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('articles_enabled', $project['id']);
			
			if($enabled == '1' && ($lastExecution == '' || $lastExecution + ( ( $articleInterval * 60 ) - 50  ) <= time())){
				
				$METRIC_starttime_getArticles = microtime(true);
			    apicenter_logs($project['id'], 'projectcontrol', 'Initiate GetArticles - ' . $erpSystem  .' Time: '. $METRIC_starttime_getArticles, false);

				$articles = array();

				//Grab the Products from AFAS
				if($erpSystem == 'afas' && $wms != 'optiply'){
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);

					$result = $this->Afas_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					if ($result['numberOfResults']>0) {
						$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
						$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					}

					$articles = $result['results'];
					
					if ($admin_debugging == '1'){
						log_message('debug', "articles for project: " . $project['id'] . " in export". var_export($articles, true));
					}
										
					//$removeArticles = $result['removeResults'];
					if ($pim == 'akeneo') {
						$result['numberOfResults'] = 1;
					} else {
						//$this->Cms_model->removeArticles($project['id'], $removeArticles);
					}
					
					if($result['numberOfResults'] < 1 ){
						if ($admin_debugging == '1') log_message('error', 'Article offset reset to zero for project '.$project['id']);
											
						$this->Projects_model->saveValue('article_offset', 0, $project['id']);

						$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $project['id']);
						
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('afas_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} elseif($erpSystem == 'exactonline' && $wms != 'optiply') {
					//if($this->Projects_model->getValue('exactonline_import_all_products', $project['id']) == '1'){ /* Webhook only */
						
					    //Added Bol.com Exact code 26 Feb 2019 - LCB
						$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
						if ($market_place == 'bol') {
							$articles = $this->Exactonline_model->getArticles($connection, null, '', 100, $connectionType);
						} else {
							$articles = $this->Exactonline_model->getArticles($connection, null, '', $articleAmount);
						}
					//}
				} elseif($erpSystem == 'visma' && $wms != 'optiply') {
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
					
					$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					
					$result = $this->Visma_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					// log_message('debug', 'visma id = ' . $project['id']);
					
					$removeArticles = $result['removeResults'];
					$this->Cms_model->removeArticles($project['id'], $removeArticles);
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('article_offset', 0, $project['id']);
						$lastUpdateDate = $this->Projects_model->getValue('visma_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('visma_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} elseif($erpSystem == 'accountview' && $wms != 'optiply') {
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
					
					$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					
					$result = $this->Accountview_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					// log_message('debug', 'accountview id = ' . $project['id']);
					
					$removeArticles = $result['removeResults'];
					$this->Cms_model->removeArticles($project['id'], $removeArticles);
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('article_offset', 0, $project['id']);
						$lastUpdateDate = $this->Projects_model->getValue('accountview_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('accountview_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} elseif($erpSystem == 'eaccounting' && $wms != 'optiply') {
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
					
					$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					
					$result = $this->Eaccounting_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					// log_message('debug', 'visma id = ' . $project['id']);

					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('article_offset', 0, $project['id']);
						$lastUpdateDate = $this->Projects_model->getValue('eaccounting_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('eaccounting_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} 
				//elseif ($crm !== null) {
				//	$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
				//	$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
				//	$articles = $this->Crm_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
				//}
				//if ($project['id'] == 184) {
				//	log_message('debug', "Articles for Magetno 1 " . $project['id'] );
				//	log_message('debug', var_export($articles, true));
				//}

				//Added Bol.com Exact code 26 Feb 2019 - LCB
				if ($market_place == 'bol') {
					//log_message('debug', 'bol update articles'.$project['id']);
					$this->Marketplace_model->updateArticles($project['id'], $articles);
				} elseif($pim == 'akeneo') {
					// log_message('debug', 'Akeneo afas');
					$this->Akeneo_model->updateArticles($project['id'], $articles);
				} else {
					$this->Cms_model->updateArticles($project['id'], $articles);
				}
				
			    //$METRIC_endtime_getArticles = microtime(true);
			    //apicenter_logs($project['id'], 'projectcontrol', 'Initiate GetArticles END - ' . $erpSystem  .' Took: '. $METRIC_endtime_getArticles-$METRIC_starttime_getArticles . 'sec' , false);
			}

			if ($project['id'] == 131) {
				
				$time = date('H:i');
				$processPrices      = false;
				$processShipment    = false;
				
				if (((date('Gi') < 830) && (date('Gi') > 800)) || ((date('Gi') < 2030) && (date('Gi') > 2000))){
				    $processShipment = true;
				}
				else if( (date('Gi') < 030) && (date('Gi') > 000) )  {
				    $processPrices = true;
				}
				
				log_message('debug', 'Servertime = ' . $time . ' Ship: ' . ($processShipment?'1':'0') . ' Prices: ' . ($processPrices?'1':'0'));
				
				
				if ($processPrices == true) {
				//if ( $time > '00:01' && $time < '01:00' ) {
					$priceChangesList = $this->Afas_model->getPriceChangesList($project['id']);
					if ($priceChangesList['numberOfResults'] > 0) {
						$this->Cms_model->updatePrice($project['id'], $priceChangesList['priceData']);
					}
				}

				if ($erpSystem == 'afas' && $cms == 'magento2')  {
					
					if ($processShipment == true) {
					//if ( ($time < '12:00' && $time > '12:30') || ($time < '20:30' && $time > '20:00') ) {
						$this->Magento2_model->getProcessingOrders($project['id']);
						$this->Projects_model->saveValue('accountview_last_update_date', date('Y-m-d H:i'), $project['id']);
					}
				}

				$offsetCredit = $this->Projects_model->getValue('credit_offset', $project['id']);
				$result = $this->Afas_model->getCredit($project['id'], $offsetCredit, 10);
				if ($result['numberOfResults']) {
					$offsetCredit = $this->Projects_model->saveValue('credit_offset', $offsetCredit + $result['numberOfResults'], $project['id']);
					$this->Magento2_model->createCreditMemo($project['id'], $result);
				}
			}

			if ($marketing_system == 'iconneqt' && $management_systems == 'mews') {
				$this->load->model('Mews_model');
				$this->load->model('Iconneqt_model');


				$lastExecution = $this->Projects_model->getValue('customers_last_execution', $project['id']);
				
				$customers = $this->Mews_model->getCustomers($project['id'], $lastExecution, date("Y-m-d\TH:i:s", strtotime($lastExecution .'+5 minutes')));

				if ($customers) {
					foreach ($customers as $customer) {
						$resutl = $this->Iconneqt_model->sendCutomers($project['id'], $customer);
					}
				}

				$this->Projects_model->saveValue('customers_last_execution', date("Y-m-d\TH:i:s", time()), $project['id']);
			}

			// Get stock
			
			$lastExecution = $this->Projects_model->getValue('stock_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('stock_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('stock_enabled', $project['id']);
			
			//Added Bol.com Exact code 26 Feb 2019 - LCB
			if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time()) && $market_place != 'bol' && $connectionType != 2){
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time())){
				$articles = array();

				$METRIC_starttime_stock = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Initiate Stock Ping Time ' . ($METRIC_starttime_stock), false);
				if($erpSystem == 'afas' && $wms != 'optiply'){
				    
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Afas_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					
					if ($admin_debugging == '1'){
						log_message('debug', "stock for project: " . $project['id'] . " in export". var_export($articles, true));
					}
					
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);

						$lastUpdateDate = $this->Projects_model->getValue('afas_stock_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('afas_stock_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				} elseif($erpSystem == 'visma' && $wms != 'optiply'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Visma_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						$this->Projects_model->saveValue('visma_last_update_date', date('Y-m-d'), $project['id']);
					}
				} elseif($erpSystem == 'accountview' && $wms != 'optiply'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Accountview_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						//$this->Projects_model->saveValue('accountview_last_update_date', date('Y-m-d'), $project['id']);
					}
				}  elseif($erpSystem == 'eaccounting' && $wms != 'optiply'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Eaccounting_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						$this->Projects_model->saveValue('eaccounting_stock_last_update_date', date('Y-m-d'), $project['id']);
					}
				}  elseif($cms == 'magento2' && $wms == 'optiply') {

				    //Stock Update based on Deliveries
				    $lastUpdateDate = $this->Projects_model->getValue('stock_last_execution', $project['id']);
				    $stockData = $this->Optiply_model->getDeliveries($project['id'], $lastUpdateDate);

                    $this->Projects_model->saveValue('stock_last_execution', $this->Projects_model->getGMTtime(), $project['id']);

                    //Cannot integrate into CMS model
                    $this->Magento2_model->updateStockDelivered($stockData, $project['id']);

                    //Stock Update from dashboard
                    $stockOffset = $this->Projects_model->getValue('stock_items_page', $project['id']);
                    $stockOffset = $stockOffset == '' ? 0 : $stockOffset;
                    $stockAmount = $this->Projects_model->getValue('stock_items_amount', $project['id']);

                    $itemStock = $this->Magento2_model->getStockArticles($project['id'], $stockOffset, $stockAmount);
                    $this->Optiply_model->updateStockItems($project['id'], $itemStock);

                    if(count($itemStock) == 0) {
                        $this->Projects_model->saveValue('stock_items_page', 0, $project['id']);
                    }
                    $this->Projects_model->saveValue('stock_items_page', $stockOffset + 1, $project['id']);
                } elseif($cms == 'magento1' && $wms == 'optiply') {

                    $lastUpdateDate = $this->Projects_model->getValue('stock_last_execution', $project['id']);
                    $stockData = $this->Optiply_model->getDeliveries($project['id'], $lastUpdateDate);

                    $this->Projects_model->saveValue('stock_last_execution', $this->Projects_model->getGMTtime(), $project['id']);

                    //Cannot integrate into CMS model
                    $this->Magento1_model->updateStockDelivered($stockData, $project['id'], $storeUrl);
                } elseif($erpSystem == 'afas' && $wms == 'optiply') {
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Afas_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);

						$lastUpdateDate = $this->Projects_model->getValue('afas_stock_last_update_date', $project['id']);
						if($lastUpdateDate != '' && $lastUpdateDate != ' '){
							$this->Projects_model->saveValue('afas_stock_last_update_date', date('Y-m-d'), $project['id']);
						}
					}
				}
				
				apicenter_logs($project['id'], 'projectcontrol', 'Initiate Stock Write ' . ($METRIC_starttime_stock), false);
				//log_message('debug', 'GetStock Ping '. var_export($articles, true));
				
				$this->Cms_model->updateStockArticles($project['id'], $articles);

				if ($erpSystem == 'afas' && $wms == 'optiply') {
					$this->Optiply_model->updateStockArticles($project['id'], $articles, $erpSystem);
				}
				if($cms == 'magento1') {
                    $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
                }
			}

			//Update Statuses Optiply <-> Magento2
			if($enabled == '1' && $wms == 'optiply') {
	            if($cms == 'magento2') {
	                $status_last_ex = $this->Projects_model->getValue('status_last_execution', $project['id']);

	                //Compare Optiply with Magento
	                $optiplyNewStatuses = $this->Optiply_model->getProductStatuses($project['id'], $status_last_ex);
	                $magentoNewStatuses = $this->Magento2_model->getProductStatusesInArray($project['id'], $optiplyNewStatuses);

	                $statusesToUpdateMagento = $this->Projects_model->processStatuses($optiplyNewStatuses, $magentoNewStatuses);
	                $updatedProducts = $this->Magento2_model->updateProductStatuses($project['id'], $statusesToUpdateMagento);

	                //Compare magento with optiply
	                $magentoStatuses = $this->Magento2_model->getProductStatuses($project['id'], 50, $status_last_ex);
	                $optiplyStatuses = $this->Optiply_model->getProductStatusesInArray($project['id'], $magentoStatuses);

	                $statusesToUpdateOptiply = $this->Projects_model->processStatuses($magentoStatuses, $optiplyStatuses, $updatedProducts);

	                $this->Optiply_model->updateProductStatuses($project['id'], $statusesToUpdateOptiply);

	                $this->Projects_model->saveValue('status_last_execution', date('Y-m-d H:i:s'), $project['id']);
	                $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
	            } elseif($erpSystem == 'afas') {
                    $lastCheckedItems = $this->Projects_model->getValue('items_last_checked', $project['id']);
                    $lastCheckedItems = $lastCheckedItems == '' ? strtotime('-1 day') : $lastCheckedItems;
                    afas_log($project['id'], 'prod_date_status', $lastCheckedItems.':'.time());
                    if($lastCheckedItems < time()) {
                        $offset = $this->Projects_model->getValue('items_offset', $project['id']);

                        $products = $this->Optiply_model->getProducts($project['id'], $offset);
                        $disabled = $this->Afas_model->checkProductsExist($project['id'], $products);

                        $this->Optiply_model->updateProductStatuses($project['id'], $disabled);
                        $this->Projects_model->saveValue('items_offset', $offset+100, $project['id']);

                        if(count($products) < 100) {
                            $this->Projects_model->saveValue('items_last_checked', strtotime('+1 day'), $project['id']);
                            $this->Projects_model->saveValue('items_offset', 0, $project['id']);
                        }
                    }

                    $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
                }
	        }
			
			// Get customers
			$lastExecution = $this->Projects_model->getValue('customers_last_execution', $project['id']);
			$customerInterval = $this->Projects_model->getValue('customers_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('customers_enabled', $project['id']);
			
			//Added Bol.com Exact code 26 Feb 2019 - LCB
			if($wms != 'optiply' && $enabled == '1' && ($lastExecution == '' || $lastExecution + ($customerInterval * 60) <= time()) && $market_place != 'bol' && $connectionType != 2){
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($customerInterval * 60) <= time())){
				
				$METRIC_starttime_customerping = microtime(true);
				apicenter_logs($projectId, 'projectcontrol', 'Start CustomerImport Ping. Time: '. $METRIC_starttime_customerping, false);
				
				if($erpSystem == 'afas'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
//$currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					
					if ($cms == 'mailchimp') {
						$currentCustomersOffset = $this->Projects_model->getValue('mailchimp_offset', $project['id']) ? $this->Projects_model->getValue('mailchimp_offset', $project['id']) : 0;
                    	$customersAmount 		= $this->Projects_model->getValue('customers_amount', $project['id']) ? $this->Projects_model->getValue('customers_amount', $project['id']) : 10;
						$result 				= $this->Afas_model->getCustomer($project['id'], $currentCustomerOffset, $customerAmount);
						$currentCustomersOffset = 0;
						if ($result) {
							if($result['numberOfResults']>0){
								$new_offset = $currentCustomersOffset + $result['numberOfResults'];
								if(!empty($result['customerData'])) {
									$this->Mailchimp_model->importIntoMailchimp($result['customerData'], $project['id']);
									$this->Projects_model->saveValue('mailchimp_offset', $new_offset, $project['id']);
								}
							} else{
								$this->Projects_model->saveValue('mailchimp_offset', 0, $project['id']);
							}
						}
					} else {
						$customersAmount = $this->Afas_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
						//log_message('debug', 'ProductData-org' . $project['id'] . var_export($currentCustomerOffset, true) . var_export($customerAmount, true));
					}
					//$customersAmount = $this->Afas_model->getDebtors($project['id'], //$currentCustomerOffset, $customerAmount);
				} 
				else if($erpSystem == 'exactonline'){
					$customersAmount = 0;
					
					if($this->Projects_model->getValue('exactonline_import_all_customers', $project['id']) == '1'){
						$customersAmount = $this->Exactonline_model->getDebtors($connection, $project['id']);
					}
				} 
				else if($erpSystem == 'visma'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
                    
                    //$currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Visma_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				} 
				else if($erpSystem == 'accountview'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
                    
                    //$currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Accountview_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				}
				else if($erpSystem == 'eaccounting'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
                    
                    //$currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Eaccounting_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				}

				if($customersAmount == 0){
					$this->Projects_model->saveValue('customers_offset', 0, $project['id']);
					$this->Projects_model->saveValue('exactonline_import_all_customers', '0', $project['id']);
				}
				
				$METRIC_endtime_customerping = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Exit CustomerImport Ping ;END; took: ' . ($METRIC_endtime_customerping - $METRIC_starttime_customerping) . ' sec', false);
			}

			// Get courses
			$lastExecution = $this->Projects_model->getValue('course_last_execution', $project['id']);
			$courseInterval = $this->Projects_model->getValue('course_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('course_enabled', $project['id']);
			if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($courseInterval * 60) <= time())){
				$courses = array();
				if($erpSystem == 'afas'){
					$currentCourseOffset = $this->Projects_model->getValue('course_offset', $project['id']) ? $this->Projects_model->getValue('course_offset', $project['id']) : 0;
					$courseAmount = $this->Projects_model->getValue('course_amount', $project['id']);
					
					$this->Projects_model->saveValue('course_offset', $currentCourseOffset + $courseAmount, $project['id']);
					$this->Projects_model->saveValue('course_last_execution', time(), $project['id']);
					
					$courses = $this->Afas_model->getCourses($project['id'], $currentCourseOffset, $courseAmount);
					if(empty($courses)){
						$this->Projects_model->saveValue('course_offset', 0, $project['id']);
					}
				}
				$this->Cms_model->updateCourses($project['id'], $courses['results']);
			}
			
			// Send orders
			$lastExecution = $this->Projects_model->getValue('orders_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('orders_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('orders_enabled', $project['id']);

			if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time())){
			    
				$METRIC_starttime_order = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Initiate Order Ping, Time: ' . ($METRIC_starttime_order), false);
                
				$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
				$orderAmount = $this->Projects_model->getValue('orders_amount', $project['id']);
				$getOrderAmount = 0;
				
				//Added Bol.com Exact code 26 Feb 2019 - LCB
				if ($market_place == 'bol') {
					$orders = $this->Marketplace_model->getOrders($project['id']);
					$getOrderAmount = count($orders);
				} else {
					$result = $this->Cms_model->getOrders($project['id'], $currentOrderOffset, $orderAmount);

					if ( $admin_debugging == '1' ){	
					 	log_message('debug', 'Get Orders info for project ' . $project['id'] );
					 	log_message('debug', var_export($result, true));
					}
					if(isset($result['count'])){
						$getOrderAmount = $result['count'];
						$orders = $result['orders'];
					} else {
						$orders = $result;
						$getOrderAmount = count($orders);
					}
				}

				if($orders != false && !empty($orders)){
					if($cms == 'shopify'){
						$lastOrder = end($orders);
						if($erpSystem == 'exactonline'){
							$this->Projects_model->saveValue('orders_offset', $lastOrder->since_id, $project['id']);
						} else {
							$this->Projects_model->saveValue('orders_offset', $lastOrder['since_id'], $project['id']);
						}
				} elseif($cms == 'shopware'){
					// Save last order id
					$lastOrder = end($orders);
					if(isset($lastOrder['order_id']) && $lastOrder['order_id'] != ''){
						$this->Projects_model->saveValue('orders_offset', $lastOrder['order_id'], $project['id']);
					}
				} else {
					$this->Projects_model->saveValue('orders_offset', $currentOrderOffset + $getOrderAmount, $project['id']);
				}
					$this->Projects_model->saveValue('orders_last_execution', time(), $project['id']);
					
					foreach($orders as $order){
						if($erpSystem == 'afas'){
							if ($afasOrdersType == "DeliveryNote") {
								$result = $this->Afas_model->sendDeliveryNote($project['id'], $order);
							} else { 
								$result = $this->Afas_model->sendOrder($project['id'], $order);
							}
						} 
						elseif($erpSystem == 'exactonline'){
							if($project['id'] == 64){
								$exportOrders = 'invoice';
							}
							
						    if ($exportOrders == 'order' || !$exportOrders) {
								$result = $this->Exactonline_model->sendOrder($connection, $project['id'], $order);
                            } 
                            else {
								$result = $this->Exactonline_model->sendInvoice($connection, $project['id'], $order);
                            }
						} 
						elseif($erpSystem == 'visma'){
							$result = $this->Visma_model->sendOrder($project['id'], $order);
						} 
						elseif($erpSystem == 'accountview'){
							$result = $this->Accountview_model->sendOrder($project['id'], $order);
						}
                        elseif($erpSystem == 'eaccounting'){
                            if ($exportOrders == 'order' || !$exportOrders) {
                                $result = $this->Eaccounting_model->sendOrder($project['id'], $order);
                            } 
                            else {
                                $result = $this->Eaccounting_model->sendInvoiceDraft($project['id'], $order);
                            }
                        }
						//elseif ($crm !== null) {
						//	$result = $this->Salesforce_model->sendOrder($project['id'], $order);
						//}
					}
				}
				
				$METRIC_endtime_order = microtime(true);
                apicenter_logs($projectId, 'projectcontrol', 'Exit Order Ping, Time: ' . ($METRIC_endtime_order), false);
			}
			
			
			if($erpSystem == 'exactonline'){
				if($this->Projects_model->getValue('exactonline_delete_webhooks', $project['id']) == '1'){
					$this->Projects_model->saveValue('exactonline_delete_webhooks', '0', $project['id']);
					$webHooks = new \Picqer\Financials\Exact\WebhookSubscription($connection);
					$webHooks = $webHooks->get();
					foreach($webHooks as $webHook){
						$webHookData = $webHook->attributes();
						if((strpos($webHookData['CallbackURL'], 'api2cart') == false && strpos($webHookData['CallbackURL'], 'apicenter') == false) || ($webHookData['Topic'] != 'Accounts' && $webHookData['Topic'] != 'Items')){
							continue;
						}
						$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
							'ID' => $webHook->ID
						));
						$result = $webHook->delete();
						if($webHookData['Topic'] == 'Accounts'){
							apicenter_logs($project['id'], 'importcustomers', 'Removed webhook '.$webHookData['Topic'], false);
						} elseif($webHookData['Topic'] == 'Items'){
							apicenter_logs($project['id'], 'importarticles', 'Removed webhook '.$webHookData['Topic'], false);
						}
					}
					
					$callbackUrl = $this->Projects_model->getValue('exactonline_redirect_url', $project['id']);
					
					// Subscribe to webhook import articles
					$enabled = $this->Projects_model->getValue('articles_enabled', $project['id']);
					if($enabled == '1'){
						$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
							'CallbackURL' => $callbackUrl.'/itemscallback/?project_id='.$project['id'],
							'Topic' => 'Items'
						));
						try{
							$result = $webHook->save();
							apicenter_logs($project['id'], 'importarticles', 'Added webhook "Items"', false);
							// @todo: Save trigger date in db
						} catch(Picqer\Financials\Exact\ApiException $e){
							if(strpos($e->getMessage(), 'Data already exists') != false){
								// @todo: Save trigger date in db
								apicenter_logs($project['id'], 'importarticles', 'Webhook "Items" already exists', false);
							}
						}
					}
			
			
					// Subscribe to webhook import customers
					$enabled = $this->Projects_model->getValue('customers_enabled', $project['id']);
					if($enabled == '1'){
						$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
							'CallbackURL' => $callbackUrl.'/accountscallback?project_id='.$project['id'],
							'Topic' => 'Accounts'
						));
						try{
							$result = $webHook->save();
							apicenter_logs($project['id'], 'importcustomers', 'Added webhook "Accounts"', false);
							// @todo: Save trigger date in db
						} catch(Picqer\Financials\Exact\ApiException $e){
							if(strpos($e->getMessage(), 'Data already exists') != false){
								// @todo: Save trigger date in db
								apicenter_logs($project['id'], 'importcustomers', 'Webhook "Accounts" already exists', false);
							}
						}
					}
				}
			}

			//Subscribe to webhook
            $subscribed = $this->Projects_model->getValue('exactonline_subscsribed_webhooks', $project['id']);
            
            if($erpSystem == 'exactonline' && $wms == 'optiply' && $subscribed != 1) {
                $webHooks = new \Picqer\Financials\Exact\WebhookSubscription($connection);
                $webHooks = $webHooks->get();
                foreach ($webHooks as $hook) {
                    $data = $hook->attributes();
                    if(strpos($data['CallbackURL'], 'stock') == true || strpos($data['CallbackURL'], 'importitem') == true
                        || strpos($data['CallbackURL'], 'importbuyorder') == true) {
	                    $del = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
	                        'ID' => $data['ID']
	                    ));
	                    $result = $del->delete();
	                }
                }
                $this->Exactonline_model->subscribeToWebhook($connection, '/optiply/stock', 'StockPositions');
                $this->Exactonline_model->subscribeToWebhook($connection, '/optiply/importbuyorder', 'PurchaseOrders');
				$this->Exactonline_model->subscribeToWebhook($connection, '/optiply/importitem', 'Items');

                $this->Projects_model->saveValue('exactonline_subscsribed_webhooks', '1', $project['id']);
            }
			// update exact article if exact webhook not created for projects
			if($erpSystem == 'exactonline'){
	            $lastExecution      = $this->Projects_model->getValue('article_update_execution', $project['id']);
	            $stocksInterval     = $this->Projects_model->getValue('article_update_interval', $project['id']);
	            $enabled            = $this->Projects_model->getValue('enabled', $project['id']);
	            $stock_enabled      = $this->Projects_model->getValue('articles_update_enabled', $project['id']);
	            // check if the last execution time is satisfy the time checking. for update article
	            if($enabled == '1' && $stock_enabled =='1' && ($lastExecution == '' || ($lastExecution + ($stocksInterval * 60) <= time()))){
					$projectId = $project['id'];
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
	            }
			}
			
			//Execute custom cronjobs
			$customcron_enabled = $this->Projects_model->getValue('customcron_enabled', $project['id']);
			
			if ($customcron_enabled == '1'){
    			// Execute custom cronjobs
    			$projectModel = 'Project'.$project['id'].'_model';
    			if(file_exists(APPPATH."models/".$projectModel.".php")){
    				$this->load->model($projectModel);
    				if(method_exists($this->$projectModel, 'customCronjob')){
    					$this->$projectModel->customCronjob();
    				}
    			}
			}
			
			$METRIC_endtime_projectping = microtime(true);
			apicenter_logs($project['id'], 'projectcontrol', 'Exit Project Ping ;END; took: ' . ($METRIC_endtime_projectping - $METRIC_starttime_projectping ) . ' sec', false );
		}
	}
	
	public function ArticleImportToERP()
	{
	    
	    
	}
	
	public function test(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Eaccounting_model');
		$this->load->model('Accountview_model');
		$this->load->model('Cms_model');
		
		$projects = $this->db->get('projects')->result_array();
		foreach($projects as $project){
			
			if($project['id'] != 141){
				continue;
			}

			// Check if enabled
/*
			if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
				continue;
			}
*/
			
			// Get credentials
			$storeUrl = $project['store_url'];
			$apiKey = $project['api_key'];
			$pluginKey = $project['plugin_key'];
			$storeKey = $project['store_key'];
			$erpSystem = $project['erp_system'];
			$cms = $this->Projects_model->getValue('cms', $project['id']);
			
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
			}
			
			
			// Get articles
			$lastExecution = $this->Projects_model->getValue('article_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('article_interval', $project['id']);
			//if($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time()){
				$articles = array();
				if($erpSystem == 'afas'){
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
$currentArticleOffset = 0;
$articleAmount = 1;
					
					//$this->Projects_model->saveValue('article_offset', $currentArticleOffset + $articleAmount, $project['id']);
					//$this->Projects_model->saveValue('article_last_execution', time(), $project['id']);
					
					$articles = $this->Afas_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					if(empty($articles)){
						//$this->Projects_model->saveValue('article_offset', 0, $project['id']);
					}
				} elseif($erpSystem == 'exactonline') {
					//if($this->Projects_model->getValue('exactonline_import_all_products', $project['id']) == '1'){
						$this->Projects_model->saveValue('exactonline_article_offset', '', $project['id']);
// 						$articlesTmp = $this->Exactonline_model->getArticles($connection);
						$articlesTmp = $this->Exactonline_model->getArticleExtraField($connection, $project['id']);
						$articles['results'] = $articlesTmp;
					//}
				} elseif($erpSystem == 'visma') {
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
						$currentArticleOffset = 0;
						$articleAmount = 10;
					
					$articles = $this->Visma_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
				} elseif($erpSystem == 'eaccounting') {
                    $currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
                    $articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
                        $currentArticleOffset = 0;
                        $articleAmount = 10;
                    
                    $articles = $this->Eaccounting_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
                } elseif($erpSystem == 'accountview') {
					$currentArticleOffset = $this->Projects_model->getValue('article_offset', $project['id']) ? $this->Projects_model->getValue('article_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);
						$currentArticleOffset = 0;
						$articleAmount = 10;
					
					$articles = $this->Accountview_model->getArticles($project['id'], $currentArticleOffset, $articleAmount);
					if(isset($articles['numberOfResults'])){
						$this->Projects_model->saveValue('article_offset', $offset + $articles['numberOfResults'], $project['id']);
					}
				}
				echo '<pre>';print_r($articles);exit;
				$this->Cms_model->updateArticles($project['id'], $articles['results']);
				echo '<pre>';
				
			//}

/*
			// Get stock
			$lastExecution = $this->Projects_model->getValue('stock_last_execution', $project['id']);
			$articleInterval = $this->Projects_model->getValue('stock_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('stock_enabled', $project['id']);
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($articleInterval * 60) <= time())){
				$articles = array();
				if($erpSystem == 'afas'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					$currentArticleOffset = 0;
					
					//$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					//$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Afas_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
//					echo '<pre>';print_r($articles);exit;
					$this->Api2cart_model->updateStockArticles($project['id'], $articles);
					exit;
					if($result['numberOfResults'] < 1){
						//$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						//$this->Projects_model->saveValue('afas_last_update_date', date('Y-m-d'), $project['id']);
					}
				} elseif($erpSystem == 'visma' && $wms != 'optiply'){
					$currentArticleOffset = $this->Projects_model->getValue('stock_offset', $project['id']) ? $this->Projects_model->getValue('stock_offset', $project['id']) : 0;
					$articleAmount = $this->Projects_model->getValue('stock_amount', $project['id']);
					$currentArticleOffset = 0;
					
					$this->Projects_model->saveValue('stock_offset', $currentArticleOffset + $articleAmount, $project['id']);
					$this->Projects_model->saveValue('stock_last_execution', time(), $project['id']);
					
					$result = $this->Visma_model->getStockArticles($project['id'], $currentArticleOffset, $articleAmount);
					$articles = $result['results'];
					if($result['numberOfResults'] < 1){
						$this->Projects_model->saveValue('stock_offset', 0, $project['id']);
						$this->Projects_model->saveValue('visma_last_update_date', date('Y-m-d'), $project['id']);
					}
					$this->Cms_model->updateStockArticles($project['id'], $articles);
				}
			//}
			exit;
*/
			
			
/*
			// Get customers
			$lastExecution = $this->Projects_model->getValue('customers_last_execution', $project['id']);
			$customerInterval = $this->Projects_model->getValue('customers_interval', $project['id']);
			$enabled = $this->Projects_model->getValue('customers_enabled', $project['id']);
			//if($enabled == '1' && ($lastExecution == '' || $lastExecution + ($customerInterval * 60) <= time())){
				if($erpSystem == 'afas'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
					$currentCustomerOffset = 0;
					$customerAmount = 10;
					
					//$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					//$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Afas_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				} elseif($erpSystem == 'exactonline'){
					$customersAmount = 0;
					if($this->Projects_model->getValue('exactonline_import_all_customers', $project['id']) == '1'){
						$customersAmount = $this->Exactonline_model->getDebtors($connection, $project['id']);
					}
				} elseif($erpSystem == 'visma'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
                    
                    //$currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Visma_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				} elseif($erpSystem == 'accountview'){
					$currentCustomerOffset = $this->Projects_model->getValue('customers_offset', $project['id']) ? $this->Projects_model->getValue('customers_offset', $project['id']) : 0;
					$customerAmount = $this->Projects_model->getValue('customers_amount', $project['id']);
                    
                    $currentCustomerOffset = 0;
					
					$this->Projects_model->saveValue('customers_offset', $currentCustomerOffset + $customerAmount, $project['id']);
					$this->Projects_model->saveValue('customers_last_execution', time(), $project['id']);
					$customersAmount = $this->Accountview_model->getDebtors($project['id'], $currentCustomerOffset, $customerAmount);
				}

				if($customersAmount == 0){
					//$this->Projects_model->saveValue('customers_offset', 0, $project['id']);
					//$this->Projects_model->saveValue('exactonline_import_all_customers', '0', $project['id']);
				}
			//}
			exit;
*/
			
/*
			// Send orders
			$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
			$orderAmount = $this->Projects_model->getValue('orders_amount', $project['id']);
			$currentOrderOffset = 9;
			$orderAmount = 1;
			
			$orders = $this->Cms_model->getOrders($project['id'], $currentOrderOffset, $orderAmount);
			if(isset($orders['count'])){
				$getOrderAmount = $orders['count'];
				$orders = $orders['orders'];
			} else {
				$getOrderAmount = count($orders);
			}
			echo '<pre>';print_r($orders);exit;
			
			foreach($orders as $order){
				if($erpSystem == 'afas'){
					$result = $this->Afas_model->sendOrder($project['id'], $order);
				} elseif($erpSystem == 'exactonline'){
					$result = $this->Exactonline_model->sendOrder($connection, $project['id'], $order);
				} elseif($erpSystem == 'visma'){
					$result = $this->Visma_model->sendOrder($project['id'], $order);
				} elseif($erpSystem == 'accountview'){
					$result = $this->Accountview_model->sendOrder($project['id'], $order);
				}
			}
			exit;
*/

/*
			// Get courses
			$lastExecution = $this->Projects_model->getValue('course_last_execution', $project['id']);
			$courseInterval = $this->Projects_model->getValue('course_interval', $project['id']);
			//if($lastExecution == '' || $lastExecution + ($courseInterval * 60) <= time()){
				$courses = array();
				if($erpSystem == 'afas'){
					$currentCourseOffset = $this->Projects_model->getValue('course_offset', $project['id']) ? $this->Projects_model->getValue('course_offset', $project['id']) : 0;
					$courseAmount = $this->Projects_model->getValue('course_amount', $project['id']);
$currentCourseOffset = 0;
$courseAmount = 1;

					
					//$this->Projects_model->saveValue('course_offset', $currentCourseOffset + $courseAmount, $project['id']);
					//$this->Projects_model->saveValue('course_last_execution', time(), $project['id']);
					
					$courses = $this->Afas_model->getCourses($project['id'], $currentCourseOffset, $courseAmount);
					if(empty($courses)){
						//$this->Projects_model->saveValue('course_offset', 0, $project['id']);
					}
				}
				//echo '<pre>';print_r($courses);exit;
				$this->Cms_model->updateCourses($project['id'], $courses['results']);
				echo '<pre>';
				print_r($courses);
				exit;
			//}
*/
			
			
			// Execute custom cronjobs
			$projectModel = 'Project'.$project['id'].'_model';
			if(file_exists(APPPATH."models/".$projectModel.".php")){
				$this->load->model($projectModel);
				if(method_exists($this->$projectModel, 'customCronjob')){
					$this->$projectModel->customCronjob();
				}
			}
		}
		return 'finish';
	}

	public function testcustomcronjob(){ 
		/*$order = array (
			'id' => '',
			'order_id' => '2280689750',
			'store_id' => '',
			'state' => '',
			'status' => '',
			'customer' => 
			array (
			  'id' => '',
			  'email' => '2ioibz4pe37ygpc5rx3ralvznyxzr4@verkopen.bol.com',
			  'first_name' => 'lisette',
			  'last_name' => 'star',
			),
			'create_at' => '2019-04-15T13:10:05.000+02:00',
			'modified_at' => '',
			'currency' => '',
			'totals' => 
			array (
			  'total' => '',
			  'subtotal' => '',
			  'shipping' => '',
			  'tax' => '',
			  'discount' => '',
			  'amount_paid' => 0,
			),
			'billing_address' => 
			array (
			  'id' => '',
			  'type' => 'billing',
			  'first_name' => 'lisette',
			  'last_name' => 'star',
			  'postcode' => '2911 RH',
			  'address1' => 'Tiber 2 ',
			  'address2' => '',
			  'phone' => NULL,
			  'city' => 'NIEUWERKERK AD IJSSEL',
			  'country' => 'NL',
			  'state' => '',
			  'company' => '',
			  'gender' => '',
			  'email' => '2ioibz4pe37ygpc5rx3ralvznyxzr4@verkopen.bol.com',
			),
			'shipping_address' => 
			array (
			  'id' => '',
			  'type' => 'shipping',
			  'first_name' => 'lisette',
			  'last_name' => 'star',
			  'postcode' => '2911 RH',
			  'address1' => 'Tiber 2 ',
			  'address2' => '',
			  'phone' => NULL,
			  'city' => 'NIEUWERKERK AD IJSSEL',
			  'country' => 'NL',
			  'state' => '',
			  'company' => '',
			  'gender' => '',
			  'email' => '2ioibz4pe37ygpc5rx3ralvznyxzr4@verkopen.bol.com',
			),
			'order_products' => 
			array (
			  0 => 
			  array (
				'product_id' => 
				array (
				),
				'order_product_id' => '2221855783',
				'model' => '6926586366665',
				'name' => 'Yunmai Mini Smart - Lichaamsanalyseweegschaal - Wit',
				'quantity' => '1',
				'total_price' => '49.99',
				'total_price_incl_tax' => 0,
				'tax_percent' => 0,
				'tax_value' => 0,
				'variant_id' => '',
			  ),
			),
		);*/
		ini_set('error_reporting', E_ALL);
		ini_set('display_errors', 1);
		ini_set('display_startup_errors', 1);

		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Eaccounting_model');
		$this->load->model('Cms_model');
		$this->load->model('Marketplace_model');
		$this->load->model('Optiply_model');
		
		$projects = $this->db->get('projects')->result_array();
		foreach($projects as $project){
			// Check if enabled
			if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
				continue;
			}
			
			if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
				continue;
			}

			if ($project['id'] != 79) {
				continue;
			}
			// Get credentials
			$storeUrl = $project['store_url'];
			$apiKey = $project['api_key'];
			$pluginKey = $project['plugin_key'];
			$storeKey = $project['store_key'];
			$erpSystem = $project['erp_system'];
			$cms = $this->Projects_model->getValue('cms', $project['id']);
			$connectionType = $project['connection_type'];
			$market_place = $this->Projects_model->getValue('market_place', $project['id']);
			$exportOrders = $this->Projects_model->getValue('send_orders_as', $project['id']);

			//$orders = $this->Marketplace_model->getOrders($project['id']);
			//echo "<pre>";
			//var_dump($orders);exit;
			if($erpSystem == 'exactonline'){
			    if($cms == 'optiply') {
                    $isRunning = $this->Projects_model->getValue('is_running', $project['id']);
                    if($isRunning == '1') { return;}
                    $this->Projects_model->scriptStart($project['id']);
                }

				$this->Exactonline_model->setData(
					array(
						'projectId' => $project['id'],
						'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $project['id']).'/?project_id='.$project['id'],
						'clientId' => $this->Projects_model->getValue('exactonline_client_id', $project['id']),
						'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $project['id']),
					)
				);
				// $this->Exactonline_model->setValue('exact_authorizationcode', null);
				$connection = $this->Exactonline_model->makeConnection($project['id']);
				
				//log_message('debug', 'log messages connect id = ' . $project['id'] . ' / '. var_export($connection, true));

				if(!$connection){ continue; }
			}

			$customersAmount = $this->Exactonline_model->getDebtors($connection, 79);
			echo "<pre>";
			var_export($customersAmount);
			/*foreach($orders as $order){ 
				$result = $this->Exactonline_model->sendInvoice($connection, 78, $order);
				echo "<pre>";
				var_export($result);
			}
			echo "<pre>";
			var_export($result);
			exit;*/
			
		}
	}
	
	public function testorders(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Eaccounting_model');
		$this->load->model('Cms_model');
		$this->load->model('Marketplace_model');
		$this->load->model('Optiply_model');
		
		$projects = $this->db->get('projects')->result_array();
		foreach($projects as $project){
			// Check if enabled
			if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
				continue;
			}
			
			if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
				continue;
			}

			if ($project['id'] != 2) {
				continue;
			}
			
			$currentOrderOffset = $this->Projects_model->getValue('orders_offset', $project['id']) ? $this->Projects_model->getValue('orders_offset', $project['id']) : 0;
			$orderAmount = $this->Projects_model->getValue('orders_amount', $project['id']);
			$getOrderAmount = 0;
			
			$result = $this->Cms_model->getOrders($project['id'], $currentOrderOffset, $orderAmount);
			if(isset($result['count'])){
				$getOrderAmount = $result['count'];
				$orders = $result['orders'];
			} else {
				$orders = $result;
				$getOrderAmount = count($orders);
			}
			echo $currentOrderOffset;
			echo '<pre>';print_r($orders);exit;
			if($orders != false && !empty($orders)){
				$this->Projects_model->saveValue('orders_offset', $currentOrderOffset + $getOrderAmount, $project['id']);
				$this->Projects_model->saveValue('orders_last_execution', time(), $project['id']);
				
				foreach($orders as $order){
				    
					//if ($project['id'] == 68) {
					//	log_message('debug', 'cycl order export');
					//}
					
					if($erpSystem == 'afas'){
						$result = $this->Afas_model->sendOrder($project['id'], $order);
					} 
					elseif($erpSystem == 'exactonline'){
						
						if($project['id'] == 64){
							$exportOrders = 'invoice';
						}
						
						//if ($project['id'] == 68) {
						//	log_message('debug', 'in exact');
						//}
						
					    if ($exportOrders == 'invoice') {
					        
							//if ($project['id'] == 68) {
							//	log_message('debug', 'invoice ? ' . var_export($exportOrders, true));
							//}
                            $result = $this->Exactonline_model->sendInvoice($connection, $project['id'], $order);
                        } 
                        else {
                            //if ($project['id'] == 79) log_message('debug', 'final step OrdExp');
							$result = $this->Exactonline_model->sendOrder($connection, $project['id'], $order);
							//if ($project['id'] == 68) {
							//	log_message('debug', 'result export orders'. var_export($result, true));
							//}
							//if ($project['id'] == 79) {
							//	log_message('debug', 'result export orders'. var_export($result, true));
							//}
                        }
					} 
					elseif($erpSystem == 'visma'){
						//log_message('debug', 'project id = '. $project['id']);
						//log_message('debug', 'visma orders info '. var_export($order, true));
						$result = $this->Visma_model->sendOrder($project['id'], $order);
					}
                    elseif ($erpSystem == 'eaccounting') {
                        if ($exportOrders == 'order' || !$exportOrders) {
                            $result = $this->Eaccounting_model->sendOrder($project['id'], $order);
                        } 
                        elseif ($exportOrders == 'invoice') {
                            $result = $this->Eaccounting_model->sendInvoiceDraft($project['id'], $order);
                        }
                    }
				}
			}
		}
	}
	
	public function customtest(){
		$projectId = $this->input->get('project');
		
		// Execute custom cronjobs
		$projectModel = 'Project'.$projectId.'_model';
		if(file_exists(APPPATH."models/".$projectModel.".php")){
			$this->load->model($projectModel);
			if(method_exists($this->$projectModel, 'customCronjob')){
				$this->$projectModel->customCronjob();
			}
		}
	}
}

/* End of file cronjob.php */
/* Location: ./application/controllers/cronjob.php */