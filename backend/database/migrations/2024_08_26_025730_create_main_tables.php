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

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email', 191)->unique();
            $table->string('phone', 64)->nullable()->unique();
            $table->string('password', 255)->index();
            $table->string('image', 255)->nullable()->index();
            $table->string('first_name', 64)->nullable()->index();
            $table->string('last_name', 64)->nullable()->index();
            $table->string('gender', 2)->nullable()->index();
            $table->string('country', 180)->nullable()->index();
            $table->string('facebook', 180)->nullable()->index();
            $table->string('instagram', 180)->nullable()->index();
            $table->string('twitter', 180)->nullable()->index();
            $table->string('linked_in', 180)->nullable()->index();
            $table->text('address')->nullable();
            $table->text('about_me')->nullable();
            $table->string('reset_token', 36)->nullable()->index();
            $table->string('confirm_token', 36)->nullable()->index();
            $table->tinyInteger('confirmed')->default(0)->index();
            $table->rememberToken();
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        Schema::create('notifications', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('subject', 255)->index();
            $table->text('message')->nullable();
            $table->tinyInteger('status')->default(0)->index();
            # begin foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            # end foreign key
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        Schema::create('activities', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('event', 255)->index();
            $table->text('description')->nullable();
            # begin foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            # end foreign key
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->string('image', 255)->nullable()->index();
            $table->string('slug', 255)->unique();
            $table->string('title', 255)->unique();
            $table->text('description');
            $table->longText('content');
            $table->longText('categories')->nullable();
            $table->longText('tags')->nullable();
            $table->Integer('total_viewer')->default(0)->index();
            $table->Integer('total_comment')->default(0)->index();
            $table->tinyInteger('status')->default(0)->index();
            # begin foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            # end foreign key
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        Schema::create('comments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('article_id')->index();
            $table->unsignedBigInteger('user_id')->index();
            $table->text('comment');
            # begin foreign key
            $table->foreign('parent_id')->references('id')->on('comments')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            # end foreign key
            $table->timestamps();
            $table->engine = 'InnoDB';
        });

        Schema::create('viewers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->index();
            $table->unsignedBigInteger('article_id')->index();
            $table->tinyInteger('status')->default(0)->index();
            # begin foreign key
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
            # end foreign key
            $table->timestamps();
            $table->engine = 'InnoDB';
        });


    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('viewers');
        Schema::dropIfExists('comments');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('activities');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('users');
    }
};
