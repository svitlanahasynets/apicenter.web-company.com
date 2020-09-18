<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Shoptrader extends MY_Controller {

    public function index() {
        ini_set('error_reporting', E_ALL);
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        $this->load->helper('ExactOnline/vendor/autoload');
		$this->load->model('Projects_model');
		$this->load->model('Afas_model');
		$this->load->model('Exactonline_model');
		$this->load->model('Visma_model');
		$this->load->model('Cms_model');
		$this->load->model('Marketplace_model');
        $this->load->model('Optiply_model');
        $this->load->model('Cscart_model');
        $this->load->model('Shoptrader_model');
		$projects = $this->db->get('projects')->result_array();
		// var_dump( $this->Afas_model->getCustomer(87));exit;
		//Grab all projects listed in APIcenter
		foreach($projects as $project){
            // Check if project is enabled

			// if($this->Projects_model->getValue('enabled', $project['id']) != '1'){
            //     continue;
			// }
			
			if($this->input->get('project') != '' && $this->input->get('project') != $project['id']){
                continue;
			}
            
            if($project['id'] != 95) {
                continue;
            }

			// Get credentials
			$storeUrl = $project['store_url'];
			$apiKey = $project['api_key'];
			$pluginKey = $project['plugin_key'];
			$storeKey = $project['store_key'];
			$erpSystem = $project['erp_system'];
			$cms = $this->Projects_model->getValue('cms', $project['id']);
			$connectionType = $project['connection_type'];
			$market_place = $this->Projects_model->getValue('market_place', $project['id']);
			$exportOrders = $this->Projects_model->getValue('send_orders_as', $project['id']);
			$amountSupliers = $this->Projects_model->getValue('customers_amount', $project['id']);
            $offsetSupliers = $this->Projects_model->getValue('customers_offset', $project['id']);
            $fromDate = '';

            $articleAmount = $this->Projects_model->getValue('article_amount', $project['id']);

            $products = $this->Afas_model->getArticles(95, 0, 10);
            echo "<pre>";
            var_export($products);exit;
            // $this->Cscart_model->getArticles($project['id'], 0, $articleAmount);
            // $articles = $this->Cscart_model->getArticles($project['id'], 0, 10);
            // $orders = array (
            //     0 => 
            //         array (
            //             'id' => '69',
            //             'order_id' => '69',
            //             'store_id' => '',
            //             'state' => '',
            //             'status' => 'In progress',
            //             'customer' => 
            //             array (
            //             'id' => '565',
            //             'email' => 'office@kokon.cc',
            //             'first_name' => 'Günter',
            //             'last_name' => 'Knittel',
            //             ),
            //             'create_at' => '2017-08-14 18:07:33',
            //             'modified_at' => '2017-08-14 18:07:33',
            //             'currency' => 'EUR',
            //             'totals' => 
            //             array (
            //             'tax' => 0,
            //             'amount_paid' => 0,
            //             'subtotal' => '0.0000',
            //             'total' => '6816.5000',
            //             ),
            //             'billing_address' => 
            //             array (
            //             'id' => '',
            //             'type' => '',
            //             'first_name' => 'Günter',
            //             'last_name' => 'Knittel',
            //             'postcode' => '6911',
            //             'address1' => 'Lindauer Strasse',
            //             'address2' => '',
            //             'phone' => '0557454820',
            //             'city' => 'Lochau',
            //             'country' => 'Austria',
            //             'state' => '',
            //             'company' => 'Lifestyle Haus Handels GmbH',
            //             'gender' => '',
            //             ),
            //             'shipping_address' => 
            //             array (
            //             'id' => '',
            //             'type' => '',
            //             'first_name' => 'Günter',
            //             'last_name' => 'Knittel',
            //             'postcode' => '6911',
            //             'address1' => 'Lindauer Strasse',
            //             'address2' => '',
            //             'phone' => '0557454820',
            //             'city' => 'Lochau',
            //             'country' => 'Austria',
            //             'state' => '',
            //             'company' => 'Lifestyle Haus Handels GmbH',
            //             'gender' => '',
            //             ),
            //             'shipping_method' => 'extra1_extra1',
            //             'payment_method' => '',
            //             'order_products' => 
            //             array (
            //             0 => 
            //             array (
            //                 'product_id' => '6185',
            //                 'order_product_id' => '349',
            //                 'model' => '255-620-096',
            //                 'name' => 'Cushion Cover Pebble Ash Grey',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             1 => 
            //             array (
            //                 'product_id' => '6313',
            //                 'order_product_id' => '350',
            //                 'model' => '255-620-158',
            //                 'name' => 'Cushion Cover Pebble Riverstone Mix',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             2 => 
            //             array (
            //                 'product_id' => '6557',
            //                 'order_product_id' => '351',
            //                 'model' => '255-620-178',
            //                 'name' => 'Cushion Cover Pebble Bordeaux Red',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             3 => 
            //             array (
            //                 'product_id' => '6310',
            //                 'order_product_id' => '352',
            //                 'model' => '',
            //                 'name' => 'Cushion Cover Pebble Desert Mix',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             4 => 
            //             array (
            //                 'product_id' => '6206',
            //                 'order_product_id' => '353',
            //                 'model' => '255-620-175',
            //                 'name' => 'Cushion Cover Pebble Army Green',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             5 => 
            //             array (
            //                 'product_id' => '5736',
            //                 'order_product_id' => '354',
            //                 'model' => '255-415-080',
            //                 'name' => 'Cushion Sheepskin Champagne White',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '20',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             6 => 
            //             array (
            //                 'product_id' => '5703',
            //                 'order_product_id' => '355',
            //                 'model' => '255-415-037',
            //                 'name' => 'Cushion Sheepskin Snow White',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '20',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             7 => 
            //             array (
            //                 'product_id' => '5733',
            //                 'order_product_id' => '356',
            //                 'model' => '255-415-076',
            //                 'name' => 'Cushion Sheepskin Ash Grey',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '10',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             8 => 
            //             array (
            //                 'product_id' => '5702',
            //                 'order_product_id' => '357',
            //                 'model' => '255-415-036',
            //                 'name' => 'Cushion Sheepskin Sand Taupe',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '10',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             9 => 
            //             array (
            //                 'product_id' => '5707',
            //                 'order_product_id' => '358',
            //                 'model' => '',
            //                 'name' => 'Cushion Sheepskin Salmon Pink',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '10',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             10 => 
            //             array (
            //                 'product_id' => '5711',
            //                 'order_product_id' => '359',
            //                 'model' => '255-415-051',
            //                 'name' => 'Cushion Sheepskin Wine Bordeaux',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '10',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             11 => 
            //             array (
            //                 'product_id' => '5717',
            //                 'order_product_id' => '360',
            //                 'model' => '255-415-057',
            //                 'name' => 'Cushion Sheepskin Army Green',
            //                 'price' => '27.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '10',
            //                 'total_price' => '27.5000',
            //                 'total_price_incl_tax' => '27.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             12 => 
            //             array (
            //                 'product_id' => '6190',
            //                 'order_product_id' => '361',
            //                 'model' => '255-620-700',
            //                 'name' => 'Cushion Cover Pebble Champagne White',
            //                 'price' => '19.5000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '12',
            //                 'total_price' => '19.5000',
            //                 'total_price_incl_tax' => '19.5000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             13 => 
            //             array (
            //                 'product_id' => '5768',
            //                 'order_product_id' => '362',
            //                 'model' => '',
            //                 'name' => 'Carpet Sheepskin Champagne White',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '8',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             14 => 
            //             array (
            //                 'product_id' => '5762',
            //                 'order_product_id' => '363',
            //                 'model' => '330-415-034',
            //                 'name' => 'Carpet Sheepskin Snow White',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '8',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             15 => 
            //             array (
            //                 'product_id' => '5765',
            //                 'order_product_id' => '364',
            //                 'model' => '330-415-038',
            //                 'name' => 'Carpet Sheepskin Ash Grey',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '4',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             16 => 
            //             array (
            //                 'product_id' => '5784',
            //                 'order_product_id' => '365',
            //                 'model' => '',
            //                 'name' => 'Carpet Sheepskin Salmon Pink',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '4',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             17 => 
            //             array (
            //                 'product_id' => '5791',
            //                 'order_product_id' => '366',
            //                 'model' => '330-415-068',
            //                 'name' => 'Carpet Sheepskin Wine Bordeaux',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '4',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             18 => 
            //             array (
            //                 'product_id' => '5796',
            //                 'order_product_id' => '367',
            //                 'model' => '',
            //                 'name' => 'Carpet Sheepskin Army Green',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '4',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             19 => 
            //             array (
            //                 'product_id' => '5763',
            //                 'order_product_id' => '368',
            //                 'model' => '330-415-074',
            //                 'name' => 'Carpet Sheepskin Sand Taupe',
            //                 'price' => '45.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '6',
            //                 'total_price' => '45.0000',
            //                 'total_price_incl_tax' => '45.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             20 => 
            //             array (
            //                 'product_id' => '5774',
            //                 'order_product_id' => '369',
            //                 'model' => '',
            //                 'name' => 'Carpet Goatskin Champagne White',
            //                 'price' => '36.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '6',
            //                 'total_price' => '36.0000',
            //                 'total_price_incl_tax' => '36.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             21 => 
            //             array (
            //                 'product_id' => '5776',
            //                 'order_product_id' => '370',
            //                 'model' => '330-415-050',
            //                 'name' => 'Carpet Goatskin Snow White',
            //                 'price' => '36.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '6',
            //                 'total_price' => '36.0000',
            //                 'total_price_incl_tax' => '36.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             22 => 
            //             array (
            //                 'product_id' => '5803',
            //                 'order_product_id' => '371',
            //                 'model' => '330-415-085',
            //                 'name' => 'Carpet Goatskin Ash Grey',
            //                 'price' => '36.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '6',
            //                 'total_price' => '36.0000',
            //                 'total_price_incl_tax' => '36.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             23 => 
            //             array (
            //                 'product_id' => '6300',
            //                 'order_product_id' => '372',
            //                 'model' => '230-311-118',
            //                 'name' => 'Stand Zinc Matt Black Round',
            //                 'price' => '19.9500',
            //                 'discount_amount' => 0,
            //                 'quantity' => '6',
            //                 'total_price' => '19.9500',
            //                 'total_price_incl_tax' => '19.9500',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             24 => 
            //             array (
            //                 'product_id' => '6301',
            //                 'order_product_id' => '373',
            //                 'model' => '230-311-119',
            //                 'name' => 'Stand Zinc Matt Black Round',
            //                 'price' => '24.9500',
            //                 'discount_amount' => 0,
            //                 'quantity' => '4',
            //                 'total_price' => '24.9500',
            //                 'total_price_incl_tax' => '24.9500',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             25 => 
            //             array (
            //                 'product_id' => '5582',
            //                 'order_product_id' => '374',
            //                 'model' => '190-505-075',
            //                 'name' => 'Candleholder Glass Clear Round',
            //                 'price' => '15.0000',
            //                 'discount_amount' => 0,
            //                 'quantity' => '24',
            //                 'total_price' => '15.0000',
            //                 'total_price_incl_tax' => '15.0000',
            //                 'tax_percent' => 0,
            //                 'tax_value' => 0,
            //                 'variant_id' => '',
            //             ),
            //             ),
            //             'comment' => '',
            //         ),
            // );
            // $orders = $this->Shoptrader_model->getOrders(95, 440, 10, 'ask');
            // echo "<pre>";
            // var_export($orders);
            // echo "</pre>";exit;
            // foreach($orders as $order){
            //     $result = $this->Afas_model->sendOrder($project['id'], $order);
            // }

        }
    }

    private function dd($data) {
        echo "<pre>";
        var_export($data);
        echo "</pre>";
    }
}