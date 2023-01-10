<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    public function init(Request $request)
    {
        $request->validate([
            'customer_xid' => 'required|string|size:36',
        ]);

        $cid = $request->customer_xid;
        $token = hash('sha256', $cid);
        DB::table('api_clients')->insert([
            'api_token' => $token,
        ]);

        return response()->json([
            'status' => 'success',
            "data" => [
                "token" => $token,
            ],
        ]);

    }
}
