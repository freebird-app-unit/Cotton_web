<?php

namespace App\Http\Controllers\Api\broker;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Brokers;
use App\Models\Sellers;
use App\Models\Buyers;
use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\DeviceDetails;
use App\Models\NegotiationComplete;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Validator;
use Storage;
use Image;
use File;
use App\Helper\NotificationHelper;
use App\Models\AddBrokers;

class LoginController extends Controller
{
     public function registration_broker(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_type = isset($content->user_type) ? $content->user_type : '';
		$device_type = isset($content->device_type) ? $content->device_type : '';
		$password = isset($content->password) ? $content->password : '';
		$name = isset($content->name) ? $content->name : '';
		$name_of_contact_person = isset($content->name_of_contact_person) ? $content->name_of_contact_person : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$mobile_number_2 = isset($content->mobile_number_2) ? $content->mobile_number_2 : '';
		$gst_no = isset($content->gst_no) ? $content->gst_no : '';
		$address = isset($content->address) ? $content->address : '';
		$email = isset($content->email) ? $content->email : '';
		$fcm_token = isset($content->fcm_token) ? $content->fcm_token : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';
		$turnover_year_one = isset($content->turnover_year_one) ? $content->turnover_year_one : '';
		$turnover_date_one = isset($content->turnover_date_one) ? $content->turnover_date_one : '';
		$turnover_year_two = isset($content->turnover_year_two) ? $content->turnover_year_two : '';
		$turnover_date_two = isset($content->turnover_date_two) ? $content->turnover_date_two : '';
		$turnover_year_three = isset($content->turnover_year_three) ? $content->turnover_year_three : '';
		$turnover_date_three = isset($content->turnover_date_three) ? $content->turnover_date_three : '';
		$website = isset($content->website) ? $content->website : '';

		$params = [
			'mobile_number' => $mobile_number,
			'email' => $email
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required|digits:10|unique:tbl_brokers,mobile_number',
            'email' => 'required|email|unique:tbl_brokers,email|max:255',
        ]);

        if ($validator->fails()) {
	       $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$header_image_name = '';
		if ($request->hasFile('header_image')) {
			$image = $request->file('header_image');
			$header_image_name = time() . '.' . $image->getClientOriginalExtension();
			$img = Image::make($image->getRealPath());
			$img->stream(); // <-- Key point
			Storage::disk('public')->put('broker/header_image/' . $header_image_name, $img, 'public');
		}

		$stamp_image_name = '';
		if ($request->hasFile('stamp_image')) {
			$image = $request->file('stamp_image');
			$stamp_image_name = time() . '.' . $image->getClientOriginalExtension();
			$img = Image::make($image->getRealPath());
			$img->stream(); // <-- Key point
			Storage::disk('public')->put('broker/stamp_image/' . $stamp_image_name, $img, 'public');
		}

    	$broker = new Brokers();
    	$broker->name = $name;
    	$broker->address = $address;
    	$broker->email = $email;
		$broker->password = Hash::make($password);
		$broker->mobile_number= $mobile_number;
		$broker->mobile_number_2= $mobile_number_2;
		$verification_code = mt_rand(100000,999999);
		$broker->otp = $verification_code;
		$broker->otp_time = date('Y-m-d H:i:s');
		$broker->header_image = $header_image_name;
		$broker->stamp_image = $stamp_image_name;
		$broker->website = $website;
		$broker->code=ucfirst($name_of_contact_person[0]).ucfirst($name_of_contact_person[1]).mt_rand(100000,999999);
		if($broker->save()){
			$id = $broker->id;
			$user_details = new UserDetails();
			$user_details->user_id = $id;
			$user_details->user_type = $user_type;
			$user_details->name_of_contact_person=$name_of_contact_person;
			$user_details->gst_no=$gst_no;
			$user_details->turnover_year_one=$turnover_year_one;
			$user_details->turnover_date_one=$turnover_date_one;
			$user_details->turnover_year_two=$turnover_year_two;
			$user_details->turnover_date_two=$turnover_date_two;
			$user_details->turnover_year_three=$turnover_year_three;
			$user_details->turnover_date_three=$turnover_date_three;
			$user_details->country_id=$country_id;
			$user_details->state_id=$state_id;
			$user_details->city_id=$city_id;
			$user_details->station_id=$station_id;
			$user_details->save();

			$device_details = new DeviceDetails();
			$device_details->user_id = $id;
			$device_details->user_type = 'broker';
			$device_details->fcm_token = $fcm_token;
			$device_details->device_token = $device_type;
			$device_details->api_token = str::random(100);
			$device_details->save();
		}

        //send otp
        $message = "OTP to verify your account is ". $verification_code ." - E - Cotton";
        NotificationHelper::send_otp($broker->mobile_number,$message);

		if($device_details->save()){
            $response['data']->id=$broker->id;
            $response['data']->mobile_number=$broker->mobile_number;
            $response['data']->email=$broker->email;
            $response['data']->api_token=$device_details->api_token;

            $response['status'] = 200;
            $response['message'] = 'Congratulations, your account has been successfully created.';
        }

        return response($response, 200);
    }

    public function login_broker(Request $request)
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

    	$login = Brokers::where('mobile_number',$mobile_number)->first();
			if(!empty($login)){
				if(Hash::check($password, $login->password)){
					if($login->is_otp_verify == 1){
						if($login->is_active == 1){
							if($login->is_approve == 1){
								if($login->is_delete == 1){

										$device_details = DeviceDetails::where(['user_id'=>$login->id,'user_type'=>'broker'])->first();

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
										$response['data']->code=$login->code;
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

    public function otp_verify_broker(Request $request)
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

    	$otp_verify = Brokers::where('mobile_number',$mobile_number)->first();
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
     public function forgot_password_broker(Request $request){
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

    	$forgot_password = Brokers::where('mobile_number',$mobile_number)->first();
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

     public function reset_password_broker(Request $request)
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
    	$reset_password = Brokers::where('mobile_number',$mobile_number)->first();
			if(!empty($reset_password)){
				if(Hash::check($password, $reset_password->password)) {
					$response['status'] = 404;
					$response['message'] = 'Old password and new password cannot be same';
				}else{
					$reset_password->password = Hash::make($password);
					$reset_password->otp = '';
					$reset_password->save();

                    // Send OTP
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
    public function change_password_broker(Request $request)
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
		$broker = DeviceDetails::where(['user_id'=>$user_id,'api_token'=>$token,'user_type'=>'broker'])->first();
		if(!empty($broker)){
			$change_password = Brokers::where('id', $user_id)->first();
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

                        // Send OTP
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
    public function resend_otp_broker(Request $request)
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

    	$resend_data = Brokers::where('mobile_number',$mobile_number)->first();
		if (!empty($resend_data)) {
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
    public function profile_broker(Request $request)
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

        $profile =  Brokers::with('bank_details', 'userDetails')->where('tbl_brokers.id',$user_id)->first();
        if(!empty($profile)){
            $response['data']->id=$profile->id;
            $response['data']->mobile_number=!empty($profile->mobile_number)?$profile->mobile_number:'';
            $response['data']->email=!empty($profile->email)?$profile->email:'';
            $response['data']->user_type=!empty($profile->userDetails->user_type)?$profile->userDetails->user_type:'';
            $response['data']->seller_buyer_type=!empty($profile->userDetails->seller_buyer_type)?$profile->userDetails->seller_buyer_type:'';
            $response['data']->name=!empty($profile->name)?$profile->name:'';
            $response['data']->address=!empty($profile->address)?$profile->address:'';
            $response['data']->name_of_contact_person=!empty($profile->userDetails->name_of_contact_person)?$profile->userDetails->name_of_contact_person:'';
            $response['data']->business_type=!empty($profile->userDetails->business_type)?$profile->userDetails->business_type:'';
            $response['data']->registration_no=!empty($profile->userDetails->registration_no)?$profile->userDetails->registration_no:'';
            $response['data']->registration_date=!empty($profile->userDetails->registration_date)?$profile->userDetails->registration_date:'';
            $response['data']->registration_as_msme=!empty($profile->userDetails->registration_as_msme)?$profile->userDetails->registration_as_msme:'';
            $response['data']->turnover_year_one=!empty($profile->userDetails->turnover_year_one)?$profile->userDetails->turnover_year_one:'';
            $response['data']->turnover_date_one=!empty($profile->userDetails->turnover_date_one)?$profile->userDetails->turnover_date_one:'';
            $response['data']->turnover_year_two=!empty($profile->userDetails->turnover_year_two)?$profile->userDetails->turnover_year_two:'';
            $response['data']->turnover_date_two=!empty($profile->userDetails->turnover_date_two)?$profile->userDetails->turnover_date_two:'';
            $response['data']->turnover_year_three=!empty($profile->userDetails->turnover_year_three)?$profile->userDetails->turnover_year_three:'';
            $response['data']->turnover_date_three=!empty($profile->userDetails->turnover_date_three)?$profile->userDetails->turnover_date_three:'';
            $response['data']->oper_in_cotton_trade=!empty($profile->userDetails->oper_in_cotton_trade)?$profile->userDetails->oper_in_cotton_trade:'';
            $response['data']->gst_no=!empty($profile->userDetails->gst_no)?$profile->userDetails->gst_no:'';
            $response['data']->pan_no_of_buyer=!empty($profile->userDetails->pan_no_of_buyer)?$profile->userDetails->pan_no_of_buyer:'';
            $response['data']->bank_name=!empty($profile->bank_details->bank_name)?$profile->bank_details->bank_name:'';
            $response['data']->account_holder_name=!empty($profile->bank_details->account_holder_name)?$profile->bank_details->account_holder_name:'';
            $response['data']->branch_address=!empty($profile->bank_details->branch_address)?$profile->bank_details->branch_address:'';
            $response['data']->ifsc_code=!empty($profile->bank_details->ifsc_code)?$profile->bank_details->ifsc_code:'';
            $response['data']->referral_code=!empty($profile->referral_code)?$profile->referral_code:'';
            $response['data']->website=!empty($profile->website)?$profile->website:'';
            $response['data']->country=!empty($profile->userDetails->country)?$profile->userDetails->country->name:'';
            $response['data']->state=!empty($profile->userDetails->state)?$profile->userDetails->state->name:'';
            $response['data']->city=!empty($profile->userDetails->city)?$profile->userDetails->city->name:'';
			$response['data']->country_id=!empty($profile->userDetails->country_id)?$profile->userDetails->country_id:'';
            $response['data']->state_id=!empty($profile->userDetails->state_id)?$profile->userDetails->state_id:'';
            $response['data']->city_id=!empty($profile->userDetails->city_id)?$profile->userDetails->city_id:'';
            $response['data']->station_id=!empty($profile->userDetails->station_id)?$profile->userDetails->station_id:''; 
            $response['data']->station=!empty($profile->userDetails->station)?$profile->userDetails->station->name:'';

            $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $profile->stamp_image);
            $broker_stamp_image = '';
            if (!empty($profile->stamp_image) && File::exists($broker_stamp_img)) {
                $broker_stamp_image = asset('storage/app/public/broker/stamp_image/' . $profile->stamp_image);
            }

            $broker_header_img = storage_path('app/public/broker/header_image/' . $profile->header_image);
            $broker_header_image = '';
            if (!empty($profile->header_image) && File::exists($broker_header_img)) {
                $broker_header_image = asset('storage/app/public/broker/header_image/' . $profile->header_image);
            }
            $response['data']->header_image = $broker_header_image;
            $response['data']->stamp_image = $broker_stamp_image;

            $response['status'] = 200;
            $response['message'] = 'Profile';
        } else {
            $response['status'] = 404;
            $response['message'] = 'User not found';
        }

		return response($response, 200);
    }

    public function logout_broker(Request $request)
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
    	$logout = DeviceDetails::where(['user_id'=>$user_id,'user_type'=>'broker'])->first();
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

    public function seller_buyer_list_base_on_code(Request $request)
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
            'broker_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }
	    $seller_array = [];
	    $buyer_array = [];
	    $final_array = [];
	    $broker = Brokers::where('id',$broker_id)->first();
	    if(!empty($broker)){
	    	$get_seller_list = Sellers::where('referral_code',$broker->code)->get();
	   		if(count($get_seller_list)>0){
	   			foreach ($get_seller_list as $value1) {
	   				$created_at = date('d-m-Y', strtotime($value1->created_at));
	   				$count = NegotiationComplete::where('seller_id',$value1->id)->get()->count();
	   				$sum_bales = NegotiationComplete::where('seller_id',$value1->id)->sum('no_of_bales');
	   				$seller_array[] = [
	   					'id' => $value1->id,
	   					'name' => $value1->name,
	   					'address' => $value1->address,
	   					'mobile_number' => $value1->mobile_number,
	   					'email' => $value1->email,
	   					'created_at' => $created_at,
	   					'count' => $count,
	   					'sum_bales' => $sum_bales,
	   					'user_type' => 'seller'
	   				];
	   			}
	   		}
	   		$get_buyer_list = Buyers::where('referral_code',$broker->code)->get();
	   		if(count($get_buyer_list)>0){
	   			foreach ($get_buyer_list as $value2) {
	   				$count = NegotiationComplete::where('seller_id',$value2->id)->get()->count();
	   				$sum_bales = NegotiationComplete::where('seller_id',$value2->id)->sum('no_of_bales');
	   				$created_at = date('d-m-Y', strtotime($value2->created_at));
	   				$buyer_array[] = [
	   					'id' => $value2->id,
	   					'name' => $value2->name,
	   					'address' => $value2->address,
	   					'mobile_number' => $value2->mobile_number,
	   					'email' => $value2->email,
	   					'created_at' => $created_at,
	   					'count' => $count,
	   					'sum_bales' => $sum_bales,
	   					'user_type' => 'buyer'
	   				];
	   			}
	   		}
	   		$final_array = array_merge($seller_array,$buyer_array);
	   		/*$final_array[] = [
	   			'seller' => $seller_array,
	   			'buyer' => $buyer_array,
	   		];*/
	   		$response['status'] = 200;
	        $response['message'] = 'Seller Buyer List';
	        $response['data'] = $final_array;
	    }else{
	    	$response['status'] = 404;
	        $response['message'] = 'User Not found';
	    }
	    return response($response, 200);
    }

    public function seller_buyer_list(Request $request)
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
            'broker_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }
	    $seller_array = [];
	    $buyer_array = [];
	    $final_array = [];
	    $broker = AddBrokers::with('seller')->where('broker_id',$broker_id)->where('user_type', 'seller')->get();
        if(!empty($broker)){
            foreach($broker as $val) {
                foreach ($val->seller as $value1) {
                    $seller_array[] = [
                        'id' => $value1->id,
                        'name' => $value1->name,
                        'user_type' => 'seller'
                    ];
                }
            }
        }
        $broker_seller = AddBrokers::with('buyer')->where('broker_id',$broker_id)->where('user_type', 'buyer')->get();

        if(!empty($broker_seller)){
            foreach($broker_seller as $val) {
                foreach ($val->buyer as $value) {
                    $buyer_array[] = [
                        'id' => $value->id,
                        'name' => $value->name,
                        'user_type' => 'buyer'
                    ];
                }
            }
        }
        $final_array = array_merge($seller_array,$buyer_array);
        /*$final_array[] = [
            'seller' => $seller_array,
            'buyer' => $buyer_array,
        ];*/
        $response['status'] = 200;
        $response['message'] = 'Seller Buyer List';
        $response['data'] = $final_array;

	    return response($response, 200);
    }
}
