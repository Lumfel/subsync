<?php

use App\Models\Admin;
use App\Models\Officer;
use App\Models\Resident;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

/**
 * The shared realtime bus — PRIVATE. Every signed-in portal member may listen
 * (all three guards are tried), but unauthenticated clients cannot subscribe.
 * Payloads deliberately carry no names (see SsePublishingObserver).
 */
Broadcast::channel('subsync', function ($user) {
    return $user instanceof Admin || $user instanceof Officer || $user instanceof Resident;
}, ['guards' => ['admin', 'officer', 'resident']]);
