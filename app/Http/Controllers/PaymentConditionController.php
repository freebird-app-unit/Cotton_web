<?php

namespace App\Http\Controllers;

use App\Models\PaymentCondition;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class PaymentConditionController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $payment = PaymentCondition::where('is_delete',1)->get();

            return Datatables::of($payment)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($value) {
                $edit = route('payment_condition_edit',$value->id);
                $delete = route('payment_condition_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-edit"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['action'])
            ->make(true);
        }

    	return view('payment_condition.list');
    }

    public function add() {
        return view('payment_condition.form');
    }

    public function store(Request $request)
    {
        // dd($request->all());
        if(empty($request->payment_id)){

            $validator = Validator::make($request->all(), [
                'name' =>  'required|unique:tbl_payment_condition,name',
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

        $payment_condition = PaymentCondition::updateOrCreate(
            [
                'id' => $request->payment_id,
            ],
            [
                'name'    => $request->name,
                'description'    => $request->description,
            ]
        );
        $payment_condition->save();

        if (!empty($request->payment_id)) {
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

        $payment = [];
        if ((int)$id > 0) {
            $payment  = PaymentCondition::find($id);
        }
        return view('payment_condition.form',compact(['payment']));
    }

    public function destroy($id) {

        $payment_condition = PaymentCondition::updateOrCreate(
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
