<?php

namespace App\Listeners;

use App\CardService;
use App\Events\UserLogged;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class UserLoggedNotification implements ShouldQueue
{
    use InteractsWithQueue;

    private CardService $cardService;

    public function __construct(CardService $cardService)
    {
        $this->cardService = $cardService;
    }

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public int $tries = 3;

    /**
     * Calculate the number of seconds to wait before retrying the job.
     *
     * @return array
     */
    public function backoff(): array
    {
        return [60, 600, 3600];
    }

    /**
     * Handle the event.
     *
     * @param \App\Events\UserLogged $event
     * @return void
     * @throws \Illuminate\Http\Client\RequestException
     */
    public function handle(UserLogged $event)
    {
        $this->cardService->UserLoggedNotification(['clientcardid' => $event->client_card_id]);
    }
}
