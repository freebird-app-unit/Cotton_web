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
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Settings</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Settings</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Settings</b></h4>
                        </div>
                        <div class="col-sm-1">
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="setting_form">
                                @csrf
                                <input type="hidden" name="setting_id" value="{{!empty($setting) ? $setting->id : ''}}">
                                <input type="hidden" name="hidden_site_logo" id="hidden_site_logo" value="{{!empty($setting) ? $setting->site_logo : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Site Name</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="site_name" id="site_name" value="{{!empty($setting) ? $setting->site_name : ''}}" required placeholder="Enter Site Name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Site Email</label>
                                    <div class="col-sm-6">
                                        <input type="email" class="form-control" name="site_email" id="site_email" value="{{!empty($setting) ? $setting->site_email : ''}}" required placeholder="Enter Site Email" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Site Contact</label>
                                    <div class="col-sm-6">
                                        <input type="text" class="form-control" name="site_contact" id="site_contact" value="{{!empty($setting) ? $setting->site_contact : ''}}" required placeholder="Enter Site Contact" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Site Address</label>
                                    <div class="col-sm-6">
                                        <textarea class="form-control" name="site_address" id="site_address" required placeholder="Enter Site Address">{{!empty($setting) ? $setting->site_address : ''}}</textarea>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Site Logo</label>
                                    <div class="col-sm-6">
                                        <input type="file" class="form-control" name="site_logo" id="site_logo" accept="image/png, image/jpeg, image/jpg"/>
                                        @if(!empty($setting->site_logo))
                                            @if(file_exists(storage_path('app/public/profile/' . $setting->site_logo)))
                                            <img src="{{url('storage/app/public/profile/'.$setting->site_logo)}}" height="50" width="50" class="img_display">
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Negotiation Count</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control" name="negotiation_count" id="negotiation_count" value="{{!empty($setting) ? $setting->negotiation_count : ''}}" required placeholder="Enter Negotiation Count" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Bunch</label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control" name="bunch" id="bunch" value="{{!empty($setting) ? $setting->bunch : ''}}" required placeholder="Enter Bunch" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Broker Commission <small>(Per Bales)</small></label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control" name="broker_commission" id="broker_commission" value="{{!empty($setting) ? $setting->broker_commission : ''}}" required placeholder="Enter Broker Commission" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Company Commission <small>(Per Bales)</small></label>
                                    <div class="col-sm-6">
                                        <input type="number" class="form-control" name="company_commission" id="company_commission" value="{{!empty($setting) ? $setting->company_commission : ''}}" required placeholder="Enter Company Commission" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <button type="reset" class="btn btn-default m-l-5">
                                            Cancel
                                        </button>
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

    $("#setting_form").validate({
        rules: {
            site_name: {
                required : true,
            },
            site_email: {
                required : true,
            },
            site_contact: {
                required : true,
            },
            site_address: {
                required : true,
            },
            negotiation_count: {
                required : true,
            },
            bunch: {
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
        var hidden_site_logo = $('#hidden_site_logo').val();

        // if (!hidden_site_logo) {
        //     $('#site_logo').prop('required',true);
        // } else {
        //     $('#site_logo').prop('required',false);
        // }

        var formData = new FormData($("#setting_form")[0]);
        if ($("#setting_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('setting_store') }}",
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
                        // window.location.href= "{{url('/settings')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>
@endsection
