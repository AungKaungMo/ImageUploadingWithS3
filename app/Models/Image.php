<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'display_order',
        'is_priority',
        
        'thumb_url',
        'small_url',
        'medium_url',
        'large_url',

        'file_name',
        'file_type',
        'file_size',
        'file_caption'
    ];

    public function imageable()
    {
        return $this->morphTo();
    }
}
