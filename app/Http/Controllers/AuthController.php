<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\VerifyCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

/**
 * 
 * @OA\Info(
 *     title="PezeshkHub super ApplicationAPI",
 *     version="1.0.0",
 * )
 */

class AuthController extends Controller
{

    /**
     * @OA\Tag(
     *     name="Auth",
     *     description="Endpoints related to user authentication"
     * )
     */

    /**
     * Check user mobile_number on database for check availability for login or register.
     *
    * @OA\Post(
     *     path="/api/auth/check-mobile",
     *     summary="Check mobile number and send verification code",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="country_code",
     *                     type="string",
     *                     description="User country code without zero. Example: 98",
     *                     example="98"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_number",
     *                     type="string",
     *                     description="User mobile number with standard format without country code and zero. Example: 9356350256",
     *                     example="9356350256"
     *                 ),
     *                 required={"country_code", "mobile_number"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Code sent successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code Sent"),
     *             @OA\Property(property="code", type="integer", example=1234)
     *         )
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="errors", type="object", example={"mobile_number": {"The mobile number field is required."}})
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */

    public function authCheckMobile(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|iran_mobile'
        ]);

        if($validator->fails()) {

            $msg = [
                'status' => false,
                'errors' => $validator->errors()
            ];

            return response()->json($msg);

        }

        $mobile_number = $request->mobile_number;

        $checkingUser = User::where('mobile_number', $mobile_number)
        ->first();

        if($checkingUser == null) {

            $checkMobileInVerifyTable = VerifyCode::where('mobile_number', $mobile_number)
            ->first();

            if($checkMobileInVerifyTable != null) {

                $id = VerifyCode::findOrFail($checkMobileInVerifyTable->id);
    
                $expireTime = $id->expire_time;

                if($expireTime > time()) {

                    $msg = [
                        'status' => false,
                        'message' => 'The security code has not yet expired!!'
                    ];

                    return response()->json($msg);
                    
                }

                $now = time();

                $expireTime = $now + 120;

                $code = rand(1000, 9999);

                $id->mobile_number = $mobile_number;

                $id->creating_time = $now;

                $id->expire_time = $expireTime;

                $id->code = $code;

                $id->save();

                $msg = [
                    'status' => true,
                    'message' => 'Code Sent'
                ];

                return response()->json($msg);

            }

            $now = time();
    
            $expireTime = $now + 120;

            $code = rand(1000, 9999);

            $register = VerifyCode::create([
                'mobile_number' => $mobile_number,
                'creating_time' => $now,
                'expire_time' => $expireTime,
                'code' => $code
            ]);

            if ($register) {

                $msg = [
                    'status' => true,
                    'message' => 'Code Sent',
                    'code' => $code
                ];

                return response()->json($msg);
            }
        }else {

            $checkMobileInVerifyTable = VerifyCode::where('mobile_number', $mobile_number)
            ->first();

            if($checkMobileInVerifyTable != null) {

                $id = VerifyCode::findOrFail($checkMobileInVerifyTable->id);

                $expireTime = $id->expire_time;

                if($expireTime > time()) {

                    $msg = [
                        'status' => false,
                        'message' => 'The security code has not yet expired!!'
                    ];

                    return response()->json($msg);
                    
                }

                $now = time();

                $expireTime = $now + 120;

                $code = rand(1000, 9999);

                $id->mobile_number = $mobile_number;

                $id->creating_time = $now;

                $id->expire_time = $expireTime;

                $id->code = $code;

                $id->save();

                $msg = [
                    'status' => true,
                    'message' => 'A new login code has been sent',
                    'code' => $code
                ];

                return response()->json($msg);

            }

            $now = time();

            $expireTime = $now + 120;

            $code = rand(1000, 9999);

            $register = VerifyCode::create([
                'mobile_number' => $mobile_number,
                'creating_time' => $now,
                'expire_time' => $expireTime,
                'code' => $code
            ]);

            if ($register) {

                $msg = [
                    'status' => true,
                    'message' => 'The login code has been sent',
                    'code' => $code
                ];

                return response()->json($msg);

            }
        }
    }

    /**
     * after check mobile_number, if user exist send OTP code
     *
     * @OA\Post(
     *     path="/api/auth/login-otp",
     *     summary="Login user with otp code",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="country_code",
     *                     type="string",
     *                     description="User country code without zero. Example: 98",
     *                     example="98"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_number",
     *                     type="string",
     *                     description="User mobile number with standard format without country code and zero. Example: 9356350256",
     *                     example="9356350256"
     *                 ),
     *                 @OA\Property(
     *                     property="otp",
     *                     type="string",
     *                     description="OTP code received from SMS or call",
     *                     example="1234"
     *                 ),
     *                 required={"country_code", "mobile_number", "otp"}
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="some-jwt-token"),
     *             @OA\Property(
     *                 property="user",
     *                 type="object",
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
     *                 @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2024-06-27T08:06:47.051Z"),
     *                 @OA\Property(property="is_mobile_verify", type="integer", example=0),
     *                 @OA\Property(property="is_user_verify", type="integer", example=0),
     *                 @OA\Property(property="is_staff", type="integer", example=0),
     *                 @OA\Property(property="email", type="string", example="string"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-06-27T08:06:47.051Z"),
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
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Unauthorized")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal Server Error")
     *         )
     *     )
     * )
     */
    public function authLoginOtp(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|iran_mobile'
        ]);

        if($validator->fails()) {

            $msg = [
                'status' => false,
                'errors' => $validator->errors()
            ];

            return response()->json($msg);

        }

        $code = $request->code;

        $mobile_number = $request->mobile_number;

        $checkUser = User::where('mobile_number', $mobile_number)
        ->first();

        if($checkUser != null) {

            $verifyMobile = VerifyCode::where('code', $code)
            ->where('mobile_number', $mobile_number)
            ->where('expire_time', '>', time())
            ->first();

            if($verifyMobile != null) {

                DB::table('personal_access_tokens')
                ->where('tokenable_id', '=', $checkUser->id)
                ->where('name', '=', 'login')
                ->delete();

                $token = $checkUser->createToken('login')->plainTextToken;

                $check = json_decode($checkUser, true);

                $msg = [
                    'status' => true,
                    'message' => 'Login Successful',
                    'token' => $token,
                    'information' => $check
                ];

                return response()->json($msg);

                }else {

                    $msg = [
                        'status' => false,
                        'message' => 'The security code has expired or is not correct'
                    ];
        
                    return response()->json($msg);

                }
            }
        

        $verifyMobile = VerifyCode::where('code', $code)
            ->where('mobile_number', $mobile_number)
            ->where('expire_time', '>', time())
            ->first();

        if ($verifyMobile != null) {

            $idAcceptMobileNumber = VerifyCode::findOrFail($verifyMobile->id);

            $token = $idAcceptMobileNumber->createToken('verify')->plainTextToken;

            $acceptMobileNumber = User::create(
                [
                    'username' => '',
                    'full_name' => '',
                    'first_name' => '',
                    'last_name' => '',
                    'gender' => '',
                    'mobile_number' => $verifyMobile->mobile_number,
                    'email' => '',
                    'password' => '',
                    'active' => 'yes' 
                ]
            );

            if ($acceptMobileNumber) {

                $msg = [
                    'status' => true,
                    'message' => 'Success',
                    'token' => $token
                ];

                return response()->json($msg);
            }
        } else {
 
            $msg = [
                'status' => false,
                'message' => 'The security code has expired or is not correct'
            ];

            return response()->json($msg);

        }
    }


    /**
     * after 60 seconds user request new otp code as sms or call. type= sms/call string
     *
     * @OA\Post(
     *     path="/api/auth/resend-otp",
     *     tags={"Auth"},
     *     summary="Resend OTP code",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="country_code",
     *                     type="string",
     *                     description="User country code without zero. Example: 98",
     *                     example="98"
     *                 ),
     *                 @OA\Property(
     *                     property="mobile_number",
     *                     type="string",
     *                     description="User mobile number with standard format without country code and zero. Example: 9356350256",
     *                     example="9356350256"
     *                 ),
     *                 @OA\Property(
     *                     property="type",
     *                     type="string",
     *                     description="Resend OTP can use both SMS & call, type can only be set to 'sms' or 'call'. Example: call",
     *                     example="call"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Code Sent")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    public function authResendOtp(Request $request)
    {

        $mobile_number = $request->mobile_number;

        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required|iran_mobile'
        ]);

        if($validator->fails()) {

            $msg = [
                'status' => false,
                'errors' => $validator->errors()
            ];

            return response()->json($msg);

        }

        $mobile_number = $request->mobile_number;

        $checkingUser = User::where('mobile_number', $mobile_number)
        ->first();

        if($checkingUser == null) {

            $checkMobileInVerifyTable = VerifyCode::where('mobile_number', $mobile_number)
            ->first();

            if($checkMobileInVerifyTable != null) {

                $id = VerifyCode::findOrFail($checkMobileInVerifyTable->id);
    
                $expireTime = $id->expire_time;

                if($expireTime > time()) {

                    $msg = [
                        'status' => false,
                        'message' => 'The security code has not yet expired!!'
                    ];

                    return response()->json($msg);
                    
                }

                $now = time();

                $expireTime = $now + 120;

                $code = rand(1000, 9999);

                $id->mobile_number = $mobile_number;

                $id->creating_time = $now;

                $id->expire_time = $expireTime;

                $id->code = $code;

                $id->save();

                $msg = [
                    'status' => true,
                    'message' => 'Code Sent'
                ];

                return response()->json($msg);

            }

            $now = time();
    
            $expireTime = $now + 120;

            $code = rand(1000, 9999);

            $register = VerifyCode::create([
                'mobile_number' => $mobile_number,
                'creating_time' => $now,
                'expire_time' => $expireTime,
                'code' => $code
            ]);

            if ($register) {

                $msg = [
                    'status' => true,
                    'message' => 'Code Sent',
                    'code' => $code
                ];

                return response()->json($msg);
            }
        }else {

            $checkMobileInVerifyTable = VerifyCode::where('mobile_number', $mobile_number)
            ->first();

            if($checkMobileInVerifyTable != null) {

                $id = VerifyCode::findOrFail($checkMobileInVerifyTable->id);

                $expireTime = $id->expire_time;

                if($expireTime > time()) {

                    $msg = [
                        'status' => false,
                        'message' => 'The security code has not yet expired!!'
                    ];

                    return response()->json($msg);
                    
                }

                $now = time();

                $expireTime = $now + 120;

                $code = rand(1000, 9999);

                $id->mobile_number = $mobile_number;

                $id->creating_time = $now;

                $id->expire_time = $expireTime;

                $id->code = $code;

                $id->save();

                $msg = [
                    'status' => true,
                    'message' => 'A new login code has been sent',
                    'code' => $code
                ];

                return response()->json($msg);

            }

            $now = time();

            $expireTime = $now + 120;

            $code = rand(1000, 9999);

            $register = VerifyCode::create([
                'mobile_number' => $mobile_number,
                'creating_time' => $now,
                'expire_time' => $expireTime,
                'code' => $code
            ]);

            if ($register) {

                $msg = [
                    'status' => true,
                    'message' => 'The login code has been sent',
                    'code' => $code
                ];

                return response()->json($msg);

            }
        }
    }

    /**
     * User logout request send from here
     * @OA\Get(
     *     path="/api/auth/logout",
     *     tags={"Auth"},
     *     summary="User logout request send from here",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Logout Successful")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
    
    public function authLogout()
    {

        $user = request()->user();

        $user = json_decode($user, true);

        request()->user()->tokens()->where('name', 'login')->delete();

        $msg = [
            'status' => true,
            'message' => 'Logout Successful'
        ];

        return response()->json($msg);

    }


     /**
     * @OA\Tag(
     *     name="Register",
     *     description="Endpoints related to user authentication"
     * )
     */

    /**
     * @OA\Post(
     *     path="/api/Register",
     *     tags={"Register"},
     *     summary="User Register",
     *     description="User Register here",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="username", type="string", description="Unique username without space and unexpected chars", example="john_doe"),
     *                 @OA\Property(property="country_code", type="string", description="User country code without zero", example="98"),
     *                 @OA\Property(property="mobile_number", type="string", description="User mobile number with standard format without country code and zero", example="9356350256"),
     *                 @OA\Property(property="password", type="string", description="8-30 char string password", example="password123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="token", type="string", example="string"),
     *             @OA\Property(property="user", type="object",
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
     *                 @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2024-06-27T18:29:23.972Z"),
     *                 @OA\Property(property="is_mobile_verify", type="integer", example=0),
     *                 @OA\Property(property="is_user_verify", type="integer", example=0),
     *                 @OA\Property(property="is_staff", type="integer", example=0),
     *                 @OA\Property(property="email", type="string", example="string"),
     *                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-06-27T18:29:23.972Z"),
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
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Dear user, you have already registered"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */

     public function register(Request $request)
     {

        $validator = Validator::make($request->all(), [
            'username' => 'required|unique:users|max:84',
            'password' => 'required|unique:users|max:64'
        ]);

        if($validator->fails()) {

            $msg = [
                'success' => false,
                'errors' => $validator->errors() 
            ];

            return response()->json($msg, 422);

        }
 
         $user = request()->user();
 
         $checkRegister = User::where('mobile_number', $user->mobile_number)
         ->where('status_register', 'complete')
         ->first();
 
         if($checkRegister != null) {
 
             $msg = [
                 'status' => false,
                 'message' => 'Dear user, you have already registered',
                 'information' => json_decode($checkRegister, true)
             ];
 
             return response()->json($msg, 422);
 
         }
 
         $username = $request->username;
 
         $password = $request->password;
 
         $userId = User::findOrFail($user->id);
 
         $userId->username = $username;
 
         $userId->password = Hash::make($password);
 
         $userId->status_register = 'complete';
 
         $userId->save();
 
         $checkUser = User::where('mobile_number', $user->mobile_number)
         ->where('status_register', 'complete')
         ->first();
 
         if($checkUser != null) {
 
             $checkUser = User::findOrFail($checkUser->id);
 
             $token = $checkUser->createToken('register')->plainTextToken;
 
             $msg = [
                 'status' => 'Successful',
                 'token' => $token,
                 'user' => json_decode($checkUser, true)
             ];
 
             return response()->json($msg);
 
         }
 
         $msg = [
             'status' => false
         ];
 
         return response()->json($msg, 404);
 
     }

        /**
     * @OA\Post(
     *     path="/api/Register/verifyOtp",
     *     tags={"Register"},
     *     summary="User Register VerifyOtp",
     *     description="User Register Verify otp request send from here",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(property="otp", type="string", description="OTP code received from SMS or call", example="1234")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="OTP verified successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized"
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error"
     *     )
     * )
     */
     public function registerVerifyOtp(Request $request)
     {


     }

      /**
     * @OA\Tag(
     *     name="Home",
     *     description="Endpoints related to home and initial app setup"
     * )
     */

    /**
     * @OA\Get(
     *     path="/api/Home",
     *     tags={"Home"},
     *     summary="Home API for initializing app",
     *     description="Home API for initializing app",
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number of the results (default is 1)",
     *         required=false,
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
     *             @OA\Property(property="data", type="array",
     *                 @OA\Items(
     *                     @OA\Property(property="current_page", type="integer", example=0),
     *                     @OA\Property(property="total", type="integer", example=0),
     *                     @OA\Property(property="data", type="array",
     *                         @OA\Items(
     *                             @OA\Property(property="id", type="integer", example=0),
     *                             @OA\Property(property="user_id", type="integer", example=0),
     *                             @OA\Property(property="description", type="string", example="string"),
     *                             @OA\Property(property="media", type="array",
     *                                 @OA\Items(
     *                                     @OA\Property(property="id", type="integer", example=0),
     *                                     @OA\Property(property="title", type="string", example="string"),
     *                                     @OA\Property(property="type", type="string", example="string"),
     *                                     @OA\Property(property="source", type="string", example="string"),
     *                                     @OA\Property(property="dimensions", type="string", example="string"),
     *                                     @OA\Property(property="media_url", type="string", example="string"),
     *                                     @OA\Property(property="order", type="integer", example=0)
     *                                 )
     *                             ),
     *                             @OA\Property(property="user", type="object",
     *                                 @OA\Property(property="id", type="integer", example=0),
     *                                 @OA\Property(property="workarea_education_id", type="integer", example=0),
     *                                 @OA\Property(property="username", type="string", example="string"),
     *                                 @OA\Property(property="fullname", type="string", example="string"),
     *                                 @OA\Property(property="fullname_fa", type="string", example="string"),
     *                                 @OA\Property(property="firstname", type="string", example="string"),
     *                                 @OA\Property(property="firstname_fa", type="string", example="string"),
     *                                 @OA\Property(property="lastname", type="string", example="string"),
     *                                 @OA\Property(property="lastname_fa", type="string", example="string"),
     *                                 @OA\Property(property="about", type="string", example="string"),
     *                                 @OA\Property(property="avatar_url", type="string", example="string"),
     *                                 @OA\Property(property="medical_id", type="integer", example=0),
     *                                 @OA\Property(property="national_id", type="integer", example=0),
     *                                 @OA\Property(property="mobile_number", type="string", example="string"),
     *                                 @OA\Property(property="country_code", type="string", example="string"),
     *                                 @OA\Property(property="phone_verified_at", type="string", format="date-time", example="2024-06-27T20:40:17.473Z"),
     *                                 @OA\Property(property="is_mobile_verify", type="integer", example=0),
     *                                 @OA\Property(property="is_user_verify", type="integer", example=0),
     *                                 @OA\Property(property="is_staff", type="integer", example=0),
     *                                 @OA\Property(property="email", type="string", example="string"),
     *                                 @OA\Property(property="email_verified_at", type="string", format="date-time", example="2024-06-27T20:40:17.473Z"),
     *                                 @OA\Property(property="fcm_token", type="string", example="string"),
     *                                 @OA\Property(property="ws_token", type="string", example="string"),
     *                                 @OA\Property(property="birthday", type="string", example="string"),
     *                                 @OA\Property(property="city", type="string", example="string"),
     *                                 @OA\Property(property="country", type="string", example="string"),
     *                                 @OA\Property(property="gender", type="string", example="string"),
     *                                 @OA\Property(property="active", type="integer", example=0),
     *                                 @OA\Property(property="timezone", type="string", example="string"),
     *                                 @OA\Property(property="language", type="string", example="string")
     *                             )
     *                         )
     *                     )
     *                 )
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

    public function home()
    {

        $posts = DB::table('posts')
        ->select(['*'])
        ->paginate(5);

        $posts = json_decode(json_encode($posts), true);

        $postMedia = [];

        $result = [];

        for( $i = 0; $i < count($posts['data']); $i++ ) {

            $result[$i]['id'] = $posts['data'][$i]['id'];

            $result[$i]['username'] = $posts['data'][$i]['username'];

            $result[$i]['user_id'] = 0;

            $result[$i]['description'] = $posts['data'][$i]['description'];

            $result[$i]['media'] = $_SERVER['HTTP_HOST'] . '/images/' . $posts['data'][$i]['media'];

            $result[$i]['like'] = $posts['data'][$i]['like'];
        }

        $msg = [
            'success' => true,
            'data' => $result
        ];

        return response()->json($msg);

    }
}
