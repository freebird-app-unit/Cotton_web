<?php

namespace App\Http\Controllers;

use App\Models\BuyerType;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class BuyerTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $buyer = BuyerType::where('is_delete',1)->get();

            return Datatables::of($buyer) ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('status', function ($status) {
                if(!empty($status->is_active) && $status->is_active == 1){
                   return '<span class="status label label-success" data-id='.$status->id.' id="active_'.$status->id.'" style="cursor: pointer;">Active</span>';
                }else{
                    return '<span class="status label label-danger" data-id="'.$status->id.'" id="inactive_'.$status->id.'" style="cursor: pointer;">InActive</span>';
                }
            })
            ->addColumn('action', function ($value) {
                $edit = route('buyer_type_edit',$value->id);
                $delete = route('buyer_type_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['status','action'])
            ->make(true);
        }

    	return view('buyer_type.list');
    }

    public function add() {
        return view('buyer_type.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $valid = 'required';
        if(empty($request->buyer_id)){
            $valid = 'required|unique:tbl_buyer_type,name';
        }
    	$validator = Validator::make($request->all(), [
            'name' => $valid,
        ]);

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        $buyer = BuyerType::updateOrCreate(
            [
                'id' => $request->buyer_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $buyer->save();

        if (!empty($request->buyer_id)) {
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

        $buyer = [];
        if ((int)$id > 0) {
            $buyer  = BuyerType::find($id);
        }
        return view('buyer_type.form',compact(['buyer']));
    }

    public function destroy($id) {

        BuyerType::updateOrCreate(
            [
                'id' => $id,
            ],
            [
                'is_delete'    => 0,
            ]
        );
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "Record deleted successfully",
            "data"            => []
        ]);
    }
    public function buyer_status(Request $request){
        $buyer = BuyerType::select('is_active')->where('id',$request->buyer_id)->first();
        if ($buyer->is_active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }

        $save = BuyerType::updateOrCreate(
            [
                'id' => $request->buyer_id
            ],
            [
                'is_active'  => $active
            ]
        );
        $save->save();

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "Status Change Sucessfully",
            "data"            => $active
        ]);
    }
}
