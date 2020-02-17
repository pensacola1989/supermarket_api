<?php namespace App\Services\VerifyCode;

use App\Services\Core\EntityBase;
/**
 * Created by PhpStorm.
 * User: weiwei
 * Date: 4/14/2015
 * Time: 5:22 PM
 */

class VerifyCodes extends EntityBase {

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'verify_codes';
    /**
     * Nothing to guarded ,or to be done
     * @var array
     */
    protected $guarded = [];
}
