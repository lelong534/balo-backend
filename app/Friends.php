<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Friends extends Model
{
    const MAX_FRIENDS = 50;
    public $fillable = ["user_id", "friend_id", "status"];
}
