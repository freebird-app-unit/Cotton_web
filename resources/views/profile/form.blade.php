@extends('layouts.app')

@section('content')
<style>
    .img_display{
        border: 2px solid #ddd;
        border-radius: 5px;
        padding: 5px;
        height: 80px;
        width: 90px;
        margin-top: 3px;
        margin-right: 15px;
    }
</style>
<div class="content">
    <div class="wraper container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Profile</h4>
                <ol class="breadcrumb">
                    <li><a href="#">Home</a></li>
                    <li class="active">Profile</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-lg-3">
                <div class="profile-detail card-box">
                    <div>
                        @if(!empty($user->image))
                            @if(file_exists(storage_path('app/public/profile/' . $user->image)))
                                <img src="{{url('storage/app/public/profile/'.$user->image)}}" class="img-circle profile_img" alt="profile-image">
                            @endif
                        @endif

                        <hr>
                        <h4 class="text-uppercase font-600">About Me</h4>

                        <div class="text-left">
                            <p class="text-muted font-13"><strong>Full Name :</strong> <span class="m-l-15">{{!empty($user) ? $user->name : ''}}</span></p>

                            <p class="text-muted font-13"><strong>Mobile :</strong><span class="m-l-15">{{!empty($user) ? $user->mobile_no : ''}}</span></p>

                            <p class="text-muted font-13"><strong>Email :</strong> <span class="m-l-15">{{!empty($user) ? $user->email : ''}}</span></p>


                        </div>

                    </div>

                </div>

            </div>
            <div class="col-lg-9 col-md-8">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Profile</b></h4>
                        </div>
                        <div class="col-sm-1">
                        </div>
                    </div>
                    <form class="form-horizontal group-border-dashed" method="post" id="profile_form" action="javascript:void(0)">
                        @csrf
                        <input type="hidden" name="user_id" value="{{!empty($user) ? $user->id : ''}}">
                        <input type="hidden" name="hidden_image" id="hidden_image" value="{{!empty($user) ? $user->image : ''}}">

                        <div class="form-group">
                            <label class="col-sm-3 control-label">Name</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="name" value="{{!empty($user) ? $user->name : ''}}" required placeholder="Enter Name" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Profile Image</label>
                            <div class="col-sm-6">
                                <input type="file" class="form-control" name="image" id="image" accept="image/png, image/jpeg, image/jpg"/>
                                @if(!empty($user->image))
                                    @if(file_exists(storage_path('app/public/profile/' . $user->image)))
                                    <img src="{{url('storage/app/public/profile/'.$user->image)}}" height="50" width="50" class="img_display">
                                    @endif
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Mobile No</label>
                            <div class="col-sm-6">
                                <input type="text" class="form-control" name="mobile_no" value="{{!empty($user) ? $user->mobile_no : ''}}" required placeholder="Enter Mobile No" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                <button type="button" class="btn btn-primary" id="btn_sub">
                                    Update
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-12 col-md-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Change Password</b></h4>
                        </div>
                        <div class="col-sm-1">
                        </div>
                    </div>
                    <form class="form-horizontal group-border-dashed" method="post" id="change_password" action="javascript:void(0)">
                        @csrf
                        <input type="hidden" name="hidden_email" value="{{!empty($user) ? $user->email : ''}}" />
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Current Password</label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" name="current_password" id="current_password" required placeholder="Enter Current Password" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">New Password</label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" name="new_password" id="new_password" required placeholder="Enter New Password" />
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">Confirm Password</label>
                            <div class="col-sm-6">
                                <input type="password" class="form-control" name="confirm_password" id="confirm_password" required placeholder="Enter Confirm Password" />
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                <button type="button" class="btn btn-primary" id="change_pswd">
                                    Change Password
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script src="{{asset('public/jquery-validator/jquery.validate.min.js')}}"></script>
<script src="{{asset('public/jquery-validator/jquery.validate.js')}}"></script>
<script src="{{asset('public/jquery-validator/jquery-ui.js')}}"></script>
<script type="text/javascript">

    $("#setting_form").validate({
        rules: {
            name: {
                required : true,
            },
            mobile_no: {
                required : true,
            },
        },
        highlight: function(element) {
            $(element).removeClass('is-valid').addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
    });

    $(document.body).on('click', '#btn_sub', function(e) {
        e.preventDefault();
        var hidden_image = $('#hidden_image').val();

        if(!hidden_image){
            $('#image').prop('required',true);
        }else{
            $('#image').prop('required',false);
        }

        var formData = new FormData($("#profile_form")[0]);
        if ($("#profile_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('profile_store') }}",
                type: "POST",
                dataType: 'JSON',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);

                        window.location.href= "{{url('/profile')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    $("#change_password").validate({
        rules: {
            current_password  : "required",
            new_password      : "required",
            confirm_password  :
                {
                    required : true,
                    equalTo: "#new_password"
                },
        },
        messages: {
            confirm_password :
                {
                    equalTo : "Enter Confirm Password Same as New Password",
                },
        },

        highlight: function(element) {
            $(element).removeClass('is-valid').addClass('is-invalid');
        },
        unhighlight: function(element) {
            $(element).removeClass('is-invalid').addClass('is-valid');
        },
    });

    $(document.body).on('click', '#change_pswd', function(e) {
        e.preventDefault();
        var formData = new FormData($("#change_password")[0]);
        if ($("#change_password").valid()) {
            $.ajax({
                url: "{{route('change_password')}}",
                type: "POST",
                dataType: 'JSON',
                data: formData,
                cache: false,
                contentType: false,
                processData: false,
                success: function(data) {

                    if (data.response_status == "success") {
                        toastr.success(data.message);
                        $('#current_password').val('');
                        $('#new_password').val('');
                        $('#confirm_password').val('');
                    }
                    if (data.response_status == "error"){
                        toastr.error(data.message);
                    }
                },

            });
        }
    });
</script>
@endsection
