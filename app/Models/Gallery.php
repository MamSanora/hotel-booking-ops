<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    //
    use HasFactory;

    // Add this inside the class
    protected $fillable = [
        'image',
    ];
}
