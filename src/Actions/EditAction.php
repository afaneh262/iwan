<?php

namespace Afaneh262\Iwan\Actions;

class EditAction extends AbstractAction
{
    public function getTitle()
    {
        return __('iwan::generic.edit');
    }

    public function getIcon()
    {
        return 'iwan-edit';
    }

    public function getPolicy()
    {
        return 'edit';
    }

    public function getAttributes()
    {
        return [
            'class' => 'btn btn-sm btn-primary pull-right edit',
        ];
    }

    public function getDefaultRoute()
    {
        return route('iwan.'.$this->dataType->slug.'.edit', $this->data->{$this->data->getKeyName()});
    }
}
