<?php

namespace App\Services\Attachments;

use App\Services\Core\EntityBase;
use App\Services\Post\Post;

/**
 * Created by PhpStorm.
 * User: danielwu
 * Date: 4/14/17
 * Time: 12:20 AM
 */
class Attachment extends EntityBase
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
    protected $table = 'attachments';
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
        'attach_type', 'attach_file_name', 'type', 'external_id', 'width', 'height', 'user_id',
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    // public function getAttachFileNameAttribute($value)
    // {
    //     return 'http://ehe1989' . '.' . env('OSS_END_POINT') . '/' . $this->attributes['attach_file_name'];
    // }

    public function Post()
    {
        return $this->belongsToMany(Post::class, 'post_photos', 'photo_id', 'post_id');

    }
}
