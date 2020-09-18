<?php namespace Picqer\Financials\Exact;

/**
 * Class Warehouse
 *
 * @package Picqer\Financials\Exact
 * @see https://start.exactonline.nl/docs/HlpRestAPIResourcesDetails.aspx?name=InventoryWarehouses
 *
*/
class Warehouse extends Model
{
    use Query\Findable;
    use Persistance\Storable;

    protected $primaryKey = 'ID';

    protected $fillable = [
        'ID',
        'Code',
        'Created',
        'Creator',
        'CreatorFullName',
        'DefaultStorageLocation',
        'DefaultStorageLocationCode',
        'DefaultStorageLocationDescription',
        'Description',
        'Division',
        'EMail',
        'Main',
        'ManagerUser',
        'Modified',
        'Modifier',
        'ModifierFullName',
        'UseStorageLocations'
    ];

    protected $url = 'inventory/Warehouses';
}
