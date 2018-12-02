<?php

namespace Afaneh262\Iwan\Events;

use Illuminate\Queue\SerializesModels;
use Afaneh262\Iwan\Database\Schema\Table;

class TableAdded
{
    use SerializesModels;

    public $table;

    public function __construct(Table $table)
    {
        $this->table = $table;

        event(new TableChanged($table->name, 'Added'));
    }
}
