<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exactonline extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
	
	public function index(){
		if($_GET['project_id'] > 0){
			$this->load->helper('ExactOnline/vendor/autoload');
			$this->load->model('Exactonline_model');
			$this->load->model('Api2cart_model');
			$this->load->model('Projects_model');
			$projectId = intval($_GET['project_id']);
			$this->Exactonline_model->setData(
				array(
					'projectId' => $projectId,
					'redirectUrl' => ($this->Projects_model->getValue('exactonline_redirect_url', $projectId) ? $this->Projects_model->getValue('exactonline_redirect_url', $projectId) : 'https://apicenterdev.web-company.nl/index.php/exactonline').'/?project_id='.$projectId,
					'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
					'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
				)
			);
			log_message('debug', 'exat controller');
			$connection = $this->Exactonline_model->makeConnection($projectId);
			return $connection;
		}
	}

	public function whmcs_tokens(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Exactonline_model');
		$this->load->model('Projects_model');
		$this->load->library('session');
		
		$clientId = $this->input->get_post('exactonline_client_id');
		$clientSecret = $this->input->get_post('exactonline_secret_key');
		if($clientId != ''){
			// Store in session
			$this->session->set_userdata(array(
				'exactonline_client_id' => $clientId,
				'exactonline_secret_key' => $clientSecret,
			));
		} else {
			$clientId = $this->session->userdata('exactonline_client_id');
			$clientSecret = $this->session->userdata('exactonline_secret_key');
		}
		$this->Exactonline_model->setData(
			array(
				'projectId' => 0,
				'redirectUrl' => 'https://apicenterdev.web-company.nl/index.php/exactonline/whmcs_tokens/',
				'clientId' => $clientId,
				'clientSecret' => $clientSecret,
			)
		);
		$connection = $this->Exactonline_model->makeConnection(0, $this->input->get('code'));
		if($connection->authorizationCode != ''){
			echo json_encode(array(
				'success' => true,
				'authorization_code' => $connection->authorizationCode,
				'access_token' => $connection->accessToken,
				'token_expires' => $connection->tokenExpires,
				'refresh_token' => $connection->refreshToken,
			));
			return;
		}
		echo json_encode(array(
			'success' => false
		));
		return;
	}
	
	public function itemscallback(){
		log_message('error', 'Received callback from items webHook:');
		if(file_get_contents('php://input') != '' && $_GET['project_id'] > 0){
			$this->load->helper('ExactOnline/vendor/autoload');
			$this->load->model('Exactonline_model');
			$this->load->model('Api2cart_model');
			$this->load->model('Projects_model');
			$projectId = intval($_GET['project_id']);
			$this->Exactonline_model->setData(
				array(
					'projectId' => $projectId,
					'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
					'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
					'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
				)
			);
			$connection = $this->Exactonline_model->makeConnection($projectId);
			$originalContent = file_get_contents('php://input');
			$content = json_decode($originalContent, true);
			log_message('error', var_export($content, true));
			
			if(isset($content['Content']) && $content['Content']['Topic'] == 'Items'){
				$authentication = new \Picqer\Financials\Exact\Webhook\Authenticatable();
				$authenticationResult = $authentication->authenticate($originalContent, $this->Projects_model->getValue('exactonline_webhook_secret_key', $projectId));
				if(!$authenticationResult){
					api2cart_log($projectId, 'importarticles', 'Webhook callback failed due to authentication issue');
				} else {
					$itemId = $content['Content']['Key'];
					$items = $this->Exactonline_model->getArticles($connection, $itemId);
					$this->Api2cart_model->updateArticles($projectId, $items);
				}
			}
		}
		echo 'success';
		return;
	}
	
	public function accountscallback(){
		log_message('error', 'Received callback from accounts webHook:');
		if(file_get_contents('php://input') != '' && $_GET['project_id'] > 0){
			$this->load->helper('ExactOnline/vendor/autoload');
			$this->load->model('Exactonline_model');
			$this->load->model('Api2cart_model');
			$this->load->model('Projects_model');
			$projectId = intval($_GET['project_id']);
			$this->Exactonline_model->setData(
				array(
					'projectId' => $projectId,
					'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
					'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
					'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
				)
			);
			$connection = $this->Exactonline_model->makeConnection($projectId);
			$originalContent = file_get_contents('php://input');
			$content = json_decode($originalContent, true);
			log_message('error', var_export($content, true));
			
			if(isset($content['Content']) && $content['Content']['Topic'] == 'Accounts'){
				$authentication = new \Picqer\Financials\Exact\Webhook\Authenticatable();
				$authenticationResult = $authentication->authenticate($originalContent, $this->Projects_model->getValue('exactonline_webhook_secret_key', $projectId));
				if(!$authenticationResult){
					api2cart_log($projectId, 'importcustomers', 'Webhook callback failed due to authentication issue');
				} else {
					$itemId = $content['Content']['Key'];
					$count = $this->Exactonline_model->getDebtors($connection, $projectId, $itemId);
				}
			}
		}
		echo 'success';
		return;
	}
	
/*
	public function index(){
		$this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Exactonline_model');
		$this->load->model('Api2cart_model');
		$this->load->model('Projects_model');
		$projectId = 2;
		$this->Exactonline_model->setData(
			array(
				'projectId' => $projectId,
				'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId),
				'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
				'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
			)
		);
		
		$connection = $this->Exactonline_model->makeConnection();
		
		if($this->Projects_model->getValue('exactonline_delete_webhooks', $projectId) == '1'){
			$this->Projects_model->saveValue('exactonline_delete_webhooks', '0', $projectId);
			$webHooks = new \Picqer\Financials\Exact\WebhookSubscription($connection);
			$webHooks = $webHooks->get();
			foreach($webHooks as $webHook){
				$webHookData = $webHook->attributes();
				if(strpos($webHookData['CallbackURL'], 'api2cart') == false || ($webHookData['Topic'] != 'Accounts' && $webHookData['Topic'] != 'Items')){
					continue;
				}
				$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
					'ID' => $webHook->ID
				));
				$result = $webHook->delete();
				if($webHookData['Topic'] == 'Accounts'){
					api2cart_log($projectId, 'importcustomers', 'Removed webhook '.$webHookData['Topic']);
				} elseif($webHookData['Topic'] == 'Items'){
					api2cart_log($projectId, 'importarticles', 'Removed webhook '.$webHookData['Topic']);
				}
			}
			
			// Subscribe to webhook import articles
			$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
				'CallbackURL' => 'https://laurens-media.nl/api2cart/index.php/exactonline/itemscallback/?project_id='.$projectId,
				'Topic' => 'Items'
			));
			try{
				$result = $webHook->save();
				api2cart_log($projectId, 'importarticles', 'Added webhook "Items"');
				// @todo: Save trigger date in db
			} catch(Picqer\Financials\Exact\ApiException $e){
				if(strpos($e->getMessage(), 'Data already exists') != false){
					// @todo: Save trigger date in db
					api2cart_log($projectId, 'importarticles', 'Webhook "Items" already exists');
				}
			}
	
	
			// Subscribe to webhook import customers
			$webHook = new \Picqer\Financials\Exact\WebhookSubscription($connection, array(
				'CallbackURL' => 'https://laurens-media.nl/api2cart/index.php/exactonline/accountscallback?project_id='.$projectId,
				'Topic' => 'Accounts'
			));
			try{
				$result = $webHook->save();
				api2cart_log($projectId, 'importcustomers', 'Added webhook "Accounts"');
				// @todo: Save trigger date in db
			} catch(Picqer\Financials\Exact\ApiException $e){
				if(strpos($e->getMessage(), 'Data already exists') != false){
					// @todo: Save trigger date in db
					api2cart_log($projectId, 'importcustomers', 'Webhook "Accounts" already exists');
				}
			}
		}
		
		
		// First run import articles
		if($this->Projects_model->getValue('exactonline_import_all_products', $projectId) == '1'){
			$items = $this->Exactonline_model->getArticles($connection);
			$this->Api2cart_model->updateArticles($projectId, $items);
		}
		
		// First run import customers
		if($this->Projects_model->getValue('exactonline_import_all_customers', $projectId) == '1'){
			$customers = $this->Exactonline_model->getDebtors($connection, $projectId);
		}
		
				


		// Export orders
		$currentOrderOffset = 49;
		$orderAmount = 1;
		$orders = $this->Api2cart_model->getOrders($projectId, $currentOrderOffset, $orderAmount);
		$orders = $orders->order;
		$orders = array_reverse($orders);
		foreach($orders as $order){
			$result = $this->Exactonline_model->sendOrder($connection, $projectId, $order);
		}

		return;
	}
*/
}

/* End of file exactonline.php */
/* Location: ./application/controllers/exactonline.php */