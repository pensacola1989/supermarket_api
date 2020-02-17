<?php

namespace App\Services\Tag;

use App\Services\Core\EntityBase;
use App\Services\Place\Place;
use App\Services\Post\Post;

class Tag extends EntityBase
{
    protected $table = 'tags';

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
    protected $fillable = ['tag_name', 'visible', 'created_by_place_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function scopeOfVisible($query, $visible = null)
    {
        if ($visible) {
            return $query->where('visible', $visible);
        }
        return $query;
    }

    public function scopeOfPlace($query, $placeId)
    {
        return $query->where('created_by_place_id', $placeId);
    }

    public function posts()
    {
        return $this->belongsToMany(Post::class, 'post_tags', 'id', 'tag_id');
    }

    public function place()
    {
        return $this->belongsTo(Place::class, 'created_by_place_id');
    }
}
