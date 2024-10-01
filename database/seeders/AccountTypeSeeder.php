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
            'name' => 'Cuenta Estandar'
        ]);

        AccountType::create([
            'name' => 'Cuenta Premium'
        ]);
    }
}
