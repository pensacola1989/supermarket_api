<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 11/9/17
 * Time: 2:33 PM
 */

namespace App\Services\Place;


use App\Services\Core\EntityBase;

class PlaceSubscribe extends EntityBase
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
    protected $table = 'place_subscribe';
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
        'place_id', 'sub_user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];
}