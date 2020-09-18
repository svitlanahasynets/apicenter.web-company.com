<?php
class Afas_common_model extends CI_Model {

	public $orders_import_error = 0;
	public $orders_import_success = 0;
	public $afas_setup_error = 0;
	public $invoice_import_error = 0;
	public $invoice_import_success = 0;


    function __construct(){
        parent::__construct();
    }
    
	function xml2array ( $xmlObject, $out = array () ){
		foreach ( (array) $xmlObject as $index => $node )
			$out[$index] = ( is_object ( $node ) ) ? $this->xml2array ( $node ) : $node;
		
		return $out;
	} 
	// afas--
	function afasCountryCode($country_code){
		$countryAfasCodes = array("AT" => "A", "AE" => "AE", "AF" => "AFG", "AG" => "AG", "AI" => "AIA", "AL" => "AL", "AM" => "AM", "AO" => "AN", "AD" => "AND", "SA" => "AS", "AS" => "ASM", "AQ" => "ATA", "TF" => "ATF", "AU" => "AUS", "AW" => "AW", "AX" => "AX", "AZ" => "AZ", "BE" => "B", "BA" => "BA", "BD" => "BD", "BB" => "BDS", "BG" => "BG", "BZ" => "BH", "BL" => "BL", "BM" => "BM", "BO" => "BOL", "BQ" => "BQ", "BR" => "BR", "BH" => "BRN", "BN" => "BRU", "BS" => "BS", "BT" => "BT", "BF" => "BU", "MM" => "BUR", "BV" => "BVT", "BY" => "BY", "CU" => "C", "CC" => "CCK", "CA" => "CDN", "CH" => "CH", "CI" => "CI", "LK" => "CL", "CN" => "CN", "CO" => "CO", "CK" => "COK", "CR" => "CR", "CV" => "CV", "CW" => "CW", "CX" => "CXR", "CY" => "CY", "KY" => "CYM", "CZ" => "CZ", "DE" => "D", "DJ" => "DJI", "DK" => "DK", "DO" => "DOM", "BJ" => "DY", "DZ" => "DZ", "ES" => "E", "KE" => "EAK", "TZ" => "EAT", "UG" => "EAU", "EC" => "EC", "EE" => "EE", "SV" => "EL", "GQ" => "EQ", "ER" => "ERI", "EH" => "ESH", "EG" => "ET", "ET" => "ETH", "FR" => "F", "FI" => "FIN", "FJ" => "FJI", "LI" => "FL", "FK" => "FLK", "FO" => "FRO", "GA" => "GA", "GB" => "GB", "GT" => "GCA", "GE" => "GE", "GF" => "GF", "GG" => "GG", "GH" => "GH", "GI" => "GIB", "GN" => "GN", "GP" => "GP", "GR" => "GR", "GL" => "GRO", "GU" => "GUM", "GY" => "GUY", "GW" => "GW", "HU" => "H", "HK" => "HK", "JO" => "HKJ", "HM" => "HMD", "HN" => "HON", "HR" => "HR", "IT" => "I", "IL" => "IL", "IM" => "IM", "IN" => "IND", "IO" => "IOT", "IR" => "IR", "IE" => "IRL", "IQ" => "IRQ", "IS" => "IS", "JP" => "J", "JM" => "JA", "JE" => "JE", "KH" => "K", "KG" => "KG", "KI" => "KIR", "KM" => "KM", "KN" => "KN", "KP" => "KO", "KW" => "KWT", "KZ" => "KZ", "LU" => "L", "LA" => "LAO", "LY" => "LAR", "LR" => "LB", "LS" => "LS", "LT" => "LT", "LV" => "LV", "MT" => "M", "MA" => "MA", "MY" => "MAL", "MH" => "MAR", "MC" => "MC", "MD" => "MD", "MX" => "MEX", "MF" => "MF", "FM" => "MIC", "MK" => "MK", "ME" => "MNE", "MP" => "MNP", "MO" => "MO", "MZ" => "MOC", "MN" => "MON", "MQ" => "MQ", "MU" => "MS", "MS" => "MSR", "MV" => "MV", "MW" => "MW", "YT" => "MYT", "NO" => "N", "AN" => "NA", "NC" => "NCL", "NF" => "NFK", "NI" => "NIC", "NU" => "NIU", "NL" => "NL", "NP" => "NPL", "NR" => "NR", "NZ" => "NZ", "UZ" => "OEZ", "OM" => "OMA", "PT" => "P", "PA" => "PA", "PN" => "PCN", "PE" => "PE", "PK" => "PK", "PL" => "PL", "PW" => "PLW", "PG" => "PNG", "PR" => "PR", "PS" => "PSE", "PY" => "PY", "PF" => "PYF", "QA" => "QA", "AR" => "RA", "BW" => "RB", "TW" => "RC", "CF" => "RCA", "CG" => "RCB", "CL" => "RCH", "RE" => "REU", "HT" => "RH", "ID" => "RI", "MR" => "RIM", "LB" => "RL", "MG" => "RM", "ML" => "RMM", "NE" => "RN", "RO" => "RO", "KR" => "ROK", "UY" => "ROU", "PH" => "RP", "SM" => "RSM", "BI" => "RU", "RU" => "RUS", "RW" => "RWA", "SE" => "S", "SB" => "SB", "SZ" => "SD", "SG" => "SGP", "GS" => "SGS", "SH" => "SHN", "SJ" => "SJM", "SK" => "SK", "SI" => "SLO", "SR" => "SME", "SN" => "SN", "SO" => "SP", "PM" => "SPM", "RS" => "SRB", "SS" => "SS", "ST" => "ST", "SD" => "SUD", "NA" => "SWA", "SX" => "SX", "SC" => "SY", "SY" => "SYR", "TH" => "T", "TJ" => "TAD", "CM" => "TC", "TC" => "TCA", "TG" => "TG", "TK" => "TKL", "TL" => "TLS", "TM" => "TMN", "TN" => "TN", "TO" => "TO", "TR" => "TR", "TD" => "TS", "TT" => "TT", "TV" => "TV", "UA" => "UA", "UM" => "UMI", "US" => "USA", "VA" => "VAT", "VG" => "VGB", "VI" => "VIR", "VN" => "VN", "VU" => "VU", "GM" => "WAG", "SL" => "WAL", "NG" => "WAN", "DM" => "WD", "GD" => "WG", "LC" => "WL", "WF" => "WLF", "WS" => "WSM", "VC" => "WV", "XK" => "XK", "YE" => "YMN", "YU" => "YU", "VE" => "YV", "ZM" => "Z", "ZA" => "ZA", "CD" => "ZRE", "ZW" => "ZW");
		$countryAfasCode = $country_code;
		if(isset($countryAfasCodes[$country_code])){
			$countryAfasCode = $countryAfasCodes[$country_code];
		}
		return $countryAfasCode;
	}

	// get orders log counter 
	public function get_afas_log_counter($projectId, $type='orders'){
		$this->load->model('Projects_model');
		if($type == 'orders'){
			$this->orders_import_error = $this->Projects_model->getValue('total_orders_import_error', $projectId)?$this->Projects_model->getValue('total_orders_import_error', $projectId):'';
			$this->orders_import_success = $this->Projects_model->getValue('total_orders_import_success', $projectId)?$this->Projects_model->getValue('total_orders_import_success', $projectId):'';
		} else if($type == 'afas_setup'){
			$this->afas_setup_error = $this->Projects_model->getValue('total_afas_setup_error', $projectId)?$this->Projects_model->getValue('total_afas_setup_error', $projectId):'';
		} else if($type == 'invoice'){
			$this->invoice_import_error = $this->Projects_model->getValue('total_invoice_import_error', $projectId)?$this->Projects_model->getValue('total_invoice_import_error', $projectId):'';
			$this->invoice_import_success = $this->Projects_model->getValue('total_invoice_import_success', $projectId)?$this->Projects_model->getValue('total_invoice_import_success', $projectId):'';
		}
	}

	// update the error or success counter if log get generated
	public function put_afas_log_counter($projectId, $log_type=true, $type='orders'){
		if($type == 'orders'){
			if($log_type)
				$this->orders_import_success++;
			else
				$this->orders_import_error++;
			$this->load->model('Projects_model');
	        $this->Projects_model->saveValue('total_orders_import_error', $this->orders_import_error, $projectId);
	        $this->Projects_model->saveValue('total_orders_import_success', $this->orders_import_success, $projectId);
	    } else if($type == 'afas_setup'){
	    	$this->afas_setup_error++;
	        $this->Projects_model->saveValue('total_afas_setup_error', $this->afas_setup_error, $projectId);
		}  else if($type == 'invoice'){
	    	if($log_type)
				$this->invoice_import_success++;
			else
				$this->invoice_import_error++;
	        $this->Projects_model->saveValue('total_invoice_import_error', $this->invoice_import_error, $projectId);
	        $this->Projects_model->saveValue('total_invoice_import_success', $this->invoice_import_success, $projectId);
		} 
	}

 	#########################################################################################################
    #      function is used to fetch debtor from afas and call woocommerce to import in woocommerce.        #
    #########################################################################################################
	public function getDebtors($projectId, $offset = 0, $amount = 10, $customerId = ''){
        $this->load->model('Woocommerce_model');
		$this->get_afas_log_counter($projectId, 'afas_setup');
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasDebtorConnector = $this->Projects_model->getValue('afas_customers_connector', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasDebtorConnector;
		$xml_array['filtersXml'] = "";
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="DebtorId" OperatorType="0" /></Index></options>';
		
		$err = $client->getError();
		if ($err) {
		    $message = '<h2>Constructor error</h2><pre>' . $err . '</pre>';
		    $message = '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    project_error_log($projectId, 'afas_setup_error','Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return false;
		}
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])){
		    $message = '<h2>Debug</h2>'.$result['faultcode'].' Error : '.$result['faultstring'];
		    project_error_log($projectId, 'afas_setup_error','Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return false;
		}
		$counter = 0;
		if (isset($result["GetDataWithOptionsResult"])) {
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

			$data = simplexml_load_string($resultData);
			if(isset($data->$afasDebtorConnector) && count($data->$afasDebtorConnector) > 0){
				foreach($data->$afasDebtorConnector as $customer){
					$results = array();
					$counter++;
					$offset++;
					$afasCustomerData = $this->xml2array($customer);
					$customerName = explode(' ', $afasCustomerData['DebtorName']);
					$customerFirstName = $customerName[0];
					unset($customerName[0]);
					$customerLastName = implode(' ', $customerName);
					if($customerLastName == ''){
						$customerLastName = ' ';
					}
					$address = isset($afasCustomerData['AdressLine3'])?explode('  ', $afasCustomerData['AdressLine3']):array();
					$postcode = isset($address[0]) ? $address[0] : '';
					$city = isset($address[1]) ? $address[1] : '';
					if(!isset($afasCustomerData['AdressLine4'])){
						$country = 'NL';
					} else {
						$country = $afasCustomerData['AdressLine4'];
					}
					$email = isset($afasCustomerData['Email']) ? $afasCustomerData['Email'] : '';
					$phone = isset($afasCustomerData['TelNr']) ? $afasCustomerData['TelNr'] : '';
					if($email=='')
						continue;
					$customerData = array(
						'email' 		=> $email,
						'first_name' 	=> $customerFirstName,
						'last_name' 	=> $customerLastName,
						'role'			=> 'customer',
						'password' 		=> 'P!O@I#U$'
					);

					$billing_address = array(
						"first_name"	=> $customerFirstName,
					    "last_name"		=> $customerLastName,
					    "company"		=> "",
					    "address_1"		=> $afasCustomerData['AdressLine1'],
					    "country"		=> $country,
					    "email"			=> $email
					);
					
					if($city != '')
						$billing_address['city'] 	= $city;
					if($postcode!='')
						$billing_address['postcode']= $postcode;
					if($phone!='')
						$billing_address['phone'] 	= $phone;
					$customerData['billing'] 		= $billing_address;
					$customerData['shipping'] 		= $billing_address;
					$results[]						= array('id'=>$offset, 'customerDetails'=> $customerData);
	                $this->Woocommerce_model->importCustomersInWoocommerce($results, $projectId, 'customers_offset');
				}
			}
		}
		return $counter;
	}

	#########################################################################################################
    #      function is used to fetch article from afas and return the bundle of article .			        #
    #########################################################################################################
	public function getArticles($projectId, $itemCode='', $offset = 0, $amount = 10, $filter = false, $debug = false){
		$this->get_afas_log_counter($projectId, 'afas_setup');
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);

		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		if($filter)
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="EAN" OperatorType="9" /></Filter></Filters>';
		else 
			$filtersXML = '';
		if($itemCode != '')
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$itemCode.'</Field></Filter></Filters>';
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take></options>';
		$err = $client->getError();
		if ($err) {
		    $message = '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    project_error_log($projectId, 'afas_setup_error','Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return;
		}
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])){
		    $message = '<h2>Debug</h2>'.$result['faultcode'].' Error : '.$result['faultstring'];
		    project_error_log($projectId, 'afas_setup_error','Error :: Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return;
		}
		if(isset($result["GetDataWithOptionsResult"])){
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = str_replace("\n", '|br|', $resultData);
			$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			
			if($debug == true){
				echo $filtersXML;
				echo '<pre>';
				print_r($result);
				return;
			}
			$data = simplexml_load_string($resultData);
			$numberOfResults = count($data->$afasArticleConnector);
			if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
				$results = array();
				$removeResults = array();
				foreach($data->$afasArticleConnector as $article){
					$article = $this->xml2array($article);						
					$results[] = $article;
				}
				return array(
					'results' => $results,
					'removeResults' => $removeResults,
					'numberOfResults' => $numberOfResults
				);
			}
		}
		return array(
			'results' => array(),
			'removeResults' => array(),
			'numberOfResults' => 0
		);
	}

	#########################################################################################################
    # 		function is used to fetch articles stock which are changed after las stock fetch from afas .  	#
    #########################################################################################################
	public function getArticlesStock($projectId, $itemCode='', $offset = 0, $amount = 10, $debug = false, $import_option_array=[]){

		$this->get_afas_log_counter($projectId, 'afas_setup');
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);		
		//$filtersXML = '';
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		if(!empty($import_option_array)){
			$lastUpdateDate = isset($import_option_array['lastUpdateDate'])?$import_option_array['lastUpdateDate']: date("Y-m-d\T00:00:00");	
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDate.'</Field></Filter></Filters>';
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take></options>';
		} else{
			$lastUpdateDate = $this->Projects_model->getValue('afas_stock_last_update_date', $projectId)?$this->Projects_model->getValue('afas_stock_last_update_date', $projectId): date("Y-m-d\T00:00:00");		
			$currentUpdateDate = date("Y-m-d\Th:i:s");
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDate.'</Field><Field FieldId="DateModified" OperatorType="3">'.$currentUpdateDate.'</Field></Filter></Filters>';
			$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>-1</Skip><Take>-1</Take></options>';
		}
		
		$xml_array['filtersXml'] = $filtersXML;
		$err = $client->getError();
		if ($err) {
		   	echo $message = '<h2>Debug</h2><pre>' . htmlspecialchars($client->getDebug(), ENT_QUOTES) . '</pre>';
		    project_error_log($projectId, 'afas_setup_error','Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return;
		}
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])){
		    echo $message = '<h2>Debug</h2>'.$result['faultcode'].' Error : '.$result['faultstring'];
		    project_error_log($projectId, 'afas_setup_error','Constructor error '.$message);
			$this->put_afas_log_counter($projectId, false, 'afas_setup');
		    return;
		}
		if (isset($result["GetDataWithOptionsResult"])) {
			$resultData = $result["GetDataWithOptionsResult"];
			$resultData = str_replace("\n", '|br|', $resultData);
			$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
			$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
			
			if($debug == true){
				echo $filtersXML;
				echo '<pre>';
				print_r($result);
				return;
			}
			$data = simplexml_load_string($resultData);
			$numberOfResults = count($data->$afasArticleConnector);
			if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
				$results = array();
				$removeResults = array();
				foreach($data->$afasArticleConnector as $article){
					$article = $this->xml2array($article);				
					$results[] = $article;
				}
				return array(
					'results' => $results,
					'removeResults' => $removeResults,
					'numberOfResults' => $numberOfResults
				);
			}
		}
		return array(
			'results' => array(),
			'removeResults' => array(),
			'numberOfResults' => 0
		);
	}

	#########################################################################################################
    # 				function is used to send orders as direct invoice to afas .  							#
    #########################################################################################################
	public function sendInvoice($projectId, $orderData){
		$billingData 	= $orderData['billing'];
		$shippingData 	= $orderData['shipping'];
		$customerData 	= array_merge($shippingData, $billingData);
		$this->get_afas_log_counter($projectId, 'invoice');

		if($this->checkAfasOrderInvoiceExists($projectId, $orderData['id'], 'orders')){
			return false;
		}

		if(!$debtorId = $this->checkAfasCustomerExists($projectId, $customerData)){
			return false;
		}

		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$xmlInvoice = new SimpleXMLElement("<FbDirectInvoice></FbDirectInvoice>");
		$invoiceElement = $xmlInvoice->addChild('Element');
		$fields = $invoiceElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		$fields->DbId = $debtorId;
		// Selecteer magazijn in order kop
		$fields->War = '*****';
		$fields->OrNu = $orderData['id'];
		$comment = isset($orderData['comment']) ? $orderData['comment'] : '';
		if($comment != ''){
			$fields->Re = $comment;
		}

		$objectsElement = $invoiceElement->addChild('Objects');
		$FbDirectInvoiceLines = $objectsElement->addChild('FbDirectInvoiceLines');
		// Add items
		$products = $orderData['line_items'];
		foreach($products as $item){
			$product = $item;
			$element = $FbDirectInvoiceLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			$fields->VaIt = 2;
			$fields->ItCd = $product['sku'];
			$fields->BiUn = 'stk';
			$fields->QuUn = floatval($product['quantity']);
			$price = $product['price'];
			$fields->Upri = round($price, 2);
		}

		$data = $xmlInvoice->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "FbDirectInvoice";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			project_error_log($projectId, 'importInvoices','Could not export Invoice '.$orderData['id'].' to AFAS. Error: '.$result['faultstring']);
			$this->put_afas_log_counter($projectId, false, 'invoice');
			return false;
		} else {
			project_error_log($projectId, 'importInvoices','Exported Invoice '.$orderData['id'].' to AFAS.');
			$this->put_afas_log_counter($projectId, true, 'invoice');
		}
		return true;
	}

	#########################################################################################################
    #      function is used to import orders as salesorder in afas   .			        					#
    #########################################################################################################
	public function sendOrder($projectId, $orderData){
		$billingData 	= $orderData['billing'];
		$shippingData 	= $orderData['shipping'];
		$customerData 	= array_merge($shippingData, $billingData);

		$this->get_afas_log_counter($projectId, 'orders');

		if($this->checkAfasOrderInvoiceExists($projectId, $orderData['id'], 'orders')){
			return false;
		}
		if(!$debtorId = $this->checkAfasCustomerExists($projectId, $customerData)){
			return false;
		}
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$xmlOrder = new SimpleXMLElement("<FbSales></FbSales>");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		$fields->DbId = $debtorId;
		// Selecteer magazijn in order kop
		$fields->War = '*****';
		$fields->OrNu = $orderData['id'];
		$comment = isset($orderData['comment']) ? $orderData['comment'] : '';
		if($comment != ''){
			$fields->Re = $comment;
		}
		// Administratie
		if($this->Projects_model->getValue('afas_orders_administration', $projectId) != '' && $this->Projects_model->getValue('afas_orders_administration', $projectId) != 'default'){
			$administration = $this->Projects_model->getValue('afas_orders_administration', $projectId);
			$fields->Unit = $administration;
		}
		
		// Delivery address
		$deliveryAddress = $this->addDeliveryAddress($orderData, $debtorId, $projectId);
		if($deliveryAddress != false){
			$fields->DlAd = $deliveryAddress;
		}
		$objectsElement = $orderElement->addChild('Objects');
		$FbSalesLines = $objectsElement->addChild('FbSalesLines');
		// Add items
		$products = $orderData['line_items'];
		foreach($products as $item){
			$product = $item;
			$element = $FbSalesLines->addChild('Element');
			$fields = $element->addChild('Fields');
			$fields->addAttribute('Action', 'insert');
			$fields->VaIt = 2;
			$fields->ItCd = $product['sku'];
			$fields->BiUn = 'stk';
			$fields->QuUn = floatval($product['quantity']);
			// Set price
			$price = $product['price'];
			$fields->Upri = round($price, 2);
		}
		// Shipping
		$shippingSku = $this->Projects_model->getValue('afas_shipping_sku', $projectId);
		if($shippingSku != '' && $shippingSku != ' '){
			if(isset($orderData['totals']) && isset($orderData['totals']['shipping']) && $orderData['totals']['shipping'] > 0){
				$element = $FbSalesLines->addChild('Element');
				$fields = $element->addChild('Fields');
				$fields->addAttribute('Action', 'insert');
				$fields->VaIt = 2;
				$fields->ItCd = $shippingSku;
				//$fields->BiUn = 'stk';
				$fields->QuUn = 1;
				// Set price
				$price = $orderData['totals']['shipping'];
				$fields->Upri = round($price, 2);
			}
		}
		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "FbSales";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;	
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			project_error_log($projectId, 'exportorders','Could not export order '.$orderData['id'].' to AFAS. Error: '.$result['faultstring']);
			$this->put_afas_log_counter($projectId, false);
			return false;
		} else {
			project_error_log($projectId, 'exportorders','Exported order '.$orderData['id'].' to AFAS.');
			$this->put_afas_log_counter($projectId, true);
		}
		return true;
	}

	#########################################################################################################
    #      function is used to import orders as salesorder in afas   .			        					#
    #########################################################################################################
	public function checkAfasOrderInvoiceExists($projectId, $orderId, $type = 'orders'){
		$exist = false;
		return $exist;
	}

	#########################################################################################################
    #      function is used to check if the customer exists in afas while orders export .  					#
    #########################################################################################################
	public function checkAfasCustomerExists($projectId, $customerData){
		$finalDebtorId = false;
		if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'email')){
			$finalDebtorId = $debtorId;
		} else {
			if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'zipcode_streetnumber')){
				$finalDebtorId = $debtorId;
			} else {
				if($debtorId = $this->checkAfasCustomer($projectId, $customerData, 'lastname_firstname')){
					$finalDebtorId = $debtorId;
				}
			}
		}
		if(!$finalDebtorId){
			if($this->createAfasCustomer($projectId, $customerData)){
				$finalDebtorId = $this->checkAfasCustomer($projectId, $customerData, 'email');
			}
		}
		return $finalDebtorId;
	}

	#########################################################################################################
    #      function is used to check if the customer exists based on checkAfasCustomerExists ( ) .  		#
    #########################################################################################################
	public function checkAfasCustomer($projectId, $customerData, $type){
		if($type == 'email'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="MailWork" OperatorType="1">'.$customerData['email'].'</Field></Filter></Filters>';
		}  elseif($type == 'zipcode_streetnumber'){
			$postCode = preg_replace('/(?<=[a-z])(?=\d)|(?<=\d)(?=[a-z])/i', ' ', strtoupper($customerData['postcode']));
			$city = strtoupper($customerData['city']);
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="AdressLine1" OperatorType="1">'.str_replace(',', '', $customerData['address_1']).'</Field><Field FieldId="AdressLine3" OperatorType="1">'.$customerData['postcode'].'  '.$customerData['city'].'</Field></Filter></Filters>';
		} elseif($type == 'lastname_firstname'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="Name" OperatorType="6">%'.$customerData['first_name'].' '.$customerData['last_name'].'%</Field></Filter></Filters>';
		} else {
			return false;
		}
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] 		= $afasToken;
		$xml_array['connectorId'] 	= "Profit_OrgPer";
		$xml_array['filtersXml'] 	= $filtersXML;
		$xml_array['options'] 		= '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$data = simplexml_load_string($resultData);
		if(isset($data->Profit_OrgPer) && count($data->Profit_OrgPer) > 0){
			$afasPersonData = $data->Profit_OrgPer;
			$afasPersonId = $afasPersonData->BcCo;
			if($afasPersonId != ''){
				// Get sales relation ID
				$debtorFiltersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="BcCo" OperatorType="1">'.$afasPersonId.'</Field></Filter></Filters>';
				
				$client = new nusoap_client($afasGetUrl, true);
				$client->setUseCurl(true);
				$client->useHTTPPersistentConnection();
				$xml_array['environmentId'] = $afasEnvironmentId;
				$xml_array['token'] = $afasToken;
				$xml_array['connectorId'] = "Profit_Debtor";
				$xml_array['filtersXml'] = $debtorFiltersXML;
				$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
				$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
				$resultData = $result["GetDataWithOptionsResult"];
				$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
				$data = simplexml_load_string($resultData);
				if(isset($data->Profit_Debtor) && count($data->Profit_Debtor) > 0){
					$debtorData = $data->Profit_Debtor;
					$debtorId = $debtorData->DebtorId;
					if($debtorId != ''){
						return $debtorId;
					}
				}
			}
		}
		return false;
	}

	#########################################################################################################
    #      function is used to create customer if not exists in afas  while orders export.  				#
    #########################################################################################################
	public function createAfasCustomer($projectId, $customerData){
		if($customerData['company'] != ''){
			return $this->createAfasCustomerOrg($projectId, $customerData);
		} else {
			return $this->createAfasCustomerPerson($projectId, $customerData);
		}
		return false;
	}

	#########################################################################################################
    #   function is used to create customer as person if company not exists in afas  while orders export.  	#    #########################################################################################################
	public function createAfasCustomerPerson($projectId, $customerData){
		$xmlCustomer = new SimpleXMLElement("<KnSalesRelationPer></KnSalesRelationPer>");
		$xmlCustomer->addAttribute("xsi:somename", "somevalue", 'http://www.w3.org/2001/XMLSchema-instance');
		$salesRelationElement = $xmlCustomer->addChild('Element');
		$salesRelationElement->addAttribute('DbId', '');
		$fields = $salesRelationElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		$paymentCondition = 14;
		if($this->Projects_model->getValue('afas_customers_payment_condition', $projectId) != ''){
			$paymentCondition = $this->Projects_model->getValue('afas_customers_payment_condition', $projectId);
		}
		$controlAccount = 1400;
		if($this->Projects_model->getValue('afas_customers_control_account', $projectId) != ''){
			$controlAccount = $this->Projects_model->getValue('afas_customers_control_account', $projectId);
		}
		if(!empty(json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true))){
			$defaultFields = json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true);
			$codes = $defaultFields['code'];
			foreach($codes as $index => $code){
				$value = $defaultFields['waarde'][$index];
				if($value != ''){
					$fields->$code = $value;
				}
			}
		}
		$fields->CuId = 'EUR';
		$fields->InPv = 'A';
		$fields->DeCo = '0';
		$fields->IsDb = 1;
		$fields->PaCd = $paymentCondition;
		$fields->ColA = $controlAccount;
		$objectsElement = $salesRelationElement->addChild('Objects');
		$knPerson = $objectsElement->addChild('KnPerson');
		$knPersonElement = $knPerson->addChild('Element');
		$knPersonFields = $knPersonElement->addChild('Fields');
		$knPersonFields->addAttribute('Action', 'insert');
		$knPersonFields->PadAdr = 1;
		$knPersonFields->AutoNum = 1;
        // 		$knPersonFields->MatchPer = 6;
		$knPersonFields->MatchPer = 3;
		$knPersonFields->SeNm = substr($customerData['first_name'].' '.$customerData['last_name'], 0, 9);
		$knPersonFields->FiNm = $customerData['first_name'];
		$knPersonFields->LaNm = $customerData['last_name'];
		$knPersonFields->EmA2 = $customerData['email'];
		$knPersonFields->EmAd = $customerData['email'];
		$knPersonFields->TeNr = $customerData['phone'];
		$knPersonFields->Corr = 1;
		$knPersonFields->AddToPortal = 0;
		$knPersonFields->ViGe = 'O';
		$street = $customerData['address_1'];
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		if(!is_numeric($homeNumber)){
			$matches = array();
			preg_match('/([0-9]+)([^0-9]+)/',$homeNumber,$matches);
			$homeNumber = isset($matches[1]) ? $matches[1] : '';
			$homeNumberAddition = isset($matches[2]) ? $matches[2] : '';
		}
		$street = implode(' ', $street);

		$magentoCountryId = $customerData['country'];
		$countryAfasCode = $this->afasCountryCode($customerData['country']);
		$knPersonObjects = $knPersonElement->addChild('Objects');
		$knBasicAddress = $knPersonObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $customerData['postcode']);
		$knBasicAddressElementFields->Rs = $customerData['city'];
		$knBasicAddressElementFields->ResZip = 0;
		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "KnSalesRelationPer";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			project_error_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in AFAS. Error: '.$result['faultstring']);
			$this->put_afas_log_counter($projectId, false);
			return false;
		}
		return true;
	}

	#########################################################################################################
    #   function is used to create customer as organisation for company in afas  while orders export.  	    #    #########################################################################################################
	public function createAfasCustomerOrg($projectId, $customerData){

		$xmlCustomer = new SimpleXMLElement("<KnSalesRelationOrg></KnSalesRelationOrg>");
		$salesRelationElement = $xmlCustomer->addChild('Element');
		$salesRelationElement->addAttribute('DbId', '');
		$fields = $salesRelationElement->addChild('Fields');
		$fields->addAttribute('Action', 'insert');
		$paymentCondition = 14;
		if($this->Projects_model->getValue('afas_customers_payment_condition', $projectId) != ''){
			$paymentCondition = $this->Projects_model->getValue('afas_customers_payment_condition', $projectId);
		}
		$controlAccount = 1400;
		if($this->Projects_model->getValue('afas_customers_control_account', $projectId) != ''){
			$controlAccount = $this->Projects_model->getValue('afas_customers_control_account', $projectId);
		}
		if(!empty(json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true))){
			$defaultFields = json_decode($this->Projects_model->getValue('default_debtor_fields', $projectId), true);
			$codes = $defaultFields['code'];
			foreach($codes as $index => $code){
				$value = $defaultFields['waarde'][$index];
				if($value != ''){
					$fields->$code = $value;
				}
			}
		}
		$fields->CuId = 'EUR';
		$fields->InPv = 'A';
		$fields->DeCo = '0';
		$fields->IsDb = 1;
		$fields->PaCd = $paymentCondition;
 		$fields->ColA = $controlAccount;
		$objectsElement = $salesRelationElement->addChild('Objects');
		$knPerson = $objectsElement->addChild('KnOrganisation');
		$knPersonElement = $knPerson->addChild('Element');
		$knPersonFields = $knPersonElement->addChild('Fields');
		$knPersonFields->addAttribute('Action', 'insert');
		$knPersonFields->PadAdr = 1;
		$knPersonFields->AutoNum = 1;
		$knPersonFields->MatchOga = 6;
		$knPersonFields->SeNm = substr($customerData['company'], 0, 9);
		$knPersonFields->Nm = $customerData['company'];
		$knPersonFields->EmAd = $customerData['email'];
		$knPersonFields->TeNr = $customerData['phone'];
		$knPersonFields->Corr = 1;
		$knPersonFields->AddToPortal = 0;
		$street = $customerData['address_1'];
		$street = str_replace("\n", ' ', $street);
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		$street = implode(' ', $street);
		$magentoCountryId = $customerData['country'];
		$countryAfasCode  = $this->afasCountryCode($customerData['country']);
		$knPersonObjects = $knPersonElement->addChild('Objects');
		$knBasicAddress = $knPersonObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $customerData['postcode']);
		$knBasicAddressElementFields->Rs = $customerData['city'];
		$knBasicAddressElementFields->ResZip = 0;
		$contactObjectsElement = $knPersonElement->addChild('Objects');
		$knContact = $contactObjectsElement->addChild('KnContact');
		$knContactElement = $knContact->addChild('Element');
		$knContactFields = $knContactElement->addChild('Fields');
		$knContactFields->addAttribute('Action', 'insert');
		$knContactFields->PadAdr = 1;
		$knContactFields->ViKc = 3;
		$contactPersonObjectsElement = $knContactElement->addChild('Objects');
		$knContact = $contactPersonObjectsElement->addChild('KnPerson');
		$knContactElement = $knContact->addChild('Element');
		$knContactFields = $knContactElement->addChild('Fields');
		$knContactFields->addAttribute('Action', 'insert');
		$knContactFields->PadAdr = 1;
		$knContactFields->AutoNum = 1;
		$knContactFields->MatchPer = 0;
		$knContactFields->SeNm = substr($customerData['first_name'].' '.$customerData['last_name'], 0, 9);
		$knContactFields->FiNm = $customerData['first_name'];
		$knContactFields->LaNm = $customerData['last_name'];
		$knContactFields->EmA2 = $customerData['email'];
		$knContactFields->EmAd = $customerData['email'];
		$knContactFields->TeNr = $customerData['phone'];
		$knContactFields->Corr = 1;
		$knContactFields->AddToPortal = 0;
		$knContactFields->ViGe = 'O';
		$data = $xmlCustomer->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);
		$data = str_replace(' xsi:somename="somevalue"', '', $data);
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "KnSalesRelationOrg";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			project_error_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in AFAS. Error: '.$result['faultstring']);
			$this->put_afas_log_counter($projectId, false);
			return false;
		}
		return true;	
	}

	#########################################################################################################
    #   function is used to create delivery address for  sales orders in afas .					 	    	#    #########################################################################################################
	public function addDeliveryAddress($orderData, $afasCustomerId, $projectId){
		$orderShippingAddress = $orderData['shipping'];
		$company = isset($orderShippingAddress['company']) ? $orderShippingAddress['company'] : '';
		if($company != ''){
			$connector = 'KnOrganisation';
		} else {
			$connector = 'KnPerson';
		}
		$street = $orderShippingAddress['address_1'];
		$street = str_replace("\n", ' ', $street);
		$street = explode(' ', $street);
		$homeNumber = end($street);
		$homeNumberAddition = '';
		array_pop($street);
		$lastStreetPart = end($street);
		if(is_numeric($lastStreetPart)){
			$homeNumberAddition = $homeNumber;
			$homeNumber = $lastStreetPart;
			array_pop($street);
		}
		$street = implode(' ', $street);
		$zipCode = str_replace(' ', '', $orderShippingAddress['postcode']);
		$magentoCountryId = $orderShippingAddress['country'];
		// Get current delivery addresses first
		$deliveryAddresses = $this->getDeliveryAddresses($afasCustomerId, $projectId);
		$organisationId = '';
		foreach($deliveryAddresses as $address){
			$organisationId = $address->OrgId;
			if($address->A_Straat == $street && $address->A_Huisnummer == $homeNumber && str_replace(' ', '', $address->A_Postcode) == $zipCode){
				return (string)$address->Adres_ID;
			}
		}
		if(!$organisationId > 0){
			$debtor = $this->loadCustomer($afasCustomerId, $projectId);
			if(isset($debtor->BcCo) && $debtor->BcCo != ''){
				$organisationId = $debtor->BcCo;
			} else {
				return false;
			}
		}
		
		// Create delivery address
		$xmlOrganisation = new SimpleXMLElement("<".$connector."></".$connector.">");
		$xmlOrganisation->addAttribute("xsi:somename", "somevalue", 'http://www.w3.org/2001/XMLSchema-instance');
		$knOrganisationElement = $xmlOrganisation->addChild('Element');
		$fields = $knOrganisationElement->addChild('Fields');
		$fields->addAttribute('Action', 'update');
		if($company != ''){
			$fields->MatchOga = 0;
		} else {
			$fields->MatchPer = 0;
		}
		$fields->BcCo = $organisationId;
		$knOrganisationObjects = $knOrganisationElement->addChild('Objects');
		$knContact = $knOrganisationObjects->addChild('KnContact');
		$knContactElement = $knContact->addChild('Element');
		$knContactElementFields = $knContactElement->addChild('Fields');
		$knContactElementFields->addAttribute('Action', 'insert');
		$knContactElementFields->ViKc = 'AFL';
		$knContactElementFields->ExAd = '';
		$knContactElementFields->PadAdr = 0;
		$knContactObjects = $knContactElement->addChild('Objects');
		$countryAfasCode = $this->afasCountryCode($orderShippingAddress['country']);
		$knBasicAddress = $knContactObjects->addChild('KnBasicAddressAdr');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $orderShippingAddress['postcode']);
		$knBasicAddressElementFields->Rs = $orderShippingAddress['city'];
		$knBasicAddressElementFields->ResZip = 0;
		$knBasicAddress = $knContactObjects->addChild('KnBasicAddressPad');
		$knBasicAddressElement = $knBasicAddress->addChild('Element');
		$knBasicAddressElementFields = $knBasicAddressElement->addChild('Fields');
		$knBasicAddressElementFields->addAttribute('Action', 'insert');
		$knBasicAddressElementFields->CoId = $countryAfasCode;
		$knBasicAddressElementFields->PbAd = 0;
		$knBasicAddressElementFields->Ad = $street;
		$knBasicAddressElementFields->HmNr = $homeNumber;
		$knBasicAddressElementFields->HmAd = $homeNumberAddition;
		$knBasicAddressElementFields->ZpCd = str_replace('  ', ' ', $orderShippingAddress['postcode']);
		$knBasicAddressElementFields->Rs = $orderShippingAddress['city'];
		$knBasicAddressElementFields->ResZip = 0;

		$data = $xmlOrganisation->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);
		
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = $connector;
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;	
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode']) && $result['faultcode'] != ''){
			project_error_log($projectId, 'exportorders'," Could not add delivery address in AFAS for order -: ".$orderData['id'].' Error: '.$result['faultstring']);
			$this->put_afas_log_counter($projectId, false);
			return false;
		}
		$deliveryAddresses = $this->getDeliveryAddresses($afasCustomerId, $projectId);
		foreach($deliveryAddresses as $address){
			if($address->A_Straat == $street && $address->A_Huisnummer == $homeNumber && str_replace(' ', '', $address->A_Postcode) == $zipCode){
				return (string)$address->Adres_ID;
			}
		}
		return false;
	}
	
	#########################################################################################################
    #   function is used to get delivery address for  sales orders in afas .					 	    	#    #########################################################################################################
	public function getDeliveryAddresses($afasCustomerId, $projectId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DebiteurId" OperatorType="1">'.$afasCustomerId.'</Field></Filter></Filters>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_Deliveryaddress";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>100</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		if(isset($result['faultcode'])){
			return false;
		}
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		$data = simplexml_load_string($resultData);
		$addresses = array();
		if(isset($data->Profit_Deliveryaddress) && count($data->Profit_Deliveryaddress) > 0){
			foreach($data->Profit_Deliveryaddress as $address){
				$addresses[] = $address;
			}
		}
		return $addresses;
	}
	
	#########################################################################################################
    #   function is used to load customer while creating sales order in afas .					 	    	#    #########################################################################################################
	public function loadCustomer($afasCustomerId, $projectId){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DebtorId" OperatorType="1">'.$afasCustomerId.'</Field></Filter></Filters>';
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = "Profit_Debtor";
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
		$debtors = array();
		if(isset($data->Profit_Debtor) && count($data->Profit_Debtor) > 0){
			foreach($data->Profit_Debtor as $debtor){
				$debtors[] = $debtor;
			}
		}
		return $debtors[0];
	}




	//--------------------------------------------------------------------------------------------

	

	function updateAfasStocks($projectId, $formated_product){

		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$formated_product['ReferenceCode'].'</Field></Filter></Filters>';
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>10</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		
		$data = simplexml_load_string($resultData);
		$numberOfResults = count($data->$afasArticleConnector);
		$numberOfResults = count($data->$afasArticleConnector);
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$results =$this->xml2array($data->$afasArticleConnector);
			$this->saveAfasStock($projectId, $formated_product);
			return $results;
		}
		return false;
	}
	
	function saveAfasStock($projectId, $formated_product){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasUpdateUrl = $this->Projects_model->getValue('afas_update_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		
		$xmlOrder = new SimpleXMLElement("<FbItemArticle ></FbItemArticle >");
		$orderElement = $xmlOrder->addChild('Element');
		$fields = $orderElement->addChild('Fields');
		$fields->addAttribute('Action', 'update');

		$fields->ItCd 	= $formated_product['ReferenceCode'];
		$fields->Qu 	= 19;
		$data = $xmlOrder->asXML();
		$data = str_replace('<?xml version="1.0"?>', '', $data);
		$data = str_replace("\n", '', $data);

		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasUpdateUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorType'] = "FbItemArticle ";
		$xml_array['connectorVersion'] = 1;
		$xml_array['dataXml'] = $data;
		print_r($xml_array);
		$result = $client->call('Execute', array('parameters' => $xml_array), '', '', false, true);
		print_r($result);
		return true;
	}

	function getStockArticles($projectId, $offset = 0, $amount = 10, $debug = false){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $projectId);
		$lastUpdateDate = $this->Projects_model->getValue('afas_last_update_date', $projectId);
		$filterEnabled = $this->Projects_model->getValue('afas_enable_article_enabled_filter', $projectId);
		
		$filtersXML = '';
		if($filterEnabled == '1'){
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
		}
		if($lastUpdateDate != ''){
			$lastUpdateDateFilter = $lastUpdateDate.'T00:00:00';
			$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field></Filter></Filters>';
			if($filterEnabled == '1'){
				$filtersXML = '<Filters><Filter FilterId="Filter1"><Field FieldId="DateModified" OperatorType="2">'.$lastUpdateDateFilter.'</Field><Field FieldId="enabled" OperatorType="1">true</Field></Filter></Filters>';
			}
		}
		
		$this->load->helper('NuSOAP/nusoap');
		
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = $filtersXML;
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>'.$offset.'</Skip><Take>'.$amount.'</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);
		
		if($debug == true){
			echo $filtersXML;
			echo '<pre>';
			print_r($result);
			return;
		}

		$data = simplexml_load_string($resultData);
		$numberOfResults = count($data->$afasArticleConnector);
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$results = array();
			foreach($data->$afasArticleConnector as $article){
				$article = $this->xml2array($article);
				if(isset($article['enabled']) && ($article['enabled'] == false || $article['enabled'] == 'false')){
					continue;
				}
				$finalArticleData = array();
				$finalArticleData['model'] = $article['ItemCode'];
				if(isset($article['StockActual'])){
					$finalArticleData['quantity'] = $article['StockActual'];
				}
				$results[] = $finalArticleData;
			}
			return array(
				'results' => $results,
				'numberOfResults' => $numberOfResults
			);
		}
		return array(
			'results' => array(),
			'numberOfResults' => 0
		);
	}
	
}