<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/13/17
 * Time: 10:01 PM
 */

namespace App\Services\Account;

use App\Services\Core\EntityBase;

class Login extends EntityBase
{
    protected $primaryKey = 'id';
    /**
     * Table name
     * @var string
     */
    protected $table = 'logins';
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
        'external_system', 'external_id', 'avatar_url'
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
}