<?php

use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'FriendsOfBotble\Xendit\Http\Controllers', 'middleware' => ['core', 'web']], function () {
    Route::get('xendit/payment/callback', [
        'as'   => 'xendit.payment.callback',
        'uses' => 'XenditController@getCallback',
    ]);

    Route::get('xendit/payment/cancel', [
        'as'   => 'xendit.payment.cancel',
        'uses' => 'XenditController@getCancel',
    ]);
});
