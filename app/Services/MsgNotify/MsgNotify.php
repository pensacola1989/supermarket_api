<?php

namespace App\Services\MsgNotify;

use App\Services\Comment\Comment;
use App\Services\Core\EntityBase;
use App\Services\Post\Like;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:53 PM
 */
class MsgNotify extends EntityBase
{
    //    protected $with = ['notifiable.Comment'];
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'notifies';
    /**
     * Not stored
     * @var array
     */
    protected $guarded = [];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    //    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'notify_id',
        'notify_type',
        'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function notifiable()
    {
        return $this->morphTo('notifiable', 'notify_type', 'notify_id');
    }

    /**
     * getter setter ,when access the morphTo relationship, always enter this getter
     * @param $value
     * @return string
     */
    public function getNotifyTypeAttribute($value)
    {
        if (is_null($value))
            return ($value);
        return ($value . 'WithPost');
    }
}
