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
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\Activity;

class AccountController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function detail()
    {
        $user = Auth::User();
        return response()->json($user);
    }

    public function logout()
    {

        $user = Auth::User();
        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Sign Out",
            "description"=> "Sign Out from application"
        ]);

        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }

    public function refresh()
    {
        $user = Auth::User();
        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Refresh Token",
            "description"=> "Generate new auth token"
        ]);

        /**
        * @disregard P1009 Undefined type
        */
        return $this->respondWithToken(auth()->refresh());
    }

    public function update(Request $request)
    {
        /**
        *
        * @var user User
        */
        $user = Auth::User();

        $rules = [
            'email' => 'required|email|max:180|unique:users,email,'.$user->id
        ];

        if($request->input('phone'))
        {
            $rules["phone"] = 'numeric|digits_between:4,14|unique:users,phone,'.$user->id;
        }

        $this->validate($request, $rules);

        $user->email = $request->input('email');

        if($request->input('phone'))
        {
            $user->phone = $request->input('phone');
        }

        $user->first_name = $request->input('first_name');
        $user->last_name = $request->input('last_name');
        $user->gender = $request->input('gender');
        $user->country = $request->input('country');
        $user->facebook = $request->input('facebook');
        $user->instagram = $request->input('instagram');
        $user->twitter = $request->input('twitter');
        $user->linked_in = $request->input('linked_in');
        $user->address = $request->input('address');
        $user->about_me = $request->input('about_me');
        $user->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Change Profile",
            "description"=> "Edit user profile account"
        ]);

        /**
        * @disregard P1009 Undefined type
        */
        return $this->respondWithToken(auth()->refresh(), "Your profile has been changed !!");
    }

    public function password(Request $request)
    {
        /**
        *
        * @var user User
        */
        $user = Auth::User();

        $this->validate($request, [
            'current_password' => 'required',
            'password' => 'required|min:6|confirmed',
        ]);

        $current_password = $request->input('current_password');
        $password = $request->input('password');

        $hashedPassword = $user->password;
        if (!Hash::check($current_password, $hashedPassword)) {
            return response()->json(["message"=> "Incorrect current password please try again !!"], 400);
        }


        $user->password = Hash::make($password);
        $user->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Change Password",
            "description"=> "Edit user password account"
        ]);

        return response()->json(['message' => 'Your password has been changed!!'], 200);
    }

    public function upload(Request $request)
    {
        /**
        *
        * @var user User
        */
        $user = Auth::User();
        $dirUpload = storage_path('files');

        if(!is_dir($dirUpload)){
            @mkdir($dirUpload);
        }

        $image = md5(Str::random(34));
        if($request->file('file_image')){
            $upload = $request->file('file_image')->move($dirUpload, $image);
            if($upload){

                if(!is_null($user->image)){
                    $currentUpload = $dirUpload."/".$user->image;
                    if(file_exists($currentUpload)){
                        unlink($currentUpload);
                    }
                }

                $user->image = $image;
                $user->save();

                Activity::create([
                    "user_id"=> $user->id,
                    "event"=> "Upload Image Profile",
                    "description"=> "Upload new user profile image"
                ]);

            }
        }

        return response()->json(["message"=> "Your profile picture has been changed !!", "image"=> $image]);
    }

    public function activity(Request $request)
    {
        /**
        *
        * @var user User
        */
        $user = Auth::User();
        $data = Activity::where("user_id", $user->id);
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 10);
        $total = $data->count();
        $offset = (($page-1)*$limit);
        $order_by = $request->input("order_by", "id");
        $order_dir = $request->input("order_dir", "desc");

        if($request->input("search"))
        {
            $data->where(function ($query) use ($request) {
                $query->where('event', "like", "%" . $request->search . "%");
                $query->orWhere('description', "like", "%" . $request->search . "%");
            });
        }

        $list = $data->offset($offset)->limit($limit)->orderBy($order_by, $order_dir)->get();
        return response()->json(["total"=> $total, "list"=> $list]);
    }

    private function respondWithToken($token, $message = "ok")
    {
        /**
        * @disregard P1009 Undefined type
        */
        return response()->json([
            'message'=> $message,
            'access_token' => $token,
            'token_type' => 'bearer',
            'user' => auth()->user(),
            'expires_in' => auth()->factory()->getTTL() * 60 * 24
        ]);
    }
}
