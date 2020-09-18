<?php

namespace Picqer_ext;

use Picqer\Financials\Exact\Model;

class Werehouse extends Model
{
    use \Picqer\Financials\Exact\Query\Findable;
    use \Picqer\Financials\Exact\Persistance\Storable;

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