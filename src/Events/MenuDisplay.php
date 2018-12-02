<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;
use Afaneh262\Iwan\Models\Menu;

class MenuDisplay
{
    use SerializesModels;

    public $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;

        // @deprecate
        //
        event('iwan.menu.display', $menu);
    }
}
