<?php

namespace App\Http\Controllers;

use App\Models\Lab;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class LabController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $lab = Lab::where('is_delete',1)->get();

            return Datatables::of($lab)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('lab_edit',$value->id);
                $delete = route('lab_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['country_name', 'action'])
            ->make(true);
        }
    	return view('lab.list');
    }

    public function add() {
        return view('lab.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'name' =>  'required',
        ]);

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        $lab = Lab::updateOrCreate(
            [
                'id' => $request->lab_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $lab->save();

        if (!empty($request->lab_id)) {
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

        $lab = [];
        if ((int)$id > 0) {
            $lab  = Lab::find($id);
        }
        return view('lab.form',compact(['lab']));
    }

    public function destroy($id) {

        $lab = Lab::updateOrCreate(
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
