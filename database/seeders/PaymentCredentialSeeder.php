<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PaymentCredentialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\PaymentCredential::updateOrCreate(
            ['name' => 'UddoktaPay'],
            [
                'base_url' => 'https://sandbox.uddoktapay.com/api',
                'environment' => 'sandbox',
                'secret_key' => 'sandbox_uddoktapay_secret_key',
                'is_active' => true,
            ]
        );
        
        $this->command->info('Payment credentials seeded successfully!');
    }
}
