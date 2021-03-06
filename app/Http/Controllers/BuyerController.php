<?php

namespace App\Http\Controllers;

use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\Buyers;
use App\Models\Brokers;
use App\Models\AddBrokers;
use Illuminate\Http\Request;
use Response;
use Validator;
use Yajra\DataTables\Facades\DataTables;

class BuyerController extends Controller
{
    public function index(Request $request)
    {
        $brokers = Brokers::where('is_delete',1)->get();
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

    	return view('buyer.list',compact('brokers'));
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

    public function check_buyer_code(Request $request){
        $buyer = Buyers::select('referral_code')->where('id',$request->buyer_id)->first();

        if(!empty($buyer->referral_code)){
            $status = "success";
        }else{
            $status = "error";
        }

        return Response::json([
            "code" => 200,
            "response_status" => $status,
            "message"         => "",
            "data"            => "",
        ]);
    }

    public function verify_buyer_broker_otp(Request $request){

    	$otp_verify = Brokers::where('id',$request->broker_id)->first();

        $check_broker = AddBrokers::where(['buyer_id' => $request->buyer_id, 'broker_id' => $otp_verify->id, 'user_type' => 'buyer'])->first();
        if (!empty($check_broker)) {
            $response['status'] = 404;
            $response['message'] = 'Broker already added in your list!';
            return response($response, 200);
        }

        $otp = $request->otp;
        if($otp == $otp_verify->otp){
            $current = date("Y-m-d H:i:s");
            $otp_time = $otp_verify->otp_time;
            $diff = strtotime($current) - strtotime($otp_time);
            $days    = floor($diff / 86400);
            $hours   = floor(($diff - ($days * 86400)) / 3600);
            $minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
            if (($diff > 0) && ($minutes <= 180)) {

                $check_broker = AddBrokers::where(['buyer_id' => $request->buyer_id, 'user_type' => 'buyer'])->first();

                $type = 'default';
                if (!empty($check_broker)) {
                    $type = 'not_default';
                }

                $broker =  new AddBrokers();
                $broker->buyer_id = $request->buyer_id;
                $broker->user_type = 'buyer';
                $broker->broker_id = $otp_verify->id;
                $broker->broker_type = $type;
                $broker->save();

                $save = Buyers::updateOrCreate(
                    [
                        'id' => $request->buyer_id
                    ],
                    [
                        'referral_code'  => $otp_verify->code,
                    ]
                );
                $save->save();

                $status = "success";
                $message = 'OTP verified successfully';
                // $otp_verify->is_otp_verify = 1;
                // $otp_verify->save();
            }else{
                    $status = "error";
                    $message = 'OTP expired';
                }
        }else{
                $status = "error";
                $message = 'OTP is not valid';
        }

        return Response::json([
            "code" => 200,
            "response_status" => $status,
            "message"         => $message,
            "data"            => "",
        ]);
    }
}
