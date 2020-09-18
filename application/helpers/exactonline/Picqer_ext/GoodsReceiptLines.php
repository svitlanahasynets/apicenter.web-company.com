<?php


namespace Picqer_ext;

use Picqer\Financials\Exact\Model;
class GoodsReceiptLines extends Model
{
    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;
    protected $fillable = [
        'ID',
        'BatchNumbers',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'GoodsReceiptID',
        'Division',
        'Item',
        'ItemCode',
        'ItemDescription',
        'ItemUnitCode',
        'LineNumber',
        'Location',
        'LocationCode',
        'LocationDescription',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Notes',
        'Project',
        'ProjectCode',
        'ProjectDescription',
        'PurchaseOrderID',
        'PurchaseOrderLineID',
        'PurchaseOrderNumber',
        'QuantityOrdered',
        'QuantityReceived',
        'SerialNumbers',
        'SupplierItemCode',
    ];
    protected $url = 'purchaseorder/GoodsReceiptLines';
}