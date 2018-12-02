<?php

namespace Afaneh262\Iwan\Widgets;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Afaneh262\Iwan\Facades\Iwan;

class PageDimmer extends BaseDimmer
{
    /**
     * The configuration array.
     *
     * @var array
     */
    protected $config = [];

    /**
     * Treat this method as a controller action.
     * Return view() or other content to display.
     */
    public function run()
    {
        $count = Iwan::model('Page')->count();
        $string = trans_choice('iwan::dimmer.page', $count);

        return view('iwan::dimmer', array_merge($this->config, [
            'icon'   => 'iwan-file-text',
            'title'  => "{$count} {$string}",
            'text'   => __('iwan::dimmer.page_text', ['count' => $count, 'string' => Str::lower($string)]),
            'button' => [
                'text' => __('iwan::dimmer.page_link_text'),
                'link' => route('iwan.pages.index'),
            ],
            'image' => iwan_asset('images/widget-backgrounds/03.jpg'),
        ]));
    }

    /**
     * Determine if the widget should be displayed.
     *
     * @return bool
     */
    public function shouldBeDisplayed()
    {
        return Auth::user()->can('browse', Iwan::model('Page'));
    }
}
