<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;

/**
 * Contact Model
 *
 * Stores messages submitted via the public contact form on the hotel website.
 * Admins can read and respond to these from the admin message inbox.
 *
 * @property int    $id
 * @property string $name
 * @property string $email
 * @property string $phone
 * @property string $message
 */
class Contact extends Model
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'message',
    ];
}
