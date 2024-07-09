@extends('layouts.app')
@section('breadcrumbs', Breadcrumbs::render('tm_tasks.create'))
@section('content')
    <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.39.0/js/tempusdominus-bootstrap-4.min.js" integrity="sha512-k6/Bkb8Fxf/c1Tkyl39yJwcOZ1P4cRrJu77p83zJjN2Z55prbFHxPs9vN7q3l3+tSMGPDdoH51AEU8Vgo1cgAA==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/tempusdominus-bootstrap-4/5.0.1/css/tempusdominus-bootstrap-4.min.css" />

    @if(config('app.production'))
    <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js"
            integrity="sha256-chlNFSVx3TdcQ2Xlw7SvnbLAavAQLO0Y/LBiWX04viY=" crossorigin="anonymous"></script>
    @else
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.js"
                integrity="sha512-eGYNRo+9eOOAd/b4UZR8f2IdWNO3+6XrJMb5M1/wPvSUA7ABpSAT7uexDGt7fNfqhwVGI1L+0lEUm/n7ZqiL9A=="
                crossorigin="anonymous"></script>
    @endif

    <div class="card" id="app">
        <form method="post" action="{{ route('task-manager.tasks.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-group row mr-0 ml-0">
                <label for="title" class="col-lg-1 col-form-label mt-2 mb-2">Title</label>
                <div class="col-lg-7">
                    <input type="text" class="form-control mt-2 mb-2" id='title' name="title"  placeholder="Title" v-model="form.title" />
                    @if($errors->has('title'))
                        <span class="form-text text-danger">{{ $errors->first('title') }}</span>
                    @endif
                </div>

                <label for="assigned_to" class="col-lg-1 col-form-label mt-2">@lang('ui.assignee')</label>
                <div class="col-lg-3 mt-2">
                    <select name="assigner_id" class="form-control mt-2" id="assigner_id">
                        <option value="" selected>@lang('ui.assignee')</option>
                        @foreach ($users as $user)
                            <option value="{{ $user['id'] }}" {{ (old('assigner_id', auth()->user()->id) == $user['id']) ? 'selected' : '' }}>{{ $user['username'] }}</option>
                        @endforeach
                    </select>

                    @if($errors->has('assigner_id'))
                        <span class="form-text text-danger">{{ $errors->first('assigner_id') }}</span>
                    @endif
                </div>

                <label for="status" class="col-lg-1 col-form-label">@lang('ui.status')</label>
                <div class="col-lg-3">
                    <select name="status" id="status" class="form-control mt-2 mb-2 {{ $errors->has('status') ? 'form-control border-danger' : 'form-control' }}">
                        <option value="to do" {{old('status', "to do") === "to do" ? 'selected' : ''}}>To Do</option>
                        <option value="in progress" {{old('status', "to do") === "in progress" ? 'selected' : ''}}>In Progress</option>
                        <option value="cancelled" {{old('status', "to do") === "cancelled" ? 'selected' : ''}}>Cancelled</option>
                        <option value="not needed" {{old('status', "to do") === "not needed" ? 'selected' : ''}}>Not Needed</option>
                        <option value="refused" {{old('status', "to do") === "refused" ? 'selected' : ''}}>Refused</option>
                        <option value="in review" {{old('status', "to do") === "in review" ? 'selected' : ''}}>In Review</option>
                        <option value="done" {{old('status', "to do") === "done" ? 'selected' : ''}}>Done</option>
                    </select>

                    @if($errors->has('status'))
                        <span class="form-text text-danger">{{ $errors->first('status') }}</span>
                    @endif
                </div>

                <label for="priority" class="col-lg-1 col-form-label">@lang('ui.priority')</label>
                <div class="col-lg-3">
                    <select name="priority" id="priority" class="form-control mt-2 mb-2 {{ $errors->has('priority') ? 'form-control border-danger' : 'form-control' }}">
                        <option value="0" {{old('priority', 1) === 0 ? 'selected' : ''}}>Urgent</option>
                        <option value="1"  {{old('priority', 1) === 1 ? 'selected' : ''}}>Blocker</option>
                        <option value="2" {{old('priority', 1) === 2 ? 'selected' : ''}}>Major</option>
                        <option value="3" {{old('priority', 1) === 3 ? 'selected' : ''}}>Critical</option>
                        <option value="4" {{old('priority', 1) === 4 ? 'selected' : ''}}>Minor</option>
                        <option value="5" {{old('priority', 1) === 5 ? 'selected' : ''}}>Trivial</option>
                    </select>

                    @if($errors->has('priority'))
                        <span class="form-text text-danger">{{ $errors->first('priority') }}</span>
                    @endif
                </div>

                <label for="delivery_date" class="col-lg-1 col-form-label mb-2">Delivery</label>
                <div class="col-lg-3">
                    <div class="input-group date" id="delivery_date" data-target-input="nearest">
                        <input type="text" name="delivery_date" class="form-control datetimepicker-input" data-target="#delivery_date"/>
                        <div class="input-group-append" data-target="#delivery_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>

                    @if($errors->has('delivery_date'))
                        <span class="form-text text-danger">{{ $errors->first('delivery_date') }}</span>
                    @endif
                </div>
                <label for="reminder" class="col-lg-1 col-form-label mb-2">Reminder</label>
                <div class="col-lg-3 mb-2">
                    <div class="input-group date" id="reminder" data-target-input="nearest">
                        <input type="text" name="reminder" class="form-control datetimepicker-input" data-target="#reminder"/>
                        <div class="input-group-append" data-target="#reminder" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>

                    @if($errors->has('reminder'))
                        <span class="form-text text-danger">{{ $errors->first('reminder') }}</span>
                    @endif
                </div>
                <label for="links" class="col-lg-1 col-form-label mb-2">@lang('task.link')</label>
                <div class="col-lg-3 mb-2 linked">
                    <select name="links[]" class="form-control" multiple>
                        @if(!empty($linked))
                            <option value="{{$linked->id}}" selected><div> {{$linked->text}}</div></option>
                        @endif
                    </select>

                    @if($errors->has('links'))
                        <span class="form-text text-danger">{{ $errors->first('links') }}</span>
                    @endif
                </div>
                <div class="col-lg-4"></div>
                <label for="task-description" class="col-lg-1 col-form-label mt-2 mb-2">Description</label>
                <div class="col-lg-11">
                    <textarea name="description" id="task-description" rows="3" class="form-control mt-2 mb-2 {{ $errors->has('description') ? 'form-control border-danger' : 'form-control' }}">{{old('description')}}</textarea>

                    @if($errors->has('description'))
                        <span class="form-text text-danger">{{ $errors->first('description') }}</span>
                    @endif
                </div>

                <label class="col-lg-1 mt-3">Attachments: </label>
                <div class="col-lg-11 mt-3">
                    <div class="form-group-feedback form-group-feedback-right">
                        <input id="attachments" name="attachments[]" type="file" class="form-control-file" multiple>
                    </div>
                </div>
            </div>
            <div class="card-footer mt-3">
                <a href="{{ back()->getTargetUrl() }}" class="btn btn-secondary"><i class="icon-backspace2 mr-2"></i>{{__('ui.back')}}</a>
                <button type="submit" class="btn btn-primary float-right">{{__('ui.save')}}<i class="icon-paperplane ml-2"></i></button>
            </div>
        </form>
    </div>
    <script>
        var app = new Vue({
            el: '#app',
            data: function(){
                return{
                    form: {
                        linked:null,
                        tagClick:false,
                        title: @json(old("title"))
                    }
                }
            },

            watch:{
                "form.title": {
                    deep: true,
                    handler: function(){
                        this.form.title = (this.form.title).length > 120 ? ( this.form.title).substring(0, 120) : this.form.title
                    }
                }
            }
        });

        $(document).ready(function() {
            $('body').on('mousedown', '.linked .select2-selection__choice', function(e) {
                app.form.tagClick = false
                let text = ($(this).html()).replace(/<\/?span[^>]*>/g,"")
                text = text.replace('×',"")
                let data = $('select[name="links[]"').select2('data')
                data.forEach(item => {
                   if(item.text === text){
                    app.form.tagClick = true
                    if(item.link){
                        app.form.linked = item.link
                    }else{
                       let value = (item.id).split('-');
                      if((value[0]).toLowerCase() === 'vehicle'){
                        app.form.linked = route('cars.show', {'dealerId': value[2], 'carId': value[1]})
                      }else if((value[0]).toLowerCase() === 'deal'){
                        app.form.linked = route('leads.show', {'id': value[1]})
                      }else if((value[0]).toLowerCase() === 'contact'){
                        app.form.linked = route('contacts.show', {'id': value[1]})
                      }else if((value[0]).toLowerCase() === 'product'){
                        app.form.linked = route('inventory.others.details', {'product': value[1]})
                      }else{
                        app.form.tagClick = false
                      }
                    }
                   }
                })
            });

            if(@json(!empty(old("delivery_date")))){
                document.getElementById("delivery_date").defaultValue = @json(old("delivery_date"));
            }

            if(@json(!empty(old("reminder")))){
                document.getElementById("reminder").defaultValue = @json(old("reminder"));
            }
            $("select[name='status']").select2({
                containerCssClass: "{{ $errors->has('status')}}" ? "border-danger" : '',
                placeholder: 'Select status',
                minimumResultsForSearch: -1
            });

            $("select[name='priority']").select2({
                containerCssClass: "{{ $errors->has('priority')}}" ? "border-danger" : '',
                placeholder: 'Select priority',
                minimumResultsForSearch: -1
            });

            $("select[name='assigner_id']").select2({
                containerCssClass: "{{ $errors->has('assigner_id')}}" ? "border-danger" : '',
                placeholder: '@lang("ui.assignee")',
                minimumResultsForSearch: -1
            });

            $('#delivery_date').datetimepicker({
                defaultDate: @json(old('delivery_date', $delivered_at ?? null)),
                format: 'YYYY-MM-DD LT',
                useCurrent: false
            });
            $('#reminder').datetimepicker({
                defaultDate: @json(old('reminder')),
                format: 'YYYY-MM-DD LT',
                useCurrent: false
            });
            $('#task-description').summernote({
                placeholder: 'Enter description',
                tabsize: 2,
                height: 220,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link']],
                    ['view', ['help']]
                ]
            });

            $("select[name='links[]']").select2({
                placeholder: '@lang('task.doc_link')',
                allowClear: false,
                templateResult: formatCustom,
                escapeMarkup : function(markup) {
                    return markup;
                },
                ajax: {
                    url: route('task-manager.tasks.issues.fetch'),
                    dataType: 'json',
                    data: function(params) {
                        return {
                            term: params.term || '',
                            page: params.page || 1,
                        }
                    },
                    cache: true
                }
            }).on('select2:opening', function (e) {
                const $self = $(this);
                if( app.form.tagClick )
                {
                    e.preventDefault();
                    $self.select2('close');
                    app.form.tagClick = false;
                    if(app.form.linked) {
                        window.open(app.form.linked, '_blank').focus();
                    }
                }
            }).on('select2:unselecting', function (e){
                app.form.tagClick = false
            });
            function formatCustom(doc) {
                if (!doc.id) {
                    return doc.text;
                }
                $('.linked li').attr('title', '');
                return $(
                    '<div>' + doc.text + '<div class="text-muted ml-4">' + doc.subText +'</div></div>'
                );
            }

            let fileinput_options = {
                theme: 'fas',
                language: '{{app()->getLocale()}}',
                showCaption: true,
                browseOnZoneClick: true,
                showUpload: false,
                initialPreviewAsData: true,
                uploadAsync: false,
                overwriteInitial: false,
                append: true,
                maxFileCount: 40,
                maxFileSize: 12288,
                allowedFileTypes: ['image', 'object'],
                allowedFileExtensions: ['jpg', 'gif', 'png', 'heic'],
                disabledPreviewTypes: ['object'],
                dropZoneTitle: '@lang('ui.form.drop_zone_title')',
                dropZoneClickTitle: '@lang('ui.form.drop_zone_click_title')',
                msgPlaceholder: '@lang('ui.form.select_files')',
                msgSizeTooLarge: '{{__('car.car_filesize_dropzone_error')}}',
                fileActionSettings: {
                    showUpload: false,
                },
                deleteExtraData: {
                    _token: "{{ csrf_token() }}",
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

            $("#attachments").fileinput(fileinput_options)
            .on('filebeforedelete', function () {
                return new Promise(function (resolve, reject) {
                    $.confirm({
                        title: "{{ __('ui.confirmation') }}" + "!",
                        content: "{{ __('ui.form.are_you_sure_you_want_to_delete_this_item') }}",
                        type: 'red',
                        buttons: {
                            ok: {
                                btnClass: 'btn-primary text-white',
                                keys: ['enter'],
                                action: function () {
                                    resolve();
                                }
                            },
                            cancel: function () {
                                $.alert("{{ __('ui.form.file_deletion_was_aborted') }}" + '!');
                            }
                        }
                    });
                });
            })
            .on('filedeleted', function () {
                setTimeout(function () {
                    // $.alert('File deletion was successful! ');
                }, 900);
            });

            $('.linked li').attr('title', '');
            $('.linked').hover(function(e){
                $('.linked li').attr('title', '');
            });
        });

        function commentToggles() {
                $('#addComment').toggle();
            }
    </script>
    <style>
        .table-sm td, .table-sm th {
            padding: 7px 7px;
        }
        li[a]{ padding: 1rem; color: blue}

    </style>
@endsection

