<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Property extends Model
{
    use HasFactory, SoftDeletes;

    // Mass assignable fields
    protected $fillable = [
        'title',
        'description',
        'price',
        'location',
        'created_by',
        'updated_by',
        'status',
    ];

    // Casts (optional)
    protected $casts = [
        'price' => 'decimal:2',
        
    ];
}
