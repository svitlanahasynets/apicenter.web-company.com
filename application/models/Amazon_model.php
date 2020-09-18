<?php if(!defined('BASEPATH')) exit('no direct script access allowed');

/**
* @author manish
* @return boolean , xml, json, array
* works as a bridge between amazon and exact
*/
class Amazon_model extends CI_Model{

	public $connection;
    public $not_throttled;

	public function __construct(){
		parent::__construct();
        $this->not_throttled = true;
		$this->load->helpers('tools');
		$this->load->helpers('constants');
	}

	public function mws_connection_params($projectId, $operationType=''){

		$this->load->model('Projects_model');
		$this->db->where('id', $projectId);
		$store_url_value = $this->db->get('projects')->result_array();
		$serviceUrl 								= $store_url_value[0]['store_url'];
		if($operationType=='orders')
			$serviceUrl = $serviceUrl.'/Orders/2013-09-01';
		$connection_params['serviceUrl'] 			= $serviceUrl;
		$connection_params['config']				= array ( 'ServiceURL' => $serviceUrl, 'ProxyHost' => null, 'ProxyPort' => -1, 'MaxErrorRetry' => 3);
		$connection_params['AWS_ACCESS_KEY_ID'] 	= $this->Projects_model->getValue('AWS_ACCESS_KEY_ID', $projectId);
		$connection_params['AWS_SECRET_ACCESS_KEY'] = $this->Projects_model->getValue('AWS_SECRET_ACCESS_KEY', $projectId);
		$connection_params['APPLICATION_NAME'] 		= 'AmazonApicenterExact';
		$connection_params['APPLICATION_VERSION'] 	= '1.0';
		$connection_params['MERCHANT_ID'] 			= $this->Projects_model->getValue('MERCHANT_ID', $projectId);
		$MARKETPLACEID 								= $this->Projects_model->getValue('MARKETPLACEID', $projectId);
		$connection_params['marketplaceIdArray'] 	= array("Id" => array($MARKETPLACEID));
		return $connection_params;
	}
	
	// import exact article into amazon
	public function importProductToAmazon($items, $projectId){
		$connection_params  = $this->mws_connection_params($projectId);
		$this->load->library('Mwsfeeds',$connection_params);

		foreach ($items as $i_key => $i_value) {
			$datei = str_replace('/Date(','',$i_value['StartDate']);
			$datei = str_replace(')/','',$datei);
			$datei = date('Y-m-d\Th:i:s',$datei/1000);

			if(!isset($i_value['Barcode']) && $i_value['Barcode']==''){
				$offset  					= $i_value['Id'];
				continue;
			}
			if($i_value['Stock']<=0){
				$offset  					= $i_value['Id'];
				continue;
			}

			$featureList  			= array();
			if($i_value['notes']!='')
				$featureList 			= explode(PHP_EOL, $i_value['notes']);
			
			$feed = '<?xml version="1.0" encoding="utf-8"?>
						<AmazonEnvelope xsi:noNamespaceSchemaLocation="amzn-envelope.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
						    <Header>
						        <DocumentVersion>1.01</DocumentVersion>
						        <MerchantIdentifier>'.$connection_params['MERCHANT_ID'].'</MerchantIdentifier>
						    </Header>
						    <MessageType>Product</MessageType>
						    <PurgeAndReplace>false</PurgeAndReplace>
						    <Message>
						        <MessageID>1</MessageID>
						        <Product>
						        <SKU>'.$i_value['SKU'].'</SKU>
						        <StandardProductID>
						          <Type>EAN</Type>
						          <Value>'.$i_value['Barcode'].'</Value>
						        </StandardProductID>
						        <LaunchDate>'.$datei.'</LaunchDate>
								<ReleaseDate>'.$datei.'</ReleaseDate>
						        <Condition>
						          <ConditionType>New</ConditionType>
						        </Condition>
						        <ItemPackageQuantity>'.$i_value['Quantity'].'</ItemPackageQuantity>
						        <NumberOfItems>'.$i_value['NumberOfItemsPerUnit'].'</NumberOfItems>
						        <DescriptionData>
						          <Title>'.$i_value['name'].'</Title>
						          <Description>'.$i_value['description'].'</Description>';

								if(!empty($featureList)){
									foreach ($featureList as $f_key => $f_value) {
										if(strlen($f_value)>1 && $f_key<6)
											$feed.= '<BulletPoint>'.$f_value.'</BulletPoint>';
									}
								}
						//$feed.= '<MSRP currency="'.$i_value['currency'].'">'.$i_value['price'].'</MSRP>'; 2012-07-19T00:00:01
						$feed.= '<MSRP currency="USD">'.$i_value['price'].'</MSRP>';
						if($i_value['ItemGroupDescription']!='')
							$feed.= '<ItemType>'.$i_value['ItemGroupDescription'].'</ItemType>';
						$feed.= '</DescriptionData>';

						if($i_value['ItemGroupCode']=='Health'){
							$feed.= '<ProductData>
							            <Health>
							                <ProductType>
									            <HealthMisc>
									              <Ingredients>'.$i_value['ItemGroupDescription'].'</Ingredients>
									            </HealthMisc>
									          </ProductType>
							            </Health>
							        </ProductData>';
						} else{
							$feed.= '<ProductData>
							            <Miscellaneous>
							                <ProductType>Misc_Other</ProductType>
							            </Miscellaneous>
							        </ProductData>';
						}
						$feed.= '</Product>
						    </Message>
						</AmazonEnvelope>';
			$inventryFeed = '<?xml version="1.0" encoding="utf-8" ?>
								<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
								<Header>
								  <DocumentVersion>1.01</DocumentVersion>
								  <MerchantIdentifier>A1EVIEKKK2Q25O</MerchantIdentifier>
								</Header>
								<MessageType>Inventory</MessageType>
								<Message>
								  <MessageID>1</MessageID>
								  <OperationType>Update</OperationType>
								<Inventory>
								  <SKU>'.$i_value['SKU'].'</SKU>
								  <Quantity>'.$i_value['Stock'].'</Quantity>
								  <FulfillmentLatency>1</FulfillmentLatency>
								  </Inventory>
								  </Message>
								</AmazonEnvelope>';
					//<StandardPrice currency="'.$i_value['currency'].'">'.$i_value['price'].'</StandardPrice>
			$pricing = '<?xml version="1.0" encoding="utf-8" ?>
							<AmazonEnvelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
							xsi:noNamespaceSchemaLocation="amzn-envelope.xsd">
							  <Header>
							    <DocumentVersion>1.01</DocumentVersion>
							    <MerchantIdentifier>A1EVIEKKK2Q25O</MerchantIdentifier>
							  </Header>
							   <MessageType>Price</MessageType>
							  <Message>
							    <MessageID>1</MessageID>
							    <Price>
							      <SKU>'.$i_value['SKU'].'</SKU>
							      <StandardPrice currency="USD">'.$i_value['price'].'</StandardPrice>
							    </Price>
							  </Message>
							</AmazonEnvelope>';
			$offset = $i_value['Id'];
			$ean = $i_value['Barcode'];
			$sku = $i_value['SKU'];
			$mwsfeed = $this->mwsfeeds->createFeed($feed);
			if($mwsfeed){
				if($mwsfeed['status']==1){
					$offset  					= $i_value['Id'];
					$data 						= array();
					$data['feedSubmissionId'] 	= $mwsfeed['feedSubmissionId'];
					$data['ean'] 				= $i_value['Barcode'];
					$data['sku'] 				= $i_value['SKU'];
					$this->saveValue($data,$projectId);
				} else{
					if($mwsfeed['statusCode']==503){
						$this->not_throttled = false;
						$data = array(
							'project_id' 	=> $projectId,
							'type' 			=> 'project_setting',
							'code' 			=> 'last_imported_item',
							'value' 		=>  $last_imported_item
						);
						$this->db->insert('project_settings', $data);
						break;
					}
					else{
						api2cart_log($projectId, 'exportorders', 'import product '.$mwsfeed['message'].' from Exact Online.');
						continue;
					}
				}
				if($this->not_throttled){
					$this->createInventry($inventryFeed, $projectId, $ean, $sku);
					$this->createPricing($pricing, $projectId, $ean, $sku);
				}
				$last_imported_item = $i_value['SKU'];
			}
		}

	}

	public function createInventry($inventryFeed, $projectId, $ean, $sku){
		$this->load->library('Mwsfeeds');
		$inventry  = $this->mwsfeeds->createFeedInventry($inventryFeed);
		if($inventry['status']==1){
			$data = array();
			$data['feedSubmissionId'] 	= $inventry['feedSubmissionId'];
			$data['ean'] 				= $ean;
			$data['sku'] 				= $sku;
			$this->saveValue($data,$projectId, $ean, $sku);
		} else{
			if($inventry['statusCode']==503){
				$this->not_throttled = false;
				sleep(250);
				$this->createInventry($inventryFeed, $projectId, $ean, $sku);
			}
			else{
				api2cart_log($projectId, 'exportorders', 'import product '.$inventry['message'].' from Exact Online.');
			}
		}
	}

	public function createPricing($pricing, $projectId, $ean, $sku){
		$this->load->library('Mwsfeeds');
		$pricing   = $this->mwsfeeds->createFeedPricing($pricing);
		if($pricing['status']==1){
			$data = array();
			$data['feedSubmissionId'] 	= $pricing['feedSubmissionId'];
			$data['ean'] 				= $ean;
			$data['sku'] 				= $sku;
			$this->saveValue($data,$projectId, $ean, $sku);
		} else{
			if($pricing['statusCode']==503){
				$this->not_throttled = false;
				sleep(125);
				$this->createPricing($pricing, $projectId);
			}
			else{
				api2cart_log($projectId, 'exportorders', 'import product '.$pricing['message'].' from Exact Online.');
			}
		}
	}

	public function saveValue($data,$projectId){
		
		$this->db->set('project_id', $projectId);
		$this->db->set('sku', $data['sku']);
		$this->db->set('ean', $data['ean']);
		$this->db->set('feedSubmissionId', $data['feedSubmissionId']);
		$this->db->set('created_date', date('Y-m-d h:i:s'));
		$this->db->insert('amazon_exact');

		return true;
	}
	
	public function getValue($projectId){
		$this->db->where('project_id', $projectId);
		$value = $this->db->get('amazon_exact')->result_array();
		return $value;
	}

	public function checkFeed($submited_feed_ids, $projectId){

		$connection_params  = $this->mws_connection_params($projectId);
		$this->load->library('Mwsfeeds',$connection_params);
		foreach ($submited_feed_ids as $feef_key => $feed_value) {
			$this->checkFeedResponse($feed_value, $projectId);
		}
	}

	public function checkFeedResponse($feed_value, $projectId){
		$this->load->library('Mwsfeeds');
		$feed_result 	= $this->mwsfeeds->checkFeedResponse($feed_value);
		if($feed_result['status']==1){
			if($feed_result['resultcode']['0'] == 'Complete'){
				$processingReport 	= $feed_result['result']['Message']['ProcessingReport'];
				if(isset($processingReport['Result'])){
					$rs_value 		= $processingReport['Result'];
					if(isset($rs_value['0'])){
						foreach ($rs_value as $key => $value) {
							if($value['ResultCode']=='Error'){
								api2cart_log($projectId, 'exportorders', 'import product '.$value['ResultDescription'].' from Exact Online.');
							}
						}
					} else{
						if($rs_value['ResultCode']=='Error'){
							api2cart_log($projectId, 'exportorders', 'import product '.$rs_value['ResultDescription'].' from Exact Online.');
						}
					}
				}
				$this->db->where('id', $feed_value['id']);
				$this->db->delete('amazon_exact');
			}
		} else{
			if($feed_result['statusCode']==503){
				sleep(60);
				$this->checkFeedResponse($feed_value, $projectId);
			}
		}
	}
}

?>