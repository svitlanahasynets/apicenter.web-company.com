<?php
class Project132_model extends CI_Model {

	public $projectId;

    public function __construct()
    {
        parent::__construct();
        $this->projectId = 132;
    }

	public function xml2array( $xmlObject, $out = array () ){
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	}
	
	public function loadCustomOrderAttributes($appendItem, $order, $projectId){
		if(isset($order['extension_attributes']['advisor_number'])){
			$appendItem['customer']['advisor_number'] = $order['extension_attributes']['advisor_number'];
		}
		if(isset($order['customer_group_id'])){
			$appendItem['customer']['customer_group_id'] = $order['customer_group_id'];
		}
		return $appendItem;
	}

	public function setOrderParams($orderData, $saveData){
		if(isset($orderData['customer']['advisor_number']) && $orderData['customer']['advisor_number'] != ''){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'EMP_NR',
				'FieldType' => 'C'
			);
			$saveData['TableData']['Data']['Rows'][0]['Values'][] = $orderData['customer']['advisor_number'];
		}

		if(isset($orderData['comment']) && $orderData['comment'] != ''){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'COMMENT2',
				'FieldType' => 'C'
			);
			$saveData['TableData']['Data']['Rows'][0]['Values'][] = $orderData['comment'];
		}

		if(isset($orderData['id']) && $orderData['id'] != ''){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'A8_INV_NR',
				'FieldType' => 'C'
			);
			$saveData['TableData']['Data']['Rows'][0]['Values'][] = $orderData['id'];
		}

		if(isset($orderData['customer']['customer_group_id']) && $orderData['customer']['customer_group_id'] != ''){
			$list = '';
			if($orderData['customer']['customer_group_id'] == 1){
				$list = 'MKB';
			} elseif($orderData['customer']['customer_group_id'] == 2){
				$list = 'HOR';
			}
			if($list != ''){
				$saveData['Table']['Definition']['Fields'][] = array(
					'name' => 'APX_LIST',
					'FieldType' => 'C'
				);
				$saveData['TableData']['Data']['Rows'][0]['Values'][] = $list;
			}
		}
		return $saveData;
	}
	
	public function createAccountviewCustomerBeforeSave($customerData, $saveData){
		// Customer group
		// DEZE WEER INSCHAKELEN
		if(isset($customerData['customer_group_id']) && $customerData['customer_group_id'] != ''){
			$list = '';
			if($customerData['customer_group_id'] == 1){
				$list = 'MKB';
			} elseif($customerData['customer_group_id'] == 2){
				$list = 'HOR';
			}
			if($list != ''){
				$saveData['Table']['Definition']['Fields'][] = array(
					'name' => 'APX_LIST',
					'FieldType' => 'C'
				);
				$saveData['TableData']['Data']['Rows'][0]['values'][] = $list;
			}
		}
		
		// Advisor number
		if(isset($customerData['advisor_number']) && $customerData['advisor_number'] != ''){
			$saveData['Table']['Definition']['Fields'][] = array(
				'name' => 'EMP_NR',
				'FieldType' => 'C'
			);
			$saveData['TableData']['Data']['Rows'][0]['values'][] = $customerData['advisor_number'];
		}
		return $saveData;
	}

}