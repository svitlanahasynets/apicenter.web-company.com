<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$config['project_settings'] = array(
	array(
		'code' => 'cms',
		'title' => 'Webshop system',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'magento2' 		=> 'Magento 2',
			'WooCommerce' 	=> 'WooCommerce',
			'Amazon' 		=> 'Amazon',
			'mailchimp' 	=> 'Mailchimp',
			'vtiger' 		=> 'Vtiger',
			'lightspeed'	=> 'Lightspeed'
		),
	),
	array(
		'code' => 'user',
		'title' => 'Gebruikersnaam',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'magento2'
		)
	),
	array(
		'code' => 'password',
		'title' => 'Wachtwoord',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'magento2'
		)
	),
	array(
		'code' => 'enabled',
		'title' => 'Is koppeling ingeschakeld?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
	),
	array(
		'code' => 'articles_enabled',
		'title' => 'Artikel import ingeschakeld?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
	),
	array(
		'code' => 'stock_enabled',
		'title' => 'Voorraad import ingeschakeld?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		)
	),
	array(
		'code' => 'customers_enabled',
		'title' => 'Klant import ingeschakeld?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
	),
	array(
		'code' => 'orders_enabled',
		'title' => 'Order export ingeschakeld?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
	),
	array(
		'code' => 'afas_article_connector',
		'title' => 'AFAS article connector ID',
		'type' => 'text',
		'default' => 'Profit_Article_Basic_App',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'article_interval',
		'title' => 'Get articles every X minutes',
		'type' => 'text'
	),
	array(
		'code' => 'article_amount',
		'title' => 'Get X articles per batch',
		'type' => 'text'
	),
	array(
		'code' => 'afas_last_update_date',
		'title' => 'AFAS load articles from date',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_enable_article_enabled_filter',
		'title' => 'Inschakelen "enabled" filter?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'stock_interval',
		'title' => 'Get stock articles every X minutes',
		'type' => 'text',
/*
		'depends_on' => array(
			'erp_system' => 'afas'
		)
*/
	),
	array(
		'code' => 'stock_amount',
		'title' => 'Get X stock articles per batch',
		'type' => 'text',
/*
		'depends_on' => array(
			'erp_system' => 'afas'
		)
*/
	),
	array(
		'code' => 'afas_customers_connector',
		'title' => 'AFAS customers connector ID',
		'type' => 'text',
		'default' => 'Profit_Debtor',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'customers_interval',
		'title' => 'Get customers every X minutes',
		'type' => 'text'
	),
	array(
		'code' => 'customers_amount',
		'title' => 'Get X customers per batch',
		'type' => 'text'
	),
	array(
		'code' => 'afas_orders_connector',
		'title' => 'AFAS orders connector ID',
		'type' => 'text',
		'default' => 'FbSales',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'orders_interval',
		'title' => 'Export orders every X minutes',
		'type' => 'text'
	),
	array(
		'code' => 'orders_amount',
		'title' => 'Export X orders per batch',
		'type' => 'text'
	),
	array(
		'code' => 'orders_offset',
		'title' => 'Current order offset',
		'type' => 'text'
	),
	array(
		'code' => 'orders_type',
		'title' => 'Send orders as',
		'type' => 'select',
		'values' => array(
			'FbSales' => 'SalesOrder',
			'FbDirectInvoice' => 'SalesInvoice'
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_customers_payment_condition',
		'title' => 'AFAS create customer - payment condition',
		'type' => 'text',
		'default' => '14',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_customers_control_account',
		'title' => 'AFAS create customer - control account',
		'type' => 'text',
		'default' => '1400',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_orders_administration',
		'title' => 'AFAS export order - administration',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_shipping_sku',
		'title' => 'AFAS itemcode voor verzending',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_environment',
		'title' => 'AFAS environment',
		'type' => 'text',
		'default' => 'AOL',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_environment_id',
		'title' => 'AFAS environment ID',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_token',
		'title' => 'AFAS token',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_get_url',
		'title' => 'AFAS GET connector URL',
		'type' => 'text',
		'default' => 'https://[ENVIRONMENT_ID].afasonlineconnector.nl/profitservices/appconnectorget.asmx?wsdl',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'afas_update_url',
		'title' => 'AFAS UPDATE connector URL',
		'type' => 'text',
		'default' => 'https://[ENVIRONMENT_ID].afasonlineconnector.nl/profitservices/appconnectorupdate.asmx?wsdl',
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'exactonline_redirect_url',
		'title' => 'Exact Online redirect URL',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_client_id',
		'title' => 'Exact Online client ID',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_secret_key',
		'title' => 'Exact Online secret key',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_webhook_secret_key',
		'title' => 'Exact Online webhook secret key',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_administration_id',
		'title' => 'Exact Online administration ID',
		'type' => 'text',
		'default' => 'default',
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_delete_webhooks',
		'title' => 'WebHooks opnieuw toevoegen bij volgende cronjob uitvoering?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_import_all_products',
		'title' => 'Importeer alle producten opnieuw?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'exactonline_import_all_customers',
		'title' => 'Importeer alle klanten opnieuw?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'exactonline'
		)
	),
	array(
		'code' => 'visma_client_id',
		'title' => 'Visma client ID',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'visma'
		)
	),
	array(
		'code' => 'visma_secret_key',
		'title' => 'Visma secret key',
		'type' => 'text',
		'depends_on' => array(
			'erp_system' => 'visma'
		)
	),
	array(
		'code' => 'visma_company_id',
		'title' => 'Visma company ID',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'erp_system' => 'visma'
		)
	),
	array(
		'code' => 'force_in_stock',
		'title' => 'Product altijd instellen als "op voorraad"?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		)
	),
	array(
		'code' => 'enable_custom_category_logic',
		'title' => 'Aangepaste categorie logica inschakelen?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'custom_category_logic',
		'title' => 'Aangepaste categorie logica',
		'type' => 'table',
		'default' => '',
		'fields' => array(
			'level' => 'Level',
			'code' => 'Code',
			'connector' => 'GET Connector'
		),
		'depends_on' => array(
			'enable_custom_category_logic' => '1',
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'enable_category_conversion_table',
		'title' => 'Categorie omzettabel inschakelen?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'enable_project_category_logic',
		'title' => 'Category logica op basis van project-specifieke code inschakelen?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		)
	),
	array(
		'code' => 'category_conversion_table',
		'title' => 'Categorie omzettabel',
		'type' => 'table',
		'default' => '',
		'fields' => array(
			'afas_id' => 'AFAS categorie ID',
			'shop_id' => 'Shop categorie ID'
		),
		'depends_on' => array(
			'enable_category_conversion_table' => '1',
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'enable_attribute_set_conversion_table',
		'title' => 'Attribuut set omzettabel inschakelen?',
		'type' => 'select',
		'default' => '0',
		'values' => array(
			'0' => 'Nee',
			'1' => 'Ja'
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'attribute_set_conversion_table',
		'title' => 'Attribuut set omzettabel',
		'type' => 'table',
		'default' => '',
		'fields' => array(
			'afas_id' => 'AFAS attribuut set ID',
			'shop_id' => 'Shop attribuut set name'
		),
		'depends_on' => array(
			'enable_attribute_set_conversion_table' => '1',
			'erp_system' => 'afas'
		)
	),
	array(
		'code' => 'default_debtor_fields',
		'title' => 'Standaard debiteur velden meesturen',
		'type' => 'table',
		'default' => '',
		'fields' => array(
			'code' => 'Code',
			'waarde' => 'Waarde',
		),
		'depends_on' => array(
			'erp_system' => 'afas'
		)
	),

	//  setting added by manish woocommerce

	array(
		'code' => 'woocommerce_api_consumer_key',
		'title' => 'WooCommerce api consumer key',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		)
	),

	array(
		'code' => 'woocommerce_api_consumer_secret',
		'title' => 'WooCommerce api consumer secret',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		)
	),
	array(
		'code' => 'woocommerce_betaalkosten_sku',
		'title' => 'WooCommerce BetaalKosten Sku',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		)
	),
	array(
		'code' => 'woocommerce_verzending_sku',
		'title' => 'WooCommerce Verzending Sku',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		)
	),
	array(
		'code' 	=> 'woocommerce_order_status',
		'title' => 'WooCommerce  Import order of status',
		'type' 	=> 'select',
		'default' => '0',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		),
		'values' => array(
			'pending' 		=> 'Pending',
			'completed' 	=> 'Completed',
			'processing' 	=> 'Processing',
			'on-hold' 		=> 'On Hold',
			'refunded' 		=> 'Refunded'
		),
	),
	array(
		'code' 	=> 'woocommerce_prices_are',
		'title' => 'WooCommerce prices are',
		'type' 	=> 'select',
		'default' => '0',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		),
		'values' => array(
			'including' 	=> 'Inclusief BTW',
			'excluding' 	=> 'Exclusief BTW'
		),
	),
	array(
		'code' 	=> 'woocommerce_import_option',
		'title' => 'WooCommerce import option',
		'type' 	=> 'select',
		'default' => '0',
		'depends_on' => array(
			'cms' => 'WooCommerce'
		),
		'values' => array(
			'orders' 	=> 'Import Orders',
			'invoices' 	=> 'Import Invoices'
		),
	),
	array(
		'code' => 'AWS_ACCESS_KEY_ID',
		'title' => 'Amazon AWS_ACCESS_KEY_ID',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'Amazon'
		)
	),
	array(
		'code' => 'AWS_SECRET_ACCESS_KEY',
		'title' => 'Amazon AWS_SECRET_ACCESS_KEY',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'Amazon'
		)
	),
	array(
		'code' => 'MERCHANT_ID',
		'title' => 'Amazon MERCHANT_ID',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'Amazon'
		)
	),
	array(
		'code' => 'MARKETPLACEID',
		'title' => 'Amazon MARKETPLACEID',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'Amazon'
		)
	),
	array(
		'code' => 'mailchimp_api',
		'title' => 'Mailchimp Api',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'mailchimp'
		)
	),
	array(
		'code' => 'mailchimp_list_id',
		'title' => 'Mailchimp list id',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'mailchimp'
		)
	),
	array(
		'code' => 'vtiger_username',
		'title' => 'Vtiger Username',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'vtiger'
		)
	),
	array(
		'code' => 'vtiger_useraccesskey',
		'title' => 'Vtiger Useraccesskey',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'vtiger'
		)
	),
	array(
		'code' => 'vtiger_exact_vatcode',
		'title' => 'Vtiger Exact Vat code',
		'type' => 'text',
		'depends_on' => array(
			'cms' => 'vtiger'
		)
	),
	array(
		'code' 	=> 'vtiger_product_sku_field',
		'title' => 'Vtiger Product Sku Field',
		'type' 	=> 'select',
		'default' 		=> '0',
		'depends_on' 	=> array(
			'cms' 		=> 'vtiger'
		),
		'values' => array(
			'product_no' 		=> 'Product no',
			'serial_no' 		=> 'Serial no',
			'mfr_part_no' 		=> 'Mfr part no',
			'productcode' 		=> 'Product code',
			'vendor_part_no' 	=> 'Vendor part no'
		),
	),
	array(
		'code' => 'lightspeed_apiurl',
		'title' => 'API URL',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'lightspeed'
		)
	),
	array(
		'code' => 'lightspeed_key',
		'title' => 'Lightspeed API key',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'lightspeed'
		)
	),
	array(
		'code' => 'lightspeed_secret',
		'title' => 'Lightspeed secret',
		'type' => 'text',
		'default' => '',
		'depends_on' => array(
			'cms' => 'lightspeed'
		)
	),
	array(
		'code' => 'wms',
		'title' => 'Warehouse Systems',
		'type' => 'select',
		'default' => '0',
		'depends_on' => array(
			'connection_type' => '5'
		),
		'values' => array(
			'0' => 'Select WMS',
			'optiply' => 'Optiply',
		),
	),
);

/* End of file project_settings.php */
/* Location: ./application/config/project_settings.php */
