<?php
class Mplus_model extends CI_Model 
{
    function __construct() {
        parent::__construct();
        $this->load->model('Projects_model');
        $this->load->helper('tools_helper');
        $this->load->helpers('tools');
        $this->load->helpers('constants');
    }

    private function initClient($projectId) {
        $params = [
            'api_url'    => $this->Projects_model->getValue('mplus_api_url', $projectId),//'https://api.mpluskassa.nl', 
            'api_port'   => $this->Projects_model->getValue('mplus_api_port', $projectId),//'44038',
            'api_ident'  => $this->Projects_model->getValue('mplus_api_ident', $projectId),//'web-company',
            'api_secret' => $this->Projects_model->getValue('mplus_api_secret', $projectId),//'pFEmacuctHrR'
        ];
        $this->load->library('mplus_client', $params);
    }

    public function sendOrder($projectId, $orderData) {
        if (!class_exists('mplus_client')) {
            $this->initClient($projectId);
        }
        // $this->mplus_client->getRelations();exit;
        $order  = $this->orderFormat($orderData, $projectId);
        $result = $this->mplus_client->sendOrder($order);

        if (isset($result['success']) && $result['success']) {
            api2cart_log($projectId, 'exportorders', $result['messages']);
        } else {
            project_error_log($projectId, 'exportorders', $result['messages']);
        }
    }

    private function orderFormat($orderData, $projectId) {
        $exist = $this->findRelation($orderData);
        $relationNumber = $exist ? $exist : $this->createRelation($orderData, $projectId);

        $order = array(
            'extOrderId'        => $orderData['order_id'],
            'entryBranchNumber' => 1, // branchNumber must be present in Mplus
            'relationNumber'    => $relationNumber, // relationNumber must be present in Mplus
            'employeeNumber'    => 999999, // employeeNumber must be present in Mplus
            'reference'         => '', // an extra optional reference text
            'orderCategoryNumber' => 0, // retrieve the available orderCategories through getOrderCategories
            'deliveryMethod' => 'BEZORGEN_MET_AUTO', // retrieve the available deliveryMethods through getDeliveryMethods
            'deliveryAddress' => array(
                'name'    => $orderData['shipping_method'],
                'contact' => $orderData['shipping_lastname'] .' '. $orderData['shipping_firstname'],
                'address' => $orderData['shipping_address_1'],
                'zipcode' => $orderData['shipping_postcode'],
                'city'    => $orderData['shipping_city'],
                'country' => $orderData['shipping_country'],
            ),
            'invoiceAddress' => array(
                'name'    => $orderData['shipping_method'],
                'contact' => $orderData['shipping_lastname'] .' '. $orderData['shipping_firstname'],
                'address' => $orderData['shipping_address_1'],
                'zipcode' => $orderData['shipping_postcode'],
                'city'    => $orderData['shipping_city'],
                'country' => $orderData['shipping_country'],
            ),
            // options are:
            // (1) VAT-METHOD-INCLUSIVE (VAT included)
            // (2) VAT-METHOD-EXCLUSIVE (VAT excluded)
            // (3) VAT-METHOD-SHIFTED (VAT shifted, for international business)
            'vatMethod' => 'VAT-METHOD-EXCLUSIVE',
        );
        $lineList = [];

        if (isset($orderData['products'])) {
            foreach ($orderData['products'] as $product) {
                $price = $product['total_exclude_tax']*100;
                $lineList[] = [
                    'articleNumber' => $product['model'],
                    'data' => array(
                        'quantity' => $product['quantity'],
                        'price' => $price, // prices are always in cents
                        'discountPercentage' => 0, // optional discountPercentage
                        'discountAmount' => 0, // optional discountAmount
                        'vatCode' => 0, // VAT code
                    ),
                    'text' => '',
                ];
            }
        }

        $order['lineList'] = $lineList;

        return $order; 
    }

    private function findRelation($data) {
        $relation = ['email' => $data['email']];
        $result   = $this->mplus_client->findRelation($relation);
        
        if ($result['success']) {
            $response = $result['response'];
            if (isset($response['relationNumber'])) {
                return $response['relationNumber'];
            }
        }

        return false;
    }

    private function createRelation($data, $projectId) {
        $relation = [
            'name'            => $data['firstname'] .' '. $data['lastname'],
            'address'         => $data['payment_address_1'],
            'zipcode'         => $data['payment_postcode'],
            'city'            => $data['payment_city'],
            'country'         => $data['payment_country'],
            'telephone'       => $data['telephone'],
            'mobile'          => $data['telephone'],
            'email'           => $data['email'],
            'contact'         => '',
            'deliveryAddress' => $data['shipping_address_1'],
            'deliveryZipcode' => $data['shipping_postcode'],
            'deliveryCity'    => $data['shipping_city'],
            'deliveryCountry' => $data['shipping_country'],
        ];
        $result = $this->mplus_client->createRelation($relation);

        if ($result['success']) {
            if ($result['response']) {
                api2cart_log($projectId, 'importcustomers', 'Created customer ' . $data['email']);
                return $result['response'];
            }
        } else {
            api2cart_log($projectId, 'importcustomers', 'Could not created customer  '. $result['messages']);
        }

        return false;
    }

    public function getStockHistory($projectId) {
        if (!class_exists('mplus_client')) {
            $this->initClient($projectId);
        }

        // Then we initialize the variables
        $branchNumber = 1; // This means we want the stock information for this branch.
        $articleNumbers = array();
        $sinceStockId = null; // sinceStockId works just like syncMarker, use this to retrieve all stock changes since the last time you checked.
        $stockInterval = $this->Projects_model->getValue('stock_interval', $projectId);
        $fromFinancialDateTime = time() - ($stockInterval * 60);
        $throughFinancialDateTime = time();

        $result = $this->mplus_client->getStockHistory($branchNumber, $articleNumbers, $sinceStockId, $fromFinancialDateTime, $throughFinancialDateTime, 0);

        if (isset($result['success']) && $result['success']) {
            foreach ($result['response'] as $data) {
                $articles[$data['branchNumber']][] = $data['articleNumber'];
            }
            return $articles; 
        }

        return false;
    }

    public function getStock($projectId, $articleNumbers, $branchNumber, $stockId = null) {
        if (!class_exists('mplus_client')) {
            $this->initClient($projectId);
        }

        $result = $this->mplus_client->getStock($articleNumbers, $branchNumber, $stockId = null);
        if (isset($result['success']) && $result['success']) {
            $finalData = [];
            foreach ($result['response'] as $stockData) {
                $finalData[$stockData['articleNumber']] = $stockData['amountFree'];
            }
            return $finalData; 
        }

        return false;
    }

    public function testConnect($projectId) {
        if (!class_exists('mplus_client')) {
            $this->initClient($projectId);
        }
        $this->mplus_client->testConnect();
    }
}