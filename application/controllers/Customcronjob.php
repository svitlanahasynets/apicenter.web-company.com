<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Customcronjob extends MY_Controller {
	
	
	public function index(){
		$this->load->helper('exactonline/vendor/autoload');
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
	
}

/* End of file Customcronjob.php */
/* Location: ./application/controllers/Customcronjob.php */