<?php

namespace App\Stats;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StatterFacade
{
    protected $queue = [];

    public function emit(StatableInterface $statable, $key, $value)
    {
        if ($statable instanceof Model) {
            $this->queue[] = [
                'id' => (string) Str::orderedUuid(),
                'created_at' => Carbon::now(),
                'key' => $key,
                'value' => $value,
                'statable_id' => $statable->getKey(),
                'statable_type' => get_class($statable),
                'tenant_id' => resolve('App\Tenant')->id,
                'hours' => intval(time() / 3600),
            ];
        }
    }

    public function save()
    {
        if (! empty($this->queue)) {
            DB::table('stats')->insert($this->queue);
            $this->queue = [];
        }
    }
}
