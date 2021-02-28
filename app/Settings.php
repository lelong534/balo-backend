<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Settings extends Model
{
    public $fillable = [
        "user_id",
        "like_comment",
        "from_friends",
        "requested_friend",
        "suggested_friend",
        "birthday",
        "video",
        "report",
        "sound_on",
        "notification_on",
        "vibrant_on",
        "led_on"
    ];
}
