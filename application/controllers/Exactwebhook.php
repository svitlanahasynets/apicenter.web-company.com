<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Exactwebhook extends MY_Controller {
	
	public function __construct(){
		parent::__construct();
	}
		
	public function itemsCallbackFromExact(){
        $this->load->model('Projects_model');

        $originalContent = file_get_contents('php://input');
		$content = json_decode($originalContent, true);
		$file = fopen('testwebhooksubrs.txt','a+');
        fwrite($file, print_r($content, true));
        fclose($file);

        $file = fopen('testwebhooksubrs.txt','a+');
        fwrite($file, print_r($_GET, true));
        fclose($file);

        $file = fopen('testwebhooksubrs.txt','a+');
        fwrite($file, 'print_r($_GET, true)we');
        fclose($file);
   
        
		// if(file_get_contents('php://input') != '' && isset($_GET['project_id']) && $_GET['project_id']> 0){
		// 	$projectId 	= intval($_GET['project_id']);
  //       	$projects 	= $this->db->get_where('projects', array('id' => $projectId))->row();
  //       	$originalContent = file_get_contents('php://input');
		// 	$content = json_decode($originalContent, true);
	 //        $file = fopen('testwebhooksubs.txt','a+');
	 //        fwrite($file, print_r($content, true));
	 //        fclose($file);
		// 	// if(isset($content['Content']) && $content['Content']['Topic'] == 'Items'){
		// 	// 	$authentication = new \Picqer\Financials\Exact\Webhook\Authenticatable();
		// 	// 	$authenticationResult = $authentication->authenticate($originalContent, $this->Projects_model->getValue('exactonline_webhook_secret_key', $projectId));
		// 	// 	if(!$authenticationResult){
		// 	// 		api2cart_log($projectId, 'importarticles', 'Webhook callback failed due to authentication issue');
		// 	// 	} else {
		// 	// 		$itemId = $content['Content']['Key'];
		// 	// 		$items = $this->Exactonline_model->getArticles($connection, $itemId);
		// 	// 		$this->Api2cart_model->updateArticles($projectId, $items);
		// 	// 	}
		// 	// }

		// }else{
		// 	$projectId  = 9;
		// 	$projects 	= $this->db->get_where('projects', array('id' => $projectId))->row();
		// 	if($projects){
		// 		if($projects->connection_type==1){
		// 			$cms = $this->Projects_model->getValue('cms', $projectId);
		// 			if($cms=='WooCommerce'){
		// 				echo "string";
		// 			}
		// 		}
		// 	}
		// }
	}

}

/* End of file exactonline.php */
/* Location: ./application/controllers/exactonline.php */