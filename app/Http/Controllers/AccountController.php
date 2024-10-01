<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\TransactionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    //Listar Cuentas
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
            ->join('account_types', function ($join) {
                $join->on('accounts.account_type_id', '=', 'account_types.id');
            })
            ->get()
            ->map(function ($item) {
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

    //Deposito
    function depositar($id, Request $request)
    {
        try {
            $request->validate([
                'monto' => ['required', 'numeric', 'gt:0'],
            ]);

            $account = Account::where('id', $id)->first();
            if (!$account) {
                return [
                    'error' => 0,
                    'message' => 'La cuenta no Existe!'
                ];
            }

            TransactionHistory::create([
                'amount' => $request->monto,
                'account_id' => $id,
                'type_deposit_id' => 1,
                'transaction_date' => Carbon::now()
            ]);
            return [
                'error' => 0,
                'message' => 'Transacción Exitosa!'
            ];
        } catch (\Exception $e) {
            return [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }
    }
}
