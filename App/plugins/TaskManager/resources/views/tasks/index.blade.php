@extends('layouts.app')
@section('breadcrumbs', Breadcrumbs::render('tm_tasks.manage'))

@section('content')
    <div class="card responsive">
        <table class="table datatable-ajax table-responsive task-table" id="dataTable">
            <thead>
            <tr>
                <th><input type="checkbox"id="selectAll"></th>
                <th>Name</th>
                <th>Creator</th>
                <th>@lang('ui.assignee')</th>
                <th>Status</th>
                <th>Priority</th>
                <th>Delivery</th>
                <th>Reminder</th>
                <th>Created</th>
                <th>Updated</th>
                <th>Control</th>
            </tr>
            </thead>
        </table>

        <div class="modal fade" id="bulk-assign-modal" role="dialog">
            <div class="modal-dialog modal-dialog-centered modal-sm">
                <div class="modal-content">
                    <div class="modal-body">
                        <input type="hidden" name="customer">
                        <div class="row justify-content-center align-self-center">
                            <div class="col-9">
                                <select name="assigner_id" class="form-control ml-1">
                                    <option value="">@lang('ui.assignee')</option>
                                    @foreach($assignees as $assignee)
                                        <option value="{{ $assignee->id }}">{{ $assignee->username }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-3">
                                <button type="button" class="btn btn-success" id="bulk-assign">{{__('ui.save')}}</button>
                                <button type="button" class="close pull-right" data-dismiss="modal">
                                    <span class="align-middle">&times;</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('footer')
    <script>
        var table = null;
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $(function () {
            // Highlighting rows and columns on mouseover
            table = $('.datatable-ajax').DataTable({
                "language": {
                    searchPlaceholder: '{{__('ui.form.search')}}',
                    lengthMenu: '_MENU_ {{ ucwords(__('ui.table.entries')) }}',
                    info: '{{__('ui.table.showing')}} _START_ {{__('ui.table.to')}} _END_ {{__('ui.table.of')}} _TOTAL_ {{__('ui.table.entries')}}',
                    paginate: {
                        'previous': '{{__('ui.table.previous')}}',
                        'next': '{{__('ui.table.next')}}'
                    }
                },
                processing: true,
                serverSide: true,
                createdRow: function(row, data, dataIndex){
                    $('td:eq(10)', row).css('min-width', '130px');
                },
                ajax: {
                    url: window.location.href,
                    data: function (d) {
                        d['creator'] = $('#creator').val();
                        d['assigned_to'] = $('#assigned_to').val();
                        d['status'] = $('#status').val();
                        d['priority'] = $('#priority').val();
                        d["created_at"] = $("#date-filter1").val()
                        d["updated_at"] = $("#date-filter2").val()
                        d['creators'] = @json($creators->pluck("id")->toArray() ?? []);
                    },
                },
                order: [[8, "desc"]],
                columns: [
                    { searchable: false, sortable: false, orderable: false,
                        visible: @json(auth()->user()->can('tasks.modify') && !auth()->user()->isTmMember()),
                        render: function (data, type, obj, meta) {
                            if(@json(auth()->user()->id) == obj.created_by || (['super_admin']).includes(@json(auth()->user()->type)) || obj.user.type == 'tm_member'){
                                return "<input type='checkbox' name='select-" + obj.id + "' value=" + obj.id + ">";
                            }
                            else{
                                return "<input type='checkbox' name='select-" + obj.id + "' value=" + obj.id + " disabled>";
                            }
                        }
                    },
                    { name: "title",data: "title",
                        render: function (data, type, obj, meta) {
                            return "<a href='" + route('task-manager.tasks.show', { id: obj.id }) + "'><span class='title' id='title-"+ obj.id +"' data-id='"+ obj.id +"'>" + obj.title + "</span></a>";
                        }
                    },
                    { name: "user.username",data: "user.username", defaultContent:"", sortable: false},
                    { name: "assigner.username",data: "assigner.username", defaultContent:"", sortable: false},
                    { name: "status.name",data: "task_status", defaultContent:""},
                    { name: "priority", defaultContent:"",
                        render: function (data, type, obj, meta) {
                            let priorities = {
                                    "0": "Urgent",
                                    "1": "Blocker",
                                    "2": "Major",
                                    "3": "Critical",
                                    "4": "Minor",
                                    "5": "Trivial"
                            }

                            return "<span class='task-status'> <img src='/backend_assets/assets/img/" + obj.priority + ".svg' width = '16' height = '16'/>" + priorities[obj.priority] + "</span>";
                        }
                    },
                    { name: "delivery_date", data: "delivery_date" },
                    { name: "reminder", data: "reminder"},
                    { name: "created_at", data: "created_at" },
                    { name: "updated_at", data: "updated_at" },
                    {name: 'actions', searchable: false, sortable: false,
                        render: function (data, type, obj, meta) {
                            let links = ""

                            if(@json(auth()->user()->id) == obj.created_by || (['dealer', 'admin', 'super_admin']).includes(@json(auth()->user()->type))){
                                links +='<a href="' + route('task-manager.tasks.edit', {
                                    id: obj.id,
                                }) + '" class="btn btn-sm btn-primary"><i class="icon-pencil"></i></a>';
                            }
                            if(@json(auth()->user()->id) == obj.created_by || (['super_admin']).includes(@json(auth()->user()->type))){
                                links += '<a data-href="' + route('task-manager.tasks.destroy', {
                                    id: obj.id,
                                }) + '" id="remove-task" class="btn btn-sm btn-danger text-white ml-1"><i class="icon-trash"></i></a>';
                            }

                            return "<div>" + links +"</div>";
                        }
                    },
                ],

            });

            $('.datatable-ajax').css('min-height', '300px');
            if(@json(auth()->user()->can('tasks.modify') )){
                $('<a href="' + route('task-manager.tasks.create') + '" class="btn btn-info float-xl-left float-lg-left float-md-left float-sm-none mr-2 mb-3">+ {{ __('ui.new') }}</a>').insertBefore('.dataTables_filter');

                $(`<div style="max-width: 250px; min-width: 170px;" class = "float-left mr-2"><select name="creator" id="creator" class="form-control ml-1 datatable-reload" style="width:150px; display:inline-block;">
                        <option value="">@lang('task.creator')</option>
                        @foreach($creators as $creator)
                            <option value="{{ $creator->id }}">{{ $creator->username }}</option>
                        @endforeach
                    </select></div>`
                ).insertBefore(".dataTables_filter");

                $(`<div style="max-width: 250px; min-width: 170px;" class = "float-left mr-2"><select name="assigned_to" id="assigned_to" class="form-control ml-1 datatable-reload" style="width:150px; display:inline-block;">
                        <option value="">@lang('ui.assignee')</option>
                        @foreach($assignees as $assignee)
                            <option value="{{ $assignee->id }}">{{ $assignee->username }}</option>
                        @endforeach
                    </select></div>`
                ).insertBefore(".dataTables_filter");
            }


            //  status filter
            $(`<div style="max-width: 250px; min-width: 170px;" class = "float-left mr-2"><select name="status" id="status" class="form-control ml-1 datatable-reload" style="width:150px; display:inline-block;">
                    <option value="">@lang('ui.status')</option>
                    <option value="to do"       >To Do</option>
                    <option value="in progress" >In Progress</option>
                    <option value="cancelled"   >Cancelled</option>
                    <option value="not needed"  >Not Needed</option>
                    <option value="refused"     >Refused</option>
                    <option value="in review"   >In Review</option>
                    <option value="done"        >Done</option>
                </select></div>`
            ).insertBefore(".dataTables_filter");
            //  status filter
            // priority filter
            $(`<div style="max-width: 250px; min-width: 170px;" class = "float-left mr-2"><select name="priority" id="priority" class="form-control ml-1 datatable-reload" style="width:150px; display:inline-block;">
                    <option value="">@lang('ui.priority')</option>
                    <option value="0">Urgent</option>
                    <option value="1">Blocker</option>
                    <option value="2">Major</option>
                    <option value="3">Critical</option>
                    <option value="4">Minor</option>
                    <option value="5">Trivial</option>
                </select></div>`
            ).insertBefore(".dataTables_filter");
            // priority filter
            // date filter
            $('<div class="float-left pl-1 mb-1 pl-sm-0  mr-2" style="width:180px;"><input type="text" id="date-filter1" class="form-control line-input d-inline-block gray-bg mr-2" style=""></div>').insertBefore(".dataTables_filter");
            $('<div class="float-left pl-1 mb-1 pl-sm-0  mr-2" style="width:180px;"><input type="text" id="date-filter2" class="form-control line-input d-inline-block gray-bg mr-2" style=""></div>').insertBefore(".dataTables_filter");


            $('#date-filter1,#date-filter2').daterangepicker({
                singleDatePicker: true,
                showDropdowns: true,
                locale: {
                    format: "YYYY-MM-DD",
                    cancelLabel: 'Clear'
                }
            });

            $('#date-filter1').val("Created At");
            $('#date-filter2').val("Updated At");

            $("#date-filter1,#date-filter2").on("change", function(event){
                event.preventDefault();
                table.ajax.reload(null, false)
            });

            $('#date-filter1').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('Created At');
                table.ajax.reload();
            });
            $('#date-filter2').on('cancel.daterangepicker', function(ev, picker) {
                $(this).val('Updated At');
                table.ajax.reload();
            });
            // date filter

            $('.dataTables_filter').append(
                "<span class='ml-2 font-weight-bold' id='selected-count' style='display: none'>0 tasks selected</span>"
                + "<button id='delete-selected-btn' type='button' class='btn btn-danger ml-3' style='display: none'><i class='icon-trash'></i> {{__('ui.delete')}} </button>"
            );

            $(`
                <div style="max-width: 250px; min-width: 170px; display: none;" class="ml-2" id="assigned_update_div">
                    <button type='button' class='btn btn-primary ml-1' data-toggle="modal" data-target="#bulk-assign-modal"><i class='icon-change-icon-16'></i> {{__('ui.assign')}} </button>
                </div>`
            ).insertAfter(".dataTables_filter");

            $(".datatable-reload").change(function () {
                table.ajax.reload();
            });

            $("select[name='assigned_to']").select2({
                containerCssClass: "{{ $errors->has('assigned_to')}}" ? "border-danger" : '',
                placeholder: '@lang('ui.assignee')',
            });

            $("select[name='status']").select2({
                containerCssClass: "{{ $errors->has('status')}}" ? "border-danger" : '',
                placeholder: '@lang('ui.status')',
            });

            $("select[name='priority']").select2({
                containerCssClass: "{{ $errors->has('priority')}}" ? "border-danger" : '',
                placeholder: '@lang('ui.priority')',
            });

            $("select[name='assigner_id']").select2({
                containerCssClass: "{{ $errors->has('assigner_id')}}" ? "border-danger" : '',
                placeholder: '@lang('ui.assignee')',
            });

            $("select[name='creator']").select2({
                containerCssClass: "{{ $errors->has('creator')}}" ? "border-danger" : '',
                placeholder: '@lang('task.creator')',
            });

            $("#bulk-assign-modal").on('hidden.bs.modal', function () {
                $("select[name='assigner_id']").val(null).trigger('change.select2');
            });

            function showActionButtons(totalChecked) {
                $('#selected-count').text(totalChecked + ' tasks selected').show();
                $('#delete-selected-btn').show();
                $('#assigned_update_div').css('display', 'inline-flex');
            }

            function hideActionButtons() {
                $('#selected-count').hide();
                $('#delete-selected-btn').hide();
                $('#assigned_update_div').hide();
            }

            $("#bulk-assign").click(function(e){
                e.preventDefault();

                let taskIds  = [];
                let assigner = $("select[name='assigner_id']").val();

                $("input[name^='select-']").each(function () {
                    if ($(this).is(':checked')) {
                        taskIds.push($(this).val());
                    }
                });

                if(!assigner){
                    new PNotify({
                        title: '{{ __('ui.note') }}',
                        text: 'Assignee is required!',
                        icon: 'icon-cancel-circle2',
                        class: 'stack-custom-bottom bg-danger border-danger',
                        type: 'error',
                        hide: true,
                        delay: 800
                    });

                    return ;
                }

                $.ajax({
                    type: "POST",
                    url: route("task-manager.bulkAssign"),
                    data: {
                        assigner: assigner,
                        taskIds: taskIds
                    },
                    success: (res) => {
                        if(res.status === 'success'){
                            $('#bulk-assign-modal').modal('hide');
                            $('#selectAll').prop('checked',false);
                        }

                        new PNotify({
                            title: `${res.status}`,
                            text: `${res.message}`,
                            icon: 'icon-checkmark3',
                            class: 'stack-custom-bottom bg-success border-success',
                            type: 'success',
                            hide: true,
                            delay: 800
                        });

                        table.ajax.reload(null, false);
                    }
                });

            });

            // Single car checkbox Click Event
            $('#dataTable').on('click', 'input[name^=\'select-\']', function () {
                let howManyChecked = 0;

                $("input[name^='select-']").each(function () {
                    if ($(this).is(':checked')) {
                        howManyChecked++;
                    }
                });

                if (howManyChecked) {
                    showActionButtons(howManyChecked);
                } else {
                    hideActionButtons();
                }
            });

            $('#selectAll').on('click', function () {
                var Checked = 0;
                if (this.checked) {
                    let allSelectInput = $("input[name^='select-']");
                    allSelectInput.each(function () {
                        if(!$(this).is(':disabled'))
                        {
                            $(this).prop('checked', true);
                            Checked++;
                        }
                    });
                    showActionButtons(Checked);
                } else {
                    $("input[name^='select-']").each(function () {
                        $(this).prop('checked', false);
                    });
                    hideActionButtons();
                }
            });

            $('#delete-selected-btn').on('click', function () {
                let deletableIds = [];

                $("input[name^='select-']").each(function () {
                    if ($(this).is(':checked')) {
                        deletableIds.push($(this).val());
                    }
                });

                if (deletableIds.length === 0)
                    alert(" Please select one or more tasks to delete ");
                else if (confirm("{{ __('ui.form.are_you_sure_you_want_to_delete')}} "+deletableIds.length+"{{ __('ui.form.selected_tasks')}}")) {
                    let form = document.createElement("form");
                    form.method = "post";
                    form.action = route('task-manager.destroyMultiple');

                    let csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.head.querySelector("[name~=csrf-token][content]").content;
                    form.appendChild(csrf);

                    let method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'delete';
                    form.appendChild(method);

                    let ids = document.createElement('input');
                    ids.type = 'hidden';
                    ids.name = 'ids';
                    ids.value = JSON.stringify(deletableIds);
                    form.appendChild(ids);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
            $('#update_assignee').on('click', function () {
                let taskIds    = [];
                let assignedTo = $('#assigned_to_update').val();

                $("input[name^='select-']").each(function () {
                    if ($(this).is(':checked')) {
                        taskIds.push($(this).val());
                    }
                });

                if (taskIds.length === 0 || assignedTo.length === 0)
                    alert(" Please select tasks and assignee ");
                else if (confirm("Continue to assign?")) {
                    let form = document.createElement("form");
                    form.method = "post";
                    form.action = route('task-manager.bulkAssign');

                    let csrf = document.createElement('input');
                    csrf.type = 'hidden';
                    csrf.name = '_token';
                    csrf.value = document.head.querySelector("[name~=csrf-token][content]").content;
                    form.appendChild(csrf);

                    let method = document.createElement('input');
                    method.type = 'hidden';
                    method.name = '_method';
                    method.value = 'post';
                    form.appendChild(method);

                    let ids = document.createElement('input');
                    ids.type = 'hidden';
                    ids.name = 'ids';
                    ids.value = JSON.stringify(taskIds);
                    form.appendChild(ids);

                    let assigned_to = document.createElement('input');
                    assigned_to.type = 'hidden';
                    assigned_to.name = 'assigned_to';
                    assigned_to.value = assignedTo;
                    form.appendChild(assigned_to);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });

        $(document).on('click', "#remove-task", function() {
            var task_id = $(this).attr("data-href").split("/").pop();
            var task_title = $("#title-"+task_id).text();
            if (!confirm("{{ __('ui.form.are_you_sure_you_want_to_delete') }} "+task_title+" ?"))
                return false;

            $.ajax({
                type: "DELETE",
                url: $(this).attr("data-href"),
                success: (res) => {
                    new PNotify({
                        title: `${res.status}`,
                        text: `${res.message}`,
                        icon: 'icon-checkmark3',
                        class: 'stack-custom-bottom bg-success border-success',
                        type: 'success',
                        hide: true,
                        delay: 800
                    });

                    console.log(table)
                    table.ajax.reload(null, false);
                }
            });
        });
    </script>
    <style>
        .task-status img{
            margin-right: 3px;
            margin-top: -3px;
        }
        #dataTable_filter{
            margin: unset;
        }
        #dataTable_filter input{
            width: 170px;
        }
        .daterangepicker.dropdown-menu .calendars{
            min-width: 350px;
        }
        .daterangepicker.dropdown-menu select.monthselect, .daterangepicker.dropdown-menu select.yearselect{
            width: 50%;
        }

    </style>

@endpush
