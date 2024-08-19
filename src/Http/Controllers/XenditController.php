<?php

namespace FriendsOfBotble\Xendit\Http\Controllers;

use FriendsOfBotble\Xendit\Library\Invoice;
use FriendsOfBotble\Xendit\Library\Xendit;
use Botble\Base\Http\Controllers\BaseController;
use Botble\Base\Http\Responses\BaseHttpResponse;
use Botble\Payment\Enums\PaymentStatusEnum;
use Botble\Payment\Supports\PaymentHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Throwable;

class XenditController extends BaseController
{
    public function getCallback(Request $request, BaseHttpResponse $response): BaseHttpResponse
    {
        $orderIds = (array)$request->input('order_ids');

        $checkoutToken = $request->input('checkout_token');

        try {
            Xendit::setApiKey(get_payment_setting('api_key', XENDIT_PAYMENT_METHOD_NAME));

            $data = Invoice::retrieve(session('xendit_invoice_id'));

            session()->forget('xendit_invoice_id');

            $nextUrl = PaymentHelper::getRedirectURL($checkoutToken);

            if (is_plugin_active('job-board')) {
                $nextUrl = PaymentHelper::getRedirectURL(Str::of($checkoutToken)->afterLast('-')) . '?charge_id=' . $data['id'];
            }

            if ($data['external_id'] == $checkoutToken) {
                do_action(PAYMENT_ACTION_PAYMENT_PROCESSED, [
                    'amount' => $data['paid_amount'],
                    'currency' => $data['currency'],
                    'charge_id' => $data['id'],
                    'payment_channel' => XENDIT_PAYMENT_METHOD_NAME,
                    'status' => PaymentStatusEnum::COMPLETED,
                    'customer_id' => $request->input('customer_id'),
                    'customer_type' => $request->input('customer_type'),
                    'payment_type' => 'direct',
                    'order_id' => $orderIds,
                ], $request);

                return $response
                    ->setNextUrl($nextUrl)
                    ->setMessage(__('Checkout successfully!'));
            }

            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($checkoutToken))
                ->setMessage($data['message'] ?? __('Payment failed!'));
        } catch (Throwable $exception) {
            return $response
                ->setError()
                ->setNextUrl(PaymentHelper::getCancelURL($checkoutToken))
                ->setMessage($exception->getMessage());
        }
    }

    public function getCancel(BaseHttpResponse $response): BaseHttpResponse
    {
        return $response
            ->setError()
            ->setNextUrl(PaymentHelper::getCancelURL())
            ->setMessage(__('Payment failed!'));
    }
}
