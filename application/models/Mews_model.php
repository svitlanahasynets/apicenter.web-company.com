<?php
   
class Mews_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->library('mews');
        $this->load->model('Projects_model');
    }

    public function getCustomers($projectId, $start_utc, $end_utc) 
    {
        $platform_address = $this->Projects_model->getValue('mews_platform_address', $projectId) 
            ? $this->Projects_model->getValue('mews_platform_address', $projectId) : '';
        $client_token = $this->Projects_model->getValue('mes_client_token', $projectId) 
            ? $this->Projects_model->getValue('mes_client_token', $projectId) : '';
        $access_oken = $this->Projects_model->getValue('mews_access_token', $projectId) 
            ? $this->Projects_model->getValue('mews_access_token', $projectId) : '';

        $mews = new Mews($platform_address, $client_token, $access_oken);

        $customers = $mews->getCustomers('Created', $start_utc, $end_utc);

        if ($customers['status'] === false) {
            project_error_log($projectId, 'importcustomers', 'Could not export customer, ' . $customers['message']);

            return false;
        }

        return $customers['data'];
    }

    private function dd($data)
    {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }

}