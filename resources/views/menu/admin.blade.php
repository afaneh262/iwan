<ol class="dd-list">

@foreach ($items as $item)

    <li class="dd-item" data-id="{{ $item->id }}">
        <div class="pull-right item_actions">
            <div class="btn btn-sm btn-danger pull-right delete" data-id="{{ $item->id }}">
                <i class="iwan-trash"></i> {{ __('iwan::generic.delete') }}
            </div>
            <div class="btn btn-sm btn-primary pull-right edit"
                data-id="{{ $item->id }}"
                data-title="{{ $item->title }}"
                data-url="{{ $item->url }}"
                data-target="{{ $item->target }}"
                data-icon_class="{{ $item->icon_class }}"
                data-color="{{ $item->color }}"
                data-route="{{ $item->route }}"
                data-parameters="{{ json_encode($item->parameters) }}"
            >
                <i class="iwan-edit"></i> {{ __('iwan::generic.edit') }}
            </div>
        </div>
        <div class="dd-handle">
            @if($options->isModelTranslatable)
                @include('iwan::multilingual.input-hidden', [
                    'isModelTranslatable' => true,
                    '_field_name'         => 'title'.$item->id,
                    '_field_trans'        => json_encode($item->getTranslationsOf('title'))
                ])
            @endif
            <span>{{ $item->title }}</span> <small class="url">{{ $item->link() }}</small>
        </div>
        @if(!$item->children->isEmpty())
            @include('iwan::menu.admin', ['items' => $item->children])
        @endif
    </li>

@endforeach

</ol>
