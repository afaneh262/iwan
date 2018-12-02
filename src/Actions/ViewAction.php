<?php

namespace Afaneh262\Iwan\Actions;

class ViewAction extends AbstractAction
{
    public function getTitle()
    {
        return __('iwan::generic.view');
    }

    public function getIcon()
    {
        return 'iwan-eye';
    }

    public function getPolicy()
    {
        return 'read';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-warning pull-right view',
        ];
    }

    public function getDefaultRoute()
    {
        return route('iwan.'.$this->dataType->slug.'.show', $this->data->{$this->data->getKeyName()});
    }
}
