<?php

namespace Picqer_ext;

use Picqer\Financials\Exact\Model;


/**
 * Class SupplierItem
 * @package Picqer_ext
 *
 *
 */

class SupplierItem extends Model
{

    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;

    protected $fillable = [
        'ID',
        'CopyRemarks',
        'CountryOfOrigin',
        'CountryOfOriginDescription',
        'Created',
        'Creator',
        'CreatorFullName',
        'Currency',
        'CurrencyDescription',
        'Division',
        'DropShipment',
        'Item',
        'ItemCode',
        'ItemDescription',
        'MainSupplier',
        'MinimumQuantity',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'Notes',
        'PurchaseLeadTime',
        'PurchasePrice',
        'PurchaseUnit',
        'PurchaseUnitDescription',
        'PurchaseUnitFactor',
        'PurchaseVATCode',
        'PurchaseVATCodeDescription',
        'Supplier',
        'SupplierCode',
        'SupplierDescription',
        'SupplierItemCode'
    ];

    protected $url = 'logistics/SupplierItem';

}