<?php


class UpdateExactBuyOrders extends CI_Controller
{
    public function update($projectId) {
        set_time_limit(0);
        error_reporting(-1);
        ini_set('display_errors', 1);
        $this->load->model('Projects_model');

        $erp = $this->Projects_model->getValue('exactonline_base_url', $projectId);
        if($erp == '') {
            return;
        }
        
        //Check if cron is working and wait until it isn't
        $isRunning = $this->Projects_model->getValue('is_running', $projectId);
        if($isRunning == '2') {
            echo 'isRunning 2';
            return;
        }

        while ($isRunning != '0') {
            echo 'waiting...';
            sleep(30);
            $isRunning = $this->Projects_model->getValue('is_running', $projectId);
        }
        //Stop cron auto start
        $this->Projects_model->saveValue('is_running', '2', $projectId);

        //Run Update process
        $this->updateProcess($projectId);
    }

    public function updateProcess($projectId) {

        //Load models and helpers
        $this->load->model('Projects_model');
        $this->load->model('Exactonline_model');
        $this->load->model('Optiply_model');
        $this->load->helper('tools_helper');
        $this->load->helper('constants_helper');
        $this->load->helper('exactonline/vendor/autoload_helper');

        api2cart_log($projectId, 'reimport_orders', 'Orders update started');

        $this->Exactonline_model->setData(
            array(
                'projectId' => $projectId,
                'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', $projectId).'/?project_id='.$projectId,
                'clientId' => $this->Projects_model->getValue('exactonline_client_id', $projectId),
                'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', $projectId),
            )
        );
        $connection = $this->Exactonline_model->makeConnection($projectId);
        if(!$connection){
            $this->Projects_model->saveValue('is_running', '0', $projectId);
            api2cart_log($projectId, 'reimport_orders', 'Connection not live');
            die('stoped');
            return;
        }

        $token = $this->Optiply_model->getAccesToken($projectId);

        $openOptOrders = $this->Optiply_model->getAllBuyOrdersforCheck($token, '1', true);
        optiply_log($projectId, 'all_orders_to_check', json_encode($openOptOrders));

        $sumOfchecked = $updated = 0;
        foreach ($openOptOrders as $order) {
            $exactId = $this->db
                ->where('optiply_id', $order['id'])
                ->where('project_id', $projectId)
                ->get('optiply_orders')
                ->result_array();

            if(isset($exactId[0]['order_id'])) {
                $status = $this->Exactonline_model->getBuyOrder($connection, $exactId[0]['order_id'])['status'];
                $sumOfchecked++;
                if($status == '10') {
                    continue;
                }

                $result = $this->Optiply_model->updateStatusOrder($token, $order['id']);

                if($result) {
                    $updated++;
                }
            }
        }

        $exactOrders = $this->Exactonline_model->getOpenedBuyOrders($connection);
        exact_log($projectId, 'opened_orders_to_reimport', json_encode($exactOrders));
        $imported = $this->Optiply_model->updateBuyOrders($projectId, $exactOrders, '1');

        api2cart_log($projectId, 'reimport_orders', 'Summary: '.$sumOfchecked.' Optiply orders checked and '.
            $updated.' orders updated. Count of imported orders is '.$imported);

        $this->Projects_model->saveValue('is_running', '0', $projectId);
    }
}