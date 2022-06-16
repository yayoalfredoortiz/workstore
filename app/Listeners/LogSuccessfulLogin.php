<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogSuccessfulLogin
{

    /**
     * Handle the event.
     *
     * @param  Login  $event
     * @return void
     */

    public function handle(Login $event)
    {
        $user = $event->user;
        $user->last_login = date('Y-m-d H:i:s'); /* @phpstan-ignore-line */
        $user->save();
    }

}
