<?php

    defined('CONTENT_TYPE_PLAZA') or define('CONTENT_TYPE_PLAZA', 'application/xml');
    defined('DATE_FORMAT_PLAZA') or define('DATE_FORMAT_PLAZA', 'D, d M Y H:i:s T');
    defined('USER_AGENT_PLAZA') or define('USER_AGENT_PLAZA', 'BolPlazaClient apicenter');
   /************************************************************************ 
    * OPTIONAL ON SOME INSTALLATIONS
    *
    * Set include path to root of library, relative to Samples directory.
    * Only needed when running library from local directory.
    * If library is installed in PHP include path, this is not needed
    ***********************************************************************/   
    define('DOCROOTBOLPLAZA', APPPATH.'/third_party/BolPlaza/');
    include_once DOCROOTBOLPLAZA.'Client.php';
    include_once DOCROOTBOLPLAZA.'Request.php';
    include_once DOCROOTBOLPLAZA.'Response.php';