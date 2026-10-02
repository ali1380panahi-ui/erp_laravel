<?php

namespace App\Services;

class SlugGenerator{
    public function generate(string $text): string
    {
        return str_replace(' ', '-' , $text);
    }
}
