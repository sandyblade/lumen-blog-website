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
use App\Models\Activity;
use App\Models\Notification;

class NotificationController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function list(Request $request)
    {
        /**
        *
        * @var user User
        */
        $user = Auth::User();
        $data = Notification::where("user_id", $user->id);
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 10);
        $total = $data->count();
        $offset = (($page-1)*$limit);
        $order_by = $request->input("order_by", "id");
        $order_dir = $request->input("order_dir", "desc");

        if($request->input("search"))
        {
            $data->where(function ($query) use ($request) {
                $query->where('subject', "like", "%" . $request->search . "%");
                $query->orWhere('message', "like", "%" . $request->search . "%");
            });
        }

        $list = $data->offset($offset)->limit($limit)->orderBy($order_by, $order_dir)->get();
        return response()->json(["total"=> $total, "list"=> $list]);
    }

    public function read(int $id)
    {
        $user = Auth::User();
        $notification = Notification::where("id", $id)->where("user_id", $user->id)->first();

        if(null === $notification)
        {
            return response()->json(['message' => 'Notification with id '.$id.' was not found.!!'], 400);
        }

        $status = (int) $notification->status;

        if($status == 0)
        {
            $notification->status = 1;
            $notification->save();

            Activity::create([
                "user_id"=> $user->id,
                "event"=> "Read notification",
                "description"=> "The user read notification with subject ".$notification->subject
            ]);

        }

        $notification = Notification::where("id", $id)->where("user_id", $user->id)->first();

        return response()->json($notification);
    }

    public function delete(int $id)
    {
        $user = Auth::User();
        $notification = Notification::where("id", $id)->where("user_id", $user->id)->first();

        if(null === $notification)
        {
            return response()->json(['message' => 'Notification with id '.$id.' was not found.!!'], 400);
        }

        $notification->delete();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Delete notification",
            "description"=> "The user delete notification with subject ".$notification->subject
        ]);

        return response()->json(['message' => 'ok'], 200);
    }

}
