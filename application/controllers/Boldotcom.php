<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Boldotcom extends CI_Controller {

    /**
    * @author Unified
    * @return NULL
    */
    public function __construct(){
        parent::__construct();
        $this->load->helper('tools');
        $this->load->helper('constants');
    }

    ##################################################################################################
    # Request payload : NULL                                                      
    # Response        : NULL
    # logic           : This function is used to import or update article and orders in Bol.com From different webshops
    # use in          : Boldotcom Controller
    # Request URL     : Called through cronjob      
    ##################################################################################################

    public function boldotcomCronJob(){
        $this->importArticleToBoldotcom();

        // call to import orders to webhop from bol.com instead of creating a seprate cron 
         $this->importOrdersToWebshops();

        // update products based on last updated date in webhop and erp system
        $this->updateArticlesInBoldotcom();
    }

    ##################################################################################################
    ### logic : This function is used to import or update article in Bol.com From different webshops
    ##################################################################################################
    public function importArticleToBoldotcom(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        // get all projects having erp system exact  with webshop woocommerce.
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                    continue;
                if($projectId!=26)
                    continue;
                $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                $ch_enabled         = $this->Projects_model->getValue('enabled', $projectId);
                $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                $product_id         = isset($_GET['product_id'])?$_GET['product_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                if($ch_enabled == 1 && $enabled == 1 && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $ui = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('articles_last_execution', $ui, $projectId);
                    if($p_value['connection_type']==3){
                        // import magento products to bol.com
                        if($this->Projects_model->getValue('cms', $projectId)=='magento2'){
                            $this->importMagentoProducts($projectId, $product_id);
                        }
                    } else  if($p_value['connection_type']==2){
                        if($p_value['erp_system'] == 'afas'){
                            // import afas products to bol.com
                            if ($projectId == 27) {$this->importAfasProducts($projectId, $product_id);}
                        } else if ($p_value['erp_system']=='exactonline') {
                            // import exact products to bol.com
                            //$this->importArticleFromExactOnline($projectId);
                        }
                    }
                }
            }
        }
    }

    ##################################################################################################
    # logic  : This function is used to import or update article in BolDotCom from ExactOnline
    # use in : Boldotcom.importArticleToBoldotcom
    ##################################################################################################
    public function importArticleFromExactOnline($projectId=''){

        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Bol_exact_model');
            $this->load->model('Woocommerce_exactonline_model');
            // get all projects having erp system exact  with webshop woocommerce.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='bol' || $p_value['erp_system']!='exactonline')
                        continue;
                    $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                    $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                    $enabled_con        = $this->Projects_model->getValue('enabled', $projectId);
                    $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                    $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; 
                    $itemCode           = isset($_GET['itemCode'])?$_GET['itemCode']:''; 
                    // check if the last execution time is satisfy the time checking. customers_amount
                   if($enabled_con == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                        //reset last execution time
                        $ui = $lastExecution + ($customersInterval * 60);
                        $this->Projects_model->saveValue('articles_last_execution', $ui, $projectId);
                        // get the offset and amount to import customers. 
                        $offset    =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : NULL;
                        $amount    = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
                        //--------------- make exact connection ----------------------------------//
                        $this->Exactonline_model->setData(
                            array(
                                'projectId'     => $projectId,
                                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                            )
                        );
                        $connection = $this->Exactonline_model->makeConnection($projectId);
                        //-------- get params from project setting  ----------------------------------//
                        $is_published = $this->Projects_model->getValue('import_as_published', $projectId) ? $this->Projects_model->getValue('import_as_published', $projectId) : 1;
                        $impost_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
                        $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
                        $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
                        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
                        $import_option_array = ['import_exact_description'=>$import_exact_description, 'import_exact_extra_description'=>$import_exact_extra_description, 'woocommerce_stock_options'=>$woocommerce_stock_options];
                        
                        //  get article from exactonline based on amount and offset --------------       //
                        $items = $this->Woocommerce_exactonline_model->getExactArticle($connection, $itemId, $offset, $amount, $is_published,$itemCode, $impost_exact_image, $import_option_array);
                        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId) ? $this->Projects_model->getValue('total_article_import_success', $projectId):0;
                        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
                        // -- call exact_bol_model to create and update article in bol.com  ------       //
                        $numberOfResults = sizeof($items);
                        if ($numberOfResults > 0) {
                            foreach ($items as $itemkey => $item) {
                                $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
                                if($bol_live_mode==0)
                                    $bol_live_mode = true;
                                else
                                    $bol_live_mode = false;
                                $upsert_product = $this->Bol_exact_model->upsertOffer($projectId, $item, $bol_live_mode);
                                if(isset($upsert_product['message'])){
                                    // for unknown error
                                    $message = "Error:".$upsert_product['message'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles',$message);
                                } elseif($upsert_product['code']==202){
                                    // sussess 
                                    $totalArticleImportSuccess++;
                                    $message = 'Success: Offer EAN:'.$item['Barcode'].' updated successfully';
                                    project_error_log($projectId, 'importarticles', $message);
                                } else{
                                    $message = 'Error: Offer EAN:'.$item['Barcode'].' Failed , Message ';
                                    $error = '';
                                    if(isset($upsert_product['result']['ServiceErrors']))
                                        $error = isset($upsert_product['result']['ServiceErrors']['ServiceError'])?' Code: '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                                    if(isset($upsert_product['result']['ValidationErrors']))
                                        $error = isset($upsert_product['result']['ValidationErrors']['ValidationError'])?' Code: '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                                    $message.= $error.'- Sku :'.$item['Barcode'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles', $message);
                                }
                            }
                            $offset =  $offset + $numberOfResults;
                        } else{
                            $offset = 0;
                        }
                        $offset =  $this->Projects_model->saveValue('article_offset', $offset, $projectId) ;
                        $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
                        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
                    }
                }
            }
        }
    }

    ##################################################################################################
    # logic  : This function is used to import or update article in Bol.com From Magento webshops
    # use in : Boldotcom.importArticleToBoldotcom
    ##################################################################################################
    public function importMagentoProducts($projectId='', $product_id=''){
        if($projectId!=''){
            $this->load->model('Magentobol_model');
            $this->load->model('Projects_model');
            // get the offset and amount to import customers. 
            $offset =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : 0;
            $article_page_no =  $this->Projects_model->getValue('article_page_no', $projectId) ? $this->Projects_model->getValue('article_page_no', $projectId) : 1;
            $amount = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
            $products_list = $this->Magentobol_model->getProductsList($projectId, $product_id, $article_page_no, $amount);
            $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
            $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
            if(isset($products_list['message'])){
                $message = "Error:".$products_list['message'];
                $totalArticleImportError++;
                project_error_log($projectId, 'importarticles',$message);
            } else{
                if($products_list['total_count']>0){
                    $last_article = '';
                    foreach ($products_list['items'] as $p_key => $p_value) {
                        $last_article = $p_value['id'];
                        if($offset>=$p_value['id'])
                            continue;
                        $offset       = $p_value['id'];                       
                        $products_details = $this->Magentobol_model->getProductDetails($projectId, $p_value['sku']);
                        log_message('error', 'Received information from Magento for bol connection:');
                        log_message('error', var_export($products_details, true));
                        if(isset($products_details['message'])){
                            $message = "Error:".$products_details['message'];
                            $totalArticleImportError++;
                            project_error_log($projectId, 'importarticles',$message);
                        } else{
                            $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
                            if($bol_live_mode==0)
                                $bol_live_mode = true;
                            else
                                $bol_live_mode = false;
                            // print_r($products_details);
                            // exit();
                            $upsert_product = $this->Boldotcom_model->upsertOffer($projectId, $products_details, $bol_live_mode);
                            if($upsert_product){
                                if(isset($upsert_product['message'])){
                                    $message = "Error:".$upsert_product['message'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles',$message);
                                } else if($upsert_product['code']==202){
                                    $totalArticleImportSuccess++;
                                    $message = 'Success: Offer sku:'.$products_details['sku'].' updated successfully';
                                    project_error_log($projectId, 'importarticles',$message);
                                } else{
                                    $message = 'Error: Offer sku:'.$products_details['sku'].' Failed , Message ';
                                    $error = '';
                                    if(isset($upsert_product['result']['ServiceErrors']))
                                        $error = isset($upsert_product['result']['ServiceErrors']['ServiceError'])?' Code: '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                                    if(isset($upsert_product['result']['ValidationErrors']))
                                        $error = isset($upsert_product['result']['ValidationErrors']['ValidationError'])?' Code: '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                                    $message.= $error.'- Sku :'.$p_value['sku'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles',$message);
                                }
                            }
                        }
                    }
                    if($last_article!='')
                        $article_page_no++;
                }
                $this->Projects_model->saveValue('article_page_no', $article_page_no, $projectId) ;
                $this->Projects_model->saveValue('article_offset', $offset, $projectId) ;
            }
            $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
            $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
        }
    }

    ################################################################################################
    #    function is used to import or update article in Bol.com From afas erp system .            #
    ################################################################################################
    public function importAfasProducts($projectId, $product_id){
        $this->load->model('Afasbol_model');
        $this->load->model('Projects_model');
        // get the offset and amount to import customers. 
        $offset =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : 0;
        $amount = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
        $filter = true;
        $products_list = $this->Afasbol_model->getArticles($projectId, $product_id, $offset, $amount, $filter);
        $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
        $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
        if($products_list['numberOfResults']>0){
            foreach ($products_list['results'] as $p_key => $p_value) {
                $formated_product = $this->Boldotcom_model->upsertFormatOffer($projectId, $p_value);
                if($formated_product['status']==1)
                    $upsert_product   = $this->Boldotcom_model->callUpserOffer($projectId, $formated_product['result']); 
                else{
                    $message = $formated_product['result'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles', $message);
                    continue;
                }
                if(isset($upsert_product['message'])){
                    $message = "Error:".$upsert_product['message'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles',$message);
                } elseif($upsert_product['code']==202){
                    $totalArticleImportSuccess++;
                    $message = 'Success: Offer EAN:'.$formated_product['EAN'].' updated successfully';
                    project_error_log($projectId, 'importarticles', $message);
                } else{
                    $message = 'Error: Offer EAN:'.$formated_product['EAN'].' Failed , Message ';
                    $error = '';
                    if(isset($upsert_product['result']['ServiceErrors']))
                        $error = isset($upsert_product['result']['ServiceErrors']['ServiceError'])?' Code: '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                    if(isset($upsert_product['result']['ValidationErrors']))
                        $error = isset($upsert_product['result']['ValidationErrors']['ValidationError'])?' Code: '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                    $message.= $error.'- Sku :'.$p_value['sku'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles', $message);
                }
            }
            $offset =  $offset + $products_list['numberOfResults'];
        } else{
            $offset = 0;
        }
        $offset =  $this->Projects_model->saveValue('article_offset', $offset, $projectId) ;
        $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
        $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
    }

    ################################################################################################
    #  function is used to update stocks from recent updated in Bol.com From different webshops .  #
    ################################################################################################
    public function updateArticlesInBoldotcom(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($projectId!=26)
                    continue;
                if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('article_update_execution', $projectId);
                $stocksInterval     = $this->Projects_model->getValue('article_update_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                $stock_enabled      = $this->Projects_model->getValue('articles_update_enabled', $projectId);
                $product_id         = isset($_GET['product_id'])?$_GET['product_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                if($stock_enabled == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($stocksInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('stock_last_execution', time(), $projectId);
                    if($p_value['connection_type']==2){
                        if($p_value['erp_system'] == 'afas'){
                            // $this->updateAfasBoldotcomStocks($projectId, $product_id);
                        }
                    }  else if($p_value['connection_type']==3){
                        if($this->Projects_model->getValue('cms', $projectId)=='magento2'){
                           $this->updateMagentoBoldotcomStocks($projectId, $product_id);
                        }
                    } 
                }
            }
        }        
    }

    ################################################################################################
    #     function is used to update stocks from recent updated in Bol.com From afas erp system .  #
    ################################################################################################
    public function updateAfasBoldotcomStocks($projectId, $product_id=''){
        $this->load->model('Afasbol_model');
        $this->load->model('Projects_model');
        // get the offset and amount to import customers. 
        $offset                 =  $this->Projects_model->getValue('stock_offset', $projectId) ? $this->Projects_model->getValue('stock_offset', $projectId) : 0;
        $amount                 = $this->Projects_model->getValue('stock_amount', $projectId) ? $this->Projects_model->getValue('stock_amount', $projectId) : 10;
        $products_stock_list    = $this->Afasbol_model->getArticlesStock($projectId, $product_id, $offset, $amount);
        $totalStockUpdateSuccess= $this->Projects_model->getValue('total_stock_update_success', $projectId)?$this->Projects_model->getValue('total_stock_update_success', $projectId):0;
        $totalStockUpdateError  = $this->Projects_model->getValue('total_stock_update_error', $projectId)?$this->Projects_model->getValue('total_stock_update_error', $projectId):0;
        if($products_stock_list['numberOfResults']>0){
            foreach ($products_stock_list['results'] as $p_key => $p_value) {
                $formated_product = $this->Boldotcom_model->upsertFormatOffer($projectId, $p_value);
                if($formated_product['status']==1)
                    $upsert_product   = $this->Boldotcom_model->callUpserOffer($projectId, $formated_product['result']); 
                else{
                    $message = $formated_product['result'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles', $message);
                    continue;
                }
                if(isset($upsert_product['message'])){
                    $message = "Error:".$upsert_product['message'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles',$message);
                } elseif($upsert_product['code']==202){
                    $totalArticleImportSuccess++;
                    $message = 'Success: Offer EAN:'.$formated_product['EAN'].' updated successfully';
                    project_error_log($projectId, 'importarticles', $message);
                } else{
                    $message = 'Error: Offer EAN:'.$formated_product['EAN'].' Failed , Message ';
                    $error = '';
                    if(isset($upsert_product['result']['ServiceErrors']))
                        $error = isset($upsert_product['result']['ServiceErrors']['ServiceError'])?' Code: '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                    if(isset($upsert_product['result']['ValidationErrors']))
                        $error = isset($upsert_product['result']['ValidationErrors']['ValidationError'])?' Code: '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                    $message.= $error.'- Sku :'.$p_value['sku'];
                    $totalArticleImportError++;
                    project_error_log($projectId, 'importarticles', $message);
                }
            }
            $offset =  $offset + $products_stock_list['numberOfResults'];
        } else{
            $offset = 0;
        }
        $offset =  $this->Projects_model->saveValue('article_offset', $offset, $projectId) ;
        $this->Projects_model->saveValue('total_stock_update_success', $totalStockUpdateSuccess, $projectId);
        $this->Projects_model->saveValue('total_stock_update_error', $totalStockUpdateError, $projectId);
    }

    ################################################################################################
    #     function is used to update stocks from recent updated in Bol.com From Magento webshops.  #
    ################################################################################################
    public function updateMagentoBoldotcomStocks($projectId='', $product_id=''){
        if($projectId!=''){
            $this->load->model('Magentobol_model');
            $this->load->model('Projects_model');
            $this->load->model('Boldotcom_model');
            $lastUpdateDate = $this->Projects_model->getValue('webshop_article_last_update_date', $projectId)?$this->Projects_model->getValue('webshop_article_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate = date("Y-m-d H:i:00", strtotime($lastUpdateDate));
            //$lastUpdateDate = '2018-11-27 12:45:00';
            $currentdatetime = date("Y-m-d H:i:00");
            // get the offset and amount to update aticle. 
            $offset =  $this->Projects_model->getValue('article_update_offset', $projectId) ? $this->Projects_model->getValue('article_update_offset', $projectId) : '';
            $amount = $this->Projects_model->getValue('article_update_amount', $projectId) ? $this->Projects_model->getValue('article_update_amount', $projectId) : 10;
            $import_option_array = ['lastUpdateDate'=>$lastUpdateDate];
            $products_list = $this->Magentobol_model->getProductsStocks($projectId, $product_id, $offset, $amount,$import_option_array);
            // print_r($products_list);
            // exit();
            $totalArticleImportSuccess = $this->Projects_model->getValue('total_article_import_success', $projectId)?$this->Projects_model->getValue('total_article_import_success', $projectId):0;
            $totalArticleImportError = $this->Projects_model->getValue('total_article_import_error', $projectId)?$this->Projects_model->getValue('total_article_import_error', $projectId):0;
            if(isset($products_list['message'])){
                $message = "Error:".$products_list['message'];
                $totalArticleImportError++;
                project_error_log($projectId, 'importarticles',$message);
            } else{
                if($products_list['total_count']>0){
                    foreach ($products_list['items'] as $p_key => $p_value) {
                        $products_details = $this->Magentobol_model->getProductDetails($projectId, $p_value['sku']);
			            log_message('error', 'Received information from Magento for bol connection (stock update):');
			            log_message('error', var_export($products_details, true));
                        if(isset($products_details['message'])){
                            $message = "Error:".$products_details['message'];
                            $totalArticleImportError++;
                            project_error_log($projectId, 'importarticles',$message);
                        } else{
                            $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
                            if($bol_live_mode==0)
                                $bol_live_mode = true;
                            else
                                $bol_live_mode = false;
                            $upsert_product = $this->Boldotcom_model->upsertOffer($projectId, $products_details, $bol_live_mode);
                            // echo "string";
                            // print_r($upsert_product);
                            //exit();
                            if($upsert_product){
                                if(isset($upsert_product['message'])){
                                    $message = "Error:".$upsert_product['message'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles',$message);
                                } else if($upsert_product['code']==202){
                                    $totalArticleImportSuccess++;
                                    $message = 'Success: Offer sku:'.$products_details['sku'].' updated successfully';
                                    project_error_log($projectId, 'importarticles',$message);
                                } else{
                                    $message = 'Error: Offer sku:'.$products_details['sku'].' Failed , Message ';
                                    $error = '';
                                    if(isset($upsert_product['result']['ServiceErrors']))
                                        $error = isset($upsert_product['result']['ServiceErrors']['ServiceError'])?' Code: '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$upsert_product['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                                    if(isset($upsert_product['result']['ValidationErrors']))
                                        $error = isset($upsert_product['result']['ValidationErrors']['ValidationError'])?' Code: '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$upsert_product['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                                    $message.= $error.'- Sku :'.$p_value['sku'];
                                    $totalArticleImportError++;
                                    project_error_log($projectId, 'importarticles',$message);
                                }
                            }
                        }
                    }
                    $offset++;
                    $this->Projects_model->saveValue('article_update_offset', $offset, $projectId);
                } else{
                    $this->Projects_model->saveValue('webshop_article_last_update_date', $currentdatetime, $projectId);
                    $this->Projects_model->saveValue('article_update_offset', null, $projectId);
                }
            }
            $this->Projects_model->saveValue('total_article_import_success', $totalArticleImportSuccess, $projectId);
            $this->Projects_model->saveValue('total_article_import_error', $totalArticleImportError, $projectId);
        }
    } 

    ################################################################################################
    #             function is used to import orders in webshops  from bol.com.                     #
    ################################################################################################
    public function importOrdersToWebshops(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        // get all projects having marketplace  bol.com exact  with webshop .
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->where('id',26)->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('orders_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                $orders_id          = isset($_GET['orders_id'])?$_GET['orders_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $ui = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('orders_last_execution', $ui, $projectId);
                    if($p_value['connection_type']==3){
                        if($this->Projects_model->getValue('cms', $projectId)=='magento2'){
                            // $this->cancelMagentoOrdersInBol($projectId);
                            $this->importOrdersInMagento($projectId, $orders_id);
                            $this->importShipmentInBol($projectId);
                            $this->bolProcessStatus($projectId);
                            // $this->cancelBolOrdersInMagento($projectId, $orders_id);
                        }
                    }
                }
            }
        }
    }

    ################################################################################################
    #             function is used to import orders in Magento webshps from bol.com.               #
    ################################################################################################
    public function importShipmentInBol($projectId = ''){
        if($projectId!=''){
            $this->load->model('Boldotcom_model');
            $this->load->model('Magentobol_model');
            $this->load->model('Projects_model');
            $lastUpdateDate = $this->Projects_model->getValue('webshop_shipment_last_update_date', $projectId)?$this->Projects_model->getValue('webshop_shipment_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate = date("Y-m-d H:i:00", strtotime($lastUpdateDate));
            $currentdatetime = date("Y-m-d H:i:00", strtotime($lastUpdateDate . "+55 minutes"));
            if(strtotime($currentdatetime) > strtotime(date("Y-m-d H:i:00")))
                $currentdatetime = date("Y-m-d H:i:00");
            $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
            if($bol_live_mode==0)
                $bol_live_mode = true;
            else
                $bol_live_mode = false;
            
            // $lastUpdateDate = '2018-09-29 04:00:00';
            // $currentdatetime = '2018-11-29 04:00:00';
            $updated_orders_list = $this->Magentobol_model->getUpdatedOrdersShipment($projectId, $lastUpdateDate, $currentdatetime);
            // print_r($updated_orders_list);
            // exit();
            // exit();
            //$updated_orders_list = $this->Magentobol_model->getUpdatedOrdersShipmentByOrder($projectId, 3742);
            // print_r($updated_orders_list);
            // exit();

            // $updated_orders_list = $this->Magentobol_model->getSingleOrder($projectId,'3484');
            // print_r($updated_orders_list);
            // exit();
            // $updated_orders_list = $this->Magentobol_model->getAllOrder($projectId);
            $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
            $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
            $bol_trackandtrace_code = $this->Projects_model->getValue('bol_trackandtrace_code', $projectId)?$this->Projects_model->getValue('bol_trackandtrace_code', $projectId):'';
            if(!isset($updated_orders_list['message'])){
                if($updated_orders_list['total_count']>0){
                    foreach ($updated_orders_list['items'] as $s_key => $s_value) {
                        $tracks = $s_value['tracks'];
                        $trace_and_trac = '';
                        if(!empty($tracks) && $bol_trackandtrace_code!=''){
                            $trace_and_trac = isset($tracks[0][$bol_trackandtrace_code])?$tracks[0][$bol_trackandtrace_code]:'';
                        }
                        $get_Orders_details = $this->Magentobol_model->getSingleOrder($projectId, $s_value['order_id']);
                        if(!isset($get_Orders_details['message'])){
                            if($get_Orders_details['total_count']>0){
                                foreach ($get_Orders_details['items'] as $p_key => $p_value) {
                                    $ext_order_id = '';
                                    $ext_order_idd = isset($p_value['customer_email'])?$p_value['customer_email']:'';
                                    if($ext_order_idd!=''){
                                        $ext_order_id = explode('.', $ext_order_idd)[0];
                                        $OrderItemId = explode('.', $ext_order_idd)[0];
                                    } else{
                                        continue;
                                    }
                                    if($ext_order_id !=''){
                                        $entity_id  = $s_value['entity_id'];
                                        $get_Single_details = $this->Boldotcom_model->getSingleOrder($projectId, $ext_order_id, $bol_live_mode);
                                        if($get_Single_details){
                                            if($get_Single_details['code']==200){
                                                if(!isset($get_Single_details['result']['Order']))
                                                    continue;
                                                $order_result = $get_Single_details['result']['Order']['OrderItems'];
                                                foreach ($order_result as $key => $value) {
                                                    $cancel_order = $this->Boldotcom_model->shipmentOrder($projectId, $value['OrderItemId'], $bol_live_mode, $entity_id, $trace_and_trac);
                                                    if($cancel_order){
                                                        if(isset($cancel_order['message'])){
                                                            $message = "Error:".$cancel_order['message'];
                                                            $totalOrderImportError++;
                                                            project_error_log($projectId, 'exportorders',$message);
                                                        } else if($cancel_order['code']==200 || $cancel_order['code']==201 ){
                                                            $cancel_order    = str_replace(array('ns1:', 'ns2:'), array('', ''), $cancel_order['result']);
                                                            $xml    = simplexml_load_string($cancel_order);
                                                            $json   = json_encode($xml);
                                                            $array  = json_decode($json,TRUE);
                                                            $process_status_id  = $array['id'];
                                                            $data = array(
                                                                'project_id' => $projectId,
                                                                'order_id' => $ext_order_id,
                                                                'order_item_id' => $OrderItemId,
                                                                'process_id' => $process_status_id,
                                                                'created_date' => date('Y-m-d H:i:s')
                                                            );
                                                            $this->db->insert('bol_process_status', $data);
                                                            $totalOrderImportSuccess++;
                                                            $message = 'Success: CONFIRM_SHIPMENT request for OrderItemId :'.$OrderItemId.' and Order id :'.$entity_id.' submitted successfully';
                                                            project_error_log($projectId, 'exportorders',$message);
                                                        } else{
                                                            $message = 'Error: Order id :'.$entity_id.' Failed , Message ';
                                                            $error = '';   
                                                            if(isset($cancel_order['result']['errorMessage']))
                                                                $error = isset($cancel_order['result']['errorMessage'])?' Code: '.$cancel_order['result']['errorCode'].' - '.$cancel_order['result']['errorMessage']:'';
                                                            if(isset($cancel_order['result']['ServiceErrors']))
                                                                $error = isset($cancel_order['result']['ServiceErrors']['ServiceError'])?' Code: '.$cancel_order['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$cancel_order['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                                                            if(isset($cancel_order['result']['ValidationErrors']))
                                                                $error = isset($cancel_order['result']['ValidationErrors']['ValidationError'])?' Code: '.$cancel_order['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$cancel_order['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                                                            $message.= $error;
                                                            $totalOrderImportError++;
                                                            project_error_log($projectId, 'exportorders',$message);
                                                        }
                                                    }
                                                }                                   
                                            }
                                        }
                                    } 
                                }
                            }
                        }
                    }
                } 
                $this->Projects_model->saveValue('webshop_shipment_last_update_date', $currentdatetime, $projectId);
            } else{
                $message = "Error:".$updated_orders_list['message'];
                $totalOrderImportError++;
                project_error_log($projectId, 'exportorders',$message);
            }
        } 
    }

    ################################################################################################
    #             function is used to cancel orders in bol.com from Magento webshps .              #
    ################################################################################################
    public function cancelMagentoOrdersInBol($projectId){
        if($projectId!=''){
            $this->load->model('Boldotcom_model');
            $this->load->model('Magentobol_model');
            $this->load->model('Projects_model');
            $lastUpdateDate = $this->Projects_model->getValue('webshop_order_last_update_date', $projectId)?$this->Projects_model->getValue('webshop_order_last_update_date', $projectId):date("Y-m-d 00:00:00");
            $lastUpdateDate = date("Y-m-d H:i:00", strtotime($lastUpdateDate));
            $currentdatetime = date("Y-m-d H:i:00", strtotime($lastUpdateDate . "+55 minutes"));
            $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
            if($bol_live_mode==0)
                $bol_live_mode = true;
            else
                $bol_live_mode = false;
            $updated_orders_list = $this->Magentobol_model->getUpdatedOrders($projectId, $lastUpdateDate, $currentdatetime);
            $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
            $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
            if(!isset($updated_orders_list['message'])){
                if($updated_orders_list['total_count']>0){
                    foreach ($updated_orders_list['items'] as $p_key => $p_value) {
                        $ext_order_id = '';
                        $ext_order_idd = isset($p_value['customer_email'])?$p_value['customer_email']:'';
                        if($ext_order_idd!=''){
                            $ext_order_id = explode('.', $ext_order_idd)[0];
                            $OrderItemId = explode('.', $ext_order_idd)[1];
                        } else{
                            continue;
                        }
                        if($p_value['status']=='canceled' && $ext_order_id !=''){
                            $updated_at  = $p_value['updated_at'];
                            $get_Single_details = $this->Boldotcom_model->getSingleOrder($projectId, $ext_order_id, $bol_live_mode);
                            if($get_Single_details){
                                if($get_Single_details['code']==200){
                                    if(!isset($get_Single_details['result']['Order']))
                                        continue;
                                    $order_result = $get_Single_details['result']['Order']['OrderItems'];
                                    foreach ($order_result as $key => $value) {
                                        if(!$OrderItemId==$value['OrderItemId'])
                                            continue;
                                        $cancel_order = $this->Boldotcom_model->cancelOrder($projectId, $OrderItemId, $bol_live_mode, $updated_at);
                                        if($cancel_order){
                                            if(isset($cancel_order['message'])){
                                                $message = "Error:".$cancel_order['message'];
                                                $totalOrderImportError++;
                                                project_error_log($projectId, 'exportorders',$message);
                                            } else if($cancel_order['code']==200 || $cancel_order['code']==201 ){
                                                $cancel_order    = str_replace(array('ns1:', 'ns2:'), array('', ''), $cancel_order['result']);
                                                $xml    = simplexml_load_string($cancel_order);
                                                $json   = json_encode($xml);
                                                $array  = json_decode($json,TRUE);
                                                $process_status_id  = $array['id'];
                                                $data = array(
                                                    'project_id' => $projectId,
                                                    'order_id' => $ext_order_id,
                                                    'order_item_id' => $OrderItemId,
                                                    'process_id' => $process_status_id,
                                                    'created_date' => date('Y-m-d H:i:s')
                                                );
                                                $this->db->insert('bol_process_status', $data);
                                                $totalOrderImportSuccess++;
                                                $message = 'Success: CANCEL_ORDER request for OrderItemId :'.$OrderItemId.' and Order id :'.$ext_order_id.' submitted successfully';
                                                project_error_log($projectId, 'exportorders',$message);
                                            } else{
                                                $message = 'Error: Order id :'.$ext_order_id.' Failed , Message ';
                                                $error = '';   
                                                if(isset($cancel_order['result']['errorMessage']))
                                                    $error = isset($cancel_order['result']['errorMessage'])?' Code: '.$cancel_order['result']['errorCode'].' - '.$cancel_order['result']['errorMessage']:'';
                                                if(isset($cancel_order['result']['ServiceErrors']))
                                                    $error = isset($cancel_order['result']['ServiceErrors']['ServiceError'])?' Code: '.$cancel_order['result']['ServiceErrors']['ServiceError']['ErrorCode'].' - '.$cancel_order['result']['ServiceErrors']['ServiceError']['ErrorMessage']:'';
                                                if(isset($cancel_order['result']['ValidationErrors']))
                                                    $error = isset($cancel_order['result']['ValidationErrors']['ValidationError'])?' Code: '.$cancel_order['result']['ValidationErrors']['ValidationError']['ErrorCode'].' - '.$cancel_order['result']['ValidationErrors']['ValidationError']['ErrorMessage']:'';
                                                $message.= $error;
                                                $totalOrderImportError++;
                                                project_error_log($projectId, 'exportorders',$message);
                                            }
                                        }
                                    }                                   
                                }
                            }
                        } 
                    }
                }
                $this->Projects_model->saveValue('webshop_order_last_update_date', $currentdatetime, $projectId);
            } else{
                $message = "Error:".$updated_orders_list['message'];
                $totalOrderImportError++;
                project_error_log($projectId, 'exportorders',$message);
            }
        }       
    }

    ################################################################################################
    #             function is used to import orders in Magento webshps from bol.com.               #
    ################################################################################################
    public function importOrdersInMagento($projectId, $orders_id){
        $this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        $this->load->model('Projects_model');
        $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
        if($bol_live_mode==0)
            $bol_live_mode = true;
        else
            $bol_live_mode = false;

        $orders_list = $this->Boldotcom_model->getOrders($projectId, $bol_live_mode);
        // $orders_list = $this->testOrder();
        if ($orders_list['code']==200) {
            $result = $orders_list['result'];
            $orders = isset($result['Order'])?$result['Order']:array();
            log_message('debug', 'Try to export orders from bol.com: ');
            log_message('debug', var_export($orders));
            if(!empty($orders)){
                if(isset($orders[0])){
                    foreach ($orders as $key => $value) {
                        $order = $this->Magentobol_model->importBundelsOrdersInMagento($projectId, $value);
                    }
                } else{
                    $order = $this->Magentobol_model->importBundelsOrdersInMagento($projectId, $orders);
                }
            }
        }else if($orders_list['code']==401){
            $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
            $error = isset($orders_list['result']['serviceError'])?' Code: '.$orders_list['result']['serviceError']['ErrorCode'].' - '.$orders_list['result']['serviceError']['ErrorMessage']:' Access denied ';
            $message = "Error :: ".$error;
            $totalOrderImportError++;
            project_error_log($projectId, 'exportorders',$message);
            $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
        }
    }

    public function bolProcessStatus($projectId=''){
        if($projectId=='')
            return;
        $this->load->model('Boldotcom_model');
        $this->load->model('Projects_model');
        $projects = $this->db->where('project_id',$projectId)->get('bol_process_status')->result_array();
        if(!empty($projects)){
            $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
            $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
            $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
            if($bol_live_mode==0)
                $bol_live_mode = true;
            else
                $bol_live_mode = false;
            foreach ($projects as $key => $value) {
                $process_status = $this->Boldotcom_model->processStatus($projectId, $value['process_id'], $bol_live_mode);
                // print_r($process_status);
                // exit();
                if($process_status){
                    if(isset($process_status['message'])){
                        $message = "Error:".$process_status['message'];
                        $totalOrderImportError++;
                        project_error_log($projectId, 'exportorders',$message);
                    } else if($process_status['code']==200 || $process_status['code']==201 ){
                        $process_status    = str_replace(array('ns1:', 'ns2:'), array('', ''), $process_status['result']);
                        $xml    = simplexml_load_string($process_status);
                        $json   = json_encode($xml);
                        $array  = json_decode($json,TRUE);
                        $process_status_id  = $array['id'];
                        if($array['status']=='PENDING')
                            continue;
                        else if($array['status']=='SUCCESS'){
                            $totalOrderImportSuccess++;
                            $message = 'Success: '.$array['eventType'].' request for OrderItemId :'.$value['order_item_id'].' and Order id :'.$value['order_id'].' executed successfully message:'.$array['description'];
                            project_error_log($projectId, 'exportorders',$message);
                            $this->db->where('id', $value['id']);
                            $this->db->delete('bol_process_status'); 
                        } else if($array['status']=='FAILURE'){
                            $totalOrderImportError++;
                            $message = 'Error: '.$array['eventType'].' request for OrderItemId :'.$value['order_item_id'].' and Order id :'.$value['order_id'].' Failed, Message:'.$array['errorMessage'];
                            project_error_log($projectId, 'exportorders',$message); 
                            $this->db->where('id', $value['id']);
                            $this->db->delete('bol_process_status'); 
                        }                        
                    } else{
                        $message = 'Error: Process status id :'.$value['process_id'].' Not found ';
                        $totalOrderImportError++;
                        project_error_log($projectId, 'exportorders',$message);
                    }
                } else{
                    $message = 'Error: Process status id :'.$value['process_id'].' Not found ';
                    $totalOrderImportError++;
                    project_error_log($projectId, 'exportorders',$message);
                }
            }
            $this->Projects_model->saveValue('total_orders_import_success', $totalOrderImportSuccess, $projectId);
            $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
        }
    }



    ################################################################################################
    #             function is used to import orders in Magento webshps from bol.com.  pending      #
    ################################################################################################
    public function cancelBolOrdersInMagento($projectId, $orders_id){
        $this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
        if($bol_live_mode==0)
            $bol_live_mode = true;
        else
            $bol_live_mode = false;
        $orders_list = $this->Boldotcom_model->getOrders($projectId, $bol_live_mode);
        print_r($orders_list);
        exit();
        //$orders_list = $this->testOrder();
        $totalOrderImportSuccess = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):0;
        $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
        if ($orders_list['code']==200) {
            $result = $orders_list['result'];
            $orders = isset($result['Order'])?$result['Order']:array();
            //$orders = isset($result)?$result:array();
            if(!empty($orders)){
                if(isset($orders[0])){
                    foreach ($orders as $key => $value) {
                        $order = $this->Magentobol_model->importOrdersInMagento($projectId, $value);
                        if (isset($order['status']) && $order['status']=1) {
                            $totalOrderImportSuccess++;
                            project_error_log($projectId, 'exportorders', $order['message']);
                        } else {
                            $totalOrderImportError++;
                            project_error_log($projectId, 'exportorders', $order['message']);
                        }
                    }
                } else{
                    $order = $this->Magentobol_model->importOrdersInMagento($projectId, $orders);
                    if (isset($order['status']) && $order['status']=1) {
                        $totalOrderImportSuccess++;
                        project_error_log($projectId, 'exportorders', $order['message']);
                    } else {
                        $totalOrderImportError++;
                        project_error_log($projectId, 'exportorders', $order['message']);
                    }
                }
                $this->Projects_model->saveValue('total_orders_import_success', $totalOrderImportSuccess, $projectId);
                $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
            }
        }
    }


    ///-----------------------------------------------------------------------------------------


    public function testOrder(){
        return $orders_list = Array
            (
                'code' => 200,
                'result' => Array
                    (
                        'Order' => Array
                        (
                            Array
                                (
                                    'OrderId' => 4849118860,
                                    'DateTimeCustomer' => '2018-07-29T20:12:23.000+02:00',
                                    'DateTimeDropShipper' => '2018-07-29T20:12:23.000+02:00',
                                    'CustomerDetails' => Array
                                        (
                                            'ShipmentDetails' => Array
                                                (
                                                    'SalutationCode' => 01,
                                                    'Firstname' => 'Joey',
                                                    'Surname' => 'Thomas',
                                                    'Streetname' => 'de Villegas de Clercampstraat',
                                                    'Housenumber' => 80,
                                                    'ZipCode' => 1853,
                                                    'City' => 'Grimbergen',
                                                    'CountryCode' => 'BE',
                                                    'Email' => '2poetjk646nlmprift3a3stvm74evue@verkopen.bol.com'
                                                ),
                                            'BillingDetails' => Array
                                                (
                                                    'SalutationCode' => 01,
                                                    'Firstname' => 'Joey',
                                                    'Surname' => 'Thomas',
                                                    'Streetname' => 'de Villegas de Clercampstraat',
                                                    'Housenumber' => 80,
                                                    'ZipCode' => 1853,
                                                    'City' => 'Grimbergen',
                                                    'CountryCode' => 'BE',
                                                    'Email' => '2poetjk646nlmprift3a3stvm74evue@verkopen.bol.com'
                                                )

                                        ),

                                    'OrderItems' => Array
                                        (
                                            'OrderItem' => Array
                                                (
                                                    'OrderItemId' => 2126188086,
                                                    'EAN' => 8437006044851,
                                                    'OfferReference' => 'E15s',
                                                    'Title' => 'Aqua Dragons - Sea Monkeys Aquarium',
                                                    'Quantity' => 1,
                                                    'OfferPrice' => 21.99,
                                                    'TransactionFee' => '4.3',
                                                    'PromisedDeliveryDate' => '2018-07-31+02:00',
                                                    'OfferCondition' => 'NEW',
                                                    'CancelRequest' => false
                                                )

                                        )

                                ),
                            Array
                                (
                                    'OrderId' => 4849080190,
                                    'DateTimeCustomer' => '2018-07-29T19:35:33.000+02:00',
                                    'DateTimeDropShipper' => '2018-07-29T19:35:33.000+02:00',
                                    'CustomerDetails' => Array
                                        (
                                            'ShipmentDetails' => Array
                                                (
                                                    'SalutationCode' => 02,
                                                    'Firstname' => 'Dinie',
                                                    'Surname' => 'Bosson-Schippers',
                                                    'Streetname' => 'Papaverstraat',
                                                    'Housenumber' => 4,
                                                    'ZipCode' => '5241 XP',
                                                    'City' => 'ROSMALEN',
                                                    'CountryCode' => 'NL',
                                                    'Email' => '22g2jjsklfneclrenifn2kgcn5gzdws@verkopen.bol.com'
                                                ),

                                            'BillingDetails' => Array
                                                (
                                                    'SalutationCode' => 02,
                                                    'Firstname' => 'Dinie',
                                                    'Surname' => 'Bosson-Schippers',
                                                    'Streetname' => 'Papaverstraat',
                                                    'Housenumber' => 4,
                                                    'ZipCode' => '5241 XP',
                                                    'City' => 'ROSMALEN',
                                                    'CountryCode' => 'NL',
                                                    'Email' => '22g2jjsklfneclreninf2kgcn5gzdws@verkopen.bol.com',
                                                )

                                        ),

                                    'OrderItems' => Array
                                        (
                                            'OrderItem' => Array
                                                (
                                                    'OrderItemId' => 2126179546,
                                                    'EAN' => 8437006044851,
                                                    'OfferReference' => 'E15s',
                                                    'Title' => 'Aqua Dragons - Sea Monkeys Aquarium',
                                                    'Quantity' => 1,
                                                    'OfferPrice' => 21.99,
                                                    'TransactionFee' => 4.3,
                                                    'PromisedDeliveryDate' => '2018-07-31+02:00',
                                                    'OfferCondition' => 'NEW',
                                                    'CancelRequest' => false
                                                )

                                        )

                                )
                        )
                    )
            );


        // $orders_list = Array
        //     (
        //         'OrderId' => '4709257481',
        //         'DateTimeCustomer' => '2018-07-04T11:19:05.000+02:00',
        //         'DateTimeDropShipper' => '2018-07-04T11:19:05.000+02:00',
        //         'CustomerDetails' => Array
        //             (
        //                 'ShipmentDetails' => Array
        //                     (
        //                         'SalutationCode' => '01',
        //                         'Firstname' => 'Folkert',
        //                         'Surname' => 'Rinkema',
        //                         'Streetname' => 'Heuveloord',
        //                         'Housenumber' => '25',
        //                         'HousenumberExtended' => '-G',
        //                         'ZipCode' => '3523 CK',
        //                         'City' => 'UTRECHT',
        //                         'CountryCode' => 'NL',
        //                         'Email' => 'manish.unified@gmail.com',
        //                         'Company' => 'Frivista'
        //                     ),

        //                 'BillingDetails' => Array
        //                     (
        //                         'SalutationCode' => '01',
        //                         'Firstname' => 'Folkert',
        //                         'Surname' => 'Rinkema',
        //                         'Streetname' => 'Bolksbeekstraat',
        //                         'Housenumber' => '24',
        //                         'ZipCode' => '3521 CS',
        //                         'City' => 'UTRECHT',
        //                         'CountryCode' => 'NL',
        //                         'Email' => 'manish.unified@gmail.com',
        //                         'Company' => 'folkert rinkema photography'
        //                     )

        //             ),

        //         'OrderItems' => Array
        //             (
        //                 'OrderItem' => Array
        //                     (
        //                         'OrderItemId' => '2121110583',
        //                         'EAN' => '9789026327346',
        //                         'OfferReference' => '40580152',
        //                         'Title' => 'Vergrootglas Groot',
        //                         'Quantity' => '1',
        //                         'OfferPrice' => '12.99',
        //                         'TransactionFee' => '2.95',
        //                         'PromisedDeliveryDate' => '2018-07-05+02:00',
        //                         'OfferCondition' => 'NEW',
        //                         'CancelRequest' => 'false',
        //                     )

        //             )

        //     );
        // return ['code'=>200, 'result'=>$orders_list];
    }

    public function updaeBolOrderCondition(){
    	$this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->where('id',26)->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('orders_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                $orders_id          = isset($_GET['orders_id'])?$_GET['orders_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                //if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('orders_last_execution', time(), $projectId);
                    if($p_value['connection_type']==3){
                        if($this->Projects_model->getValue('cms', $projectId)=='magento2'){
                            $this->updateOrdersInBol($projectId, $orders_id);
                        }
                    }
                //}
            }
        }
    }

    public function updateOrdersInBol($projectId, $orders_id = ''){
    	$this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        $lastUpdateDate = '2018-08-24 01:00:00';
        $currentdatetime = '2018-09-24 01:00:00';
        $updated_orders_list = $this->Magentobol_model->getUpdatedOrders($projectId,$lastUpdateDate, $currentdatetime);
        print_r($updated_orders_list);
        // if ($orders_list['code']==200) {
        //     $result = $orders_list['result'];
        //     $orders = isset($result['Order'])?$result['Order']:array();
        //     if(!empty($orders)){
        //         foreach ($orders as $key => $value) {
        //             $this->Magentobol_model->importOrdersInMagento($projectId, $value);
        //         }
        //     }
        // }
    }

    // these functions are used for test the connection I forgot to remove these please remove  Thanks.
    public function getProducts(){
        $this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        // $orders = Array
        //         (
        //             'OrderId' => '4710777480',
        //             'DateTimeCustomer' => '2018-07-05T11:08:43.000+02:00',
        //             'DateTimeDropShipper' => '2018-07-05T11:08:43.000+02:00',
        //             'CustomerDetails' => Array
        //                 (
        //                     'ShipmentDetails' => Array
        //                         (
        //                             'SalutationCode' => '02',
        //                             'Firstname' => 'Lieke',
        //                             'Surname' => 'Gradussen',
        //                             'Streetname' => 'Bloemstraat',
        //                             'Housenumber' => '47',
        //                             'HousenumberExtended' => '-37',
        //                             'ZipCode' => '9712 LC',
        //                             'City' => 'GRONINGEN',
        //                             'CountryCode' => 'NL',
        //                             'Email' => '2mcx7qyukimflonf6fcjop7o5j2ek4@verkopen.bol.com',
        //                             'DeliveryPhoneNumber' => '0623451593'
        //                         ),
        //                     'BillingDetails' => Array
        //                         (
        //                             'SalutationCode' => '02',
        //                             'Firstname' => 'Lieke',
        //                             'Surname' => 'Gradussen',
        //                             'Streetname' => 'Garst',
        //                             'Housenumber' => '18',
        //                             'ZipCode' => '9673 AE',
        //                             'City' => 'WINSCHOTEN',
        //                             'CountryCode' => 'NL',
        //                             'Email' => '2mcx7qyukimflonf6fcjop7o5j2ek4@verkopen.bol.com',
        //                             'DeliveryPhoneNumber' => '0623451593'
        //                         )
        //                 ),
        //             'OrderItems' => Array
        //                 (
        //                     'OrderItem' => Array
        //                         (
        //                             'OrderItemId' => '2121339001',
        //                             'EAN' => '8718807311350',
        //                             'OfferReference' => '30004488',
        //                             'Title' => 'Mieren Antquarium Super Aquarium - 13,5x3,5x17x5cm',
        //                             'Quantity' => '1',
        //                             'OfferPrice' => '32.99',
        //                             'TransactionFee' => '5.95',
        //                             'PromisedDeliveryDate' => '2018-07-06+02:00',
        //                             'OfferCondition' => 'NEW',
        //                             'CancelRequest' => 'false'
        //                         )
        //                 )
        //         );
        // $order = $this->Magentobol_model->importOrdersInMagento(21, $orders);
        // print_r($order);
        // if (isset($order['status']) && $order['status']=1) {
        //     //$totalOrderImportSuccess++;
        //     project_error_log(21, 'exportorders', $order['message']);
        // } else {
        //     //$totalOrderImportError++;
        //     project_error_log(21, 'exportorders', $order['message']);
        // }
       // $products_details = $this->Magentobol_model->getProductsList(21, '', 1, 150);
        $products_details = $this->Magentobol_model->getProductDetails(26, 'BOLVZND');
        print_r($products_details);
        exit();
        if (isset($_GET['product_id'])) {
            $products_list = $this->Magentobol_model->getProducts($_GET['product_id']);
            if($products_list['total_count']>0){
                foreach ($products_list['items'] as $p_key => $p_value) {
                    $products_details = $this->Magentobol_model->getProductDetails($p_value['sku']);
                    if($products_details)
                        $this->Boldotcom_model->upsertOffer($products_details);
                }
            }
        } else {
            echo "Please provide project id";
        }
    } 

    public function getProductsAttribute(){
        $this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        $products_list = $this->Magentobol_model->getAttributes(21);
    }

    public function getAllOffers($projectId=''){
        $projectId = 26;
        $this->load->model('Boldotcom_model');
        $products_list = $this->Boldotcom_model->getAllOffers($projectId);
        // print_r($products_list);
        // exit();
        if ($products_list['code']==200) {
            $result = $products_list['result'];
            $url = isset($result['Url'])?$result['Url']:'';
            $explode_url = explode('/', $url);
            $file_name  = '';
            if(!empty($explode_url))
                $file_name = array_reverse($explode_url)[0];
            if($file_name!=''){
                $downloadcsv  = $this->Boldotcom_model->getAllOffersDown($projectId, $file_name);
                if ($downloadcsv['code']==200) {
                    if(!empty($downloadcsv['result'])){
                        $this->updateMagentoStock($projectId, $downloadcsv['result']);
                    }
                }
            }
        }
    }

    public function updateMagentoStock($projectId, $downloadedcsv){
        $this->load->model('Magentobol_model');
        $skuList = explode(PHP_EOL, $downloadedcsv);
        foreach ($skuList as $key => $value) {
            if(!empty($value) && $key>0){
                $skuRow = explode(',', $value);
                $products_list = $this->Magentobol_model->updateProductsStock($projectId, $skuRow);
            }
        }
    }

    public function importArticleToBoldotcomAfas(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('articles_enabled', $projectId);
                $product_id             = isset($_GET['product_id'])?$_GET['product_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('articles_last_execution', time(), $projectId);
                    if($p_value['connection_type']==2){
                        if($p_value['erp_system'] == 'afas'){
                            // $this->importAfasProducts($projectId, $product_id);
                        }
                    }
                }
            }
        }
    }


    //------------------------------  CREARTED BY JAYANTA MONDAL --------------------------------------------//

    public function importOrdersInMagentotest(){
        $this->load->model('Boldotcom_model');
        $this->load->model('Magentobol_model');
        $this->load->model('Projects_model');
        $projectId = 26;
        $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);
        if($bol_live_mode==0)
            $bol_live_mode = true;
        else
            $bol_live_mode = false;

        $orders_list = $this->Boldotcom_model->getSingleOrder($projectId, $bol_live_mode,'4990458220');

        print_r($orders_list);
        exit();

        //$orders_list = $this->Boldotcom_model->getAllOffers($projectId);

        // $orders_list = $this->testOrder();
        // if ($orders_list['code']==200) {
        //     $result = $orders_list['result'];
        //     $orders = isset($result['Order'])?$result['Order']:array();
        //     if(!empty($orders)){
        //         if(isset($orders[0])){
        //             foreach ($orders as $key => $value) {
        //                 $order = $this->Magentobol_model->importBundelsOrdersInMagento($projectId, $value);
        //             }
        //         } else{
        //             $order = $this->Magentobol_model->importBundelsOrdersInMagento($projectId, $orders);
        //         }
        //     }
        // }else if($orders_list['code']==401){
        //     $totalOrderImportError = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):0;
        //     $error = isset($orders_list['result']['serviceError'])?' Code: '.$orders_list['result']['serviceError']['ErrorCode'].' - '.$orders_list['result']['serviceError']['ErrorMessage']:' Access denied ';
        //     $message = "Error :: ".$error;
        //     $totalOrderImportError++;
        //     project_error_log($projectId, 'exportorders',$message);
        //     $this->Projects_model->saveValue('total_orders_import_error', $totalOrderImportError, $projectId);
        // }
    }

    ####################################################################################################
    #        function is used to import orders in webshops  from bol.com.                              #
    ####################################################################################################
    public function importOrdersToWebshopsTest(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        // get all projects having marketplace  bol.com exact  with webshop .
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->where('id',32)->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                // if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                //     continue;
                $lastExecution      = $this->Projects_model->getValue('orders_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                $orders_id          = isset($_GET['orders_id'])?$_GET['orders_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                //if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $ui = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('orders_last_execution', $ui, $projectId);
                    if($p_value['connection_type']==2){
                        if ($p_value['erp_system'] == 'exactonline') {
                            $this->importOrdersInExactOnline($projectId, $orders_id);
                        }
                    }
                //}
            }
        }
    }

    ####################################################################################################
    #         function is used to import orders in Magento webshps from bol.com.                       #
    ####################################################################################################
    public function importOrdersInExactOnline($projectId, $orders_id=''){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Boldotcom_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Bol_exact_model');

        $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);

        /*if($bol_live_mode==0)
            $bol_live_mode = true;
        else
            $bol_live_mode = false;
        */
        //$orders_list = $this->Boldotcom_model->getOrders($projectId, $bol_live_mode);
        $orders_list = $this->testOrder();
        
        //--------------- make exact connection ----------------------------------//
        $this->Exactonline_model->setData(
            array(
                'projectId'     => $projectId,
                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );

        $connection = $this->Exactonline_model->makeConnection($projectId);
        
        if ($orders_list['code']==200) {
            $result = $orders_list['result'];
            $orders = isset($result['Order'])?$result['Order']:array();
            if(!empty($orders)){
                if(isset($orders[0])){
                    foreach ($orders as $key => $orderData) {
                        $order = $this->Bol_exact_model->sendOrder($connection, $projectId, $orderData);
                    }
                } else{
                    $order = $this->Bol_exact_model->sendOrder($connection, $projectId, $orders);
                }
            }
        }
    }

    #########################################################################################################
    #             function is used to import orders in webshops  from bol.com.                              #
    #########################################################################################################
    public function importInvoiceToWebshopsTest(){
        $this->load->model('Projects_model');
        $this->load->model('Boldotcom_model');
        // get all projects having marketplace  bol.com exact  with webshop .
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->where('id',32)->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                // if($this->Projects_model->getValue('market_place', $projectId)!='bol')
                //     continue;
                $lastExecution      = $this->Projects_model->getValue('orders_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('orders_interval', $projectId);
                $enabled            = $this->Projects_model->getValue('orders_enabled', $projectId);
                $orders_id          = isset($_GET['orders_id'])?$_GET['orders_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                //if($enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $ui = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('orders_last_execution', $ui, $projectId);
                    if($p_value['connection_type']==2){
                        if ($p_value['erp_system'] == 'exactonline') {
                            $this->importInvoiceInExactOnline($projectId, $orders_id);
                        }
                    }
                //}
            }
        }
    }


    #########################################################################################################
    #             function is used to import Invoice in ExactOnline from bol.com.                        #
    #########################################################################################################
    public function importInvoiceInExactOnline($projectId, $orders_id=''){
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Boldotcom_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Bol_exact_model');

        $bol_live_mode = $this->Projects_model->getValue('bol_live_mode', $projectId);

        if($bol_live_mode==0)
            $bol_live_mode = true;
        else
            $bol_live_mode = false;
        
        //$orders_list = $this->Boldotcom_model->getOrders($projectId, $bol_live_mode);
        //$orders_list = $this->testInvoice();
        $orders_list = $this->testOrder();



       /* echo "<pre>";
        print_r($orders_list);
        die();  */
        //--------------- make exact connection ----------------------------------//
        $this->Exactonline_model->setData(
            array(
                'projectId'     => $projectId,
                'redirectUrl'   => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                'clientId'      => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret'  => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );

        $connection = $this->Exactonline_model->makeConnection($projectId);
        
        if ($orders_list['code']==200) {
            $result = $orders_list['result'];
            $orders = isset($result['Order'])?$result['Order']:array();
            if(!empty($orders)){
                if(isset($orders[0])){
                    foreach ($orders as $key => $orderData) {
                        $order = $this->Bol_exact_model->sendInvoice($connection, $projectId, $orderData);
                    }
                } else{
                    $order = $this->Bol_exact_model->sendInvoice($connection, $projectId, $orders);
                }
            }
        }
    }

}

/* End of file Boldotcom.php */
/* Location: ./application/controllers/Boldotcom.php */