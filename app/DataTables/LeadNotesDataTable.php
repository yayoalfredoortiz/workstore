<?php

namespace App\DataTables;

use App\DataTables\BaseDataTable;
use App\Models\LeadNote;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;

class LeadNotesDataTable extends BaseDataTable
{
    private $editLeadNotePermission;
    private $deleteLeadNotePermission;

    public function __construct()
    {
        parent::__construct();
        $this->editLeadNotePermission = user()->permission('edit_lead_note');
        $this->deleteLeadNotePermission = user()->permission('delete_lead_note');
    }

    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {

        return datatables()
            ->eloquent($query)
            ->addColumn('check', function ($row) {
                return '<input type="checkbox" class="select-table-row" id="datatable-row-' . $row->id . '"  name="datatable_ids[]" value="' . $row->id . '" onclick="dataTableRowCheck(' . $row->id . ')">';
            })
            ->addColumn('action', function ($row) {

                $action = '<div class="task_view">';

                $action .= '<div class="dropdown">
                        <a class="task_view_more d-flex align-items-center justify-content-center dropdown-toggle" type="link"
                            id="dropdownMenuLink-' . $row->id . '" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <i class="icon-options-vertical icons"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenuLink-' . $row->id . '" tabindex="0">';

                if(!in_array('admin', user_roles()) && $row->ask_password == 1) {
                    $action .= '<a href="javascript:;" class="dropdown-item ask-for-password" data-lead-note-id="'.$row->id.'"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }
                else {
                    $action .= '<a href="' . route('lead-notes.show', $row->id) . '" class="openRightModal dropdown-item"><i class="fa fa-eye mr-2"></i>' . __('app.view') . '</a>';
                }


                if ($this->editLeadNotePermission == 'all' || ($this->editLeadNotePermission == 'added' && user()->id == $row->added_by) || ($this->editLeadNotePermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item openRightModal" href="' . route('lead-notes.edit', [$row->id]) . '">
                                <i class="fa fa-edit mr-2"></i>
                                ' . trans('app.edit') . '
                            </a>';
                }

                if ($this->deleteLeadNotePermission == 'all' || ($this->deleteLeadNotePermission == 'added' && user()->id == $row->added_by) || ($this->deleteLeadNotePermission == 'both' && user()->id == $row->added_by)) {
                    $action .= '<a class="dropdown-item delete-table-row" href="javascript:;" data-user-id="' . $row->id . '">
                                <i class="fa fa-trash mr-2"></i>
                                ' . trans('app.delete') . '
                            </a>';
                }

                $action .= '</div>
                    </div>
                </div>';

                return $action;
            })
            ->editColumn('title', function ($row) {
                return ucwords($row->title);
            })
            ->editColumn('type', function ($row) {
                return $row->type == '0' ? __('app.public') : __('app.private');
            })
            ->addIndexColumn()
            ->smart(false)
            ->setRowId(function ($row) {
                return 'row-' . $row->id;
            })
            ->rawColumns(['action', 'check']);
    }

    /**
     * @param LeadNote $model
     * @return LeadNote|\Illuminate\Database\Eloquent\Builder
     */
    public function query(LeadNote $model)
    {
        $request = $this->request();
        $notes = $model->where('lead_id', $request->leadID);

        if (in_array('lead', user_roles())) {
            $notes->where('type', 0);
            $notes->orWhere('is_lead_show', 1);
        }

        if(!is_null($request->searchText)) {
            $notes->where('title', 'like', '%' . request('searchText') . '%');
        }

        return $notes;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('lead-notes-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->orderBy(1)
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            /* ->stateSave(true) */
            ->processing(true)
            ->language(__('app.datatable'))
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["lead-notes-table"].buttons().container()
                    .appendTo("#table-actions")
                }',
                'fnDrawCallback' => 'function( oSettings ) {
                  //
                }',
            ])
            ->buttons(Button::make(['extend' => 'excel', 'text' => '<i class="fa fa-file-export"></i> ' . trans('app.exportExcel')]));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            'check' => [
                'title' => '<input type="checkbox" name="select_all_table" id="select-all-table" onclick="selectAllTable(this)">',
                'exportable' => false,
                'orderable' => false,
                'searchable' => false
            ],
            '#' => ['data' => 'DT_RowIndex', 'orderable' => false, 'searchable' => false, 'visible' => false],
            __('modules.client.noteTitle') => ['data' => 'title', 'name' => 'title', 'title' => __('modules.client.noteTitle')],
            __('modules.client.noteType') => ['data' => 'type', 'name' => 'type', 'title' => __('modules.client.noteType')],
            Column::computed('action', __('app.action'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
                ->addClass('text-right pr-20')
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'lead_note_' . date('YmdHis');
    }

    public function pdf()
    {
        set_time_limit(0);

        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
