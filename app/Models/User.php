<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'parent_id',
        'unique_id',
        'role_id',
        'general_status_id',
        'first_name',
        'last_name',
        'username',
        'email',
        'mobile',
        'commision',
        'password',
        'balance',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password'          => 'hashed',
        'balance'           => 'decimal:2',
        'commision'         => 'decimal:2',  // ← cast so it's always a float, never a string
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(User::class, 'parent_id');
    }

    public function status()
    {
        return $this->belongsTo(GeneralStatus::class, 'general_status_id');
    }

    public function balanceTransactions()
    {
        return $this->hasMany(UserBalanceTransaction::class);
    }

    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function advanceDraws()
    {
        return $this->hasMany(AdvanceDraw::class);
    }

    // ─── Scope ───────────────────────────────────────────────────────────────

    /**
     * Eager-loads total_play and total_win_points onto each User row.
     * Uses withSum() so it's a correlated subquery — never drops users.
     */
    public function scopeWithOversightStats($query)
    {
        return $query
            ->withSum('bets as total_play', 'total_amount')
            ->withSum(['bets as total_win_points' => function ($q) {
                $q->where('status', 'won');
            }], 'points');
    }

    // ─── Computed Attributes ──────────────────────────────────────────────────

    /**
     * Returns a stats array with commission factored in.
     *
     * total_play        — sum of all bet amounts
     * total_win         — winning payout (points × 90)
     * commission_amount — retailer/agent cut (% of total_play)
     * house_profit      — what the house keeps after payouts and commission
     *
     * Uses scope-loaded values (total_play, total_win_points) when available
     * so it doesn't fire extra queries on already-loaded collections.
     */
    public function getStatsAttribute(): array
    {
        $totalPlay      = (float) ($this->total_play      ?? $this->bets()->sum('total_amount'));
        $totalWinPoints = (float) ($this->total_win_points ?? $this->bets()->where('status', 'won')->sum('points'));

        $totalWin         = $totalWinPoints * 90;
        $commissionRate   = (float) ($this->commision ?? 0);       // stored as e.g. 10 = 10%
        $commissionAmount = $totalPlay * ($commissionRate / 100);

        return [
            'total_play'        => $totalPlay,
            'total_win'         => $totalWin,
            'commission_amount' => $commissionAmount,
            'house_profit'      => $totalPlay - $totalWin - $commissionAmount,
        ];
    }

    /**
     * Shortcut for house profit directly on the model.
     * Consistent with getStatsAttribute — commission is always deducted.
     */
    public function getHouseProfitAttribute(): float
    {
        $play       = (float) ($this->total_play       ?? 0);
        $win        = (float) ($this->total_win_points ?? 0) * 90;
        $commission = $play * ((float) ($this->commision ?? 0) / 100);

        return $play - $win - $commission;
    }
}
