<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class LoginController extends Controller
{
    public function redirectToGoogle()
    {
        // return Socialite::driver('google')
        // ->scopes(['openid', 'profile', 'email'])
        // ->redirectUrl(env('GOOGLE_REDIRECT')) // Use the correct variable
        // ->with([
        //     'client_id' => env('GOOGLE_CLIENT_ID'),
        //     'state' => 'RceSIPddgOPKeYvptGHPX8qcjJOffhr76C74RmVH',
        // ])->redirect();
        // return Socialite::driver('google')->redirect();
        return Socialite::driver('google')
        ->with(['prompt' => 'select_account']) // Force account selection
        ->redirect();

    }

    public function handleGoogleCallback()
    {
        $user = Socialite::driver('google')->user();
        $existingUser = User::where('email', $user->getEmail())->first();

        if ($existingUser) {
            Auth::login($existingUser);
        } else {
            $newUser = User::create([
                'name' => $user->getName(),
                'email' => $user->getEmail(),
                'google_id' => $user->getId(),
                'google_token' => $user->token,
            ]);

            Auth::login($newUser);
        }

        return redirect()->intended('/home');
    }
    public function index()
{
    return view('home');
}
public function buttonlogin()
{
    return view('auth.login');
}
}
