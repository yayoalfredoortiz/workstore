<!DOCTYPE html>

<html lang="en">

    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

        <!-- Font Awesome Icons -->
        <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

        <!-- Template CSS -->
        <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

        <title>@lang($pageTitle)</title>
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="{{ $global->favicon_url }}">
        <meta name="theme-color" content="#ffffff">

        @isset($activeSettingMenu)
            <style>
                .preloader-container {
                    margin-left: 510px;
                    width: calc(100% - 510px)
                }

            </style>
        @endisset

        @stack('styles')

        <style>
            :root {
                --fc-border-color: #E8EEF3;
                --fc-button-text-color: #99A5B5;
                --fc-button-border-color: #99A5B5;
                --fc-button-bg-color: #ffffff;
                --fc-button-active-bg-color: #171f29;
                --fc-today-bg-color: #f2f4f7;
            }
            .fc a[data-navlink] {
                color: #99a5b5;
            }
        </style>

    </head>

    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->


    <body><!-- change dark theme class according to application dark theme setting -->
        <div class="row">
            <div class="col-md-12">
                <div class="white-box p-t-20">
                    <x-form id="createLead" method="POST">
                        <div class="form-body">
                            <div class="row">
                                @foreach ($leadFormFields as $item)
                                    <div class="col-md-6">
                                        <x-forms.text :fieldId="$item->field_name" :fieldLabel="__('modules.leads.'.$item->field_name)"
                                            :fieldName="$item->field_name" fieldPlaceholder="" :fieldRequired="$item->required == 1">
                                        </x-forms.text>
                                    </div>
                                @endforeach

                                @if($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v2_status == 'active' && $global->ticket_form_google_captcha == 1)
                                    <div class="col-md-12 col-lg-12 mt-2 mb-2" id="captcha_container"></div>
                                @endif

                                {{-- This is used for google captcha v3 --}}
                                <input type="hidden" id="g_recaptcha" name="g_recaptcha">

                                @if ($errors->has('g-recaptcha-response'))
                                    <div class="help-block with-errors">{{ $errors->first('g-recaptcha-response') }}</div>
                                @endif

                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" id="save-form" class="btn btn-primary"> <i class="fa fa-check"></i> @lang('app.save')</button>
                            <button type="reset" class="btn btn-secondary">@lang('app.reset')</button>
                        </div>
                    </x-form>

                    <div class="row">
                        <div class="col-sm-12 col-md-12">
                            <div class="alert alert-success" id="success-message" style="display:none"></div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </body>


    <!-- jQuery -->
    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>

    <!-- Global Required Javascript -->
    <script src="{{ asset('vendor/bootstrap/javascript/bootstrap-native.js') }}"></script>

    <!-- Font Awesome -->
    <script src="{{ asset('vendor/jquery/all.min.js') }}"></script>

    <!-- Template JS -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script src="{{ asset('vendor/froiden-helper/helper.js') }}"></script>

    <script>
        $('#save-form').click(function () {
            $.easyAjax({
                url: "{{route('front.lead_store')}}",
                container: '#createLead',
                type: "POST",
                redirect: true,
                disableButton: true,
                blockUI: true,
                data: $('#createLead').serialize(),
                success: function (response) {
                    if (response.status == "success") {
                        $('#createLead')[0].reset();
                        $('#createLead').hide();
                        $('#success-message').html(response.message);
                        $('#success-message').show();
                    }
                }
            })
        });
    </script>

    @if($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v2_status == 'active')
        <script src="https://www.google.com/recaptcha/api.js?onload=onloadCallback&render=explicit"
                async defer></script>
        <script>
            var gcv3;
            var onloadCallback = function () {
                // Renders the HTML element with id 'captcha_container' as a reCAPTCHA widget.
                // The id of the reCAPTCHA widget is assigned to 'gcv3'.
                gcv3 = grecaptcha.render('captcha_container', {
                    'sitekey': '{{$global->google_recaptcha_v2_site_key}}',
                    'theme': 'light',
                    'callback': function (response) {
                        if (response) {
                            $('#g_recaptcha').val(response);
                        }
                    },
                });
            };
        </script>
    @endif

    @if($global->google_recaptcha_status == 'active' && $global->google_recaptcha_v3_status == 'active')
        <script
            src="https://www.google.com/recaptcha/api.js?render={{$global->google_recaptcha_v3_site_key}}"></script>
        <script>
            grecaptcha.ready(function () {
                grecaptcha.execute('{{$global->google_recaptcha_v3_site_key}}').then(function (token) {
                    // Add your logic to submit to your backend server here.
                    $('#g_recaptcha').val(token);
                });
            });
        </script>
    @endif

</html>

