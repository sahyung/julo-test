<?php

namespace App\Http\Controllers;

use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * init wallet
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function init(Request $request)
    {
        $rules = [
            'customer_xid' => 'required|string|size:36',
        ];

        $validator = Validator::make($request->only(['customer_xid']), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();

            return $this->responseError('validation', [
                'data' => [
                    'error' => $messages,
                ],
            ]);
        }

        $cid = $request->customer_xid;
        $token = hash('sha256', $cid);

        Wallet::firstOrCreate([
            'api_token' => $token,
            'owned_by' => $cid,
        ]);

        return $this->responseSuccess('store_data', [
            'data' => [
                "token" => $token,
            ],
        ]);
    }
}
