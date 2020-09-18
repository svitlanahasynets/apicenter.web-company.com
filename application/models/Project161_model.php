<?php
class Project161_model extends CI_Model {

	public $projectId;

    function __construct() {
        parent::__construct();
        $this->projectId = 161;
    }
	
    public function getArticleData($articleData, $finalArticleData){
        
        $finalData = $finalArticleData;
        
        $finalData['custom_attributes']['Conditie'] = "NEW";
        
        $finalData['custom_attributes']['BezorgCode'] = "FBR";
        
        $finalData['custom_attributes']['Publiseren'] = "false";
        
        $finalData['custom_attributes']['ReferentieCode'] = $articleData['ItemCode'];
        
        if(isset($articleData['BOL_EAN']) && $articleData['BOL_EAN'] != '' && $articleData['BOL_EAN'] != null) {
            $finalData['custom_attributes']['EAN'] = $articleData['BOL_EAN'];
        }
        
        if(isset($articleData['BOL_Price']) && $articleData['BOL_Price'] != '' && $articleData['BOL_Price'] != null) {
            $finalData['custom_attributes']['BOL_Price'] = $articleData['BOL_Price'];
        }
        
        if(isset($articleData['BOL_IsBolProduct']) && $articleData['BOL_IsBolProduct'] != '' && $articleData['BOL_IsBolProduct'] != null) {
            $finalData['custom_attributes']['BOL_IsBolProduct'] = $articleData['BOL_IsBolProduct'];
        }
        
        $qty = $this->getItemQty($articleData['ItemCode']);
		$finalData['quantity'] = $qty;
		
        
        return $finalData;
    }
    
    public function getItemQty($itemCode){
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
		$xml_array['connectorId'] = "Profit_Stock_App";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
        
		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_Stock_App) && count($data->Profit_Stock_App) > 0){
			$itemData = $this->Afas_model->xml2array($data->Profit_Stock_App);
			if(!empty($itemData)){
				return intval($itemData['StockActual']);
			}
		}
		return 0;
	}
	
	public function setOrderParams($fields, $orderData){
	    $fields->TrPt = "7";
	    //$fields->InVa = true;
	    $fields->RfCs = "BOL" . $orderData['order_id'];
	}
	
	public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
	     $fields->InPv = 'E';
	}
}