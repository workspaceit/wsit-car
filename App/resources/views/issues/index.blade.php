@extends('layouts.app')

@section('breadcrumbs', Breadcrumbs::render('issues'))

@section('content')
    <div class="card responsive">
        <table class="table datatable-ajax table-responsive">
            <thead>
            <tr>
                <th>{{ trans_choice('ui.ticket',1) }}</th>
                {{-- <th>{{__('ui.name')}}</th>
                <th>{{__('ui.email')}}</th>
                <th>{{__('ui.phone number')}}</th> --}}
                <th>{{__('ui.type')}}</th>
                <th>{{__('ui.description')}}</th>
                {{-- <th>{{trans_choice('ui.dealer', 1)}}</th> --}}
                <th>{{__('ui.status')}}</th>
                <th> {{__('ui.date')}}</th>
                <th>{{__('ui.control')}}</th>
            </tr>
            </thead>
        </table>
    </div>
    <!-- /ajax sourced data -->
@endsection

@push('footer')

    <script src="/backend_assets/global_assets/js/plugins/forms/styling/switch.min.js"></script>
    <script>
        $(function () {
            // Highlighting rows and columns on mouseover
            var table = $('.datatable-ajax').DataTable({
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
                ajax: {
                    url: route('supports'),
                    data: function (d) {
                    },
                },
                initComplete: function (settings, json) {

                },
                order: [[4, "desc"]],
                lengthMenu: [[10, 20, 30, 100], [10, 20, 30, 100]],
                columns: [
                    {
                        name: "ticket_no", data: "ticket_no",
                        render: function (data, type, obj, meta) {
                            return "<a href='" + route('supports.edit', {issueId: obj.id}) + "'> "+ data + " </a>";
                        }
                    },
                    // {name: "name", data: "name"},
                    // {name: "email", data: "email"},
                    // {name: "phone", data: "phone"},
                    {name: "type", data: "type"},
                    {
                        name: 'description',
                        data: 'description',
                        render: function (data, type, obj, meta) {
                            var description = obj.description;
                            if(description.length > 100) description = description.substring(0,100)+'...';
                            return '<div class="issue-description">'+description+'</div>';
                        }
                    },
                    // {name: "description", data: "description"},
                    // {
                    //     name: 'dealer',
                    //     data: 'dealer',
                    //     render: function (data, type, obj, meta) {

                    //         if (obj.dealer_id == null)
                    //             return '--';
                    //         else if(obj.dealer_id == 0)
                    //             return 'All';
                    //         return obj.dealers.name;
                    //     }
                    // },
                    {name: "status.name", data: "status.name", defaultContent: '',
                        render: function (data, type, obj, meta) {
                            return obj.status ? (obj.status.name).toUpperCase() : "";
                        }
                    },
                    {name: "created_at", data: "created_at"},
                    {
                        name: 'actions', searchable: false, sortable: false,
                        visible: @json(auth()->user()->can('supports.modify') || auth()->user()->can('supports.destroy')),
                        render: function (data, type, obj, meta) {
                            var editlink = '';

                            if( @json(auth()->user()->can('supports.modify'))) {
                                editlink = "<a href='" + route('supports.edit', {
                                    issueId: obj.id
                                }) + "' class='dropdown-item'><i class=' icon-pencil'></i> {{__('ui.edit')}}</a>";
                            }

                            if( @json(auth()->user()->can('supports.destroy'))) {
                                editlink += "<a data-href='" + route('supports.destroy', {
                                        issue_id: obj.id
                                    }) + "' class='dropdown-item remove-item'><i class='icon-trash'></i> {{__('ui.delete')}}</a>" +
                                    "<form method='POST' action='" + route('supports.destroy', {
                                        issue_id: obj.id
                                    }) + "' accept-charset='UTF-8'><input name='_method' type='hidden' value='DELETE'><input name='_token' type='hidden' value='" + window.Laravel.csrfToken + "'></form>";
                            }

                            return "<td class='text-center'>" +
                                "<div class='list-icons'>" +
                                "<div class='dropdown'>" +
                                "<a href='#' class='list-icons-item' data-toggle='dropdown'>" +
                                "<i class='icon-menu9'></i>" +
                                "</a>" +
                                "<div class='dropdown-menu dropdown-menu-right'>" +
                                editlink +
                                "</div>" +
                                "</div>" +
                                "</div>" +
                                "</td>"
                        }
                    },
                ],

            });   // end datatable ajax
            if( @json(auth()->user()->can('supports.store'))) {
                $('.dataTables_filter').prepend('<a href="' + route('supports.create') + '" style="margin-right: 40px;" class="btn btn-info btn-lg">' + "{{__('ui.new')}}" + '</a>');
            }

            //Initialize tooltip every time the table is redrawn.
            $('.datatable-ajax').on('draw.dt', function () {
                $('[data-toggle="tooltip"]').tooltip();
            });
            $('.datatable-ajax').css('min-height', '300px');
        });
    </script>
@endpush

