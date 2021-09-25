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
use Illuminate\Validation\Rule;
class ProfileController extends Controller
{
    public function edit_profile_broker(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');	
		$content = json_decode($data);

		$id = isset($content->id) ? $content->id : '';
		$device_type = isset($content->device_type) ? $content->device_type : '';
		$name = isset($content->name) ? $content->name : '';
		$name_of_contact_person = isset($content->name_of_contact_person) ? $content->name_of_contact_person : '';
		$mobile_number = isset($content->mobile_number) ? $content->mobile_number : '';
		$mobile_number_2 = isset($content->mobile_number_2) ? $content->mobile_number_2 : '';
		$gst_no = isset($content->gst_no) ? $content->gst_no : '';
		$address = isset($content->address) ? $content->address : '';
		$email = isset($content->email) ? $content->email : '';
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
            'mobile_number' => [
		        Rule::unique('tbl_brokers')->ignore($id),
		    ],
		    'mobile_number_2' => [
		        Rule::unique('tbl_brokers')->ignore($id),
		    ],
            'email' => [
		        Rule::unique('tbl_brokers')->ignore($id),
		    ],
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

    	$broker = Brokers::where('id',$id)->first();
    	if(!empty($broker)){
    		$broker->name = (empty($name))?$broker->name:$name;
	    	$broker->address = (empty($address))?$broker->address:$address;
	    	$broker->email = (empty($email))?$broker->email:$email;
			$broker->mobile_number= (empty($mobile_number))?$broker->mobile_number:$mobile_number;
			$broker->mobile_number_2= (empty($mobile_number_2))?$broker->mobile_number_2:$mobile_number_2;
			$broker->header_image = $header_image_name;
			$broker->stamp_image = $stamp_image_name;
			$broker->website = (empty($website))?$broker->website:$website;
			$broker->save();

			$user_details = UserDetails::where(['user_id'=>$id,'user_type'=>'broker'])->first();
			if(!empty($user_details)){
				$user_details->user_type = (empty($user_type))?$user_details->user_type:$user_type;
				$user_details->name_of_contact_person=(empty($name_of_contact_person))?$user_details->name_of_contact_person:$name_of_contact_person;
				$user_details->gst_no=(empty($gst_no))?$user_details->gst_no:$gst_no;
				$user_details->turnover_year_one=(empty($turnover_year_one))?$user_details->turnover_year_one:$turnover_year_one;
				$user_details->turnover_date_one=(empty($turnover_date_one))?$user_details->turnover_date_one:$turnover_date_one;
				$user_details->turnover_year_two=(empty($turnover_year_two))?$user_details->turnover_year_two:$turnover_year_two;
				$user_details->turnover_date_two=(empty($turnover_date_two))?$user_details->turnover_date_two:$turnover_date_two;
				$user_details->turnover_year_three=(empty($turnover_year_three))?$user_details->turnover_year_three:$turnover_year_three;
				$user_details->turnover_date_three=(empty($turnover_date_three))?$user_details->turnover_date_three:$turnover_date_three;
				$user_details->country_id=(empty($country_id))?$user_details->country_id:$country_id;
				$user_details->state_id=(empty($state_id))?$user_details->state_id:$state_id;
				$user_details->city_id=(empty($city_id))?$user_details->city_id:$city_id;
				$user_details->station_id=(empty($station_id))?$user_details->station_id:$station_id;
				$user_details->save();
			}

			$response['status'] = 200; 
			$response['message'] = 'Profile has been successfully updated.';
    	}else{
    		$response['status'] = 404; 
    	}
	
        return response($response, 200);
    }
}
