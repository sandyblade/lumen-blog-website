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
use App\Models\User;
use App\Models\Comment;
use App\Models\Viewer;

class Article extends Model
{
    protected $table = "articles";

    protected $fillable = [
        'user_id',
        'image',
        'slug',
        'title',
        'description',
        'content',
        'categories',
        'tags',
        'total_viewer',
        'total_comment',
        'status'
    ];

    public function User() {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Comments() {
        return $this->hasMany(Comment::class);
    }

    public function Viewers() {
        return $this->hasMany(Viewer::class);
    }

}
