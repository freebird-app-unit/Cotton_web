<?php

namespace App\Http\Controllers;

use App\Models\Station;
use App\Models\City;
use App\Models\Country;
use App\Models\State;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class StationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $station = Station::with('city')->where('is_delete',1)->get();

            return Datatables::of($station)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('city_name', function ($value) {
                return  $value->city->name;
            })
            ->addColumn('action', function ($value) {
                $edit = route('station_edit',$value->id);
                $delete = route('station_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['country_name', 'action'])
            ->make(true);
        }
    	return view('station.list');
    }

    public function add() {
        $country = Country::where('is_delete',1)->get();
        return view('station.form',compact('country'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'country_id' =>  'required',
            'state_id' =>  'required',
            'city_id' =>  'required',
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

        if (!empty($request->station_id)){
            $data = Station::where('city_id',$request->city_id)->where('name',$request->name)->first();
            if(!empty($data)){
                return Response::json([
                    "code" => 200,
                    "response_status" => "error",
                    "message"         => 'The station name has already been taken',
                    "data"            => []
                ]);
            }
        }

        $station = Station::updateOrCreate(
            [
                'id' => $request->station_id,
            ],
            [
                'country_id'    => $request->country_id,
                'state_id'    => $request->state_id,
                'city_id'    => $request->city_id,
                'name'    => $request->name,
            ]
        );
        $station->save();

        if (!empty($request->station_id)) {
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

        $station = [];
        $country = [];
        $state = [];
        $city = [];
        if ((int)$id > 0) {
            $station  = Station::find($id);
            $country = Country::where('is_delete',1)->get();
            $state = State::where('is_delete',1)->where('country_id',$station->country_id)->get();
            $city = City::where('is_delete',1)->where('state_id',$station->state_id)->get();
        }
        return view('station.form',compact(['station','country','state','city']));
    }

    public function destroy($id) {

        $station = Station::updateOrCreate(
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

    public function state_list(Request $request) {
        $country_id = $request->country_id;

        $data= [];
        if ((int)$country_id > 0) {
            $data  = State::where('area_id', $country_id)->where('is_delete',1)->get();
        }
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "",
            "data"            => $data
        ]);
    }
}
