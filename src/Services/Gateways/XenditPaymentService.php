<?php

namespace FriendsOfBotble\Xendit\Services\Gateways;

use FriendsOfBotble\Xendit\Services\Abstracts\XenditPaymentAbstract;
use Illuminate\Http\Request;

class XenditPaymentService extends XenditPaymentAbstract
{
    public function makePayment(Request $request)
    {
    }

    public function afterMakePayment(Request $request)
    {
    }

    public function supportedCurrencyCodes(): array
    {
        return [
            'USD',
            'IDR',
            'PHP',
            'SGD',
            'MYR',
        ];
    }
}
