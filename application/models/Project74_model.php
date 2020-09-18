<?php
class Project74_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 74;
    }
    
	public function customCronjob(){
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Cms_model');
		$this->load->model('Moodle_model');
		
		$projectId = 74;
		$project = $this->db->get_where('projects', array('id' => 74))->row_array();
		// Check if enabled
		if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
			return;
		}
		
		// Get customers
		$lastExecution = $this->Projects_model->getValue('orgper_last_execution_customcron', $project['id']);
		$interval = 5;
$interval = 2;
		if(($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$amount = 20;
			
			$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
			$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
			$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
			$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
			$connector = 'Profit_OrgPer_Moodle2';
			$currentOffset = $this->Projects_model->getValue('orgper_offset', $project['id']) ? $this->Projects_model->getValue('orgper_offset', $project['id']) : 0;
			$this->Projects_model->saveValue('orgper_offset', $currentOffset + $amount, $project['id']);
			$this->load->helper('NuSOAP/nusoap');
			
			$client = new nusoap_client($afasGetUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();

	        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
	        $client->soap_defencoding = 'UTF-8';
	        $client->decode_utf8 = false;
			
			$xml_array = array();
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorId'] = $connector;
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Gewijzigd_op" OperatorType="2">'.date('Y-m-d').'T00:00:00'.'</Field></Filter></Filters>';
$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Gewijzigd_op" OperatorType="2">2019-01-01T00:00:00'.'</Field></Filter></Filters>';
			$xml_array['filtersXml'] = $filtersXML;
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$currentOffset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="Nummer" OperatorType="0" /></Index></options>';
			
			$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
			$resultData = $result["GetDataWithOptionsResult"];
	
			$data = simplexml_load_string($resultData);
			$counter = 0;
			if(isset($data->$connector) && count($data->$connector) > 0){
				$results = array();
				foreach($data->$connector as $customer){
					$customerName = explode(' ', (string)$customer->Contact_Naam);
					$customerFirstName = $customerName[0];
					unset($customerName[0]);
					$customerLastName = implode(' ', $customerName);
					if($customerLastName == ''){
						$customerLastName = '_';
					}
					$customerData = array(
						'id' => (string)$customer->Nummer_2,
						'name' => (string)$customer->Contact_Naam,
						'first_name' => $customerFirstName,
						'last_name' => $customerLastName
					);
					if((string)$customer->{"Organisatie_Telefoonnr._werk"} != ''){
						$customerData['phone'] = (string)$customer->{"Organisatie_Telefoonnr._werk"};
						if($customerData['phone'] == ''){
							unset($customerData['phone']);
						}
					}
					if((string)$customer->{"Portal_e-mail"} != ''){
						$customerData['email'] = strtolower((string)$customer->{"Portal_e-mail"});
						if($customerData['email'] == ''){
							unset($customerData['email']);
						}
					}
					$this->Cms_model->createCustomer($projectId, $customerData);
				}
			} else {
				$this->Projects_model->saveValue('orgper_offset', 0, $project['id']);
			}

			if($data->$connector != false && !empty($data->$connector)){				
				$this->Projects_model->saveValue('orgper_last_execution_customcron', time(), $project['id']);
			}
		}

		// Enroll users
		$lastExecution = $this->Projects_model->getValue('enroll_last_execution_customcron', $project['id']);
		$interval = 5;
$interval = 2;
		if(($lastExecution == '' || $lastExecution + ($interval * 60) <= time())){
			$amount = 20;
			
			$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
			$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
			$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
			$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
			$connector = 'Cursussen_Moodle_Gebruikers';
			$currentOffset = $this->Projects_model->getValue('enroll_offset', $project['id']) ? $this->Projects_model->getValue('enroll_offset', $project['id']) : 0;
			$currentOffsetDate = $this->Projects_model->getValue('enroll_offset_date', $project['id']) ? $this->Projects_model->getValue('enroll_offset_date', $project['id']) : '';
			if(date('Ymd') > $currentOffsetDate){
				$currentOffset = 0;
				$this->Projects_model->saveValue('enroll_offset', 0, $project['id']);
				$this->Projects_model->saveValue('enroll_offset_date', date('Ymd'), $project['id']);
			}
			$this->load->helper('NuSOAP/nusoap');
			
			$client = new nusoap_client($afasGetUrl, true);
			$client->setUseCurl(true);
			$client->useHTTPPersistentConnection();

	        /* ADDED TO SUPPORT DIFFERENT CHARACTERS */		
	        $client->soap_defencoding = 'UTF-8';
	        $client->decode_utf8 = false;
			
			$xml_array = array();
			$xml_array['environmentId'] = $afasEnvironmentId;
			$xml_array['token'] = $afasToken;
			$xml_array['connectorId'] = $connector;
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Aangemaakt_op" OperatorType="2">'.date('Y-m-d', strtotime('-2 days')).'T00:00:00'.'</Field></Filter></Filters>';
//$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Aangemaakt_op" OperatorType="2">2019-01-01T00:00:00'.'</Field></Filter></Filters>';
			$xml_array['filtersXml'] = $filtersXML;
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$currentOffset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="Aangemaakt_op" OperatorType="1" /></Index></options>';
			
			$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
			$resultData = $result["GetDataWithOptionsResult"];
	
			$data = simplexml_load_string($resultData);
			$counter = 0;
			if(isset($data->$connector) && count($data->$connector) > 0){
				$this->Projects_model->saveValue('enroll_offset', $currentOffset + count($data->$connector), $project['id']);
				$results = array();
				foreach($data->$connector as $attendee){
					$searchCustomer = array('email' => strtolower((string)$attendee->PortalGebruiker));
					$customerExists = $this->Moodle_model->checkCustomerExists($searchCustomer, $projectId);
					if(!isset($customerExists['items'][0])){
						continue;
					}
					$userId = $customerExists['items'][0]['id'];
					
					$courseId = (string)$attendee->Itemcode;
					$searchCourse = array('code' => $courseId);
					$courseExists = $this->Moodle_model->checkCourseExists($searchCourse, $projectId);
					if(!isset($courseExists['items'][0])){
						continue;
					}
					$courseId = $courseExists['items'][0]['id'];
					
					$result = $this->Moodle_model->enrolUser($projectId, array('userid' => $userId, 'courseid' => $courseId));
                    apicenter_logs($projectId, 'importcustomers', 'Enrolled user with ID '.$userId.' to course with ID '.$courseId, false);
				}
			} else {
				$this->Projects_model->saveValue('enroll_offset', 0, $project['id']);
			}

			if($data->$connector != false && !empty($data->$connector)){
				$this->Projects_model->saveValue('enroll_last_execution_customcron', time(), $project['id']);
			}
		}
	}
}