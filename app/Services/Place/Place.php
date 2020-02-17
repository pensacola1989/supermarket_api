<?php

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityBase;
use App\Services\HistoryPlace\HistoryPlace;
use App\Services\Post\Post;
use App\Services\Recommand\Recommand;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Account\User;
use App\Services\Tag\Tag;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:53 PM
 */
class Place extends EntityBase
{
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
    protected $table = 'places';
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
    protected $dates = ['deleted_at'];
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id', 'lat', 'lng', 'geo_hash', 'name', 'external_id', 'avatar_id', 'cover_id', 'desc', 'is_private', 'fans_count', 'mp_id', 'admin_id', 'configs'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function Category()
    {
        return $this->belongsTo(PlaceCategories::class, 'category_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'place_id');
    }

    public function avatar()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo(Attachment::class, 'cover_id');
    }

    public function histories()
    {
        return $this->hasMany(HistoryPlace::class, 'place_id');
    }

    public function subscribes()
    {
        return $this->hasMany(PlaceSubscribe::class, 'place_id');
    }

    public function subscribeUsers()
    {
        return $this->belongsToMany(User::class, 'place_subscribe', 'place_id', 'sub_user_id');
    }

    public function recommand()
    {
        return $this->hasOne(Recommand::class, 'recommand_place_id');
    }

    public function blocks()
    {
        return $this->hasMany(PlaceBlock::class, 'place_id');
    }

    public function userApply()
    {
        return $this->hasMany(PlaceSubApply::class, 'place_id');
    }

    public function tags()
    {
        return $this->hasMany(Tag::class, 'created_by_place_id')->where('visible', 1);
    }

    public function getConfigsAttribute()
    {
        return json_decode($this->attributes['configs']);
    }

    public function getCanAnonymousAttribute()
    {
    }

    public function getCanPostAttribute()
    {
    }

    public function getCanReplyAttribute()
    {
    }

    public function setConfigsAttribute($value)
    {
        $this->attributes['configs'] = json_encode($value);
    }
}
