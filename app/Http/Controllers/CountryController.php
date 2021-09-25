<?php

namespace App\Http\Controllers;

use App\Models\Country;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class CountryController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $country = Country::where('is_delete',1)->get();

            return Datatables::of($country)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('country_edit',$value->id);
                $delete = route('country_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('country.list');
    }

    public function add() {
        return view('country.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());

        if(empty($request->country_id)){
            $validator = Validator::make($request->all(), [
                'name' =>  'required|unique:tbl_country,name',
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

        $country = Country::updateOrCreate(
            [
                'id' => $request->country_id,
            ],
            [
                'name'    => $request->name,
            ]
        );
        $country->save();

        if (!empty($request->country_id)) {
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

        $country = [];
        if ((int)$id > 0) {
            $country  = Country::find($id);
        }
        return view('country.form',compact(['country']));
    }

    public function destroy($id) {

        $country = Country::updateOrCreate(
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
