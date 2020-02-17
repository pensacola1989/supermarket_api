<?php

namespace App\Services\NotifyRegister;

use App\Services\Account\User;
use App\Services\Core\EntityBase;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/9/17
 * Time: 12:17 AM
 */
class NotifyRegister extends EntityBase
{
    protected $table = 'notify_registers';

    protected $fillable = ['user_id'];

    public function User()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}