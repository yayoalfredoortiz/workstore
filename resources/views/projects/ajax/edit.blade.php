@php
$addProjectCategoryPermission = user()->permission('manage_project_category');
$addDepartmentPermission = user()->permission('add_department');
$addClientPermission = user()->permission('add_clients');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-project-data-form" method="PUT">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.projects.projectInfo')</h4>
                <div class="row p-20">
                    <div class="col-lg-3 col-md-6">
                        <x-forms.text class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectName')"
                            fieldName="project_name" fieldRequired="true" fieldId="project_name"
                            :fieldValue="$project->project_name" :fieldPlaceholder="__('placeholders.project')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.datepicker fieldId="start_date" fieldRequired="true"
                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                            :fieldValue="$project->start_date->format($global->date_format)"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-3" id="deadlineBox">
                        <x-forms.datepicker fieldId="deadline" fieldRequired="true"
                            :fieldLabel="__('modules.projects.deadline')" fieldName="deadline"
                            :fieldValue="($project->deadline ? $project->deadline->format($global->date_format) : '')"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="without_deadline"
                                    :checked="($project->deadline == null) ? true : false"
                                    :fieldLabel="__('modules.projects.withoutDeadline')" fieldName="without_deadline" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.projects.projectCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="project_category_id"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option @if ($project->category_id == $category->id) selected @endif value="{{ $category->id }}">
                                        {{ ucwords($category->category_name) }}</option>
                                @endforeach
                            </select>

                            @if ($addProjectCategoryPermission == 'all' || $addProjectCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="addProjectCategory" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="department" :fieldLabel="__('app.department')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="team_id" id="employee_department"
                                data-live-search="true">
                                <option value="">--</option>
                                @foreach ($teams as $team)
                                    <option @if ($project->team_id === $team->id) selected @endif value="{{ $team->id }}">
                                        {{ ucwords($team->team_name) }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addDepartmentPermission == 'all' || $addDepartmentPermission == 'added')
                                <x-slot name="append">
                                    <button id="department-setting" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-4">
                        <x-forms.label class="my-3" fieldId="client_id" :fieldLabel="__('app.client')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="client_id" id="client_id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($clients as $client)
                                    <option
                                        data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $client->image_url }}' ></div> {{ ucfirst($client->name) }}"
                                        @if ($project->client_id == $client->id)
                                        selected

                                @endif
                                value="{{ $client->id }}">{{ ucwords($client->name) }}</option>
                                @endforeach
                            </select>

                            @if ($addClientPermission == 'all' || $addClientPermission == 'added')
                                <x-slot name="append">
                                    <button id="add-client" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="project_summary"
                                :fieldLabel="__('modules.projects.projectSummary')">
                            </x-forms.label>
                            <div id="project_summary">{!! $project->project_summary !!}</div>
                            <textarea name="project_summary" id="project_summary-text"
                                class="d-none">{!! $project->project_summary !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-6">
                        <div class="form-group my-3">
                            <x-forms.label class="my-3" fieldId="notes" :fieldLabel="__('modules.projects.note')">
                            </x-forms.label>
                            <div id="notes">{!! $project->notes !!}</div>
                            <textarea name="notes" id="notes-text" class="d-none">{!! $project->notes !!}</textarea>
                        </div>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <x-forms.select fieldId="project_status"
                            :fieldLabel="__('app.project') . ' ' . __('app.status')" fieldName="status" search="true">
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-dark-grey f-15'></i> {{ __('app.notStarted') }}"
                                @if ($project->status == 'not started')
                                selected @endif
                                value="not started">@lang('app.notStarted')
                            </option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-blue f-15'></i> {{ __('app.inProgress') }}"
                                @if ($project->status == 'in progress')
                                selected @endif
                                value="in progress">@lang('app.inProgress')
                            </option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-yellow f-15'></i> {{ __('app.onHold') }}"
                                @if ($project->status == 'on hold') selected
                                @endif
                                value="on hold">@lang('app.onHold')
                            </option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-red f-15'></i> {{ __('app.canceled') }}"
                                @if ($project->status == 'canceled') selected
                                @endif
                                value="canceled">@lang('app.canceled')
                            </option>
                            <option
                                data-content="<i class='fa fa-circle mr-1 text-dark-green f-15'></i> {{ __('app.finished') }}"
                                @if ($project->status == 'finished') selected
                                @endif
                                value="finished">@lang('app.finished')
                            </option>
                        </x-forms.select>
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <x-forms.range class="mr-0 mr-lg-2 mr-md-2"
                            :disabled="($project->calculate_task_progress == 'true' ? 'true' : 'false')"
                            :fieldLabel="__('modules.projects.projectCompletionStatus')" fieldName="completion_percent"
                            fieldId="completion_percent" :fieldValue="$project->completion_percent" />
                    </div>

                    <div class="col-md-12 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="calculate-task-progress"
                                    :checked="($project->calculate_task_progress == 'true') ? true : false"
                                    :fieldLabel="__('modules.projects.calculateTasksProgress')"
                                    fieldName="calculate_task_progress" />
                            </div>
                        </div>
                    </div>


                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    @lang('modules.client.clientOtherDetails')</h4>

                <div class="row p-20">
                    <div class="col-lg-4">
                        <x-forms.select fieldId="currency_id" :fieldLabel="__('modules.invoices.currency')"
                            fieldName="currency_id" search="true">
                            @foreach ($currencies as $currency)
                                <option @if ($currency->id == $project->currency_id) selected @endif value="{{ $currency->id }}">
                                    {{ $currency->currency_symbol . ' (' . $currency->currency_code . ')' }}
                                </option>
                            @endforeach
                        </x-forms.select>
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.projectBudget')"
                            fieldName="project_budget" fieldId="project_budget" :fieldValue="$project->project_budget"
                            :fieldPlaceholder="__('placeholders.price')" />
                    </div>

                    <div class="col-lg-4 col-md-6">
                        <x-forms.number class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('modules.projects.hours_allocated')"
                            fieldName="hours_allocated" fieldId="hours_allocated"
                            :fieldValue="$project->hours_allocated" :fieldPlaceholder="__('placeholders.hourEstimate')" />
                    </div>

                    <div class="col-md-6 col-lg-4">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="manual_timelog"
                                    :fieldLabel="__('modules.projects.manualTimelog')" :checked="($project->manual_timelog
                                    == 'enable') ? 'true' : 'false'" fieldName="manual_timelog" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-4 d-none" id="clientNotification">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox fieldId="client_task_notification" :checked="($project->allow_client_notification
                                == 'enable') ? 'true' : 'false'"
                                    :fieldLabel="__('modules.projects.clientTaskNotification')"
                                    fieldName="client_task_notification" />
                            </div>
                        </div>
                    </div>

                    @if ($editPermission == 'all')
                        <div class="col-lg-3 col-md-6">
                            <x-forms.select fieldId="added_by" :fieldLabel="__('modules.tasks.assignTo')"
                                fieldName="added_by">
                                <option value="">--</option>
                                @foreach ($employees as $item)
                                    <option
                                        @if ($project->added_by == $item->id)  selected @endif

                                        data-content="<div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $item->image_url }}' ></div> {{ ucfirst($item->name) }}"
                                        value="{{ $item->id }}">{{ ucwords($item->name) }}</option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    @endif



                </div>

                @if (isset($fields) && count($fields) > 0)
                    <div class="row p-20">
                        @foreach ($fields as $field)
                            <div class="col-md-4">
                                <div class="form-group">
                                    @if ($field->type == 'text')
                                        <x-forms.text
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldPlaceholder="$field->label"
                                            :fieldRequired="($field->required == 'yes') ? 'true' : 'false'"
                                            :fieldValue="$project->custom_fields_data['field_'.$field->id] ?? ''">
                                        </x-forms.text>
                                    @elseif($field->type == 'password')
                                        <x-forms.password
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldPlaceholder="$field->label"
                                            :fieldRequired="($field->required === 'yes') ? true : false"
                                            :fieldValue="$project->custom_fields_data['field_'.$field->id] ?? ''">
                                        </x-forms.password>
                                    @elseif($field->type == 'number')
                                        <x-forms.number
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldPlaceholder="$field->label"
                                            :fieldRequired="($field->required === 'yes') ? true : false"
                                            :fieldValue="$project->custom_fields_data['field_'.$field->id] ?? ''">
                                        </x-forms.number>
                                    @elseif($field->type == 'textarea')
                                        <x-forms.textarea :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldRequired="($field->required === 'yes') ? true : false"
                                            :fieldPlaceholder="$field->label"
                                            :fieldValue="$project->custom_fields_data['field_'.$field->id] ?? ''">
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
                                                        :fieldValue="$value"
                                                        :checked="(isset($project) && $project->custom_fields_data['field_'.$field->id] == $value) ? true : false" />
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
                                            {!! Form::select('custom_fields_data[' . $field->name . '_' . $field->id . ']', $field->values, isset($project) ? $project->custom_fields_data['field_' . $field->id] : '', ['class' => 'form-control select-picker']) !!}
                                        </div>
                                    @elseif($field->type == 'date')
                                        <x-forms.datepicker custom="true"
                                            fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldRequired="($field->required === 'yes') ? true : false"
                                            :fieldLabel="$field->label"
                                            fieldName="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                            :fieldValue="($project->custom_fields_data['field_'.$field->id] != '') ? \Carbon\Carbon::parse($project->custom_fields_data['field_'.$field->id])->format($global->date_format) : \Carbon\Carbon::now()->format($global->date_format)"
                                            :fieldPlaceholder="$field->label" />
                                    @elseif($field->type == 'checkbox')
                                        <div class="form-group my-3">
                                            <x-forms.label
                                                fieldId="custom_fields_data[{{ $field->name . '_' . $field->id }}]"
                                                :fieldLabel="$field->label"
                                                :fieldRequired="($field->required === 'yes') ? true : false">
                                            </x-forms.label>
                                            <div class="d-flex checkbox-{{$field->id}}">
                                                <input type="hidden" name="custom_fields_data[{{$field->name.'_'.$field->id}}]" id="{{$field->name.'_'.$field->id}}" value="{{$project->custom_fields_data['field_'.$field->id]}}">

                                                @foreach ($field->values as $key => $value)
                                                    <x-forms.checkbox fieldId="optionsRadios{{ $key . $field->id }}"
                                                        :fieldLabel="$value"
                                                        fieldName="$field->name.'_'.$field->id.'[]'"
                                                        :fieldValue="$value"
                                                        :fieldRequired="($field->required === 'yes') ? true : false"
                                                        onchange="checkboxChange('checkbox-{{$field->id}}', '{{$field->name.'_'.$field->id}}')"
                                                        :checked="$project->custom_fields_data['field_'.$field->id] != '' && in_array($value ,explode(', ', $project->custom_fields_data['field_'.$field->id]))"
                                                        />
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-control-focus"> </div>
                                    <span class="help-block"></span>

                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif

                <x-form-actions>
                    <x-forms.button-primary id="save-project-form" class="mr-3" icon="check">@lang('app.save')
                    </x-forms.button-primary>
                    <x-forms.button-cancel :link="route('projects.index')" class="border-0">@lang('app.cancel')
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

        $("#selectEmployee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        quillImageLoad('#project_summary');
        quillImageLoad('#notes');

        const dp1 = datepicker('#start_date', {
            position: 'bl',
            dateSelected: new Date("{{ str_replace('-', '/', $project->start_date) }}"),
            onSelect: (instance, date) => {
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#deadline', {
            position: 'bl',
            dateSelected: new Date(
                "{{ $project->deadline ? str_replace('-', '/', $project->deadline) : str_replace('-', '/', now($global->timezone)) }}"),
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        @if ($project->deadline == null)
            $('#deadlineBox').hide();
        @endif

        $('#without_deadline').click(function() {
            var check = $('#without_deadline').is(":checked") ? true : false;
            if (check == true) {
                $('#deadlineBox').hide();
            } else {
                $('#deadlineBox').show();
            }
        });

        $('#save-project-form').click(function() {
            var note = document.getElementById('project_summary').children[0].innerHTML;
            document.getElementById('project_summary-text').value = note;

            var note = document.getElementById('notes').children[0].innerHTML;
            document.getElementById('notes-text').value = note;

            const url = "{{ route('projects.update', $project->id) }}";

            $.easyAjax({
                url: url,
                container: '#save-project-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-project-form",
                data: $('#save-project-data-form').serialize(),
                success: function(response) {
                    if (response.status == 'success') {
                        window.location.href = response.redirectUrl;
                    }
                }
            });
        });

        $('#addProjectCategory').click(function() {
            const url = "{{ route('projectCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#department-setting').click(function() {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function() {
            $('#clientNotification').toggleClass('d-none');
        });

        $('#add-client').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('clients.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#add-employee').click(function() {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function(response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#calculate-task-progress').change(function() {
            if ($(this).is(':checked')) {
                $('#completion_percent').attr('disabled', 'true');
            } else {
                $('#completion_percent').removeAttr('disabled');
            }
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
