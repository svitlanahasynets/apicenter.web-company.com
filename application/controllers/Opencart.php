<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
* @author
* @return null
*/

class Opencart extends CI_Controller {

    private $params;

    public function __construct(){
        parent::__construct();
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Projects_model');
        $this->load->model('Cms_model');
        $this->load->model('Opencart_model');
        $this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Cms_model');
		$this->load->model('Optiply_model');
		$this->load->model('Mplus_opencart_model');
		$this->load->model('Mailchimp_model');
		$this->load->model('Marketplace_model');
    }

    public function index() {
        $projects = $this->db->get('projects')->result_array();

        foreach($projects as $project){
            // Check if enabled
            if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
                continue;
            }
            
            if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
                continue;
            }

            if($project['id'] != 121){
                continue;
            }

            $orderExecution = $this->Projects_model->getValue('orders_last_execution', $project['id']);
            $orderInterval  = $this->Projects_model->getValue('orders_interval', $project['id']);
            $orderEnabled   = $this->Projects_model->getValue('orders_enabled', $project['id']);
            $wms = $project['wms'];
            //Checking if script is running now
            if($wms == 'optiply') {
                $isRunning = $this->Projects_model->getValue('is_running', $project['id']);
                $lastTime = $this->Projects_model->getValue('start_running', $project['id']);
                if(($isRunning == '1' || $isRunning == '2') && time() - $lastTime < 1200) {return;}
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

            $connection = $this->Exactonline_model->makeConnection($project['id']);

            if(!$connection){ 
                $this->Projects_model->scriptFinish($project['id'], $wms, $isRunning);
                continue; 
            }

            $orders = $this->Cms_model->getOrders($project['id'], 0, 10);
            foreach($orders as $order){
                $result = $this->Exactonline_model->sendOrder($connection, $project['id'], $order);
            }
            // echo "<pre>";
            // var_export($orders);
        }
    }
}