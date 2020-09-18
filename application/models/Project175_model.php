<?php
class Project175_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 175;
    }
	
	public function getArticleData($articleData, $finalArticleData){
	    $finalData = $finalArticleData;
	    
	    
	    if(isset($articleData['Webshop_Titel']) && $articleData['Webshop_Titel'] != '' && $articleData['Webshop_Titel'] != null)
		{
			$finalData['custom_attributes']['Webshop_Titel'] = $articleData['Webshop_Titel'];
		}
		
	    if(isset($articleData['ShortDescr']) && $articleData['ShortDescr'] != '' && $articleData['ShortDescr'] != null)
		{
			$finalData['custom_attributes']['ShortDescr'] = $articleData['ShortDescr'];
		}
		
		if(isset($articleData['LargeDescr']) && $articleData['LargeDescr'] != '' && $articleData['LargeDescr'] != null)
		{
			$finalData['custom_attributes']['LargeDescr'] = $articleData['LargeDescr'];
		}
		
		if(isset($articleData['Meta_Title']) && $articleData['Meta_Title'] != '' && $articleData['Meta_Title'] != null)
		{
			$finalData['custom_attributes']['Meta_Title'] = $articleData['Meta_Title'];
		}
		
		if(isset($articleData['Meta_Description']) && $articleData['Meta_Description'] != '' && $articleData['Meta_Description'] != null)
		{
			$finalData['custom_attributes']['Meta_Description'] = $articleData['Meta_Description'];
		}
		
		if(isset($articleData['Merk']) && $articleData['Merk'] != '' && $articleData['Merk'] != null)
		{
			$finalData['custom_attributes']['Merk'] = $articleData['Merk'];
		}
		
		if(isset($articleData['EAN']) && $articleData['EAN'] != '' && $articleData['EAN'] != null)
		{
			$finalData['custom_attributes']['EAN'] = $articleData['EAN'];
		}
		
		$finalArticleData['model'] = str_replace('+', '.2.', $finalArticleData['model']);
		$finalArticleData['model'] = str_replace('/', '.1.', $finalArticleData['model']);
		
	    return $finalData;
	}
	
	public function setOrderProductParams($fields, $productData){
	    $fields->ItCd = str_replace('.2.', '+', $fields->ItCd);
	    $fields->ItCd = str_replace('.1.', '/', $fields->ItCd);
	}
	
	public function setCustomerParams($fields, $customerData, $ordernumber = "", $orderData = array()){
	    $fields->InPv = 'U';
	}
	
	public function setOrderParams($fields, $orderData){
	    
	    if($orderData['billing_address']['country'] == 'BE'){
	        $fields->Unit = '8';
	    }
	    
	    $fields->RfCs = 'WS: ' . $orderData['id'];
	    $fields->PaCd = 'Reeds';
	    $fields->PaTp = '04';
	    
	   unset($fields->OrNu);
	}
	
	public function checkAfasCustomerExists($projectId, $customerData, $ordernumber = "", $orderData){
		$contactPersonId = false;
		$debtorId = $this->Afas_model->checkAfasCustomer($projectId, $customerData, 'email');
		
		if(!$debtorId){
			// Check contacts
			$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
			$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
			$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
			$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
			$contactsConnector = 'Profit_Contacts_App';
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="MailWork" OperatorType="1">'.$customerData['email'].'</Field></Filter></Filters>';
			
			$this->load->helper('NuSOAP/nusoap');
			$client = new nusoap_client($afasGetUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();
			
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorId'] = $contactsConnector;
			$xml_array['filtersXml'] = $filtersXML;
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
			
			$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = $this->Afas_model->replaceSpecialChars($resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			$resultData = $this->Afas_model->unReplaceSpecialChars($resultData);
			
			$data = simplexml_load_string($resultData);
			if(isset($data->$contactsConnector) && count($data->$contactsConnector) > 0){
				$data = $data->$contactsConnector;
				if(isset($data->OrgNumber) && $data->OrgNumber != ''){
					$contactPersonId = (string)$data->ContactId;
					// Get sales relation ID
					$debtorFiltersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="BcCo" OperatorType="1">'.(string)$data->OrgNumber.'</Field></Filter></Filters>';
					$afasDebtorConnector = 'Profit_Debtor_App';
					
					$client = new nusoap_client($afasGetUrl, true);
					$client->setUseCurl(true);
					$client->useHTTPPersistentConnection();
					
					$xml_array['environmentId'] = $afasEnvironmentId;
					$xml_array['token'] = $afasToken;
					$xml_array['connectorId'] = $afasDebtorConnector;
					$xml_array['filtersXml'] = $debtorFiltersXML;
					$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
					
					$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
					$resultData = $result["GetDataWithOptionsResult"];
					$resultData = $this->Afas_model->replaceSpecialChars($resultData);
					$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
					$resultData = $this->Afas_model->unReplaceSpecialChars($resultData);
					
					$data = simplexml_load_string($resultData);
					if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
						$debtorData = $data->$afasDebtorConnector;
						$debtorId = $debtorData->DebtorId;
						if($debtorId != ''){
							$debtorId = (string)$debtorId;
						}
					}
				}
			}
		}
		if(!$debtorId){
			if($this->Afas_model->createAfasCustomer($projectId, $customerData, $ordernumber, $orderData)){
				$debtorId = $this->Afas_model->checkAfasCustomer($projectId, $customerData, 'email');
				$contactPersonId = false;
			}
		}
		return array(
			'debtor_id' => $debtorId,
			'contact_person_id' => $contactPersonId
		);
	}
	
	public function loadCustomOrderAttributes($appendItem, $order, $projectId){
		// Check if order already exists. If so, skip.
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$connector = 'Profit_Salesorders_App';
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="CustomerReference" OperatorType="6">%WS: '.$appendItem['id'].'%</Field></Filter></Filters>';
		
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $connector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = $this->Afas_model->replaceSpecialChars($resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$resultData = $this->Afas_model->unReplaceSpecialChars($resultData);
		
		$data = simplexml_load_string($resultData);
		if(isset($data->$connector) && count($data->$connector) > 0){
			// Order exists, do not add again
			// Prevent getting stuck
			$this->Projects_model->saveValue('orders_offset', $appendItem['order_id'], $projectId);
			return false;
		}
		return $appendItem;
	}
}