<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Notification;
use Faker\Generator as Faker;

$factory->define(Notification::class, function (Faker $faker) {
    return [
        "user_id" => rand(1, 5),
        "description" => $faker->sentence(),
        "is_read" => false
    ];
});
