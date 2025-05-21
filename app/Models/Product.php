<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use HasFactory;

    
    protected $fillable = [
        'name',
        'price',
        'description',
        'category_id',
        'stock',
        'status',
        'image',
    ];
    
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
}
