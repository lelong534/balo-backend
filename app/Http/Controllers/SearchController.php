<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\User;
use App\Comment;
use App\Search;
use App\URL;
use App\Enums\ApiStatusCode;
use Illuminate\Support\Facades\Validator;

class SearchController extends Controller
{
    public function search(Request $request) {

        $index = $request->index;
        $count = $request->count;
        $keyword = $request->keyword;
        $currentUser = $request->user();

        if ($index === null || $count === null || $keyword === null) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "Tham số không đầy đủ"
            ];
        }

        if( (int) $index < 0 || (int) $count < 0) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }

        $index = (int) $index;
        $count = (int) $count;
        $result = [];

        $user = User::where('name', 'like', '%'.$keyword.'%')
            ->orWhere('phone_number', 'like', '%'.$keyword.'%')
            ->orWhere('email', 'like', '%'.$keyword.'%')
            ->get();

        foreach ($user as $item) {
            array_push($result, [
                "id" => $item->id,
                "username" => $item->name,
                "avatar" => $item->avatar,
                "email" => $item->email,
                "phonenumber" => $item->phone_number,
            ]);
        };

        if($result == null) {
            return [
                "code" => 9994,
                "message" => "User not found"
            ];
        } else {
            // $result = array_slice($result, $index, $count);
            
            return [
                "code" => 1000,
                "message" => "OK",
                "data" => [
                    "users" => $result
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