<?php

namespace App\Services\Place;

use App\Services\Attachments\Attachment;
use App\Services\Core\EntityBase;
use App\Services\HistoryPlace\HistoryPlace;
use App\Services\Post\Post;
use App\Services\Recommand\Recommand;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Services\Account\User;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:53 PM
 */
class PlaceBlock extends EntityBase
{
    /**
     * primaryKey
     * @var string
     */
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'place_block';
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
        'place_id', 'user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];



    public function place()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
