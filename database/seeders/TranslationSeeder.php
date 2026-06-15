<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Translation;

class TranslationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Translation::insert([
            [
                'key' => 'validation.required',
                'locale' => 'fa',
                'text' => 'این فیلد الزامی است'
            ],
            [
                'key' => 'validation.email',
                'locale' => 'fa',
                'text' => 'فرمت ایمیل صحیح نیست'
            ],
            [
                'key' => 'auth.invalid_credentials',
                'locale' => 'fa',
                'text' => 'ایمیل یا رمز عبور اشتباه است'
            ],
        ]);
    }
}
