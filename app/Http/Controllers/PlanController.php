<?php

namespace App\Http\Controllers;

use App\Models\Plan;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class PlanController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $plan = Plan::get();

            return Datatables::of($plan)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('plan.edit',$value->id);
                $delete = route('plan.destroy',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }
        return view('plan.list');
    }

    public function create() {
        return view('plan.form');
    }

    public function store(Request $request)
    {
        if(empty($request->plan_id)){
            $validator = Validator::make($request->all(), [
                'name' =>  'required|unique:tbl_plans,name',
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

        $plan = Plan::updateOrCreate(
            [
                'id' => $request->plan_id,
            ],
            [
                'name' => $request->name,
                'validity' => $request->validity,
                'price' => $request->price,
            ]
        );
        $plan->save();

        if (!empty($request->plan_id)) {
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

        $plan = [];
        if ((int)$id > 0) {
            $plan  = Plan::find($id);
        }
        return view('plan.form',compact(['plan']));
    }

    public function show($id) {

        $plan = [];
        if ((int)$id > 0) {
            $plan  = Plan::find($id);
        }
        return view('plan.form',compact(['plan']));
    }

    public function destroy($id) {

        Plan::updateOrCreate(
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
