<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\UserReportPost;
use App\Enums\ApiStatusCode;
use App\Enums\URL;
use Illuminate\Support\Facades\Validator;

class UserReportPostController extends Controller
{
    //
    public function reportPost(Request $request, $id)
    {

        $validator = Validator::make($request->query(), [
            'described' => 'required'
        ]);
        if ($validator->fails()) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_NOT_ENOUGH,
                'message' => 'Số lượng parameter không dầy đủ',
                'data' => $validator->errors()
            ]);
        } else {
            $validator = Validator::make($request->query(), [
                'described' => 'string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    'message' => 'Kiểu tham số không đúng đắn',
                    'data' => $validator->errors()
                ]);
            }
        }
        $user_id = $request->user()->id;
        $report = new UserReportPost([
            'user_id' => $user_id,
            'post_id' => $id,
            'description' => $request->query("described"),
            'type' => 1
        ]);

        if ($report->save()) {
            return response()->json(
                [
                    'code' => ApiStatusCode::OK,
                    'message' => 'Report thành công',
                    'data' => [
                        'post_id' => $id,
                        'user_id' => $user_id,
                        'description' => $request->query("described")

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
