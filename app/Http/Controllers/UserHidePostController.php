<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\UserHidePost;
use App\User;
use App\Enums\ApiStatusCode;
use App\Enums\URL;
use Illuminate\Support\Facades\Validator;

class UserHidePostController extends Controller
{
    //
    public function hidePost(Request $request)
    {
        $post_id = $request->id;
        $user = $request->user();

        $hide = new UserHidePost([
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
        if ($hide->save()) {
            return response()->json(
                [
                    'code' => ApiStatusCode::OK,
                    'message' => 'Đã ẩn bài viết',
                    'data' => [
                        'post_id' => $post_id,
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
}
