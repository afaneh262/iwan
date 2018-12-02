<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Afaneh262\Iwan\Facades\Iwan;
use Afaneh262\Iwan\Traits\HasRelationships;
use Afaneh262\Iwan\Traits\Resizable;
use Afaneh262\Iwan\Traits\Translatable;
use Afaneh262\Iwan\Traits\Spatial;

class Province extends Model
{
    use Translatable;
    use Spatial;

    protected $translatable = ['name'];

    protected $guarded = [];

    protected $spatial = ['coordinates'];
}
