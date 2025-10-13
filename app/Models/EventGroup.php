<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EventGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'group',
    ];

    public function event()
    {
        return $this->belongsTo(Event::class);
    }
}
