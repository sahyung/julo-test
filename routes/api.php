<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\WalletController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
 */

Route::group(['prefix' => '/v1'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::post('init', 'init');
    });

    Route::group(['middleware' => ['customAuth']], function () {
        Route::group(['prefix' => '/wallet'], function () {
            Route::post('/', [WalletController::class, 'store'])->name('enable_wallet');
            Route::get('/', [WalletController::class, 'view'])->name('view_wallet');
            Route::patch('/', [WalletController::class, 'disable'])->name('disable_wallet');
            Route::get('/transactions', [WalletController::class, 'transaction'])->name('transaction_wallet');
            Route::post('/deposits', [WalletController::class, 'deposit'])->name('deposit_wallet');
            Route::post('/withdrawals', [WalletController::class, 'withdrawal'])->name('withdrawal_wallet');
        });
    });
});
