<?php

use function GuzzleHttp\json_decode;

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Nopaccelerate {

    protected $url;

    protected $secret_key;

    protected $admin_key;

    protected $path = [
        'get-orders' => '/Api/Admin/ListOrder',
        'get-order' => '/Api/Admin/GetOrderDetail',

    ];

    const METHOD_GET = 'GET';

    const METHOD_PUT = 'PUT';

    const METHOD_POST = 'POST';

    public function __construct(array $params)
    {   
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);

        if (!count($params)) return false;

        $this->url        = $params['url'];
        $this->secret_key = $params['secret_key'];
        $this->admin_key  = $params['admin_key'];
    }

    protected function call($url, $method = self::METHOD_GET, $data = [])
    {
        $ch = curl_init($this->url.$this->path[$url]);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            ['Content-Type: application/json; charset=utf-8']
        );

        return curl_exec($ch);
    }

    public function getOrders($startDay, $endDay) 
    {   
        $saveData = [
            "ApiSecretKey"              => $this->secret_key,
            "RestAPIAdminAccessKey"     => $this->admin_key,
            "StartDate"                 => $startDay,
            "EndDate"                   => $endDay,
            "WarehouseId"               => "0",
            "ProductId"                 => "0",
            "OrderStatusIds"            => [],
            "PaymentStatusIds"          => [],
            "ShippingStatusIds"         => [],
            "VendorId"                  => "0",
            "BillingPhone"              => "",
            "BillingEmail"              => "",
            "BillingLastName"           => "",
            "BillingCountryId"          => "0",
            "PaymentMethodSystemName"   => "",
            "OrderNotes"                => ""
        ];

        $response = $this->call('get-orders', self::METHOD_POST, $saveData);
        $response = (array) json_decode($response);
 
        if (isset($response['Error'])) {
            return $this->prepareErrorResponse($response['Error']);
        }

        return $this->prepareResponse($response);
    }

    public function getOrder($orderId)
    {
        $saveData = [
            "ApiSecretKey" => $this->secret_key,
            "RestAPIAdminAccessKey" => $this->admin_key,
            "OrderId" => $orderId
        ];

        $response = $this->call('get-order', self::METHOD_POST, $saveData);
        $response = (array) json_decode($response);

        if (isset($response['Error'])) {
            return $this->prepareErrorResponse($response['Error']);
        }

        return $this->prepareResponse($response);
    }

    protected function prepareResponse($data)
    {
        $response = [
            'status' => true,
            'data' => isset($data['Data']) ? $data['Data'] : $data,
        ];

        if (isset($data['Total'])) {
            $response['total'] = $data['Total'];
        }

        return $response;
    }

    protected function prepareErrorResponse($data)
    {
        return [
            'status' => false,
            'data' => $data
        ];
    }


}

