<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Image extends Model
{
    use HasFactory;
    public function userImages()
    {
        return $this->hasMany(UserImage::class, 'image_id');
    }
    public function getImageUrl()
    {
        if($this->image_path)
        {
            if(Storage::disk('local')->exists("public/uploaded_images/" . $this->image_path))
            {
                return asset('storage/uploaded_images')."/".$this->image_path;
            }
        }
    }
}
