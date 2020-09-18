<?php 

if ( ! defined('BASEPATH')) exit('Cheating huh!');

if ( ! class_exists('WhmcsCurlRequest'))
{
    include_once dirname(__FILE__).'/includes/request.php';
}

if ( ! class_exists('WhmcsKnowledgebase'))
{
    
    class WhmcsKnowledgebase{
        
        function __construct($url = false, $api_identifier = false, $api_secret = false) {
            $this->baseApi = new WhmcsCurlRequest($url,$api_identifier,$api_secret);
        }
        
        function get_cats($id=false){
            
            $params = ['action' => 'getkbcats'];
            if( !empty($id) ){
               $params['id'] = $id;
            }
            return $this->baseApi->postMethodRequest($params);
            
        }
        
        function get_cat_articles($cat_id){
            
            if( empty($cat_id) ){
                return ['status'=>0,'message'=>'Catid not found'];
            }
            
            $params = ['action' => 'getkbarticles','catid'=>$cat_id];
            return $this->baseApi->postMethodRequest($params);
            
        }
        
         function get_article($id){
            
            if( empty($id) ){
                return ['status'=>0,'message'=>'Article not found'];
            }
            
            $params = ['action' => 'getkbarticle','id'=>$id];
            return $this->baseApi->postMethodRequest($params);
            
        }
        
         function get_articles_by_keyword($keyword){
            
            if( empty($keyword) ){
                return ['status'=>0,'message'=>'Keyword not found'];
            }
            
            $params = ['action' => 'searchkbarticles','keyword'=>$keyword];
            return $this->baseApi->postMethodRequest($params);
            
        }
        
    }
}