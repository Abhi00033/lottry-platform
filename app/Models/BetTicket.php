<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BetTicket extends Model
{
    protected $fillable = [
        'ticket_no',
        'user_id',
        'transaction_id',
        'draw_date',
        'draw_time',
        'ticket_price',
        'total_qty',
        'total_amount',
        'status',
        'printed_at',
    ];

    protected $casts = [
        'draw_date' => 'date',
        'draw_time' => 'datetime:H:i:s',
        'printed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function transaction()
    {
        return $this->belongsTo(UserBalanceTransaction::class, 'transaction_id');
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'ticket_id')
            ->orderBy('id');
    }
}
