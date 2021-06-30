<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class UserLikePost extends Model
{
    protected $table = 'user_hide_post';
    protected $fillable = [
        'id', 'user_id', 'post_id'
    ];

    public function posts() {
   		return $this->belongsTo('App\Post');
    }
    public function users() {
        return $this->belongsTo('App\User');
 }
}
