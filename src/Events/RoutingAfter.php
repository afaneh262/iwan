<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAfter
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('iwan.routing.after', $this->router);
    }
}
