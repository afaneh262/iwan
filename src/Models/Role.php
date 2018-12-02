<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\Model;
use Afaneh262\Iwan\Facades\Iwan;
use Afaneh262\Iwan\Traits\HasRelationships;

class Role extends Model
{
    use HasRelationships;

    protected $guarded = [];

    public function users()
    {
        $userModel = Iwan::modelClass('User');

        return $this->belongsToMany($userModel, 'user_roles')
                    ->select(app($userModel)->getTable().'.*')
                    ->union($this->hasMany($userModel))->getQuery();
    }

    public function permissions()
    {
        return $this->belongsToMany(Iwan::modelClass('Permission'));
    }
}
