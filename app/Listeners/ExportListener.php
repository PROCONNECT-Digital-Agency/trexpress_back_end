<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Registered;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Export
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    
    private $data;

    public function __constructor($data) {
        $this->data = $data;
    }
    /**
     * Handle the event.
     *
     * @param Registered $event
     * @return void
     */
    public function handle(Registered $event)
    {

    }
}
