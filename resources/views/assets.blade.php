@if (get_payment_setting('payment_type', XENDIT_PAYMENT_METHOD_NAME, 'popup') == 'popup')
    <style>
        /* Modal */
        .modal-background {
            width: 100%;
            height: 100%;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 1010;
            background-color: rgba(0, 0, 0, 0.65);
            display: none;
        }

        .modal-background--blackout {
            display: block;
        }

        .modal-popup {
            height: calc(100vh - 150px);
            width: 600px;
            position: absolute;
            left: 50%;
            top: 50%;
            z-index: 1011;
            background-color: #ffffff;
            transform: translateX(-50%) translateY(calc(-50% - 0.5px));
        }

        .modal-popup__icon-close {
            width: 30px;
            height: 30px;
            position: absolute;
            right: -15px;
            top: -15px;
            cursor: pointer;
            background: url({{ url('vendor/core/plugins/xendit/images/icon-times.svg') }}) center no-repeat #4573ff;
        }

        .modal-popup,
        .modal-popup__icon-close {
            opacity: 0;
            pointer-events: none;
            transition: all 300ms ease-in-out;
        }

        .modal-popup--visible,
        .modal-popup--visible .modal-popup__icon-close {
            opacity: 1;
            pointer-events: auto;
        }

        /* iFrame */
        .iframe-invoice {
            height: inherit;
            width: inherit;
            border: 0;
            overflow-y: scroll;
        }
    </style>

    <script>

        $(document).ready(function () {

            let validatedFormFields = () => {
                let addressId = $('#address_id').val();
                if (addressId && addressId !== 'new') {
                    return true;
                }

                let validated = true;
                $.each($(document).find('.address-control-item-required'), (index, el) => {
                    if (!$(el).val()) {
                        validated = false;
                    }
                });

                return validated;
            }

            $('.payment-checkout-form').on('submit', function (e) {
                if (validatedFormFields() && $('input[name=payment_method]:checked').val() === 'xendit') {
                    e.preventDefault();
                }
            });

            const modal = document.querySelector('.modal-popup');
            const bodyBlackout = document.querySelector('.modal-background');
            const iframe = document.getElementById('iframe-invoice');
            const modalCloseTrigger = document.querySelector(
                '.modal-popup__icon-close'
            );

            modalCloseTrigger.addEventListener('click', () => {
                modal.classList.remove('modal-popup--visible');
                bodyBlackout.classList.remove('modal-background--blackout');
            });

            $(document).off('click', '.payment-checkout-btn').on('click', '.payment-checkout-btn', function (event) {
                event.preventDefault();

                let _self = $(this);
                let form = _self.closest('form');
                if (validatedFormFields()) {
                    _self.attr('disabled', 'disabled');
                    let submitInitialText = _self.html();
                    _self.html('<i class="fa fa-gear fa-spin"></i> ' + _self.data('processing-text'));

                    let method = $('input[name=payment_method]:checked').val();

                    if (method === 'xendit') {

                        $.ajax({
                            url: '{{ route('public.checkout.process', OrderHelper::getOrderSessionToken()) }}',
                            type: 'POST',
                            cache: false,
                            data: new FormData($(this).closest('form')[0]),
                            contentType: false,
                            processData: false,
                            success: res => {
                                if (res.error) {
                                    alert(res.message);
                                } else if (res.data && res.data.checkoutUrl) {
                                    iframe.src = res.data.checkoutUrl;
                                    modal.classList.add('modal-popup--visible');
                                    bodyBlackout.classList.add('modal-background--blackout');
                                }

                                _self.removeAttr('disabled');
                                _self.html(submitInitialText);
                            },
                            error: () => {
                                _self.removeAttr('disabled');
                                _self.html(submitInitialText);
                            }
                        });
                    } else {
                        form.submit();
                    }
                } else {
                    form.submit();
                }
            });
        });
    </script>

    <div class="modal-background"></div>
    <div class="modal-popup">
        <div class="modal-popup__icon-close"></div>
        <iframe
            id="iframe-invoice"
            class="iframe-invoice"
            title="Invoice"
        ></iframe>
    </div>
@endif
