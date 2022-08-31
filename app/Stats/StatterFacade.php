<?php

namespace App\Stats;

use Illuminate\Support\Facades\DB;

class StatterFacade
{
    protected $queue = [];

    public function emit(StatableInterface $statable, $key, $value)
    {
        $this->queue[] = [
            'key' => $key,
            'value' => $value,
            'statable_id' => $statable->getKey(),
            'statable_type' => get_class($statable),
            'tenant_id' => resolve('App\Tenant')->id
        ];
    }

    public function save()
    {
        if (!empty($this->queue)) {
            DB::table('stats')->insert($this->queue);
            $this->queue = [];
        }
    }
}
