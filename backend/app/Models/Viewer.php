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

class Viewer extends Model
{
    protected $table = "viewers";

    protected $fillable = [
        'user_id',
        'article_id',
        'status'
    ];

    public function Article() {
        return $this->belongsTo(Article::class, 'article_id');
    }

    public function User() {
        return $this->belongsTo(User::class, 'user_id');
    }

}
