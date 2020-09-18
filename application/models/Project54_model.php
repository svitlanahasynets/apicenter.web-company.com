<?php
class Project54_model extends CI_Model 
{

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 54;
    }
	
	public function orderBeforeSend($orderData){
		$orderData['order_number'] = $orderData['order_number'];
		return $orderData;
	}
}