<?php
class Project164_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 164;
    }
	
	public function loadCategories($finalArticleData, $article, $projectId){
		if($article['ArtGroup'] != ''){
			$artGroups = $this->getAfasCategory($projectId, $article['ArtGroup']);
			ksort($artGroups);
			
			$finalCategories = array();
			$parentId = '';
			foreach($artGroups as $categoryName){
				$categoryId = $this->Cms_model->findCategory($projectId, $categoryName);
				if(!$categoryId){
					$categoryId = $this->Cms_model->createCategory($projectId, $categoryName, $parentId);
				}
				$finalCategories[] = $categoryId;
				$parentId = $categoryId;
			}
			$finalArticleData['categories_ids'] = implode(',', $finalCategories);
		}
		return $finalArticleData;
	}
	
	public function getAfasCategory($projectId, $artGroupId = '', $artGroupName = '', $categoryArray = array()){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		if($artGroupId != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ArtGroup" OperatorType="1">'.$artGroupId.'</Field></Filter></Filters>';
		}
		if($artGroupName != ''){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Omschrijving" OperatorType="1">'.$artGroupName.'</Field></Filter></Filters>';
		}
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_ArticleGroups_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_ArticleGroups_App) && count($data->Profit_ArticleGroups_App) > 0){
			$articleGroup = $this->Afas_model->xml2array($data->Profit_ArticleGroups_App);
			if(!empty($articleGroup)){
				if(isset($articleGroup['parent']) && $articleGroup['parent'] != ''){
					$level = $articleGroup['level'];
					$categoryArray[$level] = $articleGroup['Omschrijving'];
					$categoryArray = $this->getAfasCategory($projectId, $articleGroup['parent'], '', $categoryArray);
				} else {
					$level = $articleGroup['level'];
					$categoryArray[$level] = $articleGroup['Omschrijving'];
				}
				return $categoryArray;
			}
		}
		return $categoryArray;
	}

}