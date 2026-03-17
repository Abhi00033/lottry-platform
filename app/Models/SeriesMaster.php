<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SeriesMaster extends Model
{
    use HasFactory;

    protected $table = 'series_master';

    protected $fillable = [
        'series_no',
        'start',
        'end',
        'rate',
    ];

    public function bets()
    {
        return $this->hasMany(Bet::class, 'series_id');
    }

    protected static function boot()
    {
        parent::boot();

        static::saving(function ($model) {
            $model->start = str_pad($model->start, 4, '0', STR_PAD_LEFT);
            $model->end   = str_pad($model->end, 4, '0', STR_PAD_LEFT);
        });
    }
}
