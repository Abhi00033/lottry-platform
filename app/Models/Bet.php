<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'series_id',
        'series_group', // <--- ADD THIS LINE
        'number',
        'qty',
        'points',
        'transaction_id',
        'unit_price',     // <--- Added
        'total_amount',
        'draw_time',
        'status',
    ];

    protected $casts = [
        'draw_time' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function series()
    {
        return $this->belongsTo(SeriesMaster::class, 'series_id');
    }

    public function advanceDraws()
    {
        return $this->hasMany(AdvanceDraw::class);
    }
    // Relationship to the transaction ledger
    public function transaction()
    {
        return $this->belongsTo(UserBalanceTransaction::class, 'transaction_id');
    }
}
