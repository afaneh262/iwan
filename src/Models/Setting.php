<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    protected $table = 'settings';

    protected $guarded = [];

    public $timestamps = false;
}
