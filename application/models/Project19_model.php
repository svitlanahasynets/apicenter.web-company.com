<?php
class Project19_model extends CI_Model {

	public $projectId;

    function __construct()
    {
        parent::__construct();
        $this->projectId = 19;
    }
	
	public function getArticleData($articleData, $finalArticleData){
		$finalData = $finalArticleData;
	
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '')
		// {
			// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'dropdown',
				// 'value' => $articleData['AFAS_ATTRIBUUT_CODE']
			// );
		// }
		
		// if(isset($articleData['AFAS_ATTRIBUUT_CODE']) && $articleData['AFAS_ATTRIBUUT_CODE'] != '')
		// {
			// $finalData['custom_attributes']['MAGENTO_ATTRIBUUT_CODE'] = array(
				// 'type' => 'text',
				// 'value' => $articleData['AFAS_ATTRIBUUT_CODE']
			// );
		// }

		//Aanduiding
		if(isset($articleData['Aanduiding']) && $articleData['Aanduiding'] != '')
		{
			$finalData['custom_attributes']['aanduiding'] = array(
				'type' => 'text',
				'value' => $articleData['Aanduiding']
			);
		}
		//Aantal Inner Carton
		if(isset($articleData['Aantal_Inner_Carton']) && $articleData['Aantal_Inner_Carton'] != '')
		{
			$finalData['custom_attributes']['aantal_inner_carton'] = array(
				'type' => 'text',
				'value' => $articleData['Aantal_Inner_Carton']
			);
		}
		//Aantal Laptops
		if(isset($articleData['Aantal_Laptops']) && $articleData['Aantal_Laptops'] != '')
		{
			$finalData['custom_attributes']['aantal_laptops_tabets'] = array(
				'type' => 'text',
				'value' => $articleData['Aantal_Laptops']
			);
		}
		//Aantal Monitoren
		if(isset($articleData['Aantal_Monitoren']) && $articleData['Aantal_Monitoren'] != '')
		{
			$finalData['custom_attributes']['aantal_monitoren'] = array(
				'type' => 'text',
				'value' => $articleData['Aantal_Monitoren']
			);
		}
		//Aantal Outer Carton
		if(isset($articleData['Aantal_Outer_Carton']) && $articleData['Aantal_Outer_Carton'] != '')
		{
			$finalData['custom_attributes']['aantal_outer_carton'] = array(
				'type' => 'text',
				'value' => $articleData['Aantal_Outer_Carton']
			);
		}
		//Aantal Stroom
		if(isset($articleData['Aantal_Stroom']) && $articleData['Aantal_Stroom'] != '')
		{
			$finalData['custom_attributes']['aantal_stroom'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Aantal_Stroom']
			);
		}
		//Advies Verkoopprijs
		if(isset($articleData['Advies_Verkoopprijs']) && $articleData['Advies_Verkoopprijs'] != '')
		{
			$finalData['custom_attributes']['adviesverkoopprijs'] = array(
				'type' => 'text',
				'value' => $articleData['Advies_Verkoopprijs']
			);
		}
		//Afmetingen
		if(isset($articleData['Afmetingen']) && $articleData['Afmetingen'] != '')
		{
			$finalData['custom_attributes']['afmetingen'] = array(
				'type' => 'text',
				'value' => $articleData['Afmetingen']
			);
		}
		//Afmetingen Klokken
		if(isset($articleData['Afmetingen_Klokken']) && $articleData['Afmetingen_Klokken'] != '')
		{
			$finalData['custom_attributes']['afmetingen_klokken'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Afmetingen_Klokken']
			);
		}
		//Afm Weegplateau		
		if(isset($articleData['Weegplateau']) && $articleData['Weegplateau'] != '')
		{
			$finalData['custom_attributes']['afmetingen_weegplateau'] = array(
				'type' => 'text',
				'value' => $articleData['Weegplateau']
			);
		}
		//Opening Bureaublad
		if(isset($articleData['Opening_Bureaublad']) && $articleData['Opening_Bureaublad'] != '')
		{
			$finalData['custom_attributes']['benodigdeopeningbureaublad'] = array(
				'type' => 'text',
				'value' => $articleData['Opening_Bureaublad']
			);
		}
		//Besteleenheid Magento
		if(isset($articleData['Besteleenheid_Magento']) && $articleData['Besteleenheid_Magento'] != '')
		{
			$finalData['custom_attributes']['besteleenheid'] = array(
				'type' => 'text',
				'value' => $articleData['Besteleenheid_Magento']
			);
		}
		//Binnenmaat
		if(isset($articleData['Binnenmaat']) && $articleData['Binnenmaat'] != '')
		{
			$finalData['custom_attributes']['binnenmaat'] = array(
				'type' => 'text',
				'value' => $articleData['Binnenmaat']
			);
		}
		//Breedte
		if(isset($articleData['Breedte']) && $articleData['Breedte'] != '')
		{
			$finalData['custom_attributes']['breedte'] = array(
				'type' => 'text',
				'value' => $articleData['Breedte']
			);
		}
		//Brut. Gew.
		if(isset($articleData['Brutogewicht']) && $articleData['Brutogewicht'] != '')
		{
			$finalData['custom_attributes']['brutogewicht'] = array(
				'type' => 'text',
				'value' => $articleData['Brutogewicht']
			);
		}
		//Buitenmaat
		if(isset($articleData['Buitenmaat']) && $articleData['Buitenmaat'] != '')
		{
			$finalData['custom_attributes']['buitenmaat'] = array(
				'type' => 'text',
				'value' => $articleData['Buitenmaat']
			);
		}
		//Kleur
		if(isset($articleData['Kleur']) && $articleData['Kleur'] != '')
		{
			$finalData['custom_attributes']['color'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Kleur']
			);
		}
		//Omschr Magento
		if(isset($articleData['Omschr_Magento']) && $articleData['Omschr_Magento'] != '')
		{
		    $desc_mag = str_replace('|br|', '', $articleData['Omschr_Magento']);
		    
			$finalData['custom_attributes']['description'] = array(
				'type' => 'text',
				'value' => $desc_mag,
			);
		}
		//Diepte
		if(isset($articleData['Diepte']) && $articleData['Diepte'] != '')
		{
			$finalData['custom_attributes']['diepte'] = array(
				'type' => 'text',
				'value' => $articleData['Diepte']
			);
		}
		//Draagkracht
		if(isset($articleData['Draagkracht']) && $articleData['Draagkracht'] != '')
		{
			$finalData['custom_attributes']['draagkracht'] = array(
				'type' => 'text',
				'value' => $articleData['Draagkracht']
			);
		}
		//Barcode 
		if(isset($articleData['Barcode']) && $articleData['Barcode'] != '')
		{
		    $barc = str_replace('|br|', '', $articleData['Barcode']);
		    
			$finalData['custom_attributes']['ean_code'] = array(
				'type' => 'text',
				'value' => $barc,
			);
		}
		//Extra functies
		if(isset($articleData['Extra_functies']) && $articleData['Extra_functies'] != '')
		{
			$finalData['custom_attributes']['extra_functies'] = array(
				'type' => 'text',
				'value' => $articleData['Extra_functies']
			);
		}
		//Lamp Fitting
		if(isset($articleData['Lamp_Fitting']) && $articleData['Lamp_Fitting'] != '')
		{
			$finalData['custom_attributes']['fitting'] = array(
				'type' => 'text',
				'value' => $articleData['Lamp_Fitting']
			);
		}
		//Formaat
		if(isset($articleData['Formaat']) && $articleData['Formaat'] != '')
		{
			$finalData['custom_attributes']['formaat'] = array(
				'type' => 'text',
				'value' => $articleData['Formaat']
			);
		}
		//Hechtkracht
		if(isset($articleData['Hechtkracht']) && $articleData['Hechtkracht'] != '')
		{
			$finalData['custom_attributes']['hechtkracht'] = array(
				'type' => 'text',
				'value' => $articleData['Hechtkracht']
			);
		}
		//Hoogte
		if(isset($articleData['Hoogte']) && $articleData['Hoogte'] != '')
		{
			$finalData['custom_attributes']['hoogte'] = array(
				'type' => 'text',
				'value' => $articleData['Hoogte']
			);
		}
		//Keurmerk
		if(isset($articleData['Keurmerk']) && $articleData['Keurmerk'] != '')
		{
			$finalData['custom_attributes']['keurmerk'] = array(
				'type' => 'text',
				'value' => $articleData['Keurmerk']
			);
		}
		//Klemwijdte
		if(isset($articleData['Klemwijdte']) && $articleData['Klemwijdte'] != '')
		{
			$finalData['custom_attributes']['klemwijdte'] = array(
				'type' => 'text',
				'value' => $articleData['Klemwijdte']
			);
		}
		//Land van Herkomst
		if(isset($articleData['Herkomst']) && $articleData['Herkomst'] != '')
		{
			$finalData['custom_attributes']['land_herkomst'] = array(
				'type' => 'text',
				'value' => $articleData['Herkomst']
			);
		}
		//Lengte
		if(isset($articleData['Lengte']) && $articleData['Lengte'] != '')
		{
			$finalData['custom_attributes']['lengte'] = array(
				'type' => 'text',
				'value' => $articleData['Lengte']
			);
		}
		//Lengte Messen
		if(isset($articleData['Lengte_Messen']) && $articleData['Lengte_Messen'] != '')
		{
			$finalData['custom_attributes']['lengte_messen'] = array(
				'type' => 'text',
				'value' => $articleData['Lengte_Messen']
			);
		}
		//Leverbaar vanaf
		if(isset($articleData['Leverbaar']) && $articleData['Leverbaar'] != '')
		{
			$finalData['custom_attributes']['leverbaar_vanaf'] = array(
				'type' => 'text',
				'value' => $articleData['Leverbaar']
			);
		}
		//Lamp Lichttemperatuur
		if(isset($articleData['Lichttemp']) && $articleData['Lichttemp'] != '')
		{
			$finalData['custom_attributes']['lichttemperatuur'] = array(
				'type' => 'text',
				'value' => $articleData['Lichttemp']
			);
		}
		//Lamp Lumen
		if(isset($articleData['Lumen']) && $articleData['Lumen'] != '')
		{
			$finalData['custom_attributes']['lumen'] = array(
				'type' => 'text',
				'value' => $articleData['Lumen']
			);
		}
		//Merk
		if(isset($articleData['Merk']) && $articleData['Merk'] != '')
		{
			$finalData['custom_attributes']['manufacturer'] = array(
				'type' => 'dropdown',
				'value' => $articleData['Merk']
			);
		}
		//Materiaal
		if(isset($articleData['Materiaal']) && $articleData['Materiaal'] != '')
		{
			$finalData['custom_attributes']['materiaal'] = array(
				'type' => 'text',
				'value' => $articleData['Materiaal']
			);
		}		
		//Materiaal behuizing
		if(isset($articleData['Materiaal_behuizing']) && $articleData['Materiaal_behuizing'] != '')
		{
			$finalData['custom_attributes']['materiaal_behuizing'] = array(
				'type' => 'text',
				'value' => $articleData['Materiaal_behuizing']
			);
		}
		// //Omschrijving
		// if(isset($articleData['Omschrijving']) && $articleData['Omschrijving'] != '')
		// {
			// $finalData['custom_attributes']['name'] = array(
				// 'type' => 'text',
				// 'value' => $articleData['Omschrijving']
			// );
		// }
		//OEM Code
		if(isset($articleData['OEM_Code']) && $articleData['OEM_Code'] != '')
		{
			$finalData['custom_attributes']['oem_code'] = array(
				'type' => 'text',
				'value' => $articleData['OEM_Code']
			);
		}		
		//PDF Bestand
		if(isset($articleData['PDF_Bestand']) && $articleData['PDF_Bestand'] != '')
		{
			$finalData['custom_attributes']['pdf_bestand'] = array(
				'type' => 'text',
				'value' => $articleData['PDF_Bestand']
			);
		}		
		//PDF Handleiding
		if(isset($articleData['PDF_Handleiding']) && $articleData['PDF_Handleiding'] != '')
		{
			$finalData['custom_attributes']['pdf_handleiding'] = array(
				'type' => 'text',
				'value' => $articleData['PDF_Handleiding']
			);
		}		
		//(Product)Serie
		if(isset($articleData['_Product_Serie']) && $articleData['_Product_Serie'] != '')
		{
			$finalData['custom_attributes']['product_serie'] = array(
				'type' => 'dropdown',
				'value' => $articleData['_Product_Serie']
			);
		}		
		// //Opm.
		// if(isset($articleData['Opm.']) && $articleData['Opm.'] != '')
		// {
			// $finalData['custom_attributes']['short_description'] = array(
				// 'type' => 'text',
				// 'value' => $articleData['Opm.']
			// );
		// }		
		// //itemcode
		// if(isset($articledata['itemcode']) && $articledata['itemcode'] != '')
		// {
			// $finaldata['custom_attributes']['sku'] = array(
				// 'type' => 'text',
				// 'value' => $articledata['itemcode']
			// );
		// }
		//Toepassing
		if(isset($articleData['Toepassing']) && $articleData['Toepassing'] != '')
		{
			$finalData['custom_attributes']['toepassing'] = array(
				'type' => 'text',
				'value' => $articleData['Toepassing']
			);
		}
		//Type
		 if(isset($articleData['Type_item']) && $articleData['Type_item'] != '')
		 {
			 $finalData['custom_attributes']['type_eigenschap'] = array(
				 'type' => 'text',
				 'value' => $articleData['Type_item']
			 );
		 }
		 //Uitlopend
		if(isset($articleData['Uitlopend']) && $articleData['Uitlopend'] != '')
		{
			if($articleData['Uitlopend'] == "false")
			{
				$finalData['custom_attributes']['uitlopend'] = array(
				'type' => 'text',
				'value' => 0
				);
			}
			else
			{
				$finalData['custom_attributes']['uitlopend'] = array(
				'type' => 'text',
				'value' => 1
				);
			}
		}
		 //Vermogen
		 if(isset($articleData['Vermogen']) && $articleData['Vermogen'] != '')
		 {
			 $finalData['custom_attributes']['vermogen'] = array(
				 'type' => 'text',
				 'value' => $articleData['Vermogen']
			 );
		 }
		 //Verwachte levertijd
		 if(isset($articleData['Verwachte_levertijd']) && $articleData['Verwachte_levertijd'] != '')
		 {
			 $finalData['custom_attributes']['verwachte_levertijd'] = array(
				 'type' => 'dropdown',
				 'value' => $articleData['Verwachte_levertijd']
			 );
		 }
		 
		 //Volume / Inhoud
		 if(isset($articleData['Volume___Inhoud']) && $articleData['Volume___Inhoud'] != '')
		 {
			 $finalData['custom_attributes']['volume_inhoud'] = array(
				 'type' => 'text',
				 'value' => $articleData['Volume___Inhoud']
			 );
		 }
		 //Net. Gew.
		 if(isset($articleData['Nettogewicht']) && $articleData['Nettogewicht'] != '')
		 {
			 $finalData['custom_attributes']['weight'] = array(
				 'type' => 'text',
				 'value' => $articleData['Nettogewicht']
			 );
		 }
		 //Youtube Link
		 if(isset($articleData['Youtube_Link']) && $articleData['Youtube_Link'] != '')
		 {
			 $finalData['custom_attributes']['youtube_link'] = array(
				 'type' => 'text',
				 'value' => $articleData['Youtube_Link']
			 );
		 }
		 
		 
		//Stores
		if(isset($articleData['Stores']) && $articleData['Stores'] != '')
		{
			$finalData['tmp_data']['Stores'] = $articleData['Stores'];
		}
		if(isset($articleData['In2Brands_Shop']) && $articleData['In2Brands_Shop'] != '')
		{
			$finalData['tmp_data']['In2Brands_Shop'] = $articleData['In2Brands_Shop'];
		}
		if(isset($articleData['Filex_Shop']) && $articleData['Filex_Shop'] != '')
		{
			$finalData['tmp_data']['Filex_Shop'] = $articleData['Filex_Shop'];
		}
		if(isset($articleData['BE_Shop']) && $articleData['BE_Shop'] != '')
		{
			$finalData['tmp_data']['BE_Shop'] = $articleData['BE_Shop'];
		}
		
		//Koppeling tussen InShop
		if(isset($articleData['Op_Magento_tonen']) && $articleData['Op_Magento_tonen'] != '')
		{
			if($articleData['Op_Magento_tonen'] == false || $articleData['Op_Magento_tonen'] == 'false')
			{
    			$finalData['tmp']['status'] = 2;
			} else {
				$finalData['tmp']['status'] = 1;
			}
		}
		
		
		return $finalData;
	}
	
	public function getCustomerData($afasCustomerData)
	{
		if(isset($afasCustomerData['Webshop']) && ($afasCustomerData['Webshop'] == false || $afasCustomerData['Webshop'] == 'false'))
		{
			$afasCustomerData['Blocked'] = true;
		}
		
		return $afasCustomerData;
	}
	
    public function checkConfigurable($saveData, $productData, $projectId, $type = '')
	{
		// Connect to stores
/*
		if(isset($productData['Stores']))
		{
			if($productData['Stores'] == 'In2Brands')
			{
				$saveData['product']['extension_attributes']['website_ids'] = array(1);
			}
			elseif($productData['Stores'] == 'BrandErgonomics')
			{
				$saveData['product']['extension_attributes']['website_ids'] = array(3);
			} 
			elseif($productData['Stores'] == 'Filex')
			{
				$saveData['product']['extension_attributes']['website_ids'] = array(2);
			} 
			else 
			{
				$saveData['product']['extension_attributes']['website_ids'] = array();
			}
		} 
*/

		$websiteIds = array();
		if(isset($productData['tmp_data']) && isset($productData['tmp_data']['In2Brands_Shop']) && $productData['tmp_data']['In2Brands_Shop'] == 'true'){
			$websiteIds[] = 1;
		}
		if(isset($productData['tmp_data']) && isset($productData['tmp_data']['Filex_Shop']) && $productData['tmp_data']['Filex_Shop'] == 'true'){
			$websiteIds[] = 3;
		}
		if(isset($productData['tmp_data']) && isset($productData['tmp_data']['BE_Shop']) && $productData['tmp_data']['BE_Shop'] == 'true'){
			$websiteIds[] = 2;
		}
		$saveData['product']['extension_attributes']['website_ids'] = $websiteIds;
		
		
		if(isset($productData['tmp']) && isset($productData['tmp']['status']))
		{
   			$saveData['product']['status'] = $productData['tmp']['status'];
		}
		unset($productData['tmp']);
		
		return $saveData;
    }
    
	public function loadCategories($finalArticleData, $article, $projectId){
		if($article['CategoryId'] != ''){
			$finalArticleData['categories_ids'] = $article['CategoryId'];
		}
		return $finalArticleData;
	}
    
    public function loadCustomOrderAttributes($appendItem, $order, $projectId){
	    if(isset($order['extension_attributes']['bold_order_comment'])){
		    $appendItem['custom_order_attributes'][] = array(
			    'RfCs' => $order['extension_attributes']['bold_order_comment']
		    );
	    }
	    return $appendItem;
    }
    
	public function setOrderParams($fields, $orderData){
		if(isset($orderData['custom_order_attributes']) && !empty($orderData['custom_order_attributes'])){
			foreach($orderData['custom_order_attributes'] as $customFields){
				foreach($customFields as $fieldCode => $fieldValue){
					$fields->$fieldCode = $fieldValue;
				}
			}
		}
		
		$fields->War = 'Oss';
// 		unset($fields->War);
    
        $fields->SsId = 'Europlus';
	}
	
	public function setOrderProductParams($fields, $item){
		$afasEnvironment = $this->Projects_model->getValue('afas_environment', $this->projectId);
		$afasEnvironmentId = $this->Projects_model->getValue('afas_environment_id', $this->projectId);
		$afasToken = $this->Projects_model->getValue('afas_token', $this->projectId);
		$afasGetUrl = $this->Projects_model->getValue('afas_get_url', $this->projectId);
		$afasArticleConnector = $this->Projects_model->getValue('afas_article_connector', $this->projectId);
		
		$this->load->helper('NuSOAP/nusoap');
		$client = new nusoap_client($afasGetUrl, true);
		$client->setUseCurl(true);
		$client->useHTTPPersistentConnection();
		
		$xml_array['environmentId'] = $afasEnvironmentId;
		$xml_array['token'] = $afasToken;
		$xml_array['connectorId'] = $afasArticleConnector;
		$xml_array['filtersXml'] = '<Filters><Filter FilterId="Filter1"><Field FieldId="ItemCode" OperatorType="1">'.$item['model'].'</Field></Filter></Filters>';
		$xml_array['options'] = '<options><Outputmode>1</Outputmode><Metadata>1</Metadata><Outputoptions>2</Outputoptions><Skip>0</Skip><Take>1</Take><Index><Field FieldId="ItemCode" OperatorType="1" /></Index></options>';
		
		$result = $client->call('GetDataWithOptions', array('parameters' => $xml_array), '', '', false, true);
		$resultData = $result["GetDataWithOptionsResult"];
		$resultData = str_replace("\n", '|br|', $resultData);
		$resultData = str_replace('</AfasGetConnector>|br|', '</AfasGetConnector>', $resultData);
		$resultData = preg_replace('/[^(\x20-\x7f)]*/s','',$resultData);

		$data = simplexml_load_string($resultData);
// 		echo '<pre>';print_r($data);exit;
		
		$assortimenten = array();
		if(isset($data->$afasArticleConnector) && count($data->$afasArticleConnector) > 0){
			$afasData = $data->$afasArticleConnector;
			$wareHouse = '*****';
			if(isset($afasData->Locatie)){
				$wareHouse = (string)$afasData->Locatie;
			}
			$fields->War = $wareHouse;
		}
	}
	
	public function setDeliveryAddressData($orderData, $xmlOrganisation){
		$orderBillingAddress = $orderData['shipping_address'];
		$company = isset($orderBillingAddress['company']) ? $orderBillingAddress['company'] : '';
		if($company != ''){
			$department = $company.' tav '.$orderBillingAddress['first_name'].' '.$orderBillingAddress['last_name'];
		} else {
			$department = $orderBillingAddress['first_name'].' '.$orderBillingAddress['last_name'];
		}
		$xmlOrganisation->Element->Objects->KnContact->Element->Fields->ExAd = $department;
		return $xmlOrganisation;
	}
}