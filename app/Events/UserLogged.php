<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Event fires when user is successfully logged in
 */
class UserLogged
{
    use Dispatchable;

    public string $client_card_id;

    public function __construct(string $client_card_id)
    {
        $this->client_card_id = $client_card_id;
    }
}
