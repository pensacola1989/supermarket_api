<?php

namespace App\Services\HistoryPlace;

use App\Services\Account\User;
use App\Services\Core\EntityBase;
use App\Services\Place\Place;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 7/14/17
 * Time: 5:35 PM
 */
class HistoryPlace extends EntityBase
{
    protected $table = 'history_views';

    /**
     * Not stored
     * @var array
     */
    protected $guarded = [];

    public function Users()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function Places()
    {
        return $this->belongsTo(Place::class, 'place_id');
    }
}