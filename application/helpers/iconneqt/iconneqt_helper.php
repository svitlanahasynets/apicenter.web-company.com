<?php
require __DIR__.'/../vendor/autoload.php';


if(!function_exists('get_customers')) {
    function get_customers($projectId) {
        $user = get_instance()->Projects_model->getValue('iconneqt_user', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_user', $projectId) : '';
        $password = get_instance()->Projects_model->getValue('iconneqt_password', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_password',    $projectId) : '';
        $url  = get_instance()->Projects_model->getValue('iconneqt_url', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_url',    $projectId) : '';

        if($user == '' || $password == '' || $url == '') {
            return ['message'=>'Exception: Either `$user` or `$password` or not `$url` set'];
        }

        $iconneqt = new Iconneqt\Api\Rest\Client\Client($url, $user, $password);
    }
}

if(!function_exists('check_customer')){ 
    function check_customer($projectId, $email) {
        $user = get_instance()->Projects_model->getValue('iconneqt_user', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_user', $projectId) : '';
        $password = get_instance()->Projects_model->getValue('iconneqt_password', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_password',    $projectId) : '';
        $url  = get_instance()->Projects_model->getValue('iconneqt_url', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_url',    $projectId) : '';
        $listId  = get_instance()->Projects_model->getValue('iconneqt_list_id', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_list_id',    $projectId) : '';

        if($user == '' || $password == '' || $url == '') {
            return ['message'=>'Exception: Either `$user` or `$password` or not `$url` set'];
        }

        $iconneqt =  new Iconneqt\Api\Rest\Iconneqt($url, $user, $password);

        if ($email) {
            try {
                $list = $iconneqt->getList($listId);
                if ($list->hasSubscriber($email)) {
                    return true;
                } else {
                    return false;
                }
            } catch (Exception $e) {
                return ['message' => $e->getMessage()];
            }
        }
    }
}

if(!function_exists('create_customer')) {
    function create_customer($projectId, $customerData) {
        $user = get_instance()->Projects_model->getValue('iconneqt_user', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_user', $projectId) : '';
        $password = get_instance()->Projects_model->getValue('iconneqt_password', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_password',    $projectId) : '';
        $url  = get_instance()->Projects_model->getValue('iconneqt_url', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_url',    $projectId) : '';
        $listId  = get_instance()->Projects_model->getValue('iconneqt_list_id', $projectId) 
            ? get_instance()->Projects_model->getValue('iconneqt_list_id',    $projectId) : '';


        if($user == '' || $password == '' || $url == '') {
            return ['message'=>'Exception: Either `$user` or `$password` or not `$url` set'];
        }

        $iconneqt = new Iconneqt\Api\Rest\Iconneqt($url, $user, $password);

        try {
            $list = $iconneqt->getList($listId);
            $subscriber = $list->addSubscriber($customerData['email'], true, $customerData['fields']);

            return $subscriber->getId();
        } catch (Exception $e) {
            return ['message' => $e->getCode() .", ".$e->getMessage()];
        }
    }
}
