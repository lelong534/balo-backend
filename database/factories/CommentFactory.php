<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Comment;
use Faker\Generator as Faker;

$factory->define(Comment::class, function (Faker $faker) {
    return [
        "user_id" => rand(1, 3),
        "post_id" => rand(1, 10),
        "content" => $faker->sentence()
    ];
});
