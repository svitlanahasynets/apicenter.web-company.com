<?php

use function GuzzleHttp\json_decode;

if ( ! defined('BASEPATH')) exit('No direct script access allowed'); 

class Akeneo extends CI_Controller {

    public function __construct()
    {
        parent::__construct();

        $this->load->model('Afas_model');
        $this->load->model('Projects_model');
        $this->load->model('Cms_model');
        $this->load->helper('tools');
        $this->load->model('Akeneo_model');
    }

    public  function index()
    {   
        // $this->Projects_model->saveValue('article_offset', 0, 117);
        // $offset = $this->Projects_model->getValue('article_offset', 117);
        // $this->load->model('Nopcommerce_model');
        // $projectId = 162;
        // $this->Projects_model->saveValue('np_order_last_execution', '', $projectId);
        // $startDay = $this->Projects_model->getValue('nopcommerce_order_start_day', $projectId);
        // $endDay = $this->Projects_model->getValue('np_order_last_execution', 162);
        // echo "<pre>";
        // var_dump($offset);


        // var_dump($this->Nopcommerce_model->getOrders(162, 0, 10, $sortOrder = 'asc'));exit;
        // $articles = array (
        //     0 => 
        //     array (
        //       'ItemCode' => '5064871111',
        //       'Description' => 'STUURLEIDING CWA',
        //       'UnitId' => 'STK',
        //       'ArtGroupID' => '9999',
        //       'ArtGroup' => 'Nog toewijzen',
        //       'Blocked' => false,
        //       'VATgroup' => '2',
        //       'BasicSalesPrice' => 21.19,
        //       'StockActual' => 0,
        //       'CreatedDate' => '2019-01-02T21:57:43Z',
        //       'DateModified' => '2019-09-08T13:06:05Z',
        //       'Bestandsnaam' => NULL,
        //       'Afbeelding' => NULL,
        //       'Barcode' => 'P401235',
        //       'Website' => true,
        //       'Extra_omschrijving' => NULL,
        //       'Vervangend_type_item' => NULL,
        //       'Vervang_Itemcode' => NULL,
        //       'Leverancier_art.nr' => '401235',
        //       'Standaardlocatie' => '*****',
        //       'OEM_huidig' => '#401235',
        //       'OEM_oud' => NULL,
        //       'Zoeksleutels' => NULL,
        //       'Troublefree_nummer' => NULL,
        //       'Code' => NULL,
        //       'Nettogewicht' => NULL,
        //       'Voorkeur_inkooprelatie' => true,
        //       'Supplier' => '50080',
        //       'Itemcode_inkooprelatie' => '401235',
        //       'Leverancier' => 'Certus',
        //       'Merk' => 'ORIGINEEL',
        //       'MetaTitle_NL' => NULL,
        //       'MetaTitle_GB' => NULL,
        //       'MetaTitle_DE' => NULL,
        //       'MetaTitle_FR' => NULL,
        //       'MetaDesc_NL' => NULL,
        //       'MetaDesc_GB' => NULL,
        //       'MetaDesc_DE' => NULL,
        //       'MetaDesc_FR' => NULL,
        //       'BrutoPrice' => 36.22,
        //     ),
        //     1 => 
        //     array (
        //       'ItemCode' => '5064967',
        //       'Description' => 'PIJP C50',
        //       'UnitId' => 'STK',
        //       'ArtGroupID' => '9999',
        //       'ArtGroup' => 'Nog toewijzen',
        //       'Blocked' => false,
        //       'VATgroup' => '2',
        //       'BasicSalesPrice' => 42.46,
        //       'StockActual' => 0,
        //       'CreatedDate' => '2019-01-02T21:57:45Z',
        //       'DateModified' => '2019-09-08T13:06:12Z',
        //       'Bestandsnaam' => NULL,
        //       'Afbeelding' => NULL,
        //       'Barcode' => 'P4012J8',
        //       'Website' => true,
        //       'Extra_omschrijving' => NULL,
        //       'Vervangend_type_item' => NULL,
        //       'Vervang_Itemcode' => NULL,
        //       'Leverancier_art.nr' => '4012J8',
        //       'Standaardlocatie' => '*****',
        //       'OEM_huidig' => '#4012J8',
        //       'OEM_oud' => NULL,
        //       'Zoeksleutels' => NULL,
        //       'Troublefree_nummer' => NULL,
        //       'Code' => NULL,
        //       'Nettogewicht' => NULL,
        //       'Voorkeur_inkooprelatie' => true,
        //       'Supplier' => '50080',
        //       'Itemcode_inkooprelatie' => '4012J8',
        //       'Leverancier' => 'Certus',
        //       'Merk' => 'ORIGINEEL',
        //       'MetaTitle_NL' => NULL,
        //       'MetaTitle_GB' => NULL,
        //       'MetaTitle_DE' => NULL,
        //       'MetaTitle_FR' => NULL,
        //       'MetaDesc_NL' => NULL,
        //       'MetaDesc_GB' => NULL,
        //       'MetaDesc_DE' => NULL,
        //       'MetaDesc_FR' => NULL,
        //       'BrutoPrice' => 70.05,
        //     ),
        //     2 => 
        //     array (
        //       'ItemCode' => '5065230',
        //       'Description' => 'STUURLEIDING WEB',
        //       'UnitId' => 'STK',
        //       'ArtGroupID' => '9999',
        //       'ArtGroup' => 'Nog toewijzen',
        //       'Blocked' => false,
        //       'VATgroup' => '2',
        //       'BasicSalesPrice' => 100.13,
        //       'StockActual' => 0,
        //       'CreatedDate' => '2019-01-02T21:57:48Z',
        //       'DateModified' => '2019-09-08T13:06:26Z',
        //       'Bestandsnaam' => NULL,
        //       'Afbeelding' => NULL,
        //       'Barcode' => 'P4014F2',
        //       'Website' => true,
        //       'Extra_omschrijving' => NULL,
        //       'Vervangend_type_item' => NULL,
        //       'Vervang_Itemcode' => NULL,
        //       'Leverancier_art.nr' => '4014F2',
        //       'Standaardlocatie' => '*****',
        //       'OEM_huidig' => '#4014F2',
        //       'OEM_oud' => NULL,
        //       'Zoeksleutels' => NULL,
        //       'Troublefree_nummer' => NULL,
        //       'Code' => NULL,
        //       'Nettogewicht' => NULL,
        //       'Voorkeur_inkooprelatie' => true,
        //       'Supplier' => '50080',
        //       'Itemcode_inkooprelatie' => '4014F2',
        //       'Leverancier' => 'Certus',
        //       'Merk' => 'ORIGINEEL',
        //       'MetaTitle_NL' => NULL,
        //       'MetaTitle_GB' => NULL,
        //       'MetaTitle_DE' => NULL,
        //       'MetaTitle_FR' => NULL,
        //       'MetaDesc_NL' => NULL,
        //       'MetaDesc_GB' => NULL,
        //       'MetaDesc_DE' => NULL,
        //       'MetaDesc_FR' => NULL,
        //       'BrutoPrice' => 156.03,
        //     ),
        //     3 => 
        //     array (
        //       'ItemCode' => '5065352',
        //       'Description' => 'STUURLEIDING CPB',
        //       'UnitId' => 'STK',
        //       'ArtGroupID' => '9999',
        //       'ArtGroup' => 'Nog toewijzen',
        //       'Blocked' => false,
        //       'VATgroup' => '2',
        //       'BasicSalesPrice' => 159.77,
        //       'StockActual' => 0,
        //       'CreatedDate' => '2019-01-02T21:57:51Z',
        //       'DateModified' => '2019-09-08T13:06:35Z',
        //       'Bestandsnaam' => NULL,
        //       'Afbeelding' => NULL,
        //       'Barcode' => 'P4014N4',
        //       'Website' => true,
        //       'Extra_omschrijving' => NULL,
        //       'Vervangend_type_item' => NULL,
        //       'Vervang_Itemcode' => NULL,
        //       'Leverancier_art.nr' => '4014N4',
        //       'Standaardlocatie' => '*****',
        //       'OEM_huidig' => '#4014N4',
        //       'OEM_oud' => NULL,
        //       'Zoeksleutels' => NULL,
        //       'Troublefree_nummer' => NULL,
        //       'Code' => NULL,
        //       'Nettogewicht' => NULL,
        //       'Voorkeur_inkooprelatie' => true,
        //       'Supplier' => '50080',
        //       'Itemcode_inkooprelatie' => '4014N4',
        //       'Leverancier' => 'Certus',
        //       'Merk' => 'ORIGINEEL',
        //       'MetaTitle_NL' => NULL,
        //       'MetaTitle_GB' => NULL,
        //       'MetaTitle_DE' => NULL,
        //       'MetaTitle_FR' => NULL,
        //       'MetaDesc_NL' => NULL,
        //       'MetaDesc_GB' => NULL,
        //       'MetaDesc_DE' => NULL,
        //       'MetaDesc_FR' => NULL,
        //       'BrutoPrice' => 263.63,
        //     ),
        // );

      $result   = $this->Afas_model->getArticles(117, 0, 10);
    //   echo "<pre>";
    //   var_export($result);exit;
      $this->Akeneo_model->updateArticles(117, $result['results']);
    }

}