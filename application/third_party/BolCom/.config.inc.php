<?php

    defined('DATE_FORMAT') or define('DATE_FORMAT', 'Y-m-d\TH:i:s\Z');
   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/   
    define('DOCROOTBOLCOM', APPPATH.'/third_party/BolCom/');
    include_once DOCROOTBOLCOM.'Client.php';
    include_once DOCROOTBOLCOM.'Request.php';
    // include_once DOCROOTBOLCOM.'Model/SubmitFeedRequest.php';
    // include_once DOCROOTBOLCOM.'Model/GetFeedSubmissionResultRequest.php';

    //include_once DOCROOT.'Model/GetFeedSubmissionResultRequest.php';
