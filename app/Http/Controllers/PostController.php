<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Post;
use App\Image;
use App\Video;
use App\Comment;
use App\User;
use App\UserHidePost;
use JWTAuth;
use App\Block;
use App\Enums\ApiStatusCode;
use App\Enums\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class PostController extends Controller
{
    public function addPost(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'video' => 'max:30000|nullable',
            'image' => 'max:3072|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Dung lượng file quá lớn',
                'data' => $validator->errors()
            ]);
        } 

        if( $request->hasFile('image') && $request->hasFile('video')) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Chỉ được phép gửi ảnh hoặc video',
            ]);
        }

        if ($validator->fails()) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Video không đúng định dạng'
            ]);
        } 

        // kiểm tra xem có file ảnh không
        if ($request->hasFile('image')) {
            $allowedfileExtension = ['jpg', 'png'];
            $files = $request->file('image');

            // flag xem có thực hiện lưu DB không. Mặc định là có
            // $exe_flg = true;
            // kiểm tra tất cả các files xem có đuôi mở rộng đúng không
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if (!$check) {
                    // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
					// $exe_flg = false;
					return response()->json([
                        'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                        'message' => 'File ảnh không đúng định dạng',
                    ]);
				} 
			}
		}

        if ($request->hasFile('video')) {
            $allowedfileExtension = ['mp4'];
            $files = $request->file('video');

            // flag xem có thực hiện lưu DB không. Mặc định là có
            // $exe_flg = true;
            // kiểm tra tất cả các files xem có đuôi mở rộng đúng không
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if (!$check) {
                    // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                    // $exe_flg = false;
                    return response()->json([
                        'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                        'message' => 'File video không đúng định dạng',
                    ]);
                } 
            }
        }
    	
        $user = $request->user();
		$post = new Post([
            'user_id' => $user->id,
            'described' => $request['described'],
            'like' => 0,
            'comment' => 0,
            'is_hidden' => 0
        ]);

        if ($post->save()) {
			// nếu không có file nào vi phạm validate thì tiến hành lưu DB
            if($request->hasFile('image')) {
				$i = 1;
				foreach ($request->file('image') as $image) {

                    $imageName = $image->store('', 's3');
                    Storage::disk('s3')->setVisibility($imageName, 'public');
                    $imageUrl = Storage::disk('s3')->url('' . $imageName);
					
		        	$saveImage = new Image([
			        	'post_id' => $post->id,
                        'link' => $imageUrl,
			        	'image_sort' => $i
			        ]);
			        if ( $saveImage->save() ) {
			        	$i++;
			        } else {
			        	return response()->json([
			        		'code' => ApiStatusCode::LOST_CONNECT,
			    			'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
			    		]);
			        }
				}
            }

            if($request->hasFile('video')) {
                $video = $request->file('video');
                // $video->storeAs('video', $video->getClientOriginalName());
                $videoName = $video->store('', 's3');
                Storage::disk('s3')->setVisibility($videoName, 'public');
                $videoUrl  = Storage::disk('s3')->url('' . $videoName);
                
                $saveVideo = new Video([
                    'post_id' => $post->id,
                    'link' => $videoUrl,
                ]);
                if ( !$saveVideo->save() ) {
                    return response()->json([
                        'code' => ApiStatusCode::LOST_CONNECT,
                        'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
                    ]);
                }
            }

        	return response()->json(
        		[
        			'code' => ApiStatusCode::OK,
        			'message' => 'Tạo bài viết thành công',
        			'data' => [
        				'id' => $post->id,
        				'url' => URL::ADDRESS . '/posts/' . $post->id
        			]
        		]
        	);
        }
        else return response()->json(
        	[
        		'code' => ApiStatusCode::LOST_CONNECT,
    			'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
    		]
        );
    }

    public function editPost(Request $request) {

        $post_id = (int)$request->post_id;

        $validator = Validator::make($request->all(), [
            'video' => 'max:10000|nullable',
            'image' => 'max:1024|nullable'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Dung lượng file quá lớn',
                'data' => $validator->errors()
            ]);
        } 

        if( $request->hasFile('image') && $request->hasFile('video')) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Chỉ được phép gửi ảnh hoặc video',
            ]);
        }

        $validator = Validator::make($request->all(), [
            'video' => 'mimes:mp4|nullable',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                'message' => 'Video không đúng định dạng'
            ]);
        } 

        // kiểm tra xem có file ảnh không
        if ($request->hasFile('image')) {
            $allowedfileExtension = ['jpg', 'png'];
            $files = $request->file('image');

            // flag xem có thực hiện lưu DB không. Mặc định là có
            // $exe_flg = true;
            // kiểm tra tất cả các files xem có đuôi mở rộng đúng không
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if (!$check) {
                    // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                    // $exe_flg = false;
                    return response()->json([
                        'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                        'message' => 'File ảnh không đúng định dạng',
                    ]);
                } 
            }
        }

        if ($request->hasFile('video')) {
            $allowedfileExtension = ['mp4'];
            $files = $request->file('video');

            // flag xem có thực hiện lưu DB không. Mặc định là có
            // $exe_flg = true;
            // kiểm tra tất cả các files xem có đuôi mở rộng đúng không
            foreach ($files as $file) {
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension, $allowedfileExtension);

                if (!$check) {
                    // nếu có file nào không đúng đuôi mở rộng thì đổi flag thành false
                    // $exe_flg = false;
                    return response()->json([
                        'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
                        'message' => 'File video không đúng định dạng',
                    ]);
                } 
            }
        }
        
        $user = $request->user();

        $post = Post::where('id', $post_id)->first();

        //==== Kết thúc nếu bài viết không tồn tại ====//
        if(empty($post)) {
            return response()->json([
                'code' => ApiStatusCode::NOT_EXISTED,
                'message' => 'Bài viết không tồn tại'
            ]);
        }
        //==== end ====//

        $post['described'] = $request['described'];
        
        if ($post->save()) {
            // nếu không có file nào vi phạm validate thì tiến hành lưu DB
            $saveImage = Image::where('post_id', $post_id)->get();
            // foreach($saveImage as $item) {
            //     $item['post_id'] = '';
            //     $item->save();
            // }

            if($request->hasFile('image')) {
                $i = 1;
                foreach ($request->file('image') as $image) {
                    $imageName = $image->store('', 's3');
                    Storage::disk('s3')->setVisibility($imageName, 'public');
                    $imageUrl = Storage::disk('s3')->url('' . $imageName);
                    
                    $saveImage = new Image([
                        'post_id' => $post_id,
                        // 'link' => $image->getClientOriginalName(),
                        'link' => $imageUrl,
                        'image_sort' => $i
                    ]);
                    if ( $saveImage->save() ) {
                        $i++;
                    } else {
                        return response()->json([
                            'code' => ApiStatusCode::LOST_CONNECT,
                            'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
                        ]);
                    }
                }
            }

            if($request->hasFile('video')) {
                $prevVideo = Video::where('post_id', $post_id)->get();
                foreach($item as $prevVideo) {
                    $item['post_id'] = '';
                    $item->save();
                }
                $video = $request->file('video');
                $videoName = $video->store('', 's3');
                Storage::disk('s3')->setVisibility($videoName, 'public');
                $videoUrl  = Storage::disk('s3')->url('' . $videoName);
                
                $saveVideo = new Video([
                    'post_id' => $post->id,
                    'link' => $videoUrl,
                ]);
                if ( !$saveVideo->save() ) {
                    return response()->json([
                        'code' => ApiStatusCode::LOST_CONNECT,
                        'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
                    ]);
                }
            }

            return response()->json(
                [
                    'code' => ApiStatusCode::OK,
                    'message' => 'Chỉnh sửa bài viết thành công',
                ]
            );
        }
        else return response()->json(
            [
                'code' => ApiStatusCode::LOST_CONNECT,
                'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
            ]
        );
    }

    public function getPost(Request $request) {

        $post_id = $request->id;
    	$post = Post::where('id', $post_id)->first();

        if (!$post) {
            return response()->json([
                'code' => ApiStatusCode::NOT_EXISTED, 
                'message' => 'Bài viết không tồn tại',
                'post_id' => $post_id
            ]);
        }
        $images = $post->images;
        $videos = $post->videos;
    	$user = User::where('id', $post->user_id)->first();

    	return response()->json([
    		'code' => ApiStatusCode::OK, 
    		'message' => 'Lấy bài viết thành công',
    		'data' => [
    			'id' => $post->id,
    			'described' => $post->described,
    			'created' => $post->created_at,
    			'modified' => $post->updated_at,
    			'like' => $post->like,
                'comment' => $post->comment,
    		],
    		'image' => $images,
            'video' => $videos,
    		'author' => $user
    	]);
    }

    public function deletePost(Request $request) {

        $post_id = $request->id;
    	$post = Post::where('id', $post_id)->first();

        if (!$post) {
            return response()->json([
                'code' => ApiStatusCode::NOT_EXISTED, 
                'message' => 'Bài viết không tồn tại',
            ]);
        }
        if ($post->delete()) {
            return response()->json([
                'code' => ApiStatusCode::OK,
                'message' => 'Xóa bài viết thành công'
            ]);
        }
    }

    public function hidePost(Request $request) {

        $post_id = $request->id;
    	$post = Post::where('id', $post_id)->first();

        if (!$post) {
            return response()->json([
                'code' => ApiStatusCode::NOT_EXISTED, 
                'message' => 'Bài viết không tồn tại',
            ]);
        }
        $post["is_hidden"] = 1; // bài bị ẩn
        if ($post->delete()) {
            return response()->json([
                'code' => ApiStatusCode::OK,
                'message' => 'Ẩn bài viết thành công'
            ]);
        }
    }

    public function getListPost(Request $request) {
        $user = $request->user();
        $user_id = $request->user_id;
        $in_campaign = $request->in_campaign;
        $campaign_id = $request->campaign_id;
        $last_id = $request->last_id;
        $index = (int)$request->index;
        $count = (int)$request->count;
        $list_posts = null;
        
        if($user_id !== null) {
            $list_posts = Post::with('images', 'author', 'likes')
                        ->with('videos')
                        ->where('user_id', $user_id)
                        ->skip($index)
                        ->orderBy('updated_at', 'desc')
                        ->limit($count)
                        ->get();
            foreach($list_posts as $key => $postItem) {
                $postItem["isLiked"] = false;
                $like_post = $postItem["likes"];
                foreach ($like_post as $userLike) {
                    if($userLike["user_id"] == $user->id) $postItem["isLiked"] = true;
                    break;
                }
            }
        } else {
            $list_posts = Post::with('images', 'author', 'likes')
                        ->with('videos')
                        // ->where('id', '>', $index)
                        ->skip($index)
                        ->orderBy('updated_at', 'desc')
                        ->limit($count)
                        ->get();

            $list_hide_posts = UserHidePost::where("user_id", $user->id)->get();
            $list_block      = Block::where("blocker_id", $user->id)->get();
            foreach($list_posts as $key => $postItem) {
                $postItem["isLiked"] = false;
                $postItem["isHide"] = false;
                $postItem["isBlock"] = false;
                $like_post = $postItem["likes"];
                foreach ($like_post as $userLike) {
                    if($userLike["user_id"] == $user->id) {
                        $postItem["isLiked"] = true;
                        break;
                    }
                }
                foreach ($list_hide_posts as $userHide) {
                    if($userHide["user_id"] == $user->id && $postItem["id"] == $userHide["post_id"]) {
                        $postItem["isHide"] = true;
                        break;
                    }
                }
                foreach($list_block as $block) {
                    if($postItem["author"]["id"] == $block["user_id"]) {
                        $postItem["isBlock"] = true;
                    }
                }
            }
        }
        if ($list_posts != null && $list_posts->last() != null) 
            $new_last_id = $list_posts->last()->id;
        else $new_last_id = $last_id === null ? 0 : $last_id;

        return response()->json([
            'code' => ApiStatusCode::OK,
            'message' => 'Lấy danh sách bài viết thành công',
            'data' => [
                'posts' => $list_posts,
            ],
            'last_id' => $new_last_id,
        ]);
    }

    public function checkNewItem(Request $request) {
        $last_id = $request->last_id;
        $category_id = $request->category_id;

        $list_posts = Post::where('id', '>', $last_id)
                        ->orderBy('id', 'desc')
                        ->get();
        $new_last_id = $list_posts->first()->id;


        if($list_posts) {           
            return response()->json([
                'code' => ApiStatusCode::OK,
                'message' => 'Lấy danh sách bài viết mới thành công',
                'data' => [
                    'news_items' => $list_posts->count(),
                ],
                'last_id' => $new_last_id,
            ]);   
        } 
    }
    // public function addComment(Request $request,$id) {

    // 	$validator = Validator::make($request->all(), [
    //         'described' => 'required'
    //     ]);

    // 	if ($validator->fails()) {
    // 		return response()->json([
    // 			'code' => ApiStatusCode::PARAMETER_NOT_ENOUGH,
    // 			'message' => 'Số lượng parameter không dầy đủ',
    // 			'data' => $validator->errors()
    // 		]);
    // 	}
    // 	else {
    // 		$validator = Validator::make($request->all(), [
    //             'described' => 'string',

    //         ]);

    //         if ($validator->fails()) {
    //     		return response()->json([
    //     			'code' => ApiStatusCode::PARAMETER_TYPE_INVALID,
    //     			'message' => 'Kiểu tham số không đúng đắn',
    //     			'data' => $validator->errors()
    //     		]);
    // 		}
    // 		else {
    // 			$post = Post::find($id);
    // 			if ($post == null) {
    // 				return [
    // 					"code" => 9992,
    // 					"message" => "Bài viết không tồn tại"
    // 				];
    // 		}
    // 	}
    // 	}
    // 	$post = Post::find($id);
    // 	$comment = new Comment([
    //         'user_id'=>$post->user_id,
    //         'post_id'=>$id,
    //         'content' => $request['described'],

    //     ]);

    //     if ($comment->save()) {

    //     	return response()->json(
    //     		[
    //     			'code' => ApiStatusCode::OK,
    //     			'message' => 'Tạo comment thành công',
    //     			'data' => [
    // 					'user_id'=>$post->user_id,
    //     				'post_id' => $id,
    //     				'content'=>$request['described']
    //     			]
    //     		]
    //     	);
    //     }
    //     else return response()->json(
    //     	[
    //     		'code' => ApiStatusCode::LOST_CONNECT,
    // 			'message' => 'Lỗi mất kết nối DB/ hoặc lỗi thực thi câu lệnh DB'
    // 		]
    //     );
    // }
}
