<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Comment;
use App\Search;
use App\ApiStatusCode;
use App\URL;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function search(Request $request) {

        $user_id = $request->query('user_id');
        $index = $request->query("index");
        $count = $request->query("count");
        $keyword = $request->query("keyword");
        $user = $request->user();

        if ($user_id == '' || $user->id == (int) $user_id || (int) $user_id < 0) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } 
        else 
        {
            if(!User::find($user_id) || User::find($user_id)->isBlocked()) {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "User is not validated"
                ];
            }

            if ($index == '' || $count == '' || $keyword == '') {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "PARAMETER TYPE INVALID"
                ];
            }

            if( (int) $index < 0 || (int) $count < 0) {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "PARAMETER TYPE INVALID"
                ];
            }
        }

        $index = (int) $index;
        $count = (int) $count;
        $result = [];

        $postBySearch = Post::where('content', 'LIKE', "%$keyword%")->get();

        $search = new Search ([
            'user_id' => $user->id,
            'keyword' => $keyword,
            'index' => $index
        ]);

        $search -> save();

        if($postBySearch == null) {
            return [
                "code" => ApiStatusCode::NO_DATA,
                "message" => "Post not found"
            ];
        } else {
            $postBySearch = array_slice($postBySearch, $count * $index, $count);
            foreach ($postBySearch as $item) {
                array_push($result, [
                    'id' => $item->id,
                    'image' => $item->image_link,
                    'video' => $item->video_link,
                    'like' => $item->like,
                    'described' => $item->content,
                    'comment' => Comment::where('id', $item->id)->count(),
                    'author' => User::where('id', $item->user_id)->get('id', 'username', 'avatar'),

                ]);
            };
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => [
                    "list_posts" => $result
                ]
            ];
        }

    }

    public function getSavedSearch(Request $request) {
        $index = $request->query("index");
        $count = $request->query("count");
        $user_id = $request->query('user_id');

        $user = $request->user();

        if ($user_id == '' || $user->id == (int) $user_id || (int) $user_id < 0) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            if($user->isBlocked()) {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "User is not validated"
                ];
            }

            if ($index == '' || $count == '') {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "PARAMETER TYPE INVALID"
                ];
            }

            if( (int) $index < 0 || (int) $count < 0) {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "PARAMETER TYPE INVALID"
                ];
            }
        }

        $index = (int) $index;
        $count = (int) $count;

        $result = [];

        $getSavedSearch = array_slice(Search::find()->get(), $count * $index, $count);

        foreach ($getSavedSearch as $item) {
            array_push($result, [
                'id' => $item->id,
                'keyword' => $item->keyword,
                'created' => $item->created_at

            ]);
        };
        return [
            "code" => ApiStatusCode::OK,
            "message" => "OK",
            "data" => [
                "list_saved_search" => $result
            ]
        ];
    }

    public function delSavedSearch($search_id) {

        $user = $request->user();

        if($user->isBlocked()) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "User is not validated"
            ];
        }
        
    	$search = Search::where('id', $search_id)->first();

    	if($search->delete()) {
    		return response()->json([
    			'code' => ApiStatusCode::OK,
    			'message' => 'Xóa tìm kiếm thành công'
    		]);
    	}
    }

}