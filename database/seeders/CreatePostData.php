<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CreatePostData extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $user = User::find(1);
        $post = app(Post::class);
        $post->user_id = $user->id;
        $post->content = 'the first post created by kite';
        $post->save();
    }
}
