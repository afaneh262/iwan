<?php

namespace Afaneh262\Iwan\Traits;

use App\Models\Media;

trait Mediaable
{
    public function media()
    {
        return $this->morphToMany(Iwan::model('Media'), 'mediaable')
            ->withTimestamps()
            ->withPivot('collection');
    }

    public function getMedia($collection, $with_default = TRUE)
    {
        if (isset($collection) && !empty($collection)) {
            return $this->media()
                ->wherePivot('collection', $collection)
                ->get();
        } else if ($with_default) {
            return $this->media;
        }
    }

    public function getFirstMedia($collection)
    {
        $media = $this->getMedia($collection);

        if ($media && count($media) > 0) {
            return $media[0];
        }
    }
}
