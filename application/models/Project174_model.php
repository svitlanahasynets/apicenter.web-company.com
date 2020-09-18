<?php
class Project174_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 174;
    }
	
	public function getStockArticleData($article, $finalStockData){
		if(isset($article['Naam'])){
			$finalStockData['name'] = $article['Naam'];
		}
		return $finalStockData;
	}

	public function setOrderParams($fields, $orderData){

		$fields->UABF20481436CDFCB8C41679FD9AA06FC = 'B2C';
		$fields->RfCs = $orderData['id'];
	}	
}