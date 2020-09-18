<?php
class Project137_model extends CI_Model
{
    public $projectId;
    
    function __construct()
	{
		parent::__construct();
		$this->projectId = 137;
	}
	
	public function orderBeforeSend($orderData){
	    
	    $orderData['email'] = 'admin@swordfishandfriend.com';
	    
	    log_message('debug', 'Orderdata 137 '. var_export($orderData, true));
	    
	    return $orderData;
	}
	
	public function createVismaCustomerBeforeSave($customerData, $saveData){
		if(strtoupper($customerData['country']) == 'NL'){
			$saveData['customerClassId'] = 'NLSTD';
		} elseif(in_array(strtoupper($customerData['country']), array('AT', 'BE', 'BG', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'HR', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'))){
			// https://gist.github.com/henrik/1688572
			$saveData['customerClassId'] = 'EUSTD';
		} else {
			$saveData['customerClassId'] = 'NONEUSTD';
		}
		return $saveData;
	}
}
