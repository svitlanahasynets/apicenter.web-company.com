<?php
use Vleks\BolPlazaSDK\Client;
use Vleks\BolPlazaSDK\ClientException;

// Autoload composer installed libraries
require __DIR__ . '/../vendor/autoload.php';

if(!function_exists('get_orders')){ 
    function get_orders($projectId, $debug = false, $queryParams = []) {
        get_instance()->load->model('Projects_model');
        $public_key  = get_instance()->Projects_model->getValue('bol_public_key', $projectId) 
            ? get_instance()->Projects_model->getValue('bol_public_key', $projectId)  :'';
        $private_key = get_instance()->Projects_model->getValue('bol_private_key', $projectId) 
            ? get_instance()->Projects_model->getValue('bol_private_key',    $projectId):'';
        
        if($public_key == '' || $private_key== ''){
            return ['message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }

        $bolPlazaClient = new Client($public_key, $private_key);
        $bolPlazaClient->setTestMode($debug);
        $result = [];
        try {
            $fbbOrders = $bolPlazaClient->getOrders(1, 'FBB');
            $fbrOrders = $bolPlazaClient->getOrders(1, 'FBR');

            if (count($fbbOrders->get('Order')) && count($fbrOrders->get('Order'))) {
                $result = array_merge($fbbOrders->get('Order'), $fbrOrders->get('Order'));
            } elseif(count($fbbOrders->get('Order'))) {
                $result = $fbbOrders->get('Order');
            } elseif (count($fbrOrders->get('Order'))) {
                $result = $fbbOrders->get('Order');
            }

            return $result;
        } catch (ClientException $clientException) {
            log_message('ERROR', 'Project  = '. $projectId . ' An error occurred: ' . $clientException->getMessage());
        }
    }
}