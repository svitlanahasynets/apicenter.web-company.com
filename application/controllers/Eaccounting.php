<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Eaccounting extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->model('Eaccounting_model');
	}
	
	public function index(){

		$this->load->model('Projects_model');
		$projectId = $this->uri->segment(4, 0);
		$authorizeCode = $this->Projects_model->getValue('eaccounting_authorize_code', $projectId);
		
		if($this->input->get('reauthorize') == '1'){
			$authorizeCode = '';
			$this->Projects_model->saveValue('eaccounting_authorize_code', '', $projectId);
			$this->Projects_model->saveValue('eaccounting_token', '', $projectId);
		}
		log_message('debug', 'projectId = ' . $projectId);
		log_message('debug', 'auth_coe' . $this->input->get('code'));

		if($this->input->get('code') != ''){

			$authorizeCode = $this->input->get('code');
			$projectId = $this->session->userdata('project_id');
			$this->Projects_model->saveValue('eaccounting_authorize_code', $this->input->get('code'), $projectId);
			$this->session->unset_userdata('project_id');
			$token = $this->Eaccounting_model->getToken($projectId);
			log_message('debug', 'projectId = ' . $projectId);
			log_message('debug', 'token = ' . var_export($token, true));

			redirect('/', 'refresh');
		}

		if($authorizeCode == ''){
			$url = 'https://identity-sandbox.test.vismaonline.com/connect/authorize';
			$this->load->helper('NuSOAP/nusoap');
			
			$clientId = $this->Projects_model->getValue('eaccounting_client_id', $projectId);
			$this->session->set_userdata('project_id', $projectId);
			$parameters = array();

			$parameters['client_id'] = $clientId;
			$parameters['redirect_uri'] = 'https://dev.apicenterv3.com/authorize/eaccounting';
			$parameters['scope'] = 'ea:api offline_access ea:sales';
			$parameters['response_type'] = 'code';
			$parameters['prompt'] = 'login';

			$j = json_decode(json_encode($parameters));
			$get_params = http_build_query($j);
			$authUrl = $url."?".$get_params;

			header('Location: ' . $authUrl);
			exit;
		}
		return;
	}

}

/* End of file eaccounting.php */
/* Location: ./application/controllers/eaccounting.php */