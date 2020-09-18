<?php

namespace Picqer_ext;

use Picqer\Financials\Exact\Model;

class PurchaseOrder extends Model
{
    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;

    protected $primaryKey = 'PurchaseOrderID';
    protected $purchaseOrderLines = [ ];

    protected $fillable = [
        'PurchaseOrderID',
        'AmountDC',
        'AmountFC',
        'Created',
        'Creator',
        'CreatorFullName',
        'Currency',
        'DeliveryAccount',
        'DeliveryAccountCode',
        'DeliveryAccountName',
        'DeliveryAddress',
        'DeliveryContact',
        'DeliveryContactPersonFullName',
        'Description',
        'Division',
        'Document',
        'DocumentSubject',
        'DropShipment',
        'ExchangeRate',
        'InvoiceStatus',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'OrderDate',
        'OrderNumber',
        'OrderStatus',
        'PaymentCondition',
        'PaymentConditionDescription',
        'PurchaseAgent',
        'PurchaseAgentFullName',
        'PurchaseOrderLines',
        'ReceiptDate',
        'ReceiptStatus',
        'Remarks',
        'SalesOrder',
        'SalesOrderNumber',
        'ShippingMethod',
        'ShippingMethodDescription',
        'Source',
        'Supplier',
        'SupplierCode',
        'SupplierContact',
        'SupplierContactPersonFullName',
        'SupplierName',
        'VATAmount',
        'Warehouse',
        'WarehouseCode',
        'WarehouseDescription',
        'YourRef'
    ];

    public function addItem(array $array)
    {
        if (!isset($this->attributes['PurchaseOrderLines']) || $this->attributes['PurchaseOrderLines'] == null) {
            $this->attributes['PurchaseOrderLines'] = [];
        }
        if (!isset($array['LineNumber'])) {
            $array['LineNumber'] = count($this->attributes['PurchaseOrderLines']) + 1;
        }
        $this->attributes['PurchaseOrderLines'][] = $array;
    }

    protected $url = 'purchaseorder/PurchaseOrders';
}