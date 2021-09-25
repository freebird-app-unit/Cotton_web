<?php

namespace App\Http\Controllers;

use App\Models\BussinessType;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class BussinessTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $bussiness_type = BussinessType::where('is_delete',1)->get();

            return Datatables::of($bussiness_type)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('bussiness_type_edit',$value->id);
                $delete = route('bussiness_type_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }

    	return view('bussiness_type.list');
    }

    public function add() {
        return view('bussiness_type.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        $valid = 'required';
        if(empty($request->bussiness_type_id)){
            $valid = 'required|unique:tbl_bussiness_type,name';
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

        $bussiness_type = BussinessType::updateOrCreate(
            [
                'id' => $request->bussiness_type_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $bussiness_type->save();

        if (!empty($request->bussiness_type_id)) {
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

        $bussiness_type = [];
        if ((int)$id > 0) {
            $bussiness_type  = BussinessType::find($id);
        }
        return view('bussiness_type.form',compact(['bussiness_type']));
    }

    public function destroy($id) {

        $bussiness_type = BussinessType::updateOrCreate(
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
