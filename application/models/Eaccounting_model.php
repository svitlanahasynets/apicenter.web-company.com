<?php
class Eaccounting_model extends CI_Model {
	
	public $apiURL;

    function __construct()
    {
        parent::__construct();

        $CI =& get_instance();
        $CI->config->load('apiconnection', true);
        $apiconnection = $CI->config->item('apiconnection');

        $this->apiURL = $apiconnection['eaccounting_url'];
        $this->apiConnectTokenURL = $apiconnection['connect_token'];
    }
    	
	function getToken($projectId){
		$clientId = $this->Projects_model->getValue('eaccounting_client_id', $projectId);
		$clientSecret = $this->Projects_model->getValue('eaccounting_secret_key', $projectId);
		
		$authorizeCode = $this->Projects_model->getValue('eaccounting_authorize_code', $projectId);
		$existingToken = $this->Projects_model->getValue('eaccounting_token', $projectId);
		$refreshToken = $this->Projects_model->getValue('eaccounting_refresh_token', $projectId);

		if($existingToken != ''){
			return $existingToken;
		}

		$authorization_key = base64_encode($clientId.':'.$clientSecret);

		if($refreshToken != ''){
			$data = array(
				'grant_type' => 'refresh_token',
				'refresh_token' => $refreshToken
			);
			$get_params = http_build_query($data);
			
			$ch = curl_init($this->apiConnectTokenURL);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get_params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/x-www-form-urlencoded",
				"Authorization: Basic ".$authorization_key,
			));
			 
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);

			if(isset($result['access_token'])){
				$this->Projects_model->saveValue('eaccounting_token', $result['access_token'], $projectId);
				$this->Projects_model->saveValue('eaccounting_refresh_token', $result['refresh_token'], $projectId);
				return $result['access_token'];
			}
		} else {

			$data = array(
				'grant_type' => 'authorization_code',
				'code' => $authorizeCode,
				'redirect_uri' => 'https://dev.apicenterv3.com/authorize/eaccounting'
			);
			$get_params = http_build_query($data);
			
			$ch = curl_init($this->apiConnectTokenURL);
			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
			curl_setopt($ch, CURLOPT_POSTFIELDS, $get_params);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array(
				"Content-Type: application/x-www-form-urlencoded",
				"Authorization: Basic ".$authorization_key,
			));
			 
			$result = curl_exec($ch);
			curl_close($ch);
			$result = json_decode($result, true);

			if(isset($result['access_token'])){
				$this->Projects_model->saveValue('eaccounting_token', $result['access_token'], $projectId);
				$this->Projects_model->saveValue('eaccounting_refresh_token', $result['refresh_token'], $projectId);
				return $result['access_token'];
			}
		}
		return false;
	}
	
	function getArticles($projectId, $offset = 0, $amount = 10, $debug = false){
	    
	    log_message('debug', 'EACCOUNTING GetArticle = ' . $projectId . ' ');
	    
		$lastUpdateDate = $this->Projects_model->getValue('eaccounting_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		
		//log_message('debug', 'Project id = ' . $projectId . ' token = ' . $token);
		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'PageNumber' => $pageNumber,
			'PageSize' => $amount,
			'ChangedUtc' => $lastUpdateDate
		);
		 
		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);

		
		$ch = curl_init($this->apiURL."/v2/articles?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: eaccountingapi-sandbox.test.vismaonline.com",
			"Accept: application/json",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		$articles = $result['Data'];
		$results = array();
		$numberOfResults = count($articles);

		foreach($articles as $product){

			if(!$product['IsActive']){
				continue;
			}

			if(!$product['SendToWebshop']){
				continue;
			}
			
			$productData = array();
			$productData['articleId'] = $product['Id'];
			$productData['model'] = $product['Number'];
			$productData['name'] = $product['Name'];
			$productData['itemdescription'] = $product['Name'];
			$productData['quantity'] = $product['StockBalance'];
			$productData['price'] = $product['NetPrice'];
			$productData['priceincludestax'] = '';

			$productData['Tax_class_id'] = $product['CodingName'];
			$productData['Art_group'] = $product['ArticleLabels'];
			$productData['urlkey'] = str_replace(' ', '_', $product['Name'].' '.$product['Number']);
			
			$results[] = $productData;
		}
		
		if($lastUpdateDate != '' && $numberOfResults == 0){
			$this->Projects_model->saveValue('eaccounting_last_update_date', date('Y-m-d H:i:s'), $projectId);
			$this->Projects_model->saveValue('article_offset', 0, $projectId);
		}
        
        log_message('debug', 'EACCOUNTING GetArticle END = ' . $projectId . ' ' . var_export($productData, true));        
        
		return array(
			'results' => $results,
			'numberOfResults' => $numberOfResults
		);
	}

	function getStockArticles($projectId, $offset = 0, $amount = 10, $debug = false){
		log_message('debug', 'EACCOUNTING GetArticle = ' . $projectId . ' ');
	    
		$lastUpdateDate = $this->Projects_model->getValue('eaccounting_stock_last_update_date', $projectId);
		$token = $this->getToken($projectId);
		
		//log_message('debug', 'Project id = ' . $projectId . ' token = ' . $token);
		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);
		$pageNumber = 1;
		if($offset > $amount){
			$pageNumber = ($offset / $amount) + 1;
		}
		
		$filterData = array(
			'PageNumber' => $pageNumber,
			'PageSize' => $amount,
			'StockBalanceManuallyChangedUtc' => $lastUpdateDate
		);

		$j = json_decode(json_encode($filterData));
		$get_params = http_build_query($j);

		$ch = curl_init($this->apiURL."/v2/articles?".$get_params);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Host: eaccountingapi-sandbox.test.vismaonline.com",
			"Accept: application/json",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		
		$articles = $result['Data'];
		$results = array();
		$numberOfResults = count($articles);

		foreach($articles as $product){

			if(!$product['IsActive']){
				continue;
			}

			if(!$product['SendToWebshop']){
				continue;
			}

			if($product['StockBalanceManuallyChangedUtc'] != $lastUpdateDate){
				continue;
			}
			
			$productData = array();
			$productData['model'] = $product['Number'];
			$productData['quantity'] = $product['StockBalance'];
			
			$results[] = $productData;
		}
		
		if($lastUpdateDate != '' && $numberOfResults == 0){
			$this->Projects_model->saveValue('eaccounting_stock_last_update_date', date('Y-m-d H:i:s'), $projectId);
			$this->Projects_model->saveValue('article_offset', 0, $projectId);
		}
        
        log_message('debug', 'EACCOUNTING getStockArticles END = ' . $projectId . ' ' . var_export($productData, true));        
        
		return array(
			'results' => $results,
			'numberOfResults' => $numberOfResults
		);
	}

	function sendOrder($projectId, $orderData){

		$token = $this->getToken($projectId);

		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);
		
		$billingData = $orderData['billing_address'];
		$customerData = $orderData['customer'];
		$customerData = array_merge($customerData, $billingData);

		if(!$customerId = $this->checkEaccountingCustomerExists($projectId, $orderData)){
			return false;
		}

		$articles = $this->getArticles($projectId);

        $ArticleId = '';

		foreach ($articles['results'] as $key => $article) {
			if ($article['model'] == $orderData['order_products'][0]['model']) {
				$ArticleId = $article['articleId'];
			}
		}

		if ($ArticleId == '') {
			apicenter_logs($projectId, 'projectcontrol', 'Product doesnot exist in Eaccounting', false);
			return false;
		}
		

		$saveData = array(
			// 'Id' => '',
			'Amount' => $orderData['totals']['total'],
			'CustomerId' => $customerId,
			'CurrencyCode' => $orderData['currency'],
			'CreatedUtc' => date('Y-m-d H:i:s', strtotime($orderData['create_at'])),
			'VatAmount' => $orderData['totals']['tax'],
			'RoundingsAmount' => 0,
			'DeliveredAmount' => 0,
			'DeliveredVatAmount' => 0,
			'DeliveredRoundingsAmount' => 0,
			'DeliveryCustomerName' => $orderData['shipping_address']['first_name'].' '.$orderData['shipping_address']['last_name'],
			'DeliveryAddress1' => $orderData['shipping_address']['address1'],
			'DeliveryAddress2' => $orderData['shipping_address']['address2'],
			'DeliveryPostalCode' => $orderData['shipping_address']['postcode'],
			'DeliveryCity' => $orderData['shipping_address']['city'],
			'DeliveryCountryCode' => $orderData['shipping_address']['country'],
			'YourReference' => $orderData['id'],
			'OurReference' => '',
			'InvoiceAddress1' => $orderData['billing_address']['address1'],
			'InvoiceAddress2' => $orderData['billing_address']['address2'],
			'InvoiceCity' => $orderData['billing_address']['city'],
			'InvoiceCountryCode' => $orderData['billing_address']['country'],
			'InvoiceCustomerName' => $orderData['billing_address']['first_name'].' '.$orderData['billing_address']['last_name'],
			'InvoicePostalCode' => $orderData['billing_address']['postcode'],
			'DeliveryMethodName' => null,
			'DeliveryMethodCode' => null,
			'DeliveryTermName' => null,
			'DeliveryTermCode'=> null,
			'EuThirdParty' => false,
			'CustomerIsPrivatePerson' => false,
			'OrderDate' => date('Y-m-d', strtotime($orderData['create_at'])),
			'Status' => 1,
			'Number' => null,
			'ModifiedUtc' => date('Y-m-d H:i:s', strtotime($orderData['modified_at'])),
			'DeliveryDate' => null,
			'HouseWorkAmount' => 0,
			'HouseWorkAutomaticDistribution' => false,
			'HouseWorkCorporateIdentityNumber' => null,
			'HouseWorkPropertyName' => null,
			'Rows' => 	array(
							array(
								'LineNumber' => 0,
								'DeliveredQuantity' => 0,
								// 'ArticleId' => $orderData['order_products'][0]['order_product_id'],
								'ArticleId' => $ArticleId,
								'ArticleNumber' => $orderData['order_products'][0]['model'],
								'IsTextRow' => false,
								'Text' => $orderData['order_products'][0]['name'],
								'UnitPrice' => $orderData['order_products'][0]['price'],
								'DiscountPercentage' => $orderData['order_products'][0]['discount_amount'],
								'Quantity' => $orderData['order_products'][0]['quantity'],
								'WorkCostType' => 0,
								'IsWorkCost' => false,
								'EligibleForReverseChargeOnVat' => false,
								'CostCenterItemId1' => null,
								'CostCenterItemId2' => null,
								'CostCenterItemId3' => null,
								// 'Id' => '',
								// 'ProjectId' => $projectId,
							)
						),
			'ShippedDateTime' => null,
			'RotReducedInvoicingType' => 0,
			'RotPropertyType' => null,
			'Persons' => array(),
			'ReverseChargeOnConstructionServices' => false,
		);

		$saveData = json_encode($saveData);

		log_message('debug', 'VISMA CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));


		$ch = curl_init($this->apiURL."/v2/orders");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);

		log_message('debug', 'EACCOUNTING SendInvoiceDraft END = ' . $projectId . ' ' . var_export($result, true));
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma.');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma. Error: '.curl_error($ch));
			return false;
		}

	}

	function sendInvoice($projectId, $orderData){

		$token = $this->getToken($projectId);

		if(!$customerId = $this->checkEaccountingCustomerExists($projectId, $orderData)){
			return false;
		}

		$articles = $this->getArticles($projectId);

        $ArticleId = '';
        $ArticleName = '';

		foreach ($articles['results'] as $key => $article) {
			if ($article['model'] == $orderData['order_products'][0]['model']) {
				$ArticleId = $article['articleId'];
				$ArticleName = $article['name'];
			}
		}

		if ($ArticleId == '') {
			apicenter_logs($projectId, 'projectcontrol', 'Product doesnot exist in Eaccounting', false);
			return false;
		}

		$saveData = array(
			// 'Id' => '',
			'EuThirdParty' => false,
			'IsCreditInvoice' => false,
			'CurrencyCode' => $orderData['currency'],
			'CurrencyRate' => 1,
			// 'CreatedByUserId' => '',
			// 'TotalAmount' => 20,
			// 'TotalVatAmount' => 3,
			// 'TotalRoundings' => 0,
			// 'TotalAmountInvoiceCurrency' => 20,
			// 'TotalVatAmountInvoiceCurrency' => 3,
			// 'SetOffAmountInvoiceCurrency' => 0,
			'CustomerId' => $customerId,
			'Rows' => 	array(
							array(
								// 'DeliveredQuantity' => 0,
								'ArticleId' => $ArticleId,
								'ArticleNumber' => $orderData['order_products'][0]['model'],
								// 'AmountNoVat' => 16,
								// 'PercentVat' => 0.2,
								'LineNumber' => 0,
								'IsTextRow' => false,
								'Text' => $ArticleName,
								'UnitPrice' => 16,
								'UnitAbbreviation' => '',
								'UnitAbbreviationEnglish' => 'pcs',
								'DiscountPercentage' => 0,
								'Quantity' => 1,
								'IsWorkCost' => false,
								'IsVatFree' => false,
								'CostCenterItemId1' => null,
								'CostCenterItemId2' => null,
								'CostCenterItemId3' => null,
								'ProjectId' => null,
								'UnitId' => "66ff2d2e-0ad5-4cf9-a6b9-5528aff90d0b",
								'WorkCostType' => 0,
								'WorkHours' => null,
								'MaterialCosts' => null,
							)
						),
			'VatSpecification' => 	array(
							array(
								'AmountInvoiceCurrency' => 16,
								'VatAmountInvoiceCurrency' => 3,
								'VatPercent' => 0.2,
							)
						),
            'InvoiceDate' => date('Y-m-d'),
            // 'DueDate' => date('Y-m-d', strtotime($orderData['create_at'])),
            // 'DeliveryDate' => date('Y-m-d', strtotime($orderData['create_at'])),
			'RotReducedInvoicingType' => 0,
			'RotReducedInvoicingAmount' => 0,
			'RotReducedInvoicingPercent' => 0,
			'RotReducedInvoicingPropertyName' => null,
			'RotReducedInvoicingOrgNumber' => null,
			'Persons' => [],
			'RotReducedInvoicingAutomaticDistribution' => false,
			'ElectronicReference' => '',
			'ElectronicAddress' => '',
			'EdiServiceDelivererId' => '',
			'OurReference' => '',
			'YourReference' => '',
			'BuyersOrderReference' => '',
			'InvoiceCustomerName' => $orderData['billing_address']['first_name'].' '.$orderData['billing_address']['last_name'],
			'InvoiceAddress1' => $orderData['billing_address']['address1'],
			'InvoiceAddress2' => $orderData['billing_address']['address2'],
			'InvoiceCity' => $orderData['billing_address']['city'],
			'InvoiceCountryCode' => $orderData['billing_address']['country'],			
			'InvoicePostalCode' => $orderData['billing_address']['postcode'],
            'DeliveryCustomerName' => $orderData['shipping_address']['first_name'].' '.$orderData['shipping_address']['last_name'],
			'DeliveryAddress1' => $orderData['shipping_address']['address1'],
			'DeliveryAddress2' => $orderData['shipping_address']['address2'],
			'DeliveryPostalCode' => $orderData['shipping_address']['postcode'],
			'DeliveryCity' => $orderData['shipping_address']['city'],
			'DeliveryCountryCode' => $orderData['shipping_address']['country'],
			'DeliveryMethodName' => null,
			'DeliveryTermName' => null,
			'DeliveryMethodCode' => null,
			'DeliveryTermCode' => null,
			'CustomerIsPrivatePerson' => false,
			// 'TermsOfPaymentId' => '',
			'CustomerEmail' => $orderData['customer']['email'],
			'CustomerNumber' => $orderData['customer']['id'],
			'PaymentReferenceNumber' => null,
			'RotPropertyType' => null,
			'SalesDocumentAttachments' => [],
			'HasAutoInvoiceError' => false,
			'IsNotDelivered' => true,
			'ReverseChargeOnConstructionServices' => false,
			'WorkHouseOtherCosts' => null,
			// 'RemainingAmount' => 20,
			// 'RemainingAmountInvoiceCurrency' => 20,
			'ReferringInvoiceId' => null,
			'CreatedFromOrderId' => null,
			'CreatedFromDraftId' => null,
			// 'VoucherNumber' => '',
			'CreatedUtc' => date('Y-m-d', strtotime($orderData['create_at'])),
			'ModifiedUtc' => date('Y-m-d H:i:s', strtotime($orderData['modified_at'])),
			'ReversedConstructionVatInvoicing' => false,
			'IncludesVat' => false,
			'SendType' => null,
			'PaymentReminderIssued' => false
		);

		$saveData = json_encode($saveData);

		log_message('debug', 'VISMA CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));


		$ch = curl_init($this->apiURL."/v2/customerinvoices");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);

		log_message('debug', 'EACCOUNTING SendInvoiceDraft END = ' . $projectId . ' ' . var_export($result, true));
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma.');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma. Error: '.curl_error($ch));
			return false;
		}

	}

	function sendInvoiceDraft($projectId, $orderData){

		$token = $this->getToken($projectId);

		if(!$customerId = $this->checkEaccountingCustomerExists($projectId, $orderData)){
			return false;
		}

		$articles = $this->getArticles($projectId);

        $ArticleId = '';
        $ArticleName = '';

		foreach ($articles['results'] as $key => $article) {
			if ($article['model'] == $orderData['order_products'][0]['model']) {
				$ArticleId = $article['articleId'];
				$ArticleName = $article['name'];
			}
		}

		if ($ArticleId == '') {
			apicenter_logs($projectId, 'projectcontrol', 'Product doesnot exist in Eaccounting', false);
			return false;
		}

		$saveData = array(
			// 'Id' => '',
			'CustomerId' => $customerId,
			'CreatedUtc' => date('Y-m-d H:i:s', strtotime($orderData['create_at'])),
			'IsCreditInvoice' => false,
			'RotReducedInvoicingType' => 0,
			'RotReducedInvoicingPropertyName' => null,
			'RotReducedInvoicingOrgNumber' => null,
			'RotReducedInvoicingAmount' => 0,
			'RotReducedInvoicingAutomaticDistribution' => false,
			'RotPropertyType' => null,
			'HouseWorkOtherCosts' => null,
            'Rows' => 	array(
							array(
								'LineNumber' => 0,
								'ArticleId' => $ArticleId,
								'ArticleNumber' => $orderData['order_products'][0]['model'],
								'IsTextRow' => false,
								'Text' => $ArticleName,
								'UnitPrice' => 16,
								'DiscountPercentage' => 0,
								'Quantity' => 1,
								'WorkCostType' => 0,
								'IsWorkCost' => false,
								'WorkHours' => 0,
								'MaterialCosts' => 0,
								'ReversedConstructionServicesVatFree' => false,
								'CostCenterItemId1' => null,
								'CostCenterItemId2' => null,
								'CostCenterItemId3' => null,
								'UnitAbbreviation' => '',
								'ProjectId' => null,
								'UnitName' => ""
							)
						),
            'Persons' => array(),
            'OurReference' => '',
			'YourReference' => '',
			'BuyersOrderReference' => '',
			'InvoiceCustomerName' => $orderData['billing_address']['first_name'].' '.$orderData['billing_address']['last_name'],
			'InvoiceAddress1' => $orderData['billing_address']['address1'],
			'InvoiceAddress2' => $orderData['billing_address']['address2'],
			'InvoiceCity' => $orderData['billing_address']['city'],
			'InvoiceCountryCode' => $orderData['billing_address']['country'],			
			'InvoicePostalCode' => $orderData['billing_address']['postcode'],
			'InvoiceCurrencyCode' => $orderData['currency'],
			'DeliveryCustomerName' => $orderData['shipping_address']['first_name'].' '.$orderData['shipping_address']['last_name'],
			'DeliveryAddress1' => $orderData['shipping_address']['address1'],
			'DeliveryAddress2' => $orderData['shipping_address']['address2'],
			'DeliveryPostalCode' => $orderData['shipping_address']['postcode'],
			'DeliveryCity' => $orderData['shipping_address']['city'],
			'DeliveryCountryCode' => $orderData['shipping_address']['country'],
			'DeliveryMethodName' => null,
			'DeliveryTermName' => null,
			'DeliveryMethodCode' => null,
			'DeliveryTermCode' => null,
			'EuThirdParty' => false,
			'CustomerIsPrivatePerson' => false,
			'ReverseChargeOnConstructionServices' => false,
			'SalesDocumentAttachments' => [],
			'InvoiceDate' => date('Y-m-d'),
			'CustomerNumber' => $orderData['customer']['id'],
			'IncludesVat' => false
		);

		$saveData = json_encode($saveData);

		log_message('debug', 'VISMA CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));


		$ch = curl_init($this->apiURL."/v2/customerinvoicedrafts");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);

		log_message('debug', 'EACCOUNTING SendInvoiceDraft END = ' . $projectId . ' ' . var_export($result, true));
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma.');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported order '.$orderData['id'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not export order '.$orderData['id'].' to Visma. Error: '.curl_error($ch));
			return false;
		}

	}

	function checkEaccountingCustomerExists($projectId, $orderData){

		$finalDebtorId = false;
		if($debtorId = $this->checkEaccountingCustomer($projectId, $orderData, 'email')){
			$finalDebtorId = $debtorId;
		}

		if(!$finalDebtorId){
			if($this->createEaccountingCustomer($projectId, $orderData)){
				$finalDebtorId = $this->checkEaccountingCustomer($projectId, $orderData, 'email');
			}
		}
		return $finalDebtorId;
	}

	function checkEaccountingCustomer($projectId, $orderData, $type){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);


		$get_params = '';
		
		if($type == 'email'){
			
			// $get_params = "EmailAddress%20eq%20'koendert@web-company.com'";

			$get_params = "EmailAddress%20eq%20'".$orderData['customer']['email']."'";
		} else {
			return false;
		}

		$ch = curl_init($this->apiURL.'/v2/customers?$filter='.$get_params);

		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);


		curl_close($ch);
		$result = json_decode($result, true);
		
		log_message('debug', 'EACCOUNTING CheckCustomer END = ' . $projectId . ' ' . var_export($result, true));
		
		if(empty($result)){
			return false;
		}
		$customer = $result['Data'][0];


		if(isset($customer['Id'])){
			return $customer['Id'];
		} else {
			return false;
		}		
	}

	function createEaccountingCustomer($projectId, $orderData){


		$token = $this->getToken($projectId);


		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);
		
		if($orderData['billing_address']['company'] != ''){
			$name = $orderData['billing_address']['company'];
		} else {
			$name = $orderData['customer']['first_name'].' '.$orderData['customer']['last_name'];
		}
		
		$saveData = array(
			'CorporateIdentityNumber' => '',
			'ContactPersonEmail' => '',
			'ContactPersonMobile' => '',
			'ContactPersonName' => '',
			'ContactPersonPhone' => '',
			'CurrencyCode' => $orderData['currency'],
			'EmailAddress' => $orderData['customer']['email'],
			'InvoiceAddress1' => $orderData['billing_address']['address1'],
			'InvoiceAddress2' => $orderData['billing_address']['address2'],
			'InvoiceCity' => $orderData['billing_address']['city'],
			'InvoiceCountryCode' => $orderData['billing_address']['country'],
			'InvoicePostalCode' => $orderData['billing_address']['postcode'],
			'DeliveryCustomerName' => $orderData['shipping_address']['first_name'].' '.$orderData['shipping_address']['last_name'],
			'DeliveryAddress1' => $orderData['shipping_address']['address1'],
			'DeliveryAddress2' => $orderData['shipping_address']['address2'],
			'DeliveryPostalCode' => $orderData['shipping_address']['postcode'],
			'DeliveryCity' => $orderData['shipping_address']['city'],
			'DeliveryCountryCode' => $orderData['shipping_address']['country'],
			'DeliveryMethodId' => null,
			'DeliveryTermId' => null,
			'Name' => $orderData['customer']['first_name'].' '.$orderData['customer']['last_name'],
			'Note' => '',
			'ReverseChargeOnConstructionServices' => false,
			'WebshopCustomerNumber' => null,
			'MobilePhone' => '',
			'Telephone' => $orderData['billing_address']['phone'],
			'TermsOfPaymentId' => '255aaa62-67ff-4241-985c-b84a7f83c364',
			'TermsOfPayment' => array(
				'Name' => '',
				'NameEnglish' => '',
				'NumberOfDays' => 10,
				'TermsOfPaymentTypeId' => 0,
				'TermsOfPaymentTypeText' => null,
				'AvailableForSales' => false,
				'AvailableForPurchase' => false
			),
			'VatNumber' => '',
			'WwwAddress' => '',
			'LastInvoiceDate' => null,
			'IsPrivatePerson' => false,
			'DiscountPercentage' => 0,
			'ChangedUtc' => date('Y-m-d H:i:s', strtotime($orderData['modified_at'])),
			'IsActive' => true,
			'ForceBookkeepVat' => false,
			'EdiGlnNumber' => '',
			'SalesDocumentLanguage' => 'nl',
			'ElectronicAddress' => null,
			'ElectronicReference' => null,
			'EdiServiceDelivererId' => null,
			'AutoInvoiceActivationEmailSentDate' => null,
			'AutoInvoiceRegistrationRequestSentDate' => null,
			'EmailAddresses' => [],
			'CustomerLabels' => null,
			'IsFutureInvoiceDateAllowed' => true			
		);
		
		$saveData = json_encode($saveData);
        
        log_message('debug', 'EACCOUNTING CreateDebtor = ' . $projectId . ' ' . var_export($saveData, true));
        
		$ch = curl_init($this->apiURL."/v2/customers");
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $saveData);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Content-Type: application/json",
			"Authorization: Bearer " . $token
		));
		$result = curl_exec($ch);
		// echo '<pre>';print_r($result);exit;
		
		log_message('debug', 'EACCOUNTING CreateDebtor END = ' . $projectId . ' ' . var_export($result, true));
		
		
		if(!curl_errno($ch)){
			$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			if($http_code != '201'){
				api2cart_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in Visma');
				return false;
			}
			api2cart_log($projectId, 'exportorders', 'Exported customer '.$customerData['first_name'].' '.$customerData['last_name'].' to Visma.');
			return true;
		} else {
			api2cart_log($projectId, 'exportorders', 'Could not create customer '.$customerData['first_name'].' '.$customerData['last_name'].' in Visma. Error: '.curl_error($ch));
			return false;
		}
	}

	function getItemData($projectId, $item){
		$token = $this->getToken($projectId);
		$companyId = $this->Projects_model->getValue('eaccounting_company_id', $projectId);
		
		$ch = curl_init($this->apiURL."/inventory/".$item['model']);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			"Accept: application/json",
			"ipp-company-id: ".$companyId,
			"ipp-application-type: Visma.net Financials",
			"Authorization: Bearer " . $token
		));
		 
		$result = curl_exec($ch);
		curl_close($ch);
		$result = json_decode($result, true);
		return $result;
	}
	
	
}