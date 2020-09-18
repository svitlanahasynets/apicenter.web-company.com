<?php
class Project58_model extends CI_Model 
{

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 58;
    }
	
	public function orderBeforeSend($orderData){
		$orderData['order_number'] = "EU".$orderData['order_number'];
		return $orderData;
	}
}