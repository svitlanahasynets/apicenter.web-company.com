<?php

    defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/   
    set_include_path("application/third_party/"); 

    define('DOCROOT', APPPATH.'/third_party/MarketplaceWebService/');
    include_once DOCROOT.'Client.php';
    include_once DOCROOT.'Model/SubmitFeedRequest.php';
    include_once DOCROOT.'Model/GetFeedSubmissionResultRequest.php';

    //include_once DOCROOT.'Model/GetFeedSubmissionResultRequest.php';
