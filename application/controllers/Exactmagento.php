<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exactmagento extends CI_Controller {

    /**
    * @author Manish
    * @return NULL
    */
    public function __construct(){
        parent::__construct();
        $this->load->helper('tools');
        $this->load->helper('constants');
    }

    #########################################################################################################
    #        function get called by cron job schedule from directadmin to execute Magento     functionality #
    #########################################################################################################
    public function magentoCronJob(){
        $this->load->model('Projects_model');
        $projects = $this->db->select('*')->from('projects')->where_in('connection_type',[1,3])->get()->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='magento2')
                    continue;
                $enabled            = $this->Projects_model->getValue('enabled', $projectId);
                // check if the last execution time is satisfy the time checking.
                if($enabled == '1'){
                    if($p_value['erp_system'] == 'exactonline') {
                        $this->importArticleFromExact($projectId);
                        $this->importCustomerFromExact($projectId);
                    } 
                }
            }
        } 
        $this->updateArticleInWoocommerce();
    }

    #########################################################################################################
    #      function is used to import or update article and article group in Magento from ExactOnline.      #
    #########################################################################################################
    public function importArticleFromExact($projectId=''){
        return true;
        if($projectId!=''){
            $this->load->helper('ExactOnline/vendor/autoload');
            $this->load->model('Projects_model');
            $this->load->model('Exactonline_model');
            $this->load->model('Woocommerce_exactonline_model');
            // $this->load->model('magento_exact_model');
            // get all projects having erp system exact  with webshop magento.
            $projects = $this->db->get_where('projects', array('id' => $projectId))->result_array();
            if(!empty($projects)){
                foreach ($projects as $p_key => $p_value) {
                    $projectId          = $p_value['id'];
                    if($this->Projects_model->getValue('cms', $projectId)!='magento2' || $p_value['erp_system']!='exactonline')
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
                        $up_time = $lastExecution + ($customersInterval * 60);
                        $this->Projects_model->saveValue('articles_last_execution', $up_time, $projectId);
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
                        print_r($connection);
                        exit();
                        $is_published = $this->Projects_model->getValue('import_as_published', $projectId) ? $this->Projects_model->getValue('import_as_published', $projectId) : 1;
                        $impost_exact_image = $this->Projects_model->getValue('import_image_from_exact', $projectId) ? $this->Projects_model->getValue('import_image_from_exact', $projectId) : 0;
                        $import_exact_description = $this->Projects_model->getValue('import_exact_description', $projectId) ? $this->Projects_model->getValue('import_exact_description', $projectId) : 0;
                        $import_exact_extra_description = $this->Projects_model->getValue('import_exact_extra_description', $projectId) ? $this->Projects_model->getValue('import_exact_extra_description', $projectId) : 0;
                        $woocommerce_stock_options = $this->Projects_model->getValue('woocommerce_stock_options', $projectId) ? $this->Projects_model->getValue('woocommerce_stock_options', $projectId) : 0;
                        $import_option_array = ['import_exact_description'=>$import_exact_description, 'import_exact_extra_description'=>$import_exact_extra_description, 'woocommerce_stock_options'=>$woocommerce_stock_options];
                        // ------- get article from exactonline based on amount and offset ----------------       //
                        $items = $this->Woocommerce_exactonline_model->getExactArticle($connection, $itemId, $offset, 1, $is_published,$itemCode, $impost_exact_image, $import_option_array);
                    }
                }
            }
        }
    }


 

}

/* End of file exactmagento.php */
/* Location: ./application/controllers/exactmagento.php */
