@extends('layouts.app')
@section('breadcrumbs', Breadcrumbs::render('tm_tasks.details', $task))
@section('content')
<link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />

<script src="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <!-- Basic layout-->
    <div class="card">
        <div class="card-body" id="app">
            <div class="card-header pt-0">
                <a href="{{ back()->getTargetUrl() }}" class="btn btn-secondary"><i class="icon-backspace2 mr-2"></i>{{__('ui.back')}}</a>
                @if($task->created_by === auth()->user()->id || in_array(auth()->user()->type, ["dealer", "admin", "super_admin"]))
                    <a href="{{  route("task-manager.tasks.edit", ['id' => $task['id']]) }}" type="button" class="btn btn-primary float-right"><i class="icon-pencil ml-2"></i> {{__('ui.edit')}}</a>
                @endif
            </div>
            <div class="row">
                <div class="col-md-9">
                    <div class="task-card-left">
                        <h3 for="asd" class="font-weight-bolder"> {{$task->title}}
                            @if($task->doc_link)
                                <span class="linkIcon"> <a target="_blank" href="{{$task->doc_link}}"><i class="icon-link"></i></a>
                            @endif
                        </h3>
                        <hr class="mb-1 mt-0">
                        <h5 class="col-lg-12 pl-0">Description</h5>
                        <p> {!! ($task->description ?? "<span class='ml-3 text-muted'>None</span>")!!}  </p>
                        @if(!empty($task->files))
                            <p class="font-weight-bold mt-3 mb-2">Attachments({{count($task->files)}})</p>
                            <input id="documents" type="file" class="form-control-file">
                        @endif


                        @if(!empty($linkeds))
                            <h5 class="col-lg-12 pl-0 mb-0 mt-3">Linked Links</h5>
                            <div class="pb-2 mt-2">
                                <span class="select2-selection select2-selection--multiple" style="border: none;">
                                    <ul class="select2-selection__rendered">
                                        @foreach($linkeds as $linked)
                                            <li class="select2-selection__choice" id="link-{{$linked->link_id}}">
                                                @if($task->created_by === auth()->user()->id || in_array(auth()->user()->type, ["dealer", "admin", "super_admin"]))
                                                    <span class="select2-selection__choice__remove" role="presentation" data = "{{$linked->link_id}}">×</span>
                                                @endif
                                                <a href="{{$linked->link}}", target="_blank" class="text-white"> {!! $linked->text !!}</a>
                                            </li>
                                        @endforeach
                                    </ul>
                                </span>
                            </div>
                        @endif

                        <h5 class="col-lg-12 pl-0 mb-0">Activity</h5>
                        <div class="activity-container">
                            <span class="activity active" id='comments'>Comments</span>
                            <span class="activity" id='history'>History</span>
                        </div>
                        <hr>
                        <div class="add-comment-section pb-2">
                            <input type="text" name="comment" id="addComment" placeholder="Add a Comment.." class="form-control">
                            <div class="summerComment"></div>
                            <button type="button" id="saveComment" style="display:none;"
                                class="btn btn-sm btn-primary mt-2">Save</button>
                            <button type="button" id="cancelComment" style="display:none;"
                                class="btn btn-sm btn-secondary mt-2">Cancel</button>
                        </div>
                        <div class="comment-section">
                            @if (!empty($task->comments))
                                @foreach ($task->comments as $key => $comment)
                                    <div class="comments pt-0 pb-0">
                                        <div class="col-md-12 pl-0">
                                            <p class="task-user">
                                                @if($comment->user && $comment->user->photo)
                                                <img class="avatar avatar-32" alt="Avatar" src="{{$comment->user->photo}}">
                                            @else
                                                <span class="avatar avatar-32 bg-primary text-white" style="border-radius: 5px;">{{substr($comment->user ? $comment->user->username : "", 0, 1)}}</span>
                                            @endif
                                                 <span style="font-size: 16px;">{{$comment->user ? $comment->user->username : "-"}}</span>
                                            <span class="breadcrumb-item task-no active" style="padding-left:.425rem;">{{$comment->created_at->format('d M, Y h:i A')}}</span>
                                        </p>
                                        </div>
                                        <div class="comment-details pl-4">{!!$comment->body!!}</div>
                                        @if (auth()->user()->isSuperAdmin() || $comment->user_id == auth()->user()->id)
                                        <div class="pl-2 mb-3">
                                            <button type="button" class="btn editComment ml-2" data-id="{{$comment->id}}">Edit</button>
                                            <button type="button" class="btn deleteComment" data-id="{{$comment->id}}">Delete</button>
                                        </div>
                                         @endif
                                    </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="history-section" style="display: none;">
                            @if (!empty($changeLog))
                                @foreach ($changeLog as $log)
                                    <div class="comments pt-0 pb-0">
                                        <div class="col-md-12 pl-0">
                                            <p class="task-user">
                                                @if($log->modified_by && $log->modified_by->photo)
                                                <img class="avatar avatar-32" alt="Avatar" src="{{$log->modified_by->photo}}">
                                            @else
                                                <span class="avatar avatar-32 bg-primary text-white" style="border-radius: 5px;">{{substr($log->modified_by ? $log->modified_by->username : "", 0, 1)}}</span>
                                            @endif
                                                 <Span style="font-size: 14px;">{!! $log->title !!}</Span>
                                            <span class="breadcrumb-item task-no active" style="padding-left:.425rem;">{{$log->modified_at}}</span>
                                        </p>
                                        </div>
                                        <div class="history_container">
                                            @foreach($log->modifed_data as $key => $data)
                                                <div class="history_changes d-inline-flex">

                                                    @if(count($log->modifed_data)>1)
                                                        <strong class="history_key">{{$key}}</strong>:
                                                    @endif

                                                    @if(is_array($log->old_data[$key]))
                                                        @if($log->old_data[$key])
                                                            @foreach($log->old_data[$key] as $key1 => $value)
                                                                <div class="history-old"><i class="fas fa-file attachment"></i><a href="{{$value}}" target="_blank">{!! $value ? (!empty($value) ?  pathinfo($value, PATHINFO_BASENAME) : "<span style='color: #999;'>None</span>") : "<span style='color: #999;'>None</span>"  !!}</a></div>
                                                            @endforeach
                                                        @else
                                                            <div class="history-old"><span style='color: #999;'>None</span></div>
                                                        @endif
                                                    @else
                                                        <div class="history-old">{!! $log->old_data ? (!empty($log->old_data[$key]) ? $log->old_data[$key] : "<span style='color: #999;'>None</span>") : "<span style='color: #999;'>None</span>"  !!}</div>
                                                    @endif

                                                    <div> → </div>

                                                    @if($key == "attachments")
                                                        <div class="history-modified">
                                                            @foreach(explode(",",$data) as $key => $value)
                                                               <div class="array"><i class="fas fa-file attachment"></i> <a href="{{$value}}" target="_blank">{!! !empty($value) ?  pathinfo($value, PATHINFO_BASENAME) : "<span style='color: #999;'>None</span>" !!}</a></div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="history-modified">{!! !empty($data) ? $data : "<span style='color: #999;'>None</span>" !!}</div>
                                                    @endif

                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                    <hr>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="task-card-right">
                        <select name="status" id="status" class="form-control mt-2 mb-2 {{ $errors->has('status') ? 'form-control border-danger' : 'form-control' }}">
                            <option value="to do" {{old('status', $task->status ? $task->status->name : "") === "to do" ? 'selected' : ''}}>To Do</option>
                            <option value="in progress" {{old('status', $task->status ? $task->status->name : "") === "in progress" ? 'selected' : ''}}>In Progress</option>
                            <option value="cancelled" {{old('status', $task->status ? $task->status->name : "") === "cancelled" ? 'selected' : ''}}>Cancelled</option>
                            <option value="not needed" {{old('status', $task->status ? $task->status->name : "") === "not needed" ? 'selected' : ''}}>Not Needed</option>
                            <option value="refused" {{old('status', $task->status ? $task->status->name : "") === "refused" ? 'selected' : ''}}>Refused</option>
                            <option value="in review" {{old('status', $task->status ? $task->status->name : "") === "in review" ? 'selected' : ''}}>In Review</option>
                            <option value="done" {{old('status', $task->status ? $task->status->name : "") === "done" ? 'selected' : ''}}>Done</option>
                        </select>
                        <div class="task-right-details mt-2">
                            <h5 class="mb-1">Details</h5>
                            <hr class="mb-1 mt-0">
                            <div class="block">
                                <label for="">Created By</label>
                                <label class="task-user col-md-12 pl-0">
                                    @if($task->user && $task->user->photo)
                                    <img class="avatar avatar-32" alt="Avatar" src="{{$task->user->photo}}">
                                @else
                                    <span class="avatar avatar-32 bg-primary text-white" style="border-radius: 5px;">{{substr($task->user ? $task->user->username : "", 0, 1)}}</span>
                                @endif
                                    {{$task->user ? $task->user->username : "-"}}</label>
                                <label for="" class="mt-1">Assignee</label>
                                <label class="task-user col-md-12 pl-0">
                                    @if($task->assigner && $task->assigner->photo)
                                        <img class="avatar avatar-32" alt="Avatar" src="{{$task->assigner->photo}}">
                                    @else
                                        <span class="avatar avatar-32 bg-primary text-white" style="border-radius: 5px;">{{substr($task->assigner ? $task->assigner->username : ($task->user ? $task->user->username : ""), 0, 1)}}</span>
                                    @endif
                                    {{$task->assigner ? $task->assigner->username :($task->user ? $task->user->username : "")}}</label>
                                <label for="" class="mt-1">Priority</label>
                                <label class="task-user col-md-12 pl-0">
                                    <img src="{{asset(sprintf("backend_assets/assets/img/%s.svg", $task->priority))}}" width = "16" height = "16"/>
                                    {{($task->priority == 0)? 'Urgent' : ''}}
                                    {{($task->priority == 1)? 'Blocker' : ''}}
                                    {{($task->priority == 2)? 'Major' : ''}}
                                    {{($task->priority == 3)? 'Critical' : ''}}
                                    {{($task->priority == 4)? 'Minor' : ''}}
                                    {{($task->priority == 5)? 'Trivial' : ''}}
                                </label>
                            </div>

                            <div class="form-group mb-0 pt-1">
                                <label for="reminder" class="label">Reminder</label>
                                <div class="input-group date" id="reminder" data-target-input="nearest">
                                    <input type="text" name="reminder" class="form-control datetimepicker-input" data-target="#reminder"/>
                                    <div class="input-group-append" data-target="#reminder" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="block date-time">
                            <p class="w-100 mb-0 mt-1">Created: <span>{{$task->created}}</span></p>
                            <p class="w-100">Updated: <span>{{$task->updated}}</span></p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
        <!-- /basic layout -->
    </div>
    <style>
        .file-caption-main, .fileinput-remove, .file-drag-handle, .kv-file-remove{
            display: none !important;
        }
        .kv-file-download {
            margin-left: 5px;
        }
        .file-preview-frame {
            max-width: 170px !important;
        }
        .file-preview {
            border: unset !important;
            margin-bottom: 0.4rem !important;
        }
        .kv-file-content{
            height: 120px !important;
        }
        .table-sm td, .table-sm th {
            padding: 7px 7px;
        }
        .activity-container{
            margin-top:10px;
            margin-bottom: 10px;
        }
        .activity{
            background: #ddd;
            padding: 5px 10px;
            border-radius: 3px;
            cursor: pointer;
            margin-right: 5px;
        }
        .activity:hover{
            background: #ccc;
        }
        .activity.active{
            background: #777;
            color: white;
        }
        .history-old,.history-modified{
            width: max-content;
            padding: 0px 10px;
            max-width: 50%;
        }
        .history-old .priority,.history-modified .priority,.task-user img{
            margin-right: 3px;
            margin-top: -3px;
        }
        .history_changes{
            display: grid;
            padding: 10px;
            padding-left: 1.875rem !important;
            overflow: auto;
        }
        .history_container{
            display: grid;
            overflow: auto;
        }
        .history_changes>strong{
            text-transform: capitalize;
        }
        .history_key{
            min-width: 85px;
        }
        .attachment{
            margin-right: 5px;
            font-size: 18px;
        }
        @media (max-width: 767px) {
            .history_changes{
                padding-left: 0 !important;
            }
        }

    </style>

    <script>
        var oldReminderDateTime = @json($task->reminder ?? null);
        $(document).ready(function() {
            let fileinput_options = {
                theme: 'fas',
                language: '{{app()->getLocale()}}',
                showCaption: true,
                browseOnZoneClick: false,
                showUpload: false,
                initialPreviewAsData: true,
                uploadAsync: false,
                showRemove: false,
                overwriteInitial: false,
                dropZoneTitle: "{{ __('ui.documents_will_show') }}",
                dropZoneClickTitle: '',
                msgPlaceholder: '',
                dropZoneEnabled: false,
                fileActionSettings: {
                    showUpload: false,
                },
                previewMarkupTags: {
                    tagBefore1: '<div class="file-preview-frame {frameClass}" id="{previewId}" data-fileindex="{fileindex}"' +
                        ' data-fileid="{fileid}" data-template="{template}">' +
                        '<div class="kv-file-content move drag-handle-init">\n',
                },
                otherActionButtons: '<button type="button" class="kv-cust-print-btn btn btn-sm btn-kv btn-default btn-outline-secondary" title="Print" {dataKey}>' +
                                        '<i class="fas fa-print"></i>' +
                                    '</button>',
            };

            fileinput_options.initialPreview = [
                @foreach($task->files as $file)
                    "{{ $file }}",
                @endforeach
            ];

            fileinput_options.initialPreviewConfig = [
                @foreach($task->files as $file)
                    {
                        type: "{{getFileMimeType($file) == 'pdf' ? 'pdf' : 'image'}}",
                        key: "{{ $file }}",
                        downloadUrl: "{{ $file }}", // the url to download the file
                    },
                @endforeach
            ];

            var summernoteConfig = {
                focus: true,
                dialogsInBody: true,
                tabsize: 4,
                height: 100,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['help']]
                ]
            };

            $("#documents").fileinput(fileinput_options)
            $("select[name='status']").select2({
                containerCssClass: "{{ $errors->has('status')}}" ? "border-danger" : '',
                placeholder: 'Select status',
                minimumResultsForSearch: -1
            });
            $('#reminder').datetimepicker({
                defaultDate: @json(old('reminder', $task->reminder)),
                format: 'YYYY-MM-DD LT',
                useCurrent: false
            });

            $('#reminder').on("hide.datetimepicker", ({date, oldDate}) => {
                let reminder = (date._d).toLocaleString("sv-SE");
                let url  = "{{ route('task-manager.tasks.modify.reminder') }}"

                if(oldReminderDateTime !== reminder){
                    oldReminderDateTime = reminder
                    ajaxRequest(url, { task: {{$task->id}}, reminder: reminder })
                }
            });


            $('#addComment').click(function (e) {
                $('.summerComment').summernote(summernoteConfig);
                commentToggles()
            });
            $('#saveComment').click(function (e) {
                let text = $('.summerComment').summernote('code');

                let url  = "{{ route('task-manager.addTaskComment') }}"
                let data = {
                    task_id: {{$task->id}},
                    comment: text,
                }
                ajaxRequest(url, data)
            });
            $('#cancelComment').click(function (e) {
                destroySummernote()
                commentToggles()
            });
            $(document).on("click", ".editComment", function (e) {
                let id      = $(this).data('id')
                let comment = $(this).closest('.comments').find('.comment-details')

                comment.summernote(summernoteConfig);

                $(this).hide()
                $(this).parent().find('.deleteComment').hide()

                $(this).closest('.comments').append(`
                    <button type="button" class="btn btn-sm btn-primary mb-2 updateComment" data-id="${id}">Update</button>
                    <button type="button" class="btn btn-sm btn-secondary mb-2 cancelComment">Cancel</button>
                `)
            });
            $(document).on("click", ".updateComment", function () {
                let id      = $(this).data('id')
                let comment = $(this).parent().find('.comment-details')

                let text = comment.summernote('code');

                let url  = "{{ route('task-manager.updateTaskComment') }}"
                let data = {
                    comment_id: id,
                    comment   : text,
                }
                ajaxRequest(url, data)

                comment.summernote('destroy');

                $(this).parent().find('.editComment').show()
                $(this).parent().find('.deleteComment').show()

                $(this).parent().find('.cancelComment').remove()
                $(this).remove()
            })
            $(document).on("click", ".cancelComment", function () {
                let comment = $(this).parent().find('.comment-details')
                comment.summernote('destroy');

                $(this).parent().find('.editComment').show()
                $(this).parent().find('.deleteComment').show()

                $(this).parent().find('.updateComment').remove()
                $(this).remove()
            })
            $(document).on("click", ".deleteComment", function (e) {
                let id   = $(this).data('id')

                let text = "Comment will be delete permanently?";
                if (confirm(text) == true) {
                    let url  = "{{ route('task-manager.deleteTaskComment') }}"
                    let data = {
                        comment_id  : id,
                    }
                    ajaxRequest(url, data)
                    $(this).closest('.comments').remove()
                }
            });

            $("#status").on("change", function (event){
                let url  = "{{ route('task-manager.ajaxUpdateStatus') }}"
                let data = {
                    task  : {{$task->id}},
                    status: this.value,
                }
                ajaxRequest(url, data)
            });

            $(".select2-selection__choice__remove").on("click", function(e){
                e.preventDefault();
                let linkedId =  $(this).attr('data');
                let url = route('task-manager.tasks.links.destroy', {link : linkedId});

                ajaxRequest(url, {}, "delete")
                $("#link-" + linkedId).remove()
            });

            function addComment(comment) {
                let avatar = `<span class='avatar avatar-32 bg-primary text-white' style='border-radius: 5px;'> ${(comment.username).substring(0, 1)} </span>`;
                if(comment.photo){
                    avatar = '<img class="avatar avatar-32" alt="Avatar" src="' + comment.photo + '">'
                }

                $('.comment-section').prepend(`
                    <div class="comments pt-0 pb-0">
                        <div class="col-md-12 pl-0">
                            <p class="task-user">
                                ${avatar}
                                <span style="font-size: 16px;">${comment.username}</span>
                            <span class="breadcrumb-item task-no" style="padding-left:.425rem;">${comment.created}</span>
                        </p>
                        </div>
                        <div class="comment-details pl-4">${comment.body}</div>
                        <div class="pl-2 mb-3">
                            <button type="button" class="btn editComment ml-2" data-id="${comment.id}">Edit</button>
                            <button type="button" class="btn deleteComment" data-id="${comment.id}">Delete</button>
                        </div>
                    </div>
                `);
            }

            function commentToggles() {
                $('#saveComment').toggle();
                $('#cancelComment').toggle();
                $('#addComment').toggle();
            }
            function destroySummernote(params) {
                $('.summerComment').summernote('destroy');
                $('.summerComment').html('');
            }

            function ajaxRequest(paramURL, jsonData = {}, requestType = "post") {
                $.ajax({
                    url    : paramURL,
                    headers: { 'X-CSRF-TOKEN': $('meta[name = "csrf-token"]').attr('content') },
                    type   : requestType,
                    data   : jsonData,
                    success: function (response) {
                        if (response.status) {
                            new PNotify({
                                title: "{{ __('ui.note') }}",
                                text : response.message,
                                icon : 'icon-checkmark3',
                                class: 'stack-custom-bottom bg-success border-success',
                                type : 'success',
                                hide : true,
                                delay: 800
                            });

                            if (response.comment) {
                                addComment(response.comment)
                                destroySummernote()
                                commentToggles()
                            }
                        } else {
                            new PNotify({
                                title: "{{ __('ui.note') }}",
                                text : response.message,
                                icon : 'icon-cancel-circle2',
                                class: 'stack-custom-bottom bg-danger border-danger',
                                type : 'error',
                                hide : true,
                                delay: 800
                            });
                        }
                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        console.log(textStatus, errorThrown);
                    },
                });
            }

            $(document).on("click", ".activity", function (e) {
                let id   = $(this).attr('id');
                console.log(id);

                $(".activity").removeClass("active");
                $(this).addClass("active");

                if(id == 'comments')
                {
                    $(".add-comment-section").show();
                    $(".comment-section").show();
                    $(".history-section").hide();
                }
                else if(id == 'history')
                {
                    $(".add-comment-section").hide();
                    $(".comment-section").hide();
                    $(".history-section").show();
                }
            });
        });
    </script>
@stop



{{-- @endsection --}}
