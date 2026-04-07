<?php

use App\Models\Image;
use App\Models\Setting;
use App\Models\UserImage;
use Illuminate\Support\Facades\Auth;

if (!function_exists('pre')) {
    function pre($text)
    {
        print "<pre>";
        print_r($text);
        exit();
    }
}

if (!function_exists('isRegisterEnabled')) {
    function isRegisterEnabled()
    {
        return Setting::where('id', 1)
            ->where('param_name', 'register')
            ->value('param_value');
    }
}

if (!function_exists('imagesAssignToUser')) {
    function imagesAssignToUser($data) {
        $getimg = UserImage::where('user_id', Auth::id())->pluck('image_id')->toArray();

        foreach ($data as $msgdata) {
            if ($msgdata->image_ids) {
                $image_ids = explode(',', $msgdata->image_ids);
                foreach ($image_ids as $image_id) {
                    $image_exists = Image::where('id', $image_id)->exists();
                    if ($image_exists && !in_array($image_id, $getimg)) {
                        $user_image = new UserImage();
                        $user_image->user_id = Auth::id();
                        $user_image->image_id = $image_id;
                        $user_image->save();
                    }
                }
            }
        }
    }
}

if (!function_exists('makeLinksClickable')) {
    function makeLinksClickable($text) {
        // First strip any existing HTML tags to prevent double-processing
        $cleanText = strip_tags($text);
        
        // Convert URLs to clickable links
        $pattern = '/(https?:\/\/[^\s]+)/i';
        $replacement = '<a href="$1" target="_blank">$1</a>';
        return preg_replace($pattern, $replacement, $cleanText);
    }
}

?>