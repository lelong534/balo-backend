<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DeviceController extends Controller
{
    public function setDeviceInfo(Request $request)
    {
        if ($request->user()["is_blocked"]) {
            return [
                "code" => 9995,
                "message" => "User is not validated"
            ];
        }
        $validator = Validator::make($request->query(), [
            'device_token' => 'required|uuid',
            'device_type' => 'required'
        ]);
        if ($validator->fails()) {
            return [
                "code" => 1003,
                "message" => "Parameter type is invalid",
                "data" => $validator->errors()
            ];
        } else {
            return [
                "code" => 1000,
                "message" => "OK"
            ];
        }
    }
}
