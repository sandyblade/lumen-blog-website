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
use App\Models\Article;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Notification;

class CommentController extends BaseController
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['list']]);
    }

    public function list(int $id)
    {
        $article = Article::where("id", $id)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $comment = Comment::where("article_id", $id)->orderBy("id", "DESC")->get()->toArray();
        $result = $this->buildTree($comment);
        return response()->json(["message"=> "ok", "data"=> $result]);
    }

    public function create(int $id, Request $request)
    {
        $article = Article::where("id", $id)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $this->validate($request, ['comment' => 'required']);

        $user = Auth::User();

        $comment = Comment::create([
            'parent_id'     => $request->input("parent_id"),
            'article_id'    => $id,
            'user_id'       => $user->id,
            'comment'       => $request->input("comment")
        ]);

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Create Comment Article",
            "description"=> "Comment of article ".$article->title." as been created"
        ]);

        if($article->user_id != $user->id)
        {
            if($request->input("parent_id"))
            {
                Notification::create([
                    'user_id'=> $article->user_id,
                    'subject'=> "Reply Comment",
                    'message'=> "The user ".$user->email." reply comment to your article with title `".$article->title."`.",
                    'status'=> 0
                ]);
            }
            else
            {
                Notification::create([
                    'user_id'=> $article->user_id,
                    'subject'=> "Add Comment",
                    'message'=> "The user ".$user->email." add comment to your article with title `".$article->title."`.",
                    'status'=> 0
                ]);
            }

        }


        $total_comment = Comment::where("article_id", $article->id)->count();
        $article->total_comment = $total_comment;
        $article->save();

        return response()->json(["message"=> "ok", "data"=> $comment]);
    }

    public function delete(int $id)
    {
        $user = Auth::User();

        $comment = Comment::where("id", $id)->where("user_id", $user->id)->first();
        if(null === $comment)
        {
            return response()->json(["message"=> "Comment article with id `".$id."` was not found.!!"], 400);
        }

        $article = Article::where("id", $comment->article_id)->first();

        if(null === $article)
        {
            return response()->json(["message"=> "Article with id `".$id."` was not found.!!"], 400);
        }

        $title = $article->title;

        $comment->delete();

        Activity::create([
            "user_id"=> $user->id,
            "event"=> "Delete Comment Article",
            "description"=> "Comment of article ".$title." as been deleted"
        ]);

        return response()->json(["message"=> "ok", "data"=> $article]);
    }

    private function buildTree(array &$elements, $parentId = null) {
        $branch = array();
        foreach ($elements as $element) {
            if ($element['parent_id'] == $parentId) {
                $children = $this->buildTree($elements, $element['id']);
                if ($children) {
                    $element['children'] = $children;
                }else{
                    $element['children'] = [];
                }
                $branch[] = $element;
                unset($elements[$element['id']]);
            }
        }
        return $branch;
    }

}
