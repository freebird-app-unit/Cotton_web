@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Buyer Detail</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Buyer Detail</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-10">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Buyer Detail</b></h4>
                        </div>
                        <div class="col-sm-2">
                            @if($buyer->is_approve == 0)
                                <div class="is_approved"><span class="approved btn btn-success" data-id='{{$buyer->id}}' style="cursor: pointer;margin-right:5px;">Approve</span> <span class="reject btn btn-danger" data-id="{{$buyer->id}}" style="cursor: pointer;">Reject</span></div>
                            @elseif($buyer->is_approve == 1)
                                <span class="label label-success">Approved</span>
                            @elseif($buyer->is_approve == 2)
                                <span class="label label-danger">Rejected</span>
                            @endif
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-2"></div>
                        <div class="col-sm-3">
                            <p><b>Buyer Name : </b>{{!empty($buyer) ? $buyer->name : ''}}</p>
                            <p><b>Mobile Number : </b>{{!empty($buyer) ? $buyer->mobile_number : ''}}</p>
                        </div>
                        <div class="col-sm-3">
                            <p><b>Email : </b>{{!empty($buyer) ? $buyer->email : ''}}</p>
                            <p><b>Address : </b>{{!empty($buyer) ? $buyer->address : ''}}</p>

                        </div>
                        <div class="col-sm-3">
                            <p><b>Referal Code : </b>{{!empty($buyer) ? $buyer->referral_code : ''}}</p>
                            <p><b>Status : </b>
                                @if(!empty($buyer->is_active) && $buyer->is_active == 1)
                                    <span class="status label label-success" data-id='{{$buyer->id}}' id="active_{{$buyer->id}}" style="cursor: pointer;">Active</span>
                                @else
                                    <span class="status label label-danger" data-id="{{$buyer->id}}" id="inactive_{{$buyer->id}}" style="cursor: pointer;">InActive</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>User Detail</b></h4>
                        </div>
                        <div class="col-sm-1">
                        </div>
                    </div>
                    <div class="row">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Contact Person</th>
                                <th>Bussiness Type</th>
                                <th>Registration No</th>
                                <th>Registration Date</th>
                                <th>GST No</th>
                                <th>Company Name</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(!empty($user) && count($user) > 0)
                                    @foreach ($user as $data)
                                        <tr>
                                            <td>{{$data->name_of_contact_person}}</td>
                                            <td>{{$data->business_type}}</td>
                                            <td>{{$data->registration_no}}</td>
                                            <td>{{date('d-m-Y',strtotime($data->registration_date))}}</td>
                                            <td>{{$data->gst_no}}</td>
                                            <td>{{$data->company_name}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Bank Detail</b></h4>
                        </div>
                        <div class="col-sm-1">
                        </div>
                    </div>
                    <div class="row">
                        <table class="table">
                            <thead>
                            <tr>
                                <th>Bank Name</th>
                                <th>Account Holder</th>
                                <th>Branch Holder</th>
                                <th>IFSC Code</th>
                            </tr>
                            </thead>
                            <tbody>
                                @if(!empty($bank) && count($bank) > 0)
                                    @foreach ($bank as $data)
                                        <tr>
                                            <td>{{$data->bank_name}}</td>
                                            <td>{{$data->account_holder_name}}</td>
                                            <td>{{$data->branch_address}}</td>
                                            <td>{{$data->ifsc_code}}</td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
@section('script')
<script>
    $(document.body).on('click', '.approved', function(e) {
       var buyer_id = $(this).data('id');
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
               $('.is_approved').html('<span class="label label-success">Approved</span>');
           }
       });
   });
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
               $('.is_approved').html('<span class="label label-danger">Rejected</span>');
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

</script>
@endsection
