<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Afas extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Afas_model');
        $this->load->model('Projects_model');
    }

    public function get_stock($sku)
    {
        $project_id = $this->input->get('project_id');

        if (empty($project_id)) {
            return $this->set_error(400, 'Project ID is required');
        }

        $project_token = $this->Projects_model->getValue('afas_api_stock_token', $project_id);
        $token = $this->input->get('token');

        if (!isset($token) || $token != $project_token) {
            return $this->set_error(403, 'Token is wrong');
        }

        if (empty($sku)) {
            return $this->set_error(400, 'SKU is required');
        }

        $result = $this->Afas_model->getStockBysku($project_id, $sku);
        
        if (isset($result['faultcode'])) {
            return $this->set_error(500, $result['faultcode']);
        }
        
        if (isset($result['numberOfResults']) && $result['numberOfResults'] == 0) {
            return $this->set_error(400, 'Item not found');
        }

        $stokcData = $result['stockData'];

        return $this->output
            ->set_status_header(200)
            ->set_content_type('application/json')
            ->set_output(trim(json_encode(array('in_stock' => $stokcData['In_stock']))));

    }

    private function set_error($status, $message)
    {
        return $this->output
            ->set_status_header($status)
            ->set_content_type('application/json')
            ->set_output(json_encode(array('message' => $message)));
    }

}