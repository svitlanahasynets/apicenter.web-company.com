<?php
class Project141_model extends CI_Model {

	public $projectId;

    public function __construct()
    {
        parent::__construct();
        $this->projectId = 141;
    }
    
    public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
        
        $fields->UAB99BC9342DED0B2D786FBBB9791D850 = $customerData['id'];
	    
	    //log_message('debug', 'Orders 131-Guest: ' . $this->boolGuest . ' WS: ' . $fields->UECD0D6D645F76E82AC9C3AA945C5049A);
	}
}