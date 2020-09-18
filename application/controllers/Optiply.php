<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');


class Optiply extends MY_Controller
{
    public function __construct(){
        parent::__construct();
    }

    public function importsupplier(){
        return;
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Optiply_model');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');
        $this->load->helper('file');

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        write_file(__DIR__.'/response.txt', json_encode($_GET)." \n", 'a');
        write_file(__DIR__.'/response.txt', json_encode($_POST)." \n", 'a');
        write_file(__DIR__.'/response.txt', $originalContent." \n", 'a');

        $projectId = intval($_GET['project_id']);
        $accountId = $this->Projects_model->getValue('optiply_acc_id', $projectId);
        $token = $this->Optiply_model->getAccesToken($projectId);

        $this->Exactonline_model->setData(
            array(
                'projectId' => $projectId,
                'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId).'/?project_id='.$projectId,
                'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );
        $connection = $this->Exactonline_model->makeConnection($projectId);
        if(!$connection){ return; }

        if($content['Action'] != 'Update')
            return;

        $id = $content['Key'];

        $supplierData = $this->Exactonline_model->getSupplier($connection, $id);
        if(!$supplierData['IsSupplier'])
            return;

        $supplierData['Email'] = isset($supplierData['Email']) ? $supplierData['Email'] : '';
        write_file(__DIR__.'/response.txt', json_encode($supplierData)." \n", 'a');
        $supplier = [
            'name' => $supplierData['Name'],
            'email' => $supplierData['Email']
        ];
        write_file(__DIR__.'/response.txt', json_encode($supplier)." \n", 'a');
        $cs = $this->Optiply_model->createSupplier($token, $accountId, $supplier);
        write_file(__DIR__.'/response.txt', json_encode($cs)." \n", 'a');

        echo 'success';
        return;
    }

    public function importsalesorder(){
        return;
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Optiply_model');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');
        $this->load->helper('file');

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        write_file(__DIR__.'/response.txt', json_encode($_GET)." \n", 'a');
        write_file(__DIR__.'/response.txt', json_encode($_POST)." \n", 'a');
        write_file(__DIR__.'/response.txt', $originalContent." \n", 'a');

        $projectId = intval($_GET['project_id']);

        $this->Exactonline_model->setData(
            array(
                'projectId' => $projectId,
                'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId).'/?project_id='.$projectId,
                'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );
        $connection = $this->Exactonline_model->makeConnection($projectId);
        if(!$connection){ return; }

        if($content['Action'] != 'Update')
            return;

        $id = $content['Key'];

        $orderData = $this->Exactonline_model->getSalesOrder($connection, $id);

        $this->Optiply_model->updateOrders($projectId, [$orderData]);

        echo 'success';
        return;
    }

    public function importbuyorder() {
        $this->load->model('Projects_model');
        
        $projectId = intval($_GET['project_id']);
        optiply_log($projectId, 'webhook_order', file_get_contents('php://input'));

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        //optiply_log($projectId, 'webhook_order', json_encode($content));

        if($content['Action'] != 'Update') {
            return;
        }

        $id = $content['Key'];

        $data = [
            'project_id' => $projectId,
            'order_id' => $id,
            'status' => 0
        ];

        $this->db->insert('exact_order_changes', $data);

        echo 'success';
        return;
    }

    public function importitem() {

        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Optiply_model');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        $projectId = intval($_GET['project_id']);
        optiply_log($projectId, 'test_item', json_encode($content));

        if($content['Action'] != 'Update')
            return;

        $data = [
            'project_id' => $projectId,
            'item_id' => $content['Key'],
            'status' => 0
        ];

        $this->db->insert('exact_item_changes', $data);

        echo 'success';
        return;
    }

    public function stock() {
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Optiply_model');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        $projectId = intval($_GET['project_id']);

        if($content['Action'] != 'Update')
            return;


        $data = [
            'project_id' => $projectId,
            'item_id' => $content['Key'],
            'stock' => 0,
            'status' => 0
        ];

        $this->db->insert('exact_stock_changes', $data);

        echo 'success';
        return;
    }

    public function updateOrderLine()
    {
        $this->load->helper('ExactOnline/vendor/autoload');
        $this->load->model('Optiply_model');
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');

        if(!isset($_GET['project_id']) || file_get_contents('php://input') == '') {
            return;
        }

        $originalContent = file_get_contents('php://input');
        $content = json_decode($originalContent, true)['Content'];

        $projectId = intval($_GET['project_id']);
        optiply_log($projectId, 'webhook_line', json_encode($content));

        $type = 'update';
        if($content['Action'] != 'Update') {
            $type = 'delete';
        }

        $data = [
            'project_id' => $projectId,
            'item_id' => $content['Key'],
            'status' => 0,
            'type' => $type
        ];

        $this->db->insert('exact_order_line_updates', $data);

        echo 'success';
        return;
    }
}