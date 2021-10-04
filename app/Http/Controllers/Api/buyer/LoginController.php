<?php

namespace App\Http\Controllers\Api\buyer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Buyers;
use App\Models\Brokers;
use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\DeviceDetails;
use App\Models\AddBrokers;
use App\Models\BrokerRequest;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Image;
use App\Helper\NotificationHelper;

class LoginController extends Controller
{
     public function registration_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_type = isset($content->user_type) ? $content->user_type : '';
		$seller_buyer_type = isset($content->seller_buyer_type) ? $content->seller_buyer_type : '';
		$name = isset($content->name) ? $content->name : '';
		$password = isset($content->password) ? $content->password : '';
		$address = isset($content->address) ? $content->address : '';
		$name_of_contact_person = isset($content->name_of_contact_person) ? $content->name_of_contact_person : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$email = isset($content->email) ? $content->email : '';
		$business_type = isset($content->business_type) ? $content->business_type : '';
		$registration_no = isset($content->registration_no) ? $content->registration_no : '';
		$registration_date = isset($content->registration_date) ? $content->registration_date : '';
		$registration_as_msme = isset($content->registration_as_msme) ? $content->registration_as_msme : '';
		$turnover_year_one = isset($content->turnover_year_one) ? $content->turnover_year_one : '';
		$turnover_date_one = isset($content->turnover_date_one) ? $content->turnover_date_one : '';
		$turnover_year_two = isset($content->turnover_year_two) ? $content->turnover_year_two : '';
		$turnover_date_two = isset($content->turnover_date_two) ? $content->turnover_date_two : '';
		$turnover_year_three = isset($content->turnover_year_three) ? $content->turnover_year_three : '';
		$turnover_date_three = isset($content->turnover_date_three) ? $content->turnover_date_three : '';
		$oper_in_cotton_trade = isset($content->oper_in_cotton_trade) ? $content->oper_in_cotton_trade : '';
		$gst_no = isset($content->gst_no) ? $content->gst_no : '';
		$pan_no_of_buyer = isset($content->pan_no_of_buyer) ? $content->pan_no_of_buyer : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';
		$bank_name = isset($content->bank_name) ? $content->bank_name : '';
		$account_holder_name = isset($content->account_holder_name) ? $content->account_holder_name : '';
		$branch_address = isset($content->branch_address) ? $content->branch_address : '';
		$ifsc_code = isset($content->ifsc_code) ? $content->ifsc_code : '';
		$referral_code = isset($content->referral_code) ? $content->referral_code : '';
		$establish_year = isset($content->establish_year) ? $content->establish_year : '';
		$company_name = isset($content->company_name) ? $content->company_name : '';
		$fcm_token = isset($content->fcm_token) ? $content->fcm_token : '';
		$device_type = isset($content->device_type) ? $content->device_type : '';

		$params = [
			'mobile_number' => $mobile_number,
			'email' => $email
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required|digits:10|unique:tbl_buyers,mobile_number',
            'email' => 'required|email|unique:tbl_buyers,email|max:255',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }
	      if(!empty($referral_code)){
	    	$broker = Brokers::where('code',$referral_code)->first();
	    	if(empty($broker)){
	    		$response['status'] = 404;
				$response['message'] = 'Referral Code is not avaialble';
		    }else{
		    	$image_name = '';
				if ($request->hasFile('image')) {
					$image = $request->file('image');
					$image_name = time() . '.' . $image->getClientOriginalExtension();
					$img = Image::make($image->getRealPath());
					$img->stream(); // <-- Key point
					Storage::disk('public')->put('buyer/profile/' . $image_name, $img, 'public');
				}
		    	$buyer = new Buyers();
				$buyer->name = $name;
				$buyer->password = Hash::make($password);
				$buyer->address = $address;
				$buyer->mobile_number= $mobile_number;
				$buyer->email=$email;
				$verification_code = mt_rand(100000,999999);
				$buyer->otp = $verification_code;
				$buyer->otp_time = date('Y-m-d H:i:s');
				$buyer->image = $image_name;
				$buyer->referral_code=$referral_code;

				if($buyer->save()){

					$add_broker = new AddBrokers();
					$add_broker->buyer_id = $buyer->id;
					$add_broker->user_type = 'buyer';
					$add_broker->broker_id = $broker->id;
					$add_broker->broker_type = 'default';
					$add_broker->created_at =date('Y-m-d H:i:s');
					$add_broker->updated_at =date('Y-m-d H:i:s');
					$add_broker->save();

					$id = $buyer->id;
					$user_details = new UserDetails();
					$user_details->user_id = $id;
					$user_details->user_type = $user_type;
					$user_details->seller_buyer_type = $seller_buyer_type;
					$user_details->name_of_contact_person=$name_of_contact_person;
					$user_details->business_type=$business_type;
					$user_details->registration_no=$registration_no;
					$user_details->registration_date=$registration_date;
					$user_details->registration_as_msme=$registration_as_msme;
					$user_details->turnover_year_one=$turnover_year_one;
					$user_details->turnover_date_one=$turnover_date_one;
					$user_details->turnover_year_two=$turnover_year_two;
					$user_details->turnover_date_two=$turnover_date_two;
					$user_details->turnover_year_three=$turnover_year_three;
					$user_details->turnover_date_three=$turnover_date_three;
					$user_details->oper_in_cotton_trade=$oper_in_cotton_trade;
					$user_details->gst_no=$gst_no;
					$user_details->pan_no_of_buyer=$pan_no_of_buyer;
					$user_details->country_id=$country_id;
					$user_details->state_id=$state_id;
					$user_details->city_id=$city_id;
					$user_details->station_id=$station_id;
					$user_details->save();

					$device_details = new DeviceDetails();
					$device_details->user_id = $id;
					$device_details->user_type = 'buyer';
					$device_details->fcm_token = $fcm_token;
					$device_details->device_token = $device_type;
					$device_details->api_token = str::random(100);
					$device_details->save();

					$bank_details = new BankDetails();
					$bank_details->user_id = $id;
					$bank_details->bank_name =$bank_name;
					$bank_details->account_holder_name = $account_holder_name;
					$bank_details->branch_address = $branch_address;
					$bank_details->ifsc_code = $ifsc_code;
					$bank_details->save();

				}

				if ($bank_details->save()) {
                    //send otp
					$message = "OTP to verify your account is ". $buyer->otp ." - E - Cotton";
                    NotificationHelper::send_otp($buyer->mobile_number,$message);

					$response['data']->id=$buyer->id;
					$response['data']->mobile_number=$buyer->mobile_number;
					$response['data']->email=$buyer->email;
					$response['data']->api_token=$device_details->api_token;

					$response['status'] = 200;
					$response['message'] = 'Congratulations, your account has been successfully created.';
				}
		    }
	    }else{
	    	$image_name = '';
			if ($request->hasFile('image')) {
				$image = $request->file('image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put('buyer/profile/' . $image_name, $img, 'public');
			}
	    	$buyer = new Buyers();
			$buyer->name = $name;
			$buyer->password = Hash::make($password);
			$buyer->address = $address;
			$buyer->mobile_number= $mobile_number;
			$buyer->email=$email;
			$verification_code = mt_rand(100000,999999);
			$buyer->otp = $verification_code;
			$buyer->otp_time = date('Y-m-d H:i:s');
			$buyer->image = $image_name;
			$buyer->referral_code=$referral_code;
			if($buyer->save()){
				$id = $buyer->id;
				$user_details = new UserDetails();
				$user_details->user_id = $id;
				$user_details->user_type = $user_type;
				$user_details->seller_buyer_type = $seller_buyer_type;
				$user_details->name_of_contact_person=$name_of_contact_person;
				$user_details->business_type=$business_type;
				$user_details->registration_no=$registration_no;
				$user_details->registration_date=$registration_date;
				$user_details->registration_as_msme=$registration_as_msme;
				$user_details->turnover_year_one=$turnover_year_one;
				$user_details->turnover_date_one=$turnover_date_one;
				$user_details->turnover_year_two=$turnover_year_two;
				$user_details->turnover_date_two=$turnover_date_two;
				$user_details->turnover_year_three=$turnover_year_three;
				$user_details->turnover_date_three=$turnover_date_three;
				$user_details->oper_in_cotton_trade=$oper_in_cotton_trade;
				$user_details->gst_no=$gst_no;
				$user_details->pan_no_of_buyer=$pan_no_of_buyer;
				$user_details->country_id=$country_id;
				$user_details->state_id=$state_id;
				$user_details->city_id=$city_id;
				$user_details->station_id=$station_id;
				$user_details->save();

				$device_details = new DeviceDetails();
				$device_details->user_id = $id;
				$device_details->user_type = 'buyer';
				$device_details->fcm_token = $fcm_token;
				$device_details->device_token = $device_type;
				$device_details->api_token = str::random(100);
				$device_details->save();

				$bank_details = new BankDetails();
				$bank_details->user_id = $id;
				$bank_details->bank_name =$bank_name;
				$bank_details->account_holder_name = $account_holder_name;
				$bank_details->branch_address = $branch_address;
				$bank_details->ifsc_code = $ifsc_code;
				$bank_details->save();

			}

			if ($bank_details->save()) {
                //send otp
                $message = "OTP to verify your account is ". $buyer->otp ." - E - Cotton";
                NotificationHelper::send_otp($buyer->mobile_number,$message);

                $response['data']->id=$buyer->id;
                $response['data']->mobile_number=$buyer->mobile_number;
                $response['data']->email=$buyer->email;
                $response['data']->api_token=$device_details->api_token;

                $response['status'] = 200;
                $response['message'] = 'Congratulations, your account has been successfully created.';
            }
	    }


        return response($response, 200);
    }

    public function login_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

    	$data = $request->input('data');
		$content = json_decode($data);

		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$password = isset($content->password) ? $content->password : '';
		$fcm_token = isset($content->fcm_token) ? $content->fcm_token : '';

		$params = [
			'mobile_number' => $mobile_number,
			'password' => $password
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'password' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$login = Buyers::where('mobile_number',$mobile_number)->first();
			if(!empty($login)){
				if(Hash::check($password, $login->password)){
					if($login->is_otp_verify == 1){
						if($login->is_active == 1){
							if($login->is_approve == 1){
								if($login->is_delete == 1){

										$device_details = DeviceDetails::where(['user_id'=>$login->id,'user_type'=>'buyer'])->first();
										if(empty($device_details->api_token)){
											$device_details->api_token = str::random(100);
											$device_details->fcm_token=$fcm_token;
											$device_details->save();
										}
										if(empty($device_details->fcm_token)){
											$device_details->fcm_token=$fcm_token;
											$device_details->save();
										}
										$response['data']->id=$login->id;
										$response['data']->api_token=$device_details->api_token;
										$response['status'] = 200;
										$response['message'] = 'Login Success';
									}else{
										$response['status'] = 404;
										$response['message'] = 'Your account is deleted';
									}
								}else{
									$response['status'] = 404;
									$response['message'] = 'Your account is not approved';
								}
							}else{
								$response['status'] = 404;
								$response['message'] = 'Your account is not active please contact to adminstrator';
							}
						}else{
							$response['status'] = 404;
							$response['message'] = 'Your account is not verify';
						}
					}else{
						$response['status'] = 404;
						$response['message'] = 'You have entered wrong password';
					}
				}else{
					$response['status'] = 404;
					$response['message'] = 'You have entered wrong mobileno';
				}

		return response($response, 200);
    }

    public function otp_verify_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$otp = isset($content->otp) ? $content->otp : '';

		$params = [
			'mobile_number' => $mobile_number,
			'otp' => $otp
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'otp' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$otp_verify = Buyers::where('mobile_number',$mobile_number)->first();
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
					$otp_verify->is_otp_verify = 1;
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
			$response['message'] = 'Mobile number not found';
		}
		return response($response, 200);
    }
     public function forgot_password_buyer(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

    	$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';

    	$params = [
			'mobile_number' => $mobile_number,
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$forgot_password = Buyers::where('mobile_number',$mobile_number)->first();
		if(!empty($forgot_password)){
			if($forgot_password->is_otp_verify == 1){
				if($forgot_password->is_active == 1){
					if($forgot_password->is_approve == 1){
						if($forgot_password->is_delete == 1){
							$verification_code = mt_rand(100000,999999);
							$forgot_password->otp = $verification_code;
							$forgot_password->otp_time = date('Y-m-d H:i:s');
							$forgot_password->save();

							//send otp
							$message = "OTP to verify your account is ". $forgot_password->otp ." - E - Cotton";
                            NotificationHelper::send_otp($forgot_password->mobile_number,$message);

							$response['status'] = 200;
							$response['message'] = 'Verification code successfully sent';
						}else{
							$response['status'] = 404;
							$response['message'] = 'Your account is deleted';
						}
					}else{
						$response['status'] = 404;
						$response['message'] = 'Your account is not approved';
					}
				}else{
					$response['status'] = 404;
					$response['message'] = 'Your account is not active please contact to adminstrator';
				}
			}else{
				$response['status'] = 404;
				$response['message'] = 'Your account is not verify';
			}
		}else{
			$response['status'] = 404;
			$response['message'] = 'Mobile number not found';
		}

		return response($response, 200);
    }

     public function reset_password_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();


		$data = $request->input('data');
		$content = json_decode($data);

		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$password = isset($content->password) ? $content->password : '';
		$confirm_password = isset($content->confirm_password) ? $content->confirm_password : '';

		$params = [
			'mobile_number' => $mobile_number,
			'password' => $password,
			'confirm_password' => $confirm_password
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required',
            'password' => 'required',
            'confirm_password' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$reset_password = Buyers::where('mobile_number',$mobile_number)->first();
			if(!empty($reset_password)){
				if(Hash::check($password, $reset_password->password)) {
					$response['status'] = 404;
					$response['message'] = 'Old password and new password cannot be same';
				}else{
					$reset_password->password = Hash::make($password);
					$reset_password->otp = '';
					$reset_password->save();

					$message = "Congratulations! Your password has been reset successfully. - E - Cotton";
                    NotificationHelper::send_otp($reset_password->mobile_number,$message);

					$response['status'] = 200;
					$response['message'] = 'Your password has been reset successfully';
				}
			}else{
				$response['status'] = 404;
				$response['message'] = 'Mobile number not found';
			}

		return response($response, 200);
    }
    public function change_password_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_id = isset($content->user_id) ? $content->user_id : '';
		$current_password = isset($content->current_password) ? $content->current_password : '';
		$password = isset($content->password) ? $content->password : '';
		$confirm_password = isset($content->confirm_password) ? $content->confirm_password : '';

    	$params = [
			'user_id' => $user_id,
			'current_password' => $current_password,
			'password' => $password,
			'confirm_password' => $confirm_password,
		];

		$validator = Validator::make($params, [
            'user_id' => 'required',
            'current_password' => 'required',
            'password' => 'required',
            'confirm_password' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$token =  $request->bearerToken();
		$buyer = DeviceDetails::where(['user_id'=>$user_id,'api_token'=>$token,'user_type'=>'buyer'])->first();
		if(!empty($buyer)){
			$change_password = Buyers::where('id', $user_id)->first();
			if(!empty($change_password)){
					if(Hash::check($current_password, Hash::make($password))){
						$response['status'] = 404;
						$response['message'] = 'Old password and new password cannot be same';
					}elseif (!Hash::check($current_password, $change_password->password)) {
						$response['status'] = 404;
						$response['message'] = 'Current password doent not match';
					}else{
						$change_password->password = Hash::make($password);
						$change_password->otp = '';
						$change_password->save();

						$message = "Congratulations! Your password has been changed successfully. - E - Cotton";
                        NotificationHelper::send_otp($change_password->mobile_number,$message);

						$response['status'] = 200;
						$response['message'] = 'Your password has been successfully changed';
					}
			}else{
				$response['status'] = 404;
				$response['message'] = 'User not found';
			}
		}else{
	    	$response['status'] = 401;
	        $response['message'] = 'Unauthenticated';
		}


		return response($response, 200);
    }
    public function resend_otp_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

    	$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';


    	$params = [
			'mobile_number' => $mobile_number,
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }
    	$resend_data = Buyers::where('mobile_number',$mobile_number)->first();
		if(!empty($resend_data)){
			$current = date("Y-m-d H:i:s");
			$otp_time = $resend_data->otp_time;
			$diff = strtotime($current) - strtotime($otp_time);
			$days    = floor($diff / 86400);
			$hours   = floor(($diff - ($days * 86400)) / 3600);
			$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);

			if (($diff > 0) && ($minutes <= 180)) {
                //send otp
                $message = "OTP to verify your account is ". $resend_data->otp ." - E - Cotton";
                NotificationHelper::send_otp($resend_data->mobile_number,$message);

                $response['status'] = 200;
                $response['message'] = 'Resend OTP successfully';
			} else {
                $verification_code = mt_rand(100000,999999);
                $resend_data->otp = $verification_code;
                $resend_data->otp_time = date('Y-m-d H:i:s');
                $resend_data->save();

                $message = "OTP to verify your account is ". $resend_data->otp ." - E - Cotton";
                NotificationHelper::send_otp($resend_data->mobile_number,$message);

                $response['status'] = 200;
                $response['message'] = 'Resend OTP successfully';
            }
        } else {
            $response['status'] = 404;
            $response['message'] = 'Mobile number not found';
        }

		return response($response, 200);
    }
    public function profile_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_id = isset($content->user_id) ? $content->user_id : '';


		$params = [
			'user_id' => $user_id,
		];

		$validator = Validator::make($params, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $profile =  Buyers::with('bank_details', 'user_details')->where('tbl_buyers.id',$user_id)->first();
        if(!empty($profile)){
            $response['data']->id=$profile->id;
            $response['data']->mobile_number = ($profile->mobile_number)?$profile->mobile_number:'';
            $response['data']->email=($profile->email)?$profile->email:'';
            $response['data']->user_type=($profile->user_details->user_type)?$profile->user_details->user_type:'';
            $response['data']->seller_buyer_type=($profile->user_details->seller_buyer_type)?$profile->user_details->seller_buyer_type:'';
            $response['data']->name=($profile->name)?$profile->name:'';
            $response['data']->address=($profile->address)?$profile->address:'';
            $response['data']->name_of_contact_person=($profile->user_details->name_of_contact_person)?$profile->user_details->name_of_contact_person:'';
            $response['data']->business_type=($profile->user_details->business_type)?$profile->user_details->business_type:'';
            $response['data']->registration_no=($profile->user_details->registration_no)?$profile->user_details->registration_no:'';
            $response['data']->registration_date=($profile->user_details->registration_date)?$profile->user_details->registration_date:'';
            $response['data']->registration_as_msme=($profile->user_details->registration_as_msme)?$profile->user_details->registration_as_msme:'';
            $response['data']->turnover_year_one=($profile->user_details->turnover_year_one)?$profile->user_details->turnover_year_one:'';
            $response['data']->turnover_date_one=($profile->user_details->turnover_date_one)?$profile->user_details->turnover_date_one:'';
            $response['data']->turnover_year_two=($profile->user_details->turnover_year_two)?$profile->user_details->turnover_year_two:'';
            $response['data']->turnover_date_two=($profile->user_details->turnover_date_two)?$profile->user_details->turnover_date_two:'';
            $response['data']->turnover_year_three=($profile->user_details->turnover_year_three)?$profile->user_details->turnover_year_three:'';
            $response['data']->turnover_date_three=($profile->user_details->turnover_date_three)?$profile->user_details->turnover_date_three:'';
            $response['data']->oper_in_cotton_trade=($profile->user_details->oper_in_cotton_trade)?$profile->oper_in_cotton_trade:'';
            $response['data']->gst_no=($profile->user_details->gst_no)?$profile->user_details->gst_no:'';
            $response['data']->pan_no_of_buyer=($profile->user_details->pan_no_of_buyer)?$profile->user_details->pan_no_of_buyer:'';
            $response['data']->bank_name=($profile->bank_details->bank_name)?$profile->bank_details->bank_name:'';
            $response['data']->account_holder_name=($profile->bank_details->account_holder_name)?$profile->bank_details->account_holder_name:'';
            $response['data']->branch_address=($profile->bank_details->branch_address)?$profile->bank_details->branch_address:'';
            $response['data']->ifsc_code=($profile->bank_details->ifsc_code)?$profile->bank_details->ifsc_code:'';
            $response['data']->referral_code=($profile->referral_code)?$profile->referral_code:'';

            $response['status'] = 200;
            $response['message'] = 'Profile';
        }else{
            $response['status'] = 404;
            $response['message'] = 'User not found';
        }

		return response($response, 200);
    }

    public function logout_buyer(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_id = isset($content->user_id) ? $content->user_id : '';

		$params = [
			'user_id' => $user_id,
		];

		$validator = Validator::make($params, [
            'user_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

    	$logout = DeviceDetails::where(['user_id'=>$user_id,'user_type'=>'buyer'])->first();
	    if(!empty($logout)){
	    	$logout->fcm_token = '';
	    	$logout->api_token = '';
	    	$logout->save();

	    	$response['status'] = 200;
			$response['message'] = 'Logged Out Successfully';
	    }else{
	    	$response['status'] = 404;
			$response['message'] = 'User not found';
	    }

		return response($response, 200);
    }

    public function send_broker_request(Request $request)
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
            'buyer_id' => 'required|exists:tbl_buyers,id',
            'broker_id' => 'required|exists:tbl_brokers,id'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

        $check_broker = AddBrokers::where(['buyer_id' => $buyer_id, 'broker_id' => $broker_id, 'user_type' => 'buyer'])->first();
        if (!empty($check_broker)) {
            $response['status'] = 404;
            $response['message'] = 'Broker already added in your list!';
            return response($response, 200);
        }

    	$broker_request = BrokerRequest::where(['buyer_id'=>$buyer_id,'broker_id'=> $broker_id])->first();
	    if(empty($broker_request)){
	    	$broker_request = new BrokerRequest();
            $broker_request->status = 0;
            $broker_request->buyer_id = $buyer_id;
            $broker_request->broker_id = $broker_id;
            $broker_request->save();

            $response['message'] = 'Request sent successfully!';

	    } else {
            $response['status'] = 404;
            if ($broker_request->status == 0) {
                $response['message'] = 'You have already sent request';
            } else if ($broker_request->status == 1) {
                $response['message'] = 'Broker is already added';
            } else {
                $response['status'] = 200;
                BrokerRequest::where(['buyer_id'=>$buyer_id,'broker_id'=> $broker_id])->update([
                    'status' => 0
                ]);
                $response['message'] = 'Request sent successfully!';
            }
	    }

		return response($response, 200);
    }
}
