<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
* @author manish
* @return null
*/

class Amazonmws extends My_Controller {

	public function __construct(){
		parent::__construct();
		$this->load->helpers('tools');
		$this->load->helpers('constants');
	}

    

	// use to make authorised code for exactonline
	public function index(){

		if(isset($_GET['project_id']) && $_GET['project_id']>0){
			$this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $projectId = intval($_GET['project_id']);
            $this->Exactonline_model->setData(
                array(
                    'projectId' => $projectId,
                    'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                    'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                    'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                )
            );
            $this->session->set_userdata('mwsprojectId',$projectId);
            $connection = $this->Exactonline_model->makeConnection($projectId);
            return $connection;
		} else{
			exit('Project you trying to get is no more exists in the system');
		}
	}

	// redirect url from exactonle with authorised code save token expiretime
	public function authorizeExact(){
		$projectId = $this->session->userdata('mwsprojectId');
        if($projectId && $projectId > 0){
            $this->session->unset_userdata('mwsprojectId',false);
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Exactonline_model');
            $this->load->model('Projects_model');
            $this->Exactonline_model->setData(
                array(
                    'projectId' => $projectId,
                    'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
                    'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                    'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
                )
            );
            $connection = $this->Exactonline_model->makeConnection($projectId);
            if($connection){
            	set_success_message('connection authorised successfully.');
              	redirect('/login/');
                echo "connection authorised successfully";
            }
        } else{
        	set_success_message('Project you trying to get is no more exists in the system.');
            redirect('/login/');
		}   
	}

	// export order from amazon mws and import to exactonline
	public function importMwsOrderExact(){
		if(isset($_GET['project_id']) && $_GET['project_id']>0){
			$this->load->helper('ExactOnline/vendor/autoload');
			$this->load->model('Exactonline_model');
			$this->load->model('Projects_model');
			$this->load->model('Exact_amazon_model');
			$projectId = intval($_GET['project_id']);
			$this->Exactonline_model->setData(
				array(
					'projectId' 	=> $projectId,
					'redirectUrl' 	=> $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
					'clientId' 		=> $this->Projects_model->getValue('exactonline_client_id', $projectId),
					'clientSecret' 	=> $this->Projects_model->getValue('exactonline_secret_key', $projectId),
				)
			);
			// make connection to exactonline
			$connection 			= $this->Exactonline_model->makeConnection($projectId);
			// get orders from amazon mws
			$mws_orders 			= $this->Exact_amazon_model->mws_export_orders($projectId);
			if($mws_orders['status']==1){
				// import orders to exactonline
				// $result		= $this->Exact_amazon_model->importAmazonOrderExact($connection, $projectId);
			} else{
				project_error_log($projectId, 'exportorders', 'Could not get orders - status code :'.$mws_orders['status'].' message'.$mws_orders['message']);
			}
		} else{
			exit('Project you trying to get is no more exists in the system');
		}
	}

	// method used to get article from exactonline and export to amazon mws
	public function importExactProductMws(){
		$this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Exact_amazon_model');
        // get all projects having erp system exact  with webshop amazon.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline'))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='Amazon')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                $ar_enabled         = $this->Projects_model->getValue('articles_enabled', $projectId);
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                $itemId             = isset($_GET['itemId'])?$_GET['itemId']:''; // d5df511a-eb03-4b30-a1b6-1f1526a93383
                // check if the last execution time is satisfy the time checking. customers_amount
                if($ar_enabled == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $this->Projects_model->saveValue('articles_last_execution', time(), $projectId);
                    // get the offset and amount to import customers. 
                    $offset                 =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : NULL;
                    $amount                 = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
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
                   
                    // ------- get article from exactonline based on amount and offset ----------------       //
                    $items = $this->Exact_amazon_model->getArticleMws($connection, $itemId, $offset, $amount);
                    // ------ call Woocommerce_model to create and update article in WooCommerce ------       //
					$last_imported_item  	= '';
                    if(!empty($items))
                        $items = $this->Exact_amazon_model->importExactProductAmazon($items, $projectId);
                    else
                        $this->Projects_model->saveValue('article_offset', null, $projectId);
                }
            }
        } else{
			echo 'Project you trying to get is no more exists in the system';
		}
	}

	// method is used to check feed of amazon mws

	public function checkFeedResponse(){

        $this->load->model('Projects_model');
        $this->load->model('Exact_amazon_model');
        // get all projects having erp system exact  with webshop amazon.
        $projects = $this->db->get_where('projects', array('erp_system' => 'exactonline'))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='Amazon')
                    continue;
				$submited_feed_ids 	= $this->Exact_amazon_model->getValue($projectId);
				$this->Exact_amazon_model->checkFeed($submited_feed_ids, $projectId);
            }
        } else{
			exit('Project you trying to get is no more exists in the system');
		}
	}

    
    // AFAS CONNECTION  
    // method used to get article from erp system or webshops and export to amazon mws
    public function importProductToMws(){
        $this->load->model('Projects_model');
        // get all projects having erp system exact  with webshop amazon.
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='Amazon')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('articles_last_execution', $projectId);
                $customersInterval  = $this->Projects_model->getValue('article_interval', $projectId);
                $articles_enabled   = $this->Projects_model->getValue('articles_enabled', $projectId);
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                $product_id         = isset($_GET['product_id'])?$_GET['product_id']:''; 
                // check if the last execution time is satisfy the time checking. customers_amount
                //if($enabled == '1' && $articles_enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    //$this->Projects_model->saveValue('articles_last_execution', time(), $projectId);
                    if($p_value['connection_type']==2){
                        if($p_value['erp_system'] == 'afas'){
                            $this->exportAfasProducts($projectId, $product_id);
                        }
                    }
                //}
            }
        }
    }

    public function exportAfasProducts($projectId, $product_id){
        $this->load->model('Projects_model');
        $this->load->model('Afas_common_model');
        $this->load->model('Afas_amazon_model');
        // get the offset and amount to import customers. 
        $offset =  $this->Projects_model->getValue('article_offset', $projectId) ? $this->Projects_model->getValue('article_offset', $projectId) : NULL;
        $amount = $this->Projects_model->getValue('article_amount', $projectId) ? $this->Projects_model->getValue('article_amount', $projectId) : 10;
        $filter = true;
        $products_list = $this->Afas_common_model->getArticles($projectId, $product_id, $offset, $amount, $filter);
        if($products_list['numberOfResults']>0){
            $items_import = $this->Afas_amazon_model->importAfasProductAmazon($products_list['results'], $projectId);
        } else{
            $offset =  $this->Projects_model->saveValue('article_offset', $offset, $projectId) ;
        }
    }

    public function checkFeedResponseAfas(){

        $this->load->model('Projects_model');
        $this->load->model('Afas_amazon_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[2,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('market_place', $projectId)!='Amazon')
                    continue;
                if($p_value['connection_type']==2){
                    if($p_value['erp_system'] == 'afas'){
                        $submited_feed_ids  = $this->Afas_amazon_model->getValue($projectId);
                        $this->Afas_amazon_model->checkFeed($submited_feed_ids, $projectId);
                    }
                }
            }
        }
    }
    
}