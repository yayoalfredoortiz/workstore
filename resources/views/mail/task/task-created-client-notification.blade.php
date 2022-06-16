@component('mail::message')
# @lang('modules.tasks.newTask')

@lang('email.newTask.subject')

<h5>@lang('app.task') @lang('app.details')</h5>

@component('mail::text', ['text' => $content])

@endcomponent


@lang('email.regards'),<br>
{{ config('app.name') }}
@endcomponent
