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
        \App\Models\PaymentCredential::create([
            'name' => 'UddoktaPay',
            'environment' => 'sandbox',
            'merchant_id' => 'sandbox_uddoktapay_merchant_id',
            'secret_key' => 'sandbox_uddoktapay_secret_key',
            'callback_url' => 'https://sandbox.uddoktapay.com/callback',
            'is_active' => true,
        ]);
        
        $this->command->info('Payment credentials seeded successfully!');
    }
}
