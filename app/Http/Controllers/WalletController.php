<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    const STATUS_ENABLED = 'enabled';
    const STATUS_DISABLED = 'disabled';

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
