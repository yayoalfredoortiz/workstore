<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <link rel="icon" type="image/png" sizes="16x16" href="{{ $global->favicon_url }}">
    <meta name="msapplication-TileImage" content="{{ $global->favicon_url }}">

    <!-- Font Awesome Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/all.min.css') }}">

    <!-- Simple Line Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/simple-line-icons.css') }}">

    <!-- Datepicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/datepicker.min.css') }}">

    <!-- TimePicker -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-timepicker.min.css') }}">

    <!-- Select Plugin -->
    <link rel="stylesheet" href="{{ asset('vendor/css/select2.min.css') }}">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="{{ asset('vendor/css/bootstrap-icons.css') }}">

    @stack('datatable-styles')

    <!-- Template CSS -->
    <link type="text/css" rel="stylesheet" media="all" href="{{ asset('css/main.css') }}">

    <title>@lang($pageTitle)</title>
    <meta name="msapplication-TileColor" content="#ffffff">
    <meta name="msapplication-TileImage" content="{{ $global->favicon_url }}">
    <meta name="theme-color" content="#ffffff">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    @isset($activeSettingMenu)
        <style>
            .preloader-container {
                margin-left: 510px;
                width: calc(100% - 510px)
            }

            .blur-code {
                filter: blur(3px);

            }

            .purchase-code {
                transition: filter .2s ease-out;
                margin-right: 4px;
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

    {{-- Custom theme styles --}}
    @if (!user()->dark_theme)
        @include('sections.theme_css')
    @endif

    @if (file_exists(public_path() . '/css/app-custom.css'))
        <link href="{{ asset('css/app-custom.css') }}" rel="stylesheet">
    @endif

    <script src="{{ asset('vendor/jquery/jquery.min.js') }}"></script>
    <script src="{{ asset('vendor/jquery/modernizr.min.js') }}"></script>

    {{-- Timepicker --}}
    <script src="{{ asset('vendor/jquery/bootstrap-timepicker.min.js') }}"></script>

    @if ($pushSetting->status == 'active')
        <link rel="manifest" href="{{ asset('manifest.json') }}" />
        <script src="{{ asset('vendor/onesignal/OneSignalSDK.js') }}" async='async'></script>
        <script>
            var OneSignal = window.OneSignal || [];
            OneSignal.push(function() {
                OneSignal.init({
                    appId: "{{ $pushSetting->onesignal_app_id }}",
                    autoRegister: false,
                    notifyButton: {
                        enable: true,
                        showCredit: false,
                        theme: "inverse",
                        size: "small",
                        position: '{{ user()->rtl ? 'bottom-left' : 'bottom-right' }}',
                        text: {
                            'tip.state.unsubscribed': "@lang('app.onesignal.tip.state.unsubscribed')",
                            'tip.state.subscribed': "@lang('app.onesignal.tip.state.subscribed')",
                            'tip.state.blocked': "@lang('app.onesignal.tip.state.blocked')",
                            'message.prenotify': "@lang('app.onesignal.message.prenotify')",
                            'message.action.subscribed': "@lang('app.onesignal.message.action.subscribed')",
                            'message.action.resubscribed': "@lang('app.onesignal.message.action.resubscribed')",
                            'message.action.unsubscribed': "@lang('app.onesignal.message.action.unsubscribed')",
                            'dialog.main.title': "@lang('app.onesignal.dialog.main.title')",
                            'dialog.main.button.subscribe': "@lang('app.onesignal.dialog.main.button.subscribe')",
                            'dialog.main.button.unsubscribe': "@lang('app.onesignal.dialog.main.button.unsubscribe')",
                            'dialog.blocked.title': "@lang('app.onesignal.dialog.blocked.title')",
                            'dialog.blocked.message': "@lang('app.onesignal.dialog.blocked.message')"
                        }
                    },
                    promptOptions: {
                        /* actionMessage limited to 90 characters */
                        actionMessage: "@lang('app.onesignal.actionMessage')",
                        /* acceptButtonText limited to 15 characters */
                        acceptButtonText: "@lang('app.onesignal.acceptButtonText')",
                        /* cancelButtonText limited to 15 characters */
                        cancelButtonText: "@lang('app.onesignal.cancelButtonText')"
                    }
                });
                OneSignal.on('subscriptionChange', function(isSubscribed) {
                    console.log("The user's subscription state is now:", isSubscribed);
                });


                if (Notification.permission === "granted") {
                    // Automatically subscribe user if deleted cookies and browser shows "Allow"
                    OneSignal.getUserId()
                        .then(function(userId) {
                            if (!userId) {
                                OneSignal.registerForPushNotifications();
                            } else {
                                let db_onesignal_id = '{{ $user->onesignal_player_id }}';

                                if (db_onesignal_id == null || db_onesignal_id !==
                                    userId) { //update onesignal ID if it is new
                                    updateOnesignalPlayerId(userId);
                                }
                            }
                        })
                } else {
                    OneSignal.isPushNotificationsEnabled(function(isEnabled) {

                        OneSignal.getUserId(function(userId) {
                            console.log("OneSignal User ID:", userId);
                            // (Output) OneSignal User ID: 270a35cd-4dda-4b3f-b04e-41d7463a2316
                            let db_onesignal_id = '{{ $user->onesignal_player_id }}';
                            console.log('database id : ' + db_onesignal_id);

                            if (db_onesignal_id == null || db_onesignal_id !==
                                userId) { //update onesignal ID if it is new
                                updateOnesignalPlayerId(userId);
                            }
                        });

                        if (isEnabled) {
                            console.log("Push notifications are enabled! - 2    ");
                            // console.log("unsubscribe");
                            // OneSignal.setSubscription(false);
                        } else {
                            console.log("Push notifications are not enabled yet. - 2");
                            OneSignal.showHttpPrompt();
                            // OneSignal.registerForPushNotifications({
                            //         modalPrompt: true
                            // });
                        }
                    });

                }
            });
        </script>
    @endif

    @if ($pusherSettings->status && app()->environment('codecanyon'))
        <script src="https://js.pusher.com/7.0/pusher.min.js"></script>

        <script>
            // Enable pusher logging - don't include this in production
            // Pusher.logToConsole = true;

            var pusher = new Pusher('{{ $pusherSettings->pusher_app_key }}', {
                cluster: '{{ $pusherSettings->pusher_cluster }}',
                forceTLS: '{{ $pusherSettings->force_tls }}'
            });
        </script>
    @endif

    {{-- Include file for widgets if exist --}}
    @includeif('sections.custom_script')


    <script>
        var checkMiniSidebar = localStorage.getItem("mini-sidebar");
    </script>

</head>


<body id="body" class="{{ user()->dark_theme ? 'dark-theme' : '' }} {{ user()->rtl ? 'rtl' : '' }}">
    <script>
        if (checkMiniSidebar == "yes" || checkMiniSidebar == "") {
            $('body').addClass('sidebar-toggled');
        }
    </script>
    {{-- include topbar --}}
    @include('sections.topbar')

    {{-- include sidebar menu --}}
    @include('sections.sidebar')

    <!-- BODY WRAPPER START -->
    <div class="body-wrapper clearfix">


        <!-- MAIN CONTAINER START -->
        <section class="main-container bg-additional-grey" id="fullscreen">

            <div class="preloader-container d-flex justify-content-center align-items-center">
                <div class="spinner-border" role="status" aria-hidden="true"></div>
            </div>


            @yield('filter-section')

            <x-app-title class="d-block d-lg-none" :pageTitle="__($pageTitle)"></x-app-title>

            @yield('content')


        </section>
        <!-- MAIN CONTAINER END -->
    </div>
    <!-- BODY WRAPPER END -->

    <!-- also the modal itself -->
    <div id="myModalDefault" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog d-flex justify-content-center align-items-center">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel rounded mr-3" data-dismiss="modal">Close</button>
                    <button type="button" class="btn-primary rounded">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- also the modal itself -->
    <div id="myModal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog d-flex justify-content-center align-items-center modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body">
                    Loading...
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn-cancel rounded mr-3" data-dismiss="modal">Close</button>
                    <button type="button" class="btn-primary rounded">Save changes</button>
                </div>
            </div>
        </div>
    </div>

    <!-- also the modal itself -->
    <div id="myModalXl" class="modal fade overflow-auto" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog d-flex justify-content-center align-items-center modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modelHeading">Modal title</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                            aria-hidden="true">×</span></button>
                </div>
                <div class="modal-body bg-grey">
                    Loading...
                </div>

            </div>
        </div>
    </div>

    <x-right-modal />

    <!-- Global Required Javascript -->
    <script src="{{ asset('js/main.js') }}"></script>
    <script>
        const MODAL_DEFAULT = '#myModalDefault';
        const MODAL_LG = '#myModal';
        const MODAL_XL = '#myModalXl';
        const MODAL_HEADING = '#modelHeading';
        const RIGHT_MODAL = '#task-detail-1';
        const RIGHT_MODAL_CONTENT = '#right-modal-content';
        const RIGHT_MODAL_TITLE = '#right-modal-title';
        const global_setting = @json(global_setting());
        const pusher_setting = @json(pusher_settings());
        const SEARCH_KEYWORD = "{{ request('search_keyword') }}";

        const datepickerConfig = {
            formatter: (input, date, instance) => {
                input.value = moment(date).format('{{ $global->moment_date_format }}')
            },
            showAllDates: true,
            customDays: ["@lang('app.weeks.Sun')", "@lang('app.weeks.Mon')", "@lang('app.weeks.Tue')",
                "@lang('app.weeks.Wed')", "@lang('app.weeks.Thu')", "@lang('app.weeks.Fri')",
                "@lang('app.weeks.Sat')"
            ],
            customMonths: ["@lang('app.months.January')", "@lang('app.months.February')",
                "@lang('app.months.March')", "@lang('app.months.April')", "@lang('app.months.May')",
                "@lang('app.months.June')", "@lang('app.months.July')", "@lang('app.months.August')",
                "@lang('app.months.September')", "@lang('app.months.October')",
                "@lang('app.months.November')", "@lang('app.months.December')"
            ],
            customOverlayMonths: ["@lang('app.monthsShort.Jan')", "@lang('app.monthsShort.Feb')",
                "@lang('app.monthsShort.Mar')", "@lang('app.monthsShort.Apr')",
                "@lang('app.monthsShort.May')", "@lang('app.monthsShort.Jun')",
                "@lang('app.monthsShort.Jul')", "@lang('app.monthsShort.Aug')",
                "@lang('app.monthsShort.Sep')", "@lang('app.monthsShort.Oct')",
                "@lang('app.monthsShort.Nov')", "@lang('app.monthsShort.Dec')"
            ],
            overlayButton: "@lang('app.submit')",
            overlayPlaceholder: "@lang('app.enterYear')"
        };

        const daterangeConfig = {
            "@lang('app.today')": [moment(), moment()],
            "@lang('app.last30Days')": [moment().subtract(29, 'days'), moment()],
            "@lang('app.thisMonth')": [moment().startOf('month'), moment().endOf('month')],
            "@lang('app.lastMonth')": [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month')
                .endOf(
                    'month')
            ],
            "@lang('app.last90Days')": [moment().subtract(89, 'days'), moment()],
            "@lang('app.last6Months')": [moment().subtract(6, 'months'), moment()],
            "@lang('app.last1Year')": [moment().subtract(1, 'years'), moment()]
        };

        const daterangeLocale = {
            "format": "{{ $global->moment_date_format }}",
            "customRangeLabel": "@lang('app.customRange')",
            "separator": " @lang('app.to') ",
            "applyLabel": "@lang('app.apply')",
            "cancelLabel": "@lang('app.cancel')",
            "daysOfWeek": ['@lang("app.weeks.Sun")', '@lang("app.weeks.Mon")',
                '@lang("app.weeks.Tue")',
                '@lang("app.weeks.Wed")', '@lang("app.weeks.Thu")', '@lang("app.weeks.Fri")',
                '@lang("app.weeks.Sat")'
            ],
            "monthNames": ['@lang("app.months.January")', '@lang("app.months.February")',
                "@lang('app.months.March')", "@lang('app.months.April')",
                "@lang('app.months.May')",
                "@lang('app.months.June')", "@lang('app.months.July')",
                "@lang('app.months.August')",
                "@lang('app.months.September')", "@lang('app.months.October')",
                "@lang('app.months.November')", "@lang('app.months.December')"
            ],
            "firstDay": 1
        };

        const dropifyMessages = {
            default: "@lang('app.dragDrop')",
            replace: "@lang('app.dragDropReplace')",
            remove: "@lang('app.remove')",
            error: "@lang('messages.errorOccured')",
        };

        const dropzoneFileAllow =
            "{{ $global->allowed_file_types }}";
    </script>

    <!-- Scripts -->
    <script>
        window.Laravel = {!! json_encode([
    'csrfToken' => csrf_token(),
    'user' => user(),
]) !!};
    </script>

    @stack('scripts')

    <script>
        $(window).on('load', function() {
            // Animate loader off screen
            init();
            $(".preloader-container").fadeOut("slow", function() {
                $(this).removeClass("d-flex");
            });
        });

        $('body').on('click', '.view-notification', function(event) {
            event.preventDefault();
            var id = $(this).data('notification-id');
            var href = $(this).attr('href');

            $.easyAjax({
                url: "{{ route('mark_single_notification_read') }}",
                type: "POST",
                data: {
                    '_token': "{{ csrf_token() }}",
                    'id': id
                },
                success: function() {
                    if (typeof href !== 'undefined') {
                        window.location = href;
                    }
                }
            });
        });

        $('body').on('click', '.img-lightbox', function () {
            var imageUrl = $(this).data('image-url');
            const url = "{{ route('invoices.show_image').'?image_url=' }}"+imageUrl;
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        function updateOnesignalPlayerId(userId) {
            $.easyAjax({
                url: '{{ route('profile.update_onesignal_id') }}',
                type: 'POST',
                data: {
                    'userId': userId,
                    '_token': '{{ csrf_token() }}'
                }
            })
        }

        if (SEARCH_KEYWORD != '' && $('#search-text-field').length > 0) {
            $('#search-text-field').val(SEARCH_KEYWORD);
            $('#reset-filters').removeClass('d-none');
        }

        $('body').on('click', '.show-hide-purchase-code', function() {
            $('> .icon', this).toggleClass('fa-eye-slash fa-eye');
            $(this).siblings('span').toggleClass('blur-code ');
        });
        Dropzone.prototype.defaultOptions.dictDefaultMessage = "{{ __('modules.projectTemplate.dropFile') }}";
    </script>

    <script>
        let quillArray = {};

        function quillImageLoad(ID){
            quillArray[ID] = new Quill(ID, {
                modules: {
                    toolbar: [
                        [{
                            header: [1, 2, 3, 4, 5, false]
                        }],
                        [{
                            'list': 'ordered'
                        }, {
                            'list': 'bullet'
                        }],
                        ['bold', 'italic', 'underline', 'strike'],
                        ['image', 'code-block', 'link'],
                        [{
                            'direction': 'rtl'
                        }],
                        ['clean']
                    ],
                    clipboard: {
                        matchVisual: false
                    },
                    "emoji-toolbar": true,
                    "emoji-textarea": true,
                    "emoji-shortname": true,
                },
                theme: 'snow'
            });
            $.each(quillArray, function (key,quill) {
                quill.getModule('toolbar').addHandler('image', selectLocalImage);
            });

        }
        /**
         * Step1. select local image
         *
         */
        function selectLocalImage() {
            const input = document.createElement('input');
            input.setAttribute('type', 'file');
            input.click();

            // Listen upload local image and save to server
            input.onchange = () => {
                const file = input.files[0];

                // file type is only image.
                if (/^image\//.test(file.type)) {
                    saveToServer(file);
                } else {
                    console.warn('You could only upload images.');
                }
            };
        }

        /**
         * Step2. save to server
         *
         * @param {File} file
         */
        function saveToServer(file) {
            const fd = new FormData();
            fd.append('image', file);
            $.ajax({
                type: 'POST',
                url: "{{route('image.store')}}",
                dataType: "json",
                headers: {'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')},
                data: fd,
                contentType: false,
                processData: false,
                success: function (response) {
                    insertToEditor(response)
                },
            });
        }

        function insertToEditor(url) {
            // push image url to rich editor.
            $.each(quillArray, function (key, quill) {
                try {
                    let range = quill.getSelection();
                    quill.insertEmbed(range.index, 'image', url);
                } catch (err) {
                }
            });
        }

    </script>

</body>

</html>
