<?php

namespace App\Http\Controllers;

use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\Buyers;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $buyer = Buyers::where('is_delete',1)->get();

            return Datatables::of($buyer)
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
                // if(!empty($approval->is_approve) && $approval->is_approve == 1){
                //    return '<span class="approval label label-success" data-id='.$approval->id.' id="approved_'.$approval->id.'" style="cursor: pointer;">Approved</span>';
                // }else{
                //     return '<span class="approval label label-danger" data-id="'.$approval->id.'" id="approve_'.$approval->id.'" style="cursor: pointer;">Approve</span>';
                // }
            })
            ->addColumn('status', function ($status) {
                if(!empty($status->is_active) && $status->is_active == 1){
                   return '<span class="status label label-success" data-id='.$status->id.' id="active_'.$status->id.'" style="cursor: pointer;">Active</span>';
                }else{
                    return '<span class="status label label-danger" data-id="'.$status->id.'" id="inactive_'.$status->id.'" style="cursor: pointer;">InActive</span>';
                }
            })
            ->addColumn('action', function ($value) {
                $edit = route('buyer_detail',$value->id);
                $delete = route('buyer_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-eye"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['approval','status','action'])
            ->make(true);
        }

    	return view('buyer.list');
    }

    public function destroy($id) {

        Buyers::updateOrCreate(
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

    public function buyer_approval(Request $request){

        $save = Buyers::updateOrCreate(
            [
                'id' => $request->buyer_id
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

    public function buyer_status(Request $request){
        $buyer = Buyers::select('is_active')->where('id',$request->buyer_id)->first();
        if ($buyer->is_active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }

        $save = Buyers::updateOrCreate(
            [
                'id' => $request->buyer_id
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
        $buyer = Buyers::where('id',$id)->where('is_delete',1)->first();
        $user = UserDetails::where('user_id',$id)->where('user_type','buyer')->get();
        $bank = BankDetails::where('user_id',$id)->where('user_type','buyer')->get();
        return view('buyer.detail',compact('buyer','user','bank'));
    }
}
