<?php

namespace Afaneh262\Iwan\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Model;
use Afaneh262\Iwan\Traits\HasRelationships;

/**
 * Class Media
 * @mixin \Eloquent
 */
class Media extends Model
{
    use SoftDeletes;
    use HasRelationships;

    protected $table = 'media';
    protected $appends = ['original_url', 'thumbnail_url', 'medium_url', 'large_url', 'o_url'];
    protected $guarded = ['id'];


    /**
     * Get the createdBy
     */
    public function createdBy()
    {
        return $this->belongsTo(Iwan::modelClass('User'), 'created_by', 'id');
    }

    public function getOUrlAttribute()
    {
        if ($this->hosted === 'local') {
            $original = Storage::disk(config('iwan.storage.disk'))->url($this->path . $this->uuid . '/' . $this->uuid . '_o.' . $this->original_extension);
        } else {
            $original = $this->path;
        }

        return $original;
    }

    public function getOriginalUrlAttribute()
    {
        if ($this->hosted === 'local') {
            $original = Storage::disk(config('iwan.storage.disk'))->url($this->path . $this->uuid . '/' . $this->uuid . '.' . $this->original_extension);
        } else {
            $original = $this->path;
        }

        return $original;
    }

    public function getThumbnailUrlAttribute()
    {
        if ($this->hosted === 'local') {
            $thumbnail = Storage::disk(config('iwan.storage.disk'))->url($this->path . $this->uuid . '/' . $this->uuid . '_thumbnail.jpg');
        } else {
            $thumbnail = $this->cloud_thumbnail;
        }

        return $thumbnail;
    }

    public function getMediumUrlAttribute()
    {
        if ($this->hosted === 'local') {
            $medium = Storage::disk(config('iwan.storage.disk'))->url($this->path . $this->uuid . '/' . $this->uuid . '_medium.jpg');
        } else {
            $medium = $this->cloud_thumbnail;
        }

        return $medium;
    }

    public function getLargeUrlAttribute()
    {
        if ($this->hosted === 'local') {
            $large = Storage::disk(config('iwan.storage.disk'))->url($this->path . $this->uuid . '/' . $this->uuid . '_large.jpg');
        } else {
            $large = $this->cloud_thumbnail;
        }

        return $large;
    }
}
