<?php

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 10:01 PM
 */

namespace App\Services\Account\Block;

use App\Services\Account\User;
use App\Services\Core\EntityBase;

class UserBlock extends EntityBase
{
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'user_blocks';
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
        'user_id', 'block_user_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function blockedUser()
    {
        return $this->belongsTo(User::class, 'block_user_id');
    }
}
