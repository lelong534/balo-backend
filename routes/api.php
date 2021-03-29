<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['verify-token', 'auth:api', 'user-blocked'])->group(function () {
    Route::post('/user', function (Request $request) {
        return auth()->user();
    });
    Route::post('/user/settings', "SettingsController@setPushSetting")->name("set_push_settings");
    Route::get('/user/notifications', "UserController@getNotifications")->name("get_notification");
    Route::post('/user/notifications', "UserController@setReadNotification")->name("set_read_notification");
    Route::get('/user/settings', "SettingsController@getPushSetting")->name("get_push_settings");
    Route::get('/user/get_requested_friends', "UserController@getRequestedFriends")->name("get_requested_friends");
    Route::post('/user/set_request_friend', "UserController@setRequestFriends")->name("set_request_friend");
    Route::get('/user/friends', "UserController@getFriends")->name("get_user_friends");
    Route::get('/user/get_list_suggested_friends', "UserController@getSuggestedFriends")->name("get_list_suggested_friends");
    Route::post('/user/friends', "UserController@setFriends")->name("set_accept_friend");
    Route::post("/logout", 'AuthController@logout')->name("Logout");
    Route::post("/change_password", "AuthController@changePassword")->name("change_password");
    Route::post("/device", "DeviceController@setDeviceInfo")->name("set_devtoken");
    Route::get("user/block", "UserController@getBlock")->name("get_list_blocks");
    Route::get("/user", "UserController@getInfo")->name("get_user_info");
    Route::post('change_info_after_signup', 'UserController@changeInfoAfterSignup')->name("change_info_after_signup");

    Route::post('add_post', 'PostController@addPost')->name("add_post");
    Route::post('edit_post/{id}', 'PostController@editPost')->name("edit_post");
    Route::post('get_post', 'PostController@getPost')->name("get_post");
    Route::post('delete_post', 'PostController@deletePost')->name("delete_post");
    Route::get('get_list_posts', 'PostController@getListPost')->name("get_list_posts");
    Route::get('check_new_item', 'PostController@checkNewItem')->name("check_new_item");

    Route::post("/set_user_info", "UserController@setUserInfo")->name("set_user_info");

    Route::get('messages/get_list_conversation', 'ChatController@getListConversation')->name("get_list_conversation");
    Route::get('messages/conversation', 'ChatController@getConversation')->name("get_conversation");
    Route::post('messages/set_read_message', 'ChatController@setReadMessage')->name("set_read_message");
    Route::post('messages/delete_message', 'ChatController@deleteMessage')->name("delete_message");
    Route::post('messages/delete_conversation', 'ChatController@deleteConversation')->name("delete_conversation");
    Route::get('message/{userId2}', 'ChatController@fetchAllMessages');
    Route::post('message/{userId2}', 'ChatController@sendMessage');
    Route::post('report/{id}', 'UserReportPostController@reportPost')->name("report_post");
    Route::post('like/add/{post_id}', 'UserLikePostController@likePost')->name("like_post");
    Route::get('comment/{id}', 'CommentController@getComment')->name("get_comment");
    Route::post('comment/add/{id}', 'CommentController@addComment')->name("set_comment");
    Route::post("user/block/{user_id}", "UserController@setBlock")->name("set_block");

    Route::post('search', 'SearchController@search')->name("search");
    Route::post('get_saved_search', 'SearchController@getSavedSearch')->name("get_saved_search");
    Route::post('del_saved_search', 'SearchController@delSavedSearch')->name("del_saved_search");

    Route::post('get_list_videos', 'VideoController@getListVideos')->name("get_list_videos");
});

Route::post('login', 'AuthController@getToken')->name("Login");
Route::post('register', 'AuthController@register')->name("Signup");
Route::post('check_verify_code', 'AuthController@checkVerifyCode')->name("check_verify_code");

Route::post('testSaveFile', 'UserController@testSaveFile');
Route::post('testDeleteFile', 'UserController@testDeleveFile');
Route::get('test', 'PostController@test');