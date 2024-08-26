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

use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Models\Article;
use App\Models\Activity;
use App\Models\Comment;
use App\Models\Notification;
use App\Models\Viewer;

class User extends Model implements AuthenticatableContract, AuthorizableContract, JWTSubject
{
    use Authenticatable, Authorizable, HasFactory;

    protected $table = "users";

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'email',
        'phone',
        'password',
        'image',
        'first_name',
        'last_name',
        'gender',
        'country',
        'facebook',
        'instagram',
        'job_title',
        'twitter',
        'linked_in',
        'address',
        'about_me',
        'reset_token',
        'confirm_token',
        'confirmed',
        'remember_token'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token'
    ];

    /**
     * Get the identifier that will be stored in the subject claim of the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function Articles() {
        return $this->hasMany(Article::class);
    }

    public function Activities() {
        return $this->hasMany(Activity::class);
    }

    public function Comments() {
        return $this->hasMany(Comment::class);
    }

    public function Notifications() {
        return $this->hasMany(Notification::class);
    }

    public function Viewers() {
        return $this->hasMany(Viewer::class);
    }

}
