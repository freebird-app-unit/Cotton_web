@extends('layouts.app')

@section('content')
<link href="{{asset('public/assets/plugins/datatables/jquery.dataTables.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{asset('public/assets/plugins/datatables/buttons.bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<style>
.delete-record{
    cursor: pointer !important;
    margin-left: 10px !important;
}
</style>
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Buyers List</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Buyers List</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box table-responsive">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 header-title"><b>Buyers List</b></h4>
                        </div>
                        <div class="col-sm-1">
                            <!-- <a href="{{url('state/add')}}" class="btn btn-default m-l-5">Add</a> -->
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>

                    <table id="datatable" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>Buyer Name</th>
                            <th>Mobile No</th>
                            <th>Approval</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="add_service" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Select Broker</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">

                    <div class="form-group">
                        <label class="col-sm-2 control-label">Broker</label>
                        <div class="col-sm-8">
                            <select class="form-control" name="broker_id" id="broker_id">
                                @if(!empty($brokers) && count($brokers) > 0)
                                    @foreach ($brokers as $val)
                                        <option value="{{$val->id}}">{{$val->name}}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default close_model" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary send_otp">Send OTP</button>
        </div>
      </div>
    </div>
</div>

<div class="modal fade" id="verify_otp" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
          <h4 class="modal-title" id="myModalLabel">Verify OTP</h4>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-lg-12">
                    <input type="hidden" name="hidden_broker_id" id="hidden_broker_id">
                    <div class="form-group">
                        <label class="col-sm-2 control-label">OTP</label>
                        <div class="col-sm-8">
                            <input type="text" class="form-control" name="otp" id="otp">
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-default close_model1" data-dismiss="modal">Close</button>
          <button type="button" class="btn btn-primary verify_otp">Verify OTP</button>
        </div>
      </div>
    </div>
</div>

@endsection
@section('script')
<script src="{{asset('public/assets/plugins/datatables/jquery.dataTables.min.js')}}"></script>
<script src="{{asset('public/assets/plugins/datatables/dataTables.bootstrap.js')}}"></script>
<script>
 $(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    const table = $('#datatable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ url('buyer/list') }}",
            type: 'POST',
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'name', name: 'name'},
            {data: 'mobile_number', name: 'mobile_number'},
            {data: 'approval', name: 'approval', orderable: false, searchable: false},
            {data: 'status', name: 'status', orderable: false, searchable: false},
            {data: 'action', name: 'action', orderable: false, searchable: false}
        ],
        language: {
        processing: '<div class="spinner-border text-primary m-1" role="status"><span class="sr-only">Loading...</span></div>'
        // processing: ''
        },
        order: [[0, 'DESC']],
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']]
    });
    $(document.body).on('click', '.delete-record', function(e) {
        var dataUrl = $(this).data('href');
        var dataid = $(this).data('id');
        var r = confirm('Are you sure want to delete ?');
        if(r==true)
        {
            $.ajax({
                type: "POST",
                url: dataUrl,
                data: {
                    "_token": "{{ csrf_token() }}",
                },
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);
                        $('#del_'+ dataid).remove();
                        table.draw();
                    }
                    if(data.response_status == "error")
                    {
                        toastr.error(data.message);
                    }

                }
            });
        }
    });

    $(document.body).on('click', '.send_otp', function(e) {
        var broker_id = $('#broker_id option:selected').val();
        $.ajax({
            type: "POST",
            url: "{{route('send_broker_otp')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                broker_id: broker_id,
            },
            success: function(data) {
                $('#hidden_broker_id').val(broker_id);
                toastr.success(data.message);
                $("#add_service .close_model").click();
                $("#verify_otp").modal('show');
            }
        });
    });
    var ids = 0;
    $(document.body).on('click', '.verify_otp', function(e) {
        var otp = $('#otp').val();
        var broker_id = $('#hidden_broker_id').val();
        var buyer_id = ids;
        $.ajax({
            type: "POST",
            url: "{{route('verify_buyer_broker_otp')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                broker_id: broker_id,
                buyer_id: buyer_id,
                otp: otp,
            },
            success: function(data) {
                if(data.response_status == "success"){
                    approve_buyer(ids);
                    toastr.success(data.message);
                    $("#verify_otp .close_model1").click();
                }else{
                    toastr.error(data.message);
                }
            }
        });
    });

    $(document.body).on('click', '.approved', function(e) {
        var buyer_id = $(this).data('id');
        ids = buyer_id;
        $.ajax({
            type: "POST",
            url: "{{route('check_buyer_code')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                buyer_id: buyer_id,
            },
            success: function(data) {
                if(data.response_status == "success"){
                    approve_buyer(buyer_id);
                }else{
                    $("#add_service").modal('show');
                }
            }
        });
    });

    function approve_buyer(buyer_id){
       $.ajax({
           type: "POST",
           url: "{{route('buyer_approval')}}",
           data: {
               "_token": "{{ csrf_token() }}",
               buyer_id: buyer_id,
               is_approved : 1,
           },
           success: function(data) {
               toastr.success(data.message);
               $('.is_approved_'+buyer_id).html('<span class="label label-success">Approved</span>');
           }
       });
    }

    //   $(document.body).on('click', '.approved', function(e) {
    //      var buyer_id = $(this).data('id');
    //      $.ajax({
    //          type: "POST",
    //          url: "{{route('buyer_approval')}}",
    //          data: {
    //              "_token": "{{ csrf_token() }}",
    //              buyer_id: buyer_id,
    //              is_approved : 1,
    //          },
    //          success: function(data) {
    //              toastr.success(data.message);
    //              $('.is_approved_'+buyer_id).html('<span class="label label-success">Approved</span>');
    //          }
    //      });
    //  });
    $(document.body).on('click', '.reject', function(e) {
        var buyer_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('buyer_approval')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                buyer_id: buyer_id,
                is_approved : 2,
            },
            success: function(data) {
                toastr.success(data.message);
                $('.is_approved_'+buyer_id).html('<span class="label label-danger">Rejected</span>');
            }
        });
    });
    $(document.body).on('click', '.status', function(e) {
        var buyer_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('buyer_status')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                buyer_id: buyer_id,
            },
            success: function(data) {
                toastr.success(data.message);
                if(data.data == 0 )
                {
                    $('#active_'+buyer_id).attr("id","inactive_"+buyer_id).removeClass("label-success").addClass("label-danger").html ("In Active");
                }
                else
                {
                    $('#inactive_'+buyer_id).attr("id","active_"+buyer_id).removeClass("label-danger").addClass("label-success").html( "Active");
                }
            }
        });
    });
 });
</script>
@endsection
