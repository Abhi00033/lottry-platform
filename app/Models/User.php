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
        'password' => 'hashed',
        'balance' => 'decimal:2',
    ];


    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relationship to get the person who created this user
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    // Relationship to get all users created by this user
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


    // Lottery relations (already existing)
    public function bets()
    {
        return $this->hasMany(Bet::class);
    }

    public function advanceDraws()
    {
        return $this->hasMany(AdvanceDraw::class);
    }

    // Add this to your User model
    public function getStatsAttribute()
    {
        // We use a small cache or static variable so it doesn't re-run if called twice
        return [
            'total_play' => $this->bets()->sum('total_amount'),
            'total_win'  => $this->bets()->where('status', 'won')->get()->sum(function ($bet) {
                return $bet->points * 90;
            }),
        ];
    }

    //  Scope to eager load stats in a query efficiently
    public function scopeWithOversightStats($query)
    {
        return $query->withCount([
            'bets as total_play' => function ($q) {
                $q->select(DB::raw('SUM(total_amount)'));
            },
            'bets as total_win_points' => function ($q) {
                $q->where('status', 'won')
                    ->select(DB::raw('SUM(points)'));
            }
        ]);
    }

    public function getHouseProfitAttribute()
    {
        $winAmount = ($this->total_win_points ?? 0) * 90;
        return ($this->total_play ?? 0) - $winAmount;
    }
}
