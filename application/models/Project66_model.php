<?php
class Project66_model extends CI_Model {

	public $projectId;

    function __construct() {
        parent::__construct();
        $this->projectId = 66;
    }
	
    public function getArticleData($articleData, $finalArticleData){
        
        $finalData = $finalArticleData;
    
        if(isset($articleData['Conditie']) && $articleData['Conditie'] != '' && $articleData['Conditie'] != null) {
            $finalData['custom_attributes']['Conditie'] =$articleData['Conditie'];
        }
        if(isset($articleData['BezorgCode']) && $articleData['BezorgCode'] != '' && $articleData['BezorgCode'] != null) {
            $finalData['custom_attributes']['BezorgCode'] = $articleData['BezorgCode'];
        }
        if(isset($articleData['Publiseren']) && $articleData['Publiseren'] != '' && $articleData['Publiseren'] != null) {
            $finalData['custom_attributes']['Publiseren'] = $articleData['Publiseren'];
        }
        if(isset($articleData['ReferentieCode']) && $articleData['ReferentieCode'] != '' && $articleData['ReferentieCode'] != null) {
            $finalData['custom_attributes']['ReferentieCode'] = $articleData['ReferentieCode'];
        }
        if(isset($articleData['ConditieBeschrijving']) && $articleData['ConditieBeschrijving'] != '' && $articleData['ConditieBeschrijving'] != null) {
            $finalData['custom_attributes']['ConditieBeschrijving'] = $articleData['ConditieBeschrijving'];
        }
        if(isset($articleData['BezorgMethode']) && $articleData['BezorgMethode'] != '' && $articleData['BezorgMethode'] != null) {
            $finalData['custom_attributes']['BezorgMethode'] = $articleData['BezorgMethode'];
        }
        if(isset($articleData['EAN']) && $articleData['EAN'] != '' && $articleData['EAN'] != null) {
            $finalData['custom_attributes']['EAN'] = $articleData['EAN'];
        }
        if(isset($articleData['BOL_Price']) && $articleData['BOL_Price'] != '' && $articleData['BOL_Price'] != null) {
            $finalData['custom_attributes']['BOL_Price'] = $articleData['BOL_Price'];
        }
        if(isset($articleData['BOL_IsBolProduct']) && $articleData['BOL_IsBolProduct'] != '' && $articleData['BOL_IsBolProduct'] != null) {
            $finalData['custom_attributes']['BOL_IsBolProduct'] = $articleData['BOL_IsBolProduct'];
        }
    
        return $finalData;
    }
}

    
    