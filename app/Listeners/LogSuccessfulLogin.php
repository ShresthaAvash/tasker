<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB; // Import DB facade

class LogSuccessfulLogin
{
    public function handle(Login $event): void
    {
        // Insert a record into our new activity_logs table
        DB::table('activity_logs')->insert([
            'user_id' => $event->user->id,
            'action' => 'Logged In',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}