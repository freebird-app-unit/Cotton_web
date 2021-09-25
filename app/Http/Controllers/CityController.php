<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\State;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class CityController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $city = City::with('state')->where('is_delete',1)->get();

            return Datatables::of($city)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('state_name', function ($value) {
                return  $value->state->name;
            })
            ->addColumn('action', function ($value) {
                $edit = route('city_edit',$value->id);
                $delete = route('city_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['country_name', 'action'])
            ->make(true);
        }
    	return view('city.list');
    }

    public function add() {
        $state = State::where('is_delete',1)->get();
        return view('city.form',compact('state'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'state_id' =>  'required',
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

        if (!empty($request->city_id)){
            $data = City::where('state_id',$request->state_id)->where('name',$request->name)->first();
            if(!empty($data)){
                return Response::json([
                    "code" => 200,
                    "response_status" => "error",
                    "message"         => 'The city name has already been taken',
                    "data"            => []
                ]);
            }
        }

        $city = City::updateOrCreate(
            [
                'id' => $request->city_id,
            ],
            [
                'state_id'    => $request->state_id,
                'name'    => $request->name,
            ]
        );
        $city->save();

        if (!empty($request->city_id)) {
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

        $city = [];
        $state = State::where('is_delete',1)->get();
        if ((int)$id > 0) {
            $city  = City::find($id);
        }
        return view('city.form',compact(['city','state']));
    }

    public function destroy($id) {

        $city = City::updateOrCreate(
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
