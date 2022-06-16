@extends('layouts.app')

@push('datatable-styles')
    <!-- for sortable content -->
    <link rel="stylesheet" href="{{ asset('vendor/css/jquery-ui.css') }}">

    <!-- to highlight html content -->
    <link rel="stylesheet" href="{{ asset('vendor/css/default.min.css') }}">
@endpush

@section('content')
    <!-- CONTENT WRAPPER START -->
    <div class="content-wrapper">
        <div class="row">
            <div class="col-lg-6">
                <div class="row">
                    <div class="col-sm-12">
                        <div class="card bg-white border-0 b-shadow-4">
                            <div class="card-body ">
                                <div class="col-md-12 mb-3">
                                    <div class="row">
                                        <div class="col-md-3 f-w-500">#</div>
                                        <div class="col-md-5 f-w-500">@lang('app.fields')</div>
                                        <div class="col-md-4 f-w-500">@lang('app.status')</div>
                                    </div>
                                </div>
                                <div class="col-md-12">
                                    <x-form id="editSettings" method="PUT">
                                        <div id="sortable">
                                            @foreach ($leadFormFields as $item)
                                                <div class="row py-3 pt-4 border-bottom">
                                                    <div class="col-md-3">
                                                        <span class="ui-icon ui-icon-arrowthick-2-n-s"></span>
                                                        <input type="hidden" name="sort_order[]"
                                                            value="{{ $item->id }}">
                                                    </div>
                                                    <div class="col-md-5">@lang('modules.leads.'.$item->field_name)
                                                    </div>
                                                    <div class="col-md-4">
                                                        @if ($item->field_name != 'name' && $item->field_name != 'email')
                                                            <div class="custom-control custom-switch">
                                                                <input type="checkbox"
                                                                    class="custom-control-input change-setting"
                                                                    data-setting-id="{{ $item->id }}" @if ($item->status == 'active') checked @endif
                                                                    id="{{ $item->id }}">
                                                                <label class="custom-control-label f-14"
                                                                    for="{{ $item->id }}"></label>
                                                            </div>
                                                        @else
                                                            --
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach

                                            @if($global->google_recaptcha_status == 'active')
                                                <div class="row py-3 pt-4 border-bottom">
                                                    <div class="col-md-3"></div>
                                                    <div class="col-md-5">@lang('modules.tickets.googleCaptcha')
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox"
                                                                class="custom-control-input change-setting" data-setting-id="0" id="0"
                                                                @if ($global->lead_form_google_captcha == 1) checked @endif>
                                                            <label for="0" class="custom-control-label f-14"></label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif

                                        </div>
                                    </x-form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-12 mt-4">
                        <x-cards.data>
                            <p class="f-w-500">@lang('modules.lead.iframeSnippet')</p>
                            <code>
                                &lt;iframe src="{{ route('front.lead_form') }}" width="100%"
                                frameborder="0">&lt;/iframe&gt;
                            </code>
                        </x-cards.data>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <x-cards.data>
                    <h4>@lang('app.preview')</h4>
                    <iframe src="{{ route('front.lead_form') }}" id="previewIframe" width="100%"
                        onload="resizeIframe(this)" frameborder="0"></iframe>
                </x-cards.data>
            </div>
        </div>

    </div>
    <!-- CONTENT WRAPPER END -->
@endsection

@push('scripts')
    <!-- for sortable content -->
    <script src="{{ asset('vendor/jquery/jquery-ui.min.js') }}"></script>

    <!-- to highlight html content -->
    <script src="{{ asset('vendor/jquery/highlight.min.js') }}"></script>

    <script>
        $(function() {
            $("#sortable").sortable({
                update: function(event, ui) {
                    var sortedValues = new Array();
                    $('input[name="sort_order[]"]').each(function(index, value) {
                        sortedValues[index] = $(this).val();
                    });
                    $.easyAjax({
                        url: "{{ route('lead-form.sortFields') }}",
                        type: "POST",
                        blockUI: true,
                        data: {
                            'sortedValues': sortedValues,
                            '_token': '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            var iframe = document.getElementById('previewIframe');
                            iframe.src = iframe.src;
                        }
                    })
                }
            });
        });

        $('.change-setting').change(function() {
            var id = $(this).data('setting-id');
            var sendEmail = $(this).is(':checked') ? 'active' : 'inactive';

            var url = '{{ route('lead-form.update', ':id') }}';
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "POST",
                blockUI: true,
                data: {
                    'id': id,
                    'status': sendEmail,
                    '_method': 'PUT',
                    '_token': '{{ csrf_token() }}'
                },
                success: function(response) {
                    var iframe = document.getElementById('previewIframe');
                    iframe.src = iframe.src;
                }
            })
        });

        function resizeIframe(obj) {
            obj.style.height = obj.contentWindow.document.documentElement.scrollHeight + 50 + 'px';
        }

    </script>
@endpush
