@extends('layouts.app')

@section('content')
<div class="content">
    <div class="container">
        <div class="row">
            <div class="col-sm-12">
                <h4 class="page-title">Product</h4>
                <ol class="breadcrumb">
                    <!-- <li><a href="#">Ubold</a></li> -->
                    <li><a href="#">Home</a></li>
                    <li class="active">Product</li>
                </ol>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-12">
                <div class="card-box">
                    <div class="btn-toolbar">
                        <div class="col-sm-11">
                            <h4 class="m-t-0 m-b-30 header-title"><b>Create Product</b></h4>
                        </div>

                    </div>
                    <p class="text-muted font-13 m-b-30">
                    </p>
                    <div class="row">
                        <div class="col-lg-12">

                            <form class="form-horizontal group-border-dashed" action="javascript:void(0);" id="product_form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{!empty($product) ? $product->id : ''}}">
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Product Name</label>
                                    <div class="col-sm-8">
                                        <input type="text" class="form-control" name="name" id="name" value="{{!empty($product) ? $product->name : ''}}" required placeholder="Enter Project Name" />
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="col-sm-3 control-label">Product Attribute</label>
                                    <div class="col-sm-8">
                                        <table class="table" id="product_attribute">
                                            <tr>
                                                <th>Label</th>
                                                <th>Value</th>
                                                <th></th>
                                            </tr>
                                            @if(!empty($attribute) && count($attribute) > 0)
                                            @php $i = 0; @endphp
                                                @foreach($attribute as $key => $val)
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" value="{{$val->label}}" name="label[{{$key}}]" required placeholder="Enter Label Name" />
                                                        </td>
                                                        @php
                                                            $data = App\Models\ProductAttributeValues::where('product_attribute_id',$val->id)->get();
                                                        @endphp
                                                        <td>
                                                            <div id="attribute_value" class="add_sub_row_{{$key}}">
                                                            @if(!empty($data) && count($data) > 0)
                                                                @foreach($data as $k => $value)
                                                                    <div class="row" id="remove_row_{{$i}}">
                                                                        <div class="col-sm-10">
                                                                            <input type="text" value="{{$value->value}}" class="form-control" name="value[{{$key}}][]" required placeholder="Enter value" />
                                                                        </div>
                                                                        <div class="col-sm-2">
                                                                            @if($k == 0)
                                                                                <i data-toggle="tooltip" data-placement="top" title="Add Value" class="fa fa-plus-circle add_value" data-sub_row="{{$key}}" style="font-size:20px;cursor:pointer;color:#5fbeaa;"></i>
                                                                            @else
                                                                                <i data-toggle="tooltip" data-placement="top" title="Remove Value" class="fa fa-minus-circle remove_value" data-row_id="{{$i}}" style="font-size:20px;cursor:pointer;color:red;"></i>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                    @php $i++; @endphp
                                                                @endforeach
                                                            @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            @if($key == 0)
                                                                <i data-toggle="tooltip" data-placement="top" title="Add Label" class="fa fa-plus-square add_attribute" style="font-size:20px;cursor:pointer;color:#5d9cec;"></i>
                                                            @else
                                                                <i data-toggle="tooltip" data-placement="top" title="Remove Label" class="fa fa-minus-square remove_attribute" style="font-size:20px;cursor:pointer;color:#f05050;"></i>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @else
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" name="label[0]" required placeholder="Enter Label Name" />
                                                    </td>
                                                    <td>
                                                        <div id="attribute_value" class="add_sub_row_0">
                                                            <div class="row">
                                                                <div class="col-sm-10">
                                                                    <input type="text" class="form-control" name="value[0][]" required placeholder="Enter value" />
                                                                </div>
                                                                <div class="col-sm-2">
                                                                    <i data-toggle="tooltip" data-placement="top" title="Add Value" class="fa fa-plus-circle add_value" data-sub_row="0" style="font-size:20px;cursor:pointer;color:#5fbeaa;"></i>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <i data-toggle="tooltip" data-placement="top" title="Add Label" class="fa fa-plus-square add_attribute" style="font-size:20px;cursor:pointer;color:#5d9cec;"></i>
                                                    </td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-offset-3 col-sm-9 m-t-15">
                                        <button type="button" class="btn btn-primary" id="btn_sub">
                                            Submit
                                        </button>
                                        <a href="{{url('/product')}}" class="btn btn-default m-l-5">
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

    $("#product_form").validate({
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

    var row = <?= !empty($attribute) && count($attribute) > 0 ? count($attribute) : '1'?>;
    var rows = <?= !empty($attribute) && count($attribute) > 0 ? count($attribute) : '1'?>;
    $(document.body).on('click', '.add_attribute', function(e) {
        $("[data-toggle=tooltip").tooltip();
        var html = `<tr>
                        <td>
                            <input type="text" class="form-control" name="label[`+rows+`]" required placeholder="Enter Label Name" />
                        </td>
                        <td>
                            <div id="attribute_value" class="add_sub_row_`+rows+`">
                                <div class="row" id="remove_row_`+rows+`">
                                    <div class="col-sm-10">
                                        <input type="text" class="form-control" name="value[`+rows+`][]" required placeholder="Enter value" />
                                    </div>
                                    <div class="col-sm-2">
                                        <i data-toggle="tooltip" data-placement="top" title="Add Value" class="fa fa-plus-circle add_value" data-sub_row="`+rows+`" style="font-size:20px;cursor:pointer;color:#5fbeaa;"></i>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <i data-toggle="tooltip" data-placement="top" title="Remove Label" class="fa fa-minus-square remove_attribute" style="font-size:20px;cursor:pointer;color:#f05050;"></i>
                        </td>
                    </tr>`;
        $('#product_attribute').append(html);
        row++;
        rows++;
    });

    $(document.body).on('click', '.remove_attribute', function(e) {
        $(this).parents('tr').remove();
    });

    $(document.body).on('click', '.add_value', function(e) {

        var sub_row = $(this).data('sub_row');
        html = `<div class="row" id="remove_row_`+rows+`">
                    <div class="col-sm-10">
                        <input type="text" class="form-control" name="value[`+sub_row+`][]" required placeholder="Enter value" />
                    </div>
                    <div class="col-sm-2">
                        <i data-toggle="tooltip" data-placement="top" title="Remove Value" class="fa fa-minus-circle remove_value" data-row_id="`+rows+`" style="font-size:20px;cursor:pointer;color:red;"></i>
                    </div>
                </div>`;
        $('.add_sub_row_'+sub_row).append(html);
        rows++;
        $("[data-toggle=tooltip").tooltip();
    });

    $(document.body).on('click', '.remove_value', function(e) {
        var row_id = $(this).data('row_id');
        console.log(row_id);
        $('#remove_row_'+row_id).remove();
    });

    $(document.body).on('click', '#btn_sub', function(e) {
        e.preventDefault();

        if ($("#product_form").valid()) {
            $.ajax({
                headers: {'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')},
                url: "{{ route('product_store') }}",
                type: "POST",
                dataType: 'JSON',
                data: $("#product_form").serialize(),
                success: function(data) {
                    if(data.response_status == "success")
                    {
                        toastr.success(data.message);
                        window.location.href= "{{url('/product')}}";
                    }else{
                        toastr.error(data.message);
                    }
                }
            });
        }
    });

</script>
@endsection
