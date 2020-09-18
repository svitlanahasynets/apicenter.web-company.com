<?php
class Project4_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 4;
    }
	
	public function getArticleData($articleData, $finalArticleData){
		if(isset($articleData['attributes'])){
			foreach($articleData['attributes'] as $attribute){
				if($attribute['id'] == 'KLEUR'){
					$finalArticleData['custom_attributes']['color'] = array(
						'type' => 'dropdown',
						'value' => isset($attribute['description']) ? $attribute['description'] : false
					);
				}
				if($attribute['id'] == 'LENGTE'){
					$finalArticleData['custom_attributes']['length'] = array(
						'type' => 'text',
						'value' => isset($attribute['value']) ? $attribute['value'] : false
					);
				}
				if($attribute['id'] == 'ENABLED'){
					if($attribute['value'] == '1'){
						$value = true;
					} else {
						$value = false;
					}
					$finalArticleData['enabled'] = $value;
				}
			}
		}
// 		echo '<pre>';print_r($articleData);exit;
		return $finalArticleData;
	}
	
}