@extends('layouts.app')

@isset($member)
    @section('breadcrumbs', Breadcrumbs::render('tm_members.edit', $member))
@else
    @section('breadcrumbs', Breadcrumbs::render('tm_members.create'))
@endif

@section('content')
    @if(config('app.production'))
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.min.js"
                integrity="sha256-chlNFSVx3TdcQ2Xlw7SvnbLAavAQLO0Y/LBiWX04viY=" crossorigin="anonymous"></script>
    @else
        <script src="https://cdnjs.cloudflare.com/ajax/libs/vue/2.6.10/vue.js"
                integrity="sha512-eGYNRo+9eOOAd/b4UZR8f2IdWNO3+6XrJMb5M1/wPvSUA7ABpSAT7uexDGt7fNfqhwVGI1L+0lEUm/n7ZqiL9A=="
                crossorigin="anonymous"></script>
    @endif
    <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.0/axios.min.js"
            integrity="sha256-S1J4GVHHDMiirir9qsXWc8ZWw74PHHafpsHp5PXtjTs=" crossorigin="anonymous"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet"
            type="text/css"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <!-- Basic layout-->
    <div class="card">
        <div class="card-body" id="app">
            <div class="row">
                <div class="col-md-8">

                    @isset($member)
                        {!! Form::model($member,['route'=>['task-manager.members.update', $member],'method'=>'put','class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) !!}
                    @else
                        {!! Form::open(['route'=>'task-manager.members.store','method'=>'post','class'=>'form-horizontal', 'enctype'=>'multipart/form-data']) !!}
                    @endisset

                    <div class="form-group row">
                        <label class="col-lg-3">{{ trans_choice('ui.photo', 1) }}</label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                <input id="image" name="photo" type="file" class="form-control-file"
                                        accept="image/jpg, image/jpeg, image/png, .heic">
                            </div>

                            @if ($errors->has('photo'))
                                <span class="invalid-feedbacks" role="alert">
                                    <strong>{{ $errors->first('photo') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('ui.form.first_name')}}</label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('first_name',old('first_name', $member->first_name ?? ""),['class'=>$errors->has('first_name')? 'form-control border-danger':'form-control','placeholder'=>__('ui.form.first_name')]) !!}
                                @if($errors->has('first_name'))
                                    <span class="form-text text-danger">{{$errors->first('first_name')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('ui.form.last_name')}}</label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('last_name',old('last_name', $member->last_name ?? ""),['class'=>$errors->has('last_name')? 'form-control border-danger':'form-control','placeholder'=>__('ui.form.last_name')]) !!}
                                @if($errors->has('last_name'))
                                    <span class="form-text text-danger">{{$errors->first('last_name')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('user.form.email')}}</label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::email('email',old('email', $member->email ?? ""),['class'=>$errors->has('email')? 'form-control border-danger':'form-control','placeholder'=>__('user.form.email')]) !!}
                                @if($errors->has('email'))
                                    <span class="form-text text-danger">{{$errors->first('email')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>


                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('user.form.password')}} </label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                <input class="form-control" autocomplete="off" id="password"
                                       placeholder="{{__('user.form.password')}}" name="password" type="text" value="">
                                <div class="form-control-feedback" onclick="generatePassword()">
                                    <i class="icon-circle-code"></i>
                                </div>
                                @if($errors->has('password'))
                                    <span class="form-text text-danger">{{$errors->first('password')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row {{count($userDealers) === 1 & !auth()->user()->isSuperAdmin() ? 'd-none' : ''}}" id="dealerFieldId123">
                        <label class="col-lg-3 col-form-label">{{trans_choice('ui.dealer',1)}}</label>
                        <div class="col-lg-9">
                            <select name="dealers[]" class="select2-vue custom-select" id="user-dealers" v-model="selectedDealers"></select>
                            <span id="messa" style="color: red"></span>
                        </div>
                    </div>

                    <div class="form-group row d-none">
                        <label class="col-lg-3 col-form-label">{{__('user.form.type')}}</label>
                        <div class="col-lg-9">
                            {!! Form::select('type',['tm_member'=>'Team Member'],
                                old('type', 'tm_member'),['id'=>'typeSelector','class'=> $errors->has('type')? 'form-control border-danger':'form-control']) !!}

                            @if($errors->has('type'))
                                <span class="form-text text-danger">{{$errors->first('type')}}</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('user.form.language')}}</label>
                        <div class="col-lg-9">
                            {!! Form::select('default_lang',['1'=> 'en','2'=> 'fr'],
                                old('default_lang', $member->default_lang ?? ""),['id'=>'default_lang','class'=> $errors->has('default_lang')? 'form-control border-danger text-uppercase':'form-control text-uppercase']) !!}

                            @if($errors->has('default_lang'))
                                <span class="form-text text-danger">{{$errors->first('default_lang')}}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('user.form.email_notify')}}</label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                <div class="custom-control form-control-lg custom-checkbox">
                                    <input type="checkbox" name="email_notify" class="custom-control-input"  id="email-notify"
                                    {{old('email_notify', (isset($member) ? ($member->email_notify ? 'on' : 0) : 'on')) === 'on' ? 'checked':''}}>
                                    <label class="d-block custom-control-label" for="email-notify"> @lang('ui.email') </label>
                                </div>

                                @if($errors->has('email_notify'))
                                    <span class="form-text text-danger">{{$errors->first('email_notify')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label class="col-lg-3 col-form-label">{{__('user.form.status')}}</label>
                        <div class="col-lg-9">
                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    @isset($member)
                                        {{ Form::radio('active','1',old('active'), ['class'=>'form-input-styled','data-fouc']) }} {{__('user.form.active')}}
                                    @else
                                        {{ Form::radio('active','1',1, ['class'=>'form-input-styled','data-fouc']) }}  {{__('user.form.active')}}
                                    @endisset
                                </label>
                            </div>

                            <div class="form-check form-check-inline">
                                <label class="form-check-label">
                                    {{ Form::radio('active','0',old('active'), ['class'=>'form-input-styled','data-fouc']) }}  {{__('user.form.inactive')}}
                                </label>
                            </div>
                        @if($errors->has('active'))
                        <span class="form-text text-danger">{{$errors->first('active')}}</span>
                        @endif

                        </div>
                        </div>


                    <div class="text-left">
                        <button type="submit" class="btn btn-primary"> {{__('user.form.save')}} <i class="icon-paperplane ml-2"></i>
                        </button>
                    </div>
                    {{ Form::close() }}

                </div>
                <!-- /col-md-8 -->
            </div>
        </div>
    </div>
@stop



@push('footer')

    <script>
        var store = {
            users: [],
            dealers: [],
            selectedDealers:{!! @json_encode(old('dealers', $selectedDealers)) !!},
        }
        var app = new Vue({
            el: '#app',
            data: store,
            created() {
                if(@json(auth()->user()->isSuperAdmin())){
                    axios.get("{{url('dealersGroup')}}", {params: {type:'member'}}).then((res) => {
                        store.dealers = res.data;
                        this.refreshSeletPicker();
                    });
                }else{
                    store.dealers = @json(auth()->user()->getDealers() ?? collect([]));
                    this.refreshSeletPicker();
                }
            },
            methods: {
                refreshSeletPicker: function(){
                    let options = '<option value=""></option>';
                    this.dealers.forEach(function (dealer) {
                        if(dealer.id == @json(old("dealers", $selectedDealers))){
                            options += `<option value="${dealer.id}" selected>${dealer.name}</option>`;
                        }else{
                            options += `<option value="${dealer.id}" >${dealer.name}</option>`;
                        }
                    });

                    $("#user-dealers").html(options);
                }
            }
        });
        $(function () {
            $('.select2-vue').select2({
                placeholder: "Dealer",
                allowClear: false,
            }).on('change', function (e) {
                app.selectedDealers = this.value
            });

            let fileinput_options = {
                theme: 'fas',
                language: '{{app()->getLocale()}}',
                showCaption: true,
                browseOnZoneClick: true,
                showUpload: false,
                initialPreviewAsData: true,
                initialPreview: @json(isset($member) ? [$member->photo] : []),
                initialPreviewConfig: @json(isset($member) ? $member->config : []),
                uploadAsync: false,
                overwriteInitial: true,
                append: false,
                maxFileCount: 1,
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
                    member: @json(isset($member) ? $member->id : ""),
                    _token: "{{ csrf_token() }}",
                },
                previewMarkupTags: {
                    tagBefore1: '<div class="file-preview-frame {frameClass}" id="{previewId}" data-fileindex="{fileindex}"' +
                        ' data-fileid="{fileid}" data-template="{template}">' +
                        '<div class="kv-file-content move drag-handle-init">\n',
                }
            };

            $("#image").fileinput(fileinput_options)
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
        });

    </script>
    <script>
        @if(@!$member->id)
        generatePassword();
        @endif

        function generatePassword() {
            var length = 10,
                charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789",
                retVal = "";
            for (var i = 0, n = charset.length; i < length; ++i) {
                retVal += charset.charAt(Math.floor(Math.random() * n));
            }

            $("input#password").val(retVal)
            return retVal;
        }

        $(document).ready(function () {
            function empty(n){
                return !(!!n ? typeof n === 'object' ? Array.isArray(n) ? !!n.length : !!Object.keys(n).length : true : false);
            }

            $(".form-horizontal").submit(function (event) {
                if (empty(store.selectedDealers)) {
                    $("#messa").text("Dealer list can not be null");
                    event.preventDefault();
                }
            });
        });
    </script>

    <style>
        .file-caption-main{
            display: none !important;
        }
        .file-preview{
            margin-bottom: 0 !important;
        }

        .kv-file-content img {
            width: 250px !important;
        }
    </style>
@endpush
