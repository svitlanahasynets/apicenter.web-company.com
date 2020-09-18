<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/opencartSDK/classes.php';

class Opencart_restapi 
{
    protected $username;

    protected $key;

    protected $base_url;

    protected $client = false;

    public function __construct($params) {
        if (!isset($params['username']) || !isset($params['key']) 
        || !isset($params['url']) || !isset($params['login']) || !isset($params['password'])) {
            return false;
        }
        $this->username = $params['username'];
        $this->key      = $params['key'];
        $this->base_url = $params['url'];
        $this->login    = $params['login'];
        $this->password = $params['password'];
        $this->initClient();
    }

    public function initClient() {
        $clientId       = $this->username;
        $clientSecret   = $this->key;
        $apiURL         = $this->base_url;
        $basicToken = base64_encode($clientId.":".$clientSecret);

        $OpenCartRestClient = new OpenCartRestApi($apiURL);

        if(!isset($_SESSION['bearerToken'])){
            $token = $OpenCartRestClient->customer->getToken($basicToken);
            $token = isset($token["data"]) && isset($token["data"]["access_token"]) ? $token["data"]["access_token"] : false;
            $_SESSION['bearerToken'] = $token;
        }
        if(isset($_SESSION['bearerToken']) && !empty($_SESSION['bearerToken'])) {
            $email = $this->login;
            $password = $this->password;
            $OpenCartRestClient->customer->login($email, $password);

            $this->client = $OpenCartRestClient;
        }
    }

    public function getOrders() {
        if (!$this->client) return false;
     
        $orders = $this->client->order->getOrders();
        if (isset($orders['success']) && !empty($orders['data'])) {
            return $orders['data'];
        }
    }

    public function getOrder($orderId) {
        $response = $this->client->order->getOrderById($orderId);
        if (isset($response['success']) && !empty($response['data'])) { 
            return $response['data'];
        }

        return false;
    }

    public function getProductBySku($params) {
        $response = $this->client->product->getProductBySku($params);

        if (isset($response['success']) && !empty($response['data'])) { 
            return $response['data'];
        }

        return false;
    }

    public function updateProductQuantity($productId, $qty) {
        $response = $this->client->product->updateProductQuantity($productId, $qty);

        if ($response['success']) {
            return true;
        }
    }

}
