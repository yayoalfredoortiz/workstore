@php
$addTaskCategoryPermission = user()->permission('add_task_category');
$addEmployeePermission = user()->permission('add_employees');
$addTaskFilePermission = user()->permission('add_task_files');
$addTaskPermission = user()->permission('add_tasks');
@endphp

<link rel="stylesheet" href="{{ asset('vendor/css/dropzone.min.css') }}">

<div class="row">
    <div class="col-sm-12">
        <x-form id="save-task-data-form">
            <div class="add-client bg-white rounded">
                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-bottom-grey">
                    @lang('modules.tasks.taskInfo')</h4>
                <div class="row p-20">
                    <div class="col-lg-6 col-md-6">
                        <x-forms.text :fieldLabel="__('app.title')" fieldName="heading" fieldRequired="true"
                            fieldId="heading" :fieldPlaceholder="__('placeholders.task')" />
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <x-forms.label class="my-3" fieldId="category_id"
                            :fieldLabel="__('modules.tasks.taskCategory')">
                        </x-forms.label>
                        <x-forms.input-group>
                            <select class="form-control select-picker" name="category_id" id="task_category_id"
                                data-live-search="true" data-size="8">
                                <option value="">--</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}">{{ ucwords($category->category_name) }}
                                    </option>
                                @endforeach
                            </select>

                            @if ($addTaskCategoryPermission == 'all' || $addTaskCategoryPermission == 'added')
                                <x-slot name="append">
                                    <button id="create_task_category" type="button"
                                        class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                </x-slot>
                            @endif
                        </x-forms.input-group>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        @if (isset($project) && !is_null($project))
                            <input type="hidden" name="project_id" id="project_id" value="{{ $project->id }}">
                            <x-forms.text :fieldLabel="__('app.project')" fieldName="projectName" fieldId="projectName"
                                :fieldValue="$project->project_name" fieldReadOnly="true" />
                        @else
                            <x-forms.select fieldId="project_id" fieldName="project_id" :fieldLabel="__('app.project')"
                                search="true">
                                <option value="">--</option>
                                @foreach ($projects as $data)
                                    <option @if (isset($project) && $project->id == $data->id) selected @endif value="{{ $data->id }}">
                                        {{ ucwords($data->project_name) }}
                                    </option>
                                @endforeach
                            </x-forms.select>
                        @endif
                    </div>

                    <div class="col-md-5 col-lg-4">
                        <x-forms.datepicker fieldId="task_start_date" fieldRequired="true"
                            :fieldLabel="__('modules.projects.startDate')" fieldName="start_date"
                            :fieldValue="\Carbon\Carbon::now($global->timezone)->format($global->date_format)"
                            :fieldPlaceholder="__('placeholders.date')" />
                    </div>

                    <div class="col-md-5 col-lg-4 dueDateBox">
                        <x-forms.datepicker fieldId="due_date" fieldRequired="true" :fieldLabel="__('app.dueDate')"
                            fieldName="due_date" :fieldPlaceholder="__('placeholders.date')"
                            :fieldValue="\Carbon\Carbon::now($global->timezone)->format($global->date_format)" />
                    </div>
                    <div class="col-md-2 col-lg-2 pt-5">
                        <x-forms.checkbox class="mr-0 mr-lg-2 mr-md-2" :fieldLabel="__('app.withoutDueDate')"
                                          fieldName="without_duedate" fieldId="without_duedate" fieldValue="yes" />
                    </div>

                    <div class="col-md-12 col-lg-12">
                    </div>

                    <div class="col-md-12 col-lg-8">

                        {{-- If add task permission is not given to employee but user can create task for itself is enabled from task settings then employee can create task for itself --}}
                        @if ($addTaskPermission == 'all' || (!is_null($project) && $project->project_admin == user()->id))
                            <div class="form-group my-3">
                                <x-forms.label fieldId="selectAssignee" fieldRequired="true"
                                    :fieldLabel="__('modules.tasks.assignTo')">
                                </x-forms.label>
                                <x-forms.input-group>
                                    <select class="form-control multiple-users" multiple name="user_id[]"
                                        id="selectAssignee" data-live-search="true" data-size="8">
                                        @foreach ($employees as $item)
                                            <option @if (isset($defaultAssignee) && $defaultAssignee == $item->id) selected @endif
                                                data-content="<span class='badge badge-pill badge-light border'><div class='d-inline-block mr-1'><img class='taskEmployeeImg rounded-circle' src='{{ $item->image_url }}' ></div> {{ ucfirst($item->name) }}{{ (user() && user()->id == $item->id) ? '<span class="badge badge-secondary">' . __('app.itsYou') . '</span>' : '' }}</span>"
                                                value="{{ $item->id }}">{{ ucwords($item->name) }}</option>
                                        @endforeach
                                    </select>

                                    <x-slot name="preappend">
                                        <button id="assign-self" type="button"
                                            class="btn btn-outline-secondary border-grey" data-toggle="tooltip"
                                            data-original-title="@lang('modules.tasks.assignMe')">
                                            <img src="{{ user()->image_url }}" width="23"
                                                class="img-fluid rounded-circle">
                                        </button>
                                    </x-slot>

                                    @if ($addEmployeePermission == 'all' || $addEmployeePermission == 'added')
                                        <x-slot name="append">
                                            <button id="add-employee" type="button"
                                                class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                        </x-slot>
                                    @endif
                                </x-forms.input-group>
                            </div>
                        @elseif ($addTaskPermission == 'added')
                            <input type="hidden" name="user_id[]" value="{{ user()->id }}">
                            <x-forms.text :fieldLabel="__('modules.tasks.assignTo')" fieldName="user_name"
                                fieldRequired="true" fieldId="selectAssignee" fieldReadOnly="true"
                                :fieldPlaceholder="__('placeholders.name')" :fieldValue="user()->name" />
                        @endif
                    </div>

                    <div class="col-md-12">
                        <div class="form-group my-3">
                            <x-forms.label fieldId="description" :fieldLabel="__('app.description')">
                            </x-forms.label>
                            <div id="description"></div>
                            <textarea name="description" id="description-text" class="d-none"></textarea>
                        </div>
                    </div>

                </div>

                <h4 class="mb-0 p-20 f-21 font-weight-normal text-capitalize border-top-grey">
                    <a href="javascript:;" class="text-dark toggle-other-details"><i class="fa fa-chevron-down"></i>
                        @lang('modules.client.clientOtherDetails')</a>
                </h4>

                <div class="row p-20 d-none" id="other-details">

                    <div class="col-sm-12">
                        <div class="row">

                            <div class="col-md-12 col-lg-4">
                                <div class="form-group my-3">
                                    <x-forms.label fieldId="task_labels" :fieldLabel="__('app.label')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <select class="select-picker form-control" multiple name="task_labels[]"
                                            id="task_labels" data-live-search="true" data-size="8">
                                            @foreach ($taskLabels as $label)
                                                <option
                                                    data-content="<span class='badge badge-secondary' style='background-color: {{ $label->label_color }}'>{{ $label->label_name }}</span>"
                                                    value="{{ $label->id }}">{{ $label->label_name }}</option>
                                            @endforeach
                                        </select>

                                        @if (user()->permission('task_labels') == 'all')
                                            <x-slot name="append">
                                                <button id="createTaskLabel" type="button"
                                                    class="btn btn-outline-secondary border-grey">@lang('app.add')</button>
                                            </x-slot>
                                        @endif
                                    </x-forms.input-group>
                                </div>
                            </div>

                            <div class="col-md-12 col-lg-4">
                                <x-forms.select fieldName="milestone_id" fieldId="milestone_id"
                                    :fieldLabel="__('modules.projects.milestones')">
                                    <option value="">--</option>
                                    @forelse ($milestones as $milestone)
                                        <option value="{{ $milestone->id }}">
                                            {{ $milestone->milestone_title }}</option>
                                    @empty
                                    @endforelse

                                </x-forms.select>
                            </div>

                            @if (user()->permission('change_status') == 'all')
                                <div class="col-lg-3 col-md-6">
                                    <x-forms.select fieldId="board_column_id" :fieldLabel="__('app.status')"
                                        fieldName="board_column_id" search="true">
                                        @foreach ($taskboardColumns as $item)
                                            <option @if ($columnId == $item->id) selected @elseif (global_setting()->default_task_status == $item->id) selected @endif value="{{ $item->id }}">
                                                {{ $item->slug == 'completed' || $item->slug == 'incomplete' ? __('app.' . $item->slug) : $item->column_name }}
                                            </option>
                                        @endforeach
                                    </x-forms.select>
                                </div>
                            @endif

                            <div class="col-lg-3 col-md-6">
                                <x-forms.select fieldId="priority" :fieldLabel="__('modules.tasks.priority')"
                                    fieldName="priority">
                                    <option value="high">@lang('modules.tasks.high')</option>
                                    <option selected value="medium">@lang('modules.tasks.medium')</option>
                                    <option value="low">@lang('modules.tasks.low')</option>
                                </x-forms.select>
                            </div>
                        </div>
                    </div>


                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.makePrivate')" fieldName="is_private"
                                    fieldId="is_private" :popover="__('modules.tasks.privateInfo')" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.billable')" :checked="true"
                                    fieldName="billable" fieldId="billable"
                                    :popover="__('modules.tasks.billableInfo')" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3">
                        <div class="form-group">
                            <div class="d-flex mt-5">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.setTimeEstimate')"
                                    fieldName="set_time_estimate" fieldId="set_time_estimate" />
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 d-none" id="set-time-estimate-fields">
                        <div class="form-group mt-5">
                            <input type="number" min="0" value="0" class="w-25 border rounded p-2 height-35 f-14"
                                name="estimate_hours">
                            @lang('app.hrs')
                            &nbsp;&nbsp;
                            <input type="number" min="0" value="0" name="estimate_minutes"
                                class="w-25 height-35 f-14 border rounded p-2">
                            @lang('app.mins')
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.events.repeat')" fieldName="repeat"
                                    fieldId="repeat-task" />
                            </div>
                        </div>

                        <div class="form-group my-3 d-none" id="repeat-fields">
                            <div class="row">
                                <div class="col-md-6 mt-3">
                                    <x-forms.label fieldId="repeatEvery" fieldRequired="true"
                                        :fieldLabel="__('modules.events.repeatEvery')">
                                    </x-forms.label>
                                    <x-forms.input-group>
                                        <input type="number" min="1" value="1" name="repeat_count"
                                            class="form-control f-14">

                                        <x-slot name="append">
                                            <select name="repeat_type" class="select-picker form-control">
                                                <option value="day">@lang('app.day')</option>
                                                <option value="week">@lang('app.week')</option>
                                                <option value="month">@lang('app.month')</option>
                                                <option value="year">@lang('app.year')</option>
                                            </select>
                                        </x-slot>
                                    </x-forms.input-group>
                                </div>
                                <div class="col-md-6">
                                    <x-forms.number :fieldLabel="__('modules.events.cycles')" fieldName="repeat_cycles"
                                        fieldRequired="true" fieldValue="1" minValue="1" fieldId="repeat_cycles"
                                        :fieldPlaceholder="__('modules.tasks.cyclesToolTip')"
                                        :popover="__('modules.tasks.cyclesToolTip')" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="form-group my-3">
                            <div class="d-flex">
                                <x-forms.checkbox :fieldLabel="__('modules.tasks.dependent')" fieldName="dependent"
                                    fieldId="dependent-task" />
                            </div>
                        </div>

                        <div class="d-none" id="dependent-fields">
                            <x-forms.select fieldId="dependent_task_id" :fieldLabel="__('modules.tasks.dependentTask')"
                                fieldName="dependent_task_id" search="true">
                                <option value="">--</option>
                                @foreach ($allTasks as $item)
                                    <option value="{{ $item->id }}">{{ $item->heading }} (@lang('app.dueDate'):
                                        @if(!is_null($item->due_date)) {{ $item->due_date->format($global->date_format) }} @else - @endif )
                                    </option>
                                @endforeach
                            </x-forms.select>
                        </div>
                    </div>

                    @if ($addTaskFilePermission == 'all' || $addTaskFilePermission == 'added')
                        <div class="col-lg-12">
                            <x-forms.file-multiple class="mr-0 mr-lg-2 mr-md-2"
                                :fieldLabel="__('app.add') . ' ' .__('app.file')" fieldName="file"
                                fieldId="task-file-upload-dropzone" />
                            <input type="hidden" name="image_url" id="image_url">
                        </div>
                        <input type="hidden" name="taskID" id="taskID">
                        <input type="hidden" name="addedFiles" id="addedFiles">
                    @endif

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
                    <x-forms.button-primary class="mr-3" id="save-task-form" icon="check">@lang('app.save')
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
        var add_task_files = "{{ $addTaskFilePermission }}";

        @if (isset($project) && !is_null($project))
            $('#project_id').attr("readonly", true);
        @endif

        if ($('.custom-date-picker').length > 0) {
            datepicker('.custom-date-picker', {
                position: 'bl',
                ...datepickerConfig
            });
        }

        if (add_task_files == "all" || add_task_files == "added") {

            Dropzone.autoDiscover = false;
            //Dropzone class
            taskDropzone = new Dropzone("div#task-file-upload-dropzone", {
                dictDefaultMessage: "{{ __('app.dragDrop') }}",
                url: "{{ route('task-files.store') }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                paramName: "file",
                maxFilesize: 10,
                maxFiles: 10,
                autoProcessQueue: false,
                uploadMultiple: true,
                addRemoveLinks: true,
                parallelUploads: 10,
                acceptedFiles: dropzoneFileAllow,
                init: function () {
                    taskDropzone = this;
                }
            });
            taskDropzone.on('sending', function (file, xhr, formData) {
                var ids = $('#taskID').val();
                formData.append('task_id', ids);
                $.easyBlockUI();
            });
            taskDropzone.on('uploadprogress', function () {
                $.easyBlockUI();
            });
            taskDropzone.on('completemultiple', function () {
                window.location.href = "{{ route('tasks.index') }}"
            });
        }


        $("#selectAssignee").selectpicker({
            actionsBox: true,
            selectAllText: "{{ __('modules.permission.selectAll') }}",
            deselectAllText: "{{ __('modules.permission.deselectAll') }}",
            multipleSeparator: " ",
            selectedTextFormat: "count > 8",
            countSelectedText: function(selected, total) {
                return selected + " {{ __('app.membersSelected') }} ";
            }
        });

        quillImageLoad('#description');


        const dp1 = datepicker('#task_start_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                if (typeof dp2.dateSelected !== 'undefined' && dp2.dateSelected.getTime() < date
                    .getTime()) {
                    dp2.setDate(date, true)
                }
                if (typeof dp2.dateSelected === 'undefined') {
                    dp2.setDate(date, true)
                }
                dp2.setMin(date);
            },
            ...datepickerConfig
        });

        const dp2 = datepicker('#due_date', {
            position: 'bl',
            onSelect: (instance, date) => {
                dp1.setMax(date);
            },
            ...datepickerConfig
        });

        $('#save-task-data-form').on('change', '#project_id', function () {
            let id = $(this).val();
            if (id == '') {
                id = 0;
            }
            let url = "{{ route('milestones.by_project', ':id') }}";
            url = url.replace(':id', id);

            $.easyAjax({
                url: url,
                container: '#save-task-data-form',
                type: "GET",
                blockUI: true,
                success: function (response) {
                    if (response.status == 'success') {
                        $('#milestone_id').html(response.data);
                        $('#milestone_id').selectpicker('refresh');
                    }
                }
            });
        });

        $('#save-task-data-form').on('change', '#project_id', function () {
            let id = $(this).val();
            if (id === '') {
                id = 0;
            }
            let url = "{{ route('projects.members', ':id') }}";
            url = url.replace(':id', id);
            $.easyAjax({
                url: url,
                type: "GET",
                container: '#save-task-data-form',
                blockUI: true,
                redirect: true,
                success: function (data) {
                    $('#selectAssignee').html(data.data);
                    $('#selectAssignee').selectpicker('refresh');
                }
            })
        });

        $('#save-task-form').click(function () {
            let note = document.getElementById('description').children[0].innerHTML;
            document.getElementById('description-text').value = note;

            const url = "{{ route('tasks.store') }}";

            $.easyAjax({
                url: url,
                container: '#save-task-data-form',
                type: "POST",
                disableButton: true,
                blockUI: true,
                buttonSelector: "#save-task-form",
                data: $('#save-task-data-form').serialize(),
                success: function (response) {
                    if (response.status === 'success') {
                        if (taskDropzone.getQueuedFiles().length > 0) {
                            taskID = response.taskID;
                            $('#taskID').val(response.taskID);
                            taskDropzone.processQueue();
                        } else {
                            window.location.href = response.redirectUrl;
                        }
                    }
                }
            });
        });

        $('#assign-self').click(function () {
            $('#selectAssignee').val('{{ $user->id }}');
            $('#selectAssignee').selectpicker('refresh');
        });

        $('#without_duedate').click(function () {
            $('.dueDateBox').toggle();
        });

        $('#create_task_category').click(function () {
            const url = "{{ route('taskCategory.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#department-setting').click(function () {
            const url = "{{ route('departments.create') }}";
            $(MODAL_LG + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_LG, url);
        });

        $('#client_view_task').change(function () {
            $('#clientNotification').toggleClass('d-none');
        });

        $('#set_time_estimate').change(function () {
            $('#set-time-estimate-fields').toggleClass('d-none');
        });

        $('#repeat-task').change(function () {
            $('#repeat-fields').toggleClass('d-none');
        });

        $('#dependent-task').change(function () {
            $('#dependent-fields').toggleClass('d-none');
        });

        $('.toggle-other-details').click(function () {
            $(this).find('svg').toggleClass('fa-chevron-down fa-chevron-up');
            $('#other-details').toggleClass('d-none');
        });

        $('#createTaskLabel').click(function () {
            const url = "{{ route('task-label.create') }}";
            $(MODAL_XL + ' ' + MODAL_HEADING).html('...');
            $.ajaxModal(MODAL_XL, url);
        });

        $('#add-project').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('projects.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        $('#add-employee').click(function () {
            $(MODAL_XL).modal('show');

            const url = "{{ route('employees.create') }}";

            $.easyAjax({
                url: url,
                blockUI: true,
                container: MODAL_XL,
                success: function (response) {
                    if (response.status == "success") {
                        $(MODAL_XL + ' .modal-body').html(response.html);
                        $(MODAL_XL + ' .modal-title').html(response.title);
                        init(MODAL_XL);
                    }
                }
            });
        });

        init(RIGHT_MODAL);
    });

    function checkboxChange(parentClass, id) {
        let checkedData = '';
        $('.' + parentClass).find("input[type= 'checkbox']:checked").each(function () {
            checkedData = (checkedData !== '') ? checkedData + ', ' + $(this).val() : $(this).val();
        });
        $('#' + id).val(checkedData);
    }
</script>
