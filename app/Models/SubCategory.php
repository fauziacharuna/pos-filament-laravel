<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubCategory extends Model
{
    protected $fillable = [
        'name',
        'category_id',
        'description',
        'logo',
        'is_active',
    ];
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    //
}
