<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:51 AM
 */

namespace App\Services\Order;

use MyHelper;
use App\Services\Account\User;
use App\Services\Core\EntityBase;
use App\Services\Order\OrderItem\OrderItem;
use App\Services\Place\Place;
use App\Services\Post\Post;
use League\Fractal\Resource\Item;

class Order extends EntityBase
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
    protected $table = 'orders';
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
        'custom_id',
        'order_sn',
        'order_amount',
        'order_status',
        'pay_screenshot_id',
        'store_id',
        'remark',
        'mobile'
    ];


    protected $casts = [
        'order_amount' => 'float'
    ];
    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function custom()
    {
        return $this->belongsTo(User::class, 'custom_id');
    }

    public function store()
    {
        return $this->belongsTo(Place::class, 'store_id');
        // return $this->belongsTo(User::class, 'store_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }
}
