<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    //
    protected $table = 'images';
    protected $fillable = [
        'id', 'post_id', 'image_sort', 'link'
    ];
}
