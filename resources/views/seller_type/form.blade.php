@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Seller Type</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Seller Type</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Create Seller Type</b></h4>
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="seller_form">
                                @csrf
                                <input type="hidden" name="seller_id" value="{{!empty($seller) ? $seller->id : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Seller Type Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="name" id="name" value="{{!empty($seller) ? $seller->name : ''}}" required placeholder="Enter Seller Type Name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <a href="{{url('/seller_type')}}" class="btn btn-default m-l-5">
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

    $("#seller_form").validate({
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

        if ($("#seller_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('seller_type_store') }}",
                type: "POST",
                dataType: 'JSON',
                data: $("#seller_form").serialize(),
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);
                        window.location.href= "{{url('/seller_type')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>
@endsection
