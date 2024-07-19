@php $xenditStatus = get_payment_setting('status', XENDIT_PAYMENT_METHOD_NAME); @endphp

<table class="table payment-method-item">
    <tbody>
    <tr class="border-pay-row">
        <td class="border-pay-col"><i class="fa fa-theme-payments"></i></td>
        <td style="width: 20%;">
            <img class="filter-black" src="{{ url('vendor/core/plugins/xendit/images/logo.svg') }}"
                 alt="Xendit">
        </td>
        <td class="border-right">
            <ul>
                <li>
                    <a href="https://xendit.co" target="_blank">{{ __('Xendit') }}</a>
                    <p>{{ __('Customer can buy product and pay directly using Visa, Credit card via :name', ['name' => 'Xendit']) }}</p>
                </li>
            </ul>
        </td>
    </tr>
    <tr class="bg-white">
        <td colspan="3">
            <div class="float-start" style="margin-top: 5px;">
                <div
                    class="payment-name-label-group @if (get_payment_setting('status', XENDIT_PAYMENT_METHOD_NAME) == 0) hidden @endif">
                    <span class="payment-note v-a-t">{{ trans('plugins/payment::payment.use') }}:</span> <label
                        class="ws-nm inline-display method-name-label">{{ get_payment_setting('name', XENDIT_PAYMENT_METHOD_NAME) }}</label>
                </div>
            </div>
            <div class="float-end">
                <a class="btn btn-secondary toggle-payment-item edit-payment-item-btn-trigger @if ($xenditStatus == 0) hidden @endif">{{ trans('plugins/payment::payment.edit') }}</a>
                <a class="btn btn-secondary toggle-payment-item save-payment-item-btn-trigger @if ($xenditStatus == 1) hidden @endif">{{ trans('plugins/payment::payment.settings') }}</a>
            </div>
        </td>
    </tr>
    <tr class="paypal-online-payment payment-content-item hidden">
        <td class="border-left" colspan="3">
            {!! Form::open() !!}
            {!! Form::hidden('type', XENDIT_PAYMENT_METHOD_NAME, ['class' => 'payment_type']) !!}
            <div class="row">
                <div class="col-sm-6">
                    <ul>
                        <li>
                            <label>{{ trans('plugins/payment::payment.configuration_instruction', ['name' => 'Xendit']) }}</label>
                        </li>
                        <li class="payment-note">
                            <p>{{ trans('plugins/payment::payment.configuration_requirement', ['name' => 'Xendit']) }}:</p>
                            <ul class="m-md-l" style="list-style-type:decimal">
                                <li style="list-style-type:decimal">
                                    <a href="https://xendit.co" target="_blank">
                                        {{ __('Register an account on :name', ['name' => 'Xendit']) }}
                                    </a>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('After registration at :name, you will have API & Secret keys', ['name' => 'Xendit Checkout']) }}</p>
                                </li>
                                <li style="list-style-type:decimal">
                                    <p>{{ __('Enter API key, Secret into the box in right hand') }}</p>
                                </li>
                            </ul>
                        </li>
                    </ul>
                </div>
                <div class="col-sm-6">
                    <div class="well bg-white">
                        <div class="form-group mb-3">
                            <label class="text-title-field"
                                   for="xendit_name">{{ trans('plugins/payment::payment.method_name') }}</label>
                            <input type="text" class="next-input" name="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_name"
                                   id="xendit_name" data-counter="400"
                                   value="{{ get_payment_setting('name', XENDIT_PAYMENT_METHOD_NAME, __('Online payment via :name', ['name' => 'Xendit'])) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_description">{{ trans('core/base::forms.description') }}</label>
                            <textarea class="next-input" name="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_description" id="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_description">{{ get_payment_setting('description', XENDIT_PAYMENT_METHOD_NAME) }}</textarea>
                        </div>

                        <p class="payment-note">
                            {{ trans('plugins/payment::payment.please_provide_information') }} <a target="_blank" href="https://xendit.co">Xendit</a>:
                        </p>
                        <div class="form-group mb-3">
                            <label class="text-title-field" for="{{ XENDIT_PAYMENT_METHOD_NAME }}_api_key">{{ __('API Key') }}</label>
                            <input type="text" class="next-input"
                                   name="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_api_key" id="{{ XENDIT_PAYMENT_METHOD_NAME }}_api_key"
                                   value="{{ get_payment_setting('api_key', XENDIT_PAYMENT_METHOD_NAME) }}">
                        </div>
                        <div class="form-group mb-3">
                            <label class="text-title-field" for="{{ XENDIT_PAYMENT_METHOD_NAME }}_public_key">{{ __('Public Key') }}</label>
                            <input type="text" class="next-input"
                                   name="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_public_key" id="{{ XENDIT_PAYMENT_METHOD_NAME }}_public_key"
                                   value="{{ get_payment_setting('public_key', XENDIT_PAYMENT_METHOD_NAME) }}">
                        </div>

                        <div class="form-group mb-3">
                            <label class="text-title-field" for="{{ XENDIT_PAYMENT_METHOD_NAME }}_payment_type">{{ __('Payment Type') }}</label>
                            <div class="ui-select-wrapper">
                                <select name="payment_{{ XENDIT_PAYMENT_METHOD_NAME }}_payment_type" class="ui-select select-search-full" id="{{ XENDIT_PAYMENT_METHOD_NAME }}_payment_type">
                                    <option value="popup" @if (get_payment_setting('payment_type', XENDIT_PAYMENT_METHOD_NAME, 'popup') == 'popup') selected @endif>Dialog Pop-up</option>
                                    <option value="redirect_checkout" @if (get_payment_setting('payment_type', XENDIT_PAYMENT_METHOD_NAME, 'popup') == 'redirect_checkout') selected @endif>Redirect Checkout</option>
                                </select>
                                <svg class="svg-next-icon svg-next-icon-size-16">
                                    <use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#select-chevron"></use>
                                </svg>
                            </div>
                        </div>

                        {!! apply_filters(PAYMENT_METHOD_SETTINGS_CONTENT, null, XENDIT_PAYMENT_METHOD_NAME) !!}
                    </div>
                </div>
            </div>
            <div class="col-12 bg-white text-end">
                <button class="btn btn-warning disable-payment-item @if ($xenditStatus == 0) hidden @endif"
                        type="button">{{ trans('plugins/payment::payment.deactivate') }}</button>
                <button
                    class="btn btn-info save-payment-item btn-text-trigger-save @if ($xenditStatus == 1) hidden @endif"
                    type="button">{{ trans('plugins/payment::payment.activate') }}</button>
                <button
                    class="btn btn-info save-payment-item btn-text-trigger-update @if ($xenditStatus == 0) hidden @endif"
                    type="button">{{ trans('plugins/payment::payment.update') }}</button>
            </div>
            {!! Form::close() !!}
        </td>
    </tr>
    </tbody>
</table>
