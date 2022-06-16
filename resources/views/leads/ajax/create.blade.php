@php
$addLeadAgentPermission = user()->permission('add_lead_agent');
$addLeadSourcesPermission = user()->permission('add_lead_sources');
$addLeadCategoryPermission = user()->permission('add_lead_category');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-lead-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.lead.leadDetails')</h4>
                <div class="row p-20">

                    <div class="col-lg-4 col-md-6">
                        <x-forms.select fieldId="salutation" :fieldLabel="__('modules.client.salutation')"
                            fieldName="salutation">
                            <option value="">--</option>
                            @foreach ($salutations as $salutation)
                                <option value="{{ $salutation }}">@lang('app.'.$salutation)</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.clientName')" fieldName="client_name"
                            fieldId="client_name" :fieldPlaceholder="__('placeholders.name')" fieldRequired="true" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.email fieldId="client_email" :fieldLabel="__('modules.lead.clientEmail')"
                            fieldName="client_email" :fieldPlaceholder="__('placeholders.email')" fieldRequired="true">
                        </x-forms.email>
                    </div>


                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="" :fieldLabel="__('modules.tickets.chooseAgents')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="agent_id" id="agent_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($leadAgents as $emp)
                                    <option
                                        data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $emp->user->image_url }}' ></div> {{ ucfirst($emp->user->name) }}"
                                        value="{{ $emp->id }}">{{ ucwords($emp->user->name) }}</option>
                                @endforeach
                            </select>

                            @if ($addLeadAgentPermission == 'all' || $addLeadAgentPermission == 'added')
                                <x-slot name="append">
                                    <button type="button"
                                        class="btn btn-outline-secondary border-grey add-lead-agent">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="source_id" :fieldLabel="__('modules.lead.leadSource')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="source_id" id="source_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($sources as $source)
                                    <option value="{{ $source->id }}">{{ ucwords($source->type) }}</option>
                                @endforeach
                            </select>

                            @if ($addLeadSourcesPermission == 'all' || $addLeadSourcesPermission == 'added')
                                <x-slot name="append">
                                    <button type="button"
                                        class="btn btn-outline-secondary border-grey add-lead-source">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.label class="my-3" fieldId="category_id" :fieldLabel="__('modules.lead.leadCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addLeadCategoryPermission == 'all' || $addLeadCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button type="button"
                                        class="btn btn-outline-secondary border-grey add-lead-category">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4 mt-2">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.lead') .' '. __('app.value')"
                            fieldName="value" fieldId="value" />
                    </div>

                    <div class="col-lg-4">
                        <x-forms.label class="my-3" fieldId="next_follow_up" :fieldLabel="__('app.next_follow_up')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="next_follow_up" id="next_follow_up"
                                data-live-search="true" data-size="8">
                                <option value="yes"> @lang('app.yes')</option>
                                <option value="no"> @lang('app.no')</option>
                            </select>
                        </x-forms.input-group>
                    </div>

                    <div class="col-lg-4">
                        <x-forms.select fieldId="status" :fieldLabel="__('app.status')" fieldName="status">
                            <option value="">--</option>
                            @foreach ($status as $sts)
                                <option @if ($columnId == $sts->id) selected @endif value="{{ $sts->id }}">
                                    {{ ucfirst($sts->type) }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="note" :fieldLabel="__('app.note')">
                            </x-forms.label>
                            <div id="note"></div>
                            <textarea name="note" id="note-text" class="d-none"></textarea>
                        </div>
                    </div>
                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.companyDetails')</a>
                </h4>


                <div class="row p-20 d-none" id="other-details">

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.companyName')" fieldName="company_name"
                            fieldId="company_name" :fieldPlaceholder="__('modules.lead.companyName')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.lead.website')" fieldName="website" fieldId="website"
                            :fieldPlaceholder="__('modules.lead.website')" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.tel fieldId="mobile" :fieldLabel="__('modules.lead.mobile')" fieldName="mobile"
                            fieldPlaceholder="e.g. 987654321"></x-forms.tel>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.client.officePhoneNumber')" fieldName="office"
                            fieldId="office" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.select fieldId="country" :fieldLabel="__('app.country')" fieldName="country"
                            search="true">
                            <option value="">--</option>
                            @foreach ($countries as $item)
                                <option data-tokens="{{ $item->iso3 }}"
                                    data-content="<span class='flag-icon flag-icon-{{ strtolower($item->iso) }} flag-icon-squared'></span> {{ $item->nicename }}"
                                    value="{{ $item->nicename }}">{{ $item->nicename }}</option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.state')" fieldName="state"
                            fieldId="state" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.city')" fieldName="city"
                            fieldId="city" fieldPlaceholder="" />
                    </div>

                    <div class="col-lg-3 col-md-6">
                        <x-forms.text :fieldLabel="__('modules.stripeCustomerAddress.postalCode')"
                            fieldName="postal_code" fieldId="postal_code" fieldPlaceholder="" />
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.textarea class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.address')"
                                fieldName="address" fieldId="address" fieldPlaceholder="e.g. Rocket Road">
                            </x-forms.textarea>
                        </div>
                    </div>

                    @if (isset($fields) && count($fields) > 0)
                        @foreach ($fields as $field)
                            <div class="col-md-4">
                                @if ($field->type == 'text')
                                    <x-forms.text fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldLabel="$field->label"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldPlaceholder="$field->label"
                                        :fieldRequired="($field->required === 'yes') ? true : false">
                                    </x-forms.text>
                                @elseif($field->type == 'password')
                                    <x-forms.password
                                        fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldLabel="$field->label"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldPlaceholder="$field->label"
                                        :fieldRequired="($field->required === 'yes') ? true : false">
                                    </x-forms.password>
                                @elseif($field->type == 'number')
                                    <x-forms.number
                                        fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldLabel="$field->label"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldPlaceholder="$field->label"
                                        :fieldRequired="($field->required === 'yes') ? true : false">
                                    </x-forms.number>
                                @elseif($field->type == 'textarea')
                                    <x-forms.textarea :fieldLabel="$field->label"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldRequired="($field->required === 'yes') ? true : false"
                                        :fieldPlaceholder="$field->label">
                                    </x-forms.textarea>
                                @elseif($field->type == 'radio')
                                    <div class="form-group my-3">
                                        <x-forms.label
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            :fieldRequired="($field->required === 'yes') ? true : false">
                                        </x-forms.label>
                                        <div class="d-flex">
                                            @foreach ($field->values as $key => $value)
                                                <x-forms.radio fieldId="optionsRadios{{ $key . $field->id }}"
                                                    :fieldLabel="$value"
                                                    fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                                    :fieldValue="$value" :checked="($key == 0) ? true : false" />
                                            @endforeach
                                        </div>
                                    </div>
                                @elseif($field->type == 'select')
                                    <div class="form-group my-3">
                                        <x-forms.label
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            :fieldRequired="($field->required === 'yes') ? true : false">
                                        </x-forms.label>
                                        {!! Form::select('custom_fields_data[' . $field->name . '_' . $field->id . ']', $field->values, isset($editUser) ? $editUser->custom_fields_data['field_' . $field->id] : '', ['class' => 'form-control select-picker']) !!}
                                    </div>
                                @elseif($field->type == 'date')
                                    <x-forms.datepicker custom="true"
                                        fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldRequired="($field->required === 'yes') ? true : false"
                                        :fieldLabel="$field->label"
                                        fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                        :fieldValue="now()->timezone($global->timezone)->format($global->date_format)"
                                        :fieldPlaceholder="$field->label" />
                                @elseif($field->type == 'checkbox')
                                    <div class="form-group my-3">
                                        <x-forms.label
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            :fieldRequired="($field->required === 'yes') ? true : false">
                                        </x-forms.label>
                                        <div class="d-flex checkbox-{{$field->id}}">
                                            <input type="hidden" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" id="{{$field->name.'_'.$field->id}}">

                                            @foreach ($field->values as $key => $value)
                                                <x-forms.checkbox fieldId="optionsRadios{{ $key . $field->id }}"
                                                    :fieldLabel="$value"
                                                    fieldName="$field->name.'_'.$field->id.'[]'"
                                                    :fieldValue="$value"
                                                    onchange="checkboxChange('checkbox-{{$field->id}}', '{{$field->name.'_'.$field->id}}')"
                                                    :fieldRequired="($field->required === 'yes') ? true : false" />
                                            @endforeach
                                        </div>
                                    </div>
                                @endif

                            </div>
                        @endforeach
                    @endif

                </div>

                <x-form-actions>
                    <x-forms.button-primary id="save-lead-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('tasks.index')" class="border-0">@lang('app.cancel')
                    </x-forms.button-cancel>
                </x-form-actions>

            </div>
        </x-form>

    </div>
</div>


<script src="{{ asset('vendor/jquery/dropzone.min.js') }}"></script>
<script>
    $(document).ready(function() {

        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        quillImageLoad('#note');

        $('#save-lead-form').click(function() {
            var note = document.getElementById('note').children[0].innerHTML;
            document.getElementById('note-text').value = note;

            const url = "{{ route('leads.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-lead-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-lead-form",
                data: $('#save-lead-data-form').serialize(),
                success: function(response) {
                    window.location.href = response.redirectUrl;
                }
            });
        });

        $('body').on('click', '.add-lead-agent', function() {
            var url = '{{ route('lead-agent-settings.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.add-lead-source', function() {
            var url = '{{ route('lead-source-settings.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('body').on('click', '.add-lead-category', function() {
            var url = '{{ route('leadCategory.create') }}';
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('.toggle-other-details').click(function() {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        init(RIGHT_MODAL);
    });

    function checkboxChange(parentClass, id){
        var checkedData = '';
        $('.'+parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData+', '+$(this).val() : $(this).val();
        });
        $('#'+id).val(checkedData);
    }
</script>
