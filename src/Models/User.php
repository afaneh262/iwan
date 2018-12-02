<?php

namespace Afaneh262\Iwan\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Afaneh262\Iwan\Contracts\User as UserContract;
use Afaneh262\Iwan\Traits\HasRelationships;
use Afaneh262\Iwan\Traits\IwanUser;

class User extends Authenticatable implements UserContract
{
    use IwanUser;
    use HasRelationships;

    protected $guarded = [];

    protected $casts = [
        'settings' => 'array',
    ];

    public function getAvatarAttribute($value)
    {
        if (is_null($value)) {
            return config('iwan.user.default_avatar', 'users/default.png');
        }

        return $value;
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function setLocaleAttribute($value)
    {
        $this->attributes['settings'] = collect($this->settings)->merge(['locale' => $value]);
    }

    public function getLocaleAttribute()
    {
        return $this->settings['locale'];
    }
}
