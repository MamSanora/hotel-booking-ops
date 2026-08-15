<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoomTypeSetting extends Model
{
    protected $fillable = [
        'room_type_id',
        'chart_color',
    ];

    public function roomType()
    {
        return $this->belongsTo(RoomType::class);
    }
}
