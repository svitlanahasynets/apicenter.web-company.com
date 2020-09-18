<?php

use function GuzzleHttp\json_encode;

class Iconneqt_model extends CI_Model {

    function __construct()
    {
        parent::__construct();
        $this->load->helpers('iconneqt/Iconneqt');
    }
    
    public function sendCutomers($projectId, $customerData) 
    {
        if ($this->checkCustomerExists($projectId, $customerData) === false) {
            $this->createCustomer($projectId, $customerData);
        }
    }

    public function checkCustomerExists($projectId, $customerData)
    {
    $response = check_customer($projectId, $customerData['Email']);

        if (isset($response['message'])) {
            project_error_log($projectId, 'importcustomers', 'Could not export customer, ' . $response['message']);

            return false;
        }

        return $response;
    }

    public function createCustomer($projectId, $customerData) 
    {
        $customerData = [
            'email' => $customerData['Email'],
            'fields' => []
            // 'fields' => [
            //     3056 => 'Slim',
            //     3057 => 'CHEBBI',
            // ]
        ];
        $response = create_customer($projectId, $customerData);
        
        if ($response['message']) {
            project_error_log($projectId, 'importcustomers', 'Could not export customer, ' . $response['message']);
        }

        api2cart_log($projectId, 'importcustomers', 'Created customer, e-mail = ' . $customerData['email'] .  ', id = ' . $response);
    }

}