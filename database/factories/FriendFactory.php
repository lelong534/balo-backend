<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Enums\FriendStatus;
use App\Friends;
use Faker\Generator as Faker;

$factory->define(Friends::class, function (Faker $faker) {
    $user_id = rand(1, 10);
    do {
        $friend_id = rand(1, 10);
    } while ($friend_id == $user_id);
    return [
        "user_id" => $user_id,
        "friend_id" => $friend_id
    ];
});
