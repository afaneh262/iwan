<?php

namespace Afaneh262\Iwan\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class IwanEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Afaneh262\Iwan\Events\BreadAdded' => [
            'Afaneh262\Iwan\Listeners\AddBreadMenuItem',
            'Afaneh262\Iwan\Listeners\AddBreadPermission',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
