@extends('layouts.app')
@section('breadcrumbs', Breadcrumbs::render('tm_members.manage'))

@section('content')


    <!-- Ajax sourced data -->
    <div class="card">

        <table class="table datatable-ajax">
            <thead>
            <tr>
                <th>ID</th>
                <th>Name</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th>Status</th>
                <th>Creator</th>
                <th>Control</th>
            </tr>
            </thead>
        </table>

    </div>
    <!-- /ajax sourced data -->

@endsection




@push('footer')
    <style>
        .dataTable {
            margin: 0;
            max-width: none;
            min-height: 250px;
        }
    </style>

    <script>

        $(function () {
            // Highlighting rows and columns on mouseover
            var table = $('.datatable-ajax').dataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: window.location.href,
                    data: function (d) {
                    },
                },
                initComplete: function (settings, json) {

                },
                order: [[0, "desc"]],
                lengthMenu: [[10, 20, 50, 100], [10, 20, 50, 100]],
                columns: [
                    {name: "id", data: "id", visible:false},
                    {name: "profile.first_name", data: "username", defaultContent: ""},
                    {name: "username", data: "username", defaultContent: "", visible:false},
                    {name: "profile.last_name", data: "profile.last_name", defaultContent: "", visible:false},
                    {name: "email", data: "email"},
                    {
                        name: 'active',
                        data: 'active',
                        render: function (data, type, obj, meta) {
                            if (obj.active)
                                return "<label class='badge bg-success'>Active</label>";
                            return "<label class='badge bg-danger'>Deactivated</label>";
                        }
                    },
                    {name: "creator.username", data: "creator.username", defaultContent: "", searchable: false, sortable: false},
                    {
                        name: 'actions', searchable: false, sortable: false,
                       visible: (@json(auth()->user()->can('members.modify') || auth()->user()->can('members.destroy'))),
                        render: function (data, type, obj, meta) {
                            let impersonate = "";
                            if(@json(auth()->user()->isSuperAdmin())){
                                impersonate += "<a href='" + route('impersonate', {id: obj.id}) + "' class='dropdown-item' title='Logged in as" + obj.name + "'><i class=' icon-redo2'></i>  Logged in as Member</a>"
                            }

                            return "<td class='text-center'>" +
                                "<div class='list-icons'>" +
                                "<div class='dropdown'>" +
                                "<a href='#' class='list-icons-item' data-toggle='dropdown'>" +
                                "<i class='icon-menu9'></i>" +
                                "</a>" +
                                "<div class='dropdown-menu dropdown-menu-right'>" +
                                "<a href='" + route('task-manager.members.edit', {member: obj.id}) + "' class='dropdown-item'><i class=' icon-pencil'></i> Edit</a><a data-href='" + route('users.destroy', {user: obj}) + "' class='dropdown-item remove-user'><i class=' icon-trash'></i> Delete</a>" +
                                "<form method='POST' action='" + route('task-manager.members.destroy', {member: obj.id}) + "' accept-charset='UTF-8'><input name='_method' type='hidden' value='DELETE'><input name='_token' type='hidden' value='" + window.Laravel.csrfToken + "'></form>" +
                                impersonate +
                                "</div>" +
                                "</div>" +
                                "</div>" +
                                "</td>"
                        }
                    },
                ],

            });   // end datatable ajax

            //Initialize tooltip every time the table is redrawn.
            $('.datatable-ajax').on('draw.dt', function () {
                $('[data-toggle="tooltip"]').tooltip();
            });

            if(@json(auth()->user()->can('members.store'))) {
                $('.dataTables_filter').prepend('<a href="' + route('task-manager.members.create') + '" style="margin-right: 40px;" class="btn btn-info btn-lg">New</a>');
            }

            $('body').on('click', '.remove-user', function (e) {
                var user_name = $(this).closest("tr").find("td").eq(0).html();
                if (!confirm("{{ __('ui.form.are_you_sure_you_want_to_delete') }} " + user_name + "?"))
                    return false;
                $(this).next('form').submit();
            });
            $('.datatable-ajax').css('min-height', '300px');
        });
    </script>

@endpush
