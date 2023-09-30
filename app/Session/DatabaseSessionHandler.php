<?php

/**
 * Extends the default session handler by introducing tenant scoping.
 * Used as `databaseWithCache`. See `config/session.php`
 */

namespace App\Session;

use App\Tenant;
use Illuminate\Session\DatabaseSessionHandler as BaseDatabaseSessionHandler;

class DatabaseSessionHandler extends BaseDatabaseSessionHandler
{
    protected function getQuery()
    {
        return $this->connection->table($this->table)->where('tenant_id', resolve(Tenant::class)->id);
    }

    protected function getDefaultPayload($data)
    {
        $result = parent::getDefaultPayload($data);

        $result['tenant_id'] = resolve(Tenant::class)->id;

        return $result;
    }
}
