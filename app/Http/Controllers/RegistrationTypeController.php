<?php

namespace App\Http\Controllers;

use App\Models\RegistrationType;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class RegistrationTypeController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $registration = RegistrationType::where('is_delete',1)->get();

            return Datatables::of($registration)
            ->setRowId(function ($value) {
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
                $edit = route('registration_type_edit',$value->id);
                $delete = route('registration_type_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['status','action'])
            ->make(true);
        }

    	return view('registration_type.list');
    }

    public function add() {
        return view('registration_type.form');
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

        $registration = RegistrationType::updateOrCreate(
            [
                'id' => $request->registration_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $registration->save();

        if (!empty($request->registration_id)) {
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

        $registration = [];
        if ((int)$id > 0) {
            $registration  = RegistrationType::find($id);
        }
        return view('registration_type.form',compact(['registration']));
    }

    public function destroy($id) {

        RegistrationType::updateOrCreate(
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
    public function registration_status(Request $request){
        $registration = RegistrationType::select('is_active')->where('id',$request->registration_id)->first();
        if ($registration->is_active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }

        $save = RegistrationType::updateOrCreate(
            [
                'id' => $request->registration_id
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
