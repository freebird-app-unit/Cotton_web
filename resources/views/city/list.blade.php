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
                <h4 class="page-title">City List</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">City List</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box table-responsive">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 header-title"><b>City List</b></h4>
                        </div>
                        <div class="col-sm-1">
                            <a href="{{url('city/add')}}" class="btn btn-default m-l-5">Add</a>
                        </div>
                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>

                    <table id="datatable" class="table table-striped table-bordered">
                        <thead>
                        <tr>
                            <th>Sr No.</th>
                            <th>State Name</th>
                            <th>City Name</th>
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
            url: "{{ url('city/list') }}",
            type: 'POST',
        },
        columns: [
            {data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false},
            {data: 'state_name', name: 'state_name'},
            {data: 'name', name: 'name'},
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
                        table.draw();                    }
                    if(data.response_status == "error")
                    {
                        toastr.error(data.message);
                    }

                }
            });
        }
    });
});
</script>
@endsection
