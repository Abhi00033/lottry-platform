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

        // Claim
        'claim_status',
        'claim_amount',
        'claimed_at',
        'claimed_by',
    ];

    protected $casts = [
        'draw_date'    => 'date',
        'draw_time'    => 'datetime:H:i:s',
        'printed_at'   => 'datetime',
        'claimed_at'   => 'datetime',
        'claim_amount' => 'decimal:2',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

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

    /**
     * User who claimed the ticket
     */
    public function claimedBy()
    {
        return $this->belongsTo(User::class, 'claimed_by');
    }
}
