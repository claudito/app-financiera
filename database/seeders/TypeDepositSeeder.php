<?php

namespace Database\Seeders;

use App\Models\TypeDeposit;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TypeDepositSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TypeDeposit::create([
            'name'=>'Depósito'
        ]);

        TypeDeposit::create([
            'name'=>'Retiro'
        ]);

        TypeDeposit::create([
            'name'=>'Transferencia'
        ]);
    }
}
