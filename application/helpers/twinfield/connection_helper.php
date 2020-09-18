<?php
/**
 * This helper is used for making sonnection to twinfield.
 * @author manish kumar singh 
 *
 */

    function connectTwinfield($params_twin, $projectId){
        $data = array();
        try {
            $session = new SoapClient("https://login.twinfield.com/webservices/session.asmx?wsdl", array('trace' => 1));
            $result = $session->logon($params_twin);

        } catch (SoapFault $e){
          return false;
        }
        if($result->LogonResult=='Ok'){
            // header
            $cluster = $result->cluster;
            $qq = new domDocument();
            $qq->loadXML($session->__getLastResponse());
            $sessionID = $qq->getElementsByTagName('SessionID')->item(0)->textContent;
            $newurl = $cluster . '/webservices/processxml.asmx?wsdl';

            try {
                $client = new SoapClient($newurl);
                $header = new SoapHeader('http://www.twinfield.com/', 'Header', array('SessionID'=> $sessionID));
                $data['client'] = $client;
                $data['header'] = $header;
                $data['cluster'] = $cluster;
                return $data;
            } catch (SoapFault $e) {
              return false;
            }
        }
        else{
            api2cart_log($projectId, 'exportorders', $result->LogonResult . " Twinfield credentials." );
            return false;
        }
    }

    function AbandonConnection($twin_connection){
        try {
            $session = new SoapClient($twin_connection['cluster'] . "/webservices/session.asmx?wsdl");
            $session->__soapCall('Abandon', array(''), null, $twin_connection['header']);
        } catch (SoapFault $e){
            echo $e->getMessage();
        }
    }
 
 
