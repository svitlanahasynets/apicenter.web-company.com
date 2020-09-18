<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Moreapp extends CI_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){

		$input = @file_get_contents('php://input');
		$event_json = json_decode($input);

  //       $data       = json_decode(file_get_contents('php://input'),true);
  //       $a = $_REQUEST;
		// $file =  fopen('text.txt', 'a+');
  //       fwrite($file, $data);
  //       fclose($file);
  //       $file =  fopen('textq.txt', 'a+');
  //       fwrite($file, $a);
  //       fclose($file);

         $file =  fopen('textqa.txt', 'a+');
        fwrite($file, print_r($event_json, true));
        fclose($file);
        http_response_code(200);

		// 162d46d3976d856b6ecc4e9e0129a8660606338a
		//52f764923930210eada94b3e67c96191cf621b46
  //   	$this->load->helper('moreapp/Moreapp');
  //   	$params  = ['endpoint'=>'https://api.moreapp.com/api/v1.0/', 'consumer_key'=> 'jelle@web-company.nl', 'consumer_secret'=>'52f764923930210eada94b3e67c96191cf621b46', 'token_secret'=>''];
		// $app = new App($params);
		// echo "<pre>";
		// $app->getCustomers();
		// $app->getDatasource();

		// $ipaddress = $_SERVER['REMOTE_ADDR'];
		// $cmd = "arp -a " . $ipaddress;
		// $status = 0;
		// $return = [];
		// exec($cmd, $return, $status);
		// var_dump($status, $return);
		// die;

		// echo $ipAddress=$_SERVER['REMOTE_ADDR'];
		// $macAddr=false;

		// #run the external command, break output into lines
		// echo $arp=`arp -a $ipAddress`;
		// $lines=explode("\n", $arp);

		// print_r($lines);
		// echo "string";

	}
	// public function index(){
	// 	$this->load->model('Projects_model');
	// 	$projectId = $this->uri->segment(4, 0);
	// 	$authorizeCode = $this->Projects_model->getValue('visma_authorize_code', $projectId);
		
	// 	if($this->input->get('reauthorize') == '1'){
	// 		$authorizeCode = '';
	// 		$this->Projects_model->saveValue('visma_authorize_code', '', $projectId);
	// 		$this->Projects_model->saveValue('visma_token', '', $projectId);
	// 	}
		
	// 	if($this->input->get('code') != '' && $this->input->get('state') == 'preventcsr'){
	// 		$this->Projects_model->saveValue('visma_authorize_code', $this->input->get('code'), $projectId);
	// 		return;
	// 	}

	// 	if($authorizeCode == ''){
	// 		$url = 'https://integration.visma.net/API/resources/oauth/authorize';
	// 		$this->load->helper('NuSOAP/nusoap');
			
	// 		$clientId = $this->Projects_model->getValue('visma_client_id', $projectId);
			
	// 		$parameters = array();
	// 		$parameters['response_type'] = 'code';
	// 		$parameters['client_id'] = $clientId;
	// 		$parameters['redirect_uri'] = 'http://apicenterdev.web-company.nl/index.php/visma/index/project/'.$projectId;
	// 		$parameters['scope'] = 'financialstasks';
	// 		$parameters['state'] = 'preventcsr';
			
	// 		$j = json_decode(json_encode($parameters));
	// 		$get_params = http_build_query($j);
	// 		$authUrl = $url."?".$get_params;
	// 		header('Location: ' . $authUrl);
	// 		exit;
	// 	}
	// 	return;
	// }

}

/* End of file Moreapp.php */
/* Location: ./application/controllers/Moreapp.php */