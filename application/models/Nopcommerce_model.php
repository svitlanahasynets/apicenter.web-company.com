<?php

class Nopcommerce_model extends CI_Model
{
    protected $helper;

    public function __construct() {
        parent::__construct();
    }

    protected function getAccess($projectId)
    {
        $url = $this->Projects_model->getProjectValue('store_url', $projectId) 
            ? $this->Projects_model->getProjectValue('store_url', $projectId) 
            : $this->Projects_model->getValue('nopcommerce_url', $projectId);
        $secret_key = $this->Projects_model->getValue('nopcommerce_secret_key', $projectId) 
            ? $this->Projects_model->getValue('nopcommerce_secret_key', $projectId) : '';
        $admin_key  = $this->Projects_model->getValue('nopcommerce_admin_key', $projectId) 
            ? $this->Projects_model->getValue('nopcommerce_admin_key', $projectId) : '';

        return [
            'url' => $url,
            'secret_key' => $secret_key,
            'admin_key' => $admin_key
        ];
    }

    public function getOrders($projectId, $offset = 0, $amount = 10, $sortOrder = 'asc')
    {
        $this->load->library('nopaccelerate', $this->getAccess($projectId));

        $endDay = $this->Projects_model->getValue('np_order_last_execution', $projectId);

        if ($endDay) {
            $startDay = $this->prepareDate($endDay);
        } else {
            $startDay = $this->Projects_model->getValue('nopcommerce_order_start_day', $projectId) ? 
                $this->Projects_model->getValue('nopcommerce_order_start_day', $projectId) : date('m/d/Y H:i:s');
            $startDay = $this->prepareDate($startDay);
        }

        $endDay = $this->prepareDate($startDay, 5);

        $orders = $this->nopaccelerate->getOrders($startDay, $endDay);

        if ($orders['status'] === false) {
            api2cart_log($projectId, 'exportorders', 'Could not get orders list, ' . var_export($orders['data'], true));
            return false;
        }

        $endDay = date('Y-m-d H:i:00', strtotime($endDay));

        $this->Projects_model->saveValue('np_order_last_execution', $endDay, $projectId);

        return $this->prepareData($projectId, $orders['data']);
    }

    protected function prepareData($projectId, $orders)
    {
        $this->load->library('nopaccelerate', $this->getAccess($projectId));

        $finalOrders = [];

        foreach ($orders as $order) {
            $orderDetail = $this->nopaccelerate->getOrder($order->Id)['data'];
            $appendItem = array(
                'id' => $order->OrderGuid,
                'order_id' => $order->Id,
                'store_id' => $order->StoreName,
                'state' => '',//$order['state'],
                'status' => $order->OrderStatus,
                'customer' => array(
                    'id' => empty($order->CustomerId) ? '' : $order->CustomerId,
                    'email' => empty($order->CustomerEmail) ? $orderDetail['CustomerInfo'] : $order->CustomerEmail,
                    'first_name' => $this->getName($order->CustomerFullName)['first'],
                    'last_name' => $this->getName($order->CustomerFullName)['last'],
                ),
                'create_at' => date('Y-m-d', strtotime($order->CreatedOn)),
                'modified_at' => date('Y-m-d', strtotime($order->CreatedOn)),
                'currency' => $orderDetail['PrimaryStoreCurrencyCode'],
                'totals' => array(
                    'total' => $orderDetail['OrderTotalValue'],
                    'subtotal' => $orderDetail['OrderSubtotalExclTaxValue'],
                    'shipping' => $orderDetail['OrderShippingExclTaxValue'],
                    'tax' => $orderDetail['TaxValue'],
                    'discount' => $orderDetail['OrderTotalDiscount'],
                    'amount_paid' => 0
                )
            );
            if(isset($orderDetail['BillingAddress']) && !empty($orderDetail['BillingAddress'])) {
                $billingInfo = (array) $orderDetail['BillingAddress'];
                $appendItem['billing_address'] = array(
                    'id'         => '',
                    'type'       => 'billing',
                    'first_name' => $billingInfo['FirstName'],
                    'last_name'  => $billingInfo['LastName'],
                    'postcode'   => $billingInfo['ZipPostalCode'],
                    'address1'   => $billingInfo['Address1'],
                    'address2'   => isset($billingInfo['Address2']) && !empty($billingInfo['Address2']) ? $billingInfo['Address2'] : '',
                    'phone'      => $billingInfo['PhoneNumber'],
                    'city'       => $billingInfo['City'],
                    'country'    => $billingInfo['County'],
                    'state'      => isset($billingInfo['StateProvinceName']) ? $billingInfo['StateProvinceName'] : '',
                    'company'    => '',//isset($billingInfo['Company']) ? $billingInfo['Company'] : '',
                    'gender'     => '',
                );
            }

            if(isset($orderDetail['ShippingAddress']) && !empty($orderDetail['ShippingAddress'])) {
                $shippingInfo = (array) $orderDetail['ShippingAddress'];
                $appendItem['shipping_address'] = array(
                    'id'         => '',
                    'type'       => 'shipping',
                    'first_name' => $shippingInfo['FirstName'],
                    'last_name'  => $shippingInfo['LastName'],
                    'postcode'   => $shippingInfo['ZipPostalCode'],
                    'address1'   => $shippingInfo['Address1'],
                    'address2'   => isset($shippingInfo['Address2']) && !empty($shippingInfo['Address2']) ? $shippingInfo['Address2'] : '',
                    'phone'      => $shippingInfo['PhoneNumber'],
                    'city'       => $shippingInfo['PhoneNumber'],
                    'country'    => $shippingInfo['County'],
                    'state'      => isset($shippingInfo['StateProvinceName']) ? $shippingInfo['StateProvinceName'] : '',
                    'company'    => isset($shippingInfo['Company']) ? $shippingInfo['Company'] : '',
                    'gender'     => '',
                );
            }

            if(isset($orderDetail['ShippingMethod']) && $orderDetail['ShippingMethod']) {
                $appendItem['shipping_method'] = $orderDetail['ShippingMethod'];
            }

            if(isset($orderDetail['ShippingMethod']) && $orderDetail['ShippingMethod']) {
                $appendItem['payment_method'] = $orderDetail['PaymentMethod'];
            }

            if (isset($orderDetail['Items'])) {
                $appendItem['order_products'] = array();
                foreach($orderDetail['Items'] as $item) {
                    $item = (array) $item;
                    $appendItem['order_products'][] = array(
                        'product_id' => $item['ProductId'],
                        'order_product_id' => $item['ProductId'],
                        'model' => $item['Sku'],
                        'name' => $item['ProductName'],
                        'price' => $item['UnitPriceExclTaxValue'],
                        'discount_amount' => isset($item['DiscountExclTaxValue']) ? $item['DiscountExclTaxValue'] : 0,
                        'quantity' => $item['Quantity'],
                        'total_price' => $item['SubTotalExclTaxValue'],
                        'total_price_incl_tax' => $item['SubTotalInclTaxValue'],
                        'tax_percent' => 0,
                        'tax_value' => 0,
                        'variant_id' => ''
                    );
                }
            }

            $projectModel = 'Project'.$projectId.'_model';

            if(file_exists(APPPATH."models/".$projectModel.".php")){
                $this->load->model($projectModel);
                if(method_exists($this->$projectModel, 'loadCustomOrderAttributes')){
                    $appendItem = $this->$projectModel->loadCustomOrderAttributes($appendItem, $order, $projectId);
                }
            }
            
            if($appendItem != false){
                $finalOrders[] = $appendItem;
            }
        }

        return $finalOrders;
    }

    protected function getName($fullName)
    {
        $data = [
            'first' => '',
            'last' => ''
        ];
    
        if (empty($fullName)) return $data;

        $name = explode(' ', $fullName);

        $data['first'] = isset($name[0]) ? $name[0] : '';
        $data['last']  = isset($name[1]) ? $name[1] : '';

        return $data;
    }

    protected function prepareDate($date, $plus = '') 
    {
        if ($plus) {
            return date("m/d/Y H:i:s", strtotime($date . ' +' . $plus . 'minutes'));
        }
    
        return date("m/d/Y H:i:s", strtotime($date));
    }

}