<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use Validator;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class LoginController extends Controller
{
    // public function loginWithGoogle(Request $request)
    // {
    //     try {
    //         $user = Socialite::driver('google')->stateless()->user();
    //     } catch (\Exception $e) {
    //         return response()->json(['message' => 'Google authentication failed'], 401);
    //     }

    //     $existingUser = User::where('email', $user->getEmail())->first();

    //     if ($existingUser) {
    //         Auth::login($existingUser);
    //     } else {
    //         $newUser = User::create([
    //             'name' => $user->getName(),
    //             'email' => $user->getEmail(),
    //             'google_id' => $user->getId(),
    //             'google_token' => $user->token,
    //         ]);

    //         Auth::login($newUser);
    //     }

    //     return response()->json(['message' => 'Login successful'], 200);
    // }


    public function redirectToGoogle()
    {

        $url = Socialite::driver('google')->redirect()->getTargetUrl();
        return response()->json(['redirect_url' => $url]);
    }

    public function handleGoogleCallback()
    {

        $code = $request->input('code');

        if (!$code) {
            return response()->json(['error' => 'Authorization code missing'], 400);
        }

        $response = Socialite::driver('google')->getAccessTokenResponse($code);

        $accessToken = $response['access_token'];
        $userData = Socialite::driver('google')->userFromToken($accessToken);

        // Check if the user already exists
        $user = User::where('email', $userData->getEmail())->first();

        if (!$user) {
            // Create a new user
            $user = new User();
            $user->name = $userData->getName();
            $user->email = $userData->getEmail();
            $user->password = Hash::make(str_random(16)); // Generate a random password
            $user->save();
        }

        // You can generate a JWT token here for user authentication if needed

        return response()->json(['user' => $user]);
        // $user = Socialite::driver('google')->user();
        // $existingUser = User::where('email', $user->getEmail())->first();

        // if ($existingUser) {
        //     Auth::login($existingUser);
        //     return response()->json(['message' => 'Successfully logged in']);
        // } else {
        //     return response()->json(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        // }
    }

    // public function loginWithSocialMedia(Request $request)



	// {



	// 	$error_message = 	[



	// 		'email.required'   			=> 'Email address should be required',



	// 		'social_media_id.required'  => 'Social media id should be required',



	// 		'device_token.required'     => 'Device token should be required',



	// 		'login_type.required'     	=> 'User type should be required',



	// 	];





	// 	$rules = [



	// 		'social_media_id'       => 'required',



	// 		'device_token'          => 'required',



	// 		'login_type'          	=> 'required',



	// 	];



	// 	$validator = Validator::make($request->all(), $rules, $error_message);



	// 	if ($validator->fails()) {



	// 		return $this->sendFailed($validator->errors()->first(), 201);



	// 	}



	// 	try {



	// 		$user_detail = User::where('social_media_id', $request->social_media_id)->first();



	// 		if (!isset($user_detail) || $user_detail->social_media_id == '') {



	// 			$user_detail = User::create($request->only('email', 'social_media_id', 'device_token', 'login_type'));



	// 		}



	// 		if (auth()->loginUsingId($user_detail->id)) {



	// 			\DB::beginTransaction();



	// 			$access_token 	  = $user_detail->createToken("API TOKEN")->plainTextToken;



	// 			$access_token 	  = explode('|', $access_token)[1];



	// 			User::where('device_token', $request->device_token)->where('email', '!=', $request->email)->update(['device_token' => null]);



	// 			if ($user_detail->unique_id == '') {



	// 				$unique_id  = self::uniqueNumber();



	// 				$request->unique_id = $unique_id;



	// 				$request['unique_id'] = $unique_id;



	// 			} else {



	// 				$request->unique_id = $user_detail->unique_id;



	// 				$request['unique_id'] = $user_detail->unique_id;



	// 			}



	// 			auth()->user()->fill($request->only('device_token', 'unique_id'))->save();



	// 			\DB::commit();



	// 			return $this->sendSuccess('LOGGED IN SUCCESSFULLY', ['access_token' => $access_token, 'profile_data' => new UserProfileCollection(auth()->user())]);



	// 		} else {



	// 			return $this->sendFailed('WE COULD NOT FOUND ANY ACCOUNT', 201);



	// 		}



	// 	} catch (\Throwable $e) {



	// 		\DB::rollback();



	// 		return $this->sendFailed($e->getMessage() . ' on line ' . $e->getLine(), 400);



	// 	}



	// }

    public function loginWithSocialMedia(Request $request)
{
    $error_message = [
        'email.required'         => 'Email address is required',
        'social_media_id.required' => 'Social media id is required',
        // 'device_token.required'   => 'Device token is required',
        'login_type.required'     => 'User type is required',
    ];

    $rules = [
        'social_media_id' => 'required',
        // 'device_token'    => 'required',
        'login_type'      => 'required',
    ];

    $validator = Validator::make($request->all(), $rules, $error_message);

    if ($validator->fails()) {
        return response()->json(['error' => $validator->errors()->first()], 201);
    }

    try {
        $user_detail = User::where('social_media_id', $request->social_media_id)->first();

        if (!isset($user_detail) || $user_detail->social_media_id == '') {
            $user_detail = User::create($request->only('email', 'social_media_id', 'login_type'));
        }

        if (auth()->loginUsingId($user_detail->id)) {
            \DB::beginTransaction();
            $access_token = $user_detail->createToken("API TOKEN")->plainTextToken;
            $access_token = explode('|', $access_token)[1];

            // User::where('email', '!=', $request->email)->update(['device_token' => null]);

            // if ($user_detail->unique_id == '') {
            //     $unique_id = self::uniqueNumber();
            //     $request->unique_id = $unique_id;
            //     $request['unique_id'] = $unique_id;
            // } else {
            //     $request->unique_id = $user_detail->unique_id;
            //     $request['unique_id'] = $user_detail->unique_id;
            // }

            auth()->user()->fill($request->only('device_token', 'unique_id'))->save();
            \DB::commit();

            return response()->json([
                'message' => 'LOGGED IN SUCCESSFULLY',
                'data' => ['access_token' => $access_token, 'profile_data' => auth()->user()]
            ]);
        } else {
            return response()->json(['error' => 'WE COULD NOT FIND ANY ACCOUNT'], 201);
        }
    } catch (\Throwable $e) {
        \DB::rollback();
        return response()->json(['error' => $e->getMessage() . ' on line ' . $e->getLine()], 400);
    }
}


}

