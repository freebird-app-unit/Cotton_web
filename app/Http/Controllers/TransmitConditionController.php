<?php

namespace App\Http\Controllers;

use App\Models\TransmitCondition;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class TransmitConditionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $transmit = TransmitCondition::where('is_delete',1)->get();

            return Datatables::of($transmit)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('transmit_condition_edit',$value->id);
                $delete = route('transmit_condition_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }

    	return view('transmit_condition.list');
    }

    public function add() {
        return view('transmit_condition.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        $valid = 'required';
        if(empty($request->transmit_id)){
            $valid = 'required|unique:tbl_transmit_condition,name';
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

        $transmit_condition = TransmitCondition::updateOrCreate(
            [
                'id' => $request->transmit_id,
            ],
            [
                'name'    => $request->name,
                'is_dispatch'    => $request->is_dispatch,
            ]
        );
        $transmit_condition->save();

        if (!empty($request->transmit_id)) {
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

        $transmit = [];
        if ((int)$id > 0) {
            $transmit  = TransmitCondition::find($id);
        }
        return view('transmit_condition.form',compact(['transmit']));
    }

    public function destroy($id) {

        $transmit_condition = TransmitCondition::updateOrCreate(
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
}
