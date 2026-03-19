<?php

$dir = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(__DIR__ . '/app/Http/Controllers/API/V1'));

foreach ($dir as $file) {
    if ($file->isDir() || $file->getExtension() !== 'php') continue;

    $path = $file->getRealPath();
    $content = file_get_contents($path);

    // Determine group
    $group = 'Public';
    $className = $file->getBasename('.php');

    if (strpos($path, DIRECTORY_SEPARATOR . 'Admin') !== false || str_starts_with($className, 'Admin') || in_array($className, ['RoleController', 'PermissionController', 'InventoryController', 'SettingsController', 'CourierController', 'ActivityLogController'])) {
        $group = 'Admin';
    } elseif (in_array($className, ['AuthController', 'WishlistController', 'CartController', 'OrderController', 'UserController', 'PaymentController', 'NotificationController'])) {
        $group = 'Customer';
    }

    if (!str_contains($content, '@group ')) {
        $content = preg_replace('/(class\s+[a-zA-Z0-9_]+\s+extends)/', "/**\n * @group {$group}\n */\n$1", $content);
        file_put_contents($path, $content);
    }
}
echo "Done injecting docs.";
