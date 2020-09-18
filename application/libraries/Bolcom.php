<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
 
include_once APPPATH.'/third_party/BolCom/.config.inc.php';

class Bolcom {
 
    public static $rooturl;
    public static $bRaw;
    public static $apiClient = null;
    public static $bolPartnerSiteId;
    public static $params;

    public function __construct($connection_params=null) {
        if($connection_params!=null){
            self::$params                   = $connection_params;
            $bol_api_key                    = $connection_params['bol_api_key'];
            $bol_api_format                 = 'json';
            $bol_api_debug_mode             = 0;
            $bol_api_library_version        = 'v.2.3.0';
            self::$bolPartnerSiteId         = $connection_params['bolPartnerSiteId'];
            self::$apiClient = new BolCom\Client($bol_api_key, $bol_api_format, $bol_api_debug_mode);
            $servername = str_replace("www.", "", $_SERVER['SERVER_NAME']);
            self::$rooturl = 'http://' . $servername . $_SERVER['SCRIPT_NAME'];
        }
    }

    public function ping(){
        $this->__construct(self::$params);
        $ping = self::$apiClient->getPingResponse();
        return json_decode(json_encode($ping),true);
    }

    public function createProduct($products_list){
        $this->__construct(self::$params);
        $ping  = self::$apiClient->upsertOffers($products_list);
        print_r($ping);
    }

}
