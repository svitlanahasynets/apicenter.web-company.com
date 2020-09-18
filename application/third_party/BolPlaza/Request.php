<?php

namespace BolPlaza;

class Request
{
    private $public_key;
    private $private_key;
    private $debugMode;
    private $url;
    private $content_type;
    protected $ch;
    protected $request_url;
    protected $request_headers;
    protected $responseHeaders;
    public function __construct($public_key, $private_key, $debugMode)
    {
        try {
            $this->public_key   = $public_key;
            $this->private_key  = $private_key;
            $this->debugMode    = (bool) $debugMode;
            $this->content_type = CONTENT_TYPE_PLAZA;
            if ($this->debugMode) {
                $this->url = 'https://test-plazaapi.bol.com';   
            } else {
                $this->url = 'https://plazaapi.bol.com';  
            }
        } catch (Exception $e) {
            echo "Exception: " . $e->getMessage() . "\n";
            return "Exception: " . $e->getMessage() . "\n";
        }
    }

    protected function setDefaultCurlSettings()
    {
        \curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, $this->getRequestHeaders());
        \curl_setopt($this->ch, CURLOPT_URL, $this->getRequestUrl());
    }

    protected function setRequestHeaders($headers_data){
        $this->request_headers = $headers_data;
    }

    protected function getRequestHeaders(){
        return $this->request_headers;
    }

    protected function setRequestUrl($url){
        $this->request_url = $url;
    }

    protected function getRequestUrl(){
        return $this->request_url;
    }

    protected function buildUrlQuery($url, $parameters = []){
        if (!empty($parameters)) {
            $url .= '?' . \http_build_query($parameters);
        }
        return $url;
    }
    protected function setPostFields($body){
        \curl_setopt($this->ch, CURLOPT_POSTFIELDS, $body);
    }

    public function makeRequest($http_verb, $endPoint, $data = [], $parameters = [], $custom_headers= ''){
        $date       = gmdate('D, d M Y H:i:s T');
        $http_verb  = strtoupper($http_verb);
        $body       = '';
        $url        = $this->url . $endPoint;
        $hasData    = !empty($data);
        // create signature.
        $signature = $this->signature($http_verb, $endPoint, $date);
        // // Setup method.
        $this->setupMethod($http_verb);
        // set post fields.
        if ($hasData) {
            $this->setPostFields($data);
        } 
        if($custom_headers!='')
            $headers_data = array ( "Content-type:" . $this->content_type, "X-BOL-Date:" . $date,  "X-BOL-Authorization:" . $signature, $custom_headers);
        else
            $headers_data = array ( "Content-type:" . $this->content_type, "X-BOL-Date:" . $date,  "X-BOL-Authorization:" . $signature);
        $this->setRequestUrl($this->buildUrlQuery($url, $parameters));
        $this->setRequestHeaders($headers_data);

    }

    public function signature($http_verb, $endPoint, $date){
        $signatureString  = $http_verb . "\n\n"; 
        $signatureString .= $this->content_type . "\n"; 
        $signatureString .= $date."\n"; 
        $signatureString .= "x-bol-date:" . $date . "\n";
        $signatureString .= $endPoint;
        $signature = $this->public_key . ':' . base64_encode (hash_hmac ('SHA256', $signatureString, $this->private_key, true));
        return $signature;
    }

    protected function setupMethod($method){
        if ('POST' == $method) {
            \curl_setopt($this->ch, CURLOPT_POST, true);
        } elseif (\in_array($method, ['PUT', 'DELETE', 'OPTIONS'])) {
            \curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, $method);
        } else {
            \curl_setopt($this->ch, CURLOPT_POST, false);
        }
    }

    protected function createResponse(){
        // Set response headers.
        $this->responseHeaders = '';
        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, function ($_, $headers) {
            $this->responseHeaders .= $headers;
            return \strlen($headers);
        });
        // Get response data.
        $body    = \curl_exec($this->ch);
        $code    = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $doc     = @simplexml_load_string($body);
        if ($doc) {
            $json = json_encode($doc); 
            $body = json_decode($json,TRUE);
        } 
        return $body;
    }

    public function request($http_verb, $endPoint, $data = [], $parameters = [], $custom_headers=''){
        if (!function_exists('curl_init') || !function_exists('curl_setopt')) {
            // custom response status 3
            return ['status'=>3,'message'=>"cURL support is required, but can't be found."];
        }
        // Initialize cURL.
        $this->ch = \curl_init();
        // Set request args.
        $request = $this->makeRequest($http_verb, $endPoint, $data, $parameters, $custom_headers);
        // // Default cURL settings.
        $this->setDefaultCurlSettings();
        // // Get response.
        $response = $this->createResponse();
        // Check for cURL errors.
        if (curl_errno ($this->ch)) {
            return curl_errno ($this->ch);
        } else{
            $code    = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
            return ['code'=>$code,'result'=>$response];
        }
        \curl_close($this->ch);
    }

}
