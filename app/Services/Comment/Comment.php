<?php

namespace App\Services\Comment;

use App\Services\Account\User;
use App\Services\Attachments\Attachment;
use App\Services\Core\EntityBase;
use App\Services\MsgNotify\MsgNotify;
use App\Services\Post\Post;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 10:23 PM
 */
class Comment extends EntityBase
{
    //    protected $with = ['Parent', 'Post'];
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'comments';
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
    protected $fillable = [
        'content',
        'visible',
        'photo_id',
        'is_top'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function Post()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }

    public function photo()
    {
        return $this->belongsTo(Attachment::class, 'photo_id');
    }

    public function FromUser()
    {
        return $this->belongsTo(User::class, 'from_uid');
    }

    public function ToUser()
    {
        return $this->belongsTo(User::class, 'to_uid');
    }

    public function Parent()
    {
        return $this->belongsTo(Comment::class, 'reply_id');
    }

    public function subComments()
    {
        return $this->hasMany(Comment::class, 'reply_id')->where('visible', 1);
    }

    public function Notify()
    {
        return $this
            ->morphMany(MsgNotify::class, 'notifiable', 'notify_type', 'notify_id');
    }

    public function slientUpdate(array $attributes = null)
    {
        return static::withoutEvents(function () use ($attributes) {
            return $this->update($attributes);
        });
    }

    public function shouldUpdateVisible($newIsTopVal)
    {
        $this->is_top = $newIsTopVal;
        return $this->isDirty('is_top') && $this->attributes['is_top'];
    }
}
