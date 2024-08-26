<?php

/**
 * This file is part of the Sandy Andryanto Blog Application.
 *
 * @author     Sandy Andryanto <sandy.andryanto.blade@gmail.com>
 * @copyright  2024
 *
 * For the full copyright and license information,
 * please view the LICENSE.md file that was distributed
 * with this source code.
 */

namespace App\Http\Controllers;

use Laravel\Lumen\Routing\Controller as BaseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;
use App\Models\User;
use App\Models\Activity;

class AuthController extends BaseController
{

    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email|max:180',
            'password' => 'required|string',
        ]);

        $credentials = $request->only(['email', 'password']);

        if (! $token = Auth::attempt($credentials)) {
            return response()->json(['message' => 'These credentials do not match our records.'], 401);
        }

        $auth_user = auth()->user();
        $confirmed = (int) $auth_user->confirmed;

        if($confirmed == 0)
        {
            return response()->json(['message' => 'You need to confirm your account. We have sent you an activation code, please check your email.'], 401);
        }

        Activity::create([
            "user_id"=> $auth_user->id,
            "event"=> "Sign In",
            "description"=> "Sign in to application"
        ]);

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $faker = Faker::create();

        $this->validate($request, [
            'email' => 'required|email|max:180|unique:users',
            'password' => 'required|min:6|confirmed',
        ]);

        $email = $request->input('email');
        $password = $request->input('password');

        $user = User::create([
            "email"=> $email,
            "password"=> Hash::make($password),
            "confirmed"=> 1,
            "remember_token"=> $faker->uuid(),
            "confirm_token"=> $faker->uuid()
        ]);

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Sign Up",
            "description"=> "Register new user account"
        ]);

        return response()->json(['message' => 'You need to confirm your account. We have sent you an activation code, please check your email.'], 200);

    }

    public function confirm(string $token)
    {
        $user = User::where("confirm_token", $token)->where("confirmed", 0)->first();

        if(null === $user)
        {
            return response()->json(['message' => "We can't find a user with that  token is invalid.!"], 400);
        }

        $user->confirmed = 1;
        $user->confirm_token = null;
        $user->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Email Verification",
            "description"=> "Confirm new member registration account"
        ]);

        return response()->json(['message' => 'Your e-mail is verified. You can now login.'], 200);
    }

    public function forgot(Request $request)
    {
        $faker = Faker::create();
        $token = $faker->uuid();

        $this->validate($request, [
            'email' => 'required|email|max:180'
        ]);

        $email = $request->input('email');
        $user = User::where("email", $email)->first();

        if(null === $user)
        {
            return response()->json(['message' => "We can't find a user with that e-mail address."], 400);
        }

        $user->reset_token = $token;
        $user->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Forgot Password",
            "description"=> "Request reset password link"
        ]);

        return response()->json(['message' => 'We have e-mailed your password reset link!'], 200);
    }

    public function reset(string $token, Request $request)
    {
        $user = User::where("reset_token", $token)->first();

        if(null === $user)
        {
            return response()->json(['message' => "We can't find a user with that token is invalid.!"], 400);
        }

        $this->validate($request, [
            'email' => 'required|email|max:180',
            'password' => 'required|min:6|confirmed',
        ]);

        $password = $request->input('password');
        $user->password = Hash::make($password);
        $user->reset_token = null;
        $user->confirm_token = null;
        $user->confirmed = 1;
        $user->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Reset Password",
            "description"=> "Reset account password"
        ]);

        return response()->json(['message' => 'Your password has been reset!'], 200);
    }

    private function respondWithToken($token)
    {
        /**
        * @disregard P1009 Undefined type
        */
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }
}
