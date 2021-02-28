<?php

use App\Enums\FriendStatus;
use App\Friends;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $user = new User();
        $user->email = 'admin1@gmail.com';
        $user->name = 'Admin1';
        $user->phone_number = '0987654321';
        $user->password = Hash::make('123456');
        $user->save();
        $user = new User();
        $user->email = 'admin2@gmail.com';
        $user->name = 'Admin2';
        $user->phone_number = '0987654322';
        $user->password = Hash::make('123456');
        $user->save();
        $user = new User();
        $user->email = 'admin3@gmail.com';
        $user->name = 'Admin3';
        $user->phone_number = '0987654323';
        $user->password = Hash::make('123456');
        $user->is_blocked = true;
        $user->save();
        factory(User::class, 20)->create();
        factory(Friends::class, 50)->create(['status' => FriendStatus::ACCEPTED]);
        $friendRequested = [[
            "user_id" => 1,
            "friend_id" => 2,
            'status' => FriendStatus::REQUESTED
        ], [
            "user_id" => 1,
            "friend_id" => 4,
            'status' => FriendStatus::REQUESTED
        ], [
            "user_id" => 1,
            "friend_id" => 5,
            'status' => FriendStatus::REQUESTED
        ], [
            "user_id" => 1,
            "friend_id" => 6,
            'status' => FriendStatus::REQUESTED
        ]];
        foreach ($friendRequested as $requested) {
            Friends::where([
                'user_id' => $requested["user_id"],
                'friend_id' => $requested["friend_id"]
            ])->orWhere([
                'user_id' => $requested["friend_id"],
                'friend_id' => $requested["user_id"]
            ])->delete();
            factory(Friends::class)->create($requested);
        }
    }
}
