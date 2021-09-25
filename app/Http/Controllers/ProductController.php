<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValues;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $product = Product::where('is_delete',0)->get();

            return Datatables::of($product)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('product_edit',$value->id);
                $delete = route('product_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
    	return view('product.list');
    }

    public function add() {
        return view('product.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if(empty($request->product_id)){
            $validator = Validator::make($request->all(), [
                'name' =>  'required|unique:tbl_product,name',
            ]);


            if ($validator->fails()) {
                return Response::json([
                    "code" => 200,
                    "response_status" => "error",
                    "message"         => $validator->errors()->first(),
                    "data"            => []
                ]);
            }
        }

        $product = Product::updateOrCreate(
            [
                'id' => $request->product_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $product->save();

        if (!empty($request->product_id)) {
            $attr = ProductAttribute::select('id')->where('product_id',$product->id)->get();
            $attribute_id = [];
            foreach($attr as $v){
                array_push($attribute_id,$v->id);
            }
            ProductAttribute::where('product_id',$product->id)->delete();
            ProductAttributeValues::whereIn('product_attribute_id',$attribute_id)->delete();
        }
        foreach($request->label as $key => $val){
            $values = $request->value[$key];
            $attribute = ProductAttribute::create(
                [
                    'product_id'    => $product->id,
                    'label'    => $val,
                ]
            );
            $attribute->save();
            foreach($values as $data){
                ProductAttributeValues::create(
                [
                    'product_attribute_id'  => $attribute->id,
                    'value'    => $data,
                ]);
            }
        }

        if (!empty($request->product_id)) {
            $message = "Record Update successfully";
        } else {
            $message = "Record Save successfully";
        }

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => $message,
            "data"            => []
        ]);
    }

    public function edit($id) {

        $product = [];
        $attribute = [];
        if ((int)$id > 0) {
            $product  = Product::find($id);
            $attribute  = ProductAttribute::where('product_id',$id)->get();
        }
        return view('product.form',compact(['product','attribute']));
    }

    public function destroy($id) {

        $product = Product::updateOrCreate(
            [
                'id' => $id,
            ],
            [
                'is_delete'    => 1,
            ]
        );
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "Record deleted successfully",
            "data"            => []
        ]);
    }
}
