<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use DB;

class Search extends Model
{
    //
    protected $table = 'search';
    protected $fillable = [
        'id', 'user_id', 'keyword', 'index'
    ];
}