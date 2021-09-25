<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="A fully featured admin theme which can be used to build CRM, CMS, etc.">
        <meta name="author" content="Coderthemes">

        <link rel="shortcut icon" href="{{asset('public/assets/images/favicon.png')}}">

        <title>e-Cotton</title>

        <link href="{{asset('public/assets/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/core.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/components.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/icons.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/pages.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/responsive.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset('public/assets/css/toastr.css')}}" rel="stylesheet" type="text/css" />

        <script src="{{('public/assets/js/modernizr.min.js')}}"></script>

    </head>
    <body>

        <div class="account-pages"></div>
        <div class="clearfix"></div>
        <div class="wrapper-page">
        	<div class=" card-box">
                <div class="panel-heading">
                    <h3 class="text-center"> Sign In to <strong class="text-custom">e-Cotton</strong> </h3>
                </div>

                <div class="panel-body">
                    <form class="form-horizontal m-t-20" id="login_form" method="post" action="javascript:void(0)">
                        @csrf

                        <div class="form-group ">
                            <div class="col-xs-12">
                                <input class="form-control" type="text" required="" name="email" id="email" placeholder="Username">
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-xs-12">
                                <input class="form-control" type="password" name="password" id="password" required="" placeholder="Password">
                            </div>
                        </div>

                        <!-- <div class="form-group ">
                            <div class="col-xs-12">
                                <div class="checkbox checkbox-primary">
                                    <input id="checkbox-signup" type="checkbox">
                                    <label for="checkbox-signup">
                                        Remember me
                                    </label>
                                </div>

                            </div>
                        </div>
                        -->
                        <div class="form-group text-center m-t-40">
                            <div class="col-xs-12">
                                <button type="button" class="btn btn-pink btn-block text-uppercase waves-effect waves-light" id="btn_sub" >Log In</button>
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>

    	<script>
            var resizefunc = [];
        </script>

        <script src="{{asset('public/assets/js/jquery.min.js')}}"></script>
        <script src="{{asset('public/assets/js/bootstrap.min.js')}}"></script>
        <script src="{{asset('public/assets/js/detect.js')}}"></script>
        <script src="{{asset('public/assets/js/fastclick.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.slimscroll.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.blockUI.js')}}"></script>
        <script src="{{asset('public/assets/js/waves.js')}}"></script>
        <script src="{{asset('public/assets/js/wow.min.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.nicescroll.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.scrollTo.min.js')}}"></script>
        <script src="{{asset('public/assets/js/jquery.core.js')}}"></script>

        <script src="{{asset('public/assets/js/jquery.app.js')}}"></script>

        <script src="{{asset('public/jquery-validator/jquery.validate.min.js')}}"></script>
        <script src="{{asset('public/jquery-validator/jquery.validate.js')}}"></script>
        <script src="{{asset('public/jquery-validator/jquery-ui.js')}}"></script>
        <script src="{{asset('public/assets/js/toastr.js')}}"></script>

        <script>

            $("#login_form").validate({
                rules: {
                    email: {
                        email : true,
                        required : true,

                    },
                    password: {
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
                var formData = new FormData($("#login_form")[0]);
                var password = $('#password').val()
                var email = $('#email').val()
                formData.append('email',email);
                formData.append('password',password);

                console.log(formData)
                if ($("#login_form").valid()) {
                $.ajax({
                        url: "{{ url('login_admin') }}",
                        type: "POST",
                        data: {
                            "_token": "{{ csrf_token() }}",
                            email : email,
                            password : password,

                        },
                        cache: false,
                        success: function(data) {
                            if(data.response_status == "success"){
                                toastr.success(data.message);
                                window.location.href = "{{route('dashboard')}}";
                            } else {
                                toastr.error(data.message);
                            }
                        }
                    });
                }
            });
        </script>
	</body>
</html>
