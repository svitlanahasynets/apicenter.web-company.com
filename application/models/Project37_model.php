<?php
class Project37_model extends CI_Model {

	public $projectId;

    function __construct(){
        parent::__construct();
        $this->projectId = 37;
    }

    public function checkVatCode($country_code){
    	$europian_c_code =  ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];
    	if($country_code=='NL')
    		return 'B';
    	else if (in_array($country_code, $europian_c_code))
    		return 'C';
    	else
    		return 'D';
    }

    public function checkExactCustomerExists($connection, $projectId){

	    $exact_customer_id = $this->Projects_model->getValue('exact_customer_id', $projectId)?$this->Projects_model->getValue('exact_customer_id', $projectId):'';
	    if($exact_customer_id!=''){
	    	$exact_customer_id = str_pad($exact_customer_id, 18,' ', STR_PAD_LEFT);
	    	$customer = new \Picqer\Financials\Exact\Account($connection);
			$result = $customer->filter("Code eq '".$exact_customer_id."'");
			if(empty($result)){
				return false;
			}
			return $result[0]->ID;
		}
		else
			return false;
    }

}
