<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'icon' => 'Truck',
                'title' => 'Free Shipping',
                'subtitle' => 'On orders over ৳2,000',
                'sort_order' => 1
            ],
            [
                'icon' => 'ShieldCheck',
                'title' => 'Secure Payment',
                'subtitle' => '100% secure transactions',
                'sort_order' => 2
            ],
            [
                'icon' => 'CreditCard',
                'title' => 'Easy Returns',
                'subtitle' => '30 days return policy',
                'sort_order' => 3
            ],
            [
                'icon' => 'Headset',
                'title' => '24/7 Support',
                'subtitle' => 'Dedicated support team',
                'sort_order' => 4
            ],
        ];

        foreach ($features as $feature) {
            \App\Models\ServiceFeature::create($feature);
        }
    }
}
