<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        Account::create([
            'user_id'=>1,
            'account_type_id' => 1
        ]);

        Account::create([
            'user_id'=>2,
            'account_type_id' => 1
        ]);

        Account::create([
            'user_id'=>3,
            'account_type_id' => 2
        ]);
    }
}
