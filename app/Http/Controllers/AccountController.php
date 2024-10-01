<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\User;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    //
    function index(Request $request)
    {
        $result =  Account::selectRaw("
                accounts.id,
                accounts.user_id,
                null saldo,
                null titularCuenta,
                account_types.name tipoCuenta,
                null historialTransacciones 
        ")
        ->join('account_types',function($join){
            $join->on('accounts.account_type_id','=','account_types.id');
        })
        ->get()
        ->map(function($item){
            $item->titularCuenta = $item->canTitular();
            $item->historialTransacciones  = $item->canHistory();
            $item->saldo = $item->canBalance();
            return $item->makeHidden(['user_id']);
        });
        return [
            'error' => 0,
            'data' => $result
        ];
    }
}
