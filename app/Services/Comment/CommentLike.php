<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 10:09 PM
 */

namespace App\Services\Comment;


use App\Services\Account\User;
use App\Services\Core\EntityBase;
use App\Services\MsgNotify\MsgNotify;
use App\Services\Comment\Comment;

class CommentLike extends EntityBase
{
    //    protected $with = ['Post'];
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'comment_likes';
    /**
     * Not stored
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Comment()
    {
        return $this->belongsTo(Comment::class, 'comment_id');
    }

    public function Notify()
    {
        return $this
            ->morphMany(MsgNotify::class, 'notifiable', 'notify_type', 'notify_id');
    }
}
