<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
 include_once APPPATH.'/third_party/orders/MarketplaceWebServiceOrders/samples/.config.inc.php';

class Mwsorders {
 
    public $serviceUrl;
    public $config;
    public $service;

    public $AWS_ACCESS_KEY_ID;
    public $AWS_SECRET_ACCESS_KEY;
    public $APPLICATION_NAME;
    public $APPLICATION_VERSION;
    public $MERCHANT_ID;
    public $marketplaceIdArray;

    public function __construct($connection_params=null) {
        if($connection_params!=null){
            $this->serviceUrl               = $connection_params['serviceUrl'];
            $this->config                   = $connection_params['config'];
            $this->AWS_ACCESS_KEY_ID        = $connection_params['AWS_ACCESS_KEY_ID'];
            $this->AWS_SECRET_ACCESS_KEY    = $connection_params['AWS_SECRET_ACCESS_KEY'];
            $this->APPLICATION_NAME         = $connection_params['APPLICATION_NAME'];
            $this->APPLICATION_VERSION      = $connection_params['APPLICATION_VERSION'];
            $this->MERCHANT_ID              = $connection_params['MERCHANT_ID'];
            $this->marketplaceIdArray       = $connection_params['marketplaceIdArray'];
        }
    }

    public function listOrders(){

        $service = new MarketplaceWebServiceOrders_Client( $this->AWS_ACCESS_KEY_ID, $this->AWS_SECRET_ACCESS_KEY, $this->APPLICATION_NAME, $this->APPLICATION_VERSION, $this->config );
        $request = new MarketplaceWebServiceOrders_Model_ListOrdersRequest();
        $request->setSellerId($this->MERCHANT_ID);
        $request->setMarketplaceId($this->marketplaceIdArray['Id']['0']);
        $setCreatedAfter = date('Y-m-d', strtotime(date('Y-m-d') . ' -1 day'));
        $setCreatedAfter = $setCreatedAfter.'T00:00:22Z';
        $request->setCreatedAfter($setCreatedAfter);
        $result = $this->invokeListOrders($service, $request);
        return $result;
    }

   
    public function invokeListOrders(MarketplaceWebServiceOrders_Interface $service, $request){ 
        $OrdersResult     = array();
        try {
            $response = $service->ListOrders($request);
            $dom = new DOMDocument();
            $dom->loadXML($response->toXML());
            $dom->preserveWhiteSpace                    = false;
            $dom->formatOutput                          = true;
            $OrdersResult['status']                     = 1;
            $OrdersResult['result']                     = $dom->saveXML();
            $OrdersResult['ResponseHeaderMetadata']     = $response->getResponseHeaderMetadata();
            
        } catch (MarketplaceWebServiceOrders_Exception $ex) {
            $OrdersResult['status']                     = 0;
            $OrdersResult['message']                    = $ex->getMessage();
            $OrdersResult['statusCode']                 = $ex->getStatusCode();
            $OrdersResult['errorCode']                  = $ex->getErrorCode();
            $OrdersResult['errorType']                  = $ex->getErrorType();
            $OrdersResult['requestId']                  = $ex->getRequestId();
            $OrdersResult['XML']                        = $ex->getXML();
            $OrdersResult['responseHeaderMetadata']     = $ex->getResponseHeaderMetadata();
        }
        return $OrdersResult;
    }

}

