<?php


namespace Picqer_ext;

use Picqer\Financials\Exact\Model;

class SalesItemPrice extends Model
{
    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;

    protected $primaryKey = 'ItemId';

    protected $fillable = [
        'CurrencyCode',
        'ItemCode',
        'ItemDescription',
        'ItemId',
        'PriceExcludingVAT',
        'PriceIncludingVAT',
        'UnitCode',
        'UnitDescription',
        'VATCode'
    ];

    protected $url = 'logistics/SalesItemPrice';
}