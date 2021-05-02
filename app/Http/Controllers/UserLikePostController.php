<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\UserLikePost;
use App\User;
use App\Enums\ApiStatusCode;
use App\Enums\URL;
use Illuminate\Support\Facades\Validator;

class UserLikePostController extends Controller
{
    //
    public function likePost(Request $request)
    {
        $post_id = $request->id;
        $user = $request->user();
        if (UserLikePost::where("user_id", $user->id)->where("post_id", $post_id)->exists()) return [
            "code" => ApiStatusCode::NO_DATA,
            "message" => "Đã thích bài viết"
        ];
        $like = new UserLikePost([
            'user_id' => $user->id,
            'post_id' => $post_id,
        ]);
        $post = Post::find($post_id);
        if ($post == null) {
            return [
                "code" => 9992,
                "message" => "Bài viết không tồn tại"
            ];
        }
        if ($like->save()) {
            $post = Post::find($post_id);
            $post->like += 1;
            $post->save();
            return response()->json(
                [
                    'code' => ApiStatusCode::OK,
                    'message' => 'Liked',
                    'data' => [
                        'post_id' => $post_id,
                        'user' => $like->user_id
                    ]
                ]

            );
        } else return response()->json(
            [
                'code' => ApiStatusCode::LOST_CONNECTED,
                'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
            ]
        );
    }

    public function unLikePost(Request $request)
    {
        $post_id = $request->id;
        $user = $request->user();
        $userLikePost = UserLikePost::where("user_id", $user->id)->where("post_id", $post_id)->first();
        if (!$userLikePost) return [
            "code" => 9992,
            "message" => "Chưa thích bài viết",
        ];
        if ($userLikePost->delete()) {
            return response()->json([
                'code' => ApiStatusCode::OK,
                'message' => 'Đã bỏ thích bài viết'
            ]);
        }
    }

    public function getlikePost(Request $request)
    {
        $id = $request->id;
        $post = Post::where('id', $id)->first();
        $like = UserLikePost::where('post_id', $id)->count();


        if ($post == null) {
            return [
                "code" => 9992,
                "message" => "Bài viết không tồn tại"
            ];
        } else {
            return response()->json([

                'code' => ApiStatusCode::OK,
                'message' => 'Lấy số like thành công',
                'data' => [
                    'post_id' => $post,
                    'count_like' => $like
                ],


            ]);
        }
    }
}
