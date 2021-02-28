<?php

use App\Chat;
use App\Comment;
use App\Notification;
use App\Post;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->call([
            UserSeeder::class,
            ChatSeeder::class,
        ]);
        $posts = factory(Post::class, 15)->make();
        foreach ($posts as $post) {
            $post->save();
        }
        $comments = factory(Comment::class, 20)->make();
        foreach ($comments as $comment) {
            $comment->save();
        }
        factory(Notification::class, 50)->create();
    }
}
