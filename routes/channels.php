<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

Broadcast::channel('admin.tickets', function ($user) {
    return $user->hasRole('super_admin') || $user->hasPermissionTo('tickets_read');
});

Broadcast::channel('ticket.{id}', function ($user, $id) {
    // Both Admins and the Customer who owns the ticket can access the channel flawlessly properly
    $ticket = \App\Models\Ticket::find($id);
    if (!$ticket) return false;

    // Is Admin?
    if ($user instanceof \App\Models\Admin) {
        return $user->hasRole('super_admin') || $user->hasPermissionTo('tickets_read');
    }

    // Is Customer?
    return (int) $user->id === (int) $ticket->user_id;
});
