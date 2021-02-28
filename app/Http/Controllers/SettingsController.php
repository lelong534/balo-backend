<?php

namespace App\Http\Controllers;

use App\Enums\ApiStatusCode;
use App\Settings;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function setPushSetting(Request $request)
    {
        $settings = [
            "like_comment" => null,
            "from_friends" => null,
            "requested_friend" => null,
            "suggested_friend" => null,
            "birthday" => null,
            "video" => null,
            "report" => null,
            "sound_on" => null,
            "notification_on" => null,
            "vibrant_on" => null,
            "led_on" => null
        ];
        foreach ($settings as $setting => $value) {
            $queryValue = $request->query($setting);
            if ($queryValue == '' || ((int)$queryValue != 0 && (int)$queryValue != 1)) {
                return [
                    "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                    "message" => "PARAMETER TYPE INVALID: " . $setting
                ];
            } else {
                $settings[$setting] = $queryValue;
            }
        }
        $request->user()->setting()->delete();
        $settings["user_id"] = $request->user()->id;
        Settings::create($settings);
        return [
            "code" => ApiStatusCode::OK,
            "message" => "OK"
        ];
    }

    public function getPushSetting(Request $request)
    {
        if ($request->user()->setting == null) {
            $newUserSetting = new Settings();
            $newUserSetting->user_id = $request->user()->id;
            $newUserSetting->save();
        }
        $userSetting = Settings::find($newUserSetting->id);
        unset($userSetting["user_id"]);
        unset($userSetting["id"]);
        unset($userSetting["created_at"]);
        unset($userSetting["updated_at"]);
        return [
            "code" => ApiStatusCode::OK,
            "message" => "OK",
            "data" => $userSetting
        ];
    }
}
