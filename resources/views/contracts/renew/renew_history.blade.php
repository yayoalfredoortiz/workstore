@php
$editContractPermission = user()->permission('edit_contract');
$deleteContractPermission = user()->permission('delete_contract');
@endphp
@forelse ($contract->renewHistory as $history)
    <div class="card w-100 rounded-0 border-0 comment">
        <div class="card-horizontal">
            <div class="card-img my-1 ml-0">
                <a href="{{ route('employees.show', $history->renewedBy->id) }}">
                    <img src="{{ $history->renewedBy->image_url }}" alt="{{ ucwords($history->renewedBy->name) }}"></a>
            </div>
            <div class="card-body border-0 pl-0 py-1">
                <div class="d-flex flex-grow-1">
                    <h4 class="card-title f-15 f-w-500 mr-3"><a class="text-dark"
                            href="{{ route('employees.show', $history->renewedBy->id) }}">{{ ucwords($history->renewedBy->name) }}</a>
                    </h4>
                    <p class="card-date f-11 text-lightest mb-0">
                        {{ $history->created_at->timezone($global->timezone)->format($global->date_format) }}
                    </p>
                    <div class="dropdown ml-auto comment-action">
                        <button class="btn btn-lg f-14 p-0 text-lightest text-capitalize rounded  dropdown-toggle"
                            type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="fa fa-ellipsis-h"></i>
                        </button>

                        <div class="dropdown-menu dropdown-menu-right border-grey rounded b-shadow-4 p-0"
                            aria-labelledby="dropdownMenuLink" tabindex="0">
                            @if ($editContractPermission == 'all' || ($editContractPermission == 'added' && $history->added_by == user()->id))
                                <a class="cursor-pointer d-block text-dark-grey f-13 py-3 px-3 edit-comment"
                                    href="javascript:;" data-row-id="{{ $history->id }}">@lang('app.edit')</a>
                            @endif

                            @if ($deleteContractPermission == 'all' || ($deleteContractPermission == 'added' && $history->added_by == user()->id))
                                <a class="cursor-pointer d-block text-dark-grey f-13 pb-3 px-3 delete-comment"
                                    data-row-id="{{ $history->id }}" href="javascript:;">@lang('app.delete')</a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="card-text f-14 text-dark-grey text-justify">
                    <x-table class="table-bordered my-3 rounded">
                        <x-slot name="thead">
                            <th>@lang('modules.contracts.newStartDate')</th>
                            <th>@lang('modules.contracts.newEndDate')</th>
                            <th class="text-right">@lang('modules.contracts.newAmount')</th>
                        </x-slot>
                        <tr>
                            <td>{{ $history->start_date->timezone($global->timezone)->format($global->date_format) }}</td>
                            <td>{{ $history->end_date->timezone($global->timezone)->format($global->date_format) }}</td>
                            <td class="text-right">{{ currency_formatter($history->amount) }}</td>
                        </tr>
                    </x-table>
                </div>
            </div>
        </div>
    </div>
@empty
    <x-cards.no-record icon="redo" :message="__('messages.noRecordFound')" />

@endforelse
