<?php
/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 8/17/17
 * Time: 12:51 AM
 */

namespace App\Services\Report;


use App\Services\Account\User;
use App\Services\Core\EntityBase;
use App\Services\Post\Post;

class Report extends EntityBase
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
    protected $table = 'reports';
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
    protected $fillable = [];

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
    public function User()
    {
        return $this->belongsTo(User::class, 'report_from_uid');
    }


    public function Post()
    {
        return $this->belongsTo(Post::class, 'report_post_id');
    }
}