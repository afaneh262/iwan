<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAdminAfter
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('iwan.admin.routing.after', $this->router);
    }
}
