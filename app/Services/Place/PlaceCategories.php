<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 9:57 PM
 */

namespace App\Services\Place;

use App\Services\Core\EntityBase;
use Illuminate\Database\Eloquent\SoftDeletes;


class PlaceCategories extends EntityBase
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
    protected $table = 'place_categories';
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
        'name', 'description'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function Places()
    {
        return $this->hasMany(Place::class, 'category_id');
    }
}