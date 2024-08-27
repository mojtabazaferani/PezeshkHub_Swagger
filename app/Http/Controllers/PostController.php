<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\PostLike;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    
     /**
     * @OA\Tag(
     *     name="Posts",
     *     description="Endpoints related to posts"
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/Post/create",
     *     tags={"Posts"},
     *     summary="Create a new post",
     *     description="Create a new post with media uploads",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="description", type="string", description="Description of the post", example="This is a sample post"),
     *                 @OA\Property(property="media_upload", type="array", @OA\Items(type="string", format="binary"))
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Post created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="array", @OA\Items(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="registered", type="boolean", example=true)
     *             ))
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Miss media",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="miss media")
     *         )
     *     )
     * )
     */

    

    public function postCreate(Request $request)
    {

        $user = request()->user();

        $check = User::where('mobile_number', $user->mobile_number)
        ->where('status_register', 'complete')
        ->first();

        if($check == null) {

            $msg = [
                'status' => false,
                'message' => 'You are not registered'
            ];

            return response()->json($msg, 422);

        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|max:200',
            'media' => 'required|mimes:pdf,jpg,jpeg'
        ]);

        if($validator->fails()) {

            $msg = [
                'status' => false,
                'errors' => $validator->errors()
            ];

            return response()->json($msg, 422);

        }

        $images = [];

        $medias = $request->file('media');

        if(isset($medias)) {
    
                $nameImage = $medias->getClientOriginalName();

                $medias->move('images',$nameImage);

        }

        $description = $request->description;

        $create = Post::create([
            'username' => $user->username,
            'description' => $description,
            'media' => $nameImage
        ]);

        if($create) {

            $msg = [
                'success' => true,
                'data' => [
                    'success' => true,
                    'registered' => true
                ]
            ];

            return response()->json($msg);

        }else {

            $msg = [
                'success' => false
            ];

            return response()->json($msg, 422);

        }
    }

    public function post($post_id)
    {

        $user = request()->user();

        $check = User::where('mobile_number', $user->mobile_number)
        ->where('status_register', 'complete')
        ->first();

        if($check == null) {

            $msg = [
                'status' => false,
                'message' => 'You are not registered'
            ];

            return response()->json($msg, 422);

        }

        $post = DB::table('posts')
        ->where('username', $user->username)
        ->where('id', $post_id)
        ->get();

        $post = json_decode($post, true);

        $result = [];

        for( $i = 0; $i < count($post); $i++ ) {

            $result[$i]['id'] = $post[$i]['id'];

            $result[$i]['username'] = $post[$i]['username'];

            $result[$i]['description'] = $post[$i]['description'];

            $result[$i]['media'] = $_SERVER['HTTP_HOST'] . '/images/' . $post[$i]['media'];

            $result[$i]['like'] = $post[$i]['like'];

        }

        $msg = [
            'success' => true,
            'post' => $result
        ];

        return response()->json($msg);

    }

    public function postEdit(Request $request, $post_id)
    {

        $validator = Validator::make($request->all(), [
            'description' => 'required|max:200',
            'media' => 'required|mimes:pdf,jpg,jpeg'
        ]);

        if($validator->fails()) {

            $msg = [
                'status' => false,
                'errors' => $validator->errors()
            ];

            return response()->json($msg, 422);

        }
        
        $post = Post::findOrFail($post_id);

        $oldDescription = $post->description;

        $oldMedia = $post->media;

        $newDescription = $request->description;

        $newMedia = $request->file('media');

        if(isset($newDescription)) {

            $oldDescription = $newDescription;

        }

        if(isset($newMedia)) {

            $oldMedia = $newMedia->getClientOriginalName();

            $newMedia->move('images', $oldMedia);

        }

        $post->description = $oldDescription;

        $post->media = $oldMedia;

        $post->save();

        $msg = [
            'success' => true,
            'message' => 'Your post has been successfully edited'
        ];

        return response()->json($msg);

    }

    public function postDelete($post_id)
    {

        $user = request()->user();

        $delete = DB::table('posts')
        ->where('id', $post_id)
        ->where('username', $user->username)
        ->delete();

        if($delete) {

            $msg = [
                'success' => true,
                'message' => 'Your post has been successfully deleted'
            ];

            return response()->json($msg);

        }
    }

    
   /**
     * @OA\Post(
     *     path="/api/Post/like",
     *     tags={"Posts"},
     *     summary="Like a post",
     *     description="Like a post",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="post_id", type="integer", description="Post ID for like", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="liked", type="boolean", example=true),
     *             @OA\Property(property="like_count", type="integer", example=0)
     *         )
     *     )
     * )
     */
    public function postLike($post_id)
    {

        $user = request()->user();

        $checkPots = Post::where('id', $post_id)
        ->first();

        if($checkPots == null) {

            $msg = [
                'success' => false,
                'error' => 'There is no such post' 
            ];

            return response()->json($msg);

        }

        $check = User::where('mobile_number', $user->mobile_number)
        ->where('status_register', 'complete')
        ->first();

        if($check == null) {

            $msg = [
                'status' => false,
                'message' => 'You are not registered'
            ];

            return response()->json($msg, 422);

        }

        $checkLike = PostLike::where('username', $user->username)
        ->where('post_id', $post_id)
        ->first();

        if($checkLike != null) {

            $msg = [
                'success' => false,
                'message' => 'You have already liked this post'
            ];

            return response()->json($msg, 400);

        }

        $like = PostLike::create([
            'post_id' => $post_id,
            'username' => $user->username,
            'user_id' => $user->id
        ]);

        if($like) {

            $post = Post::findOrFail($post_id);

            $post->like = $post->like + 1;

            $post->save();

            $msg = [
                'success' => true,
                'liked' => true
            ];

            return response()->json($msg);

        }else {

            $msg = [
                'success' => false
            ];

            return response()->json($msg, 422);

        }

    }
}
