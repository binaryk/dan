@extends('frontend.layouts.app')

@section('title', app_name() . ' | Register')

@section('content')
    <div class="row">

        <div class="col-md-8 col-md-offset-2">

            <div class="panel panel-default">
                <div class="panel-heading">{{ trans('labels.frontend.auth.register_box_title') }}</div>

                <div class="panel-body">

                    {{ Form::open(['route' => 'frontend.auth.register.post', 'class' => 'form-horizontal', "enctype"=> "multipart/form-data"]) }}

                    <div class="form-group">
                        {{ Form::label('first_name', trans('validation.attributes.frontend.first_name'),
                        ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::text('first_name', null,
                            ['class' => 'form-control', 'maxlength' => '191', 'required' => 'required', 'autofocus' => 'autofocus', 'placeholder' => trans('validation.attributes.frontend.first_name')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('last_name', trans('validation.attributes.frontend.last_name'),
                        ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::text('last_name', null,
                            ['class' => 'form-control', 'maxlength' => '191', 'required' => 'required', 'placeholder' => trans('validation.attributes.frontend.last_name')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('email', trans('validation.attributes.frontend.email'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::email('email', null, ['class' => 'form-control', 'maxlength' => '191', 'required' => 'required', 'placeholder' => trans('validation.attributes.frontend.email')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('password', trans('validation.attributes.frontend.password'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::password('password', ['class' => 'form-control', 'required' => 'required', 'placeholder' => trans('validation.attributes.frontend.password')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('password_confirmation', trans('validation.attributes.frontend.password_confirmation'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                            {{ Form::password('password_confirmation', ['class' => 'form-control', 'required' => 'required', 'placeholder' => trans('validation.attributes.frontend.password_confirmation')]) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    <div class="form-group">
                        {{ Form::label('password_confirmation', trans('validation.attributes.frontend.photo'), ['class' => 'col-md-4 control-label']) }}
                        <div class="col-md-6">
                           {{ Form::file('photo_path') }}
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            <div id="my_camera"></div><br>
                            <input type=button class="btn btn-xs btn-success"  value="Test Image" id="take_snapshot" style="display: none;">
                        </div>
                    </div>


                @if (config('access.captcha.registration'))
                        <div class="form-group">
                            <div class="col-md-6 col-md-offset-4">
                                {!! Form::captcha() !!}
                                {{ Form::hidden('captcha_status', 'true') }}
                            </div><!--col-md-6-->
                        </div><!--form-group-->
                    @endif

                    <div class="form-group">
                        <div class="col-md-6 col-md-offset-4">
                            {{ Form::submit(trans('labels.frontend.auth.register_button'), ['class' => 'btn btn-primary']) }}
                        </div><!--col-md-6-->
                    </div><!--form-group-->

                    {{ Form::close() }}

                </div><!-- panel body -->

            </div><!-- panel -->

        </div><!-- col-md-8 -->

    </div><!-- row -->
@endsection

@section('after-scripts')
    @if (config('access.captcha.registration'))
        {!! Captcha::script() !!}
    @endif
    <script src="{!! asset('js/webcam.js') !!}"></script>
    <script>
        $(document).ready(function() {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $('#test_mail').on('click',function(e){
                var mail = $('[name=email]').val();
                var that = this;
                $.get('users/check-mail/'+mail, function(data){
                    if(data['exists']){

                        Webcam.set({
                            width: 330,
                            height: 250,
                            image_format: 'jpeg',
                            jpeg_quality: 90
                        });
                        Webcam.attach( '#my_camera' );
                        $('#take_snapshot').css("display", "block");

                        $(that).closest('.form-group').removeClass('has-warning').find('.help-block').remove();
                        $(that).closest('.form-group').addClass('has-success').append('<p class=help-block>Success!</p>');
                        var base =  '{!! asset("image/") !!}';
                        $('#exists_image').attr('src',base +"/"+ data['exists']['image']+".png").parent().show();
                    }else{
                        $('#exists_image').attr('src','').parent().hide();
                        $(that).closest('.form-group').addClass('has-warning').append('<p class=help-block> User not exist!</p>');
                    }
                })
            });

            $('#take_snapshot').on('click',function(e){
                Webcam.snap( function(data_uri) {
                    $.ajax({
                        url: "{{ url('/check-image')}} ",
                        type: "post",
                        data: {'email':$('[name=email]').val(), 'image' : data_uri},
                        success: function(data){
                            var userImage = "data:image/jpeg;base64," + data.user_image;
                            var currentImage = "data:image/jpeg;base64," + data.this_image;

                            var api = resemble(userImage).compareTo(currentImage).onComplete(function(data){
                                $('#match-percentage').css("display","block");
                                $('#match-percentage .progress-bar').css("width", 100 - data.misMatchPercentage + '%');
                                if (parseInt(data.misMatchPercentage) < parseInt(85)) {
                                    toastr.success((100 - data.misMatchPercentage), 'Match percentage:');
                                    $('#show_login').css("display", "block");
                                } else {
                                    toastr.warning('Try Again or Register Again!');
                                    toastr.error((data.misMatchPercentage),'Difference percentage too big:');
                                    $('#show_login').css("display", "none");
                                }
                            });

                        }
                    });
                });
            });
        })

    </script>
@endsection