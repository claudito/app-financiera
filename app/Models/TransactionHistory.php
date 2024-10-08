<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'amount',
        'account_id',
        'type_deposit_id',
        'transaction_date',
        'fee',
        'amount_before_fee',
        'account_id_reference',
    ];

    protected $casts = [
        'transaction_date' => 'datetime:d/m/Y H:i:s'
    ];
}
