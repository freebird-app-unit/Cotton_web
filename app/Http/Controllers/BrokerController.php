<?php

namespace App\Http\Controllers;

use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\Brokers;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class BrokerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $broker = Brokers::where('is_delete',1)->get();

            return Datatables::of($broker)
            ->setRowId(function ($value) {
                return 'del_'.$value->id;
            })
            ->addIndexColumn()
            ->addColumn('approval', function ($approval) {
                if($approval->is_approve == 0){
                    return '<div class="is_approved_'.$approval->id.'"><span class="approved label label-success" data-id='.$approval->id.' style="cursor: pointer;">Approve</span> <span class="reject label label-danger" data-id="'.$approval->id.'" style="cursor: pointer;">Reject</span></div>';
                }else if($approval->is_approve == 1){
                    return '<span class="label label-success">Approved</span>';
                }else if($approval->is_approve == 2){
                    return '<span class="label label-danger">Rejected</span>';
                }

            })
            ->addColumn('status', function ($status) {
                if(!empty($status->is_active) && $status->is_active == 1){
                   return '<span class="status label label-success" data-id='.$status->id.' id="active_'.$status->id.'" style="cursor: pointer;">Active</span>';
                }else{
                    return '<span class="status label label-danger" data-id="'.$status->id.'" id="inactive_'.$status->id.'" style="cursor: pointer;">InActive</span>';
                }
            })
            ->addColumn('action', function ($value) {
                $edit = route('broker_detail',$value->id);
                $delete = route('broker_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-eye"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['approval','status','action'])
            ->make(true);
        }

    	return view('broker.list');
    }

    public function destroy($id) {

        Brokers::updateOrCreate(
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

    public function broker_approval(Request $request){

        $save = Brokers::updateOrCreate(
            [
                'id' => $request->broker_id
            ],
            [
                'is_approve'  => $request->is_approved
            ]
        );
        $save->save();

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => "Status Change Sucessfully",
            "data"            => []
        ]);
    }

    public function broker_status(Request $request){
        $broker = Brokers::select('is_active')->where('id',$request->broker_id)->first();
        if ($broker->is_active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }

        $save = Brokers::updateOrCreate(
            [
                'id' => $request->broker_id
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

    public function detail($id){
        $broker = Brokers::where('id',$id)->where('is_delete',1)->first();
        $user = UserDetails::where('user_id',$id)->where('user_type','broker')->get();
        $bank = BankDetails::where('user_id',$id)->where('user_type','broker')->get();
        return view('broker.detail',compact('broker','user','bank'));
    }
}
