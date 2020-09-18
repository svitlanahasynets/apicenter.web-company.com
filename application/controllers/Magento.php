<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Magento extends MY_Controller {

    public function index() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Projects_model');
        $this->load->model('Afas_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Visma_model');
        $this->load->model('Cms_model');
        $this->load->model('Marketplace_model');
        $this->load->model('Optiply_model');
        $this->load->model('Cscart_model');
        $this->load->model('Akeneo_model');
        $this->load->model('Magento2_model');
        $projects = $this->db->get('projects')->result_array();

        foreach($projects as $project){

            
            if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
                continue;
            }
            
            if($project['id'] != 131) {
                continue;
            }
            ini_set('error_reporting', E_ALL);
            ini_set('display_errors', 1);
            ini_set('display_startup_errors', 1);

            // $result = $this->Afas_model->getCredit($project['id'], 0, 10);
            // $this->dd($result);
            // $this->Magento2_model->createCreditMemo($project['id'], $result);

            $result = $this->Afas_model->getArticles($project['id'], 0, 20);

            $this->dd($result);exit;
            // $articles = $result['results'];
            // $this->Cms_model->updateArticles($project['id'], $articles);
            // $result = $this->Cms_model->getOrders($project['id'], 0, 10);
            // $orders = $result['orders'];
            // $orders = array_reverse($orders);
            
            // foreach($orders as $order){
            //     $result = $this->Afas_model->sendDeliveryNote($project['id'], $order);
                
            // }

            // $date = date("Y-m-d").'T00:00:00';
		    // var_dump($date);exit;
            // $priceChangesList = $this->Afas_model->getPriceChangesList($project['id']);
            // $this->Cms_model->updatePrice($project['id'], $priceChangesList['priceData']);

        }
    }

    private function dd($data) {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    }
}