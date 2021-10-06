@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Transmit Condition</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Transmit Condition</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Create Transmit Condition</b></h4>
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="transmit_form">
                                @csrf
                                <input type="hidden" name="transmit_id" value="{{!empty($transmit) ? $transmit->id : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Transmit Condition Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="name" id="name" value="{{!empty($transmit) ? $transmit->name : ''}}" required placeholder="Enter Transmit Condition Name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Dispatch</label>
                                    <div class="col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_dispatch" value="0" id="flexRadioDefault1" {{!empty($transmit) && $transmit->is_dispatch == 0 ? 'checked' : 'checked'}}>
                                            <label class="form-check-label" for="flexRadioDefault1">
                                            No
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="is_dispatch" value="1" id="flexRadioDefault2" {{!empty($transmit) && $transmit->is_dispatch == 0 ? 'checked' : ''}}>
                                            <label class="form-check-label" for="flexRadioDefault2">
                                            Yes
                                            </label>
                                        </div>
                                    </div>

                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <a href="{{url('/transmit_condition')}}" class="btn btn-default m-l-5">
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

    $("#transmit_form").validate({
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

        if ($("#transmit_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('transmit_condition_store') }}",
                type: "POST",
                dataType: 'JSON',
                data: $("#transmit_form").serialize(),
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);
                        window.location.href= "{{url('/transmit_condition')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>
@endsection
