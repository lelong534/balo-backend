<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Post extends Model
{
    //
    protected $table = 'posts';
    protected $fillable = [
        'id', 'user_id', 'described', 'like', 'comment', 'is_hidden'
    ];

    public function images() {
   		return $this->hasMany('App\Image', 'post_id', 'id');
    }

    public function videos() {
   		return $this->hasMany('App\Video', 'post_id', 'id');
    }

    public function author() {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }

    public function likes() {
        return $this->hasMany('App\UserLikePost', 'post_id', 'id');
    }
}


