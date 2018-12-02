<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;

class TableChanged
{
    use SerializesModels;

    public $name;

    public function __construct($name)
    {
        $this->name = $name;
    }
}
