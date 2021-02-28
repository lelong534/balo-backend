<?php

use App\Chat;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $amount = 5;
        $this->makeChat(1, 2, $amount);
        $this->makeChat(2, 1, $amount);
        $this->makeChat(1, 3, $amount);
        $this->makeChat(2, 3, $amount);
        $this->makeChat(4, 3, $amount);
        $this->makeChat(3, 1, $amount);
        $this->makeChat(3, 2, $amount);
        $this->makeChat(3, 4, $amount);
        $this->makeChat(2, 4, $amount);
        $this->makeChat(1, 4, $amount);
    }

    public function makeChat($u1, $u2, $amount)
    {
        $chats = factory(Chat::class, $amount)->make([
            "user_a_id" => $u1,
            "user_b_id" => $u2
        ]);
        foreach ($chats as $chat) {
            $chat->save();
        }
    }
}
