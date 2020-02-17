<?php

namespace App\Services\Account;

use App\Services\Account\Block\UserBlock;
use App\Services\Attachments\Attachment;
use App\Services\Core\EntityBase;
use App\Services\HistoryPlace\HistoryPlace;
use App\Services\MsgNotify\MsgNotify;
use App\Services\NotifyRegister\NotifyRegister;
use App\Services\Place\PlaceSubscribe;
use App\Services\Post\Like;
use App\Services\Post\Post;
use Illuminate\Auth\Authenticatable;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Lumen\Auth\Authorizable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use App\Services\Place\Place;
use App\Services\Place\PlaceBlock;

/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/26/2015
 * Time: 2:23 PM
 */
class User extends EntityBase implements JWTSubject, AuthenticatableContract, AuthorizableContract
{
    use Authenticatable, Authorizable;
    use SoftDeletes;
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'users';
    /**
     * Not stored
     * @var array
     */
    protected $guarded = [];

    /**
     * automatically hash the password when store
     * @param $value
     */
    //    public function setPasswordAttribute($value)
    //    {
    //        $this->attributes['password'] = bcrypt($value);
    //    }

    //    public function setEmailAttribute($value)
    //    {
    //        if (empty($value)) { // will check for empty string, null values, see php.net about it
    //            $this->attributes['email'] = NULL;
    //        } else {
    //            $this->attributes['email'] = $value;
    //        }
    //    }

    public function touchSystemReadTime()
    {
        $this->sys_notify_read_time = $this->freshTimestamp();
        return $this->save();
    }

    /***
     * User Avatar model
     */
    public function Avatar()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id');
    }

    /**
     * User posts model
     */
    public function Posts()
    {
        return $this->hasMany(Post::class, 'user_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany logs
     */
    public function Logins()
    {
        return $this->hasMany(Login::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany likes
     */
    public function Likes()
    {
        return $this->hasMany(Like::class, 'user_id');
    }

    public function HistoryViews()
    {
        return $this->hasMany(HistoryPlace::class, 'user_id');
    }

    public function NotifyRegister()
    {
        return $this->hasOne(NotifyRegister::class, 'user_id');
    }

    public function MsgNotifies()
    {
        return $this->hasMany(MsgNotify::class, 'user_id');
    }

    public function subscribes()
    {
        return $this->belongsToMany(Place::class, 'place_subscribe', 'sub_user_id', 'place_id')->withPivot(['created_at']);
    }

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'nick_name', 'mobile', 'gender', 'avatar_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Eloquent model method
    }

    /**
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function PlaceSubscribe()
    {
        return $this->hasMany(PlaceSubscribe::class, 'sub_user_id');
    }

    public function blocks()
    {
        return $this->hasMany(PlaceBlock::class, 'user_id');
    }

    public function blockUsers()
    {
        return $this->hasMany(UserBlock::class, 'user_id');
    }
}
