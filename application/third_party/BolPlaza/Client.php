<?php

namespace BolPlaza;

class Client
{
    private $requestHelper;
    private $fullResponse;

     // available endpoints
    public $endPoints = [
        'orders' => '/services/rest/orders/v2',
        'shipments' => '/services/rest/shipments/v2',
        'returns' => '/services/rest/return-items/v2/unhandled',
        'cancellations' => '/services/rest/order-items/v2/:id/cancellation',
        'process-status' => '/services/rest/orders/v2/process/:id',
        'shipping-status' => '/services/rest/process-status/v2/:id',
        'shipping-label' => '/services/rest/transports/v2/:transportId/shipping-label/:labelId', 
        'shipping-labels' => '/services/rest/purchasable-shipping-labels/v2?orderItemId=:id',     
        'invoices_list' => '/services/rest/invoices',
        'payments' => '/services/rest/payments/v2/:month',
        'offers-export' => '/offers/v1/export',
        'offer-stock' => '/offers/v1/:id/stock',
        'offer-update' => '/offers/v1/:id',
        'offer-delete' => '/offers/v1/:id',
        'offer-create' => '/offers/v1/:id',
        'offers-export-v2' => '/offers/v2/export',
        'get-single-offer' => '/offers/v2/',
        'upsert-offer' => '/offers/v2/',
    ];

    public function __construct($public_key = NULL, $private_key = NULL, $debugMode = false){
        $this->requestHelper = new Request($public_key, $private_key, $debugMode);
    }

    public function getOrders($queryParams = array()){
        $endpoints      = $this->endPoints['orders'];
        if (!isset($queryParams['fulfilment-method' ])) {
            $queryParams['page' ] = 1;
            $queryParams['fulfilment-method'] = "FBB";
        }
        $httpResponse   = $this->requestHelper->request('GET', $endpoints, '', $queryParams);
        log_message('debug', 'Bol dot com loggg' . var_export($httpResponse, true));
        return $httpResponse;
    }

    public function getSingleOffers($offer_ean = null, $queryParams = array()){
        $endpoints      = $this->endPoints['get-single-offer'].$offer_ean;
        $httpResponse   = $this->requestHelper->request('GET', $endpoints, '', $queryParams);
        return $httpResponse;
    }

    public function upsertOffer($offer_data = []){
        $endpoints = $this->endPoints['upsert-offer'];
        $httpResponse = $this->requestHelper->request('PUT', $endpoints, $offer_data, '');
        return $httpResponse;
    } 

    public function getAllOffers(){
        $endpoints = $this->endPoints['offers-export-v2'];
        $httpResponse = $this->requestHelper->request('GET', $endpoints, '', '');
        return $httpResponse;
    } 

    public function getAllOffersDown($file_name=''){
        $endpoints = $this->endPoints['offers-export-v2'].'/'.$file_name;
        $httpResponse = $this->requestHelper->request('GET', $endpoints, '', '',"Accept: text/csv");
        return $httpResponse;
    }

    public function getSingleOrder($orderId){
        $endpoints = $this->endPoints['orders'];
        $endpoints = $endpoints.'/'.$orderId;
        $httpResponse = $this->requestHelper->request('GET', $endpoints, '', '',"Accept: application/vnd.orders-v2.1+xml");
        return $httpResponse;
    }

    public function cancelOrder($ext_order_id, $xml){
        $endpoints = $this->endPoints['cancellations'];
        $endpoints = str_replace(':id', $ext_order_id, $endpoints);
        $httpResponse = $this->requestHelper->request('PUT', $endpoints, $xml, '');
        return $httpResponse;
    }

    public function shipmentOrder($offer_data_xml){
        $endpoints = $this->endPoints['shipments'];
        $httpResponse = $this->requestHelper->request('POST', $endpoints, $offer_data_xml, '',"Accept: application/vnd.shipments-v2.1+xml");
        return $httpResponse;
    }

    public function processStatus($processStatusId){
        $endpoints = $this->endPoints['shipping-status'];
        $endpoints = str_replace(':id', $processStatusId, $endpoints);
        $httpResponse = $this->requestHelper->request('GET', $endpoints, '', '');
        return $httpResponse;
    }

}
