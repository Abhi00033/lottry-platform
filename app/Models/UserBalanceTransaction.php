<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserBalanceTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'amount',
        'balance_after',
        'remarks',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class, 'transaction_id');
    }

    public function getTransactionNumberAttribute()
    {
        $prefix = strtoupper(substr($this->user->username, 0, 4));
        $random = strtoupper(substr(md5($this->id), 0, 4));
        $paddedId = str_pad($this->id, 5, '0', STR_PAD_LEFT);

        return "{$prefix}-{$random}-{$paddedId}";
    }
}
