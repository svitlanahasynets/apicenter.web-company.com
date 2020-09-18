<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class VismaTest extends MY_Controller {
	
	public function __construct(){
        parent::__construct();
        $this->load->library('session');
	}
	
	public function index(){
		$this->load->model('Projects_model');
		$projectId = $this->uri->segment(4, 0);
        $authorizeCode = $this->Projects_model->getValue('visma_authorize_code', $projectId);

		var_dump($projectId);exit;
		if($this->input->get('reauthorize') == '1'){
			$authorizeCode = '';
			$this->Projects_model->saveValue('visma_authorize_code', '', $projectId);
			$this->Projects_model->saveValue('visma_token', '', $projectId);
		}
		
		if($this->input->get('code') != '' && $this->input->get('state') == 'preventcsr'){
            $projectId = $this->session->userdata('project_id');
            $this->Projects_model->saveValue('visma_authorize_code', $this->input->get('code'), $projectId);
            $this->session->unset_userdata('project_id');
			return;
		}

		if($authorizeCode == ''){
			$url = 'https://integration.visma.net/API/resources/oauth/authorize';
			$this->load->helper('NuSOAP/nusoap');
            $this->session->set_userdata('project_id', $projectId);
			$clientId = $this->Projects_model->getValue('visma_client_id', $projectId);
            
			$parameters = array();
			$parameters['response_type'] = 'code';
			$parameters['client_id'] = 'web_company_live_98uk70';//$clientId;
			$parameters['redirect_uri'] = 'https://apicenterdev.web-company.nl/index.php/visma/index/project/'. 44;
			$parameters['scope'] = 'financialstasks';
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

/* End of file visma.php */
/* Location: ./application/controllers/visma.php */