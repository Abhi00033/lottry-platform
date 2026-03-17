<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdvanceDraw extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'bet_id',
        'draw_time',
    ];

    protected $casts = [
        'draw_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bet()
    {
        return $this->belongsTo(Bet::class);
    }
}
