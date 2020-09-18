<?php if(!defined('BASEPATH')) exit('No direct script access allowed');

/**
* @author
* @return null
*/

class Mplus extends CI_Controller {

    private $params;

    public function __construct(){
        parent::__construct();
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Projects_model');
        $this->load->model('Cms_model');
        $this->load->model('Mplus_opencart_model');
        $this->load->model('Mplus_model');
    }

    public function index() {
        $projects = $this->db->get('projects')->result_array();

        foreach($projects as $project){
            // Check if enabled

            // if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
            //     continue;
            // }
            
            if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
                continue;
            }

            if($project['id'] != 94){
                continue;
            }

            $orderExecution = $this->Projects_model->getValue('orders_last_execution', $project['id']);
            $orderInterval  = $this->Projects_model->getValue('orders_interval', $project['id']);
            $orderEnabled   = $this->Projects_model->getValue('orders_enabled', $project['id']);
            
            // if ( $orderEnabled == '1' && ( $orderExecution == '' || $orderExecution <= ( time() - $orderInterval * 60) ) ) {
            if (false) {
                $this->Mplus_opencart_model->insertOpencartToMPuls($project['id']);
            }
            
            $stockEnabled       = $this->Projects_model->getValue('stock_enabled', $project['id']);
            $stockInterval      = $this->Projects_model->getValue('stock_interval', $project['id']);
            $stockLastExecution = $this->Projects_model->getValue('stock_last_execution', $project['id']);

            // if ( $stockEnabled == '1' && ( $stockLastExecution == '' || $stockLastExecution <= ( time() - $stockInterval * 60) ) ) { 
            if (true) {
                $this->Mplus_opencart_model->stockSync($project['id']);
            }
        }
    }
}