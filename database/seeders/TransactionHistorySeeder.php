<?php

namespace Database\Seeders;

use App\Models\TransactionHistory;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TransactionHistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        TransactionHistory::create([
            'account_id' => 1,
            'type_deposit_id'=>1,
            'amount' => 5000,
            'transaction_date' => Carbon::now()
        ]);

        TransactionHistory::create([
            'account_id' => 2,
            'type_deposit_id'=>1,
            'amount' => 200,
            'transaction_date' => Carbon::now()
        ]);
    }
}
