<?php

class WhmcsCurlRequest {

    private $curl_handle;
    private $apiBaseUrl;
    private $method;
    private $request_body;
    private $api_identifier;
    private $api_secret;
    private $responseBody;
    private $responseStatus;
   
    public function __construct( $url = false, $api_identifier = false, $api_secret = false) {

        $this->method = "GET";
        $this->request_body = "";
        $this->contentType = "multipart/form-data";
        $this->response_type = 'json';
        $this->request_url = $url;
        
        $this->api_identifier = $api_identifier;
        $this->api_secret = $api_secret;

    }

    public function setRequestBody($request_body) {
        $this->request_body = $request_body;
    }
    
    public function setMethod($method) {
        $this->method = $method;
    }
    
    //make post request
    public function postMethodRequest(array $requestData,$method = 'POST'){
		
                $requestData['identifier'] = $this->api_identifier;
                $requestData['secret'] = $this->api_secret;
                $requestData['responsetype'] = $this->response_type;
                
		$this->setRequestBody($requestData);
		$this->setMethod($method);
		$result = $this->execute();
                
                if($result['0']=='200'){

                        $data = json_decode($result['1'],true);
                        $response = ['status' => $result['0'], 'data' => $data];

                }else{
                    
                        $data = json_decode($result['1'],true);
                        $message = isset($data['message']) ? trim($data['message']) : 'Connection problem';
                        $response = ['status' => $result['0'], 'message' => $message];
                        
                }
                
                return $response;

    }
    
    private function setCurlOption() {
        
        curl_setopt($this->curl_handle, CURLOPT_TIMEOUT, 10);
        curl_setopt($this->curl_handle, CURLOPT_URL, $this->request_url);
        
        $headers = array("Content-Type: " . $this->contentType,);
        
        curl_setopt($this->curl_handle, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($this->curl_handle, CURLOPT_RETURNTRANSFER, true);

    }

    private function invoke() {

        $this->responseBody = curl_exec($this->curl_handle);
        $this->responseStatus = curl_getinfo($this->curl_handle, CURLINFO_HTTP_CODE);

    }

    public function execute() {

        $this->curl_handle = curl_init();
        $this->setCurlOption();

        switch (strtoupper($this->method)) {

            case "GET":
                $this->setGet();
                break;

            case "POST":
                $this->setPost();
                break;

            case "PUT":
                $this->setPut();
                break;

            case "DELETE":
                $this->setDelete();
                break;

            default:
                $this->setGet();
                break;

        }

        $this->invoke();
        curl_close($this->curl_handle);
        $result = array($this->responseStatus, $this->responseBody);
        return $result;

    }

    private function setPost() {

        curl_setopt($this->curl_handle, CURLOPT_POST, true);
        curl_setopt($this->curl_handle, CURLOPT_POSTFIELDS, $this->request_body);

    }

}