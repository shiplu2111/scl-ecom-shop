<?php
/*
|--------------------------------------------------------------------------
| Migration: Remove Auto Confirm Free Delivery Setting
|--------------------------------------------------------------------------
|
| This migration removes the 'auto_confirm_free_delivery' setting from
| the 'settings' table as it's no longer required by the business logic.
|
*/

use Illuminate\Database\Migrations\Migration;
use App\Models\Setting;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Setting::where('key', 'auto_confirm_free_delivery')->delete();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: We don't want to restore it unless explicitly needed.
        // If we really wanted to, we could insert a default value here.
    }
};
