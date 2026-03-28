<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.tickets', function ($user) {
    // Basic check: if it's an admin
    // If you have a specific admin model or role, check it here
    // For now, let's assume any authenticated user with admin-like permissions can see it
    return $user->hasRole('super_admin') || $user->hasPermissionTo('tickets_read');
});
