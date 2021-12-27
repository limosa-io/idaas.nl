<?php
/**
 * Extends the default session handler by introducing tenant scoping.
 * Used as `databaseWithCache`. See `config/session.php`
 */
namespace App\Session;

use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Arr;

class DatabaseSessionHandler extends BaseDatabaseSessionHandler
{

    protected function getQuery()
    {
        return $this->connection->table($this->table)->where('tenant_id', resolve('App\Tenant')->id);
    }

    protected function getDefaultPayload($data)
    {
        
        $result = parent::getDefaultPayload($data);

        $result['tenant_id'] = resolve('App\Tenant')->id;

        return $result;
    }

}