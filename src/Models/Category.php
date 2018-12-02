<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\Model;
use Afaneh262\Iwan\Facades\Iwan;
use Afaneh262\Iwan\Traits\HasRelationships;
use Afaneh262\Iwan\Traits\Translatable;

class Category extends Model
{
    use Translatable,
        HasRelationships;

    protected $translatable = ['slug', 'name'];

    protected $table = 'categories';

    protected $fillable = ['slug', 'name'];

    public function posts()
    {
        return $this->hasMany(Iwan::modelClass('Post'))
            ->published()
            ->orderBy('created_at', 'DESC');
    }

    public function parentId()
    {
        return $this->belongsTo(self::class);
    }
}
