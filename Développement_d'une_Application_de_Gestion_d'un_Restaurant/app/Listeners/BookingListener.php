<?php

namespace App\Listeners;

use App\Events\BookingEvent;
use App\Mail\BookingMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Mail;

class BookingListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BookingEvent $event): void
    {
        Mail::to($event->booking->user->email)->send(new BookingMail($event->booking));
        //
    }
}
