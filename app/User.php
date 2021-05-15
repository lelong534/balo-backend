<?php

namespace App;

use App\Enums\FriendStatus;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use Notifiable, HasApiTokens;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'phonenumber'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function getJWTIdentifier() 
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims() {
        return [];
    }

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function isBlocked()
    {
        return $this["is_blocked"];
    }

    public function getBlockSelfId()
    {
        return Block::select("blocker_id")->where("user_id", $this->id)->get()->toArray();
    }

    public function getBlockId()
    {
        return Block::select("user_id")->where("blocker_id", $this->id)->get()->toArray();
    }

    public function isBlockSelf(int $userId)
    {
        return in_array($userId, $this->getBlockSelfId());
    }



    public function getFriends()
    {
        $friendsInfo = Friends::where([
            ["user_id", $this->id],
            ["status", FriendStatus::ACCEPTED]
        ])->orWhere([
            ["friend_id", $this->id],
            ["status", FriendStatus::ACCEPTED]
        ])->get();
        $friends = [];
        foreach ($friendsInfo as $friendInfo) {
            if ($friendInfo["user_id"] == $this->id) {
                $user = User::find($friendInfo["friend_id"]);
                if ($user != null)
                    array_push($friends, $user);
            } else if ($friendInfo["friend_id"] == $this->id) {
                $user = User::find($friendInfo["user_id"]);
                if ($user != null)
                    array_push($friends, $user);
            }
        }
        return $friends;
    }

    public function getFriendRequest()
    {
        $friendsInfo = Friends::where("user_id", $this->id)
            ->where('status', FriendStatus::REQUESTED)->get();
        $friends = [];
        foreach ($friendsInfo as $friendInfo) {
            $user = User::find($friendInfo->friend_id);
            if ($user != null)
                array_push($friends, $user);
        }
        return $friends;
    }

    public function getSameFriends($user_id)
    {
        $myFriends = $this->getFriends();
        $otherUser = User::find($user_id);
        if ($otherUser == null) return 0;
        else {
            $hisFriends = $otherUser->getFriends();
            $count = 0;
            foreach ($myFriends as $myFriend) {
                foreach ($hisFriends as $hisFriend) {
                    if ($myFriend->id == $hisFriend->id) $count += 1;
                }
            }
            return $count;
        }
    }

    public function messagesSend()
    {
        return $this->hasMany(Chat::class, 'user_a_id');
    }

    public function messagesReceive()
    {
        return $this->hasMany(Chat::class, 'user_b_id');
    }

    public function setting()
    {
        return $this->hasOne(Settings::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function devices()
    {
        return $this->hasMany(Device::class);
    }

    public function changePassword(string $password)
    {
        $this->password = Hash::make($password);
    }

    // -------------------------------- static --------------------------------
    public static function makeUser(array $data): User
    {
        $user = new User();
        $user->email = $data['email'];
        $user->name = $data["name"];
        $user->phone_number = $data["phone_number"];
        $user->password = Hash::make($data['password']);
        return $user;
    }
}
