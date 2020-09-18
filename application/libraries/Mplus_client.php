<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/mplusApiClient/Mplusqapiclient.php';

class Mplus_client {

    protected $mplusqapiclient;

    public function __construct($params) {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $mplusqapiclient = new Mplusqapiclient();
        $mplusqapiclient->setApiServer($params['api_url']);
        $mplusqapiclient->setApiPort($params['api_port']);
        $mplusqapiclient->setApiIdent($params['api_ident']);
        $mplusqapiclient->setApiSecret($params['api_secret']);

        try {
            $mplusqapiclient->initClient();
            $this->mplusqapiclient = $mplusqapiclient;
        } catch (MplusQAPIException $e) {
            exit($e->getMessage());
        }
    }

    public function testConnect() {
        try {
            $api_version = $this->mplusqapiclient->getApiVersion();
            echo sprintf('Current API version: %d.%d.%d', 
            $api_version['majorNumber'], 
            $api_version['minorNumber'], 
            $api_version['revisionNumber']);
        } catch (MplusQAPIException $e) {
            exit($e->getMessage());
        }
    }

    public function sendOrder($order) {
        // $this->getDelivery();exit;
        // $this->dd($orderData);exit;
        try {
            if (false !== ($order_result = $this->mplusqapiclient->createOrder($order))) {
                return ['success'=>1, 'messages' => 'Created new order with id '. $order_result['orderId']];
            } else {
                return ['success'=>0, 'messages' => 'Failure during order creation.'];
            }
        } catch (MplusQAPIException $e) {
            return ['success'=>0, 'messages' => 'Failure during order creation.' . $e->getMessage()];
        }
    }

    public function getRelations() {
        $relationNumbers = array();
        $syncMarker = 0;
        
        try {
            if (false !== ($relations = $this->mplusqapiclient->getRelations($relationNumbers, $syncMarker))) {
                // Success, we show the amount of found relations
                $this->dd($relations);
                exit(sprintf('Found %d relations.', count($relations)));
            } else {
                exit('Failure while getting relations.');
            }
        } catch (MplusQAPIException $e) {
            exit($e->getMessage());
        }
    }

    protected function getEmployees() {
        $syncMarker = 0;

        try {
            if (false !== ($employees= $this->mplusqapiclient->getEmployees($syncMarker))) {
                // Success, we show the number of retrieved employees.
                $this->dd($employees);
                exit(sprintf('Found %d employees.', count($employees)));
            } else {
                exit('Unable to retrieve employees.');
            }
        } catch (MplusQAPIException $e) {
            exit($e->getMessage());
        }
    }

    protected function getDelivery() {
        // Then we call the getDeliveryMethods() function wrapped in a try/catch block to intercept any exceptions.
        try {
            if (false !== ($deliveryMethods = $this->mplusqapiclient->getDeliveryMethods())) {
                $this->dd($deliveryMethods);
                exit(sprintf('%d delivery methods found.', count($deliveryMethods)));
            } else {
                exit('Unable to retrieve delivery methods.');
            }
        } catch (MplusQAPIException $e) {
            exit($e->getMessage());
        }
    }

    public function findRelation($relation) {
        try {
            if (false !== ($existing_relation = $this->mplusqapiclient->findRelation($relation))) {
              return ['success'=>1, 'messages'=>'Found existing relation with number ' . $existing_relation['relationNumber'], 'response' => $existing_relation];
            } else {
                return ['success'=>0, 'messages'=>'Relation not found. We\'d better create a new one.', 'response' => $existing_relation];
            }
        } catch (MplusQAPIException $e) {
            return ['success'=>0, 'messages'=>$e->getMessage()];
        }
    }

    public function createRelation($relation) {
        try {
            if (false !== ($relation_number = $this->mplusqapiclient->createRelation($relation))) {
                // Success, we show the created relation's number.
                return ['success'=>1, 'messages'=>'Found existing relation with number ' . $relation_number, 'response' => $relation_number];
                // exit(sprintf('Created relation with number %d.', $relation_number));
            } else {
                // Failure, unfortunately something went wrong.
                return ['success'=>0, 'messages'=>'Unable to create relation.'];
                // exit('Unable to create relation.');
            }
        } catch (MplusQAPIException $e) {
            return ['success'=>0, 'messages'=>$e->getMessage()];
            // exit($e->getMessage());
        }
    }

    public function getStockHistory($branchNumber, $articleNumbers=array(), $sinceStockId=null, $fromFinancialDateTime=null,         $throughFinancialDateTime=null, $attempts=0) {
        try {
            if (false !== ($stock_histories = $this->mplusqapiclient->getStockHistory($branchNumber, $articleNumbers, $sinceStockId, $fromFinancialDateTime, $throughFinancialDateTime, $attempts))) {
                // Success, we show how much stock histories we got.
                return ['success'=>1, 'messages'=>'Found stock changes.', 'response' => $stock_histories];
                //exit(sprintf('Found %d stock changes.', count($stock_histories)));
            } else {
                return ['success'=>0, 'messages'=>'Unable to retrieve stock changes.'];
                //exit('Unable to retrieve stock changes.');
            }
        } catch (MplusQAPIException $e) {
            return ['success'=>0, 'messages'=>$e->getMessage()];
            //exit($e->getMessage());
        }
    }

    public function getStock($articleNumbers, $branchNumber, $stockId = null) {
        // Then we call the getStock() function wrapped in a try/catch block to intercept any exceptions.
        try {
            if (false !== ($stock_information = $this->mplusqapiclient->getStock($branchNumber, $articleNumbers, $stockId))) {
                // Success, we show how much stock information we got.
                return ['success'=>1, 'messages'=>'Found stock information for articles.', 'response' => $stock_information];
                //exit(sprintf('Found stock information for %d articles.', count($stock_information)));
            } else {
                return ['success'=>0, 'messages'=>'Unable to retrieve stock information.'];
                //exit('Unable to retrieve stock information.');
            }
        } catch (MplusQAPIException $e) {
            return ['success'=>0, 'messages'=>$e->getMessage()];
            //exit($e->getMessage());
        }
    }

    private function dd($data) {
        echo "<pre>";
        var_dump($data);
        echo "</pre>";
    }

}
