<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Mail\RegistrationMail;
use App\Rating;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\UserAccount;
use Auth;
use Carbon\Carbon;





/**
 * @group  Authentication management
 *
 * APIs for Authenticating users
 */
class AuthController extends Controller
{


    public function __construct()
    {
    }


    public function signup(Request $request)
    {
        try {
            //return $request;
            $request->validate([
                'name' => 'required|string|min:3',
                'email' => 'required|string|email|max:255',
                'password' => 'required',
                'password_confirmation' => 'required|same:password',
                'phone' => 'required|unique:users',


            ]);

            // if(isset($request->phone)){
            //     $request->validate([
            //         'phone' => 'unique:users',        

            //     ]);
            // }       


            $user = User::where('email', $request->get('email'))->first();


            if ($user && ($user->active == false || strlen($user->password) < 8 || $user->password == '$2y$10$XuFENwoCWjr5NbqK1bmtKuGfQSY87WO785OC2rCoN1V471bsMcb9q')) {
                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'active' => true,
                    'referral_token' => Str::random(10) . date('dmyHis'),
                ]);

                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User created',
                    'access_token' => $tokenResult->accessToken,
                    'data' => $user,

                ]);
            } elseif ($user) {
                return response()->json([
                    'status' => 'exist',
                    'message' => 'User already exist. please login',
                ], 409);
            } else {

                $emailToken = Str::random(8) . date('dmyHis');
                $verifyEmailLink = "https://paylidate.com/verify/" . $emailToken;
                $user_name = 'user_' . rand(1,400) . date('dmyHis');
                //return $verifyEmailLink;


                $input = $request->all();
                $input['password'] = bcrypt($input['password']);
                $input['email_token'] = $emailToken;
                $input['username'] = $user_name;
                $input['referral_token'] = Str::random(10) . date('dmyHis');
                $user = User::create($input);

                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User created',
                    'access_token' => $tokenResult->accessToken,
                    'data' => $user,

                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e,


            ]);
        }
    }

    public function refferal_signup(Request $request, $ref)
    {
        try {
            //return $request;
            $request->validate([
                'name' => 'required|string|min:3',
                'email' => 'required|string|email|max:255',
                'password' => 'required',
                'password_confirmation' => 'required|same:password',
                'phone' => 'required|unique:users',


            ]);

            // if(isset($request->phone)){
            //     $request->validate([
            //         'phone' => 'unique:users',        

            //     ]);
            // }       


            $user = User::where('email', $request->get('email'))->first();


            if ($user && ($user->active == false || strlen($user->password) < 8 || $user->password == '$2y$10$XuFENwoCWjr5NbqK1bmtKuGfQSY87WO785OC2rCoN1V471bsMcb9q')) {
                $user->update([
                    'name' => $request->name,
                    'phone' => $request->phone,
                    'password' => bcrypt($request->password),
                    'active' => true,
                    'referral_token' => Str::random(10) . date('dmyHis'),
                ]);

                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User created',
                    'access_token' => $tokenResult->accessToken,
                    'data' => $user,

                ]);
            } elseif ($user) {
                return response()->json([
                    'status' => 'exist',
                    'message' => 'User already exist. please login',
                ], 409);
            } else {

                $emailToken = Str::random(8) . date('dmyHis');
                $verifyEmailLink = "https://paylidate.com/verify/" . $emailToken;
                $user_name = 'user_' . rand(1,400) . date('dmyHis');
                $reffer = User::where('referral_token', $ref)->get();
                //return $verifyEmailLink;


                $input = $request->all();
                $input['password'] = bcrypt($input['password']);
                $input['email_token'] = $emailToken;
                $input['username'] = $user_name;
                $input['referred_by'] = $reffer->id;
                $input['referral_token'] = Str::random(10) . date('dmyHis');
                $user = User::create($input);

                $tokenResult = $user->createToken('Personal Access Token');
                $token = $tokenResult->token;
                $token->save();

                return response()->json([
                    'status' => 'success',
                    'message' => 'User created',
                    'access_token' => $tokenResult->accessToken,
                    'data' => $user,

                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'Error',
                'message' => $e,


            ]);
        }
    }

    /**
     * Login user and create token
     *
     * @bodyParam email string required the email of the user
     * @bodyParam password string required the users prefered password
     * @bodyParam  remember_me boolean
     *
     * @return [string] access_token
     * @return [string] token_type
     * @return [string] expires_at
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // \Artisan::call('migrate');
        // $userss = User::all();
        // foreach ($userss as $user) {
        //     $user->update(
        //         [
        //             'referral_token' => Str::random(10) . date('dmyHis'),
        //         ]
        //     );
        // }

        //$credentials['active'] = 1;
        $credentials['email']   = strtolower($request->email);
        $credentials['password']   = $request->password;
        $credentials['deleted_at'] = null;


        if (!Auth::attempt($credentials))
            return response()->json([
                'status' => 'failed',
                'message' => 'Wrong email and password'
            ], 401);

        // if (isset(Auth::user()->email_token) || !Auth::user()->email_verified_at)
        //     {
        //         return response()->json([
        //         'status' => 'failed',
        //         'message' => 'Please verify your email address'
        //     ], 401);
        //         }

        if (!Auth::user()->active)
            return response()->json([
                'status' => 'failed',
                'message' => 'Your account is not activated'
            ], 401);

        $user = User::where('id', Auth::user()->id)->first();

        // $user_account = new UserAccount;
        // $account = $user_account->where('user_id', Auth::user()->id)->first();


        // if ($account && $account->ref) {
        //     $response = $user_account->getVirtualAccount($account->ref);

        //     if ($response['status'] == 'success') {
        //             $account['account'] = $response['data'];
        //         }
        // }

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        if ($request->remember_me)
            $token->expires_at = Carbon::now()->addWeeks(4);
        $token->save();



        return response()->json([
            'status' => 'success',
            'access_token' => $tokenResult->accessToken,
            'message' => 'login successful',
            'data' => $user
            // 'account' => $account
        ]);
    }

    /**
     * Logout user (Revoke the token)
     *
     * @authenticated
     *
     * @return [string] message
     */
    public function logout(Request $request)
    {
        $request->user()->token()->revoke();

        return response()->json([
            'status' => 'success',
            'message' => 'Logged out'
        ]);
    }

    /**
     * Get the authenticated User
     *
     * @authenticated
     *
     * @return [json] user object
     */
    public function user(Request $request)
    {
        $user = User::where('id', Auth::user()->id)->first();
        $account = UserAccount::where('user_id', Auth::user()->id)->first();

        if ($account && $account->ref) {
            $user_object = new User;
            $virtual_account = $user_object->getVirtualAccount($account->ref);

            if ($virtual_account['status'] == 'success') {
                $account['account'] = $virtual_account['data'];
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'user fetched',
            'data' => $user,
            'account' => $account
        ]);
    }


    public function verifyEmail($token)
    {


        $user = User::where('email_token', $token)->first();
        if (!$token || !$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'This verification token is invalid.'
            ], 404);
        }

        $user->email_verified_at = date("Y-m-d H:i:s", strtotime('now'));
        $user->email_token = null;
        $user->save();

        $tokenResult = $user->createToken('Personal Access Token');
        $token = $tokenResult->token;

        $token->save();

        return response()->json([
            'status' => 'success',
            'access_token' => $tokenResult->accessToken,
            'message' => 'login successful',
            'data' => $user,
            // 'account' => $account
        ]);
    }
    public function resendVerificationEmail($email)
    {


        $user = User::where('email', $email)->first();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'No user with such email.'
            ], 404);
        }
        $emailToken = Str::random(8) . date('dmyHis');
        $verifyEmailLink = "https://paylidate.com/verify/" . $emailToken;

        $user->email_verified_at = null;
        $user->email_token = $emailToken;
        $user->save();

        try {
            Mail::to($email)->send(new RegistrationMail($user, $verifyEmailLink));
            return response()->json([
                'status' => 'success',
                'message' => 'Email verification link sent',

            ]);
        } catch (\Exception $e) {

            return response()->json([
                'status' => 'error',
                'message' => 'Email sending error'
            ], 450);
        }
    }


    /**
     * User Activation
     *
     * * @urlParam  token string required the token sent to the users email address
     *
     * @return [json] user object
     */
    public function signupActivate($token)
    {
        $user = User::where('activation_token', $token)->first();
        if (!$user) {
            return response()->json([
                'status' => 'failed',
                'message' => 'This activation token is invalid.'
            ], 404);
        }

        $user->active = true;
        $user->activation_token = '';
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'user activated',
            'data' => $user
        ]);
    }


    /**
     * Update user
     *
     *
     * @bodyParam name string required the full name of the user
     * @bodyParam email string required the email of the user , this value is unige
     * @bodyParam phone string required the valide phone number of the user, this value is unige
     *
     *
     * @return [string] message
     */
    public function update(Request $request)
    {
        $input = $request->all();
        $id = Auth::user()->id;
        $user = User::where('id', $id)->update($input);
        $user = Auth::user();

        return response()->json([
            'status' => 'success',
            'message' => 'User updated',
            'data' => $user,
        ]);
    }

    public function check_email($email)
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            return response()->json([
                'status' => 'true',
                'message' => 'User exist.',
            ]);
        } else {
            return response()->json([
                'status' => 'false',
                'message' => 'User doesnot exist.'
            ], 406);
        }
    }

    public function rate_user(Request $request, $id)
    {
        try {
            
            if($id == auth()->user()->id){
                return response()->json([
                    'status' => 'error',
                    'message' => 'Error user can rate self.'
                ]);
            }

            $rating = new Rating;
            $rating->rated_user_id = $id;
            $rating->user_id = auth()->user()->id;
            $rating->rating = $request->rating;
            $rating->save();
            return response()->json([
                'status' => 'Success',
                'message' => 'User rated sousecfully'
            ], 200);

        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                'status' => 'Error',
                'message' => 'An Error Occured',
                'data' => $th
            ]);
        }
    }

    public function get_user_ratings($id)
    {
        try {
            $ratings = Rating::where('rated_user_id', $id)->get();
            $totalScore = 0;
            if(!$ratings){
                return response()->json([
                    'message' => 'no ratings found for user',
                    'data' => 0
                ]);
            }
            foreach ($ratings as $rating) {
                $totalScore += $rating->rating;
            }
            $averageScore = $totalScore / count($ratings);
            
            return response()->json([
                'status' => 'Success',
                'message' => 'ratings found',
                'data' => $averageScore
            ]);

        } catch(\Throwable $th) {
            return response()->json([
                'status' => 'Error',
                'message' => 'An Error Occured',
                'data' => $th
            ]);
        }
    }
}
