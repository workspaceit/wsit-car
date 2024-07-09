@extends('layouts.app')
@section('breadcrumbs', Breadcrumbs::render('settings'))

@section('content')


    <!-- Basic layout-->
    <div class="card">

        <div class="card-body">
            <div class="row">
                <div class="col-md-9">
                    {!! Form::model($settings,['route'=>['settings.update'],'method'=>'post','class'=>'form-horizontal']) !!}

                    <fieldset>
                        <legend><strong> {{__('ui.monezftp_settings.title')}} </strong></legend>
                    </fieldset>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">{{__('ui.monezftp_settings.server')}}</label>
                        <div class="col-lg-8">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('ftp_server',old('ftp_server', $settings->ftp_server ?? null),['class'=>$errors->has('ftp_server')? 'form-control border-danger':'form-control','placeholder'=>'{{__("ui.monezftp_settings.server")}}']) !!}
                                @if($errors->has('ftp_server'))
                                    <span class="form-text text-danger">{{$errors->first('ftp_server')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">{{__("ui.monezftp_settings.username")}}</label>
                        <div class="col-lg-8">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('ftp_user_name',old('ftp_user_name', $settings->ftp_user_name ?? null),['class'=>$errors->has('ftp_user_name')? 'form-control border-danger':'form-control','placeholder'=>'{{__("ui.monezftp_settings.username")}}']) !!}
                                @if($errors->has('ftp_user_name'))
                                    <span class="form-text text-danger">{{$errors->first('ftp_user_name')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">{{__("ui.monezftp_settings.password")}}</label>
                        <div class="col-lg-8">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('ftp_user_pass',old('ftp_user_pass', $settings->ftp_user_pass ?? null),['class'=>$errors->has('ftp_user_pass')? 'form-control border-danger':'form-control','placeholder'=>'{{__("ui.monezftp_settings.password")}}']) !!}
                                @if($errors->has('ftp_user_pass'))
                                    <span class="form-text text-danger">{{$errors->first('ftp_user_pass')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="form-group row">
                        <label class="col-lg-4 col-form-label">{{__("ui.monezftp_settings.port")}}</label>
                        <div class="col-lg-8">
                            <div class="form-group-feedback form-group-feedback-right">
                                {!! Form::text('ftp_port',old('ftp_port', $settings->ftp_port ?? 21),['class'=>$errors->has('ftp_port')? 'form-control border-danger':'form-control','placeholder'=> __("ui.monezftp_settings.port")]) !!}
                                @if($errors->has('ftp_port'))
                                    <span class="form-text text-danger">{{$errors->first('ftp_port')}}</span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="text-left">
                        <button type="submit" class="btn btn-primary"> @lang('ui.save') <i
                                class="icon-paperplane ml-2"></i>
                        </button>
                    </div>
                    {!! Form::close() !!}

                </div>
                <div class="col-md-3"></div>

            </div>
        </div>
    </div>
@stop
