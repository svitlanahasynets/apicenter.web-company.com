<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Mailchimp extends CI_Controller {

    public function __construct(){
        parent::__construct();
        return;
    }

    // This method is called through webhook from mailchimp to update in afas.
    public function checkWebhook(){
        $request = $_REQUEST;
        $list_id = $request['data']['list_id'];
        $this->load->model('Projects_model');

       /* $f = fopen('custom.txt', 'a+');
        fwrite($f, print_r($request, true));
        fclose($f);*/
        log_message('debug', 'webhook is okey');
        // project id from list_id of mailchimp
        $projectId = $this->Projects_model->getProjectId('mailchimp_list_id', $list_id) ? $this->Projects_model->getProjectId('mailchimp_list_id', $list_id) : '';
        $project    = $this->db->get_where('projects', array('id' => $projectId))->row_array();
        log_message('debug', 'Mailchimp afas Project id');
        log_message('debug', var_export($projectId, true));
        if($projectId!=''){
            if($project['erp_system']=='afas'){
                $contact_type = $this->Projects_model->getValue('contact_type', $projectId)?$this->Projects_model->getValue('contact_type', $projectId):'contacts'; 
                if($contact_type=='contacts'){
                    $this->load->model('Afas_model');
                    $this->Afas_model->postOrPatchOrgCustomer($request,$projectId);
                } else {
                    $this->load->model('Afas_model');
                    $this->Afas_model->postOrPatchOrgDebtor($request,$projectId);
                }
            } else if($project['erp_system']=='twinfield'){
                $this->load->model('Twinfield_model');
                $this->Twinfield_model->postOrPatchTwinCustomer($projectId, $request);
            }
        }
    }

    // this method is used to fetch contact from afas as per project setting and import in mailchimp
    public function importCustomerMailchimp(){
		$this->load->model('Afas_model');
		$this->load->model('Mailchimp_model');
        $this->load->model('Projects_model');
        // get all projects having erp system afas with mailchimp.
        $projects = $this->db->get_where('projects', array('erp_system' => 'afas'))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='mailchimp')
                    continue;
                $lastExecution      = $this->Projects_model->getValue('customers_last_execution', $p_value['id']);
                $customersInterval  = $this->Projects_model->getValue('customers_interval', $p_value['id']);
                $enabled            = $this->Projects_model->getValue('customers_enabled', $p_value['id']);
                $enabled_co         = $this->Projects_model->getValue('enabled', $p_value['id']);
                // check if the last execution time is satisfy the time checking.
                if($enabled_co == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $up_time = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('customers_last_execution', $up_time, $p_value['id']);
                    // get the offset and amount to import customers.
                    $currentCustomersOffset = $this->Projects_model->getValue('mailchimp_offset', $p_value['id']) ? $this->Projects_model->getValue('mailchimp_offset', $p_value['id']) : 0;
                    $customersAmount = $this->Projects_model->getValue('customers_amount', $p_value['id']) ? $this->Projects_model->getValue('customers_amount', $p_value['id']) : 10;
                    $result = $this->Afas_model->getCustomer($p_value['id'],$currentCustomersOffset, $customersAmount);
                    if ($result) {
                        if($result['numberOfResults']>0){
                            $new_offset = $currentCustomersOffset + $result['numberOfResults'];
                            // call mode maplchimp to omport data in mailchimp
                            if(!empty($result['customerData']))
                                $this->Mailchimp_model->importIntoMailchimp($result['customerData'], $p_value['id']);
                            // set new offset in afas.
                            $this->Projects_model->saveValue('mailchimp_offset', $new_offset, $p_value['id']);
                        } else{
                            $this->Projects_model->saveValue('mailchimp_offset', 0, $p_value['id']);
                        }
                    }
                } 
            }
        }
    }

    public function importdebtortest(){
        $this->load->model('Afas_model');
		$this->load->model('Mailchimp_model');
        $this->load->model('Projects_model');
        // get all projects having erp system afas with mailchimp.
        $projects = $this->db->get_where('projects', array('erp_system' => 'afas'))->result_array();
        if(!empty($projects)){
            foreach ($projects as $p_key => $p_value) {
                $projectId          = $p_value['id'];
                if($this->Projects_model->getValue('cms', $projectId)!='mailchimp')
                    continue;
                if($projectId!=11)
                    continue;
                $lastExecution      = $this->Projects_model->getValue('customers_last_execution', $p_value['id']);
                $customersInterval  = $this->Projects_model->getValue('customers_interval', $p_value['id']);
                $enabled            = $this->Projects_model->getValue('customers_enabled', $p_value['id']);
                $enabled_co         = $this->Projects_model->getValue('enabled', $p_value['id']);
                // check if the last execution time is satisfy the time checking.
                //if($enabled_co == '1' && $enabled == '1' && ($lastExecution == '' || ($lastExecution + ($customersInterval * 60) <= time()))){
                    //reset last execution time
                    $up_time = $lastExecution + ($customersInterval * 60);
                    $this->Projects_model->saveValue('customers_last_execution', $up_time, $p_value['id']);
                    // get the offset and amount to import customers.
                    $currentCustomersOffset = $this->Projects_model->getValue('mailchimp_offset', $p_value['id']) ? $this->Projects_model->getValue('mailchimp_offset', $p_value['id']) : 0;
                    $customersAmount = $this->Projects_model->getValue('customers_amount', $p_value['id']) ? $this->Projects_model->getValue('customers_amount', $p_value['id']) : 10;
                    $result = $this->Afas_model->getCustomer($p_value['id'],0, 10);
                    print_r($result);
                    //exit;
                    if ($result) {
                        if($result['numberOfResults']>0){
                            $new_offset = $currentCustomersOffset + $result['numberOfResults'];
                            // call mode maplchimp to omport data in mailchimp
                            if(!empty($result['customerData']))
                                $this->Mailchimp_model->importIntoMailchimp($result['customerData'], $p_value['id']);
                            // set new offset in afas.
                            $this->Projects_model->saveValue('mailchimp_offset', $new_offset, $p_value['id']);
                        } else{
                            $this->Projects_model->saveValue('mailchimp_offset', 0, $p_value['id']);
                        }
                    }
                //} 
            }
        }
    }
    
}
/* End of file mailchimp.php */
/* Location: ./application/controllers/mailchimp.php */