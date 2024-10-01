<?php

namespace Database\Seeders;

use App\Models\AccountType;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        AccountType::create([
            'name' => 'Cuenta Estandar',
            'minimum_balance'=>100
        ]);

        AccountType::create([
            'name' => 'Cuenta Premium',
            'minimum_balance'=>null
        ]);
    }
}
