<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;

class WalletController extends Controller
{
    const STATUS_ENABLED = 'enabled';

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
        $api_token = explode('Token ', $request->header('Authorization'))[1];
        $wallet = Wallet::where('api_token', $api_token)->first();

        if ($wallet->status === $this::STATUS_ENABLED) {
            return $this->responseError('bad_request', [
                'data' => [
                    'message' => 'Already enabled',
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
     * disable wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }
}
