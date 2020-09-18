<?php
class Project84_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 84;
    }
	
	public function getArticleData($articleData, $finalArticleData){

		$finalData = $finalArticleData;
		
		$finalData['categories_ids'] = 84;
		
		//special price
		$spec_price = $this->getItemPrice($articleData['ItemCode']);
		
		if($spec_price != ''){
		    $finalData['custom_attributes']['special_price'] = array(
				'type' => 'text',
				'value' => $spec_price
			);
		}
		
		//New price
		/*if(isset($articleData['NewFrom']) && $articleData['NewFrom'] != '')
		{
			$date = explode('T', $articleData['NewFrom']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_from_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}*/
		
		//New price
		/*if(isset($articleData['NewTo']) && $articleData['NewTo'] != '')
		{
			$date = explode('T', $articleData['NewTo']);
			$date = $date[0];
			
			$finalData['custom_attributes']['news_to_date'] = array(
				'type' => 'text',
				'value' => $date
			);
		}*/

		return $finalData;
	}
	
	public function loadCategories($finalArticleData, $article, $projectId){
	    $finalArticleData['categories_ids'] = 84;
	    
	    return $finalArticleData;
	}
	
public function getItemPrice($itemCode){
	    $projectId = $this->projectId;
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($itemCode != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		}
	    
	    $xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_SalesPrice_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
	    
	    $result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		
		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_SalesPrice_App) && count($data->Profit_SalesPrice_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Profit_SalesPrice_App);
			if(!empty($itemData)){
				return floatval($itemData['Price']);
			}
		}
		
		return 0;
	}
}