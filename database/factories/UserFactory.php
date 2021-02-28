<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\User;
use Faker\Generator as Faker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/

$factory->define(User::class, function (Faker $faker) {
    $phoneNumber = '';
    for ($i = 0; $i < 10; $i++) {
        $phoneNumber .= (string)rand(0, 9);
    }
    return [
        'name' => $faker->name,
        'email' => $faker->safeEmail,
        'phone_number' => $phoneNumber,
        'password' => Hash::make('123456')
    ];
});
