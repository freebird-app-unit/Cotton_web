<?php

namespace App\Http\Controllers;

use App\Models\BankDetails;
use App\Models\Sellers;
use App\Models\UserDetails;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class SellerController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $seller = Sellers::where('is_delete',1)->get();

            return Datatables::of($seller)
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
                //     return '<span class="approval label label-success" data-id='.$approval->id.' id="approved_'.$approval->id.'" style="cursor: pointer;">Approved</span>';
                //  }else{
                //      return '<span class="approval label label-danger" data-id="'.$approval->id.'" id="approve_'.$approval->id.'" style="cursor: pointer;">Approve</span>';
                //  }
            })
            ->addColumn('status', function ($status) {
                if(!empty($status->is_active) && $status->is_active == 1){
                   return '<span class="status label label-success" data-id='.$status->id.' id="active_'.$status->id.'" style="cursor: pointer;">Active</span>';
                }else{
                    return '<span class="status label label-danger" data-id="'.$status->id.'" id="inactive_'.$status->id.'" style="cursor: pointer;">InActive</span>';
                }
            })
            ->addColumn('action', function ($value) {
                $edit = route('seller_detail',$value->id);
                $delete = route('seller_delete',$value->id);
                return  '<a href="'.$edit.'"><i class="fa fa-eye"></i></a>
                <i data-href="'.$delete.'" data-original-title="Delete" data-id="'.$value->id.'" class="fa fa-trash delete-record"></i>';
            })
            ->rawColumns(['approval','status','action'])
            ->make(true);
        }

    	return view('seller.list');
    }

    public function destroy($id) {

        Sellers::updateOrCreate(
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

    public function seller_approval(Request $request){
        $save = Sellers::updateOrCreate(
            [
                'id' => $request->seller_id
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
    // public function seller_approval(Request $request){
    //     $seller = Sellers::select('is_approve')->where('id',$request->seller_id)->first();
    //     if ($seller->is_approve == 1) {
    //         $approval = 0;
    //     } else {
    //         $approval = 1;
    //     }

    //     $save = Sellers::updateOrCreate(
    //         [
    //             'id' => $request->seller_id
    //         ],
    //         [
    //             'is_approve'  => $approval
    //         ]
    //     );
    //     $save->save();

    //     return Response::json([
    //         "code" => 200,
    //         "response_status" => "success",
    //         "message"         => "Status Change Sucessfully",
    //         "data"            => $approval
    //     ]);
    // }

    public function seller_status(Request $request){
        $seller = Sellers::select('is_active')->where('id',$request->seller_id)->first();
        if ($seller->is_active == 1) {
            $active = 0;
        } else {
            $active = 1;
        }

        $save = Sellers::updateOrCreate(
            [
                'id' => $request->seller_id
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
        $seller = Sellers::where('id',$id)->where('is_delete',1)->first();
        $user = UserDetails::where('user_id',$id)->where('user_type','seller')->get();
        $bank = BankDetails::where('user_id',$id)->where('user_type','seller')->get();
        return view('seller.detail',compact('seller','user','bank'));
    }
}
