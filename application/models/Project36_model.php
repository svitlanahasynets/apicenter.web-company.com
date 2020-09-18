<?php
class Project36_model extends CI_Model 
{

	public $projectId;
	public $bufferStock;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 36;
    }
	
	public function getArticleData($articleData, $finalArticleData)
	{
		$finalData = $finalArticleData;
		
		if(isset($articleData['Verzendklasse']) && $articleData['Verzendklasse'] != '' && $articleData['Verzendklasse'] != null)
		{
		    $value = '';
		    
		    if ($articleData['Verzendklasse'] == 'L'){
		        $value = 'L';
		    }
		    else if ($articleData['Verzendklasse'] == 'M'){
		        $value = 'M';
		    }
		    else if ($articleData['Verzendklasse'] == 'S'){
		        $value = 'S';
		    }
		    else if ($articleData['Verzendklasse'] == 'XL'){
		        $value = 'XL';
		    }
		    
		    $finalData['custom_attributes']['shipping_class'] = array(
				'type' => 'dropdown',
				'value' => $value
			);
		}
		
		//if(isset($articleData['Verzendklasse']) && $articleData['Verzendklasse'] != '' && $articleData['Verzendklasse'] != null)
		//{
		//	$finalData['custom_attributes']['shipping_class'] = array(
		//		'type' => 'dropdown',
		//		'value' => $articleData['Verzendklasse']
		//	);
		//}
		
		return $finalData;
	}
}