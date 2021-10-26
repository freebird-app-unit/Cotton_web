<?php

namespace App\Http\Controllers\Api\broker;

use Illuminate\Http\Request;
use App\Models\Brokers;
use App\Models\UserDetails;
use App\Models\AddBrokers;
use App\Models\BrokerRequest;
use Validator;
use App\Helper\NotificationHelper;

class SearchController extends Controller
{
    public function search_broker(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';

		$params = [
			'country_id' => $country_id,
			'state_id' => $state_id,
			'city_id' => $city_id,
			'station_id' => $station_id,
		];

		$validator = Validator::make($params, [
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'station_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

		$search_broker = [];

		$search_data = UserDetails::with('broker')->where(['country_id'=>$country_id,'state_id'=>$state_id,'city_id'=>$city_id,'station_id'=>$station_id,'user_type'=>'broker'])->get();
		if(count($search_data)>0){
			foreach ($search_data as $value) {
                $search_broker[] = [
                    'id' => $value->broker->id,
                    'name' => $value->broker->name,
                    'mobile_number' => $value->broker->mobile_number,
                    'mobile_number_2' => $value->broker->mobile_number_2,
                ];
			}
			$response['status'] = 200;
			$response['message'] = 'Search Broker';
			$response['data'] = $search_broker;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function add_broker(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';

		$params = [
			'buyer_id' => $buyer_id,
			'broker_id' => $broker_id,
		];

		$validator = Validator::make($params, [
            'buyer_id' => 'required',
            'broker_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }
        $check_broker = AddBrokers::where(['buyer_id' => $broker_id, 'broker_id' => $broker_id])->first();
        if (!empty($check_broker)) {
            $response['status'] = 404;
            $response['message'] = 'Broker already added';
            return response($response, 200);
        }


		$add_broker = new AddBrokers();
		$add_broker->buyer_id = $buyer_id;
		$add_broker->user_type = 'buyer';
		$add_broker->broker_id = $broker_id;
		$add_broker->broker_type = 'not_default';
		$verification_code = mt_rand(100000,999999);
		$add_broker->otp = $verification_code;
		$add_broker->otp_time = date('Y-m-d H:i:s');
		$add_broker->created_at =date('Y-m-d H:i:s');
		$add_broker->updated_at =date('Y-m-d H:i:s');

		if($add_broker->save()){

			$broker_mob = Brokers::where('id',$broker_id)->first();
			if(!empty($broker_mob)){
				//send otp
				$message = "OTP is ".$add_broker->otp."-E - Cotton";
                NotificationHelper::send_otp($broker_mob->mobile_number,$message);

				$response['status'] = 200;
				$response['message'] = 'Broker Added successfully.';
			}
		}
		return response($response, 200);
    }

    public function add_broker_verify(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';
		$otp = isset($content->otp) ? $content->otp : '';

		$params = [
			'buyer_id' => $buyer_id,
			'broker_id' =>$broker_id,
			'otp' => $otp
		];

		$validator = Validator::make($params, [
            'buyer_id' => 'required',
            'broker_id' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$otp_verify = AddBrokers::where(['buyer_id'=>$buyer_id,'broker_id'=>$broker_id])->first();
		if(!empty($otp_verify)){
			if($otp == $otp_verify->otp){
				$current = date("Y-m-d H:i:s");
				$otp_time = $otp_verify->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 180)) {
					$response['status'] = 200;
					$response['message'] = 'Your mobile number has been verified successfully';
					$otp_verify->is_verify = 1;
					$otp_verify->save();
				}else{
						$response['status'] = 404;
						$response['message'] = 'OTP expired';
					}
			}else{
					$response['status'] = 404;
					$response['message'] = 'OTP is not valid';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Not found';
		}
		return response($response, 200);
    }

    public function add_broker_list(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';

		$params = [
			'buyer_id' => $buyer_id,
		];

		$validator = Validator::make($params, [
            'buyer_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

	    $broker_list = [];
	    $brokers = AddBrokers::with('broker.userDetails.city')->where('buyer_id',$buyer_id)->get();
	    if(count($brokers)>0){
	    	foreach ($brokers as $value) {
	    		$broker_list[] = [
	    			'id' => $value->id,
	    			'broker_name' => $value->broker->name,
	    			'is_verify' => $value->is_verify,
	    			'broker_type'=>$value->broker_type,
	    			'city'=>$value->broker->userDetails->city->name,
	    		];
	    	}
	    	$response['status'] = 200;
			$response['message'] = 'Brokers List';
			$response['data'] = $broker_list;
	    }else{
	    	$response['status'] = 404;
	    }
	    return response($response, 200);
    }
    public function delete_broker(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';

		$params = [
			'buyer_id' => $buyer_id,
			'broker_id' =>$broker_id
		];

		$validator = Validator::make($params, [
            'buyer_id' => 'required',
            'broker_id'=>'required'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

	    $delete_broker = AddBrokers::where(['buyer_id'=>$buyer_id,'broker_id'=>$broker_id])->first();
	    if(!empty($delete_broker)){
	    	$delete_broker->delete();
	    	$response['status'] = 200;
			$response['message'] = 'Broker deleted successfully.';
	    }else{
            $response['message'] = 'No Broker Found';
	    	$response['status'] = 404;
	    }
	    return response($response, 200);
    }

    public function broker_request_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$broker_id = isset($content->broker_id) ? $content->broker_id : '';

		$params = [
			'broker_id' => $broker_id,
		];

		$validator = Validator::make($params, [
            'broker_id' => 'required|exists:tbl_brokers,id'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $buyer_list = [];
    	$broker_request = BrokerRequest::with('buyer')->where(['broker_id'=> $broker_id])->get();

	    if(!empty($broker_request)){
            foreach($broker_request as $value) {
                $buyer_list[] = [
                    'id' => $value->id,
                    'buyer_id' => $value->buyer_id,
                    'name' => $value->buyer->name,
                    'time_ago' => $value->created_at->diffForHumans()
                ];
            }
	    }
        $response['data'] = $buyer_list;

		return response($response, 200);
    }

    public function accept_broker_request(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$request_id = isset($content->request_id) ? $content->request_id : '';
		$type = isset($content->type) ? $content->type : '';

		$params = [
			'request_id' => $request_id,
		];

		$validator = Validator::make($params, [
            'request_id' => 'required|exists:tbl_broker_requests,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

        if ($type != 'accept' && $type != 'reject') {
            $response['status'] = 404;
            $response['message'] = 'Please select valid type';
            return response($response, 200);
        }


        if ($type == 'accept') {
            $broker_request = BrokerRequest::where(['id'=> $request_id])->first();
            $brokers = AddBrokers::where(['buyer_id'=> $broker_request->buyer_id,'user_type' => 'buyer'])->first();

            $broker_type = 'not_default';
            if(empty($brokers)){
                $broker_type = 'default';

            }
            $add_broker = new AddBrokers();
            $add_broker->buyer_id = $broker_request->buyer_id;
            $add_broker->user_type = 'buyer';
            $add_broker->broker_id = $broker_request->broker_id;
            $add_broker->broker_type = $broker_type;
            $add_broker->created_at =date('Y-m-d H:i:s');
            $add_broker->updated_at =date('Y-m-d H:i:s');
            $add_broker->save();

            $response['message'] = 'Request accepted';
        } else {
            $response['message'] = 'Request rejected';
        }

        BrokerRequest::where(['id'=> $request_id])->delete();


		return response($response, 200);
    }
}
