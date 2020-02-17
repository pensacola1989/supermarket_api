<?php

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityBase;
use App\Services\HistoryPlace\HistoryPlace;
use App\Services\Post\Post;
use App\Services\Recommand\Recommand;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:53 PM
 */
class PlaceApply extends EntityBase
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
    protected $table = 'place_applies';
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
        'lat', 'lng', 'geo_hash', 'name', 'external_id', 'avatar_id', 'cover_id', 'desc', 'is_private', 'apply_user_id', 'mp_id', 'category_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function avatar()
    {
        return $this->belongsTo(Attachment::class, 'avatar_id');
    }

    public function cover()
    {
        return $this->belongsTo(Attachment::class, 'cover_id');
    }
}
