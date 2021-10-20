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
use App\Models\Buyers;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Validator;
use Storage;
use Image;
use Illuminate\Validation\Rule;
class ProfileController extends Controller
{
    public function edit_profile(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$id = isset($content->id) ? $content->id : '';
		$seller_buyer_type = isset($content->seller_buyer_type) ? $content->seller_buyer_type : '';
		$name = isset($content->name) ? $content->name : '';
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
		$device_type = isset($content->device_type) ? $content->device_type : '';

		$params = [
			'mobile_number' => $mobile_number,
			'email' => $email,
		];

		$validator = Validator::make($params, [
            'mobile_number' => [
		        Rule::unique('tbl_sellers')->ignore($id),
		    ],
            'email' => [
		        Rule::unique('tbl_sellers')->ignore($id),
		    ],
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }
	    	$buyer = Sellers::where('id',$id)->first();
	    	if(!empty($buyer)){

	    		$image_name = '';
				if ($request->hasFile('profile_image')) {
					$image = $request->file('profile_image');
					$image_name = time() . '.' . $image->getClientOriginalExtension();
					$img = Image::make($image->getRealPath());
					$img->stream(); // <-- Key point
					Storage::disk('public')->put('seller/profile/' . $image_name, $img, 'public');
				}
	    		$buyer->name = (empty($name))?$buyer->name:$name;
				$buyer->address = (empty($address))?$buyer->address:$address;
				$buyer->mobile_number= (empty($mobile_number))?$buyer->mobile_number:$mobile_number;
				$buyer->email=(empty($email))?$buyer->email:$email;
				$buyer->image = $image_name;
				$buyer->save();

				$user_details = UserDetails::where(['user_id'=>$id,'user_type'=>'seller'])->first();
				if(!empty($user_details)){
					$user_details->user_type = (empty($user_type))?$user_details->user_type:$user_type;
					$user_details->seller_buyer_type = (empty($seller_buyer_type))?$user_details->seller_buyer_type:$seller_buyer_type;
					$user_details->name_of_contact_person=(empty($name_of_contact_person))?$user_details->name_of_contact_person:$name_of_contact_person;
					$user_details->business_type=(empty($business_type))?$user_details->business_type:$business_type;
					$user_details->registration_no=(empty($registration_no))?$user_details->registration_no:$registration_no;
					$user_details->registration_date=(empty($registration_date))?$user_details->registration_date:$registration_date;
					$user_details->registration_as_msme=(empty($registration_as_msme))?$user_details->registration_as_msme:$registration_as_msme;
					$user_details->turnover_year_one=(empty($turnover_year_one))?$user_details->turnover_year_one:$turnover_year_one;
					$user_details->turnover_date_one=(empty($turnover_date_one))?$user_details->turnover_date_one:$turnover_date_one;
					$user_details->turnover_year_two=(empty($turnover_year_two))?$user_details->turnover_year_two:$turnover_year_two;
					$user_details->turnover_date_two=(empty($turnover_date_two))?$user_details->turnover_date_two:$turnover_date_two;
					$user_details->turnover_year_three=(empty($turnover_year_three))?$user_details->turnover_year_three:$turnover_year_three;
					$user_details->turnover_date_three=(empty($turnover_date_three))?$user_details->turnover_date_three:$turnover_date_three;
					$user_details->oper_in_cotton_trade=(empty($oper_in_cotton_trade))?$user_details->oper_in_cotton_trade:$oper_in_cotton_trade;
					$user_details->gst_no=(empty($gst_no))?$user_details->gst_no:$gst_no;
					$user_details->pan_no_of_buyer=(empty($pan_no_of_buyer))?$user_details->pan_no_of_buyer:$pan_no_of_buyer;
					$user_details->country_id=(empty($country_id))?$user_details->country_id:$country_id;
					$user_details->state_id=(empty($state_id))?$user_details->state_id:$state_id;
					$user_details->city_id=(empty($city_id))?$user_details->city_id:$city_id;
					$user_details->station_id=(empty($station_id))?$user_details->station_id:$station_id;
					$user_details->save();
				}

				$bank_details = BankDetails::where(['user_id'=>$id,'user_type'=>'seller'])->first();
                
				if(!empty($bank_details)){
					$bank_details->user_type = 'seller';
					$bank_details->bank_name =(empty($bank_name))?$bank_details->bank_name:$bank_name;
					$bank_details->account_holder_name = (empty($account_holder_name))?$bank_details->account_holder_name:$account_holder_name;
					$bank_details->branch_address = (empty($branch_address))?$bank_details->branch_address:$branch_address;
					$bank_details->ifsc_code = (empty($ifsc_code))?$bank_details->ifsc_code:$ifsc_code;
					$bank_details->save();
				}
				$response['status'] = 200;
				$response['message'] = 'Profile has been successfully updated.';
			}else{
				$response['status'] = 404;
			}
        	return response($response, 200);
    }
}
