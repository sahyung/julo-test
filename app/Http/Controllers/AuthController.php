<?php

namespace App\Http\Controllers;

use App\Models\ApiClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function init(Request $request)
    {
        $rules = [
            'customer_xid' => 'required|string|size:36',
        ];

        $validator = Validator::make($request->all(), $rules);
        if ($validator->fails()) {
            $messages = $validator->messages();
            $errors = $messages->all();

            return $this->responseError('validation', [
                'data' => [
                    'errors' => $errors,
                ],
            ]);
        }

        $cid = $request->customer_xid;
        $token = hash('sha256', $cid);

        ApiClient::firstOrCreate([
            'api_token' => $token,
        ]);

        return $this->responseSuccess('store_data', [
            'data' => [
                "token" => $token,
            ],
        ]);
    }
}
