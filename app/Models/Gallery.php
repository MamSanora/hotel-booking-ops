<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Gallery Model
 *
 * Stores hotel gallery images managed by admins and displayed on the
 * public-facing hotel website.
 *
 * @property int    $id
 * @property string $image  Relative path or filename of the uploaded image
 */
class Gallery extends Model
{
    use HasFactory;

    protected $fillable = ['image'];
}
