<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Cscart extends MY_Controller {

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
        $this->load->model('Magento1_model');
        $this->load->model('Magento1_cms_model');
        

		//$projects = $this->db->get('projects')->result_array();
		// var_dump( $this->Afas_model->getCustomer(87));exit;
        //Grab all projects listed in APIcenter
        
        // $article = $this->Afas_model->getArticles(184, 0, 10);
        // $this->dd();
        $cate = $this->Magento1_cms_model->findCategory(184, 'Stoelen');

    }

    private function dd($data) {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    }
}