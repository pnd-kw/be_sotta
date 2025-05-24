<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Gallery extends Model
{
    use SoftDeletes;
    
    protected $table = 'gallery';

    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = ['name', 'published', 'imageUrl', 'alt', 'caption', 'tags', 'mimeType', 'size', 'createdBy', 'updatedBy'];

    protected $casts = [
        'tags' => 'array',
        'published' => 'boolean',
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::uuid();
            }
        });
    }

}
