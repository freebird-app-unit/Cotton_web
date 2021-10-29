@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Station</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Station</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Create Station</b></h4>
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="station_form">
                                @csrf
                                <input type="hidden" name="station_id" value="{{!empty($station) ? $station->id : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Country Name</label>
                                    <div class="col-sm-6">
                                        <select name="country_id" class="form-control" id="country_id">
                                            <option selected disabled>Select</option>
                                            @if(!empty($country) && count($country) > 0)
                                                @foreach($country as $val)
                                                    <option value="{{$val->id}}" {{!empty($station) && $station->country_id == $val->id ? 'selected' : ''}}>{{$val->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">State Name</label>
                                    <div class="col-sm-6">
                                        <select name="state_id" class="form-control" id="state_id">
                                            <option selected disabled>Select</option>
                                            @if(!empty($state) && count($state) > 0)
                                                @foreach($state as $val)
                                                    <option value="{{$val->id}}" {{!empty($station) && $station->state_id == $val->id ? 'selected' : ''}}>{{$val->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">City Name</label>
                                    <div class="col-sm-6">
                                        <select name="city_id" class="form-control" id="city_id">
                                            <option selected disabled>Select</option>
                                            @if(!empty($city) && count($city) > 0)
                                                @foreach($city as $val)
                                                    <option value="{{$val->id}}" {{!empty($station) && $station->city_id == $val->id ? 'selected' : ''}}>{{$val->name}}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Station Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="name" id="name" value="{{!empty($station) ? $station->name : ''}}" required placeholder="Enter Station Name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <a href="{{url('/station')}}" class="btn btn-default m-l-5">
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

    $("#station_form").validate({
        rules: {
            name: {
                required : true,
            },
            country_id: {
                required : true,
            },
            state_id: {
                required : true,
            },
            city_id: {
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

        if ($("#station_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('station_store') }}",
                type: "POST",
                dataType: 'JSON',
                data: $("#station_form").serialize(),
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);
                        window.location.href= "{{url('/station')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

    $(document.body).on('change', '#country_id', function(e) {

        var country_id = $('#country_id').val();

        $.ajax({
            url: "{{ route('state_list') }}",
            type: "POST",
            dataType: 'JSON',
            data: {"_token" : "{{ csrf_token() }}", country_id : country_id},
            success: function(data) {
                $('#state_id').html('');
                var html = '<option value="">Select</option>';
                if (data.length > 0) {
                    $.each(data, function( index, value ) {
                        html += '<option value="'+value.id+'">'+value.name+'</option>';
                    });
                }
                $('#state_id').html(html);
            },
            error: function() {}
        });
    });
    $(document.body).on('change', '#state_id', function(e) {

        var state_id = $('#state_id').val();

        $.ajax({
            url: "{{ route('city_list') }}",
            type: "POST",
            dataType: 'JSON',
            data: {"_token" : "{{ csrf_token() }}", state_id : state_id},
            success: function(data) {
                $('#city_id').html('');
                var html = '<option value="">Select</option>';
                if (data.length > 0) {
                    $.each(data, function( index, value ) {
                        html += '<option value="'+value.id+'">'+value.name+'</option>';
                    });
                }
                $('#city_id').html(html);
            },
            error: function() {}
        });
    });

</script>
@endsection
