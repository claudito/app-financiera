<?php

namespace Database\Seeders;

use App\Models\CommissionType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommissionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        CommissionType::create([
            'name' =>'Comisión por Retiro',
            'fee' => 2
        ]);

        CommissionType::create([
            'name' =>'Comisión por Transferencia',
            'fee' => 1
        ]);
    }
}
