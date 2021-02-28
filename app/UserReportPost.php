<?php

namespace App;
use DB;
use Illuminate\Database\Eloquent\Model;

class UserReportPost extends Model
{
    protected $table = 'user_report_post';
    protected $fillable = [
        'id', 'user_id', 'post_id', 'type','description'
    ];

    public function user() {
   		return $this->belongsTo('App\User');
    }
    public function post() {
        return $this->belongsTo('App\Post');
 }
}
