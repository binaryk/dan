@extends('frontend.layouts.app')

@section('title', app_name() . ' | Login')

@section('content')

    <div class="row">

        <div class="col-md-8 col-md-offset-2">

            <div class="panel panel-default">
                <div class="panel-heading">{{ trans('labels.frontend.auth.login_box_title') }}</div>

                <div class="panel-body">

                    {{ Form::open(['route' => 'frontend.auth.login.post', 'class' => 'form-horizontal', "enctype"=> "multipart/form-data"]) }}

                    <div class="form-group">
                        {{ Form::label('email', trans('validation.attributes.frontend.email'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::email('email', null,
                            ['class' => 'form-control', 'maxlength' => '191', 'required' => false, 'autofocus' => 'autofocus', 'placeholder' => trans('validation.attributes.frontend.email')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('password', trans('validation.attributes.frontend.password'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::password('password', ['class' => 'form-control', 'required' => false, 'placeholder' => trans('validation.attributes.frontend.password')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <video autoplay></video>
                            <img alt="" id="preview">
                            <canvas style="display: none;"></canvas>
                            <button type="button" id="take-photo" class="btn btn-sm btn-success">
                                Poza
                            </button>
                            <input type="hidden" id="photo_path" name="photo_path">
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <div class="checkbox">
                                <label>
                                    {{ Form::checkbox('remember') }} {{ trans('labels.frontend.auth.remember_me') }}
                                </label>
                            </div>
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            {{ Form::submit(trans('labels.frontend.auth.login_button'), ['class' => 'btn btn-primary', 'style' => 'margin-right:15px']) }}

                            {{ link_to_route('frontend.auth.password.reset', trans('labels.frontend.passwords.forgot_password')) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    {{ Form::close() }}

                    <div class="row text-center">
                        {!! $socialite_links !!}
                    </div>
                </div><!-- panel body -->

            </div><!-- panel -->

        </div><!-- col-md-8 -->

    </div><!-- row -->

@endsection
@section('after-scripts')
    <script src="{!! asset('js/Web.js') !!}"></script>
@endsection

@section('after-styles')
    <style>
        video{
            width: 300px;
            height: 250px;
        }
    </style>
@endsection
