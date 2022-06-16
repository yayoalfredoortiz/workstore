<?php

namespace App\Http\Controllers;

use App\Helper\Reply;
use App\Http\Requests\Admin\TaskLabel\StoreRequest;
use App\Models\TaskLabelList;
use Illuminate\Http\Request;

class TaskLabelController extends AccountBaseController
{

    public function __construct()
    {
        parent::__construct();
        $this->pageTitle = 'app.menu.taskLabel';
    }

    public function create()
    {
        $this->taskLabels = TaskLabelList::all();
        return view('tasks.create_label', $this->data);
    }

    public function store(StoreRequest $request)
    {
        abort_403(user()->permission('task_labels') !== 'all');
        $taskLabel = new TaskLabelList();
        $this->storeUpdate($request, $taskLabel);

        $allTaskLabels = TaskLabelList::all();

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {
            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>">' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('messages.taskLabel.addedSuccess'), ['data' => $labels]);
    }

    public function update(Request $request, $id)
    {
        abort_403(user()->permission('task_labels') !== 'all');

        $taskLabel = TaskLabelList::findOrFail($id);
        $this->storeUpdate($request, $taskLabel);

        $allTaskLabels = TaskLabelList::all();

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {
            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>">' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('messages.taskLabel.addedSuccess'), ['data' => $labels]);
    }

    private function storeUpdate($request, $taskLabel)
    {
        $taskLabel->label_name = trim($request->label_name);

        if ($request->has('color')) {
            $taskLabel->color = $request->color;
        }

        $taskLabel->description = str_replace('<p><br></p>', '', trim($request->description));
        $taskLabel->save();

        return $taskLabel;
    }

    public function destroy($id)
    {
        abort_403(user()->permission('task_labels') !== 'all');

        TaskLabelList::destroy($id);

        $allTaskLabels = TaskLabelList::all();

        $labels = '';

        foreach ($allTaskLabels as $key => $value) {
            $labels .= '<option value="' . $value->id . '" data-content="<span class=\'badge badge-secondary\' style=\'background-color: ' . $value->label_color . '\'>' . $value->label_name . '</span>">' . $value->label_name . '</option>';
        }

        return Reply::successWithData(__('messages.taskLabel.addedSuccess'), ['data' => $labels]);
    }

}
