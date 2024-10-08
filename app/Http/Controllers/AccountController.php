<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountType;
use App\Models\CommissionType;
use App\Models\TransactionHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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


    //Listar 1 Sola Cuenta
    function info($id)
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
            ->where('accounts.id', $id)
            ->get()
            ->map(function ($item) {
                $item->titularCuenta = $item->canTitular();
                $item->historialTransacciones  = $item->canHistory();
                $item->saldo = $item->canBalance();
                return $item->makeHidden(['user_id']);
            });
        return [
            'error' => 0,
            'data' => $result->first()
        ];
    }

    //Deposito
    function depositar($id, Request $request)
    {
        try {
            DB::beginTransaction();
            //Validar Request
            $request->validate([
                'monto' => ['required', 'numeric', 'gt:0'],
            ]);

            //Validar Si existe Cuenta
            $account = Account::where('id', $id)->first();
            if (!$account) {
                DB::commit();
                return [
                    'error' => 0,
                    'message' => 'La cuenta no Existe!'
                ];
            }

            //Registrar Transacción
            TransactionHistory::create([
                'amount' => $request->monto,
                'account_id' => $id,
                'type_deposit_id' => 1,
                'transaction_date' => Carbon::now()
            ]);
            DB::commit();
            return [
                'error' => 0,
                'message' => 'Transacción Exitosa!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }
    }

    //Retirar
    function retirar($id, Request $request)
    {
        try {
            DB::beginTransaction();
            //Validar Request
            $request->validate([
                'monto' => ['required', 'numeric', 'gt:0'],
            ]);

            //Validar Si Existe Cuenta
            $account = Account::where('id', $id)->first();
            if (!$account) {
                DB::commit();
                return [
                    'error' => 0,
                    'message' => 'La cuenta no Existe!'
                ];
            }

            //Validar Tipo de Comisión 
            $validateCommission = $this->validateCommission($account, $request);

            if ($validateCommission['error'] == 1) {
                DB::commit();
                return $validateCommission;
            }

            //dd( $validateCommission['fee'] );

            //Registrar Transacción
            TransactionHistory::create([
                'amount' => -$validateCommission['amount_fee'],
                'account_id' => $id,
                'fee' => $validateCommission['fee'],
                'amount_before_fee' => $request->monto,
                'type_deposit_id' => 2,
                'transaction_date' => Carbon::now()
            ]);
            DB::commit();
            return [
                'error' => 0,
                'message' => 'Transacción Exitosa!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }
    }


    //Transferir
    function transferir($id, Request $request)
    {
        try {
            DB::beginTransaction();
            //Validar Request
            $request->validate([
                'monto' => ['required', 'numeric', 'gt:0'],
                'cuenta_destino' => ['required', 'exists:accounts,id'],
            ]);

            //Validar Si Existe Cuenta
            $account = Account::where('id', $id)->first();
            if (!$account) {
                DB::commit();
                return [
                    'error' => 0,
                    'message' => 'La cuenta no Existe!'
                ];
            }

            //Validar Tipo de Comisión 
            $validateCommission = $this->validateTransaction($account, $request);

            if ($validateCommission['error'] == 1) {
                DB::commit();
                return $validateCommission;
            }

            //Registrar Transacción

            //Cuenta Remitente
            TransactionHistory::create([
                'amount' => -$validateCommission['amount_fee'],
                'account_id' => $id,
                'fee' => $validateCommission['fee'],
                'amount_before_fee' => $request->monto,
                'type_deposit_id' => 3,
                'account_id_reference' => $request->cuenta_destino,
                'transaction_date' => Carbon::now()

            ]);

            //Cuenta Destino
            TransactionHistory::create([
                'amount' => $validateCommission['amount_fee'],
                'account_id' => $request->cuenta_destino,
                'fee' => null,
                'amount_before_fee' => null,
                'type_deposit_id' => 1,
                'account_id_reference' => $id,
                'transaction_date' => Carbon::now()
            ]);

            DB::commit();
            return [
                'error' => 0,
                'message' => 'Transacción Exitosa!'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 1,
                'message' => $e->getMessage()
            ];
        }
    }

    private function validateCommission($account, $request)
    {
        $accountType = AccountType::where('id', $account->account_type_id)->first();
        $minimum_balance  = $accountType->minimum_balance;

        if ($account->account_type_id <> 1) { //Cuenta Premium
            $commsionType = CommissionType::where('id', 1)->first();
            $fee = $commsionType->fee;

            $retiro = $request->monto * (($fee / 100)  + 1);
            $saldo  = $account->canBalance();

            //Validar saldo Disponible
            if ((float)$retiro <= (float)$saldo) {
                return [
                    'error' => 0,
                    'message' => 'Autorizado!',
                    'fee' =>  $fee . '%',
                    'amount_fee' => $retiro
                ];
            } else {
                return [
                    'error' => 1,
                    'message' => 'No se puede retirar!',
                    'data' => [
                        'Monto Solicitado + Comisión' => $retiro,
                        'Saldo Actual' => $account->canBalance()
                    ]
                ];
            }
        } else { //Cuenta Estandar
            $retiro = $request->monto;
            $saldo  = $account->canBalance();

            //Validar saldo Disponible
            if ((float)$retiro <= (float)$saldo) {
                $saldo_after_retiro = (float)$saldo - (float)$retiro;

                //Validar Saldo Minimo Luego de Retiro:
                if ((float)$saldo_after_retiro <= (float)$minimum_balance) {
                    return [
                        'error' => 1,
                        'message' => 'No se puede retirar: Retiro Excede Monto Mínimo de la cuenta',
                        'data' => [
                            'tipoCuenta' => $accountType->name,
                            'Monto Minimo Cuenta' => $minimum_balance,
                            'Monto Solicitado' => $retiro,
                            'Saldo Actual' => $account->canBalance()
                        ]
                    ];
                }

                return [
                    'error' => 0,
                    'message' => 'Autorizado!',
                    'fee' => null,
                    'amount_fee' => null
                ];
            } else {
                return [
                    'error' => 1,
                    'message' => 'No se puede retirar: Monto Excede Saldo Disponible',
                    'data' => [
                        'tipoCuenta' => $accountType->name,
                        'Monto Solicitado' => $retiro,
                        'Saldo Actual' => $account->canBalance()
                    ]
                ];
            }
        }
    }


    private function validateTransaction($account, $request)
    {
        $accountType = AccountType::where('id', $account->account_type_id)->first();
        $minimum_balance  = $accountType->minimum_balance;

        if ($account->account_type_id <> 1) { //Cuenta Premium
            $commsionType = CommissionType::where('id', 2)->first();
            $fee = $commsionType->fee;

            $retiro = $request->monto * (($fee / 100)  + 1);
            $saldo  = $account->canBalance();

            //Validar saldo Disponible
            if ((float)$retiro <= (float)$saldo) {
                return [
                    'error' => 0,
                    'message' => 'Autorizado!',
                    'fee' =>  $fee . '%',
                    'amount_fee' => $retiro
                ];
            } else {
                return [
                    'error' => 1,
                    'message' => 'No se puede retirar!',
                    'data' => [
                        'Monto Solicitado + Comisión' => $retiro,
                        'Saldo Actual' => $account->canBalance()
                    ]
                ];
            }
        } else { //Cuenta Estandar
            $retiro = $request->monto;
            $saldo  = $account->canBalance();

            //Validar saldo Disponible
            if ((float)$retiro <= (float)$saldo) {
                $saldo_after_retiro = (float)$saldo - (float)$retiro;
                //Validar Saldo Minimo Luego de Retiro:
                if ((float)$saldo_after_retiro <= (float)$minimum_balance) {

                    return [
                        'error' => 1,
                        'message' => 'No se puede retirar: Retiro Excede Monto Mínimo de la cuenta',
                        'data' => [
                            'tipoCuenta' => $accountType->name,
                            'Monto Minimo Cuenta' => $minimum_balance,
                            'Monto Solicitado' => $retiro,
                            'Saldo Actual' => $account->canBalance()
                        ]
                    ];
                }

                return [
                    'error' => 0,
                    'message' => 'Autorizado!',
                    'fee' =>  null,
                    'amount_fee' => $request->monto
                ];
            } else {
                return [
                    'error' => 1,
                    'message' => 'No se puede Transferir: Monto Excede Saldo Disponible de la cuenta Origen',
                    'data' => [
                        'tipoCuenta' => $accountType->name,
                        'Monto Solicitado' => $retiro,
                        'Saldo Actual' => $account->canBalance()
                    ]
                ];
            }
        }
    }
}
