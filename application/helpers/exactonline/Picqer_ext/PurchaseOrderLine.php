<?php

namespace Picqer_ext;

use Picqer\Financials\Exact\Model;

class PurchaseOrderLine extends Model
{
    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;

    protected $fillable = [
        'ID',
        'AmountDC',
        'AmountFC',
        'CostCenter',
        'CostCenterDescription',
        'CostUnit',
        'CostUnitDescription',
        'Created',
        'Creator',
        'CreatorFullName',
        'Description',
        'Discount',
        'Division',
        'Expense',
        'ExpenseDescription',
        'InStock',
        'InvoicedQuantity',
        'Item',
        'ItemCode',
        'ItemDescription',
        'ItemDivisable',
        'LineNumber',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'NetPrice',
        'Notes',
        'Project',
        'ProjectCode',
        'ProjectDescription',
        'ProjectedStock',
        'PurchaseOrderID',
        'Quantity',
        'QuantityInPurchaseUnits',
        'Rebill',
        'ReceiptDate',
        'ReceivedQuantity',
        'SalesOrder',
        'SalesOrderLine',
        'SalesOrderLineNumber',
        'SalesOrderNumber',
        'SupplierItemCode',
        'SupplierItemCopyRemarks',
        'Unit',
        'UnitDescription',
        'UnitPrice',
        'VATAmount',
        'VATCode',
        'VATDescription',
        'VATPercentage'
    ];

    protected $url = 'purchaseorder/PurchaseOrderLines';
}