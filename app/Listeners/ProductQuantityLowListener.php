<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Events\ProductQuantityLow;
use App\Models\User;
use App\Notifications\ProductQuantityLowNotification;
use Illuminate\Support\Facades\Notification;

class ProductQuantityLowListener
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
    public function handle(ProductQuantityLow $event): void
    {
        Notification::send(User::query()->get(), new ProductQuantityLowNotification($event->product));
    }
}
