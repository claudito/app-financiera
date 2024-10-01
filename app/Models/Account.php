<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Account extends Model
{
    use HasFactory;

    public function canTitular()
    {
        return User::selectRaw("
            name nombre,
            address direccion
        ")
            ->where('id', $this->user_id)
            ->get();
    }

    public function canbalance()
    {
        return TransactionHistory::where('transaction_histories.account_id', $this->id)->get()->sum('amount');
    }

    public function canHistory()
    {
        return TransactionHistory::selectRaw("
                type_deposits.name tipo,
                transaction_histories.amount monto,
                transaction_histories.transaction_date fecha,
                transaction_histories.fee comision,
                transaction_histories.account_id_reference cuenta_de_referencia
        ")
            ->join('type_deposits', function ($join) {
                $join->on('transaction_histories.type_deposit_id', '=', 'type_deposits.id');
            })
            ->where('transaction_histories.account_id', $this->id)->get();
    }
}
