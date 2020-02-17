<?php

namespace App\Services\Post;

use App\Services\Account\User;
use App\Services\Attachments\Attachment;
use App\Services\Comment\Comment;
use App\Services\Core\EntityBase;
use App\Services\Place\Place;
use App\Services\Post\Like;
use App\Services\Tag\Tag;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\PageWithHaving\PaginationWithHavings;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:38 PM
 */
class Post extends EntityBase
{
    protected $with = ['User', 'User.Avatar', 'User.Logins', 'Photos'];
    use SoftDeletes;
    use PaginationWithHavings;
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'posts';
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
        'title',
        'content',
        'lat',
        'lng',
        'geo_hash',
        'show_location',
        'is_verify',
        'is_anonymous',
        'top',
        'lat',
        'lng',
        'mp_link',
        'can_reply',
        'can_anonymous',
        'is_default_comment_visible',
        'location_name',
        'article_unread'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];


    public function scopeOfAdmin($query, $adminId)
    {
        // return $query->where('admin_id', $adminId);
        return $query->whereHas('Place', function ($q) use ($adminId) {
            $q->where('admin_id', $adminId);
        });
    }

    public function scopeOfAritcleUnRead($query)
    {
        return $query->where('article_unread', 1)->whereNotNull('mp_link');
    }

    /**
     * user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function Place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    /**
     * likes
     * @return \Illuminate\Database\Eloquent\Relations\HasMany likes
     */
    public function Likes()
    {
        return $this->hasMany(Like::class, 'post_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Comments()
    {
        return $this->hasMany(Comment::class, 'post_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Photos()
    {
        return $this->belongsToMany(Attachment::class, 'post_photos', 'post_id', 'photo_id');
    }

    public function Tags()
    {
        return $this->belongsToMany(Tag::class, 'post_tags', 'post_id', 'tag_id')->where('visible', 1);
    }

    public function getIsMpLinkAttribute()
    {
        return $this->attributes['mp_link'] !== null;
    }
}
