<?php
  
    defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');

    define('DOCROOTORDERS', APPPATH.'/third_party/orders/MarketplaceWebServiceOrders/');
    include_once DOCROOTORDERS.'Client.php';
    include_once DOCROOTORDERS.'Model/ListOrderItemsRequest.php';
    include_once DOCROOTORDERS.'Model/GetServiceStatusRequest.php';
    include_once DOCROOTORDERS.'Model/GetOrderRequest.php';
    include_once DOCROOTORDERS.'Model/ListOrdersRequest.php';
    
    