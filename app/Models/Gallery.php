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

    // protected $fillable = ['name', 'published', 'imageUrl', 'public_id', 'alt', 'caption', 'tags', 'mimeType', 'size', 'createdBy', 'updatedBy'];
    protected $fillable = ['name', 'published', 'images', 'thumbnailUrl', 'caption', 'tags', 'createdBy', 'updatedBy'];

    protected $casts = [
        'tags' => 'array',
        'images' => 'array',
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

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'category_gallery');
    }

}
