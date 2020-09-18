<?php //APICDEV

class Mailchimp_model extends CI_Model {

	private $dropdownparams = [
		"true" => "Ja",
		"false" => "Nee"
   	];
	
    function __construct(){
        parent::__construct();
    }

    // method used to import data in mailchimp
    function importIntoMailchimp($data, $projectId, $method=''){

    	$this->load->model('Projects_model');
    	$this->load->helper('mailchimp/Mailchimp');
    	$this->load->helper('tools');
		$this->load->helper('constants');

		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId);
		$contact_type = $this->Projects_model->getValue('contact_type', $projectId)?$this->Projects_model->getValue('contact_type', $projectId):'contacts'; 

		$totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
		$totalCustomerImportError 	= $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;
		// create connection with mailchimp.
		$MailChimp = new MailChimpMethod($api_key);
		// run loop for each contacts and create or update if exists through put method.
		
		//ADDED 1 Augustus
		$data = $this->formCustomerFormatForMailchimp($data, $contact_type, $projectId);
		
		foreach ($data as $key => $value) {

			$email_hash = $MailChimp->subscriberHash($value['email_address']);
			$already_imported = $this->checkImportedContacts($projectId,$value['merge_fields']);
			if($already_imported){
				$method = 'update';
				$email_hash = $MailChimp->subscriberHash($already_imported);
			}
			if($method!='' && $method=='update'){
				$result = $MailChimp->patch("lists/$list_id/members/$email_hash", $value);
			} else{
				$result = $MailChimp->put("lists/$list_id/members/$email_hash", $value);
			}
			if(isset($result['status']) && ($result['status']==400 || $result['status']==404)){
				// get log if failed
				if($project['erp_system']=='afas'){
					if($contact_type=='contacts')
            			apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for name : ".$value['merge_fields']['NAME']." and ContactId ".$value['merge_fields']['CONTACTID'], true );
            		else
            			apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for name : ".$value['merge_fields']['DEBTORNAME']." and DEBTORID: ".$value['merge_fields']['DEBTORID'], true );
                	$totalCustomerImportError++;
				} else if($project['erp_system']=='twinfield'){
            		apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for name : ".$value['merge_fields']['NAME']." and email id".$value['email_address'], true );
				}
			} else{
				if($project['erp_system']=='afas'){
					if($method==''){
						if($contact_type=='contacts')
            				apicenter_logs($projectId, 'importcustomers'," Success:  Customer ".$value['merge_fields']['NAME']." and ContactId ".$value['merge_fields']['CONTACTID'].' imported successfully. ', false );
            			else
            				apicenter_logs($projectId, 'importcustomers'," Success:  Customer ".$value['merge_fields']['DEBTORNAME']." and DEBTORID: ".$value['merge_fields']['DEBTORID'].' imported successfully. ', false );
                		$totalCustomerImportSuccess++;
					} 
				} else if($project['erp_system']=='twinfield'){
            		apicenter_logs($projectId, 'importcustomers'," Success:  Customer ".$value['merge_fields']['NAME']." and ContactId ".$value['merge_fields']['CONTACTID'].' imported successfully. ', false );
				}
			}
		}

		$this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
		$this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
    }
    
    function checkImportedContacts($projectId,$customer){
        $this->load->model('Projects_model');
        $this->load->helper('mailchimp/Mailchimp');
     
        $api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
        $list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId);
		$contact_type = $this->Projects_model->getValue('contact_type', $projectId)?$this->Projects_model->getValue('contact_type', $projectId):'contacts'; 
        $MailChimp = new MailChimpMethod($api_key);
        if($contact_type=='contacts')
        	$result = $MailChimp->get("search-members",['query' => $customer['CONTACTID'],'list_id'=>$list_id]);
        else{
        	$result = $MailChimp->get("search-members",['query' => $customer['DEBTORID'],'list_id'=>$list_id]);
        }
        if($result){
        	if($result['full_search']['total_items']>0){
        		foreach ($result['full_search']['members'] as $key => $value) {
        			if($value['merge_fields']['DEBTORID']==$customer['DEBTORID']){
        				return $value['email_address'];
        			}
        		}
        	} else
        		return false;
        } else
        	return false;
    }

    function updateIntoMailchimp($value, $projectId, $method=''){
    	$this->load->model('Projects_model');
    	$this->load->helper('mailchimp/Mailchimp');
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
    	$api_key = $project['mailchimp_api'];
		$list_id = $project['mailchimp_list_id'];
		$this->load->helper('tools');
		$this->load->helper('constants');

		// cretae connection with mailchimp.
		$MailChimp = new MailChimpMethod($api_key);
		// run loop for each contacts and create or update if exists through put method.
		$email_hash = $MailChimp->subscriberHash($value['email_address']);

		$result = $MailChimp->put("lists/$list_id/members/$email_hash", $value);
		if(isset($result['status']) && $result['status']==400){
			// get log if failed
			if($project['erp_system']=='afas'){
        		apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for name : ".$value['email_address']." and ContactId ".$value['merge_fields']['CONTACTID'], true );
			} else if($project['erp_system']=='twinfield'){
        		apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for email : ".$value['email_address']." and email id".$value['email_address'], true );
			}
		}
    }

    // method used to import data in mailchimp
    function importTwinIntoMailchimp($projectId, $data){
    	$this->load->model('Projects_model');
    	$this->load->helper('mailchimp/Mailchimp');
		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
    	$api_key = $project['mailchimp_api'];
		$list_id = $project['mailchimp_list_id'];
		$this->load->helper('tools');
		$this->load->helper('constants');

		// crete connection with mailchimp.
		$MailChimp = new MailChimpMethod($api_key);

		// run loop for each contacts and create or update if exists through put method.
		foreach ($data as $key => $value) {
			$email_hash = $MailChimp->subscriberHash($value['email_address']);
			$result = $MailChimp->put("lists/$list_id/members/$email_hash", $value);
			if(isset($result['status']) && ($result['status']==401 || $result['status']==404 || $result['status']==400) ){
            	apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail']." for name : ".$value['merge_fields']['NAME']." and email id".$value['email_address'], true );
			}
		}
    }

    // method is used to create webhook when a is created or updated
    function putMailchimpWebhook($projectId){
    	$this->load->model('Projects_model');
    	$this->load->helper('mailchimp/Mailchimp');
    	$this->load->helper('tools');
		$this->load->helper('constants');

		$project = $this->db->get_where('projects', array('id' => $projectId))->row_array();
		if(!empty($project)){
			$totalCustomerImportSuccess = $this->Projects_model->getValue('total_customer_import_success', $projectId)?$this->Projects_model->getValue('total_customer_import_success', $projectId):0;
			$totalCustomerImportError 	= $this->Projects_model->getValue('total_customer_import_error', $projectId)?$this->Projects_model->getValue('total_customer_import_error', $projectId):0;

			$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
			$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
			$contact_type = $this->Projects_model->getValue('contact_type', $projectId); 
			$connection = $project['erp_system'];
			$MailChimp = new MailChimpMethod($api_key);

			$result = $MailChimp->get("lists/$list_id/webhooks");
			$status = true;
			$webhooks = array();
			$url = site_url().'/mailchimp/checkWebhook';
			if(isset($result['status']) && ($result['status']==401 || $result['status']==404)){
				// get log if failed echo "failed".$result['detail'].'<br/>';
                apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail'], true);
                $totalCustomerImportError++;
			} else{
				if(isset($result['webhooks']))
					$webhooks = $result['webhooks'];
				else {
					if(!empty($webhooks)){
						foreach ($webhooks as $key => $value) {
							if($url==$value['url']){
								$status = false;
							}
						}
					}
				}
				if($status){
					$events = new stdClass();
					$events->subscribe = true;
					$events->unsubscribe = true;
					$events->profile = true;
					$events->cleaned = true;
					$events->upemail = false;
					$events->campaign = false;
					$sources = new stdClass();
					$sources->user = true;
					$sources->admin = true;

					$result = $MailChimp->post("lists/$list_id/webhooks",['url'=>$url, 'events'=>$events, 'sources'=>$sources]);
					if(isset($result['status']) && ($result['status']==401 || $result['status']==404)){
						// get log if failed echo "failed".$result['detail'].'<br/>';
                		apicenter_logs($projectId, 'importcustomers'," Failed : ".$result['detail'], true);
                  		$totalCustomerImportError++;
					}  else{
						$this->createWebForm($api_key, $list_id, $connection, $contact_type);
                  		//$totalCustomerImportSuccess++;
					}
				}
				$this->Projects_model->saveValue('total_customer_import_success', $totalCustomerImportSuccess, $projectId);
                $this->Projects_model->saveValue('total_customer_import_error', $totalCustomerImportError, $projectId);
			}
		}
    	return true;
    }

    // method is used to create web form in mailchimp when project is created or updated
    function createWebForm($api_key,$list_id,$connection, $contact_type='contacts'){
    	$this->load->helper('mailchimp/Mailchimp');
		$MailChimp 		= new MailChimpMethod($api_key);
		$array_merges  = array();
		if($connection == 'afas'){
			if($contact_type == 'debtor'){
				$array_merges = array(
					array(
						'tag'		=> 'DEBTORID',
						'name'		=> 'DebtorId',
						'type'		=> 'text',
						'help_text'	=> 'DebtorId'
					),
					array(
						'tag'		=> 'DEBTORNAME',
						'name'		=> 'DebtorName',
						'type'		=> 'text',
						'help_text'	=> 'DebtorName'
					),
					array(
						'tag'		=> 'BCCO',
						'name'		=> 'BcCo',
						'type'		=> 'text',
						'help_text'	=> 'BcCo'
					),
					array(
						'tag'		=> 'EMAIL',
						'name'		=> 'Email',
						'type'		=> 'text',
						'help_text'	=> 'Email'
					),
					array(
						'tag'		=> 'CREATEDATE',
						'name'		=> 'CreateDate',
						'type'		=> 'text',
						'help_text'	=> 'CreateDate'
					),
					array( 
						'tag'		=> 'MODIFIDATE',
						'name'		=> 'ModifiedDate',
						'type'		=> 'text',
						'help_text'	=> 'ModifiedDate'
					),
					array(
						'tag'		=> 'STATSRELAT',
						'name'		=> 'Status relatie',
						'type'		=> 'text',
						'help_text'	=> 'Status relatie'
					)
				);
			}
			if($contact_type=='contacts') {
				$array_merges = array(
					array(
						'tag'		=> 'TYPE',
						'name'		=> 'Type',
						'type'		=> 'text',
						'help_text'	=> 'Type'
					),
					array(
						'tag'		=> 'CONTACTID',
						'name'		=> 'ContactId',
						'type'		=> 'text',
						'help_text'	=> 'ContactId'
					),
					array(
						'tag'		=> 'NAME',
						'name'		=> 'Name',
						'type'		=> 'text',
						'help_text'	=> 'Name'
					),
					array(
						'tag'		=> 'FIRSTNAME',
						'name'		=> 'First name',
						'type'		=> 'text',
						'help_text'	=> 'First name'
					),
					array(
						'tag'		=> 'LASTNAME',
						'name'		=> 'Last name',
						'type'		=> 'text',
						'help_text'	=> 'Last name'
					),
					array(
						'tag'		=> 'ORGNUMBER',
						'name'		=> 'OrgNumber',
						'type'		=> 'text',
						'help_text'	=> 'OrgNumber'
					),
					array(
						'tag'		=> 'PERNUMBER',
						'name'		=> 'PerNumber',
						'type'		=> 'text',
						'help_text'	=> 'PerNumber'
					)
				); 
			}

		} else if($connection == 'twinfield'){

			$array_merges = array(

				array(
					'tag'		=> 'OFFICE',
					'name'		=> 'Office',
					'type'		=> 'text',
					'help_text'	=> 'Office'
				),
				array(
					'tag'		=> 'NAME',
					'name'		=> 'Name',
					'type'		=> 'text',
					'help_text'	=> 'Name'
				),
				array(
					'tag'		=> 'CODE',
					'name'		=> 'Code',
					'type'		=> 'text',
					'help_text'	=> 'Code'
				)

			); 

		}

		if(!empty($array_merges)){

			$result_delete = $MailChimp->get("lists/$list_id/merge-fields");
			foreach ($result_delete['merge_fields'] as $key => $value) {
				if($value['tag']=='FNAME' || $value['tag']=='LNAME'){
					$merge_id = $value['merge_id'];
					$resultq = $MailChimp->delete("lists/$list_id/merge-fields/$merge_id");
				}
			}
			foreach ($array_merges as $key => $value) {
				$result = $MailChimp->post("lists/$list_id/merge-fields",array(

	                    "tag" => $value['tag'],
	                    "required" => false, 
	                    "name" => $value['name'],
	                    "type" => $value['type'],
	                    "default_value" => "", 
	                    "public" => true, 
	                    "help_text" => $value['help_text']
	                ));
			}
		}
	}
	
	function getWebForm ($projectId, $connection = 'afas', $contact_type='contacts') {
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
		$this->load->helper('mailchimp/Mailchimp');
		$MailChimp = new MailChimpMethod($api_key);
		$response  = $MailChimp->get("lists/$list_id/merge-fields", ['count' => '10000']);
		$result    = [];
		if(isset($response['merge_fields'])) {
			$result = $response['merge_fields'];
		}
		return $result;
	}

	function getList($projectId, $connection = 'afas', $contact_type='contacts')
	{
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
		$this->load->helper('mailchimp/Mailchimp');
		$MailChimp = new MailChimpMethod($api_key);
		$response  = $MailChimp->get("lists");
		// $result    = [];
		// if(isset($response['merge_fields'])) {
		// 	$result = $response['merge_fields'];
		// }
		return $response;
	}

	function createList($projectId, $connection = 'afas', $contact_type='contacts')
	{
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
		$this->load->helper('mailchimp/Mailchimp');
		$MailChimp = new MailChimpMethod($api_key);
		$response  = $MailChimp->get("lists");
		$contact = array (
			'company' => 'test',
			'address1' => 'test',
			'address2' => '5000',
			'city' => 'test',
			'state' => 'GA',
			'zip' => '30308',
			'country' => 'US',
			'phone' => '',
		);
		$cpd = array (
			'from_name' => 'Jelle Troost',
			'from_email' => 'jelle@web-company.nl',
			'subject' => '',
			'language' => 'en',
		);
		$response  = $MailChimp->post("lists", [
			"name" => 'test debtors',
			"contact" => (object) $contact, 
			"permission_reminder" => "Welcome Test",
			"campaign_defaults" => (object) $cpd,
			"email_type_option" => false, 
		]);
		return $response;
	}

	function deleteFields($projectId, $fields) 
	{
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
		$this->load->helper('mailchimp/Mailchimp');
		$MailChimp = new MailChimpMethod($api_key);

		if (is_array($fields)) {
			foreach ($fields as $id) {
				$resultq = $MailChimp->delete("lists/$list_id/merge-fields/$id");
			}
			return $resultq;
		}
	}

	function updateWebForm ($projectId, $connection = 'afas') {
		$this->load->helper('mailchimp/Mailchimp');
		$MailChimp = new MailChimpMethod($api_key);
		$response  = $MailChimp->get("lists/$list_id/merge-fields");
	}

	function formCustomerFormatForMailchimp($data, $contact_type = 'contacts', $projectId)
    {
		$api_key = $this->Projects_model->getValue('mailchimp_api', $projectId);
		$list_id = $this->Projects_model->getValue('mailchimp_list_id', $projectId); 
		
		$this->load->helper('mailchimp/Mailchimp');
		
		$MailChimp = new MailChimpMethod($api_key);
		
		$webFromFields = $this->getWebForm($projectId);
		$colums 	   = array_column($webFromFields, 'tag');
		$customer_data = array();
		$count = 1;
		
		foreach ($data as $val) {
			$each_data = array();
			$val 	   = (array) $val;
			foreach ($val as $field=>$v) {
				$fieldName = $field;
				$field     = trim(strtoupper($field));
				if (strlen($field) > 10) {
					$field = str_replace(" ", "_", $field);
					$field = substr($field, 0, 10);
				}

				if (strripos($field, 'ADDRESS') === 0) {
					$field = 'ADDRESS';
				}

				if ($fieldName == 'MailWork' || $fieldName == 'Email') {
					if ($contact_type == 'contacts') {
						if (!isset($val['MailWork'])) {
							continue;
						}
					} else {
						if (!isset($val['Email'])) {
							continue;
						}
					}

					$each_data['email_address'] = $val['MailWork'];//'test_afas'.$count.'@mail.com';
					$count++;
				} else if (array_search($field, $colums) !== false) {
					if ($field == 'ADDRESS') {
						$line1 = isset($val['AddressLine1']) ? $val['AddressLine1'] : '';
						$line2 = isset($val['AddressLine3']) ? $val['AddressLine3'] : '';
						$value = $line1 . ' ' . $line2;
						$each_data['merge_fields']['ADDRESS'] = $value;
					} else {
						$value = isset($val[$fieldName]) ? $val[$fieldName] : '';
						if ($value == "true" || $value == "false") {
							$value = $this->dropdownparams[$value];
						}
						$each_data['merge_fields'][$field] = $value;
					}
				} else {
					$type = 'text';
					if ($val[$fieldName] == "true" || $val[$fieldName] == "false") {
						$val[$fieldName] = $this->dropdownparams[$val[$fieldName]];
						$type = 'dropdown';
					}

					$args = array(
	                    "tag" => $field,
	                    "required" => false, 
	                    "name" => $fieldName,
	                    "type" => $type,
						"public" => true, 
					);

					if ($type == 'dropdown') {
						$args["options"] = (object) ['choices'=>['', 'Ja', 'Nee']];
					}
					$result = $MailChimp->post("lists/$list_id/merge-fields", $args);

					if (isset($result['merge_id'])) {
						$colums[] = $result['tag'];
						$value = isset($val[$fieldName]) ? $val[$fieldName] : '';
						$each_data['merge_fields'][$result['tag']] = $value;
						apicenter_logs($projectId, 'importcustomers', "Created " . $fieldName . " field", false);
					} else {
						apicenter_logs($projectId, 'importcustomers'," Failed : ". var_export($result, true), true);
					}
				}
			}
			$each_data['status'] = 'subscribed';
			$customer_data[] = $each_data;
		}

		return $customer_data;

	}
	
	function cc() {
		$customer_data = array();
        if ($contact_type == 'contacts') {
            foreach ($data as $key => $value) {
                $each_data = array();
                $value = json_decode(json_encode($value), 1);
                $TYPE = isset($value['Type']) ? $value['Type'] : '';
                $CONTACTID = isset($value['ContactId']) ? $value['ContactId'] : '';
                $NAME = isset($value['Name']) ? $value['Name'] : '';
                $ORGNUMBER = isset($value['OrgNumber']) ? $value['OrgNumber'] : '';
                $PERNUMBER = isset($value['PerNumber']) ? $value['PerNumber'] : '';
                $MAILWORK = isset($value['MailWork']) ? $value['MailWork'] : '';

                $name = '';
                $first_name = '';
                $last_name = '';
                if ($NAME != '') {
                    $name_array = explode(' - ', $NAME);
                    if (isset($name_array['0']))
                        $name = $name_array['0'];
                    else
                        $name = '';
                    $name_array = array_reverse($name_array);
                    if (isset($name_array['0']))
                        $full_name = $name_array['0'];
                    else
                        $full_name = '';
                    if ($full_name != '') {
                        $full_name_array = explode(' ', $full_name);
                        if (isset($full_name_array['0'])) {
                            $first_name = $full_name_array['0'];
                            unset($full_name_array['0']);
                            $rev_array = array_reverse($full_name_array);
                            if (isset($rev_array['0']))
                                $last_name = $rev_array['0'];
                        }
                    }
                }
                $email_address = $MAILWORK;
                if ($email_address == '' || $CONTACTID == '' || $ORGNUMBER == '' || $PERNUMBER == '')
                    continue;
                $each_data['merge_fields'] = ['TYPE' => $TYPE, 'CONTACTID' => $CONTACTID, 'NAME' => $name, 'FIRSTNAME' => $first_name, 'LASTNAME' => $last_name, 'ORGNUMBER' => $ORGNUMBER, 'PERNUMBER' => $PERNUMBER];
                $each_data['email_address'] = $email_address;
                $each_data['status'] = 'subscribed';
                $customer_data[] = $each_data;
            }
        } else {
            foreach ($data as $key => $value) {
                $each_data = array();
                $value = json_decode(json_encode($value), 1);
                $DEBTORID = isset($value['DebtorId']) ? $value['DebtorId'] : '';
                $DEBTORNAME = isset($value['DebtorName']) ? $value['DebtorName'] : '';
                $BCCO = isset($value['BcCo']) ? $value['BcCo'] : '';
                $CREATEDATE = isset($value['CreateDate']) ? $value['CreateDate'] : '';
                $MODIFIEDDATE = isset($value['ModifiedDate']) ? $value['ModifiedDate'] : '';
                $EMAIL = isset($value['Email']) ? $value['Email'] : '';
                $STATUSRELATIE = isset($value['Status_relatie']) ? $value['Status_relatie'] : '';

                $email_address = $EMAIL;
                if ($email_address == '')
                    continue;
                $each_data['merge_fields'] = ['DEBTORID' => $DEBTORID, 'DEBTORNAME' => $DEBTORNAME, 'BCCO' => $BCCO, 'CREATEDATE' => $CREATEDATE, 'MODIFIDATE' => $MODIFIEDDATE, 'STATSRELAT' => $STATUSRELATIE];
                $each_data['email_address'] = $email_address;
                $each_data['status'] = 'subscribed';
                $customer_data[] = $each_data;
            }
        }
        return $customer_data;
	}


	function object2array($data)
	{
		if (is_array($data) || is_object($data))
		{
			$result = array();
			foreach ($data as $key => $value)
			{
				$result[$key] = $this->object2array($value);
			}
			return $result;
		}
		return $data;
	}

}