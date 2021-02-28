<?php

namespace App\Http\Controllers;

use App\Block;
use App\Enums\ApiStatusCode;
use App\Enums\FriendStatus;
use App\Friends;
use App\Notification;
use App\Service\IFileService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    protected $fileService;

    public function __construct(IFileService $fileService)
    {
        $this->fileService = $fileService;
    }

    public function getRequestedFriends(Request $request)
    {
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            $user = $request->user();
            $result = [];
            $count = (int)$count;
            $index = (int)$index;
            $requestedFriends = $user->getFriendRequest();
            foreach ($requestedFriends as $item) {
                array_push($result, [
                    "id" => $item->id,
                    "username" => $item->name,
                    "avatar" => $item->avatar,
                    "same_friends" => $user->getSameFriends($item->id),
                    "created" => $item->created_at,
                ]);
            };
            $result = array_slice($result, $count * $index, $count);
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => [
                    "requested" => $result,
                    "total" => count($result)
                ]
            ];
        }
    }

    public function getFriends(Request $request)
    {
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            $user = $request->user();
            $result = [];
            $count = (int)$count;
            $index = (int)$index;
            $requestedFriends = array_slice($user->getFriends(), $count * $index, $count);
            foreach ($requestedFriends as $item) {
                array_push($result, [
                    "id" => $item->id,
                    "username" => $item->name,
                    "avatar" => $item->avatar,
                    "same_friends" => $user->getSameFriends($item->id),
                    "created" => $item->created_at,
                ]);
            };
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => [
                    "friends" => $result,
                    "total" => count($result)
                ]
            ];
        }
    }

    public function getSuggestedFriends(Request $request)
    {
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            $user = $request->user();
            $result = [];
            $suggestedFriends = [];
            $count = (int)$count;
            $index = (int)$index;
            $friends = $request->user()->getFriends();
            foreach ($friends as $friend) {
                $suggestedFriends = array_merge($suggestedFriends, $friend->getFriends());
            };
            $suggestedFriends = array_slice($suggestedFriends, $count * $index, $count);
            foreach ($suggestedFriends as $item) {
                array_push($result, [
                    "id" => $item->id,
                    "username" => $item->name,
                    "avatar" => $item->avatar,
                    "same_friends" => $user->getSameFriends($item->id),
                    "created" => $item->created_at,
                ]);
            };
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => [
                    "list_users" => $result,
                    "total" => count($result)
                ]
            ];
        }
    }

    public function setRequestFriends(Request $request)
    {
        $user_id = $request->query("user_id");
        $user = $request->user();
        if ($user_id == '' || $user->id == (int)$user_id || (int)$user_id < 0) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else if (count($user->getFriends()) > Friends::MAX_FRIENDS) {
            return [
                "code" => ApiStatusCode::NO_DATA,
                "message" => "User friend is max"
            ];
        } else {
            $requestedFriend = User::find((int)$user_id);
            if ($requestedFriend == null) {
                return [
                    "code" => ApiStatusCode::NOT_EXISTED,
                    "message" => "Not existed user"
                ];
            } else {
                $relation = Friends::where("user_id", $user->id)
                    ->where("friend_id", (int)$user_id)->get();
                if ($relation->isEmpty()) {
                    Friends::create([
                        "user_id" => $user->id,
                        "friend_id" => (int)$user_id,
                        "status" => FriendStatus::REQUESTED
                    ]);
                } else {
                    $relation[0]->delete();
                }
                return [
                    "code" => ApiStatusCode::OK,
                    "message" => "OK",
                    "data" => [
                        "requested_friends" => count($user->getFriendRequest())
                    ]
                ];
            }
        }
    }

    public function setFriends(Request $request)
    {
        $user = $request->user();
        if ($request->query("user_id") == '' || $request->query("is_accept") == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        }
        $friends = Friends::where("user_id", $user->id)
            ->where("friend_id", (int)$request->query("user_id"))->get();
        if ($friends->isEmpty()) {
            return [
                "code" => ApiStatusCode::NOT_EXISTED,
                "message" => "Not exist"
            ];
        } else if ($friends[0]->status == FriendStatus::ACCEPTED) {
            return [
                "code" => ApiStatusCode::NOT_EXISTED,
                "message" => "User already friend"
            ];
        } else {
            $relation = $friends[0];
            $is_accept = (int)$request->query("is_accept");
            if ($is_accept == 0 || $is_accept == 1) {
                if ($is_accept == 0) {
                    $relation->delete();
                } else if ($is_accept == 1) {
                    $relation->status = FriendStatus::ACCEPTED;
                    $relation->save();
                }
                return [
                    "code" => ApiStatusCode::OK,
                    "message" => "OK"
                ];
            } else {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "Is Accept invalid"
                ];
            }
        }
    }

    public function getInfo(Request $request, $id)
    {
        $user = $request->user();
        if ($user->id != $id) {
            return [
                "code" => ApiStatusCode::NOT_VALIDATE,
                "message" => "User is not validated"
            ];
        }
        if ($user == null) {
            return [
                "code" => 9994,
                "message" => "User not found"
            ];
        } else if (false) {
            // nguoi dung $id chan tai khoan nguoi dung request
        } else {
            return [
                "code" => 1000,
                "message" => "OK",
                "data" => [
                    "id" => $user["id"],
                    "username" => $user["name"],
                    "created" => $user["created_at"],
                    "avatar" => $user["avatar"],
                    "cover_image" => $user["cover_image"],
                    "address" => $user["address"],
                    "city" => $user["city"],
                    "country" => $user["country"],
                    "listing" => -1, // list friends
                    "is_friend" => -1,
                    "online" => false
                ]
            ];
        }
    }

    public function setReadNotification(Request $request)
    {
        $notificationId = $request->query("notification_id");
        if ($notificationId == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            $user = $request->user();
            $notificationId = (int)$notificationId;
            $notifs = Notification::where("user_id", $user->id)->where("id", $notificationId)->get();
            if ($notifs->isEmpty()) {
                return [
                    "code" => ApiStatusCode::NOT_EXISTED,
                    "message" => "Not existed notification id: " . $notificationId
                ];
            } else {
                $notifs[0]->is_read = true;
                $notifs[0]->save();
                return [
                    "code" => ApiStatusCode::OK,
                    "message" => "OK"
                ];
            }
        }
    }

    public function getNotifications(Request $request)
    {
        $index = $request->query("index");
        $count = $request->query("count");
        if ($index == '' || $count == '') {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "PARAMETER TYPE INVALID"
            ];
        } else {
            $user = $request->user();
            $count = (int)$count;
            $index = (int)$index;
            $notifications = $user->notifications->toArray();
            $notifications = array_slice($notifications, $count * $index, $count);
            $notifications = array_map(function ($item) {
                unset($item["user_id"]);
                unset($item["updated_at"]);
                return $item;
            }, $notifications);
            return [
                "code" => ApiStatusCode::OK,
                "message" => "OK",
                "data" => $notifications,
                "last_update" => now()
            ];
        }
    }

    public function setUserInfo(Request $request)
    {
        $user = $request->user();
        $validator = Validator::make($request->query(), [
            'username' => 'string',
            "description" => "string|max:150",
            'avatar' => 'file|max:1024',
            "address" => "string",
            "city" => "string",
            "country" => "string",
            'cover_image' => 'file|max:1024',
            "link" => "url",
        ]);
        if ($validator->fails()) {
            return [
                "code" => 1003,
                "message" => "Parameter type is invalid",
                "data" => $validator->errors()
            ];
        } else {
            if ($user->avatar != null) {
                $this->fileService->deleteFile($user->avatar);
            }
            if ($user->cover_image != null) {
                $this->fileService->deleteFile($user->cover_image);
            }
            $linkAvatar = $this->fileService->saveFile($request->file("avatar"));
            $user->avatar = $linkAvatar;
            $linkCoverImage = $this->fileService->saveFile($request->file("cover_image"));
            $user->cover_image = $linkCoverImage;
            $user["name"] = $request->query("username");
            $user["description"] = $request->query("description");
            $user["address"] = $request->query("address");
            $user["city"] = $request->query("city");
            $user["country"] = $request->query("country");
            $user["link"] = $request->query("link");
            $user->save();
            return [
                "code" => 1000,
                "message" => "OK",
                "data" => [
                    "avatar" => Storage::url($linkAvatar),
                    "cover_image" => Storage::url($linkCoverImage),
                    "link" => $user->link,
                    "city" => $user->city,
                    "country" => $user->country,
                ]
            ];
        }
    }

    public function changeInfoAfterSignup(Request $request)
    {
        $user = $request->user();
        $fileValidator = Validator::make($request->all(), [
            'avatar' => 'file|max:1024',
        ]);
        if ($fileValidator->fails()) {
            return [
                "code" => 1006,
                "message" => "File size is too big",
            ];
        } else if (strcmp($user->phone_number, $request->query("username")) == 0) {
            return [
                "code" => 1004,
                "message" => "User name is invalid",
            ];
        } else {
            $user->name = $request->query("username");
            if ($request->file("avatar") != null) {
                $linkAvatar = $this->fileService->saveFile($request->file("avatar"));
                if ($user->avatar != null) {
                    $this->fileService->deleteFile($user->avatar);
                };
                $user->avatar = $linkAvatar;
            } else {
                $linkAvatar = "";
            }
            $user->save();
            return [
                "code" => 1000,
                "message" => "OK",
                "data" => [
                    "id" => $user->id,
                    "username" => $user->name,
                    "phonenumber" => $user["phone_number"],
                    "created" => $user["created_at"],
                    "avatar" => Storage::url($linkAvatar),
                ]
            ];
        }
    }

    public function getBlock(Request $request)
    {
        $user = $request->user();
        $blocks = Block::select("blocker_id")->where("user_id", $user->id)->get();
        $blocks = array_map(function ($item) {
            $user = User::find($item["blocker_id"]);
            if ($user != null && !$user->is_blocked) {
                return [
                    "id" => $user->id,
                    "name" => $user->name,
                    "avatar" => $user->avatar
                ];
            }
        }, $blocks->toArray());
        return [
            "code" => 1000,
            "message" => "OK",
            "data" => $blocks
        ];
    }

    public function setBlock(Request $request, $user_id)
    {
        $validator = Validator::make($request->query(), [
            "type" => "required|numeric"
        ]);
        if ($validator->fails()) {
            return [
                "code" => 1003,
                "message" => "Parameter type is invalid",
                "data" => $validator->errors()
            ];
        } else {
            $type = (int)$request->query("type");
            $user_id = (int)$user_id;
            if ($type != 0 && $type != 1) {
                return [
                    "code" => 1003,
                    "message" => "Trường Type có giá trị sai"
                ];
            } else if (!User::find($user_id) || User::find($user_id)->isBlocked()) {
                return [
                    "code" => 1003,
                    "message" => "User với id " . $user_id . " đã bị khóa hoặc không tồn tại"
                ];
            } else {
                $block = Block::where("blocker_id", $user_id)
                    ->where("user_id", $request->user()->id)->get();
                if (!$block->isEmpty()) {
                    if ($type == 1) {
                        $block[0]->delete();
                    }
                } else {
                    $block = new Block([
                        "blocker_id" => $user_id,
                        "user_id" => $request->user()->id
                    ]);
                    $block->save();
                }
                return [
                    "code" => ApiStatusCode::OK,
                    "message" => "OK"
                ];
            }
        }
    }

    public function testSaveFile(Request $request)
    {
        return Storage::url($this->fileService->saveFile($request->file("file")));
    }

    public function testDeleveFile(Request $request)
    {
        $this->fileService->deleteFile($request["link"]);
        return "OK";
    }
}
