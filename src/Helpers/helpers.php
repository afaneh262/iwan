<?php
use Intervention\Image\Facades\Image;
use Illuminate\Support\Str;
use Intervention\Image\Constraint;

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return Afaneh262\Iwan\Facades\Iwan::setting($key, $default);
    }
}

if (!function_exists('menu')) {
    function menu($menuName, $type = null, array $options = [])
    {
        return Afaneh262\Iwan\Facades\Iwan::model('Menu')->display($menuName, $type, $options);
    }
}

if (!function_exists('iwan_asset')) {
    function iwan_asset($path, $secure = null)
    {
        return asset(config('iwan.assets_path').'/'.$path, $secure);
    }
}

if (!function_exists('input')) {
    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    function input($key = '', $default = null)
    {
        return (Illuminate\Support\Facades\Input::has($key) ? Illuminate\Support\Facades\Input::get($key) : $default);
    }
}


if (!function_exists('upload_media_resource')) {
    function upload_media_resource($file, $resource)
    {
        $uuid = (string)Str::uuid();
        $original_name = $file->getClientOriginalName();
        $size = $file->getClientSize();
        $original_extension = $file->getClientOriginalExtension();
        $mime_type = $file->getClientMimeType();
        list($fileType) = explode("/", $mime_type);

        $path = 'uploads/';
        $file->storeAs($path . $uuid, $uuid . '.' . $original_extension, config('iwan.storage.disk'));

        if ($fileType == 'image') {
            //Save with orginal width and height

            $sizes = [
                'user_avatar' => [300, 300],
                'thumbnail' => [555, 350],
                'medium' => [1200, 630],
                'large' => [1900, 900],
            ];

            foreach ($sizes as $key => $value) {
                $image = Image::make($file->getRealPath())
                    ->fit($value[0], $value[1])
                    ->encode('jpg', 75);

                Storage::disk(config('iwan.storage.disk'))->put($path . $uuid . '/' . $uuid . '_' . $key . '.jpg', (string) $image, 'public');
            }

            $image = Image::make($file->getRealPath());
            $resize_width = $image->width();
            $resize_height = $image->height();
            $resize_quality = 75;

            $image->fit($resize_width, $resize_height)
                ->encode($file->getClientOriginalExtension(), $resize_quality);

            Storage::disk(config('iwan.storage.disk'))->put($path . $uuid . '/' . $uuid . '_o.'.$file->getClientOriginalExtension(), (string) $image, 'public');
        }

        $media = Iwan::model('Media')::create(array(
            'original_name' => $original_name,
            'original_extension' => $original_extension,
            'size' => $size,
            'path' => $path,
            'uuid' => $uuid,
            'mime_type' => $mime_type,
            'hosted' => 'local',
            'type' => $fileType,
            'resource' => $resource
        ));

        return $media;
    }
}


if (!function_exists('uploadImageFromUrl')) {
    function uploadImageFromUrl($url, $collection)
    {
        $file = Image::make($url);
        $uuid = (string)Str::uuid();
        $size = $file->filesize();
        $original_extension = pathinfo($url, PATHINFO_EXTENSION);
        $original_name = basename(str_replace("." . $original_extension, "", basename($url)));
        $mime_type = $file->mime();
        list($fileType) = explode("/", $mime_type);

        $path = 'uploads/';

        Storage::disk('s3')->put($path . $uuid, $uuid . '.' . $original_extension, (string)$file->encode());

        $sizes = [
            'thumbnail_user' => [350, 350],
            'thumbnail' => [350, 250],
            'medium' => [1200, 630],
            'large' => [1900, 900],
        ];

        foreach ($sizes as $key => $value) {
            $image = Image::make($url)
                ->fit($value[0], $value[1]);

            Storage::disk('s3')->put($path . $uuid . '/' . $uuid . '_' . $key . '.jpeg', (string)$image->encode());
        }

        $media = Media::create(array(
            'original_name' => $original_name,
            'original_extension' => $original_extension,
            'size' => $size,
            'path' => $path,
            'uuid' => $uuid,
            'mime_type' => $mime_type,
            'hosted' => 'local',
            'type' => $fileType,
            'collection' => $collection
        ));

        return $media;
    }
}



