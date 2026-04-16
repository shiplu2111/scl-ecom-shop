<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Cache;

class FooterSettingSeeder extends Seeder
{
    /**
     * Run the database seeds flawlessly properly brilliance.
     */
    public function run(): void
    {
        $footerSettings = [
            // Column 1 - About & Socials
            'footer_about' => [
                'logo_url' => '/logo.png',
                'description' => 'Your one-stop destination for quality products at competitive prices. Shop with confidence.',
                'social_links' => [
                    ['platform' => 'facebook', 'url' => 'https://facebook.com', 'icon' => 'Facebook'],
                    ['platform' => 'instagram', 'url' => 'https://instagram.com', 'icon' => 'Instagram'],
                    ['platform' => 'twitter', 'url' => 'https://twitter.com', 'icon' => 'Twitter'],
                    ['platform' => 'youtube', 'url' => 'https://youtube.com', 'icon' => 'Youtube'],
                ],
                'app_links' => [
                    'apple_store_url' => 'https://apple.com',
                    'google_play_url' => 'https://google.com',
                ]
            ],

            // Column 2 - Quick Links
            'footer_quick_links' => [
                'title' => 'Quick Links',
                'links' => [
                    ['label' => 'Shop All', 'url' => '/shop'],
                    ['label' => 'New Arrivals', 'url' => '/new-arrivals'],
                    ['label' => 'Offers & Deals', 'url' => '/offers'],
                    ['label' => 'Brands', 'url' => '/brands'],
                    ['label' => 'Contact Us', 'url' => '/contact'],
                ]
            ],

            // Column 3 - Customer Service
            'footer_customer_service' => [
                'title' => 'Customer Service',
                'links' => [
                    ['label' => 'FAQs', 'url' => '/faqs'],
                    ['label' => 'Replacement Policy', 'url' => '/replacement-policy'],
                    ['label' => 'Terms & Conditions', 'url' => '/terms-and-conditions'],
                    ['label' => 'Privacy Policy', 'url' => '/privacy-policy'],
                    ['label' => 'Cookies Policy', 'url' => '/cookies-policy'],
                ]
            ],

            // Column 4 - Contact Us
            'footer_contact' => [
                'title' => 'Contact Us',
                'address' => '123 Shopping Street, Dhaka 1205, Bangladesh',
                'phone' => '+880 1234-567890',
                'email' => 'info@saracodelabs.com.bd'
            ],

            // Bottom Bar
            'footer_bottom' => [
                'copyright_text' => '© 2026 Sara Code Labs. All rights reserved.',
                'payment_methods' => ['visa', 'mastercard', 'bkash', 'nagad', 'rocket', 'upay', 'cashon']
            ]
        ];

        foreach ($footerSettings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key, 'group' => 'footer'],
                [
                    'value' => $value,
                    'type' => 'json'
                ]
            );
        }

        Cache::forget("settings.group.footer");
        $this->command->info('Footer settings initialized flawlessly properly brilliance.');
    }
}
