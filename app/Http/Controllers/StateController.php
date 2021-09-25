<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class StateController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $state = State::with('country')->where('is_delete',1)->get();

            return Datatables::of($state)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('country_name', function ($value) {
                return  $value->country->name;
            })
            ->addColumn('action', function ($value) {
                $edit = route('state_edit',$value->id);
                $delete = route('state_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['country_name', 'action'])
            ->make(true);
        }
    	return view('state.list');
    }

    public function add() {
        $country = Country::where('is_delete',1)->get();
        return view('state.form',compact('country'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'country_id' =>  'required',
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
        if (empty($request->state_id)){
            $data = State::where('country_id',$request->country_id)->where('name',$request->name)->first();
            if(!empty($data)){
                return Response::json([
                    "code" => 200,
                    "response_status" => "error",
                    "message"         => 'The state name has already been taken',
                    "data"            => []
                ]);
            }
        }
        $state = State::updateOrCreate(
            [
                'id' => $request->state_id,
            ],
            [
                'country_id'    => $request->country_id,
                'name'    => $request->name,
            ]
        );
        $state->save();

        if (!empty($request->state_id)) {
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

        $state = [];
        $country = Country::where('is_delete',1)->get();
        if ((int)$id > 0) {
            $state  = State::find($id);
        }
        return view('state.form',compact(['state','country']));
    }

    public function destroy($id) {

        $state = State::updateOrCreate(
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
