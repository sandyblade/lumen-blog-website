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
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use App\Models\Activity;
use App\Models\Article;
use App\Models\Viewer;
use App\Models\Notification;

class ArticleController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['list', 'read']]);
    }

    public function list(Request $request)
    {
        $data = Article::where("status", 1);
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 10);
        $total = $data->count();
        $offset = (($page-1)*$limit);
        $order_by = $request->input("order_by", "id");
        $order_dir = $request->input("order_dir", "desc");

        if($request->input("search"))
        {
            $data->where(function ($query) use ($request) {
                $query->where('title', "like", "%" . $request->search . "%");
                $query->orWhere('description', "like", "%" . $request->search . "%");
                $query->orWhere('categories', "like", "%" . $request->search . "%");
                $query->orWhere('tags', "like", "%" . $request->search . "%");
                $query->orWhere('content', "like", "%" . $request->search . "%");
            });
        }

        $list = $data->offset($offset)->limit($limit)->orderBy($order_by, $order_dir)->get();

        $list = $list->map(function($row){
            $row->json_categories = json_decode($row->categories);
            $row->json_tags = json_decode($row->tags);
            return $row;
        });

        return response()->json(["total"=> $total, "list"=> $list]);
    }

    public function create(Request $request)
    {
        $this->validate($request, [
            'title' => 'required|max:255|unique:articles',
            'description' => 'required|min:6',
            'status' => 'required',
            'content' => 'required|min:6',
        ]);

        $user = Auth::User();
        $title = $request->input("title");
        $description = $request->input("description");
        $content = $request->input("content");
        $categories = $request->input("categories");
        $tags = $request->input("tags");
        $status = $request->input("status");
        $slug = $this->slugify($title);

        $article = new Article();
        $article->user_id = $user->id;
        $article->title = $title;
        $article->description = $description;
        $article->content = $content;
        $article->categories = $categories ? json_encode($categories) : json_encode([]);
        $article->tags = $tags ? json_encode($tags) : json_encode([]);
        $article->status = $status;
        $article->slug = $slug;
        $article->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Create New Article",
            "description"=> "A new article with title `".$title."` has been created."
        ]);

        return response()->json(["message"=> "ok", "data"=> $article]);

    }

    public function read(string $slug)
    {
        $article = Article::where("slug", $slug)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with slug `".$slug."` was not found.!!"], 400);
        }

        $user = Auth::User();
        if($user)
        {

            if($user->id != $article->user_id)
            {
                $viewer = Viewer::where("user_id", $user->id)->where("article_id", $article->id)->first();

                if(null === $viewer)
                {
                    Viewer::create(["user_id"=> $user->id, "article_id"=> $article->id]);

                    Notification::create([
                        'user_id'=> $article->user_id,
                        'subject'=> "Read Article",
                        'message'=> "The user ".$user->email." view to your article with title `".$article->title."`.",
                        'status'=> 0
                    ]);

                    $total_viewer = Viewer::where("article_id", $article->id)->count();
                    $article->total_viewer = $total_viewer;
                    $article->save();

                }

            }

        }

        $article = Article::where("slug", $slug)->first();
        $article->json_categories = json_decode($article->categories);
        $article->json_tags = json_decode($article->tags);

        return response()->json(["message"=> "ok", "data"=> $article]);

    }

    public function update($id, Request $request)
    {
        $user = Auth::User();
        $article = Article::where("id", $id)->where("user_id", $user->id)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $this->validate($request, [
            'title' => 'required|max:255|unique:articles,title,'.$article->id,
            'description' => 'required|min:6',
            'status' => 'required',
            'content' => 'required|min:6',
        ]);

        $title = $request->input("title");
        $description = $request->input("description");
        $content = $request->input("content");
        $categories = $request->input("categories");
        $tags = $request->input("tags");
        $status = $request->input("status");
        $slug = $this->slugify($title);

        $article->title = $title;
        $article->description = $description;
        $article->content = $content;
        $article->categories = $categories ? json_encode($categories) : json_encode([]);
        $article->tags = $tags ? json_encode($tags) : json_encode([]);
        $article->status = $status;
        $article->slug = $slug;
        $article->save();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Edit Article",
            "description"=> "An a article with title `".$title."` has been updated."
        ]);

        return response()->json(["message"=> "ok", "data"=> $article]);

    }

    public function delete(int $id)
    {
        $user = Auth::User();
        $article = Article::where("id", $id)->where("user_id", $user->id)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $title = $article->title;

        $article->delete();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Delete Article",
            "description"=> "An a article with title `".$title."` has been deleted."
        ]);

        return response()->json(["message"=> "ok", "data"=> $article]);

    }

    public function user(Request $request)
    {
        $user = Auth::User();
        $data = Article::where("user_id", $user->id);
        $page = $request->input("page", 1);
        $limit = $request->input("limit", 10);
        $total = $data->count();
        $offset = (($page-1)*$limit);
        $order_by = $request->input("order_by", "id");
        $order_dir = $request->input("order_dir", "desc");

        if($request->input("search"))
        {
            $data->where(function ($query) use ($request) {
                $query->where('title', "like", "%" . $request->search . "%");
                $query->orWhere('description', "like", "%" . $request->search . "%");
                $query->orWhere('categories', "like", "%" . $request->search . "%");
                $query->orWhere('tags', "like", "%" . $request->search . "%");
                $query->orWhere('content', "like", "%" . $request->search . "%");
            });
        }

        $list = $data->offset($offset)->limit($limit)->orderBy($order_by, $order_dir)->get();
        return response()->json(["total"=> $total, "list"=> $list]);
    }

    public function words(Request $request)
    {
        $result = [];
        $max = $request->input("max", 10);

        for($i = 1; $i <= $max; $i++)
        {
            $faker = Faker::create();
            $result[] = ucfirst($faker->sentence(2));
        }

        sort($result);

        return response()->json(["message"=> "ok", "data"=> $result]);
    }

    public function upload(int $id, Request $request)
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

        $article = Article::where("id", $id)->where("user_id", $user->id)->first();
        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $image = md5(Str::random(34));
        if($request->file('file_image')){
            $upload = $request->file('file_image')->move($dirUpload, $image);
            if($upload){

                if(!is_null($article->image)){
                    $currentUpload = $dirUpload."/".$article->image;
                    if(file_exists($currentUpload)){
                        unlink($currentUpload);
                    }
                }

                $article->image = $image;
                $article->save();

                Activity::create([
                    "user_id"=> $article->id,
                    "event"=> "Upload Image Article",
                    "description"=> "Upload new article image"
                ]);

            }
        }

        return response()->json(["message"=> "Your image article has been changed !!", "image"=> $image]);
    }

    private function slugify($text)
    {
        $text = preg_replace('~[^\pL\d]+~u', '-', $text);
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
        $text = preg_replace('~[^-\w]+~', '', $text);
        $text = trim($text, '-');
        $text = preg_replace('~-+~', '-', $text);
        $text = strtolower($text);
        if (empty($text)) {
           return 'n-a';
        }
        return $text;
    }

}
