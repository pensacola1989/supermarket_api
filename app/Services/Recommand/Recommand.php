<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 11/3/17
 * Time: 3:01 PM
 */

namespace App\Services\Recommand;


use App\Services\Core\EntityBase;
use App\Services\Place\Place;

class Recommand extends EntityBase
{

    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'recommands';
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
        'recommand_place_id',
        'recommand_order'
    ];

    public function Place()
    {
        return $this->hasOne(Place::class, 'id', 'recommand_place_id');
    }
}