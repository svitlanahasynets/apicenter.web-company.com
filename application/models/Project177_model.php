<?php
class Project177_model extends CI_Model 
{

	public $projectId;
	
    function __construct()
    {
        parent::__construct();
        $this->projectId = 177;
    }
	
	public function getArticleData($articleData, $finalArticleData)
	{
		$finalData = $finalArticleData;
		
		if(isset($articleData['ProductGroup']) && $articleData['ProductGroup'] != '' && $articleData['ProductGroup'] != null)
		{
		    $finalData['custom_attributes']['productgroep'] = array(
				'type' => 'dropdown',
				'value' => $articleData['ProductGroup']
			);
		}
		
		return $finalData;
	}
}