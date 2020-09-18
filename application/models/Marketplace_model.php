<?php
   
class Marketplace_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
    }

    function updateArticles($projectId, $articles){
        
		if($projectId == 27){
			log_message('debug', 'AFASBOL - Marketplace_model: ');
		}
		
		$market_place = $this->Projects_model->getValue('market_place', $projectId);
        $model = false;
        
		if($market_place == 'bol'){
            $this->load->model('Boldotcom_model');
            $model = $this->Boldotcom_model;
        }
        if(!$model){ return; }
        $model->updateArticles($projectId, $articles);
        return;
    }

    function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc'){
        $market_place = $this->Projects_model->getValue('market_place', $projectId);
        $model = false;
        if($market_place == 'bol'){
            $this->load->model('Boldotcom_model');
            $model = $this->Boldotcom_model;
        }
        if(!$model){
            return false;
        }
        $orders = $model->getOrders($projectId, false, true);
        return $orders;
    }

}