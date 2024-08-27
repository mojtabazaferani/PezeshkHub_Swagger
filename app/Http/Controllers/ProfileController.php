<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    /**
     * @OA\Tag(
     *     name="Profile",
     *     description="Endpoints related to posts"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/Profile/show",
     *     tags={"Profile"},
     *     summary="profile api get profile detail",
     *     description="profile api get profile detail",
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="user_id for get profile detail",
     *         required=true,
     *         @OA\Schema(
     *             type="integer",
     *             format="int64"
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="id", type="integer", example=0),
     *                 @OA\Property(property="workarea_education_id", type="integer", example=0),
     *                 @OA\Property(property="username", type="string", example="string"),
     *                 @OA\Property(property="fullname", type="string", example="string"),
     *                 @OA\Property(property="fullname_fa", type="string", example="string"),
     *                 @OA\Property(property="firstname", type="string", example="string"),
     *                 @OA\Property(property="firstname_fa", type="string", example="string"),
     *                 @OA\Property(property="lastname", type="string", example="string"),
     *                 @OA\Property(property="lastname_fa", type="string", example="string"),
     *                 @OA\Property(property="about", type="string", example="string"),
     *                 @OA\Property(property="avatar_url", type="string", example="string"),
     *                 @OA\Property(property="medical_id", type="integer", example=0),
     *                 @OA\Property(property="national_id", type="integer", example=0),
     *                 @OA\Property(property="mobile_number", type="string", example="string"),
     *                 @OA\Property(property="country_code", type="string", example="string"),
     *                 @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2024-06-27T20:29:58.770Z"),
     *                 @OA\Property(property="is_mobile_verify", type="integer", example=0),
     *                 @OA\Property(property="is_user_verify", type="integer", example=0),
     *                 @OA\Property(property="is_staff", type="integer", example=0),
     *                 @OA\Property(property="email", type="string", example="string"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-06-27T20:29:58.770Z"),
     *                 @OA\Property(property="fcm_token", type="string", example="string"),
     *                 @OA\Property(property="ws_token", type="string", example="string"),
     *                 @OA\Property(property="birthday", type="string", example="string"),
     *                 @OA\Property(property="city", type="string", example="string"),
     *                 @OA\Property(property="country", type="string", example="string"),
     *                 @OA\Property(property="gender", type="string", example="string"),
     *                 @OA\Property(property="active", type="integer", example=0),
     *                 @OA\Property(property="timezone", type="string", example="string"),
     *                 @OA\Property(property="language", type="string", example="string")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Tag not found"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function profileShow()
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

        $msg = [
            'success' => true,
            'data' => json_decode($user, true)
        ];

        return response()->json($msg);

    }
}
