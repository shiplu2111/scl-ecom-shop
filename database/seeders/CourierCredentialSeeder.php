<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CourierCredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\CourierCredential::create([
            'name' => 'Stadefast',
            'environment' => 'sandbox',
            'api_key' => 'sandbox_stadefast_key',
            'api_secret' => 'sandbox_stadefast_secret',
            'account_id' => 'sandbox_stadefast_account',
            'is_active' => true,
        ]);

        \App\Models\CourierCredential::create([
            'name' => 'Pathao',
            'environment' => 'sandbox',
            'api_key' => 'sandbox_pathao_key',
            'api_secret' => 'sandbox_pathao_secret',
            'account_id' => 'sandbox_pathao_account',
            'is_active' => true,
        ]);
    }
}
