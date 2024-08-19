<?php

namespace FriendsOfBotble\Xendit\Providers;

use FriendsOfBotble\Xendit\Library\Invoice;
use FriendsOfBotble\Xendit\Library\Xendit;
use FriendsOfBotble\Xendit\Services\Gateways\XenditPaymentService;
use Botble\Ecommerce\Models\Currency as CurrencyEcommerce;
use Botble\JobBoard\Models\Currency as CurrencyJobBoard;
use Botble\Payment\Enums\PaymentMethodEnum;
use Html;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Throwable;

class HookServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        add_filter(PAYMENT_FILTER_ADDITIONAL_PAYMENT_METHODS, [$this, 'registerXenditMethod'], 19, 2);

        $this->app->booted(function () {
            add_filter(PAYMENT_FILTER_AFTER_POST_CHECKOUT, [$this, 'checkoutWithXendit'], 19, 2);
        });

        add_filter(PAYMENT_METHODS_SETTINGS_PAGE, [$this, 'addPaymentSettings'], 93);

        add_filter(BASE_FILTER_ENUM_ARRAY, function ($values, $class) {
            if ($class == PaymentMethodEnum::class) {
                $values['XENDIT'] = XENDIT_PAYMENT_METHOD_NAME;
            }

            return $values;
        }, 32, 2);

        add_filter(BASE_FILTER_ENUM_LABEL, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == XENDIT_PAYMENT_METHOD_NAME) {
                $value = 'Xendit';
            }

            return $value;
        }, 32, 2);

        add_filter(BASE_FILTER_ENUM_HTML, function ($value, $class) {
            if ($class == PaymentMethodEnum::class && $value == XENDIT_PAYMENT_METHOD_NAME) {
                $value = Html::tag(
                    'span',
                    PaymentMethodEnum::getLabel($value),
                    ['class' => 'label-success status-label']
                )
                    ->toHtml();
            }

            return $value;
        }, 32, 2);

        add_filter(PAYMENT_FILTER_GET_SERVICE_CLASS, function ($data, $value) {
            if ($value == XENDIT_PAYMENT_METHOD_NAME) {
                $data = XenditPaymentService::class;
            }

            return $data;
        }, 32, 2);

        add_filter(PAYMENT_FILTER_PAYMENT_INFO_DETAIL, function ($data, $payment) {
            if ($payment->payment_channel == XENDIT_PAYMENT_METHOD_NAME) {
                $paymentService = (new XenditPaymentService());
                $paymentDetail = $paymentService->getPaymentDetails($payment);
                if ($paymentDetail) {
                    $data = view(
                        'plugins/xendit::detail',
                        ['payment' => $paymentDetail, 'paymentModel' => $payment]
                    )->render();
                }
            }

            return $data;
        }, 32, 2);

        add_filter('ecommerce_checkout_footer', function ($html) {
            if (! in_array(Route::currentRouteName(), ['public.checkout.information', 'public.checkout.recover'])) {
                return $html;
            }

            return $html . view('plugins/xendit::assets')->render();
        }, 32);
    }

    public function addPaymentSettings(?string $settings): string
    {
        return $settings . view('plugins/xendit::settings')->render();
    }

    public function registerXenditMethod(?string $html, array $data): string
    {
        return $html . view('plugins/xendit::methods', $data)->render();
    }

    public function checkoutWithXendit(array $data, Request $request): array
    {
        if ($data['type'] !== XENDIT_PAYMENT_METHOD_NAME) {
            return $data;
        }

        $supportedCurrencies = (new XenditPaymentService())->supportedCurrencyCodes();

        if (! in_array($data['currency'], $supportedCurrencies)) {
            $data['error'] = true;
            $data['message'] = __(
                ":name doesn't support :currency. List of currencies supported by :name: :currencies.",
                [
                    'name' => 'Xendit',
                    'currency' => $data['currency'],
                    'currencies' => implode(', ', $supportedCurrencies),
                ]
            );

            return $data;
        }

        $currentCurrency = get_application_currency();

        $paymentData = apply_filters(PAYMENT_FILTER_PAYMENT_DATA, [], $request);

        if (strtoupper($currentCurrency->title) !== 'IDR') {
            $currency = is_plugin_active('ecommerce') ? CurrencyEcommerce::class : CurrencyJobBoard::class;
            $supportedCurrency = $currency::query()->where('title', 'IDR')->first();

            if ($supportedCurrency) {
                $paymentData['currency'] = strtoupper($supportedCurrency->title);
                if ($currentCurrency->is_default) {
                    $paymentData['amount'] = $paymentData['amount'] * $supportedCurrency->exchange_rate;
                } else {
                    $paymentData['amount'] = format_price(
                        $paymentData['amount'] / $currentCurrency->exchange_rate,
                        $currentCurrency,
                        true
                    );
                }
            }
        }

        $orderIds = $paymentData['order_id'];

        try {
            Xendit::setApiKey(get_payment_setting('api_key', XENDIT_PAYMENT_METHOD_NAME));

            $checkoutToken = $paymentData['checkout_token'];

            if (is_plugin_active('job-board')) {
                $checkoutToken =  Str::uuid() . '-' . $checkoutToken;
            }

            $params = [
                'external_id' => $checkoutToken,
                'amount' => $paymentData['amount'],
                'currency' => $paymentData['currency'],
                'payer_email' => $paymentData['address']['email'],
                'description' => $paymentData['description'],
                'success_redirect_url' => route('xendit.payment.callback', [
                    'checkout_token' => $checkoutToken,
                    'order_ids' => $orderIds,
                    'customer_id' => $paymentData['customer_id'],
                    'customer_type' => $paymentData['customer_type'],
                ]),
                'failure_redirect_url' => route('xendit.payment.cancel'),
                'metadata' => [
                    'order_ids' => json_encode($orderIds),
                    'checkout_token' => $checkoutToken,
                ],
            ];

            $response = Invoice::create($params);

            if (! empty($response['invoice_url'])) {
                $data['checkoutUrl'] = $response['invoice_url'];

                session()->put('xendit_invoice_id', $response['id']);

                return $data;
            }

            $data['error'] = true;
            $data['message'] = $response['message'];
        } catch (Throwable $exception) {
            $data['error'] = true;
            $data['message'] = json_encode($exception->getMessage());
        }

        return $data;
    }
}
