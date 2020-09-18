<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class MewsController extends MY_Controller {

    public function index() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        $this->load->model('Mews_model');
        $this->load->model('Iconneqt_model');
        $customers = $this->Mews_model->getCustomers(90, date("Y-m-d\TH:i:s", strtotime('-5 minutes')), date("Y-m-d\TH:i:s", time()));

        if ($customers) {
            foreach ($customers as $customer) {
                $resutl = $this->Iconneqt_model->sendCutomers(90, $customer);
            }
        }
    }
}