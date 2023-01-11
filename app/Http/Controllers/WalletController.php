<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class WalletController extends Controller
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';
    const STATUS_SUCCESS = 'success';
    const STATUS_FAILED = 'failed';
    const TYPE_DEPOSIT = 'deposit';
    const TYPE_WITHDRAWAL = 'withdrawal';

    private function getWallet($request)
    {
        $api_token = explode('Token ', $request->header('Authorization'))[1];
        return Wallet::where('api_token', $api_token)->first();
    }

    /**
     * view wallet balance
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
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

        if ($wallet->status === $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Already enabled',
                ],
            ]);
        }

        $wallet->update([
            'status' => $this::STATUS_ENABLED,
            'enabled_at' => now(),
        ]);

        return $this->responseSuccess('store_data', [
            'data' => [
                'wallet' => $wallet,
            ],
        ]);
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

        if ($wallet->status !== $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Wallet disabled',
                ],
            ]);
        }

        return $this->responseSuccess('store_data', [
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
            'amount' => $request->amount,
            'reference_id' => $request->reference_id,
        ];

        $wallet->balance += $request->amount;

        if ($wallet->save()) {
            $newTrx['status'] = $this::STATUS_SUCCESS;
        } else {
            $newTrx['status'] = $this::STATUS_FAILED;
        }

        $trx = Transaction::create($newTrx)->makeHidden([
            'withdrawn_at',
            'withdrawn_by',
            'type',
            'owned_by',
        ]);

        return $this->responseSuccess('store_data', [
            'data' => [
                'deposit' => $trx,
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
        if ($wallet->status === $this::STATUS_DISABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'error' => 'Already disabled',
                ],
            ]);
        }

        $wallet->update([
            'status' => $this::STATUS_DISABLED,
        ]);

        return $this->responseSuccess('default', [
            'data' => [
                'wallet' => $wallet,
            ],
        ]);
    }
}
