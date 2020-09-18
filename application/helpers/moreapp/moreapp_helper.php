<?php
require __DIR__.'/../vendor/autoload.php';

use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class App
{ 

    private  $endpoint;
    private  $config;
    private  $client;
    private  $params;

    public function __construct($params){
        $this->params       = $params;
        $this->endpoint     = $params['endpoint'];
        $this->config       = [ 'consumer_key' => $params['consumer_key'], 'consumer_secret' => $params['consumer_secret'], 'token_secret' => '' ];
        $stack = HandlerStack::create();
        $middleware = new Oauth1($this->config);
        $stack->push($middleware);
        $this->client = new Client([ 'base_uri' => $this->endpoint, 'handler' => $stack, 'auth' => 'oauth']);
    }

    public function getCustomers()
    {
        $response = $this->client->get('customers');
        $customers = json_decode($response->getBody()->getContents());

        print_r($customers);
    }

    public function PostFilter()
    {
        $response = $this->client->post('customers/900001/folders/c900001a4/forms/2c0f6671443345c881ec50a4a7454814/registrations/filter/0', [
            json => [
                pageSize => 100,
                sort => [],
                idSelection => [],
                query => []
            ]
        ]);

        $registrations = json_decode($response->getBody()->getContents());
        print_r($registrations);
    }

    public function getDatasource()
    {
        $response = $this->client->get('customers/10007/datasources');

        $customers = json_decode($response->getBody()->getContents());

        print_r($customers);

    }
}

// $app->PostFilter();
