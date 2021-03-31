<?php

namespace App\Http\Controllers;

use App\Enums\ApiStatusCode;
use App\Http\Controllers\Controller;
use App\User;
use JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function getToken(Request $request)
    {
        $phoneNumber = $request->phone_number;
        $password = $request->password;

        if ($phoneNumber == "" && $password == "") {
            return [
                "code" => ApiStatusCode::PARAMETER_NOT_ENOUGH,
                "message" => "Parameter is not enough"
            ];
        }

        if ($phoneNumber == $password || !str_starts_with($phoneNumber, "0")) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "Parameter type is invalid" 
            ];
        }

        $validator = Validator::make($request->all(), [
            'phone_number' => 'required|digits:10',
            'password' => 'required|string|max:10|min:6',
        ]);
        
        if ($validator->fails()) {
            return [
                "code" => 1003,
                "message" => "Parameter type is invalid",
                "data" => $validator->errors()
            ];
        }

        $user = User::where('phone_number', $phoneNumber)->first();
        if ($user == null) {
            return [
                "code" => 1004,
                "message" => "Parameter value is invalid"
            ];
        }

        $credentals = request([ 'phone_number', 'password']);

        $token = auth()->attempt($credentals);

        if ($this->checkPasswordCorrect($user, $password)) {
            $user->tokens()->delete();
            return [
                "code" => 1000,
                "message" => "OK",
                "data" => [
                    "id" => $user->id,
                    "username" => $user->name,
                    // "token" => $user->createToken(env('APP_KEY'))->plainTextToken,
                    "token" => $token,
                    "avatar" => $user->avatar
                ]
            ];
        } else {
            return [
                "code" => 1004,
                "message" => "Password is not correct"
            ];
        }
    }

    private function checkPasswordCorrect($user, string $password): bool
    {
        return ($user && Hash::check($password, $user->password));
    }

    public function register(Request $request)
    {
        $data = [];
        $data["phone_number"] = $request->phone_number;

        $data["password"] = $request->password;

        if ($data["phone_number"] == "" && $data["password"] == "") {
            return [
                "code" => ApiStatusCode::PARAMETER_NOT_ENOUGH,
                "message" => "Parameter is not enough"
            ];
        }
        if ($data["phone_number"] == $data["password"]) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "Parameter type is invalid"
            ];
        }
        if (!str_starts_with($data["phone_number"], "0")) {
            return [
                "code" => ApiStatusCode::PARAMETER_TYPE_INVALID,
                "message" => "Parameter type is invalid"
            ];
        }
        $data["name"] = $request->query("name");
        $data["email"] = $request->query("email");

        $validator = Validator::make($data, [
            'phone_number' => 'required|unique:users|digits:10',
            'password' => 'required|string|max:10|min:6',
        ]);
        if ($validator->fails()) {
            $phoneUniqueValidator = Validator::make($data, [
                'phone_number' => 'required|unique:users'
            ]);
            if ($phoneUniqueValidator->fails()) {
                return [
                    "code" => 9996,
                    "message" => "User existed"
                ];
            } else {
                return [
                    "code" => 1003,
                    "message" => "Parameter type is invalid",
                    "data" => $validator->errors()
                ];
            }
        }
        $user = User::makeUser([
            "phone_number" => $data["phone_number"],
            "password" => $data["password"],
            "name" => $data["name"],
            "email" => $data["email"]
        ]);
        $user->save(); 
        return [
            "code" => 1000,
            "message" => "OK"
        ];
    }

    public function changePassword(Request $request)
    {
        $user = $request->user();
        if ($user["is_blocked"]) {
            return [
                "code" => 9995,
                "message" => "User is not validated"
            ];
        }
        $validator = Validator::make($request->query(), [
            'password' => 'required|string|max:10|min:6',
            'new_password' => 'required|string|max:10|min:6'
        ]);
        if ($validator->fails()) {
            return [
                "code" => 1003,
                "message" => "Parameter type is invalid",
                "data" => $validator->errors()
            ];
        } else if (!$this->checkPasswordCorrect($user, $request->query("password"))) {
            return [
                "code" => 1003,
                "message" => "Old password is not correct"
            ];
        } else {
            $user->changePassword($request->query("new_password"));
            $user->save();
            return [
                "code" => 1000,
                "message" => "OK"
            ];
        }
    }

    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response([
            "code" => 1000,
            "message" => "OK"
        ]);
    }

    public function checkVerifyCode(Request $request)
    {
        $validator = Validator::make($request->query(), [
            'phonenumber' => 'required|digits:10',
            'code_verify' => 'required',
        ]);
        if ($validator->fails()) {
            if (Validator::make($request->query(), [
                'code_verify' => 'required',
            ])->fails()) {
                return [
                    "code" => 1002,
                    "message" => "Dont have Code Verify",
                ];
            } else {
                return [
                    "code" => 1003,
                    "message" => "Parameter type is invalid",
                    "data" => $validator->errors()
                ];
            }
        } else {
            $user = User::where("phone_number", $request->query("phonenumber"))->first();
            if ($user == null) {
                return [
                    "code" => 1004,
                    "message" => "User didn't exist",
                ];
            } else {
                $user->tokens()->delete();
                return [
                    "code" => 1000,
                    "message" => "OK",
                    "data" => [
                        "id" => $user->id,
                        "token" => $user->createToken(env('APP_KEY'))->plainTextToken,
                    ]
                ];
            }
        }
    }
}
