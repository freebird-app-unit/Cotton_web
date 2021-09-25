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
                <h4 class="page-title">Seller List</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Seller List</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box table-responsive">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 header-title"><b>Seller List</b></h4>
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
                            <th>Seller Name</th>
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
            url: "{{ url('seller/list') }}",
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
    $(document.body).on('click', '.approved', function(e) {
        var seller_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('seller_approval')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                seller_id: seller_id,
                is_approved : 1,
            },
            success: function(data) {
                toastr.success(data.message);
                $('.is_approved_'+seller_id).html('<span class="label label-success">Approved</span>');
            }
        });
    });
    $(document.body).on('click', '.reject', function(e) {
        var seller_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('seller_approval')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                seller_id: seller_id,
                is_approved : 2,
            },
            success: function(data) {
                toastr.success(data.message);
                $('.is_approved_'+seller_id).html('<span class="label label-danger">Rejected</span>');
            }
        });
    });
    // $(document.body).on('click', '.approval', function(e) {
    //     var seller_id = $(this).data('id');
    //     $.ajax({
    //         type: "POST",
    //         url: "{{route('seller_approval')}}",
    //         data: {
    //             "_token": "{{ csrf_token() }}",
    //             seller_id: seller_id,
    //         },
    //         success: function(data) {
    //             toastr.success(data.message);
    //             if(data.data == 0 )
    //             {
    //                 $('#approved_'+seller_id).attr("id","approve_"+seller_id).removeClass("label-success").addClass("label-danger").html ("Approve");
    //             }
    //             else
    //             {
    //                 $('#approve_'+seller_id).attr("id","approved_"+seller_id).removeClass("label-danger").addClass("label-success").html( "Approved");
    //             }
    //         }
    //     });
    // });
    $(document.body).on('click', '.status', function(e) {
        var seller_id = $(this).data('id');
        $.ajax({
            type: "POST",
            url: "{{route('seller_status')}}",
            data: {
                "_token": "{{ csrf_token() }}",
                seller_id: seller_id,
            },
            success: function(data) {
                toastr.success(data.message);
                if(data.data == 0 )
                {
                    $('#active_'+seller_id).attr("id","inactive_"+seller_id).removeClass("label-success").addClass("label-danger").html ("In Active");
                }
                else
                {
                    $('#inactive_'+seller_id).attr("id","active_"+seller_id).removeClass("label-danger").addClass("label-success").html( "Active");
                }
            }
        });
    });
 });
</script>
@endsection
