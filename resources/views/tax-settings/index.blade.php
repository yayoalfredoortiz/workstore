@extends('layouts.app')

@section('content')

    <!-- SETTINGS START -->
    <div class="w-100 d-flex ">

        <x-setting-sidebar :activeMenu="$activeSettingMenu" />

        <x-setting-card>
            <x-slot name="header">
                <div class="s-b-n-header" id="tabs">
                    <h2 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                        @lang($pageTitle)</h2>
                </div>
            </x-slot>

            @if (user()->permission('manage_tax') == 'all')
                <x-slot name="buttons">
                    <div class="row">

                        <div class="col-md-12 mb-2">
                            <x-forms.button-primary icon="plus" id="add-tax" class="type-btn mb-2 actionBtn">
                                @lang('app.add') @lang('app.new') @lang('app.tax')
                            </x-forms.button-primary>
                        </div>

                    </div>
                </x-slot>
            @endif

            <div class="col-lg-12 col-md-12 ntfcn-tab-content-left w-100 p-4 ">
                <div class="table-responsive">
                    <x-table class="table-bordered">
                        <x-slot name="thead">
                            <th>#</th>
                            <th>@lang('modules.invoices.taxName')</th>
                            <th>@lang('modules.invoices.rate') %</th>
                            <th class="text-right">@lang('app.action')</th>
                        </x-slot>

                        @forelse($taxes as $key => $tax)
                            <tr id="tax-{{ $tax->id }}">
                                <td>{{ $key + 1 }}</td>
                                <td>{{ ucwords($tax->tax_name) }}</td>
                                <td>{{ ucwords($tax->rate_percent) }}</td>
                                <td class="text-right">
                                    <div class="task_view">
                                        <a class="task_view_more d-flex align-items-center justify-content-center edit-tax" href="javascript:;" data-tax-id="{{ $tax->id }}" >
                                            <i class="fa fa-edit icons mr-2"></i>  @lang('app.edit')
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3">@lang('messages.noRecordFound')</td>
                            </tr>
                        @endforelse
                    </x-table>
                </div>
            </div>

        </x-setting-card>

    </div>
    <!-- SETTINGS END -->
@endsection

@push('scripts')

<script>
    // create new tax
    $('#add-tax').click(function() {
        const url = "{{ route('taxes.create') }}?via=tax-setting";
        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });

    // edit new tax
    $('.edit-tax').click(function() {
        let id = $(this).data('tax-id');

        let url = "{{ route('taxes.edit', ':id') }}?via=tax-setting";
        url = url.replace(':id', id);

        $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
        $.ajaxModal(MODAL_LG, url);
    });
</script>

@endpush
