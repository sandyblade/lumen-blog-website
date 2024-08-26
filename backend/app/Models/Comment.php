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

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Article;
use App\Models\User;

class Comment extends Model
{
    protected $table = "comments";

    protected $fillable = [
        'parent_id',
        'article_id',
        'user_id',
        'comment'
    ];

    public function Parent() {
        return $this->belongsTo(Comment::class, 'parent_id');
    }

    public function Article() {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function User() {
        return $this->belongsTo(User::class, 'user_id');
    }

}
