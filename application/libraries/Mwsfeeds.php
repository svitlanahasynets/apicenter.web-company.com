<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
include_once APPPATH.'/third_party/MarketplaceWebService/samples/.config.inc.php';

class Mwsfeeds {
 
    public $serviceUrl;
    public $config;
    public $service;
    public $AWS_ACCESS_KEY_ID;
    public $AWS_SECRET_ACCESS_KEY;
    public $APPLICATION_NAME;
    public $APPLICATION_VERSION;
    public $MERCHANT_ID;

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

    public function createFeed($feed = null){

        $service = new MarketplaceWebService_Client( $this->AWS_ACCESS_KEY_ID, $this->AWS_SECRET_ACCESS_KEY, $this->config, $this->APPLICATION_NAME, $this->APPLICATION_VERSION);
        if($feed==null)
            return false;

        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->MERCHANT_ID,
            'MarketplaceIdList' => $this->marketplaceIdArray,
            'FeedType' => '_POST_PRODUCT_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
        );
        rewind($feedHandle);
        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        $result = $this->invokeSubmitFeed($service, $request);
        @fclose($feedHandle);
        return $result;
    }

    public function createFeedInventry($feed = null){
        
        $service = new MarketplaceWebService_Client( $this->AWS_ACCESS_KEY_ID, $this->AWS_SECRET_ACCESS_KEY, $this->config, $this->APPLICATION_NAME, $this->APPLICATION_VERSION);
        if($feed==null)
            return false;

        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->MERCHANT_ID,
            'MarketplaceIdList' => $this->marketplaceIdArray,
            'FeedType' => '_POST_INVENTORY_AVAILABILITY_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
        );
        rewind($feedHandle);
        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        $result = $this->invokeSubmitFeed($service, $request);
        @fclose($feedHandle);
        return $result;
    }

    public function createFeedPricing($feed = null){
        
        $service = new MarketplaceWebService_Client( $this->AWS_ACCESS_KEY_ID, $this->AWS_SECRET_ACCESS_KEY, $this->config, $this->APPLICATION_NAME, $this->APPLICATION_VERSION);
        if($feed==null)
            return false;

        $feedHandle = @fopen('php://temp', 'rw+');
        fwrite($feedHandle, $feed);
        rewind($feedHandle);
        $parameters = array (
            'Merchant' => $this->MERCHANT_ID,
            'MarketplaceIdList' => $this->marketplaceIdArray,
            'FeedType' => '_POST_PRODUCT_PRICING_DATA_',
            'FeedContent' => $feedHandle,
            'PurgeAndReplace' => false,
            'ContentMd5' => base64_encode(md5(stream_get_contents($feedHandle), true)),
        );
        rewind($feedHandle);
        $request = new MarketplaceWebService_Model_SubmitFeedRequest($parameters);
        $result = $this->invokeSubmitFeed($service, $request);
        @fclose($feedHandle);
        return $result;
    }

    public function invokeSubmitFeed(MarketplaceWebService_Interface $service, $request) {
        $feedResult         = array();
        try {
            $response = $service->submitFeed($request);
            $feedResult['status']  = 1;
            if ($response->isSetSubmitFeedResult()) { 
                $submitFeedResult   = $response->getSubmitFeedResult();
                if ($submitFeedResult->isSetFeedSubmissionInfo()) { 
                    $feedSubmissionInfo = $submitFeedResult->getFeedSubmissionInfo();
                    if ($feedSubmissionInfo->isSetFeedSubmissionId()) {
                        $feedResult['feedSubmissionId'] = $feedSubmissionInfo->getFeedSubmissionId();
                    }
                    if ($feedSubmissionInfo->isSetFeedType()) {
                        $feedResult['feedType'] = $feedSubmissionInfo->getFeedType();
                    }
                    if ($feedSubmissionInfo->isSetSubmittedDate()) {
                        $feedResult['submittedDate'] = $feedSubmissionInfo->getSubmittedDate()->format(DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetFeedProcessingStatus()) {
                        $feedResult['feedProcessingStatus'] = $feedSubmissionInfo->getFeedProcessingStatus();
                    }
                    if ($feedSubmissionInfo->isSetStartedProcessingDate()) {
                        $feedResult['startedProcessingDate'] = $feedSubmissionInfo->getStartedProcessingDate()->format(DATE_FORMAT);
                    }
                    if ($feedSubmissionInfo->isSetCompletedProcessingDate()) {
                        $feedResult['completedProcessingDate'] = $feedSubmissionInfo->getCompletedProcessingDate()->format(DATE_FORMAT);
                    }
                } 
            } 
            if ($response->isSetResponseMetadata()) { 
                $responseMetadata = $response->getResponseMetadata();
                if ($responseMetadata->isSetRequestId()) {
                    $feedResult['requestId'] = $responseMetadata->getRequestId();
                }
            }
            $feedResult['responseHeaderMetadata']           = $response->getResponseHeaderMetadata();
        } catch (MarketplaceWebService_Exception $ex) {
            $feedResult['status']           = 0;
            $feedResult['message']       = $ex->getMessage();
            $feedResult['statusCode']    = $ex->getStatusCode();
            $feedResult['errorCode']     = $ex->getErrorCode();
            $feedResult['errorType']     = $ex->getErrorType();
            $feedResult['requestId']     = $ex->getRequestId();
            $feedResult['XML']           = $ex->getXML();
            $feedResult['responseHeaderMetadata']           = $ex->getResponseHeaderMetadata();
        }
        return $feedResult;
    }

    public function checkFeedResponse($mwsFeed){
        $service = new MarketplaceWebService_Client( $this->AWS_ACCESS_KEY_ID, $this->AWS_SECRET_ACCESS_KEY, $this->config, $this->APPLICATION_NAME, $this->APPLICATION_VERSION);
        $request = new MarketplaceWebService_Model_GetFeedSubmissionResultRequest();
        $request->setMerchant($this->MERCHANT_ID);
        $request->setFeedSubmissionId($mwsFeed['feedSubmissionId']);
        $handle = fopen(__DIR__.'/file.xml', 'w+');
        $request->setFeedSubmissionResult($handle);
        $result = $this->invokeGetFeedSubmissionResult($service, $request,$mwsFeed);
        fclose($handle);
        @unlink(__DIR__.'/file.xml');
        return $result;
    }

    public function invokeGetFeedSubmissionResult(MarketplaceWebService_Interface $service, $request, $mwsFeed){

        $feedResult                 = array();
        try {               
            $result                 = $service->getFeedSubmissionResult($request);
            $feedResult['status']   = 1;
            $tempFile               = __DIR__.'/file.xml';
            $response               = file_get_contents($tempFile);

            $xml                    = new SimpleXMLElement($response);
            $result                 = new StdClass();
            $result->report         = $xml->Message->ProcessingReport;
            $result->summary        = $result->report->ProcessingSummary;
            $feedResult['feedSubmissionId']   = $mwsFeed['feedSubmissionId'];

            $json = json_encode($xml);
            $array = json_decode($json,TRUE);
            $feedResult['result']   = $array;

            if(isset($result->report)){
                foreach ($result->report as $item){
                    $feedResult['resultcode']   = $item->StatusCode;
                    $feedResult['DocumentTransactionID'] = $item->DocumentTransactionID;
                }
            }

        } catch (MarketplaceWebService_Exception $ex) {
            $feedResult['status']           = 0;
            $feedResult['message']       = $ex->getMessage();
            $feedResult['statusCode']    = $ex->getStatusCode();
            $feedResult['errorCode']     = $ex->getErrorCode();
            $feedResult['errorType']     = $ex->getErrorType();
            $feedResult['requestId']     = $ex->getRequestId();
            $feedResult['XML']           = $ex->getXML();
            $feedResult['responseHeaderMetadata']           = $ex->getResponseHeaderMetadata();
        }
        return $feedResult;
    }
}
