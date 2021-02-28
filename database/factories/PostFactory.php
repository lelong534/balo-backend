<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Post;
use Faker\Generator as Faker;

$factory->define(Post::class, function (Faker $faker) {
    return [
        "user_id" => rand(1, 3),
        "content" => $faker->sentence(),
        "image_link" => "https://picsum.photos/370",
        "video_link" => "https://picsum.photos/350"
    ];
});
