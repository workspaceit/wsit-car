@extends('layouts.app')

@section('breadcrumbs', Breadcrumbs::render('issues.edit'))

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

    {{--Bootstrap Select--}}
    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/js/bootstrap-select.min.js"></script>
    <link href="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.css" rel="stylesheet"
          type="text/css"/>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-confirm/3.3.2/jquery-confirm.min.js"></script>
    <meta name="csrf-token" content="{{csrf_token()}}">
    <div class="card" id="app">
        {!! Form::open([ 'route'=>['supports.update'],'method'=>'post','class'=>'form-horizontal', 'enctype'=>"multipart/form-data"]) !!}

        <div class="card-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">@lang('ui.name')<span class="text-danger"><sup>*</sup></span></label>
                        <div class="col-md-9">
                            {!! Form::text('name',old('name'),['class'=>$errors->has('name')? 'form-control border-danger':'form-control','placeholder'=>'' ]) !!}
                            @if($errors->has('name'))
                                <span class="form-text text-danger">{{ $errors->first('name') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">@lang('ui.email')<span class="text-danger"><sup>*</sup></span></label>
                        <div class="col-md-9">
                            {!! Form::text('email',old('email'),['class'=>$errors->has('email')? 'form-control border-danger':'form-control','placeholder'=>'' ]) !!}
                            @if($errors->has('email'))
                                <span class="form-text text-danger">{{ $errors->first('email') }}</span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-md-3 col-form-label">@lang('ui.phone number')<span class="text-danger"><sup>*</sup></span></label>
                        <div class="col-md-9">
                            {!! Form::text('phone',old('phone'),['class'=>$errors->has('phone')? 'form-control border-danger':'form-control','placeholder'=>'','id'=>'phone','minlength'=>10 ]) !!}
                            @if($errors->has('phone'))
                                <span class="form-text text-danger">{{ $errors->first('phone') }}</span>
                            @endif
                            {!! Form::text(null,null,['class'=>'form-control d-none','id'=>'temp_phone' ]) !!}
                        </div>
                    </div>
                    <div class="form-group row mb-3">
                        <label class="col-lg-3">{{ trans_choice('ui.ticket',1) }}</label>
                        <div class="col-lg-9">
                            <div class="float-left">
                            {{ $issue->ticket_no }}
                            </div>
                        </div>
                    </div>
                    {!! Form::hidden('issue_id', $issue->id) !!}
                    <div class="form-group row mb-3">
                        <label class="col-lg-3">@lang('ui.type')<span class="text-danger"><sup>*</sup></span> <sub><br>
                        ({{__('ui.type_of_issue')}})</sub></label>
                        <div class="col-lg-9">
                            <div class="float-left">
                                <div class="form-check form-check-inline mt-0">
                                    {!! Form::radio('type', 'General', true, ['class' => 'form-check-input', 'id' => 'radio111']) !!}
                                    {!! Form::label('radio111', __('ui.general'), ['class' => 'form-check-label']) !!}

                                </div>
                                <div class="form-check form-check-inline mt-0">
                                    {!! Form::radio('type', 'Dealer Feed', false, ['class' => 'form-check-input', 'id' => 'radio112']) !!}
                                    {!! Form::label('radio112', trans_choice('ui.dealer', 1).' Feed', ['class' => 'form-check-label']) !!}
                                </div>
                                <div class="form-check form-check-inline mt-0">
                                    {!! Form::radio('type', 'Billing', false, ['class' => 'form-check-input', 'id' => 'radio113']) !!}
                                    {!! Form::label('radio113', __('ui.billing'), ['class' => 'form-check-label']) !!}
                                </div>
                            </div>
                            @if($errors->has('type'))
                                <span class="form-text text-danger">{{ $errors->first('type') }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row mb-3 dealer-div d-none">
                        <label class="col-md-3 col-form-label">{{trans_choice('ui.dealer', 1)}}<span class="text-danger"><sup>*</sup></span> <sub><br>
                        ({{__('ui.select_one_dealer')}})</sub></label>
                        <div class="col-md-9">
                            <select name="dealer_id" class="custom-select">
                                <option value="0">@lang('ui.all')</option>
                                @foreach($dealers as $item)
                                    <option value="{{ $item->id }}" {{ old('dealer_id') == $item->id ? 'selected' : '' }}>{{ $item->name }}</option>
                                @endforeach
                            </select>
                            @if ($errors->has('dealer_id'))
                                <span class="invalid-feedbacks text-danger" role="alert">
                                    <strong>{{ $errors->first('dealer_id') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="form-group row mb-3">
                        <label class="col-lg-3">{{__('ui.description')}}<span class="text-danger"><sup>*</sup></span> <sub><br>
                        ({{__('ui.please_describe_issue')}})</sub> </label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::textarea('description', old('description'), ['class'=>$errors->has('description')? 'form-control border-danger':'form-control','placeholder'=>'' ]) !!}
                                @if($errors->has('description'))
                                    <span class="form-text text-danger">{{ $errors->first('description') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row" style="margin-bottom: 8rem;">
                        <label class="col-lg-3">{{trans_choice('ui.photo', 2)}} <sub><br>({{__('ui.attach_photo')}})</sub></label>
                        <div class="col-lg-9">
                            <div class="form-group-feedback form-group-feedback-right">
                                <input id="image" name="photos[]" type="file" class="form-control-file" multiple
                                        accept="image/jpg, image/jpeg, image/png, .heic">
                            </div>

                            @if ($errors->has('photos'))
                                <span class="invalid-feedbacks text-danger" role="alert">
                                    <strong>{{ $errors->first('photos') }}</strong>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div class="text-right">
                <a class="btn btn-danger" id="single_profile_back"
                    href="javascript: history.go(-1)">@lang('ui.form.cancel')</a>
                <button type="submit" class="btn btn-primary">@lang('ui.form.save') <i class="icon-paperplane ml-2"></i>
                </button>
            </div>
        </div>
        {!! Form::close() !!}
    </div>
@stop

<style>
    label {
        font-weight: bold;
    }
    input.form-control {
        margin-bottom: 5px;
    }
    input.loading {
        background: url(/images/loading.gif) no-repeat right center;
    }
    span.select2-selection.select2-selection--single {
        height: 100%;
    }
    .issue-description{
        max-height: 50px;

    }
    sub, sup {
        font-size: 72% !important;
    }
    .col-form-label {
        padding-top: 0 !important;
    }
    .file-footer-caption, .file-drag-handle {
        display: none !important;
    }
    .file-other-icon{
        display: none;
    }
    .kv-preview-data.file-preview-other-frame.file-zoom-detail img{
        width: 100% !important;
    }
</style>

@push('footer')
    <script src="/backend_assets/global_assets/js/plugins/media/heic2any.min.js"></script>
    <script>
        let dealers = @json($dealers);
        let issuePhotos = @json( $issue->photos );
        let issuePhotosConfig = @json( $issue->photo_config );

        let fileinput_options = {
            theme: 'fas',
            language: '{{app()->getLocale()}}',
            showCaption: false,
            browseOnZoneClick: true,
            showUpload: false,
            initialPreviewAsData: true,
            uploadAsync: false,
            overwriteInitial: false,
            initialPreview: issuePhotos,
            initialPreviewConfig: issuePhotosConfig,
            maxFileCount: 10,
            allowedFileTypes: ['image', 'object'],
            allowedFileExtensions: ['jpg', 'gif', 'png', 'heic'],
            dropZoneTitle: '@lang('ui.form.drop_zone_title')',
            dropZoneClickTitle: '@lang('ui.form.drop_zone_click_title')',
            msgPlaceholder: '@lang('ui.form.select_files')',
            fileActionSettings: {
                showUpload: false,
            },
            previewMarkupTags: {
                tagBefore1: '<div class="file-preview-frame {frameClass}" id="{previewId}" data-fileindex="{fileindex}"' +
                    ' data-fileid="{fileid}" data-template="{template}">' +
                    '<div class="kv-file-content move drag-handle-init">\n',
            },
        };

        $(document).ready(function () {
            if($('input[name="type"]:checked').val()=='Dealer Feed')
                $('.dealer-div').removeClass('d-none');
            // Initiate FileInput
            $("#image").fileinput(fileinput_options)
                .on('filebatchuploadsuccess', function (event, data) {
                    fileinput_options.initialPreview = data.response.initialPreview;
                    fileinput_options.initialPreviewConfig = data.response.initialPreviewConfig;
                })
                .on('filebatchuploadcomplete', function (event, data) {
                    $("#image").fileinput('destroy');
                    $(event.target).fileinput(fileinput_options);
                })
                .on('fileloaded', function(event, file, previewId, fileId, index, reader) {

                    let divElement = "div[data-fileid='"+fileId+"']";
                    $(divElement+" .kv-preview-data").remove();

                    let divPreview = $(divElement+" .kv-file-content");
                    divPreview.css("width", "200");
                    divPreview.prepend(`<img src="/images/loading.gif" class="file-preview-image kv-preview-data loading" title="heic.jpg"
                            alt="heic.jpg" style="width:50px;margin-top:56px;"> `);

                    fetch(URL.createObjectURL(file))
                    .then((res) => res.blob())
                    .then((blob) => heic2any({
                        blob,
                        toType: "image/jpeg",
                        quality: 0.7, //set quality
                    }))
                    .then((conversionResult) => {

                        var url = URL.createObjectURL(conversionResult);
                        console.log('Conversion Result');
                        console.log(url);

                        $(divElement+" .loading").remove();

                        divPreview.css("width", "");
                        divPreview.prepend(`<img src="`+url+`" class="file-preview-image kv-preview-data" title="heic.jpg"
                            alt="heic.jpg" style="width: auto; height: auto; max-width: 100%; max-height: 100%;"> `);

                        // $(this).attr('data-url', v.src);
                        // $(this).attr("src", url);
                    })
                    .catch((e) => {
                        console.log(e);
                    });
                })
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


            $(".border-danger.select2-hidden-accessible").next().css({"border": "1px solid #f44336"});

            $('input[type=radio][name=type]').change(function () {
                if (this.value == 'Dealer Feed') {
                    $('.dealer-div').removeClass('d-none');
                } else
                    $('.dealer-div').addClass('d-none');
            });

            var radioValue = "{{ $issue->type }}";
            if (radioValue == 'General') {
                $("#radio111").prop("checked", true);
            }else if(radioValue == 'Dealer Feed'){
                $("#radio112").prop("checked", true);
                $('.dealer-div').removeClass('d-none');
            } else {$("#radio113").prop("checked", true);}

            $('[name=name]').val("{{ $issue->name }}");
            $('[name=email]').val("{{ $issue->email }}");
            $('[name=phone]').val("{{ $issue->phone }}");
            $('[name=dealer_id]').val({{ $issue->dealer_id }});
            $("textarea").val(`{{ $issue->description }}`);

            $.ajaxSetup({
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                }
            });


            $("input[id='phone']").on("input", function () {
                $("input[id='temp_phone']").val(destroyMask(this.value));
                this.value = createMask($("input[id='temp_phone']").val());
            });
        });

        function createMask(string) {
            return string.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
        }

        function destroyMask(string) {
            return string.replace(/\D/g, '').substring(0, 10);
        }

    </script>
@endpush
