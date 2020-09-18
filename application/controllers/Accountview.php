<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Accountview extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
		$this->load->library('session');
		$this->load->model('Accountview_model');
	}
	
	public function index(){
		$this->load->model('Projects_model');
		$projectId = $this->uri->segment(4, 0);
		$authorizeCode = $this->Projects_model->getValue('accountview_authorize_code', $projectId);
		
		if($this->input->get('reauthorize') == '1'){
			$authorizeCode = '';
			$this->Projects_model->saveValue('accountview_authorize_code', '', $projectId);
			$this->Projects_model->saveValue('accountview_token', '', $projectId);
		}
		log_message('debug', 'projectId = ' . $projectId);
		log_message('debug', 'auth_coe' . $this->input->get('code'));
		if($this->input->get('code') != '' && $this->input->get('state') == 'preventcsr'){
			$this->Projects_model->saveValue('accountview_authorize_code', $this->input->get('code'), $projectId);
			$token = $this->Accountview_model->getToken($projectId);
			log_message('debug', 'projectId = ' . $projectId);
			log_message('debug', 'token = ' . var_export($token, true));
			return;
		}

		if($authorizeCode == ''){
			$url = 'https://www.accountview.net/ams/authorize.aspx';
			$this->load->helper('NuSOAP/nusoap');
			
			$clientId = $this->Projects_model->getValue('accountview_client', $projectId);
			$this->session->set_userdata('project_id', $projectId);
			$parameters = array();
			$parameters['response_type'] = 'code';
			$parameters['client_id'] = $clientId;
			$parameters['redirect_uri'] = 'https://apicenterdev.web-company.nl/index.php/accountview/index/project/'.$projectId;
			$parameters['scope'] = 'readaccountviewdata updateaccountviewdata deleteaccountviewdata';
			$parameters['state'] = 'preventcsr';
			
			$j = json_decode(json_encode($parameters));
			$get_params = http_build_query($j);
			$authUrl = $url."?".$get_params;
			header('Location: ' . $authUrl);
			exit;
		}
		return;
	}

}

/* End of file accountview.php */
/* Location: ./application/controllers/accountview.php */