@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Confirm To</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Confirm To</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Create Confirm To</b></h4>
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="confirm_to_form">
                                @csrf
                                <input type="hidden" name="confirm_to_id" value="{{!empty($confirm_to) ? $confirm_to->id : ''}}">
                                <input type="hidden" name="hidden_image" value="{{!empty($confirm_to) ? $confirm_to->image : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="name" id="name" value="{{!empty($confirm_to) ? $confirm_to->name : ''}}" required placeholder="Enter Name" />
                                    </div>
                                </div>
                               
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <a href="{{url('/confirm_to')}}" class="btn btn-default m-l-5">
                                            Cancel
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
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

    $("#confirm_to_form").validate({
        rules: {
            name: {
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

        if ($("#confirm_to_form").valid()) {
            var formData = new FormData($("#confirm_to_form")[0]);
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('confirm_to_store') }}",
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
                        window.location.href= "{{url('/confirm_to')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>
@endsection
