<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SeriesMaster;
use Illuminate\Support\Facades\DB;

class SeriesMasterSeeder extends Seeder
{
    public function run(): void
    {
        $series = [
            ['series_no' => '0000', 'start' => 0,    'end' => 999,  'rate' => 180.00],
            ['series_no' => '1000', 'start' => 1000, 'end' => 1999, 'rate' => 180.00],
            ['series_no' => '2000', 'start' => 2000, 'end' => 2999, 'rate' => 360.00],
            ['series_no' => '3000', 'start' => 3000, 'end' => 3999, 'rate' => 540.00],
            ['series_no' => '4000', 'start' => 4000, 'end' => 4999, 'rate' => 900.00],
            ['series_no' => '5000', 'start' => 5000, 'end' => 5999, 'rate' => 900.00],
            ['series_no' => '6000', 'start' => 6000, 'end' => 6999, 'rate' => 1800.00],
            ['series_no' => '7000', 'start' => 7000, 'end' => 7999, 'rate' => 3600.00],
            ['series_no' => '8000', 'start' => 8000, 'end' => 8999, 'rate' => 4500.00],
            ['series_no' => '9000', 'start' => 9000, 'end' => 9999, 'rate' => 4500.00],
        ];

        foreach ($series as $data) {
            SeriesMaster::updateOrCreate(
                ['series_no' => $data['series_no']],
                [
                    'start' => $data['start'],
                    'end' => $data['end'],
                    'rate' => $data['rate'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}
