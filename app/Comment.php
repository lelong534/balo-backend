<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $table = 'comments';
    protected $fillable = [
        'id', 'user_id', 'post_id', 'content'
    ];

    public function posts()
    {
        return $this->belongsTo('App\Post');
    }
    public function users()
    {
        return $this->belongsTo('App\User');
    }
}

