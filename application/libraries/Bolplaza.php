<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

include_once APPPATH.'/third_party/BolPlaza/.config.inc.php';

class Bolplaza {

    public static $config_params;
    public static $plazaClient;
        // possible deliveryCodes
    public $deliveryCodes = [
        '24uurs-23',
        '24uurs-22',
        '24uurs-21',
        '24uurs-20',
        '24uurs-19',
        '24uurs-18',
        '24uurs-17',
        '24uurs-16',
        '24uurs-15',
        '24uurs-14',
        '24uurs-13',
        '24uurs-12',
        '1-2d',
        '2-3d',
        '3-5d',
        '4-8d',
        '1-8d'
    ];
        // Available fulfillment methods
    private $fulfilmentMethods = [
        'FBR', 
        'FBB',
        'ALL'
    ];

    //Available artcle conditions
    private $artcleConditions = [
        'NEW', 
        'AS_NEW',
        'GOOD',
        'REASONABLE',
        'MODERATE'
    ];

    public function __construct($config_params = null){
        if($config_params != null){
            self::$config_params = $config_params;
            $config_params = $config_params[0];
            $public_key        = $config_params['public_key'];
            $private_key       = $config_params['private_key'];
            $test              = isset($config_params['test'])?$config_params['test']:false;
            self::$plazaClient = new BolPlaza\Client($public_key, $private_key, $test);
        } else {
            return ['status'=>3,'message'=>'Exception: Either `$publicKey` or `$privateKey` not set'];
        }
    }

    public function upsertOffer($offer_data= array(), $config_params=null){

        $offer_data_xml = $this->convertOfferArrayToXml($offer_data);
        // print_r($offer_data_xml);
        // exit();
        log_message('debug', "Offer data " . var_export($offer_data, true));
        log_message('debug', "Offer data XML" . var_export($offer_data_xml, true));
     	if($config_params==null){
        	$this->__construct(self::$config_params);
       		return self::$plazaClient->upsertOffer($offer_data_xml);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->upsertOffer($offer_data_xml);
        	
        }
    }

    public function getOrders($params = array(), $config_params=null){

     	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->getOrders();
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->getOrders();
        }
    }

    public function convertOfferArrayToXml($data_array = []){
        $xml = array();
        if(!empty($data_array)){
            $xml = '<UpsertRequest xmlns="https://plazaapi.bol.com/offers/xsd/api-2.0.xsd">
                <RetailerOffer>';
            $xml.= '<EAN>'.$data_array['EAN'].'</EAN>';
            $xml.= '<Condition>'.$data_array['Condition'].'</Condition>';
            $xml.= '<Price>'.$data_array['Price'].'</Price>';
            $xml.= '<DeliveryCode>'.$data_array['DeliveryCode'].'</DeliveryCode>';
            $xml.= '<QuantityInStock>'.$data_array['QuantityInStock'].'</QuantityInStock>';
            $xml.= '<Publish>'.$data_array['Publish'].'</Publish>';
            if(isset($data_array['ReferenceCode']))
                $xml.= '<ReferenceCode>'.$data_array['ReferenceCode'].'</ReferenceCode>';
            if(isset($data_array['Description']))
                $xml.= '<Description>'.$data_array['Description'].'</Description>';
            if(isset($data_array['Title']))
                $xml.= '<Title>'.$data_array['Title'].'</Title>';
            $xml.= '<FulfillmentMethod>'.$data_array['FulfillmentMethod'].'</FulfillmentMethod>';
            $xml.= '</RetailerOffer>
            </UpsertRequest>';
        }
        return $xml;
    }

    public function getAllOffers($config_params=null){
    	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->getAllOffers();
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->getAllOffers();
        }
    }

    public function getAllOffersDown($file_name, $config_params=null){
    	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->getAllOffersDown($file_name);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->getAllOffersDown($file_name);
        }
    }

    public function getSingleOrder($orderId, $config_params=null){

    	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->getSingleOrder($orderId);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->getSingleOrder($orderId);
        }
    }

    public function processStatus($process_status_id, $config_params=null){
    	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->processStatus($process_status_id);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->processStatus($process_status_id);
        }
    }

    public function cancelOrder($ext_order_id, $update_date, $config_params=null){

      	$xml = '<Cancellation xmlns="https://plazaapi.bol.com/services/xsd/v2/plazaapi.xsd">
	          	<DateTime>'.date('c',strtotime($update_date)).'</DateTime>
	          	<ReasonCode>REQUESTED_BY_CUSTOMER</ReasonCode>
        	</Cancellation>';

    	if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->cancelOrder($ext_order_id, $xml);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->cancelOrder($ext_order_id, $xml);
        }
    }

    public function getSingleOffers($offer_ean, $queryParams = array(), $config_params=null){
        if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->getSingleOffers($offer_ean, $queryParams);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->getSingleOffers($offer_ean, $queryParams);
        }
    }

    public function shipmentOrder($orderId, $entity_id, $bol_transporters_code, $bol_trackandtrace_code='', $config_params=null){
        $xml = '<ShipmentRequest xmlns="https://plazaapi.bol.com/services/xsd/v2.1/plazaapi.xsd">
            <OrderItemId>'.$orderId.'</OrderItemId>
            <ShipmentReference>'.$entity_id.'</ShipmentReference>
            <Transport>
            <TransporterCode>'.$bol_transporters_code.'</TransporterCode>';
        if($bol_trackandtrace_code!='')
            $xml.='<TrackAndTrace>'.$bol_trackandtrace_code.'</TrackAndTrace>';
        $xml.='</Transport>
        </ShipmentRequest>';
        //print_r($xml);
        if($config_params==null){
	        $this->__construct(self::$config_params);
	        return self::$plazaClient->shipmentOrder($xml);
        } else{
        	$config_params 	= $config_params[0];
            $public_key        	= $config_params['public_key'];
            $private_key       	= $config_params['private_key'];
            $test              	= isset($config_params['test'])?$config_params['test']:false;
            $plazaClient 		= new BolPlaza\Client($public_key, $private_key, $test);
            return $plazaClient->shipmentOrder($xml);
        }
    }
}