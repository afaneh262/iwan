<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAdmin
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('iwan.admin.routing', $this->router);
    }
}
