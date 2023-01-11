<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';

    /**
     * get wallet by api_token
     *
     * @return \App\Models\Wallet || QueryException
     */
    private function getWallet($request)
    {
        $api_token = explode('Token ', $request->header('Authorization'))[1];

        try {
            return Wallet::where('api_token', $api_token)->first();
        } catch (QueryException $exception) {
            return $exception;
        }
    }

    /**
     * enable wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        $wallet->makeHidden([
            'disabled_at',
        ]);

        if ($wallet->status === $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Already enabled',
                ],
            ]);
        }

        try {
            $wallet->update([
                'status' => $this::STATUS_ENABLED,
                'enabled_at' => now(),
            ]);

            return $this->responseSuccess('store_data', [
                'data' => [
                    'wallet' => $wallet,
                ],
            ]);
        } catch (QueryException $exception) {
            $errorInfo = $exception->errorInfo;

            return $this->responseError('default', [
                'message' => $errorInfo,
            ]);
        }
    }

    /**
     * view my wallet balance
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function view(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        if ($wallet->status !== $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Wallet disabled',
                ],
            ]);
        }

        return $this->responseSuccess('default', [
            'data' => [
                'wallet' => $wallet,
            ],
        ]);
    }

    /**
     * Add virtual money to my wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function deposit(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        if ($wallet->status !== $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Wallet disabled',
                ],
            ]);
        }

        $rules = [
            'amount' => 'required|numeric|min:0|not_in:0',
            'reference_id' => 'required|string|size:36|unique:transactions,reference_id',
        ];

        $validator = Validator::make($request->only([
            'amount',
            'reference_id',
        ]), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();

            return $this->responseError('validation', [
                'data' => [
                    'error' => $messages,
                ],
            ]);
        }

        $newTrx = [
            'owned_by' => $wallet->owned_by,
            'type' => $this::TYPE_DEPOSIT,
            'status' => $this::STATUS_FAILED,
            'amount' => $request->amount,
            'reference_id' => $request->reference_id,
        ];

        $wallet->balance += $request->amount;

        // Rollback all data if one of the database transactions fails
        DB::beginTransaction();

        if ($wallet->save()) {
            $newTrx['status'] = $this::STATUS_SUCCESS;
        }

        // restore balance if fail to save transaction
        try {
            $trx = Transaction::create($newTrx)->makeHidden([
                'withdrawn_at',
                'withdrawn_by',
                'type',
                'owned_by',
            ]);

            DB::commit();
            return $this->responseSuccess('store_data', [
                'data' => [
                    'deposit' => $trx,
                ],
            ]);
        } catch (QueryException $exception) {
            DB::rollback();
            $errorInfo = $exception->errorInfo;

            return $this->responseError('default', [
                'message' => $errorInfo,
            ]);
        }
    }

    /**
     * Use virtual money from my wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function withdrawal(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        if ($wallet->status !== $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Wallet disabled',
                ],
            ]);
        }

        $rules = [
            'amount' => "required|numeric|min:0|not_in:0|max:$wallet->balance",
            'reference_id' => 'required|string|size:36|unique:transactions,reference_id',
        ];

        $validator = Validator::make($request->only([
            'amount',
            'reference_id',
        ]), $rules);

        if ($validator->fails()) {
            $messages = $validator->messages();

            return $this->responseError('validation', [
                'data' => [
                    'error' => $messages,
                ],
            ]);
        }

        $newTrx = [
            'owned_by' => $wallet->owned_by,
            'type' => $this::TYPE_WITHDRAWAL,
            'status' => $this::STATUS_FAILED,
            'amount' => $request->amount,
            'reference_id' => $request->reference_id,
        ];

        $wallet->balance -= $request->amount;

        // Rollback all data if one of the database transactions fails
        DB::beginTransaction();

        if ($wallet->save()) {
            $newTrx['status'] = $this::STATUS_SUCCESS;
        }

        // restore balance if fail to save transaction
        try {
            $trx = Transaction::create($newTrx)->makeHidden([
                'deposited_at',
                'deposited_by',
                'type',
                'owned_by',
            ]);

            DB::commit();
            return $this->responseSuccess('store_data', [
                'data' => [
                    'withdrawal' => $trx,
                ],
            ]);
        } catch (QueryException $exception) {
            DB::rollback();
            $errorInfo = $exception->errorInfo;

            return $this->responseError('default', [
                'message' => $errorInfo,
            ]);
        }
    }

    /**
     * View my wallet transactions
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function transaction(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        if ($wallet->status !== $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Wallet disabled',
                ],
            ]);
        }

        $page = $request->query('page', 1);
        $perPage = $request->query('per_page', 10);
        $offset = ($page - 1) * $perPage;

        $trxModel = DB::table('transactions AS t')->whereNull('t.deleted_at');
        $trxs = $trxModel->select(
            't.id',
            't.status',
            't.created_at AS transacted_at',
            't.type',
            't.amount',
            't.reference_id'
        )
            ->orderBy('transacted_at', 'desc');

        $totalData = $trxModel->count();
        $trxs = $trxs->offset($offset)
            ->limit($perPage)
            ->get();

        return $this->responseSuccess('default', [
            'data' => [
                'total' => $totalData,
                'page' => $page,
                'transactions' => $trxs,
            ],
        ]);
    }

    /**
     * disable wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function disable(Request $request)
    {
        $wallet = $this->getWallet($request);

        if ($wallet instanceof QueryException) {
            return $this->responseError('default', [
                'message' => $wallet->errorInfo,
            ]);
        }

        $wallet->makeHidden([
            'enabled_at',
        ]);
        if ($wallet->status === $this::STATUS_DISABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Already disabled',
                ],
            ]);
        }

        try {
            $wallet->update([
                'status' => $this::STATUS_DISABLED,
                'disabled_at' => now(),
            ]);

            return $this->responseSuccess('default', [
                'data' => [
                    'wallet' => $wallet,
                ],
            ]);
        } catch (QueryException $exception) {
            $errorInfo = $exception->errorInfo;

            return $this->responseError('default', [
                'message' => $errorInfo,
            ]);
        }

    }
}
