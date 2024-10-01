<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionHistory extends Model
{
    use HasFactory;


    protected $casts = [
        'transaction_date' => 'datetime:d/m/Y H:i:s'
    ];

}
