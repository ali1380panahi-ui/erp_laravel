<?php

use App\Models\Translation;

if (!function_exists('db_trans')) {
    function db_trans($key, $locale = 'fa')
    {
        $item = Translation::where('key', $key)
            ->where('locale', $locale)
            ->first();

        return $item ? $item->text : $key;
    }
}

