<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Prestashop extends MY_Controller {

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
      $this->load->model('Prestashop_model');
      $projects = $this->db->get('projects')->result_array();

      $orders = array (
        0 => 
        array (
          'id' => '0',
          'order_id' => '7',
          'store_id' => '1',
          'state' => '1',
          'status' => 'Awaiting check payment',
          'customer' => 
          array (
            'id' => '17',
            'email' => 'info@berlicummetalen.afas',
            'first_name' => 'Test',
            'last_name' => 'Dev',
          ),
          'create_at' => '2019-09-02 14:22:08',
          'modified_at' => '2019-09-02 14:22:08',
          'currency' => 'EUR',
          'totals' => 
          array (
            'total' => '24.500000',
            'subtotal' => 24.5,
            'shipping' => '0.000000',
            'tax' => '21.000',
            'discount' => '0.000000',
          ),
          'billing_address' => 
          array (
            'id' => '',
            'type' => 'billing',
            'first_name' => 'Test',
            'last_name' => 'Dev',
            'postcode' => '1000 AP',
            'address1' => 'testStreet',
            'address2' => '',
            'phone' => '26312345',
            'city' => 'testCit',
            'country' => 'NL',
            'company' => 'ffetest',
            'gender' => '',
          ),
          'shipping_address' => 
          array (
            'id' => '',
            'type' => 'shipping',
            'first_name' => 'Test',
            'last_name' => 'Dev',
            'postcode' => '1000 AP',
            'address1' => 'testStreet',
            'address2' => '',
            'phone' => '26312345',
            'city' => 'testCit',
            'country' => 'NL',
            'company' => 'ffetest',
            'gender' => '',
          ),
          'shipping_method' => 'Test',
          'payment_method' => 'Payments by check',
          'order_products' => 
          array (
            0 => 
            array (
              'product_id' => '114',
              'order_product_id' => '114',
              'model' => 'IND300655',
              'name' => 'Dartpijlen 21 gram update356',
              'price' => '24.500000',
              'discount_amount' => 0,
              'quantity' => '1',
              'total_price' => '24.500000',
              'total_price_incl_tax' => '24.500000',
              'tax_percent' => 0,
              'tax_value' => 0,
              'variant_id' => '',
            ),
          ),
        ),
      );

      
				$fromDate = $this->Projects_model->getDate(126);
				$onlyOpen = $this->Projects_model->getValue('only_open_orders', 126);
				
				$this->Exactonline_model->setData(
					array(
						'projectId' => 126,
						'redirectUrl' => $this->Projects_model->getValue('exactonline_redirect_url', 126).'/?project_id='.'126',
						'clientId' => $this->Projects_model->getValue('exactonline_client_id', 126),
						'clientSecret' => $this->Projects_model->getValue('exactonline_secret_key', 126),
					)
				);

				$connection = $this->Exactonline_model->makeConnection(126);

				if(!$connection){

					continue; 
				}

      // $orders = $this->Prestashop_model->getOrders(126, 0, 10);
      // foreach ($orders as $order) {
      //   $result = $this->Exactonline_model->sendOrder($connection, 126, $order);
      // }
      // $articles = $this->Exactonline_model->getArticles($connection, null, '', 10);
      // $this->Prestashop_model->createCategory(126, 'Exact Category');
      // echo "<pre>";
      // var_export($articles);
      $articles = array (
        0 => 
        array (
          'model' => '12test',
          'name' => 'New test product for OPtiply2',
          'description' => 'New test product for OPtiply',
          'tax_class_id' => NULL,
          'id' => 'f7e9684e-1f6b-4089-b39e-8f1e424795d5',
          'price' => '12',
          'created' => '/Date(1550748604243)/',
          'modified' => '/Date(1558714362020)/',
          'categories_ids' => '52',
        ),
      );

      $this->Prestashop_model->updateArticles(126, $articles);
    }
}