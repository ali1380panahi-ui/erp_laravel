<?php

namespace App\Traits;

use Illuminate\Support\Facades\DB;

trait HandlesModelTransactions
{

    public function withRowLock(string $modelClass, $id, \Closure $callback)
    {
        return DB::transaction(function () use ($modelClass, $id, $callback) {
            $lockedModel = $modelClass::where('id', $id)->lockForUpdate()->firstOrFail();

            return $callback($lockedModel);
        });
    }
    
    public function withTransaction(\Closure $callback)
    {
        return DB::transaction($callback);
    }

}
