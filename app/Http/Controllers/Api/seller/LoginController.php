<?php

namespace App\Http\Controllers\Api\seller;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\Sellers;
use App\Models\BankDetails;
use App\Models\UserDetails;
use App\Models\DeviceDetails;
use App\Models\BussinessType;
use App\Models\RegistrationType;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Station;
use App\Models\BuyerType;
use App\Models\SellerType;
use App\Models\Brokers;
use App\Models\News;
use App\Models\UserPlan;
use App\Models\Plan;
use Illuminate\Support\Str;
use Validator;
use Storage;
use Image;
use File;
use App\Models\AddBrokers;
use App\Helper\NotificationHelper;
use App\Models\Buyers;
use App\Models\Transactions;
use Carbon\Carbon;

class LoginController extends Controller
{
    public function registration_seller(Request $request)
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
			'email' => $email,
			//'plan_id' => $plan_id,
		];

		$validator = Validator::make($params, [
            'mobile_number' => 'required|digits:10|unique:tbl_sellers,mobile_number',
            'email' => 'required|email|unique:tbl_sellers,email|max:255',
            //'plan_id' => 'required|exists:tbl_plans,id',
        ]);

        if ($validator->fails()) {
	            $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

	    if (!empty($referral_code)) {
	    	$broker = Brokers::where('code',$referral_code)->first();
	    	if (empty($broker)) {
	    		$response['status'] = 404;
				$response['message'] = 'Referral Code is not avaialble';
		    } else {
		    	$image_name = '';
                if ($request->hasFile('image')) {
                    $image = $request->file('image');
                    $image_name = time() . '.' . $image->getClientOriginalExtension();
                    $img = Image::make($image->getRealPath());
                    $img->stream(); // <-- Key point
                    Storage::disk('public')->put('seller/profile/' . $image_name, $img, 'public');
                }

                $seller = new Sellers();
				$seller->name = $name;
				$seller->password = Hash::make($password);
				$seller->address = $address;
				$seller->mobile_number= $mobile_number;
				$seller->email=$email;
				$verification_code = mt_rand(100000,999999);
				$seller->otp = $verification_code;
				$seller->otp_time = date('Y-m-d H:i:s');
				$seller->image = $image_name;
				$seller->referral_code=$referral_code;
				$seller->wallet_amount=0;

				if($seller->save()){
					$id = $seller->id;

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
					$device_details->user_type = 'seller';
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
					$bank_details->user_type = 'seller';
					$bank_details->save();

					$add_broker = new AddBrokers();
					$add_broker->buyer_id = $id;
					$add_broker->user_type = 'seller';
					$add_broker->broker_id = $broker->id;
					$add_broker->broker_type = 'default';
					$add_broker->created_at =date('Y-m-d H:i:s');
					$add_broker->updated_at =date('Y-m-d H:i:s');
					$add_broker->save();
				}

				if($bank_details->save()){
                    //send otp
                    $message = "OTP to verify your account is ". $verification_code ." - E - Cotton";
                    // NotificationHelper::send_otp($seller->mobile_number,$message);

                    $response['data']->id=$seller->id;
                    $response['data']->mobile_number=$seller->mobile_number;
                    $response['data']->email=$seller->email;
                    $response['data']->api_token=$device_details->api_token;

                    $response['status'] = 200;
                    $response['message'] = 'Congratulations, your account has been successfully created.';
                }
		    }
	    } else {
	    	$image_name = '';
			if ($request->hasFile('image')) {
				$image = $request->file('image');
				$image_name = time() . '.' . $image->getClientOriginalExtension();
				$img = Image::make($image->getRealPath());
				$img->stream(); // <-- Key point
				Storage::disk('public')->put('seller/profile/' . $image_name, $img, 'public');
			}

            $plan_detail = Plan::where('id', $plan_id)->first();

            $seller = new Sellers();
            $seller->name = $name;
            $seller->password = Hash::make($password);
            $seller->address = $address;
            $seller->mobile_number= $mobile_number;
            $seller->email=$email;
            $verification_code = mt_rand(100000,999999);
            $seller->otp = $verification_code;
            $seller->otp_time = date('Y-m-d H:i:s');
            $seller->image = $image_name;
            $seller->referral_code=$referral_code;
            $seller->wallet_amount=$plan_detail->price;

            if($seller->save()){
                $id = $seller->id;

                $date = Carbon::now();
                $date->addDays($plan_detail->validity);

                UserPlan::create([
                    'user_id' => $id,
                    'user_type' => 'seller',
                    'plan_id' => $plan_id,
                    'status' => 1,
                    'purchase_date' => date('Y-m-d'),
                    'expiry_date' => $date
                ]);

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
                $device_details->user_type = 'seller';
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
                $bank_details->user_type = 'seller';
                $bank_details->save();

            }

            if($bank_details->save()){
                //send otp
                $message = "OTP to verify your account is ". $seller->otp ." - E - Cotton";
                // NotificationHelper::send_otp($seller->mobile_number,$message);

                $response['data']->id=$seller->id;
                $response['data']->mobile_number=$seller->mobile_number;
                $response['data']->email=$seller->email;
                $response['data']->api_token=$device_details->api_token;

                $response['status'] = 200;
                $response['message'] = 'Congratulations, your account has been successfully created.';
            }
	    }

        return response($response, 200);
    }

    public function login_seller(Request $request)
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

    	$login = Sellers::where('mobile_number',$mobile_number)->first();
			if(!empty($login)){
				if(Hash::check($password, $login->password)){
					if($login->is_otp_verify == 1){
						if($login->is_active == 1){
							if($login->is_approve == 1){
								if($login->is_delete == 1){

										$device_details = DeviceDetails::where(['user_id'=>$login->id,'user_type'=>'seller'])->first();
                                        $device_details->api_token = str::random(100);
                                        $device_details->fcm_token=$fcm_token;
                                        $device_details->save();

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

    public function otp_verify_seller(Request $request)
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
    	$otp_verify = Sellers::where('mobile_number',$mobile_number)->first();
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
     public function forgot_password_seller(Request $request){
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

    	$forgot_password = Sellers::where('mobile_number',$mobile_number)->first();
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

     public function reset_password_seller(Request $request)
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

    	$reset_password = Sellers::where('mobile_number',$mobile_number)->first();
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
    public function change_password_seller(Request $request)
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
		$seller = DeviceDetails::where(['user_id'=>$user_id,'api_token'=>$token,'user_type'=>'seller'])->first();
		if(!empty($seller)){
			$change_password = Sellers::where('id', $user_id)->first();
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
    public function resend_otp_seller(Request $request)
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

    	$resend_data = Sellers::where('mobile_number',$mobile_number)->first();
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

                // Send OTP
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
    public function profile_seller(Request $request)
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


        $profile =  Sellers::with('bank_details', 'user_details')->where('tbl_sellers.id',$user_id)->first();
        if(!empty($profile)){
            $response['data']->id=$profile->id;
            $response['data']->mobile_number=!empty($profile->mobile_number)?$profile->mobile_number:'';
            $response['data']->email=!empty($profile->email)?$profile->email:'';
            $response['data']->user_type=!empty($profile->user_details->user_type)?$profile->user_details->user_type:'';
            $response['data']->seller_buyer_type=!empty($profile->user_details->seller_buyer_type)?$profile->user_details->seller_buyer_type:'';
            $response['data']->name=!empty($profile->name)?$profile->name:'';
            $response['data']->address=!empty($profile->address)?$profile->address:'';
            $response['data']->name_of_contact_person=!empty($profile->user_details->name_of_contact_person)?$profile->user_details->name_of_contact_person:'';
            $response['data']->business_type=!empty($profile->user_details->business_type)?$profile->user_details->business_type:'';
            $response['data']->registration_no=!empty($profile->user_details->registration_no)?$profile->user_details->registration_no:'';
            $response['data']->registration_date=!empty($profile->user_details->registration_date)?$profile->user_details->registration_date:'';
            $response['data']->registration_as_msme=!empty($profile->user_details->registration_as_msme)?$profile->user_details->registration_as_msme:'';
            $response['data']->turnover_year_one=!empty($profile->user_details->turnover_year_one)?$profile->user_details->turnover_year_one:'';
            $response['data']->turnover_date_one=!empty($profile->user_details->turnover_date_one)?$profile->user_details->turnover_date_one:'';
            $response['data']->turnover_year_two=!empty($profile->user_details->turnover_year_two)?$profile->user_details->turnover_year_two:'';
            $response['data']->turnover_date_two=!empty($profile->user_details->turnover_date_two)?$profile->user_details->turnover_date_two:'';
            $response['data']->turnover_year_three=!empty($profile->user_details->turnover_year_three)?$profile->user_details->turnover_year_three:'';
            $response['data']->turnover_date_three=!empty($profile->user_details->turnover_date_three)?$profile->user_details->turnover_date_three:'';
            $response['data']->oper_in_cotton_trade=!empty($profile->user_details->oper_in_cotton_trade)?$profile->user_details->oper_in_cotton_trade:'';
            $response['data']->gst_no=!empty($profile->user_details->gst_no)?$profile->user_details->gst_no:'';
            $response['data']->pan_no_of_buyer=!empty($profile->user_details->pan_no_of_buyer)?$profile->user_details->pan_no_of_buyer:'';
            $response['data']->bank_name=!empty($profile->bank_details->bank_name)?$profile->bank_details->bank_name:'';
            $response['data']->account_holder_name=!empty($profile->bank_details->account_holder_name)?$profile->bank_details->account_holder_name:'';
            $response['data']->branch_address=!empty($profile->bank_details->branch_address)?$profile->bank_details->branch_address:'';
            $response['data']->ifsc_code=!empty($profile->bank_details->ifsc_code)?$profile->bank_details->ifsc_code:'';
            $response['data']->referral_code=!empty($profile->referral_code)?$profile->referral_code:'';
            $response['data']->country=!empty($profile->user_details->country)?$profile->user_details->country->name:'';
            $response['data']->state=!empty($profile->user_details->state)?$profile->user_details->state->name:'';
            $response['data']->city=!empty($profile->user_details->city)?$profile->user_details->city->name:'';
            $response['data']->station=!empty($profile->user_details->station)?$profile->user_details->station->name:'';

            $image = '';
            $seller_img = storage_path('app/public/seller/profile/' . $profile->image);
            if (File::exists($seller_img)) {
                $image = asset('storage/app/public/seller/profile/' . $profile->image);
            }

            $response['data']->profile_image = $image;

            $response['status'] = 200;
            $response['message'] = 'Profile';
        }else{
            $response['status'] = 404;
            $response['message'] = 'User not found';
        }

		return response($response, 200);
    }

    public function logout_seller(Request $request)
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
    	$logout = DeviceDetails::where(['user_id'=>$user_id,'user_type'=>'seller'])->first();
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
    public function business_type(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$business_type_list = [];
		$business = BussinessType::where('is_delete',1)->get();
		if(count($business)>0){
			foreach ($business as $value) {
				$business_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Bussiness Type';
			$response['data'] = $business_type_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
    public function registration_as(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$registration_type_list = [];
		$registration = RegistrationType::where('is_delete',1)->get();
		if(count($registration)>0){
			foreach ($registration as $value) {
				$registration_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Registration Type';
			$response['data'] = $registration_type_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
     public function country_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$country_list = [];
		$country = Country::where('is_delete',1)->get();
		if(count($country)>0){
			foreach ($country as $value) {
				$country_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Country List';
			$response['data'] = $country_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
    public function state_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$country_id = isset($content->country_id) ? $content->country_id : '';

		$state_list = [];
		$state = State::where('country_id',$country_id)->where('is_delete',1)->get();
		if(count($state)>0){
			foreach ($state as $value) {
				$state_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'state List';
			$response['data'] = $state_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
    public function city_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$state_id = isset($content->state_id) ? $content->state_id : '';

		$city_list = [];
		$city = City::where('state_id',$state_id)->where('is_delete',1)->get();
		if(count($city)>0){
			foreach ($city as $value) {
				$city_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'city List';
			$response['data'] = $city_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

     public function station_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$city_id = isset($content->city_id) ? $content->city_id : '';

		$station_list = [];
		$station = Station::where('city_id',$city_id)->where('is_delete',1)->get();
		if(count($station)>0){
			foreach ($station as $value) {
				$station_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'station List';
			$response['data'] = $station_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function buyer_type(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$buyer_type_list = [];
		$buyer_type = BuyerType::where(['is_active'=>1,'is_delete'=>1])->get();
		if(count($buyer_type)>0){
			foreach ($buyer_type as $value) {
				$buyer_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Buyer Type List';
			$response['data'] = $buyer_type_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

     public function seller_type(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$seller_type_list = [];
		$seller_type = SellerType::where(['is_active'=>1,'is_delete'=>1])->get();
		if(count($seller_type)>0){
			foreach ($seller_type as $value) {
				$seller_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Seller Type List';
			$response['data'] = $seller_type_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
     public function sellertype_buyertype_businesstype_registrationas(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$sellertype_buyertype_businesstype_registrationas =[];
		$seller_type_list = [];
		$seller_type = SellerType::where(['is_active'=>1,'is_delete'=>1])->get();
		if(count($seller_type)>0){
			foreach ($seller_type as $value) {
				$seller_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
		}
		$buyer_type_list = [];
		$buyer_type = BuyerType::where(['is_active'=>1,'is_delete'=>1])->get();
		if(count($buyer_type)>0){
			foreach ($buyer_type as $value) {
				$buyer_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
		}
		$business_type_list = [];
		$business = BussinessType::where('is_delete',1)->get();
		if(count($business)>0){
			foreach ($business as $value) {
				$business_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
		}
		$registration_type_list = [];
		$registration = RegistrationType::where('is_delete',1)->get();
		if(count($registration)>0){
			foreach ($registration as $value) {
				$registration_type_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
		}
		$sellertype_buyertype_businesstype_registrationas[] = [
			'seller_type' => $seller_type_list,
			'buyer_type' => $buyer_type_list,
			'business_type' => $business_type_list,
			'registration_as' => $registration_type_list
		];
		$response['status'] = 200;
		$response['data'] = $sellertype_buyertype_businesstype_registrationas;
		return response($response, 200);
    }

	public function news_list(Request $request)
    {
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$offset = isset($content->offset) ? $content->offset : 10;
		$limit = isset($content->limit) ? $content->limit : 0;

        $news_list = [];
        $news = News::select('id', 'name', 'image')->skip($offset)->take($limit)->get();

        if (count($news) > 0) {
            foreach ($news as $value) {
				$image = '';
				if(file_exists(storage_path('app/public/news/' . $value->image))){
                    $image = url('storage/app/public/news/'.$value->image);
                }
                $news_list[] = [
                    'id' => $value->id,
                    'name' => $value->name,
                    'image' => $image
                ];
            }
        }

        $response['status'] = 200;
        $response['data'] = $news_list;
        return response($response, 200);
    }

	public function news_details(Request $request)
    {
        $response = array();
        $response['status'] = 200;
        $response['message'] = '';
        $response['data'] = (object)array();

		$data = $request->input('data');
        $content = json_decode($data);

        $news_id = isset($content->news_id) ? $content->news_id : '';

		$params = [
            'news_id' => $news_id
        ];

        $validator = Validator::make($params, [
            'news_id' => 'required|exists:tbl_news,id',
        ]);

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $news_list = [];
        $news = News::where('id', $news_id)->first();
        if (!empty($news)) {

			$image = '';
			if(file_exists(storage_path('app/public/news/' . $news->image))){
				$image = url('storage/app/public/news/'.$news->image);
			}
			$news_list = [
				'id' => $news->id,
				'name' => $news->name,
				'description' => $news->description,
				'time_ago' => $news->created_at->diffForHumans(),
				'image' => $image
			];
        } else {
			$response['status'] = 404;
		}


        $response['data'] = $news_list;
        return response($response, 200);
    }

	public function broker_list(Request $request)
    {
		$data = $request->input('data');
        $content = json_decode($data);

        $buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';

        $params = [
            'buyer_id' => $buyer_id,
        ];

        $validator = Validator::make($params, [
            'buyer_id' => 'required|exists:tbl_buyers,id',
        ]);

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $buyer_broker = AddBrokers::with('broker')->where(['user_type' => 'buyer', 'buyer_id' => $buyer_id, 'broker_type' => 'default'])->get();

        $buyer_brokers = AddBrokers::with('broker')->where(['user_type' => 'buyer', 'buyer_id' => $buyer_id, 'broker_type' => 'not_default'])->get();
        $final_merged = $buyer_broker->merge($buyer_brokers);

        $broker_list = [];
        if (!empty($final_merged)) {
            foreach($final_merged as $broker) {
                $broker_list[] = [
                    'id' => $broker->broker->id,
                    'name' => $broker->broker->name,
                    'type' => $broker->broker_type,
                ];
            }
        }

		$response['message'] = 'Broker List';
        $response['status'] = 200;
        $response['data'] = $broker_list;
        return response($response, 200);
    }

    public function broker_list_v1(Request $request)
    {
		$data = $request->input('data');
        $content = json_decode($data);

        $seller_id = isset($content->seller_id) ? $content->seller_id : '';
        $buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
        $type = isset($content->type) ? $content->type : '';

        $params = [
            'seller_id' => $seller_id,
            'buyer_id' => $buyer_id,
            'type' => $type
        ];

        $validator = Validator::make($params, [
            'seller_id' => 'required|exists:tbl_sellers,id',
            'buyer_id' => 'required|exists:tbl_buyers,id',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $final_merged = [];
        if ($type == 'seller') {
            $seller_broker = AddBrokers::with('broker')->where(['user_type' => 'seller', 'buyer_id' => $seller_id, 'broker_type' => 'default'])->get();
            $buyer_broker = AddBrokers::with('broker')->where(['user_type' => 'buyer', 'buyer_id' => $buyer_id, 'broker_type' => 'default'])->get();
            $merged = $seller_broker->merge($buyer_broker);

        } else {

            $buyer_broker = AddBrokers::with('broker')->where(['user_type' => 'buyer', 'buyer_id' => $buyer_id, 'broker_type' => 'default'])->get();
            $seller_broker = AddBrokers::with('broker')->where(['user_type' => 'seller', 'buyer_id' => $seller_id, 'broker_type' => 'default'])->get();
            $merged = $buyer_broker->merge($seller_broker);

        }

        $buyer_brokers = AddBrokers::with('broker')->where(['user_type' => 'buyer', 'buyer_id' => $buyer_id, 'broker_type' => 'not_default'])->get();
        $final_merged = $merged->merge($buyer_brokers);

        $broker_list = [];
        if (!empty($final_merged)) {
            foreach($final_merged as $broker) {
                $broker_list[] = [
                    'id' => $broker->broker->id,
                    'name' => $broker->broker->name,
                    'type' => $broker->broker_type,
                ];
            }
        }

		$response['message'] = 'Broker List';
        $response['status'] = 200;
        $response['data'] = $broker_list;
        return response($response, 200);
    }

    public function plan_list(Request $request) {
        $plan_detail = Plan::get();

        $response['message'] = 'Plan List';
        $response['status'] = 200;
        $response['data'] = $plan_detail;
        return response($response, 200);
    }

    public function addUserPlan(Request $request) {

        $data = $request->input('data');
        $content = json_decode($data);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $user_type = isset($content->user_type) ? $content->user_type : '';
        $plan_id = isset($content->plan_id) ? $content->plan_id : '';

        $params = [
            'plan_id' => $plan_id,
            'user_id' => $user_id,
            'user_type' => $user_type
        ];

        if ($user_type == 'seller') {

            $validator = Validator::make($params, [
                'user_id' => 'required|exists:tbl_sellers,id',
                'plan_id' => 'required|exists:tbl_plans,id',
                'user_type' => 'required',
            ]);
        } else {
            $validator = Validator::make($params, [
                'user_id' => 'required|exists:tbl_buyers,id',
                'plan_id' => 'required|exists:tbl_plans,id',
                'user_type' => 'required',
            ]);
        }

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $date = Carbon::now();

        UserPlan::create([
            'user_id' => $user_id,
            'user_type' => $user_type,
            'plan_id' => $plan_id,
            'status' => 1,
            'purchase_date' => date('Y-m-d'),
            'expiry_date' => $date
        ]);

        $plans = Plan::where('id',$plan_id)->first();
        if ($user_type == 'seller') {
            $sellers = Sellers::where('id',$user_id)->first();
            $sellers->wallet_amount = $sellers->wallet_amount + $plans->price;
            $sellers->save();
        }else{
            $buyers = Buyers::where('id',$user_id)->first();
            $buyers->wallet_amount = $buyers->wallet_amount + $plans->price;
            $buyers->save();
        }

        $response['message'] = 'Plan added successfully!';
        $response['status'] = 200;
        $response['data'] = (object)[];
        return response($response, 200);
    }

    public function transaction_history(Request $request) {

        $data = $request->input('data');
        $content = json_decode($data);

        $user_id = isset($content->user_id) ? $content->user_id : '';
        $user_type = isset($content->user_type) ? $content->user_type : '';

        $params = [
            'user_id' => $user_id,
            'user_type' => $user_type
        ];

        if ($user_type == 'seller') {

            $validator = Validator::make($params, [
                'user_id' => 'required|exists:tbl_sellers,id',
                'user_type' => 'required',
            ]);

            $users = Sellers::select('wallet_amount')->where('id',$user_id)->first();
        } else {
            $validator = Validator::make($params, [
                'user_id' => 'required|exists:tbl_buyers,id',
                'user_type' => 'required',
            ]);
            $users = Buyers::select('wallet_amount')->where('id',$user_id)->first();
        }

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] = $validator->errors()->first();
            return response($response, 200);
        }

        $transaction = Transactions::where('user_id',$user_id)->where('user_type',$user_type)->get();

        $trans_data = [];
        if(!empty($transaction) && count($transaction) > 0)
        {
            foreach($transaction as $value){
                $trans_data[] = [
                    'type' => $value->type,
                    'amount' => $value->amount,
                    'message' => $value->message,
                ];
            }
        }

        $data = [
            'wallet_amount' => $users->wallet_amount,
            'transaction_history' => $trans_data,
        ];

        $response['message'] = 'Transaction history';
        $response['status'] = 200;
        $response['data'] = $data;
        return response($response, 200);
    }
}
