<?php

namespace App\Http\Controllers\Api\product;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\ProductAttribute;
use App\Models\ProductAttributeValues;
use App\Models\Post;
use App\Models\Notification;
use App\Models\SelectionSellerBuyer;
use App\Models\Country;
use App\Models\State;
use App\Models\City;
use App\Models\Station;
use App\Models\Brokers;
use App\Models\Sellers;
use App\Models\Buyers;
use App\Models\UserDetails;
use App\Models\PostDetails;
use App\Models\NotificatonDetails;
use App\Models\TransmitCondition;
use App\Models\PaymentCondition;
use App\Models\Lab;
use App\Models\Negotiation;
use App\Models\Settings;
//use App\Models\Pdf;
use App\Models\DealPdf;
use App\Models\WithoutNegotiationMakeDeal;
use App\Models\NegotiationComplete;
use App\Models\NegotiationLog;
use App\Models\NegotiationHistory;
use App\Models\SubjectTo;
use App\Models\DeviceDetails;
use App\Models\DealQueue;
use App\Models\NegotiationDebitNote;
use App\Helper\CommonHelper;
use Validator;
use Carbon\Carbon;
use PDF;
use Storage;
use File;
use Image;
use App\Helper\NotificationHelper;
use App\Models\AddBrokers;
use App\Models\Transactions;
use App\Models\User;
use App\Events\NotificationSeller;
use App\Events\NotificationBuyer;
use App\Events\NegotiationSeller;
use App\Events\NegotiationBuyer;
use App\Events\NegotiationMultipleSeller;
use App\Events\NegotiationMultipleBuyer;
use App\Events\MakedealBuyer;
use App\Events\MakedealSeller;
use Mail;

class ProductController extends Controller
{
    public function product_list(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$product_list = [];
		$product = Product::where('is_delete',0)->get();
		if(count($product)>0){
			foreach ($product as $value) {

				$product_list[] =[
					'id' => $value->id,
					'name' => $value->name
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Product List';
			$response['data'] = $product_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

     public function product_attribute_list(Request $request){
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';


		$params = [
			'product_id' => $product_id,
		];

		$validator = Validator::make($params, [
            'product_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$product_attribute_list = [];
		$product = ProductAttribute::where(['product_id'=>$product_id,'is_delete'=>0])->get();
		if(count($product)>0){
			foreach ($product as $value) {
				$product_attribute_values_list = [];
				$product_attribute_values = ProductAttributeValues::where(['product_attribute_id'=>$value->id,'is_delete'=>0])->get();
				foreach ($product_attribute_values as $val) {
					$product_attribute_values_list[] =[
						'value' => $val->id,
						/*'value' => $val->product_attribute_id,*/
						'label' => $val->value,
						'is_required' => 0,
					];
				}
				$product_attribute_list[] =[
					'id' => $value->id,
					'product_id' => $value->product_id,
					'label' => $value->label,
					'value' => $product_attribute_values_list
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Product Attribute List';
			$response['data'] = $product_attribute_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function post_to_sell(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$product_id = isset($content->product_id) ? $content->product_id : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$address = isset($content->address) ? $content->address : '';
		$attribute_array =  isset($content->attribute_array) ? $content->attribute_array : '';

		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'product_id' => $product_id,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'address' => $address,
			'attribute_array' => $attribute_array,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'product_id' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'address' => 'required',
            'attribute_array' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        // $check_data = CommonHelper::check_user_amount($seller_buyer_id,'seller');

        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$response['data'] = (object)[];

		$post_to_sell = new Post();
		$post_to_sell->seller_buyer_id = $seller_buyer_id;
		$post_to_sell->user_type = 'seller';
		$post_to_sell->status = 'active';
		$post_to_sell->product_id = $product_id;
		$post_to_sell->price = $price;
		$post_to_sell->no_of_bales = $no_of_bales;
		$post_to_sell->remain_bales = $no_of_bales;
		$post_to_sell->address = $address;
		if($post_to_sell->save()){

			if(!empty($attribute_array)){
				foreach ($attribute_array as $value) {
					$post_detail = new PostDetails();
					$post_detail->post_id = $post_to_sell->id;
					$post_detail->attribute = $value->attribute;
					$post_detail->attribute_value = $value->attribute_value;
					$post_detail->save();
 				}
			}
			$response['status'] = 200;
			$response['message'] = 'Post Added Sucessfully!!';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function post_to_sell_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';


		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$post_to_sell_list = [];

		$post = Post::where(['seller_buyer_id'=>$seller_id,'is_active'=>0,'user_type'=>'seller'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($post)>0){
			foreach ($post as $value) {
					$product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($product)){
						$product_name = $product->name;
					}
					$attribute_array = [];
					$attribute = PostDetails::where('post_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'post_id' => $val->post_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$post_to_sell_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'address' => $value->address,
					'date' => $created_at,
					'attribute_array' => $attribute_array
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Post to sell list';
			$response['data'] = $post_to_sell_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function notification_to_buy(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$product_id = isset($content->product_id) ? $content->product_id : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';
		$buy_for = isset($content->buy_for) ? $content->buy_for : '';
		$spinning_meal_name = isset($content->spinning_meal_name) ? $content->spinning_meal_name : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';
		$buyers = isset($content->buyers) ? $content->buyers : '';
		$brokers = isset($content->brokers) ? $content->brokers : '';
		$attribute_array =  isset($content->attribute_array) ? $content->attribute_array : '';

		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'product_id' => $product_id,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'd_e' => $d_e,
			'country_id' => $country_id,
			'state_id' => $state_id,
			'city_id' => $city_id,
			'station_id' => $station_id,
			'buyers' => $buyers,
			'brokers' => $brokers,
			'attribute_array' => $attribute_array,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'product_id' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'd_e' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'station_id' => 'required',
            'buyers' => 'required',
            'brokers' => 'required',
            'attribute_array' => 'required',
        ]);

        if ($validator->fails()) {
            $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

	    if($d_e == "Domestic"){
	    	$params = [
				'buy_for' => $buy_for,
			];

			$validator = Validator::make($params, [
	            'buy_for' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }
	    if($buy_for == "Other"){
	    	$params = [
				'spinning_meal_name' => $spinning_meal_name,
			];

			$validator = Validator::make($params, [
	            'spinning_meal_name' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }

        // $check_data = CommonHelper::check_user_amount($seller_buyer_id,'seller');
        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$notification = new Notification();
		$notification->seller_buyer_id = $seller_buyer_id;
		$notification->user_type = 'seller';
		$notification->status = 'active';
		$notification->product_id = $product_id;
		$notification->price = $price;
		$notification->no_of_bales = $no_of_bales;
		$notification->remain_bales = $no_of_bales;
		$notification->d_e = $d_e;
		$notification->buy_for = $buy_for;
		$notification->spinning_meal_name = $spinning_meal_name;
		$notification->country_id = $country_id;
		$notification->state_id = $state_id;
		$notification->city_id = $city_id;
		$notification->station_id = $station_id;
		if($notification->save())
		{
			if(!empty($attribute_array)){
				foreach ($attribute_array as $value) {
					$post_detail = new NotificatonDetails();
					$post_detail->notification_id = $notification->id;
					$post_detail->attribute = $value->attribute;
					$post_detail->attribute_value = $value->attribute_value;
					$post_detail->save();
 				}
			}

			$send_by = '';
			$seller = Sellers::where('id',$seller_buyer_id)->first();
			if(!empty($seller)){
				$send_by = $seller->name;
			}
            $fcm_token = [];
			if(!empty($buyers)){
				foreach ($buyers as $value) {
					$select = new SelectionSellerBuyer();
					$select->user_type = 'buyer';
					$select->seller_buyer_id = $value;
					$select->notification_id = $notification->id;
					foreach ($brokers as $val_broker) {
						$select->broker_id = $val_broker->id;
						$select->broker_type = $val_broker->type;
					}
					$select->save();

					//event

					$product_name = '';
					$product = Product::where('id',$notification->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$country_name = '';
					$country = Country::where('id',$country_id)->first();
					if(!empty($country)){
						$country_name= $country->name;
					}
					$state_name = '';
					$state = State::where('id',$state_id)->first();
					if(!empty($state)){
						$state_name= $state->name;
					}
					$city_name = '';
					$city = City::where('id',$city_id)->first();
					if(!empty($city)){
						$city_name= $city->name;
					}
					$station_name = '';
					$station = Station::where('id',$station_id)->first();
					if(!empty($station)){
						$station_name= $station->name;
					}
					$attribute_array = [];
					$attribute = NotificatonDetails::where('notification_id', $notification->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'notification_id' => $val->notification_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$notificationBuyerData = new Notification();
					$notificationBuyerData->buyer_id = $value;
					$notificationBuyerData->notification_id = $notification->id;
					$notificationBuyerData->status = $notification->status;
					$notificationBuyerData->seller_buyer_id = $seller_buyer_id;
					$notificationBuyerData->send_by = $send_by;
					$notificationBuyerData->user_type = $notification->user_type;
					$notificationBuyerData->product_id = $notification->product_id;
					$notificationBuyerData->product_name = $product_name;
					$notificationBuyerData->no_of_bales = $notification->no_of_bales;
					$notificationBuyerData->price = $notification->price;
					$notificationBuyerData->d_e = $notification->d_e;
					$notificationBuyerData->buy_for = $notification->buy_for;
					$notificationBuyerData->spinning_meal_name = $notification->spinning_meal_name;
					$notificationBuyerData->country = $country_name;
					$notificationBuyerData->state = $state_name;
					$notificationBuyerData->city = $city_name;
					$notificationBuyerData->station = $station_name;
					$notificationBuyerData->attribute_array = $attribute_array;

					event(new NotificationBuyer($notificationBuyerData));
					//event

                    $buyer_data = DeviceDetails::select('fcm_token')->where('user_type','buyer')->where('user_id',$value)->first();
					if (!empty($buyer_data->fcm_token)) {
						// array_push($fcm_token,$buyer_data->fcm_token);

                        $json_array = [
                            "registration_ids" => [$buyer_data->fcm_token],
                            "data" => [
                                'navigateto' => 'NotificationList',
                                'sellerId' => $seller_buyer_id,
                                'post_id' => $notification->id,
                                'type' => 'notification',
								'product_name' => $product_name,
                            ],
                            "notification" => [
                                "body" => "Notification send by ".$seller->name,
                                "title" => "Sell Notification",
                                "icon" => "ic_launcher"
                            ]
                        ];
                        NotificationHelper::notification($json_array,'buyer');
					}
				}
			}

			$response['status'] = 200;
			$response['message'] = 'Notification Send Sucessfully!!';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

   public function notification_to_buy_list(Request $request)
    {
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

		$notification_to_buy_list = [];
		$buyer = SelectionSellerBuyer::where(['seller_buyer_id'=>$buyer_id,'user_type'=>'buyer'])->orderBy('id','DESC')->get();
		if(count($buyer)>0){
			foreach ($buyer as $val_buyer) {
				$notification = Notification::where(['id'=>$val_buyer->notification_id,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($notification)){
					$negotiation = Negotiation::where(['buyer_id'=>$buyer_id,'post_notification_id'=>$notification->id,'negotiation_type'=>'notification'])->get();
					if(count($negotiation) == 0){
						$name = '';
						$seller = Sellers::where('id',$notification->seller_buyer_id)->first();
						if(!empty($seller)){
							$name= $seller->name;
						}
						$product_name = '';
						$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
							if(!empty($product)){
								$product_name = $product->name;
							}
						$country_name = '';
							$country = Country::where(['id'=>$notification->country_id,'is_delete'=>1])->first();
								if(!empty($country)){
									$country_name = $country->name;
								}
							$state_name = '';
							$state = State::where(['id'=>$notification->state_id,'is_delete'=>1])->first();
								if(!empty($state)){
									$state_name = $state->name;
								}
							$city_name = '';
							$city = City::where(['id'=>$notification->city_id,'is_delete'=>1])->first();
								if(!empty($city)){
									$city_name = $city->name;
								}
								$station_name = '';
							$station = Station::where(['id'=>$notification->station_id,'is_delete'=>1])->first();
								if(!empty($station)){
									$station_name = $station->name;
								}
						$attribute_array = [];
							$attribute = NotificatonDetails::where('notification_id', $notification->id)->get();
							foreach ($attribute as $val) {
								$attribute_array[] = [
									'id' => $val->id,
									'notification_id' => $val->notification_id,
									'attribute' => $val->attribute,
									'attribute_value' => $val->attribute_value,
								];
							}
						$notification_to_buy_list[] = [
							'notification_id' => $notification->id,
							'status' => $notification->status,
							'seller_buyer_id' => $notification->seller_buyer_id,
							'send_by' => $name,
							'user_type'=>$notification->user_type,
							'product_id'=>$notification->product_id,
							'product_name' =>$product_name,
							'no_of_bales' => $notification->no_of_bales,
							'price' => $notification->price,
							'd_e' => $notification->d_e,
							'buy_for' =>$notification->buy_for,
							'spinning_meal_name' => $notification->spinning_meal_name,
							'country' => $country_name,
							'state' => $state_name,
							'city' => $city_name,
							'station' => $station_name,
							'attribute_array'=>$attribute_array
						];
						$response['status'] = 200;
						$response['message'] = 'Notification';
						$response['data'] = $notification_to_buy_list;
					}
				}
			}
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
    }
    public function post_to_buy(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$product_id = isset($content->product_id) ? $content->product_id : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';
		$buy_for = isset($content->buy_for) ? $content->buy_for : '';
		$spinning_meal_name = isset($content->spinning_meal_name) ? $content->spinning_meal_name : '';
		$attribute_array =  isset($content->attribute_array) ? $content->attribute_array : '';


		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'product_id' => $product_id,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'd_e' => $d_e,
			'attribute_array' => $attribute_array,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'product_id' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'd_e' => 'required',
            'attribute_array' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

	    if($d_e == "Domestic"){
	    	$params = [
				'buy_for' => $buy_for,
			];

			$validator = Validator::make($params, [
	            'buy_for' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }
	    if($buy_for == "Other"){
	    	$params = [
				'spinning_meal_name' => $spinning_meal_name,
			];

			$validator = Validator::make($params, [
	            'spinning_meal_name' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }

        // $check_data = CommonHelper::check_user_amount($seller_buyer_id,'buyer');

        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$post_to_buy = new Post();
		$post_to_buy->seller_buyer_id = $seller_buyer_id;
		$post_to_buy->user_type = 'buyer';
		$post_to_buy->status = 'active';
		$post_to_buy->product_id = $product_id;
		$post_to_buy->price = $price;
		$post_to_buy->no_of_bales = $no_of_bales;
		$post_to_buy->remain_bales = $no_of_bales;
		$post_to_buy->d_e = $d_e;
		$post_to_buy->buy_for = $buy_for;
		$post_to_buy->spinning_meal_name = $spinning_meal_name;
		if($post_to_buy->save()){

			if(!empty($attribute_array)){
				foreach ($attribute_array as $val) {
					$attribute = new PostDetails();
					$attribute->post_id = $post_to_buy->id;
					$attribute->attribute = $val->attribute;
					$attribute->attribute_value = $val->attribute_value;
					$attribute->save();
				}
			}
			$response['status'] = 200;
			$response['message'] = 'Post Added Sucessfully!!';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
    public function post_to_buy_list(Request $request)
    {
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

		$post_to_buy_list = [];

		$post = Post::where(['seller_buyer_id'=>$buyer_id,'is_active'=>0,'user_type'=>'buyer'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($post)>0){
			foreach ($post as $value) {
					$product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($product)){
						$product_name = $product->name;
					}
					$attribute_array = [];
					$attribute = PostDetails::where('post_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'post_id' => $val->post_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$post_to_buy_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'attribute_array' => $attribute_array
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Post to buy list';
			$response['data'] = $post_to_buy_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }
    public function notification_to_seller(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$product_id = isset($content->product_id) ? $content->product_id : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';
		$buy_for = isset($content->buy_for) ? $content->buy_for : '';
		$spinning_meal_name = isset($content->spinning_meal_name) ? $content->spinning_meal_name : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';
		$sellers = isset($content->sellers) ? $content->sellers : '';
		$attribute_array =  isset($content->attribute_array) ? $content->attribute_array : '';

		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'product_id' => $product_id,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'd_e' => $d_e,
			'country_id' => $country_id,
			'state_id' => $state_id,
			'city_id' => $city_id,
			'station_id' => $station_id,
			'sellers' => $sellers,
			'attribute_array' => $attribute_array,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'product_id' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'd_e' => 'required',
            'country_id' => 'required',
            'state_id' => 'required',
            'city_id' => 'required',
            'station_id' => 'required',
            'sellers' => 'required',
            'attribute_array' => 'required',
        ]);

		// dd($seller_buyer_id);

        if ($validator->fails()) {
	        	$response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

	     if($d_e == "Domestic"){
	    	$params = [
				'buy_for' => $buy_for,
			];

			$validator = Validator::make($params, [
	            'buy_for' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }
	    if($buy_for == "Other"){
	    	$params = [
				'spinning_meal_name' => $spinning_meal_name,
			];

			$validator = Validator::make($params, [
	            'spinning_meal_name' => 'required',
	        ]);

	        if ($validator->fails()) {
		        $response['status'] = 404;
					$response['message'] =$validator->errors()->first();
					return response($response, 200);
		    }
	    }

        // $check_data = CommonHelper::check_user_amount($seller_buyer_id,'buyer');
        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$notification = new Notification();
		$notification->seller_buyer_id = $seller_buyer_id;
		$notification->user_type = 'buyer';
		$notification->status = 'active';
		$notification->product_id = $product_id;
		$notification->price = $price;
		$notification->no_of_bales = $no_of_bales;
		$notification->remain_bales = $no_of_bales;
		$notification->d_e = $d_e;
		$notification->buy_for = $buy_for;
		$notification->spinning_meal_name = $spinning_meal_name;
		$notification->country_id = $country_id;
		$notification->state_id = $state_id;
		$notification->city_id = $city_id;
		$notification->station_id = $station_id;
		if($notification->save())
		{
			if(!empty($attribute_array)){
				foreach ($attribute_array as $val) {
					$attribute = new NotificatonDetails();
					$attribute->notification_id = $notification->id;
					$attribute->attribute = $val->attribute;
					$attribute->attribute_value = $val->attribute_value;
					$attribute->save();
				}
			}

			$send_by = '';
			$buyer_id = $seller_buyer_id;
			$buyer = Buyers::where('id',$seller_buyer_id)->first();
			if(!empty($buyer)){
				$send_by = $buyer->name;
			}

            $fcm_token = [];
			if(!empty($sellers)){
				foreach ($sellers as $value) {
					$select = new SelectionSellerBuyer();
					$select->user_type = 'seller';
					$select->seller_buyer_id = $value;
					$select->notification_id = $notification->id;
					$select->save();

					//event
					// $send_by = '';
					// $buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
					// if(!empty($buyer)){
						// $send_by = $buyer->name;
					// }
					$product_name = '';
					$product = Product::where('id',$notification->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$country_name = '';
					$country = Country::where('id',$country_id)->first();
					if(!empty($country)){
						$country_name= $country->name;
					}
					$state_name = '';
					$state = State::where('id',$state_id)->first();
					if(!empty($state)){
						$state_name= $state->name;
					}
					$city_name = '';
					$city = City::where('id',$city_id)->first();
					if(!empty($city)){
						$city_name= $city->name;
					}
					$station_name = '';
					$station = Station::where('id',$station_id)->first();
					if(!empty($station)){
						$station_name= $station->name;
					}
					$attribute_array = [];
					$attribute = NotificatonDetails::where('notification_id', $notification->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'notification_id' => $val->notification_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}

					$notificationSellerData = new Notification();
					$notificationSellerData->seller_id = $value;
					$notificationSellerData->notification_id = $notification->id;
					$notificationSellerData->status = $notification->status;
					$notificationSellerData->seller_buyer_id = $buyer_id;
					$notificationSellerData->send_by = $send_by;
					$notificationSellerData->user_type = $notification->user_type;
					$notificationSellerData->product_id = $notification->product_id;
					$notificationSellerData->product_name = $product_name;
					$notificationSellerData->no_of_bales = $notification->no_of_bales;
					$notificationSellerData->price = $notification->price;
					$notificationSellerData->d_e = $notification->d_e;
					$notificationSellerData->buy_for = $notification->buy_for;
					$notificationSellerData->spinning_meal_name = $notification->spinning_meal_name;
					$notificationSellerData->country = $country_name;
					$notificationSellerData->state = $state_name;
					$notificationSellerData->city = $city_name;
					$notificationSellerData->station = $station_name;
					$notificationSellerData->attribute_array = $attribute_array;


					event(new NotificationSeller($notificationSellerData));
					//event

                    $seller_data = DeviceDetails::select('fcm_token')->where('user_type','seller')->where('user_id',$value)->first();
                    if (!empty($seller_data->fcm_token)) {
						// array_push($fcm_token,$seller_data->fcm_token);
                        $json_array = [
                            "registration_ids" => [$seller_data->fcm_token],
                            "data" => [
                                'user_type' => 'buyer',
                                'navigateto' => 'NotificationList',
                                'sellerId' => $seller_buyer_id,
                                'post_id' => $notification->id,
                                'type' => 'notification',
                                'product_name' => $product_name,
                            ],
                            "notification" => [
                                "body" => "Notification send by ".$buyer->name,
                                "title" => "Buy Notification",
                                "icon" => "ic_launcher"
                            ]
                        ];

                        NotificationHelper::notification($json_array,'seller');
					}
				}
			}

			$response['status'] = 200;
			$response['message'] = 'Notification Send Sucessfully!!';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
    }

    public function notification_to_seller_list(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';


		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$notification_to_seller_list = [];
		$seller = SelectionSellerBuyer::where(['seller_buyer_id'=>$seller_id,'user_type'=>'seller'])->orderBy('id','DESC')->get();
		if(count($seller)>0){
			foreach ($seller as $val_buyer) {
				$notification = Notification::where(['id'=>$val_buyer->notification_id,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($notification)){
					$negotiation = Negotiation::where(['seller_id'=>$seller_id,'post_notification_id'=>$notification->id,'negotiation_type'=>'notification'])->get();
					if(count($negotiation) == 0){
						$name = '';
						$buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
						if(!empty($buyer)){
							$name= $buyer->name;
						}
						$product_name = '';
						$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
							if(!empty($product)){
								$product_name = $product->name;
							}
						$country_name = '';
							$country = Country::where(['id'=>$notification->country_id,'is_delete'=>1])->first();
								if(!empty($country)){
									$country_name = $country->name;
								}
							$state_name = '';
							$state = State::where(['id'=>$notification->state_id,'is_delete'=>1])->first();
								if(!empty($state)){
									$state_name = $state->name;
								}
							$city_name = '';
							$city = City::where(['id'=>$notification->city_id,'is_delete'=>1])->first();
								if(!empty($city)){
									$city_name = $city->name;
								}
								$station_name = '';
							$station = Station::where(['id'=>$notification->station_id,'is_delete'=>1])->first();
								if(!empty($station)){
									$station_name = $station->name;
								}
						$attribute_array = [];
							$attribute = NotificatonDetails::where('notification_id', $notification->id)->get();
							foreach ($attribute as $val) {
								$attribute_array[] = [
									'id' => $val->id,
									'notification_id' => $val->notification_id,
									'attribute' => $val->attribute,
									'attribute_value' => $val->attribute_value,
								];
							}
						$notification_to_seller_list[] = [
							'notification_id' => $notification->id,
							'status' => $notification->status,
							'seller_buyer_id' => $notification->seller_buyer_id,
							'send_by' => $name,
							'user_type'=>$notification->user_type,
							'product_id'=>$notification->product_id,
							'product_name' =>$product_name,
							'no_of_bales' => $notification->no_of_bales,
							'price' => $notification->price,
							'd_e' => $notification->d_e,
							'buy_for' =>$notification->buy_for,
							'spinning_meal_name' => $notification->spinning_meal_name,
							'country' => $country_name,
							'state' => $state_name,
							'city' => $city_name,
							'station' => $station_name,
							'attribute_array'=>$attribute_array
						];
						$response['status'] = 200;
						$response['message'] = 'Notification';
						$response['data'] = $notification_to_seller_list;
					}
				}
			}
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
    }

    public function search_to_sell(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$attribute_array = isset($content->attribute_array) ? $content->attribute_array : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';
		$seller_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';

        // $check_data = CommonHelper::check_user_amount($seller_id,'seller');

        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];
		if(!empty($attribute_array)){
			foreach ($attribute_array as $val) {
				//$search = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->get();
				$search = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute])->whereBetween('tbl_post_details.attribute_value',[$val->from,$val->to])->get();

                // dd($search);
                if(count($search)>0){
					$seller_id = [];
					foreach ($search as $value) {
                        $post_arr[] = $value->id;
                        $country_arr[] = $value->user_detail->country->id;
                        $state_arr[] = $value->user_detail->state->id;
                        $city_arr[] = $value->user_detail->city->id;
                        $station_arr[] = $value->user_detail->station->id;
					}
				}
			}
		}

        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);
			$search_array = [];
            foreach($country_arr as $country_val) {
                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.buyer')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name', 'tbl_post.remain_bales')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {
                                            dd($station_result);
                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->buyer->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'no_of_bales' => $station_result_val->remain_bales,
                                                    'remaining_bales' => $station_result_val->remain_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to sell';
        $response['data'] = $final_arr;

		return response($response, 200);
    }

    public function post_details(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$type = isset($content->type) ? $content->type : '';


		$params = [
			'post_notification_id' => $post_notification_id,
			'type' => $type,
		];

		$validator = Validator::make($params, [
            'post_notification_id' => 'required',
            'type' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		if($type == "post"){
			$post_to_sell_list = [];
			$post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
			if(!empty($post)){
				$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
						$product_name = '';
						if(!empty($product)){
							$product_name = $product->name;
						}
						$attribute_array = [];
						$attribute = PostDetails::where('post_id',$post->id)->get();
						foreach ($attribute as $val) {
							$attribute_array[] = [
								'id' => $val->id,
								'post_id' => $val->post_id,
								'attribute' => $val->attribute,
								'attribute_value' => $val->attribute_value,
							];
						}
						$created_at = date('d-m-Y, h:i a', strtotime($post->created_at));
						$post_to_sell_list[] = [
						'id' => $post->id,
						'seller_buyer_id' => $post->seller_buyer_id,
						'user_type' => $post->user_type,
						'product_id' => $post->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $post->no_of_bales,
						'price' => $post->price,
						'address' => $post->address,
						'date' => $created_at,
						'attribute_array' => $attribute_array
					];
	        		$response['status'] = 200;
	        		$response['message'] = 'Post details';
	        		$response['data'] = $post_to_sell_list;
			}else{
				$response['status'] = 404;
			}
		}else{
			$notification_to_sell_list = [];
			$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
			if(!empty($notification)){
				$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
						$product_name = '';
						if(!empty($product)){
							$product_name = $product->name;
						}
						$attribute_array = [];
						$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
						foreach ($attribute as $val) {
							$attribute_array[] = [
								'id' => $val->id,
								'notification_id' => $val->notification_id,
								'attribute' => $val->attribute,
								'attribute_value' => $val->attribute_value,
							];
						}
						$created_at = date('d-m-Y, h:i a', strtotime($notification->created_at));
						$notification_to_sell_list[] = [
						'id' => $notification->id,
						'seller_buyer_id' => $notification->seller_buyer_id,
						'user_type' => $notification->user_type,
						'product_id' => $notification->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $notification->no_of_bales,
						'price' => $notification->price,
						'address' => $notification->address,
						'date' => $created_at,
						'attribute_array' => $attribute_array
					];
	        		$response['status'] = 200;
	        		$response['message'] = 'Notification details';
	        		$response['data'] = $notification_to_sell_list;
			}else{
				$response['status'] = 404;
			}
		}

		return response($response, 200);
    }

     public function search_to_buy(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$attribute_array = isset($content->attribute_array) ? $content->attribute_array : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';

        // $check_data = CommonHelper::check_user_amount($buyer_id,'buyer');

        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];
		if(!empty($attribute_array)){
			foreach ($attribute_array as $val) {
				$search = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->orWhere('tbl_post.no_of_bales',$no_of_bales)->get();

                // dd($search);
                if(count($search)>0){
					$seller_id = [];
					foreach ($search as $value) {
                        $post_arr[] = $value->id;
                        $country_arr[] = $value->user_detail->country->id;
                        $state_arr[] = $value->user_detail->state->id;
                        $city_arr[] = $value->user_detail->city->id;
                        $station_arr[] = $value->user_detail->station->id;
					}
				}
			}
		}

        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);

			$search_array = [];
            foreach($country_arr as $country_val) {

                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.seller')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {

                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->seller->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to buy';
        $response['data'] = $final_arr;

		return response($response, 200);

	}
	public function cancel_post(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$post_id = isset($content->post_id) ? $content->post_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';


		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'post_id' => $post_id,
			'user_type' => $user_type,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'post_id' => 'required',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$cancel_post = Post::where(['id'=>$post_id,'seller_buyer_id'=>$seller_buyer_id,'user_type'=>$user_type])->first();
		if(!empty($cancel_post)){
			$cancel_post->status = 'cancel';
			$cancel_post->updated_at = date('Y-m-d H:i:s');
			$cancel_post->save();
			$response['status'] = 200;
			$response['message'] = 'cancel post';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function cancel_notification(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$notification_id = isset($content->notification_id) ? $content->notification_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';


		$params = [
			'seller_buyer_id' => $seller_buyer_id,
			'notification_id' => $notification_id,
			'user_type' => $user_type,
		];

		$validator = Validator::make($params, [
            'seller_buyer_id' => 'required',
            'notification_id' => 'required',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$cancel_notification = Notification::where(['id'=>$notification_id,'seller_buyer_id'=>$seller_buyer_id,'user_type'=>$user_type])->first();
		if(!empty($cancel_notification)){
			$cancel_notification->status = 'cancel';
			$cancel_notification->updated_at = date('Y-m-d H:i:s');
			$cancel_notification->save();
			$response['status'] = 200;
			$response['message'] = 'cancel notification';
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function transmit_payment_lab_list(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$transmit_payment_lab_array = [];
		$transmit_condition = TransmitCondition::where('is_delete',1)->get();
		$transmit = [];
		if(count($transmit_condition)>0){
			foreach ($transmit_condition as $value) {
				$transmit[] = [
					'id' => $value->id,
					'name' => $value->name,
					'is_dispatch' => $value->is_dispatch,
				];
			}
		}
		$payment = [];
		$payment_condition = PaymentCondition::where('is_delete',1)->get();
		if(count($payment_condition)>0){
			foreach ($payment_condition as $value) {
				$payment[] = [
					'id' => $value->id,
					'name' => $value->name,
				];
			}
		}
		$lab_list = [];
		$lab = Lab::where('is_delete',1)->get();
		if(count($lab)>0){
			foreach ($lab as $value) {
				$lab_list[] = [
					'id' => $value->id,
					'name' => $value->name,
				];
			}
		}

		$header = [
			[
				'id' => 1,
				'name' => 'Subject To',
			],
			[
				'id' => 2,
				'name' => 'Confirm To',
			]
		];

		$transmit_payment_lab_array[] = [
			'transmit_condition' => $transmit,
			'payment_condition' => $payment,
			'lab_list' => $lab_list,
			'header' => $header,
		];

		$response['status'] = 200;
		$response['data'] = $transmit_payment_lab_array;
		return response($response, 200);
	}
	public function transmit_condition(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$transmit = [];
		$transmit_condition = TransmitCondition::where('is_delete',1)->get();
		if(count($transmit_condition)>0){
			foreach ($transmit_condition as $value) {
				$transmit[] = [
					'id' => $value->id,
					'name' => $value->name,
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Transmit Condition';
			$response['data'] = $transmit;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function payment_condition(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$payment = [];
		$payment_condition = PaymentCondition::where('is_delete',1)->get();
		if(count($payment_condition)>0){
			foreach ($payment_condition as $value) {
				$payment[] = [
					'id' => $value->id,
					'name' => $value->name,
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Payment Condition';
			$response['data'] = $payment;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function lab_list(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$lab_list = [];
		$lab = Lab::where('is_delete',1)->get();
		if(count($lab)>0){
			foreach ($lab as $value) {
				$lab_list[] = [
					'id' => $value->id,
					'name' => $value->name,
				];
			}
			$response['status'] = 200;
			$response['message'] = 'Lab List';
			$response['data'] = $lab_list;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function negotiation(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$negotiation_type = isset($content->negotiation_type) ? $content->negotiation_type : '';
		$negotiation_by = isset($content->negotiation_by) ? $content->negotiation_by : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$payment_condition = isset($content->payment_condition) ? $content->payment_condition : '';
		$transmit_condition = isset($content->transmit_condition) ? $content->transmit_condition : '';
		$lab = isset($content->lab) ? $content->lab : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';
		$header = isset($content->header) ? $content->header : '';
		$notes = isset($content->notes) ? $content->notes : '';


		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
			'negotiation_type' => $negotiation_type,
			'negotiation_by' => $negotiation_by,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'payment_condition' => $payment_condition,
			'transmit_condition' => $transmit_condition,
			'lab' => $lab,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required|exists:tbl_sellers,id',
            'buyer_id' => 'required|exists:tbl_buyers,id',
            'post_notification_id' => 'required',
            'negotiation_type' => 'required',
            'negotiation_by' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'payment_condition' => 'required|exists:tbl_payment_condition,id',
            'transmit_condition' => 'required|exists:tbl_transmit_condition,id',
            'lab' => 'required|exists:tbl_lab,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }

	    if($negotiation_type == "post"){
	    	 $post_remain = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
		   	if($no_of_bales > $post_remain->no_of_bales){
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}elseif ($no_of_bales > $post_remain->remain_bales) {
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}else{
		    	$negotiation = new Negotiation();
				$negotiation->seller_id = $seller_id;
				$negotiation->buyer_id = $buyer_id;
				$negotiation->post_notification_id = $post_notification_id;
				$negotiation->negotiation_type = $negotiation_type;
				$negotiation->negotiation_by = $negotiation_by;
				$negotia = Negotiation::where(['post_notification_id'=>$post_notification_id,'is_deal'=>0,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id])->get();
				if(count($negotia)==0){
					$post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
					if(!empty($post)){
						$negotiation->prev_price = $post->price;
						$negotiation->prev_no_of_bales = $post->no_of_bales;
					}else{
						$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
						if(!empty($notification)){
							$negotiation->prev_price = $notification->price;
							$negotiation->prev_no_of_bales = $notification->no_of_bales;
						}
					}
				}else{
					foreach ($negotia as $value) {
						$negotiation->prev_price = $value->current_price;
						$negotiation->prev_no_of_bales = $value->current_no_of_bales;
					}
				}
				$negotiation->current_price = $price;
				$negotiation->current_no_of_bales = $no_of_bales;
				$negotiation->payment_condition = $payment_condition;
				$negotiation->transmit_condition = $transmit_condition;
				$negotiation->lab = $lab;
				if (!empty($broker_id)) {
					$negotiation->broker_id = $broker_id;
				}
				if (!empty($notes)) {
					$negotiation->notes = $notes;
				}
				if (!empty($header)) {
					$negotiation->header = $header;
				}

				if($negotiation->save()){
					$response['status'] = 200;
					$response['message'] = 'Negotiation';
				}else{
					$response['status'] = 404;
				}
	    	}
	    }
	 	if($negotiation_type == "notification"){
	    	$notification_remain = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
		   	if($no_of_bales > $notification_remain->no_of_bales){
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}elseif ($no_of_bales > $notification_remain->remain_bales) {
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}else{
		    	$negotiation = new Negotiation();
				$negotiation->seller_id = $seller_id;
				$negotiation->buyer_id = $buyer_id;
				$negotiation->post_notification_id = $post_notification_id;
				$negotiation->negotiation_type = $negotiation_type;
				$negotiation->negotiation_by = $negotiation_by;
				$negotia = Negotiation::where(['post_notification_id'=>$post_notification_id,'is_deal'=>0,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id])->get();
				if(count($negotia)==0){
					$post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
					if(!empty($post)){
						$negotiation->prev_price = $post->price;
						$negotiation->prev_no_of_bales = $post->no_of_bales;
					}else{
						$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
						if(!empty($notification)){
							$negotiation->prev_price = $notification->price;
							$negotiation->prev_no_of_bales = $notification->no_of_bales;
						}
					}
				}else{
					foreach ($negotia as $value) {
						$negotiation->prev_price = $value->current_price;
						$negotiation->prev_no_of_bales = $value->current_no_of_bales;
					}
				}
				$negotiation->current_price = $price;
				$negotiation->current_no_of_bales = $no_of_bales;
				$negotiation->payment_condition = $payment_condition;
				$negotiation->transmit_condition = $transmit_condition;
				$negotiation->lab = $lab;
				if (!empty($broker_id)) {
					$negotiation->broker_id = $broker_id;
				}
				if (!empty($notes)) {
					$negotiation->notes = $notes;
				}
				if (!empty($header)) {
					$negotiation->header = $header;
				}

				if($negotiation->save()){
					$response['status'] = 200;
					$response['message'] = 'Negotiation';
				}else{
					$response['status'] = 404;
				}
	    	}
	    }

        $fcm_token = "";
        $user = [];
        $data = [];
        if ($negotiation_by == "seller") {
            $user_type = "buyer";
            $data = [
                'navigateto' => 'DealDetails',
                'buyerId' => $buyer_id,
                'post_id' => $post_notification_id,
                'type' => $negotiation_type,
            ];
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$buyer_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Sellers::select('name')->where('id',$seller_id)->first();
        }else if($negotiation_by == 'buyer'){
            $user_type = "seller";
            $data = [
                'navigateto' => 'DealDetails',
                'sellerId' => $seller_id,
                'post_id' => $post_notification_id,
                'type' => $negotiation_type,
            ];
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$seller_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Buyers::select('name')->where('id',$buyer_id)->first();
        }

        if (!empty($fcm_token)) {
            $json_array = [
                "registration_ids" => [$fcm_token],
                "data" => $data,
                "notification" => [
                    "body" => "Notification send by ".$user->name,
                    "title" => "Buy Notification",
                    "icon" => "ic_launcher"
                ]
            ];
            NotificationHelper::notification($json_array,$user_type);
        }

		return response($response, 200);
	}
	public function negotiation_list(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';


		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$negotiation_array = [];
		$ids = [];
		$buyer_ids = [];
		$negotiation = Negotiation::where(['seller_id'=>$seller_id,'is_deal'=>0])->orderBy('id','DESC')->get();
		if(count($negotiation)>0){
			foreach ($negotiation as $value) {
				array_push($ids, $value->id);
				array_push($buyer_ids, $value->buyer_id);
			}
			$post_ids = [];
			$notification_ids = [];
			$post_negotiation_buyer_ids = [];
			$notification_negotiation_buyer_ids = [];
			$unique_buyer_ids = array_unique($buyer_ids);
			foreach ($ids as $id) {
				foreach ($unique_buyer_ids as $buy_id) {
					$negotiation_new = Negotiation::where(['buyer_id'=>$buy_id,'id'=>$id,'seller_id'=>$seller_id,'is_deal'=>0])->orderBy('id','DESC')->first();
					if(!empty($negotiation_new)){
						if($negotiation_new->negotiation_type == "post"){
							array_push($post_ids, $negotiation_new->post_notification_id);
							array_push($post_negotiation_buyer_ids, $negotiation_new->buyer_id);
						}
						if($negotiation_new->negotiation_type == "notification"){
							array_push($notification_ids, $negotiation_new->post_notification_id);
							array_push($notification_negotiation_buyer_ids, $negotiation_new->buyer_id);
						}
					}
				}
			}

			$negotiation_post_arr = [];
			$negotiation_notification_arr = [];
			$unique_post_ids = array_unique($post_ids);
			$unique_notification_ids = array_unique($notification_ids);
			$unique_post_negotiation_buyer_ids = array_unique($post_negotiation_buyer_ids);
			$unique_notification_negotiation_buyer_ids = array_unique($notification_negotiation_buyer_ids);
			foreach ($unique_post_ids as $i1) {
				foreach ($unique_post_negotiation_buyer_ids as $i11) {
					$negotiation_post = Negotiation::where(['buyer_id'=>$i11,'post_notification_id'=>$i1,'seller_id'=>$seller_id,'is_deal'=>0,'negotiation_type'=>'post'])->orderBy('id','DESC')->first();
					if(!empty($negotiation_post)){
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation_post->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation_post->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation_post->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$buyer_name = '';
						$buyer =  Buyers::where('id',$negotiation_post->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller =  Sellers::where('id',$negotiation_post->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						if($negotiation_post->negotiation_type == "post"){
							$post_array = [];
							$post = Post::where(['id'=>$negotiation_post->post_notification_id,'is_active'=>0,'status'=>'active'])->first();
							if(!empty($post)){
								$product_name = '';
				                $products = Product::where('id',$post->product_id)->first();
				                if(!empty($products)){
				                    $product_name = $products->name;
				                }
				                $name = '';
				                if($post->user_type == "seller"){
				                	$Seller = Sellers::where('id',$post->seller_buyer_id)->first();
				                	if(!empty($seller)){
				                		$name = $seller->name;
				                	}
				                }
				                if($post->user_type == "buyer"){
				                	$buyer = Buyers::where('id',$post->seller_buyer_id)->first();
				                	if(!empty($buyer)){
				                		$name = $buyer->name;
				                	}
				                }
								$post_array[] = [
									'post_id' => $post->id,
									'status' => $post->status,
									'seller_buyer_id' => $post->seller_buyer_id,
									'name' => $name,
									'user_type' => $post->user_type,
									'product_id' => $post->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $post->no_of_bales,
									'price' => $post->price,
									'address' => $post->address,
									'd_e' => $post->d_e,
									'buy_for' => $post->buy_for,
									'spinning_meal_name' => $post->spinning_meal_name,
								];
							}
						}
						$negotiation_post_arr[] = [
							'negotiation_id' => $negotiation_post->id,
							'buyer_id' => $negotiation_post->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation_post->seller_id,
							'seller_name' => $seller_name,
							'negotiation_by' => $negotiation_post->negotiation_by,
							'negotiation_type' => $negotiation_post->negotiation_type,
							'current_price' => $negotiation_post->current_price,
							'prev_price' => $negotiation_post->prev_price,
							'current_no_of_bales' => $negotiation_post->current_no_of_bales,
							'prev_no_of_bales' => $negotiation_post->prev_no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'post_notification_id' => $negotiation_post->post_notification_id,
							'post_detail' => (!empty($post_array))?$post_array:'',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}
			foreach ($unique_notification_negotiation_buyer_ids as $i22) {
				foreach ($unique_notification_ids as $i2) {
					$negotiation_notification = Negotiation::where(['buyer_id'=>$i22,'post_notification_id'=>$i2,'seller_id'=>$seller_id,'is_deal'=>0,'negotiation_type'=>'notification'])->orderBy('id','DESC')->first();
					if(!empty($negotiation_notification)){
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation_notification->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation_notification->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation_notification->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$buyer_name = '';
						$buyer =  Buyers::where('id',$negotiation_notification->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller =  Sellers::where('id',$negotiation_notification->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						if($negotiation_notification->negotiation_type == "notification"){
							$notification_array = [];
							$notification = Notification::where(['id'=>$negotiation_notification->post_notification_id,'is_active'=>0,'status'=>'active'])->first();
							if(!empty($notification)){
								$product_name = '';
				                $products = Product::where('id',$notification->product_id)->first();
				                if(!empty($products)){
				                    $product_name = $products->name;
				                }
				                $name = '';
				                if($notification->user_type == "seller"){
				                	$Seller = Sellers::where('id',$notification->seller_buyer_id)->first();
				                	if(!empty($seller)){
				                		$name = $seller->name;
				                	}
				                }
				                if($notification->user_type == "buyer"){
				                	$buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
				                	if(!empty($buyer)){
				                		$name = $buyer->name;
				                	}
				                }
								$notification_array[] = [
									'notification_id' => $notification->id,
									'status' => $notification->status,
									'seller_buyer_id' => $notification->seller_buyer_id,
									'name' => $name,
									'user_type' => $notification->user_type,
									'product_id' => $notification->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $notification->no_of_bales,
									'price' => $notification->price,
									'address' => $notification->address,
									'd_e' => $notification->d_e,
									'buy_for' => $notification->buy_for,
									'spinning_meal_name' => $notification->spinning_meal_name,
								];
							}
						}
						$negotiation_notification_arr[] = [
							'negotiation_id' => $negotiation_notification->id,
							'buyer_id' => $negotiation_notification->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation_notification->seller_id,
							'seller_name' => $seller_name,
							'negotiation_by' => $negotiation_notification->negotiation_by,
							'negotiation_type' => $negotiation_notification->negotiation_type,
							'current_price' => $negotiation_notification->current_price,
							'prev_price' => $negotiation_notification->prev_price,
							'current_no_of_bales' => $negotiation_notification->current_no_of_bales,
							'prev_no_of_bales' => $negotiation_notification->prev_no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'post_notification_id' => $negotiation_notification->post_notification_id,
							'post_detail' => (!empty($post_array))?$post_array:'',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}
			$negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);

			$response['status'] = 200;
			$response['message'] = 'Negotiation list';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}

	public function negotiation_list_buyer(Request $request)
	{
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

		$negotiation_array = [];
		$ids = [];
		$seller_ids = [];
		$negotiation = Negotiation::where(['buyer_id'=>$buyer_id,'is_deal'=>0])->orderBy('id','DESC')->get();
		if(count($negotiation)>0){
			foreach ($negotiation as $value) {
				array_push($ids, $value->id);
				array_push($seller_ids, $value->seller_id);
			}
			$post_ids = [];
			$notification_ids = [];
			$post_negotiation_seller_ids = [];
			$notification_negotiation_seller_ids = [];
			$unique_seller_ids = array_unique($seller_ids);
			foreach ($ids as $id) {
				foreach ($unique_seller_ids as $sel_id) {
					$negotiation_new = Negotiation::where(['seller_id'=>$sel_id,'id'=>$id,'buyer_id'=>$buyer_id,'is_deal'=>0])->orderBy('id','DESC')->first();
					if(!empty($negotiation_new)){
						if($negotiation_new->negotiation_type == "post"){
							array_push($post_ids, $negotiation_new->post_notification_id);
							array_push($post_negotiation_seller_ids, $negotiation_new->seller_id);
						}
						if($negotiation_new->negotiation_type == "notification"){
							array_push($notification_ids, $negotiation_new->post_notification_id);
							array_push($notification_negotiation_seller_ids, $negotiation_new->seller_id);
						}
					}
				}
			}

			$negotiation_post_arr = [];
			$negotiation_notification_arr = [];
			$unique_post_ids = array_unique($post_ids);
			$unique_notification_ids = array_unique($notification_ids);
			$unique_post_negotiation_seller_ids = array_unique($post_negotiation_seller_ids);
			$unique_notification_negotiation_seller_ids = array_unique($notification_negotiation_seller_ids);
			foreach ($unique_post_ids as $i1) {
				foreach ($unique_post_negotiation_seller_ids as $i11) {
					$negotiation_post = Negotiation::where(['seller_id'=>$i11,'post_notification_id'=>$i1,'buyer_id'=>$buyer_id,'is_deal'=>0,'negotiation_type'=>'post'])->orderBy('id','DESC')->first();
					if(!empty($negotiation_post)){
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation_post->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation_post->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation_post->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$buyer_name = '';
						$buyer =  Buyers::where('id',$negotiation_post->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller =  Sellers::where('id',$negotiation_post->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						if($negotiation_post->negotiation_type == "post"){
							$post_array = [];
							$post = Post::where(['id'=>$negotiation_post->post_notification_id,'is_active'=>0,'status'=>'active'])->first();
							if(!empty($post)){
								$product_name = '';
				                $products = Product::where('id',$post->product_id)->first();
				                if(!empty($products)){
				                    $product_name = $products->name;
				                }
				                $name = '';
				                if($post->user_type == "seller"){
				                	$Seller = Sellers::where('id',$post->seller_buyer_id)->first();
				                	if(!empty($seller)){
				                		$name = $seller->name;
				                	}
				                }
				                if($post->user_type == "buyer"){
				                	$buyer = Buyers::where('id',$post->seller_buyer_id)->first();
				                	if(!empty($buyer)){
				                		$name = $buyer->name;
				                	}
				                }
								$post_array[] = [
									'post_id' => $post->id,
									'status' => $post->status,
									'seller_buyer_id' => $post->seller_buyer_id,
									'name' => $name,
									'user_type' => $post->user_type,
									'product_id' => $post->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $post->no_of_bales,
									'price' => $post->price,
									'address' => $post->address,
									'd_e' => $post->d_e,
									'buy_for' => $post->buy_for,
									'spinning_meal_name' => $post->spinning_meal_name,
								];
							}
						}
						$negotiation_post_arr[] = [
							'negotiation_id' => $negotiation_post->id,
							'buyer_id' => $negotiation_post->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation_post->seller_id,
							'seller_name' => $seller_name,
							'negotiation_by' => $negotiation_post->negotiation_by,
							'negotiation_type' => $negotiation_post->negotiation_type,
							'current_price' => $negotiation_post->current_price,
							'prev_price' => $negotiation_post->prev_price,
							'current_no_of_bales' => $negotiation_post->current_no_of_bales,
							'prev_no_of_bales' => $negotiation_post->prev_no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'post_notification_id' => $negotiation_post->post_notification_id,
							'post_detail' => (!empty($post_array))?$post_array:'',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}
			foreach ($unique_notification_negotiation_seller_ids as $i22) {
				foreach ($unique_notification_ids as $i2) {
					$negotiation_notification = Negotiation::where(['seller_id'=>$i22,'post_notification_id'=>$i2,'buyer_id'=>$buyer_id,'is_deal'=>0,'negotiation_type'=>'notification'])->orderBy('id','DESC')->first();
					if(!empty($negotiation_notification)){
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation_notification->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation_notification->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation_notification->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$buyer_name = '';
						$buyer =  Buyers::where('id',$negotiation_notification->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller =  Sellers::where('id',$negotiation_notification->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						if($negotiation_notification->negotiation_type == "notification"){
							$notification_array = [];
							$notification = Notification::where(['id'=>$negotiation_notification->post_notification_id,'is_active'=>0,'status'=>'active'])->first();
							if(!empty($notification)){
								$product_name = '';
				                $products = Product::where('id',$notification->product_id)->first();
				                if(!empty($products)){
				                    $product_name = $products->name;
				                }
				                $name = '';
				                if($notification->user_type == "seller"){
				                	$Seller = Sellers::where('id',$notification->seller_buyer_id)->first();
				                	if(!empty($seller)){
				                		$name = $seller->name;
				                	}
				                }
				                if($notification->user_type == "buyer"){
				                	$buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
				                	if(!empty($buyer)){
				                		$name = $buyer->name;
				                	}
				                }
								$notification_array[] = [
									'notification_id' => $notification->id,
									'status' => $notification->status,
									'seller_buyer_id' => $notification->seller_buyer_id,
									'name' => $name,
									'user_type' => $notification->user_type,
									'product_id' => $notification->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $notification->no_of_bales,
									'price' => $notification->price,
									'address' => $notification->address,
									'd_e' => $notification->d_e,
									'buy_for' => $notification->buy_for,
									'spinning_meal_name' => $notification->spinning_meal_name,
								];
							}
						}
						$negotiation_notification_arr[] = [
							'negotiation_id' => $negotiation_notification->id,
							'buyer_id' => $negotiation_notification->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation_notification->seller_id,
							'seller_name' => $seller_name,
							'negotiation_by' => $negotiation_notification->negotiation_by,
							'negotiation_type' => $negotiation_notification->negotiation_type,
							'current_price' => $negotiation_notification->current_price,
							'prev_price' => $negotiation_notification->prev_price,
							'current_no_of_bales' => $negotiation_notification->current_no_of_bales,
							'prev_no_of_bales' => $negotiation_notification->prev_no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'post_notification_id' => $negotiation_notification->post_notification_id,
							'post_detail' => (!empty($post_array))?$post_array:'',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}
			$negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);

			$response['status'] = 200;
			$response['message'] = 'Negotiation list Buy';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}
	public function negotiation_detail(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';


		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
            'buyer_id' => 'required',
            'post_notification_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$negotiation_array = [];
		$negotiation = Negotiation::where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id])->first();
		if(!empty($negotiation)){
			$negotiation_log = NegotiationLog::where('negotiation_id',$negotiation->id)->take(2)->orderBy('id','DESC')->get();
			if(count($negotiation_log)>0){
				foreach ($negotiation_log as $value) {
					$transmit_condition_name = '';
					$transmit_condition = TransmitCondition::where('id',$value->transmit_condition_id)->first();
					if(!empty($transmit_condition)){
						$transmit_condition_name = $transmit_condition->name;
					}
					$payment_condition_name = '';
					$payment_condition = PaymentCondition::where('id',$value->payment_condition_id)->first();
					if(!empty($payment_condition)){
						$payment_condition_name = $payment_condition->name;
					}
					$lab_name = '';
					$lab = Lab::where('id',$value->lab_id)->first();
					if(!empty($lab)){
						$lab_name = $lab->name;
					}
					$seller_name = '';
					$seller = Sellers::where('id',$negotiation->seller_id)->first();
					if(!empty($seller)){
						$seller_name = $seller->name;
					}
					$buyer_name = '';
					$buyer = Buyers::where('id',$negotiation->buyer_id)->first();
					if(!empty($buyer)){
						$buyer_name = $buyer->name;
					}
					$broker_name = '';
					$broker = Brokers::where('id',$negotiation->broker_id)->first();
					if(!empty($broker)){
						$broker_name = $broker->name;
					}
					$header_name ='';
					if($negotiation->header == 1){
						$subject_to_data = SubjectTo::where('id',$negotiation->header)->first();
						if(!empty($subject_to_data)){
							$header_name = $subject_to_data->name;
						}
					}elseif ($negotiation->header == 2) {
						$confirm_to_data = SubjectTo::where('id',$negotiation->header)->first();
						if(!empty($confirm_to_data)){
							$header_name = $confirm_to_data->name;
						}
					}
					$negotiation_array[] = [
							'negotiation_id' => $value->id,
							'seller_id' => $negotiation->seller_id,
							'seller_name' => $seller_name,
							'buyer_id' => $negotiation->buyer_id,
							'buyer_name' => $buyer_name,
							'negotiation_by' => $value->negotiation_by,
							'post_notification_id' => $negotiation->post_notification_id,
							'negotiation_type' => $negotiation->negotiation_type,
							'current_price' => $value->price,
							'prev_price' => $negotiation->prev_price,
							'current_no_of_bales' => $value->bales,
							'prev_no_of_bales' => $negotiation->prev_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'header' => $header_name,
							'notes' => $negotiation->notes,
							'broker_name' => $broker_name,
					];
					$response['status'] = 200;
					$response['message'] = 'Negotiation Detail';
					$response['data'] = $negotiation_array;
				}
			}
		}
		return response($response, 200);
	}
	public function settings(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$settings_array = [];
		$settings = Settings::all();
		if(count($settings)>0){
			foreach ($settings as $value) {
				$settings_array[] = [
					'id' => $value->id,
					'negotiation_count' => $value->negotiation_count,
					'bunch' => $value->bunch,
				];
			}
			$response['status'] = 200;
			$response['message'] = 'settings';
			$response['data'] = $settings_array;
		}
		return response($response, 200);
	}

	public function make_deal(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$type = isset($content->post_notification_id) ? $content->type : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$done_by = isset($content->done_by) ? $content->done_by : '';

		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
			'type' => $type,
			'no_of_bales' => $no_of_bales,
			'done_by' => $done_by
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
            'buyer_id' => 'required',
            'post_notification_id' => 'required',
            'type' => 'required',
            'no_of_bales' => 'required',
            'done_by' => 'required'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$negotiation = Negotiation::where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id,'negotiation_type'=>$type,'is_deal'=>0])->orderBy('id','DESC')->get();

		if(count($negotiation)>0){
			foreach ($negotiation as $value) {

				if($value->negotiation_type == "post"){
		    	 	$post_remain = Post::where(['id'=>$value->post_notification_id,'is_active'=>0])->first();
				   	if($no_of_bales > $post_remain->no_of_bales){
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}/*elseif ($no_of_bales > $post_remain->remain_bales) {
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}*/else{
				   		$complete = Negotiation::where('id',$value->id)->orderBy('id','DESC')->first();
						if(!empty($complete)){
							$complete->is_deal = 1;
							$complete->status = 'complete';
							$complete->updated_at = date('Y-m-d H:i:s');
							$complete->save();
							$make_deal = new NegotiationComplete();

							$response['status'] = 200;
							$response['message'] = 'Make Deal done';
						}
				   	}
				}
				if($value->negotiation_type == "notification"){
		    	 	$notification_remain = Notification::where(['id'=>$value->post_notification_id,'is_active'=>0])->first();
				   	if($no_of_bales > $notification_remain->no_of_bales){
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}/*elseif ($no_of_bales > $notification_remain->remain_bales) {
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}*/else{
				   		$complete = Negotiation::where('id',$value->id)->orderBy('id','DESC')->first();
						if(!empty($complete)){
							$complete->is_deal = 1;
							$complete->status = 'complete';
							$complete->updated_at = date('Y-m-d H:i:s');
							$complete->save();
							$make_deal = new NegotiationComplete();

							$response['status'] = 200;
							$response['message'] = 'Make Deal done';
						}
				   	}
				}
			}

			$negotiation_comp = Negotiation::where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id,'negotiation_type'=>$type,'is_deal'=>1])->orderBy('id','DESC')->first();

			if(!empty($negotiation_comp)){

					if($type == "post"){
									$post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
									if(!empty($post)){

										$post->sold_bales = $post->sold_bales + $no_of_bales;
										$post->remain_bales = $post->no_of_bales - $post->sold_bales;
										//$make_deal->no_of_bales = $post->sold_bales;
										$make_deal->no_of_bales = $no_of_bales;
										if($post->remain_bales <= 0){
											$negotiation_in = Negotiation::where(['post_notification_id'=>$post_notification_id,'negotiation_type'=>'post','status'=>'incomplete'])->get();
											if(count($negotiation_in)>0){
												foreach ($negotiation_in as $val) {
													$compl = Negotiation::where('id',$val->id)->first();
													if(!empty($compl)){
														$compl->is_deal = 2;
														$compl->save();
													}
												}
											}
											$post->status = 'complete';
											$post->updated_at = date('Y-m-d H:i:s');
										}
										$post->save();
									}
								}
							if($type == "notification"){
									$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
									if(!empty($notification)){
										$notification->sold_bales = $notification->sold_bales +  $no_of_bales;
										$notification->remain_bales = $notification->no_of_bales - $notification->sold_bales;
										//$make_deal->no_of_bales = $notification->sold_bales;
										$make_deal->no_of_bales = $no_of_bales;
										if($notification->remain_bales <= 0){
											$negotiation_in = Negotiation::where(['post_notification_id'=>$post_notification_id,'negotiation_type'=>'notification','status'=>'incomplete'])->get();
											if(count($negotiation_in)>0){
												foreach ($negotiation_in as $val) {
													$compl = Negotiation::where('id',$val->id)->first();
													if(!empty($compl)){
														$compl->is_deal = 2;
														$compl->save();
													}
												}
											}
											$notification->status = 'complete';
											$notification->updated_at = date('Y-m-d H:i:s');
										}
										$notification->save();
									}
								}
					$make_deal->negotiation_id = $negotiation_comp->id;
					$make_deal->buyer_id = $negotiation_comp->buyer_id;
					$make_deal->seller_id = $negotiation_comp->seller_id;
					$make_deal->negotiation_by = $negotiation_comp->negotiation_by;
					$make_deal->post_notification_id = $negotiation_comp->post_notification_id;
					$make_deal->negotiation_type = $negotiation_comp->negotiation_type;
					$make_deal->price = $negotiation_comp->current_price;
					$make_deal->done_by = $done_by;
					$make_deal->payment_condition = $negotiation_comp->payment_condition;
					$make_deal->transmit_condition = $negotiation_comp->transmit_condition;
					$make_deal->lab = $negotiation_comp->lab;
					$make_deal->is_deal = $negotiation_comp->is_deal;
					$make_deal->status = $negotiation_comp->status;
					$make_deal->save();

					//pdf
				        $broker_name = '';
				        $broker_address = '';
				        $broker_country = '';
				        $broker_state = '';
				        $broker_mobile_number = '';
				        $broker_mobile_number_2 = '';
				        $broker_email = '';
				        $broker_url = '';
				        $seller_name_address = '';
				        $seller_station = '';
				        $buyer_name_address = '';
				        $buyer_station = '';
				        $product_name_pdf = '';
				        $deal_date = '';
				        $deal_no_of_bales = '';
				        $deal_price = '';
				        $ref_no = '';
				        $attribute_array_pdf = '';
				        $broker_stamp_image = '';
				        $buyer_stamp_image = '';
				        $seller_stamp_image = '';
				        $broker_header_image = '';

				        $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
				        $deal_price = $make_deal->price;
				        $ref_no = $make_deal->id;
				        $deal_no_of_bales = $make_deal->no_of_bales;
						if($done_by == "seller"){
							$seller = Sellers::where('id',$seller_id)->first();
							if(!empty($seller)){
								$broker = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;
												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($done_by == "buyer"){
							$buyer = Buyers::where('id',$buyer_id)->first();
							if(!empty($buyer)){
								$broker = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;
												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($make_deal->negotiation_type == "post"){
							$post_detail = Post::where('id',$post_notification_id)->first();
							if(!empty($post_detail)){
								$product = Product::where('id',$post_detail->product_id)->first();
								if(!empty($product)){
									$product_name_pdf = $product->name;
								}
								$attribute_array_push  = [];
								$attribute = PostDetails::where('post_id',$post_detail->id)->get();
								foreach ($attribute as $value) {
									array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
								}
								$attribute_array_pdf = implode(",",$attribute_array_push);
							}
						}
						if($make_deal->negotiation_type == "notification"){
							$notification_detail = Notification::where('id',$post_notification_id)->first();
							if(!empty($notification_detail)){
								$product = Product::where('id',$notification_detail->product_id)->first();
								if(!empty($product)){
									$product_name_pdf = $product->name;
								}
								$attribute_array_push  = [];
								$attribute = NotificatonDetails::where('notification_id',$notification_detail->id)->get();
								foreach ($attribute as $value) {
									array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
								}
								$attribute_array_pdf = implode(",",$attribute_array_push);
							}
						}

						$seller_details = Sellers::where('id',$seller_id)->first();
						if(!empty($seller_details)){
							$seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
							if(!empty($seller_sub_details)){
								$station = Station::where('id',$seller_sub_details->station_id)->first();
								if(!empty($station)){
									$seller_name_address = $seller_details->name.' '.$seller_details->address;
									$seller_station = $station->name;
									if(!empty($seller_details->image)){
										$seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
										$file3 = file_get_contents($seller_stamp_img);
										$seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file3);
									}
								}
							}
						}

						$buyer_details = Buyers::where('id',$buyer_id)->first();
						if(!empty($buyer_details)){
							$buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
							if(!empty($buyer_sub_details)){
								$station = Station::where('id',$buyer_sub_details->station_id)->first();
								if(!empty($station)){
									$buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
									$buyer_station = $station->name;
									if(!empty($buyer_details->image)){
										$buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
										$file4 = file_get_contents($buyer_stamp_img);
										$buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file4);
									}
								}
							}
						}

						$pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
				        $pdf->getDomPDF()->setHttpContext(
				            stream_context_create([
				                'ssl' => [
				                    'allow_self_signed'=> TRUE,
				                    'verify_peer' => FALSE,
				                    'verify_peer_name' => FALSE,
				                ]
				            ])
				        );

						$current_time = time();
						$file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
						//$pdf->save(public_path().$file_name);
						Storage::put('public/pdf/'.$file_name, $pdf->output());

						$save = new DealPdf();
						$save->deal_id = $make_deal->id;
						$save->done_by = $make_deal->done_by;
						$save->seller_id = $make_deal->seller_id;
						$save->buyer_id = $make_deal->buyer_id;
						$save->filename = $file_name;
						$save->save();
						//pdf
			}
		}else{

			if($type == "notification"){
				$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($notification)){
					if($no_of_bales > $notification->no_of_bales){
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}/*elseif ($no_of_bales > $notification->remain_bales) {
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}*/else{
				   		$notification->sold_bales = $notification->no_of_bales;
						$notification->remain_bales = 0;
						$notification->status = 'complete';
						$notification->updated_at = date('Y-m-d H:i:s');
						$notification->save();


							$make_deal = new NegotiationComplete();
							$make_deal->negotiation_id = 0;
							$make_deal->done_by = $done_by;
							$without = new WithoutNegotiationMakeDeal();
							$without->post_notification_id = $notification->id;
							$without->type = 'notification';
							if($notification->user_type == "seller"){
								$without->seller_buyer_id = $buyer_id;
								$without->user_type = 'buyer';
								$make_deal->seller_id = $notification->seller_buyer_id;
								$make_deal->buyer_id = $buyer_id;
							}
							if($notification->user_type == "buyer"){
								$without->seller_buyer_id = $seller_id;
								$without->user_type = 'seller';
								$make_deal->seller_id = $seller_id;
								$make_deal->buyer_id = $notification->seller_buyer_id;
							}
							$without->save();

							$make_deal->negotiation_by = 'seller';
							$make_deal->post_notification_id = $notification->id;
							$make_deal->negotiation_type = 'notification';
							$make_deal->price = $notification->price;
							$make_deal->no_of_bales = $notification->no_of_bales;
							$make_deal->payment_condition = $notification->payment_condition;
							$make_deal->transmit_condition = $notification->transmit_condition;
							$make_deal->lab = $notification->lab;
							$make_deal->is_deal = '1';
							$make_deal->status = 'complete';
							$make_deal->save();

						//pdf
				        $broker_name = '';
				        $broker_address = '';
				        $broker_country = '';
				        $broker_state = '';
				        $broker_mobile_number = '';
				        $broker_mobile_number_2 = '';
				        $broker_email = '';
				        $broker_url = '';
				        $seller_name_address = '';
				        $seller_station = '';
				        $buyer_name_address = '';
				        $buyer_station = '';
				        $product_name_pdf = '';
				        $deal_date = '';
				        $deal_no_of_bales = '';
				        $deal_price = '';
				        $ref_no = '';
				        $attribute_array_pdf = '';
				        $broker_stamp_image = '';
				        $buyer_stamp_image = '';
				        $seller_stamp_image = '';
				        $broker_header_image = '';

				        $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
				        $deal_price = $make_deal->price;
				        $ref_no = $make_deal->id;
				        $deal_no_of_bales = $make_deal->no_of_bales;
						if($done_by == "seller"){
							$seller = Sellers::where('id',$seller_id)->first();
							if(!empty($seller)){
								$broker = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;

												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($done_by == "buyer"){
							$buyer = Buyers::where('id',$buyer_id)->first();
							if(!empty($buyer)){
								$broker = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;

												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($make_deal->negotiation_type == "notification"){
							$notification_detail = Notification::where('id',$post_notification_id)->first();
							if(!empty($notification_detail)){
								$product = Product::where('id',$notification_detail->product_id)->first();
								if(!empty($product)){
									$product_name_pdf = $product->name;
								}
								$attribute_array_push  = [];
								$attribute = NotificatonDetails::where('notification_id',$notification_detail->id)->get();
								foreach ($attribute as $value) {
									array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
								}
								$attribute_array_pdf = implode(",",$attribute_array_push);
							}
						}

						$seller_details = Sellers::where('id',$seller_id)->first();
						if(!empty($seller_details)){
							$seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
							if(!empty($seller_sub_details)){
								$station = Station::where('id',$seller_sub_details->station_id)->first();
								if(!empty($station)){
									$seller_name_address = $seller_details->name.' '.$seller_details->address;
									$seller_station = $station->name;
									if(!empty($seller_details->image)){
										$seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
										$file3 = file_get_contents($seller_stamp_img);
										$seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file3);
									}
								}
							}
						}

						$buyer_details = Buyers::where('id',$buyer_id)->first();
						if(!empty($buyer_details)){
							$buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
							if(!empty($buyer_sub_details)){
								$station = Station::where('id',$buyer_sub_details->station_id)->first();
								if(!empty($station)){
									$buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
									$buyer_station = $station->name;
									if(!empty($buyer_details->image)){
										$buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
										$file4 = file_get_contents($buyer_stamp_img);
										$buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file4);
									}
								}
							}
						}

						$pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
				        $pdf->getDomPDF()->setHttpContext(
				            stream_context_create([
				                'ssl' => [
				                    'allow_self_signed'=> TRUE,
				                    'verify_peer' => FALSE,
				                    'verify_peer_name' => FALSE,
				                ]
				            ])
				        );

						$current_time = time();
						$file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
						//$pdf->save(public_path().$file_name);
						Storage::put('public/pdf/'.$file_name, $pdf->output());

						$save = new DealPdf();
						$save->deal_id = $make_deal->id;
						$save->done_by = $make_deal->done_by;
						$save->seller_id = $make_deal->seller_id;
						$save->buyer_id = $make_deal->buyer_id;
						$save->filename = $file_name;
						$save->save();
						//pdf

						$response['status'] = 200;
						$response['message'] = 'Make Deal done';
				   	}
				}
			}elseif ($type == "post") {
				$post = Post::where(['id'=>$post_notification_id,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($post)){
					if($no_of_bales > $post->no_of_bales){
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}/*elseif ($no_of_bales > $post->remain_bales) {
				   		$response['status'] = 404;
				   		$response['message'] = 'Please Enter Less Bales';
				   	}*/else{
				   		$post->sold_bales = $post->no_of_bales;
						$post->remain_bales = 0;
						$post->status = 'complete';
						$post->updated_at = date('Y-m-d H:i:s');
						$post->save();

							$make_deal = new NegotiationComplete();
							$make_deal->negotiation_id = 0;
							$make_deal->done_by = $done_by;
							$without = new WithoutNegotiationMakeDeal();
							$without->post_notification_id = $post->id;
							$without->type = 'post';
							if($post->user_type == "seller"){
								$without->seller_buyer_id = $buyer_id;
								$without->user_type = 'buyer';
								$make_deal->seller_id = $post->seller_buyer_id;
								$make_deal->buyer_id = $buyer_id;
							}
							if($post->user_type == "buyer"){
								$without->seller_buyer_id = $seller_id;
								$without->user_type = 'seller';
								$make_deal->seller_id = $seller_id;
								$make_deal->buyer_id = $post->seller_buyer_id;
							}
							$without->save();
							$make_deal->negotiation_by = 'seller';
							$make_deal->post_notification_id = $post->id;
							$make_deal->negotiation_type = 'post';
							$make_deal->price = $post->price;
							$make_deal->no_of_bales = $post->no_of_bales;
							$make_deal->payment_condition = $post->payment_condition;
							$make_deal->transmit_condition = $post->transmit_condition;
							$make_deal->lab = $post->lab;
							$make_deal->is_deal = '1';
							$make_deal->status = 'complete';
							$make_deal->save();


						//pdf
				        $broker_name = '';
				        $broker_address = '';
				        $broker_country = '';
				        $broker_state = '';
				        $broker_mobile_number = '';
				        $broker_mobile_number_2 = '';
				        $broker_email = '';
				        $broker_url = '';
				        $seller_name_address = '';
				        $seller_station = '';
				        $buyer_name_address = '';
				        $buyer_station = '';
				        $product_name_pdf = '';
				        $deal_date = '';
				        $deal_no_of_bales = '';
				        $deal_price = '';
				        $ref_no = '';
				        $attribute_array_pdf = '';
				        $broker_stamp_image = '';
				        $buyer_stamp_image = '';
				        $seller_stamp_image = '';
				        $broker_header_image = '';

				        $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
				        $deal_price = $make_deal->price;
				        $ref_no = $make_deal->id;
				        $deal_no_of_bales = $make_deal->no_of_bales;
						if($done_by == "seller"){
							$seller = Sellers::where('id',$seller_id)->first();
							if(!empty($seller)){
								$broker = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;

												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($done_by == "buyer"){
							$buyer = Buyers::where('id',$buyer_id)->first();
							if(!empty($buyer)){
								$broker = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker)){
									$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
									if(!empty($broker_data)){
										$country = Country::where('id',$broker_data->country_id)->first();
										if(!empty($country)){
											$state = State::where('id',$broker_data->state_id)->first();
											if(!empty($state)){
												$broker_name = $broker->name;
												$broker_address = $broker->address;
												$broker_country = $country->name;
												$broker_state = $state->name;
												$broker_mobile_number = $broker->mobile_number;
												$broker_mobile_number_2 =$broker->mobile_number_2;
												$broker_email =$broker->email;
												$broker_url =$broker->website;

												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
										}
									}
								}
							}
						}

						if($make_deal->negotiation_type == "post"){
							$post_detail = Post::where('id',$post_notification_id)->first();
							if(!empty($post_detail)){
								$product = Product::where('id',$post_detail->product_id)->first();
								if(!empty($product)){
									$product_name_pdf = $product->name;
								}
								$attribute_array_push  = [];
								$attribute = PostDetails::where('post_id',$post_detail->id)->get();
								foreach ($attribute as $value) {
									array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
								}
								$attribute_array_pdf = implode(",",$attribute_array_push);
							}
						}

						$seller_details = Sellers::where('id',$seller_id)->first();
						if(!empty($seller_details)){
							$seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
							if(!empty($seller_sub_details)){
								$station = Station::where('id',$seller_sub_details->station_id)->first();
								if(!empty($station)){
									$seller_name_address = $seller_details->name.' '.$seller_details->address;
									$seller_station = $station->name;
									if(!empty($seller_details->image)){
										$seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
										$file3 = file_get_contents($seller_stamp_img);
										$seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file3);
									}
								}
							}
						}

						$buyer_details = Buyers::where('id',$buyer_id)->first();
						if(!empty($buyer_details)){
							$buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
							if(!empty($buyer_sub_details)){
								$station = Station::where('id',$buyer_sub_details->station_id)->first();
								if(!empty($station)){
									$buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
									$buyer_station = $station->name;
									if(!empty($buyer_details->image)){
										$buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
										$file4 = file_get_contents($buyer_stamp_img);
										$buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file4);
									}
								}
							}
						}

						$pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
				        $pdf->getDomPDF()->setHttpContext(
				            stream_context_create([
				                'ssl' => [
				                    'allow_self_signed'=> TRUE,
				                    'verify_peer' => FALSE,
				                    'verify_peer_name' => FALSE,
				                ]
				            ])
				        );

						$current_time = time();
						$file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
						//$pdf->save(public_path().$file_name);
						Storage::put('public/pdf/'.$file_name, $pdf->output());

						$save = new DealPdf();
						$save->deal_id = $make_deal->id;
						$save->done_by = $make_deal->done_by;
						$save->seller_id = $make_deal->seller_id;
						$save->buyer_id = $make_deal->buyer_id;
						$save->filename = $file_name;
						$save->save();
						//pdf

						$response['status'] = 200;
						$response['message'] = 'Make Deal done';
				   	}
				}
			}else{
				$response['status'] = 404;
			}

		}

        $fcm_token = "";
        $user = [];
        if ($done_by == "seller") {
            $user_type = "buyer";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$buyer_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Sellers::select('name')->where('id',$seller_id)->first();
        }else if($done_by == 'buyer'){
            $user_type = "seller";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$seller_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Buyers::select('name')->where('id',$buyer_id)->first();
        }

        if (!empty($fcm_token)) {
            $json_array = [
                "registration_ids" => [$fcm_token],
                "data" => [
                    'user_type' => $done_by,
                ],
                "notification" => [
                    "body" => "Notification send by ".$user->name,
                    "title" => "Buy Notification",
                    "icon" => "ic_launcher"
                ]
            ];
            NotificationHelper::notification($json_array,$user_type);
        }

		return response($response, 200);
	}

	public function completed_deal(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';

		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }


			$post_array = [];
			$post_complete_cancel = Post::where(['seller_buyer_id'=>$seller_id,'is_active'=>0,'user_type'=>'seller'])->where(function($query) {
                        $query->where('status','cancel')
                            ->orWhere('status','complete');
                    })->orderBy('id','DESC')->get();
			if(count($post_complete_cancel)>0){
				foreach ($post_complete_cancel as $value) {
					$negotiation_array = [];
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id])->first();
					if(!empty($negotiation)){
						$buyer_name = '';
						$buyer = Buyers::where('id',$negotiation->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller = Sellers::where('id',$negotiation->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$negotiation_array[] = [
							'negotiation_id' => $negotiation->id,
							'buyer_id' => $negotiation->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation->seller_id,
							'seller_name' => $seller_name,
							'post_notification_id' => $negotiation->post_notification_id,
							'negotiation_type'=> $negotiation->negotiation_type,
							'current_price'=>$negotiation->current_price,
							'prev_price'=>$negotiation->prev_price,
							'current_no_of_bales' => $negotiation->current_no_of_bales,
							'prev_no_of_bales' => $negotiation->prev_no_of_bales,
							'no_of_bales'=>$negotiation->no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name
						];
					}
					$name = '';
					if($value->user_type == "seller"){
						 $seller = Sellers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($seller)){
						 	$name = $seller->name;
						 }
					}
					if($value->user_type == "buyer"){
						 $buyer = Buyers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($buyer)){
						 	$name = $buyer->name;
						 }
					}
					$product_name = '';
					$product = Product::where('id',$value->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$done_by = '';
					$without = WithoutNegotiationMakeDeal::where(['post_notification_id'=>$value->id,'type'=>'post'])->first();
					if(!empty($without)){
						if($without->user_type == "seller"){
							$seller_without = Sellers::where('id',$without->seller_buyer_id)->first();
							if(!empty($seller_without)){
								$done_by = $seller_without->name;
							}
						}
						if($without->user_type == "buyer"){
							$buyer_without = Buyers::where('id',$without->seller_buyer_id)->first();
							if(!empty($buyer_without)){
								$done_by = $buyer_without->name;
							}
						}
					}elseif (!empty($buyer_name)) {
						$done_by = $buyer_name;
					}else{
						$done_by = $name;
					}

					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$updated_at = date('d-m-Y, h:i a', strtotime($value->updated_at));
					$post_array[] = [
						'post_id' => $value->id,
						'status' => $value->status,
						'seller_buyer_id' => $value->seller_buyer_id,
						'name' => $name,
						'done_by' => $done_by,
						'user_type' =>$value->user_type,
						'product_id'=>$value->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $value->no_of_bales,
						'price'=> $value->price,
						'address' => $value->address,
						'd_e' => $value->d_e,
						'buy_for'=>$value->buy_for,
						'spinning_meal_name' => $value->spinning_meal_name,
						'negotiation_array' => $negotiation_array,
						'created_at' => $created_at,
						'updated_at' => $updated_at,
						'type' => 'post'
					];
				}
			}
			$notification_array = [];
			$buyer_array = [];
			$notification_complete_cancel = Notification::where(['seller_buyer_id'=>$seller_id,'is_active'=>0,'user_type'=>'seller'])->where(function($query) {
                        $query->where('status','cancel')
                            ->orWhere('status','complete');
                    })->orderBy('id','DESC')->get();
			if(count($notification_complete_cancel)>0){
				foreach ($notification_complete_cancel as $value) {
					$negotiation_array = [];
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id])->first();
					if(!empty($negotiation)){
						$buyer_name = '';
						$buyer = Buyers::where('id',$negotiation->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller = Sellers::where('id',$negotiation->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$negotiation_array[] = [
							'negotiation_id' => $negotiation->id,
							'buyer_id' => $negotiation->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation->seller_id,
							'seller_name' => $seller_name,
							'post_notification_id' => $negotiation->post_notification_id,
							'negotiation_type'=> $negotiation->negotiation_type,
							'current_price'=>$negotiation->current_price,
							'prev_price'=>$negotiation->prev_price,
							'current_no_of_bales' => $negotiation->current_no_of_bales,
							'prev_no_of_bales' => $negotiation->prev_no_of_bales,
							'no_of_bales'=>$negotiation->no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name
						];
					}
					$product_name = '';
					$product = Product::where('id',$value->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$buyer_data = SelectionSellerBuyer::where('notification_id',$value->id)->get();
					foreach ($buyer_data as $val2) {
						$name = Buyers::where('id',$val2->seller_buyer_id)->first();
						$city_data = UserDetails::where(['user_id'=>$val2->seller_buyer_id,'user_type'=>'buyer'])->first();
						$city_name = City::where('id',$city_data->city_id)->first();
						if(!empty($name)){
							if(!empty($city_data)){
								if(!empty($city_name))
								$buyer_array[] = [
									'name' => $name->name,
									'city' => $city_name->name
								];
							}
						}
					}
					$name = '';
					if($value->user_type == "seller"){
						 $seller = Sellers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($seller)){
						 	$name = $seller->name;
						 }
					}
					if($value->user_type == "buyer"){
						 $buyer = Buyers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($buyer)){
						 	$name = $buyer->name;
						 }
					}
					$done_by = '';
					$without = WithoutNegotiationMakeDeal::where(['post_notification_id'=>$value->id,'type'=>'notification'])->first();
					if(!empty($without)){
						if($without->user_type == "seller"){
							$seller_without = Sellers::where('id',$without->seller_buyer_id)->first();
							if(!empty($seller_without)){
								$done_by = $seller_without->name;
							}
						}
						if($without->user_type == "buyer"){
							$buyer_without = Buyers::where('id',$without->seller_buyer_id)->first();
							if(!empty($buyer_without)){
								$done_by = $buyer_without->name;
							}
						}
					}elseif (!empty($buyer_name)) {
						$done_by = $buyer_name;
					}else{
						$done_by = $name;
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$updated_at = date('d-m-Y, h:i a', strtotime($value->updated_at));
					$notification_array[] = [
						'notification_id' => $value->id,
						'status' => $value->status,
						'seller_buyer_id' => $value->seller_buyer_id,
						'name' => $name,
						'done_by' => $done_by,
						'user_type' =>$value->user_type,
						'product_id'=>$value->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $value->no_of_bales,
						'price'=> $value->price,
						'address' => $value->address,
						'd_e' => $value->d_e,
						'buy_for'=>$value->buy_for,
						'spinning_meal_name' => $value->spinning_meal_name,
						'negotiation_array' => $negotiation_array,
						'created_at' => $created_at,
						'updated_at' => $updated_at,
						'type' => 'notification',
						'buyer_array' => $buyer_array
					];
				}
			}
		$final_arr = array_merge($post_array,$notification_array);
		$response['status'] = 200;
		$response['message'] = 'completed deal';
		$response['data'] = $final_arr;
		return response($response, 200);
	}
	public function completed_deal_buyer(Request $request)
	{
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

	    $final_arr = [];

			$post_array = [];
					$post_complete_cancel = Post::where(['seller_buyer_id'=>$buyer_id,'is_active'=>0,'user_type'=>'buyer'])->where(function($query) {
                        $query->where('status','cancel')
                            ->orWhere('status','complete');
                    })->orderBy('id','DESC')->get();
			if(count($post_complete_cancel)>0){
				foreach ($post_complete_cancel as $value) {
					$negotiation_array = [];
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id])->orderBy('id','DESC')->first();
					if(!empty($negotiation)){
						$buyer_name = '';
						$buyer = Buyers::where('id',$negotiation->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller = Sellers::where('id',$negotiation->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$negotiation_array[] = [
							'negotiation_id' => $negotiation->id,
							'buyer_id' => $negotiation->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation->seller_id,
							'seller_name' => $seller_name,
							'post_notification_id' => $negotiation->post_notification_id,
							'negotiation_type'=> $negotiation->negotiation_type,
							'current_price'=>$negotiation->current_price,
							'prev_price'=>$negotiation->prev_price,
							'current_no_of_bales' => $negotiation->current_no_of_bales,
							'prev_no_of_bales' => $negotiation->prev_no_of_bales,
							'no_of_bales'=>$negotiation->no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name
						];
					}
					$name = '';
					if($value->user_type == "seller"){
						 $seller = Sellers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($seller)){
						 	$name = $seller->name;
						 }
					}
					if($value->user_type == "buyer"){
						 $buyer = Buyers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($buyer)){
						 	$name = $buyer->name;
						 }
					}
					$product_name = '';
					$product = Product::where('id',$value->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$done_by = '';
					$without = WithoutNegotiationMakeDeal::where(['post_notification_id'=>$value->id,'type'=>'post'])->first();
					if(!empty($without)){
						if($without->user_type == "seller"){
							$seller_without = Sellers::where('id',$without->seller_buyer_id)->first();
							if(!empty($seller_without)){
								$done_by = $seller_without->name;
							}
						}
						if($without->user_type == "buyer"){
							$buyer_without = Buyers::where('id',$without->seller_buyer_id)->first();
							if(!empty($buyer_without)){
								$done_by = $buyer_without->name;
							}
						}
					}elseif (!empty($seller_name)) {
						$done_by = $seller_name;
					}else{
						$done_by = $name;
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$updated_at = date('d-m-Y, h:i a', strtotime($value->updated_at));
					$post_array[] = [
						'post_id' => $value->id,
						'status' => $value->status,
						'seller_buyer_id' => $value->seller_buyer_id,
						'name' => $name,
						'done_by' => $done_by,
						'user_type' =>$value->user_type,
						'product_id'=>$value->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $value->no_of_bales,
						'price'=> $value->price,
						'address' => $value->address,
						'd_e' => $value->d_e,
						'buy_for'=>$value->buy_for,
						'spinning_meal_name' => $value->spinning_meal_name,
						'negotiation_array' => $negotiation_array,
						'created_at'=>$created_at,
						'updated_at'=>$updated_at,
						'type' => 'post'
					];
				}
			}
			$notification_array = [];
			$seller_array = [];
			$notification_complete_cancel = Notification::where(['seller_buyer_id'=>$buyer_id,'is_active'=>0,'user_type'=>'buyer'])->where(function($query) {
                        $query->where('status','cancel')
                            ->orWhere('status','complete');
                    })->orderBy('id','DESC')->get();
			if(count($notification_complete_cancel)>0){
				foreach ($notification_complete_cancel as $value) {
					$negotiation_array = [];
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id])->first();
					if(!empty($negotiation)){
						$buyer_name = '';
						$buyer = Buyers::where('id',$negotiation->buyer_id)->first();
						if(!empty($buyer)){
							$buyer_name = $buyer->name;
						}
						$seller_name = '';
						$seller = Sellers::where('id',$negotiation->seller_id)->first();
						if(!empty($seller)){
							$seller_name = $seller->name;
						}
						$transmit_condition_name = '';
						$transmit_condition = TransmitCondition::where('id',$negotiation->transmit_condition)->first();
						if(!empty($transmit_condition)){
							$transmit_condition_name = $transmit_condition->name;
						}
						$payment_condition_name = '';
						$payment_condition = PaymentCondition::where('id',$negotiation->payment_condition)->first();
						if(!empty($payment_condition)){
							$payment_condition_name = $payment_condition->name;
						}
						$lab_name = '';
						$lab = Lab::where('id',$negotiation->lab)->first();
						if(!empty($lab)){
							$lab_name = $lab->name;
						}
						$negotiation_array[] = [
							'negotiation_id' => $negotiation->id,
							'buyer_id' => $negotiation->buyer_id,
							'buyer_name' => $buyer_name,
							'seller_id' => $negotiation->seller_id,
							'seller_name' => $seller_name,
							'post_notification_id' => $negotiation->post_notification_id,
							'negotiation_type'=> $negotiation->negotiation_type,
							'current_price'=>$negotiation->current_price,
							'prev_price'=>$negotiation->prev_price,
							'current_no_of_bales' => $negotiation->current_no_of_bales,
							'prev_no_of_bales' => $negotiation->prev_no_of_bales,
							'no_of_bales'=>$negotiation->no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name
						];
					}
					$product_name = '';
					$product = Product::where('id',$value->product_id)->first();
					if(!empty($product)){
						$product_name = $product->name;
					}
					$seller_data = SelectionSellerBuyer::where('notification_id',$value->id)->get();
					foreach ($seller_data as $val2) {
						$name = Sellers::where('id',$val2->seller_buyer_id)->first();
						$city_data = UserDetails::where(['user_id'=>$val2->seller_buyer_id,'user_type'=>'seller'])->first();
						$city_name = City::where('id',$city_data->city_id)->first();
						if(!empty($name)){
							if(!empty($city_data)){
								if(!empty($city_name))
								$seller_array[] = [
									'name' => $name->name,
									'city' => $city_name->name
								];
							}
						}
					}
					$name = '';
					if($value->user_type == "seller"){
						 $seller = Sellers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($seller)){
						 	$name = $seller->name;
						 }
					}
					if($value->user_type == "buyer"){
						 $buyer = Buyers::where('id',$value->seller_buyer_id)->first();
						 if(!empty($buyer)){
						 	$name = $buyer->name;
						 }
					}
					$done_by = '';
					$without = WithoutNegotiationMakeDeal::where(['post_notification_id'=>$value->id,'type'=>'notification'])->first();
					if(!empty($without)){
						if($without->user_type == "seller"){
							$seller_without = Sellers::where('id',$without->seller_buyer_id)->first();
							if(!empty($seller_without)){
								$done_by = $seller_without->name;
							}
						}
						if($without->user_type == "buyer"){
							$buyer_without = Buyers::where('id',$without->seller_buyer_id)->first();
							if(!empty($buyer_without)){
								$done_by = $buyer_without->name;
							}
						}
					}elseif (!empty($seller_name)) {
						$done_by = $seller_name;
					}else{
						$done_by = $name;
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$updated_at = date('d-m-Y, h:i a', strtotime($value->updated_at));
					$notification_array[] = [
						'notification_id' => $value->id,
						'status' => $value->status,
						'seller_buyer_id' => $value->seller_buyer_id,
						'name' => $name,
						'done_by' => $done_by,
						'user_type' =>$value->user_type,
						'product_id'=>$value->product_id,
						'product_name' => $product_name,
						'no_of_bales' => $value->no_of_bales,
						'price'=> $value->price,
						'address' => $value->address,
						'd_e' => $value->d_e,
						'buy_for'=>$value->buy_for,
						'spinning_meal_name' => $value->spinning_meal_name,
						'negotiation_array' => $negotiation_array,
						'created_at'=>$created_at,
						'updated_at'=>$updated_at,
						'type' => 'notification',
						'seller_array' => $seller_array
					];
				}
			}
		$final_arr = array_merge($post_array,$notification_array);
		$response['status'] = 200;
		$response['message'] = 'completed deal buyer';
		$response['data'] = $final_arr;

		return response($response, 200);
	}
	public function search_seller(Request $request){
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_type = isset($content->user_type) ? $content->user_type : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';

		$seller_array =[];
		$seller = UserDetails::where(['user_type'=>'seller','country_id'=>$country_id,'state_id'=>$state_id,'city_id'=>$city_id,'station_id'=>$station_id])->get();
		if(count($seller)>0){
			foreach ($seller as $value) {
				$country = Country::where(['id'=>$value->country_id,'is_delete'=>1])->first();
						$country_name = '';
						if(!empty($country)){
							$country_name = $country->name;
						}
					$state = State::where(['id'=>$value->state_id,'is_delete'=>1])->first();
						$state_name = '';
						if(!empty($state)){
							$state_name = $state->name;
						}
					$city = City::where(['id'=>$value->city_id,'is_delete'=>1])->first();
						$city_name = '';
						if(!empty($city)){
							$city_name = $city->name;
						}
					$station = Station::where(['id'=>$value->station_id,'is_delete'=>1])->first();
						$station_name = '';
						if(!empty($station)){
							$station_name = $station->name;
						}
				$seller_data = Sellers::where(['id'=>$value->user_id,'is_active'=>1,'is_delete'=>1,'is_approve'=>1])->first();
				if(!empty($seller_data)){
					$seller_array[] = [
						'seller_id' => $seller_data->id,
						'name' => $seller_data->name,
						'country' => $country_name,
						'state' => $state_name,
						'city' => $city_name,
						'station' => $station_name
					];
				}
			}
			$response['status'] = 200;
			$response['message'] = 'sellers';
			$response['data'] = $seller_array;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}
	public function search_buyer(Request $request){
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$user_type = isset($content->user_type) ? $content->user_type : '';
		$country_id = isset($content->country_id) ? $content->country_id : '';
		$state_id = isset($content->state_id) ? $content->state_id : '';
		$city_id = isset($content->city_id) ? $content->city_id : '';
		$station_id = isset($content->station_id) ? $content->station_id : '';

		$buyer_array =[];
		$buyer = UserDetails::where(['user_type'=>'buyer','country_id'=>$country_id,'state_id'=>$state_id,'city_id'=>$city_id]);
		if(!empty($station_id) && $station_id != 0){
			$buyer->where('station_id',$station_id);
		}
		$buyer = $buyer->get();
		if(count($buyer)>0){
			foreach ($buyer as $value) {
				$country = Country::where(['id'=>$value->country_id,'is_delete'=>1])->first();
						$country_name = '';
						if(!empty($country)){
							$country_name = $country->name;
						}
					$state = State::where(['id'=>$value->state_id,'is_delete'=>1])->first();
						$state_name = '';
						if(!empty($state)){
							$state_name = $state->name;
						}
					$city = City::where(['id'=>$value->city_id,'is_delete'=>1])->first();
						$city_name = '';
						if(!empty($city)){
							$city_name = $city->name;
						}
					$station = Station::where(['id'=>$value->station_id,'is_delete'=>1])->first();
						$station_name = '';
						if(!empty($station)){
							$station_name = $station->name;
						}
				$buyer_data = Buyers::where(['id'=>$value->user_id,'is_active'=>1,'is_delete'=>1,'is_approve'=>1])->first();
				if(!empty($buyer_data)){
					$buyer_array[] = [
						'buyer_id' => $buyer_data->id,
						'name' => $buyer_data->name,
						'country' => $country_name,
						'state' => $state_name,
						'city' => $city_name,
						'station' => $station_name
					];
				}
			}
			$response['status'] = 200;
			$response['message'] = 'buyers';
			$response['data'] = $buyer_array;
		}else{
			$response['status'] = 404;
		}
		return response($response, 200);
	}

	public function my_contract(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

		$final_arr = [];
		$dates = [];
        $negotiation_ids = [];

		if($user_type == "seller"){
		    $make_deals = NegotiationComplete::where(['seller_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));

                    $negotiation = Negotiation::select('negotiation_complete_id')->where('negotiation_complete_id', $make_deal->id)->get()->first();
                    if (!empty($negotiation)) {
                        $negotiation_ids = array_column($negotiation,'negotiation_complete_id');
                    }
				}
			}
            // dd($negotiation_ids);

			$unique_date = array_unique($dates);
			foreach ($unique_date as $date) {

                if (!empty($negotiation_ids)) {
                    $negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('seller_id',$seller_buyer_id)->whereNotIn('id',$negotiation_ids)->orderBy('id','DESC')->get();
                } else {
                    $negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('seller_id',$seller_buyer_id)->orderBy('id','DESC')->get();
                }

				// $negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('seller_id',$seller_buyer_id)->where(function($query) {
                    // $query->where('lab_report_status','pass')
                    // ->orWhere('lab_report_status',NULL);
                    // })->orderBy('id','DESC')->get();
                    foreach ($negotiation_data as $value) {

                        $negotiation_debit_file = [];
                        $debit_note = NegotiationDebitNote::select('file_name')->where('negotiation_complete_id',$value->id)->get();
                        if(!empty($debit_note)){
                            foreach($debit_note as $val){
                                $_file = storage_path('app/public/content_images/' . $val->file_name);
                                if (File::exists($_file) && !empty($val->file_name)) {
                                    $negotiation_debit_file [] = [
                                        'file_name' => asset('storage/app/public/content_images/' . $val->file_name),
                                    ];
                                }
                            }
                        }
                        $dates = date('d-m-Y', strtotime($value->updated_at));

					if($value->negotiation_type=="post"){
							$post = Post::where('id',$value->post_notification_id)->first();
							if(!empty($post)){
								$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = PostDetails::where('post_id',$post->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($post->price)){
									$post_price = $post->price;
								}
								$post_no_of_bales = '';
								if(!empty($post->no_of_bales)){
									$post_no_of_bales = $post->no_of_bales;
								}
								$post_date ='';
								if(!empty($post->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
							$broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
							$is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
								$is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}

						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
							'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
							'debit_note_array' =>  $negotiation_debit_file,
							'is_dispatch' =>  $is_dispatch,
							'is_sample' =>  $value->is_sample,
							'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
					if($value->negotiation_type=="notification"){
							$notification = Notification::where('id',$value->post_notification_id)->first();
							if(!empty($notification)){
								$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($notification->price)){
									$post_price = $notification->price;
								}
								$post_no_of_bales = '';
								if(!empty($notification->no_of_bales)){
									$post_no_of_bales = $notification->no_of_bales;
								}
								$post_date ='';
								if(!empty($notification->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($notification->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
                            $broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
                            $is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
                                $is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}

						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
                            'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
                            'debit_note_array' =>  $negotiation_debit_file,
                            'is_dispatch' =>  $is_dispatch,
                            'is_sample' =>  $value->is_sample,
                            'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
				}
			}
		}
		if($user_type == "buyer"){
		    // $make_deals = NegotiationComplete::where(['buyer_id'=>$seller_buyer_id])->skip($offset)->take($limit)->get();
		    $make_deals = NegotiationComplete::where(['buyer_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));
				}
			}
			$unique_date = array_unique($dates);

			foreach ($unique_date as $date) {

				$negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('buyer_id',$seller_buyer_id)->whereNotIn('id',$negotiation_ids)->orderBy('id','DESC')->get();

				foreach ($negotiation_data as $value) {

                    $negotiation_debit_file = [];
                    $debit_note = NegotiationDebitNote::select('file_name')->where('negotiation_complete_id',$value->id)->get();
                    if(!empty($debit_note)){
                        foreach($debit_note as $val){

                            $_file = storage_path('app/public/content_images/' . $val->file_name);
                            if (File::exists($_file) && !empty($val->file_name)) {
                                $negotiation_debit_file [] = [
                                     'file' => asset('storage/app/public/content_images/' . $val->file_name),
                                ];
                            }
                        }
                    }

					$dates = date('d-m-Y', strtotime($value->updated_at));

					if($value->negotiation_type=="post"){
							$post = Post::where('id',$value->post_notification_id)->first();
							if(!empty($post)){
								$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = PostDetails::where('post_id',$post->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($post->price)){
									$post_price = $post->price;
								}
								$post_no_of_bales = '';
								if(!empty($post->no_of_bales)){
									$post_no_of_bales = $post->no_of_bales;
								}
								$post_date ='';
								if(!empty($post->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
                            $broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
                            $is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
                                $is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}


						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
                            'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
                            'debit_note_array' =>  $negotiation_debit_file,
                            'is_sample' =>  $value->is_sample,
                            'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
					if($value->negotiation_type=="notification"){
							$notification = Notification::where('id',$value->post_notification_id)->first();
							if(!empty($notification)){
								$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($notification->price)){
									$post_price = $notification->price;
								}
								$post_no_of_bales = '';
								if(!empty($notification->no_of_bales)){
									$post_no_of_bales = $notification->no_of_bales;
								}
								$post_date ='';
								if(!empty($notification->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($notification->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
                            $broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
                            $is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
                                $is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}

						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
                            'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
                            'debit_note_array' =>  $negotiation_debit_file,
                            'is_dispatch' =>  $is_dispatch,
                            'is_sample' =>  $value->is_sample,
                            'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
				}
			}
		}
        if($user_type == "broker"){
		    // $make_deals = NegotiationComplete::where(['broker_id'=>$seller_buyer_id])->skip($offset)->take($limit)->get();
		    $make_deals = NegotiationComplete::where(['broker_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));
				}
			}
			$unique_date = array_unique($dates);
			foreach ($unique_date as $date) {

				$negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('broker_id',$seller_buyer_id)->whereNotIn('id',$negotiation_ids)->orderBy('id','DESC')->get();
				foreach ($negotiation_data as $value) {
					$dates = date('d-m-Y', strtotime($value->updated_at));

                    $negotiation_debit_file = [];
                    $debit_note = NegotiationDebitNote::select('file_name')->where('negotiation_complete_id',$value->id)->get();
                    if(!empty($debit_note)){
                        foreach($debit_note as $val){

                            $_file = storage_path('app/public/content_images/' . $val->file_name);
                            if (File::exists($_file) && !empty($val->file_name)) {
                                $negotiation_debit_file [] = [
                                     'file' => asset('storage/app/public/content_images/' . $val->file_name),
                                ];
                            }
                        }
                    }

					if($value->negotiation_type=="post"){
							$post = Post::where('id',$value->post_notification_id)->first();
							if(!empty($post)){
								$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = PostDetails::where('post_id',$post->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($post->price)){
									$post_price = $post->price;
								}
								$post_no_of_bales = '';
								if(!empty($post->no_of_bales)){
									$post_no_of_bales = $post->no_of_bales;
								}
								$post_date ='';
								if(!empty($post->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
                            $broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
                            $is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
                                $transmit_condition_name = $transmit_condition->name;
                                $is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}

						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
                            'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
                            'debit_note_array' =>  $negotiation_debit_file,
                            'is_dispatch' => $is_dispatch,
                            'is_sample' =>  $value->is_sample,
                            'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
					if($value->negotiation_type=="notification"){
							$notification = Notification::where('id',$value->post_notification_id)->first();
							if(!empty($notification)){
								$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($notification->price)){
									$post_price = $notification->price;
								}
								$post_no_of_bales = '';
								if(!empty($notification->no_of_bales)){
									$post_no_of_bales = $notification->no_of_bales;
								}
								$post_date ='';
								if(!empty($notification->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($notification->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
                            $broker_name = '';
							$broker = Brokers::where('id',$value->broker_id)->first();
							if(!empty($broker)){
								$broker_name = $broker->name;
							}
							$transmit_condition_name = '';
                            $is_dispatch = 0;
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
                                $is_dispatch = $transmit_condition->is_dispatch;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
							$url = '';
							$url = DealPdf::where('deal_id',$value->id)->first();
							if(!empty($url)){
								if (!empty($url->filename)) {

									$filename = storage_path('app/public/pdf/' . $url->filename);

									if (File::exists($filename)) {
										$url = asset('storage/app/public/pdf/' . $url->filename);
									} else {
										$url = '';
									}
								}
							}

						$lab_report = '';
						$lab_report_file = storage_path('app/public/transaction_tracking/' . $value->lab_report);
						if (File::exists($lab_report_file) && !empty($value->lab_report)) {
							$lab_report = asset('storage/app/public/transaction_tracking/' . $value->lab_report);
						}

						$transmit_deal = '';
						$transmit_deal_file = storage_path('app/public/transaction_tracking/' . $value->transmit_deal);
						if (File::exists($transmit_deal_file) && !empty($value->transmit_deal)) {
							$transmit_deal = asset('storage/app/public/transaction_tracking/' . $value->transmit_deal);
						}

						$without_gst = '';
						$without_gst_file = storage_path('app/public/transaction_tracking/' . $value->without_gst);
						if (File::exists($without_gst_file) && !empty($value->without_gst)) {
							$without_gst = asset('storage/app/public/transaction_tracking/' . $value->without_gst);
						}

						$gst_reciept = '';
						$gst_reciept_file = storage_path('app/public/transaction_tracking/' . $value->gst_reciept);
						if (File::exists($gst_reciept_file) && !empty($value->gst_reciept)) {
							$gst_reciept = asset('storage/app/public/transaction_tracking/' . $value->gst_reciept);
						}

						$final_arr[$dates][]  = [
							'deal_id' => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
							'buyer_id' => $value->buyer_id,
							'buyer_name' =>$buyer_name,
							'seller_id' => $value->seller_id,
							'seller_name' => $seller_name,
                            'broker_id' => $value->broker_id,
							'broker_name' => $broker_name,
							'negotiation_by' => $value->negotiation_by,
							'negotiation_type' => $value->negotiation_type,
							'post_price' => $post_price,
							'post_bales' => $post_no_of_bales,
							'sell_bales' => $value->no_of_bales,
							'sell_price' => $value->price,
							'payment_condition' => $payment_condition_name,
							'transmit_condition' => $transmit_condition_name,
							'lab' => $lab_name,
							'lab_report' => $lab_report,
							'transmit_deal' => $transmit_deal,
							'without_gst' => $without_gst,
							'gst_reciept' => $gst_reciept,
							'lab_report_mime' => !empty($value->lab_report_mime) ? $value->lab_report_mime : '',
							'transmit_deal_mime' => !empty($value->transmit_deal_mime) ? $value->transmit_deal_mime : '',
							'without_gst_mime' => !empty($value->without_gst_mime) ? $value->without_gst_mime : '',
							'gst_reciept_mime' => !empty($value->gst_reciept_mime) ? $value->gst_reciept_mime : '',
							'product_name' => $product_name,
							'attribute_array' => $attribute_array,
							'url'=>$url,
							'lab_report_status' => $value->lab_report_status,
                            'debit_note_array' =>  $negotiation_debit_file,
                            'is_dispatch' => $is_dispatch,
                            'is_sample' =>  $value->is_sample,
                            'is_seller_otp_verify' =>  $value->is_seller_otp_verify,
							'is_buyer_otp_verify' =>  $value->is_buyer_otp_verify,
							'is_broker_otp_verify' =>  $value->is_broker_otp_verify,
						];
					}
				}
			}
		}
		$test_arr = [];
		foreach ($final_arr as $key => $value) {
			$test_arr[] = [
				'deal_date' => $key,
				'deal_details' => $value
			];
		}
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = $test_arr;
		return response($response, 200);
	}

	public function my_contract_filter(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
		$time_duration = isset($content->time_duration) ? $content->time_duration : '';
		$date_to = isset($content->date_to) ? $content->date_to : '';
		$date_from = isset($content->date_from) ? $content->date_from : '';
		$seller_buyer = isset($content->seller_buyer) ? $content->seller_buyer : '';

        $params = [
			'seller_buyer' => $seller_buyer,
		];

		$validator = Validator::make($params, [
            'seller_buyer' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$final_arr = [];
		$dates = [];
		if($user_type == "seller"){
				if($time_duration == "monthly"){
					$start_date = date('Y-m-01'); // hard-coded '01' for first day
               		$end_date  = date('Y-m-t');
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$start_date,$end_date])->where('seller_id',$seller_buyer_id)->whereIn('buyer_id',$seller_buyer)->get();
				}
				if($time_duration == "custom"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$date_to,$date_from])->where('seller_id',$seller_buyer_id)->whereIn('buyer_id',$seller_buyer)->get();
				}
				if($time_duration == "weekly"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('seller_id',$seller_buyer_id)->whereIn('buyer_id',$seller_buyer)->get();
				}
				foreach ($negotiation_data as $value) {
					$dates = date('d-m-Y', strtotime($value->updated_at));
					if($value->negotiation_type=="post"){
							$post = Post::where('id',$value->post_notification_id)->first();
							if(!empty($post)){
								$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = PostDetails::where('post_id',$post->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($post->price)){
									$post_price = $post->price;
								}
								$post_no_of_bales = '';
								if(!empty($post->no_of_bales)){
									$post_no_of_bales = $post->no_of_bales;
								}
								$sold_bales ='';
								if(!empty($post->sold_bales)){
									$sold_bales = $post->sold_bales;
								}
								$post_date ='';
								if(!empty($post->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
							$transmit_condition_name = '';
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
						$final_arr[$dates][]  = [
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
								'buyer_id' => $value->buyer_id,
								'buyer_name' =>$buyer_name,
								'seller_id' => $value->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $value->negotiation_by,
								'negotiation_type' => $value->negotiation_type,
								'post_price' => $post_price,
								'post_bales' => $post_no_of_bales,
								'sell_bales' => $sold_bales,
								'sell_price' => $value->price,
								'payment_condition' => $payment_condition_name,
								'transmit_condition' => $transmit_condition_name,
								'lab' => $lab_name,
								'product_name' => $product_name,
								'attribute_array' => $attribute_array
						];
					}
					if($value->negotiation_type=="notification"){
							$notification = Notification::where('id',$value->post_notification_id)->first();
							if(!empty($notification)){
								$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($notification->price)){
									$post_price = $notification->price;
								}
								$post_no_of_bales = '';
								if(!empty($notification->no_of_bales)){
									$post_no_of_bales = $notification->no_of_bales;
								}
								$sold_bales ='';
								if(!empty($notification->sold_bales)){
									$sold_bales = $notification->sold_bales;
								}
								$post_date ='';
								if(!empty($notification->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($notification->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
							$transmit_condition_name = '';
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
						$final_arr[$dates][]  = [
							'post_notification_id' => $value->post_notification_id,
								'post_date' => $post_date,
								'buyer_id' => $value->buyer_id,
								'buyer_name' =>$buyer_name,
								'seller_id' => $value->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $value->negotiation_by,
								'negotiation_type' => $value->negotiation_type,
								'post_price' => $post_price,
								'post_bales' => $post_no_of_bales,
								'sell_bales' => $sold_bales,
								'sell_price' => $value->price,
								'payment_condition' => $payment_condition_name,
								'transmit_condition' => $transmit_condition_name,
								'lab' => $lab_name,
								'product_name' => $product_name,
								'attribute_array' => $attribute_array
						];
					}
				}
			}
		if($user_type == "buyer"){
				if($time_duration == "monthly"){
					$start_date = date('Y-m-01'); // hard-coded '01' for first day
               		$end_date  = date('Y-m-t');
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$start_date,$end_date])->where('buyer_id',$seller_buyer_id)->whereIn('seller_id',$seller_buyer)->get();
				}
				if($time_duration == "custom"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$date_to,$date_from])->where('buyer_id',$seller_buyer_id)->whereIn('seller_id',$seller_buyer)->get();
				}
				if($time_duration == "weekly"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('buyer_id',$seller_buyer_id)->whereIn('seller_id',$seller_buyer)->get();
				}

				foreach ($negotiation_data as $value) {
					$dates = date('d-m-Y', strtotime($value->updated_at));

					if($value->negotiation_type=="post"){
							$post = Post::where('id',$value->post_notification_id)->first();
							if(!empty($post)){
								$product = Product::where(['id'=>$post->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = PostDetails::where('post_id',$post->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($post->price)){
									$post_price = $post->price;
								}
								$post_no_of_bales = '';
								if(!empty($post->no_of_bales)){
									$post_no_of_bales = $post->no_of_bales;
								}
								$sold_bales ='';
								if(!empty($post->sold_bales)){
									$sold_bales = $post->sold_bales;
								}
								$post_date ='';
								if(!empty($post->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
							$transmit_condition_name = '';
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
						$final_arr[$dates][]  = [
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
								'buyer_id' => $value->buyer_id,
								'buyer_name' =>$buyer_name,
								'seller_id' => $value->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $value->negotiation_by,
								'negotiation_type' => $value->negotiation_type,
								'post_price' => $post_price,
								'post_bales' => $post_no_of_bales,
								'sell_bales' => $sold_bales,
								'sell_price' => $value->price,
								'payment_condition' => $payment_condition_name,
								'transmit_condition' => $transmit_condition_name,
								'lab' => $lab_name,
								'product_name' => $product_name,
								'attribute_array' => $attribute_array
						];
					}
					if($value->negotiation_type=="notification"){
							$notification = Notification::where('id',$value->post_notification_id)->first();
							if(!empty($notification)){
								$product = Product::where(['id'=>$notification->product_id,'is_delete'=>0])->first();
								$product_name = '';
								if(!empty($product)){
									$product_name = $product->name;
								}
								$attribute_array = [];
								$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
								foreach ($attribute as $val) {
									$attribute_array[] = [
										'id' => $val->id,
										'attribute' => $val->attribute,
										'attribute_value' => $val->attribute_value,
									];
								}
								$post_price = '';
								if(!empty($notification->price)){
									$post_price = $notification->price;
								}
								$post_no_of_bales = '';
								if(!empty($notification->no_of_bales)){
									$post_no_of_bales = $notification->no_of_bales;
								}
								$sold_bales ='';
								if(!empty($notification->sold_bales)){
									$sold_bales = $notification->sold_bales;
								}
								$post_date ='';
								if(!empty($notification->created_at)){
									$post_date = date('d-m-Y, h:i a', strtotime($notification->created_at));
								}
							}
							$seller_name = '';
							$seller = Sellers::where('id',$value->seller_id)->first();
							if(!empty($seller)){
								$seller_name = $seller->name;
							}
							$buyer_name = '';
							$buyer = Buyers::where('id',$value->buyer_id)->first();
							if(!empty($buyer)){
								$buyer_name = $buyer->name;
							}
							$transmit_condition_name = '';
							$transmit_condition = TransmitCondition::where('id',$value->transmit_condition)->first();
							if(!empty($transmit_condition)){
								$transmit_condition_name = $transmit_condition->name;
							}
							$payment_condition_name = '';
							$payment_condition = PaymentCondition::where('id',$value->payment_condition)->first();
							if(!empty($payment_condition)){
								$payment_condition_name = $payment_condition->name;
							}
							$lab_name = '';
							$lab = Lab::where('id',$value->lab)->first();
							if(!empty($lab)){
								$lab_name = $lab->name;
							}
						$final_arr[$dates][]  = [
							'post_notification_id' => $value->post_notification_id,
							'post_date' => $post_date,
								'buyer_id' => $value->buyer_id,
								'buyer_name' =>$buyer_name,
								'seller_id' => $value->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $value->negotiation_by,
								'negotiation_type' => $value->negotiation_type,
								'post_price' => $post_price,
								'post_bales' => $post_no_of_bales,
								'sell_bales' => $sold_bales,
								'sell_price' => $value->price,
								'payment_condition' => $payment_condition_name,
								'transmit_condition' => $transmit_condition_name,
								'lab' => $lab_name,
								'product_name' => $product_name,
								'attribute_array' => $attribute_array
						];
					}
				}
			}
		$test_arr = [];
		foreach ($final_arr as $key => $value) {
			$test_arr[] = [
				'deal_date' => $key,
				'deal_details' => $value
			];
		}
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = $test_arr;
		return response($response, 200);
	}

    public function my_contract_list(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

		$final_arr = [];

        if($user_type == "seller"){
            $make_deals = NegotiationComplete::with('buyer')->where(['seller_id'=>$seller_buyer_id])->skip($offset)->take($limit)->get();

            if(!empty($make_deals) && count($make_deals) > 0){
                foreach($make_deals as $val){
                    $final_arr [] = [
                        'buyer_id' => $val->buyer_id,
                        'buyer_name' => $val->buyer->name
                    ];
                }
            }

        }else if($user_type == "buyer"){
            $make_deals = NegotiationComplete::with('seller')->where(['buyer_id'=>$seller_buyer_id])->skip($offset)->take($limit)->get();

            if(!empty($make_deals) && count($make_deals) > 0){
                foreach($make_deals as $val){
                    $final_arr [] = [
                        'seller_id' => $val->seller_id,
                        'seller_name' => $val->seller->name
                    ];
                }
            }
        }

		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = $final_arr;
		return response($response, 200);
	}

	public function search_to_sell_new(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		// $required = isset($content->required) ? $content->required : '';
		$non_required = isset($content->non_required) ? $content->non_required : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];

        // $cnt_required = count($required);
        // if (!empty($required)) {
        //     $temp = 0;
        //     foreach ($required as $val) {
        //     	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0])->where('tbl_post_details.attribute',$val->attribute)->where('tbl_post_details.attribute_value', $val->attribute_value)->get();
        //     	 if(count($query)>0){
        //     	 	$temp++;
        //     	 }
        //     }
        //     if($cnt_required == $temp){
        //     	 foreach ($query as $value) {
	    //             $post_arr[] = $value->id;
	    //             $country_arr[] = $value->user_detail->country->id;
	    //             $state_arr[] = $value->user_detail->state->id;
	    //             $city_arr[] = $value->user_detail->city->id;
	    //             $station_arr[] = $value->user_detail->station->id;
	    //         }
        //     }
        // }

        if(!empty($non_required)){
        	foreach ($non_required as $key => $val) {
        		$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0])->where('tbl_post_details.attribute',$val->attribute)->whereBetween('tbl_post_details.attribute_value', [$val->from,$val->to])->get();

	        	if(count($query)>0){
	            	 foreach ($query as $value) {
		                $post_arr[] = $value->id;
		                $country_arr[] = $value->user_detail->country->id;
		                $state_arr[] = $value->user_detail->state->id;
		                $city_arr[] = $value->user_detail->city->id;
		                $station_arr[] = $value->user_detail->station->id;
		            }
	            }
        	}
        }
        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);
			$search_array = [];
            foreach($country_arr as $country_val) {
                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.buyer')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {

                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->buyer->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to sell';
        $response['data'] = $final_arr;

		return response($response, 200);
    }

    public function search_to_buy_new(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		// $required = isset($content->required) ? $content->required : '';
		$non_required = isset($content->non_required) ? $content->non_required : '';

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];

		// $cnt_required = count($required);
        // if (!empty($required)) {
        //     $temp = 0;
        //     foreach ($required as $val) {
        //     	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->get();
        //     	 if(count($query)>0){
        //     	 	$temp++;
        //     	 }
        //     }
        //     if($cnt_required == $temp){
        //     	 foreach ($query as $value) {
	    //             $post_arr[] = $value->id;
	    //             $country_arr[] = $value->user_detail->country->id;
	    //             $state_arr[] = $value->user_detail->state->id;
	    //             $city_arr[] = $value->user_detail->city->id;
	    //             $station_arr[] = $value->user_detail->station->id;
	    //         }
        //     }
        // }

        if(!empty($non_required)){
        	foreach ($non_required as $key => $val) {
        		$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute])->whereBetween('tbl_post_details.attribute_value', [$val->from,$val->to])->get();

	        	if(count($query)>0){
	            	 foreach ($query as $value) {
		                $post_arr[] = $value->id;
		                $country_arr[] = $value->user_detail->country->id;
		                $state_arr[] = $value->user_detail->state->id;
		                $city_arr[] = $value->user_detail->city->id;
		                $station_arr[] = $value->user_detail->station->id;
		            }
	            }
        	}
        }

        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);

			$search_array = [];
            foreach($country_arr as $country_val) {

                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.seller')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name', 'remain_bales')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {

                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->seller->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'remaining_bales' => $station_result_val->remain_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to buy';
        $response['data'] = $final_arr;

		return response($response, 200);

	}

	public function notification_post_buy_list(Request $request)
	{
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

		$notification_to_buy_list = [];
		$post_to_buy_list = [];
		$final_arr = [];
		$post = Post::whereHas('product', function($q){
                $q->where(['is_delete'=>0]);
            })->where(['seller_buyer_id'=>$buyer_id,'is_active'=>0,'user_type'=>'buyer'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($post)>0){
			foreach ($post as $value) {
					// $product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($value->product->name)){
						$product_name = $value->product->name;
					}
					$attribute_array = [];
					$attribute = PostDetails::where('post_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'post_id' => $val->post_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$post_to_buy_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'type' => 'post',
					'remaining_bales' => $value->remain_bales,
					'date' => $created_at,
					'attribute_array' => $attribute_array,
				];
			}
		}
		$notification = Notification::where(['seller_buyer_id'=>$buyer_id,'is_active'=>0,'user_type'=>'buyer'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($notification)>0){
			foreach ($notification as $value) {
					$product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($product)){
						$product_name = $product->name;
					}
					$attribute_array = [];
					$attribute = NotificatonDetails::where('notification_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'notification_id' => $val->notification_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$seller_array = [];
					$seller_data = SelectionSellerBuyer::where('notification_id',$value->id)->get();
					foreach ($seller_data as $val2) {
						$name = Sellers::where('id',$val2->seller_buyer_id)->first();
						if(!empty($name)){
						$city_data = UserDetails::where(['user_id'=>$val2->seller_buyer_id,'user_type'=>'seller'])->first();
						if(!empty($city_data)){
						$city_name = City::where('id',$city_data->city_id)->first();
								if(!empty($city_name))
								$seller_array[] = [
									'name' => $name->name,
									'city' => $city_name->name
								];
							}
						}
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$notification_to_buy_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'type' => 'notification',
					'date' => $created_at,
                    'remaining_bales' => 0,
					'attribute_array' => $attribute_array,
					'seller_array' => $seller_array
				];
			}
		}
		$final_arr = array_merge($post_to_buy_list,$notification_to_buy_list);

		$response['status'] = 200;
		$response['message'] = 'list';
		$response['data'] = $final_arr;

		return response($response, 200);
	}
	public function notification_post_seller_list(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';


		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$post_to_sell_list = [];
		$notification_to_sell_list = [];
		$final_arr = [];
		$post = Post::whereHas('product', function($q){
            $q->where(['is_delete'=>0]);
        })->where(['seller_buyer_id'=>$seller_id,'is_active'=>0,'user_type'=>'seller'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($post)>0){
			foreach ($post as $value) {
					// $product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($value->product->name)){
						$product_name = $value->product->name;
					}
					$attribute_array = [];
					$attribute = PostDetails::where('post_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'post_id' => $val->post_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$post_to_sell_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'address' => $value->address,
					'date' => $created_at,
					'type' => 'post',
					'remaining_bales' => $value->remain_bales,
					'attribute_array' => $attribute_array,
				];
			}
		}
		$notification = Notification::where(['seller_buyer_id'=>$seller_id,'is_active'=>0,'user_type'=>'seller'])->where('status','active')->orderBy('id', 'DESC')->get();
		if(count($notification)>0){
			foreach ($notification as $value) {
					$product = Product::where(['id'=>$value->product_id,'is_delete'=>0])->first();
					$product_name = '';
					if(!empty($product)){
						$product_name = $product->name;
					}
					$attribute_array = [];
					$attribute = NotificatonDetails::where('notification_id',$value->id)->get();
					foreach ($attribute as $val) {
						$attribute_array[] = [
							'id' => $val->id,
							'notification_id' => $val->notification_id,
							'attribute' => $val->attribute,
							'attribute_value' => $val->attribute_value,
						];
					}
					$buyer_array = [];
					$buyer_data = SelectionSellerBuyer::where('notification_id',$value->id)->get();
					foreach ($buyer_data as $val2) {
						$name = Buyers::where('id',$val2->seller_buyer_id)->first();
						if(!empty($name)){
						$city_data = UserDetails::where(['user_id'=>$val2->seller_buyer_id,'user_type'=>'buyer'])->first();
						if(!empty($city_data)){
						$city_name = City::where('id',$city_data->city_id)->first();
								if(!empty($city_name))
								$buyer_array[] = [
									'name' => $name->name,
									'city' => $city_name->name
								];
							}
						}
					}
					$created_at = date('d-m-Y, h:i a', strtotime($value->created_at));
					$notification_to_sell_list[] = [
					'id' => $value->id,
					'seller_buyer_id' => $value->seller_buyer_id,
					'user_type' => $value->user_type,
					'product_id' => $value->product_id,
					'product_name' => $product_name,
					'no_of_bales' => $value->no_of_bales,
					'price' => $value->price,
					'type' => 'notification',
					'date' => $created_at,
                    'remaining_bales' => 0,
					'attribute_array' => $attribute_array,
					'buyer_array' => $buyer_array,
				];
			}
		}
		$final_arr = array_merge($post_to_sell_list,$notification_to_sell_list);
		$response['status'] = 200;
		$response['message'] = 'list';
		$response['data'] = $final_arr;

		return response($response, 200);
	}

	public function update_transaction_tracking(Request $request)
    {
		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$upload_by = isset($content->upload_by) ? $content->upload_by : '';
		$sample = isset($content->sample) ? $content->sample : '';

		$params = [
			'deal_id' => $deal_id,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }
    	$negotiation_comp = NegotiationComplete::find($deal_id);

        if ($files = $request->file('lab_report')) {
			$image_name = time() . '.' .$files->getClientOriginalName();
			$mime_type = $files->getMimeType();
			$files->move(storage_path('app/public/transaction_tracking/'), $image_name);

			$negotiation_comp->lab_report = $image_name;
			$negotiation_comp->lab_report_mime = $mime_type;
			$negotiation_comp->lab_report_upload_by = $upload_by;
			$negotiation_comp->save();
        }

		if ($files = $request->file('transmit_deal')) {
			$image_name = time() . '.' .$files->getClientOriginalName();
			$mime_type = $files->getMimeType();
			$files->move(storage_path('app/public/transaction_tracking/'), $image_name);

			$negotiation_comp->transmit_deal = $image_name;
			$negotiation_comp->transmit_deal_mime = $mime_type;
			$negotiation_comp->transmit_deal_upload_by = $upload_by;
			$negotiation_comp->save();
        }

		if ($files = $request->file('without_gst')) {
			$image_name = time() . '.' .$files->getClientOriginalName();
			$mime_type = $files->getMimeType();
			$files->move(storage_path('app/public/transaction_tracking/'), $image_name);

			$negotiation_comp->without_gst = $image_name;
			$negotiation_comp->without_gst_mime = $mime_type;
			$negotiation_comp->without_gst_upload_by = $upload_by;
			$negotiation_comp->save();
        }

		if($files = $request->file('gst_reciept')){
			$name = time() . '.' .$files->getClientOriginalName();
			$mime_type = $files->getMimeType();
			$files->move(storage_path('app/public/transaction_tracking/'), $name);

			$negotiation_comp->gst_reciept = $name;
			$negotiation_comp->gst_reciept_mime = $mime_type;
			$negotiation_comp->gst_reciept_upload_by = $upload_by;
			$negotiation_comp->save();

		}

		if ($files = $request->file('debit_note')) {
            $name = time() . '.' .$files->getClientOriginalName();
			$mime_type = $files->getMimeType();
			$files->move(storage_path('app/public/content_images/'), $name);

			$debit_note = new NegotiationDebitNote();
            $debit_note->negotiation_complete_id = $deal_id;
            $debit_note->file_name = $name;
            $debit_note->save();
		}

		if(isset($content->sample)){
			$negotiation_comp->is_sample = $sample;
            $negotiation_comp->save();
		}

        $deal_data = NegotiationComplete::where('id',$deal_id)->first();
        $fcm_token = "";
        $user = [];
        if ($upload_by == "seller") {
            $user_type = "buyer";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$deal_data->buyer_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Sellers::select('name')->where('id',$deal_data->seller_id)->first();
        }else if($upload_by == 'buyer'){
            $user_type = "seller";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$deal_data->seller_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Buyers::select('name')->where('id',$deal_data->buyer_id)->first();
        }

        if (!empty($fcm_token)) {
            $json_array = [
                "registration_ids" => [$fcm_token],
                "data" => [
                    'deal_id' => $deal_id,
                    'user_type' => $upload_by,
                ],
                "notification" => [
                    "body" => "Notification send by ".$user->name,
                    "title" => "Negotiation Notification",
                    "icon" => "ic_launcher"
                ]
            ];
            NotificationHelper::notification($json_array,$user_type);
        }

		$response['status'] = 200;
		$response['message'] = 'Updated Successfully';
		$response['data'] = (object)[];
		return response($response, 200);
	}

   public function negotiation_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$negotiation_type = isset($content->negotiation_type) ? $content->negotiation_type : '';
		$negotiation_by = isset($content->negotiation_by) ? $content->negotiation_by : '';
		$price = isset($content->price) ? $content->price : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$payment_condition = isset($content->payment_condition) ? $content->payment_condition : '';
		$transmit_condition = isset($content->transmit_condition) ? $content->transmit_condition : '';
		$lab = isset($content->lab) ? $content->lab : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';
		$header = isset($content->header) ? $content->header : '';
		$notes = isset($content->notes) ? $content->notes : '';
		$deal_id = isset($content->deal_id) ? $content->deal_id : '';

		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'broker_id' => $broker_id,
			'post_notification_id' => $post_notification_id,
			'negotiation_type' => $negotiation_type,
			'negotiation_by' => $negotiation_by,
			'price' => $price,
			'no_of_bales' => $no_of_bales,
			'payment_condition' => $payment_condition,
			'transmit_condition' => $transmit_condition,
			'lab' => $lab,
			// 'header' => $header,
			// 'notes' => $notes,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required|exists:tbl_sellers,id',
            'buyer_id' => 'required|exists:tbl_buyers,id',
            'broker_id' => 'required|exists:tbl_brokers,id',
            'post_notification_id' => 'required',
            'negotiation_type' => 'required',
            'negotiation_by' => 'required',
            'price' => 'required',
            'no_of_bales' => 'required',
            'payment_condition' => 'required|exists:tbl_payment_condition,id',
            'transmit_condition' => 'required|exists:tbl_transmit_condition,id',
            'lab' => 'required|exists:tbl_lab,id',
            // 'header' => 'required',
            // 'notes' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }

        if(!empty($deal_id)){
            $params = [
                'deal_id' => $deal_id,
            ];

            $validator = Validator::make($params, [
                'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            ]);

            if ($validator->fails()) {
                $response['status'] = 404;
                $response['message'] =$validator->errors()->first();
                return response($response, 200);
            }
        }

        // if ($negotiation_by == 'seller') {
            // $check_data = CommonHelper::check_user_amount($seller_id,'seller');
        // } else {
        	// $check_data = CommonHelper::check_user_amount($buyer_id,'buyer');
        // }

        // if (!$check_data['success']) {
            // $response['status'] = 404;
		    // $response['message'] = $check_data['message'];
            // return response($response, 200);
        // }
        $product_name = '';

		$broker_details = Brokers::where('id',$broker_id)->first();

	    if($negotiation_type == "post"){
	    	$post_remain = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();

            if ($post_remain->status == 'cancel') {
                $response['status'] = 404;
                $response['message'] = 'Sorry, Post is cancelled!';
                return response($response, 200);
            }

		   	if($no_of_bales > $post_remain->no_of_bales){
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}elseif ($no_of_bales > $post_remain->remain_bales) {
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}else{
		    	$negotiation = new Negotiation();
                $prev_price = $price;
                $prev_bales = $no_of_bales;
				$negotia = Negotiation::where(['post_notification_id'=>$post_notification_id,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id])->first();
                if(!empty($negotia)){
                    $negotiation = Negotiation::where('id',$negotia->id)->first();
                    $prev_price = $negotia->price;
                    $prev_bales = $negotia->bales;
                } else {
					$prev_price = $post_remain->price;
                    $prev_bales = $post_remain->no_of_bales;
				}
				$negotiation->seller_id = $seller_id;
				$negotiation->buyer_id = $buyer_id;
                $negotiation->negotiation_complete_id = $deal_id;
				$negotiation->post_notification_id = $post_notification_id;
				$negotiation->negotiation_type = $negotiation_type;
                $negotiation->negotiation_by = $negotiation_by;
                $negotiation->prev_price = $prev_price;
                $negotiation->prev_bales = $prev_bales;
                $negotiation->price = $price;
				$negotiation->bales = $no_of_bales;
                $negotiation->broker_id = $broker_id;
				$negotiation->notes = $notes;
				$negotiation->header = $header;
                $negotiation->payment_condition = $payment_condition;
                $negotiation->transmit_condition = $transmit_condition;
                $negotiation->lab = $lab;

				if($negotiation->save()){
					$negotiation_log = new NegotiationLog();
	                $negotiation_log->negotiation_id = $negotiation->id;
	                $negotiation_log->negotiation_by = $negotiation_by;
	                $negotiation_log->price = $price;
	                $negotiation_log->bales = $no_of_bales;
	                $negotiation_log->payment_condition_id = $payment_condition;
	                $negotiation_log->transmit_condition_id = $transmit_condition;
	                $negotiation_log->lab_id = $lab;
                    $negotiation_log->seller_id = $seller_id;
					$negotiation_log->buyer_id = $buyer_id;
					$negotiation_log->broker_id = $broker_id;
	                $negotiation_log->save();
					if($negotiation->negotiation_by == "seller"){
						$product_name = '';
						$best_bales = '';
						$best_dealer_name = '';
						$best_price = '';
						$base_price = '';
						$base_bales = '';
						$post_by='';
						$buyer_name = '';
						$seller_name = '';
						$sellers = [];
						$multiple_buyers = Negotiation::where(['post_notification_id'=>$post_notification_id,'negotiation_type' => $negotiation_type])->get();
						if(count($multiple_buyers)>1){
							$best_price = [];
							foreach ($multiple_buyers as $value) {
								array_push($sellers,$value->seller_id);
								$negotiation_log = NegotiationLog::select('negotiation_id','negotiation_by','price')->where(['negotiation_id'=>$value->id,'negotiation_by'=>'seller'])->orderBy('id','DESC')->first();
								if(!empty($negotiation_log)){
									array_push($best_price, $negotiation_log->price);
								}
							}
							$unique_seller = array_unique($sellers);
							if(count($unique_seller)>1){
                                if($negotiation->negotiation_type == "post"){
									$post = Post::select('price','no_of_bales','product_id','id')->where('id',$post_notification_id)->first();
									if(!empty($post)){
										$base_price = $post->price;
										$base_bales = $post->no_of_bales;
										$product = Product::select('name','id')->where('id',$post->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
									}
								}
								$best_dealer_data = NegotiationLog::select('price','id','bales','negotiation_id')->where('price',max($best_price))->first();
								if(!empty($best_dealer_data)){
									$best_bales = $best_dealer_data->bales;
									$best_price = $best_dealer_data->price;
									$negotiation_id = Negotiation::select('id','seller_id')->where('id',$best_dealer_data->negotiation_id)->first();
									if(!empty($negotiation_id)){
										$seller_data = Sellers::select('id','name')->where('id',$negotiation_id->seller_id)->first();
										if(!empty($seller_data)){
											$best_dealer_name = $seller_data->name;
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}

								$negotiationBuyerData = new Negotiation();
								$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationBuyerData->buyer_name = $buyer_name;
								$negotiationBuyerData->seller_id = $negotiation->seller_id;
								$negotiationBuyerData->seller_name = $seller_name;
								$negotiationBuyerData->post_notification_id = $post_notification_id;
								$negotiationBuyerData->broker_name = $broker_details->name;
								$negotiationBuyerData->negotiation_by = $negotiation_by;
								$negotiationBuyerData->negotiation_type = $negotiation_type;
								$negotiationBuyerData->prev_price = $negotiation->prev_price;
								$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationBuyerData->current_price = $price;
								$negotiationBuyerData->current_bales = $no_of_bales;
								$negotiationBuyerData->best_dealer_name = $best_dealer_name;
								$negotiationBuyerData->best_price = $best_price;
								$negotiationBuyerData->best_bales = $best_bales;
								$negotiationBuyerData->negotiation_count = count($multiple_buyers);
								event(new NegotiationBuyer($negotiationBuyerData));


								$negotiationMultipleBuyerData = new Negotiation();
								$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleBuyerData->buyer_name = $buyer_name;
								$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleBuyerData->seller_name = $seller_name;
								$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
								$negotiationMultipleBuyerData->broker_name = $broker_details->name;
								$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
								$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
								$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleBuyerData->current_price = $price;
								$negotiationMultipleBuyerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
							}else{
								if($negotiation->negotiation_type == "post"){
									$post = Post::select('id','product_id','seller_buyer_id','user_type')->where('id',$negotiation->post_notification_id)->first();
									if(!empty($post)){
										$product = Product::select('id','name')->where('id',$post->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
										if($post->user_type == "seller"){
											$seller = Sellers::select('id','name')->where('id',$post->seller_buyer_id)->first();
											if(!empty($seller)){
												$post_by = $seller->name;
											}
										}else{
											$buyer = Buyers::select('id','name')->where('id',$post->seller_buyer_id)->first();
											if(!empty($buyer)){
												$post_by = $buyer->name;
											}
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}

								$negotiationBuyerData = new Negotiation();
								$negotiationBuyerData->post_notification_id = $post_notification_id;
								$negotiationBuyerData->negotiation_type = $negotiation->negotiation_type;
								$negotiationBuyerData->broker_name = $broker_details->name;
								$negotiationBuyerData->seller_id = $negotiation->seller_id;
								$negotiationBuyerData->seller_name = $seller_name;
								$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationBuyerData->negotiation_by = $negotiation_by;
								$negotiationBuyerData->negotiation_type = $negotiation_type;
								$negotiationBuyerData->product_name = $product_name;
								$negotiationBuyerData->post_by = $post_by;
								$negotiationBuyerData->prev_price = $negotiation->prev_price;
								$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationBuyerData->new_price = $negotiation->price;
								$negotiationBuyerData->new_bales = $negotiation->bales;
								event(new NegotiationBuyer($negotiationBuyerData));

								$negotiationMultipleBuyerData = new Negotiation();
								$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleBuyerData->buyer_name = $buyer_name;
								$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleBuyerData->seller_name = $seller_name;
								$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
								$negotiationMultipleBuyerData->broker_name = $broker_details->name;
								$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
								$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
								$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleBuyerData->current_price = $price;
								$negotiationMultipleBuyerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
							}
						}else{
							if($negotiation->negotiation_type == "post"){
								$post = Post::select('id','product_id','seller_buyer_id','user_type')->where('id',$negotiation->post_notification_id)->first();
								if(!empty($post)){
									$product = Product::select('id','name')->where('id',$post->product_id)->first();
									if(!empty($product)){
										$product_name = $product->name;
									}
									if($post->user_type == "seller"){
										$seller = Sellers::select('id','name')->where('id',$post->seller_buyer_id)->first();
										if(!empty($seller)){
											$post_by = $seller->name;
										}
									}else{
										$buyer = Buyers::select('id','name')->where('id',$post->seller_buyer_id)->first();
										if(!empty($buyer)){
											$post_by = $buyer->name;
										}
									}
								}
							}
							if(!empty($negotiation->buyer_id)){
								$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
								if(!empty($buyer)){
									$buyer_name = $buyer->name;
								}
							}
							if(!empty($negotiation->seller_id)){
								$seller =  Sellers::where('id',$negotiation->seller_id)->first();
								if(!empty($seller)){
									$seller_name = $seller->name;
								}
							}

							$negotiationBuyerData = new Negotiation();
							$negotiationBuyerData->post_notification_id = $post_notification_id;
							$negotiationBuyerData->negotiation_type = $negotiation->negotiation_type;
							$negotiationBuyerData->broker_name = $broker_details->name;
							$negotiationBuyerData->seller_id = $negotiation->seller_id;
							$negotiationBuyerData->seller_name = $seller_name;
							$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
							$negotiationBuyerData->negotiation_by = $negotiation_by;
							$negotiationBuyerData->negotiation_type = $negotiation_type;
							$negotiationBuyerData->product_name = $product_name;
							$negotiationBuyerData->post_by = $post_by;
							$negotiationBuyerData->prev_price = $negotiation->prev_price;
							$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
							$negotiationBuyerData->new_price = $negotiation->price;
							$negotiationBuyerData->new_bales = $negotiation->bales;
							event(new NegotiationBuyer($negotiationBuyerData));

							$negotiationMultipleBuyerData = new Negotiation();
							$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
							$negotiationMultipleBuyerData->buyer_name = $buyer_name;
							$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
							$negotiationMultipleBuyerData->seller_name = $seller_name;
							$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
							$negotiationMultipleBuyerData->broker_name = $broker_details->name;
							$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
							$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
							$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
							$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
							$negotiationMultipleBuyerData->current_price = $price;
							$negotiationMultipleBuyerData->current_bales = $no_of_bales;
							event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
						}
					}else{
						$product_name = '';
						$best_bales = '';
						$best_dealer_name = '';
						$best_price = '';
						$base_price = '';
						$base_bales = '';
						$post_by='';
						$buyer_name = '';
						$seller_name = '';
						$buyers = [];
						$multiple_sellers = Negotiation::where(['post_notification_id'=> $post_notification_id,'negotiation_type' => $negotiation_type])->get();
						if(count($multiple_sellers)>1){
							$best_price = [];
							foreach ($multiple_sellers as $value) {
								array_push($buyers, $value->buyer_id);
								$negotiation_log = NegotiationLog::select('negotiation_id','negotiation_by','price')->where(['negotiation_id'=>$value->id,'negotiation_by'=>'buyer'])->orderBy('id','DESC')->first();
								if(!empty($negotiation_log)){
									array_push($best_price, $negotiation_log->price);
								}
							}
							$unique_buyers = array_unique($buyers);
							if(count($unique_buyers)>1){
								if($negotiation->negotiation_type == "post"){
									$post = Post::select('price','no_of_bales','product_id','id')->where('id',$post_notification_id)->first();
									if(!empty($post)){
										$base_price = $post->price;
										$base_bales = $post->no_of_bales;
										$product = Product::select('name','id')->where('id',$post->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
									}
								}
								$best_dealer_data = NegotiationLog::select('price','id','bales','negotiation_id')->where('price',max($best_price))->first();
								if(!empty($best_dealer_data)){
									$best_bales = $best_dealer_data->bales;
									$best_price = $best_dealer_data->price;
									$negotiation_id = Negotiation::select('id','buyer_id')->where('id',$best_dealer_data->negotiation_id)->first();
									if(!empty($negotiation_id)){
										$buyer_data = Buyers::select('id','name')->where('id',$negotiation_id->buyer_id)->first();
										if(!empty($buyer_data)){
											$best_dealer_name = $buyer_data->name;
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}
								$negotiationSellerData = new Negotiation();
								$negotiationSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationSellerData->buyer_name = $buyer_name;
								$negotiationSellerData->seller_id = $negotiation->seller_id;
								$negotiationSellerData->seller_name = $seller_name;
								$negotiationSellerData->post_notification_id = $post_notification_id;
								$negotiationSellerData->broker_name = $broker_details->name;
								$negotiationSellerData->negotiation_by = $negotiation_by;
								$negotiationSellerData->negotiation_type = $negotiation_type;
								$negotiationSellerData->prev_price = $negotiation->prev_price;
								$negotiationSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationSellerData->current_price = $price;
								$negotiationSellerData->current_bales = $no_of_bales;
								$negotiationSellerData->best_dealer_name = $best_dealer_name;
								$negotiationSellerData->best_price = $best_price;
								$negotiationSellerData->best_bales = $best_bales;
								$negotiationSellerData->negotiation_count = count($multiple_sellers);
								event(new NegotiationSeller($negotiationSellerData));


								$negotiationMultipleSellerData = new Negotiation();
								$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleSellerData->buyer_name = $buyer_name;
								$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleSellerData->seller_name = $seller_name;
								$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
								$negotiationMultipleSellerData->broker_name = $broker_details->name;
								$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
								$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
								$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleSellerData->current_price = $price;
								$negotiationMultipleSellerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
							}else{
								$post_notification = Post::where('id',$negotiation->post_notification_id)->first();
								if(!empty($post_notification)){
									$product = Product::where('id',$post_notification->product_id)->first();
									if(!empty($product)){
										$product_name = $product->name;
									}
									if($post_notification->user_type == "seller"){
										$seller = Sellers::where('id',$post_notification->seller_buyer_id)->first();
										if(!empty($seller)){
											$post_by = $seller->name;
										}
									}else{
										$buyer = Buyers::where('id',$post_notification->seller_buyer_id)->first();
										if(!empty($buyer)){
											$post_by = $buyer->name;
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}

								$negotiationSellerData = new Negotiation();
								$negotiationSellerData->broker_name = $broker_details->name;
								$negotiationSellerData->post_notification_id = $negotiation->post_notification_id;
								$negotiationSellerData->negotiation_type = $negotiation->negotiation_type;
								$negotiationSellerData->seller_id = $negotiation->seller_id;
								$negotiationSellerData->seller_name = $seller_name;
								$negotiationSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationSellerData->negotiation_by = $negotiation_by;
								$negotiationSellerData->negotiation_type = $negotiation_type;
								$negotiationSellerData->product_name = $product_name;
								$negotiationSellerData->post_by = $post_by;
								$negotiationSellerData->prev_price = $negotiation->prev_price;
								$negotiationSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationSellerData->new_price = $negotiation->price;
								$negotiationSellerData->new_bales = $negotiation->bales;
								event(new NegotiationSeller($negotiationSellerData));

								$negotiationMultipleSellerData = new Negotiation();
								$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleSellerData->buyer_name = $buyer_name;
								$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleSellerData->seller_name = $seller_name;
								$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
								$negotiationMultipleSellerData->broker_name = $broker_details->name;
								$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
								$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
								$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleSellerData->current_price = $price;
								$negotiationMultipleSellerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
							}
						}else{
							$post_notification = Post::where('id',$negotiation->post_notification_id)->first();
							if(!empty($post_notification)){
								$product = Product::where('id',$post_notification->product_id)->first();
								if(!empty($product)){
									$product_name = $product->name;
								}
								if($post_notification->user_type == "seller"){
									$seller = Sellers::where('id',$post_notification->seller_buyer_id)->first();
									if(!empty($seller)){
										$post_by = $seller->name;
									}
								}else{
									$buyer = Buyers::where('id',$post_notification->seller_buyer_id)->first();
									if(!empty($buyer)){
										$post_by = $buyer->name;
									}
								}
							}
							if(!empty($negotiation->buyer_id)){
								$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
								if(!empty($buyer)){
									$buyer_name = $buyer->name;
								}
							}
							if(!empty($negotiation->seller_id)){
								$seller =  Sellers::where('id',$negotiation->seller_id)->first();
								if(!empty($seller)){
									$seller_name = $seller->name;
								}
							}

							$negotiationSellerData = new Negotiation();
							$negotiationSellerData->broker_name = $broker_details->name;
							$negotiationSellerData->post_notification_id = $negotiation->post_notification_id;
							$negotiationSellerData->negotiation_type = $negotiation->negotiation_type;
							$negotiationSellerData->seller_id = $negotiation->seller_id;
							$negotiationSellerData->seller_name = $seller_name;
							$negotiationSellerData->buyer_id = $negotiation->buyer_id;
							$negotiationSellerData->negotiation_by = $negotiation_by;
							$negotiationSellerData->negotiation_type = $negotiation_type;
							$negotiationSellerData->product_name = $product_name;
							$negotiationSellerData->post_by = $post_by;
							$negotiationSellerData->prev_price = $negotiation->prev_price;
							$negotiationSellerData->prev_bales = $negotiation->prev_bales;
							$negotiationSellerData->new_price = $negotiation->price;
							$negotiationSellerData->new_bales = $negotiation->bales;
							event(new NegotiationSeller($negotiationSellerData));

							$negotiationMultipleSellerData = new Negotiation();
							$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
							$negotiationMultipleSellerData->buyer_name = $buyer_name;
							$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
							$negotiationMultipleSellerData->seller_name = $seller_name;
							$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
							$negotiationMultipleSellerData->broker_name = $broker_details->name;
							$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
							$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
							$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
							$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
							$negotiationMultipleSellerData->current_price = $price;
							$negotiationMultipleSellerData->current_bales = $no_of_bales;
							event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
						}
					}
					$response['status'] = 200;
					$response['message'] = 'Negotiation';
				}else{
					$response['status'] = 404;
				}
	    	}
	    }
	 	if($negotiation_type == "notification"){
	    	$notification_remain = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
		   	if($no_of_bales > $notification_remain->no_of_bales){
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}elseif ($no_of_bales > $notification_remain->remain_bales) {
		   		$response['status'] = 404;
		   		$response['message'] = 'Please Enter Less Bales';
		   	}else{
		    	$negotiation = new Negotiation();
                $prev_price = $price;
                $prev_bales = $no_of_bales;
				$negotia = Negotiation::where(['post_notification_id'=>$post_notification_id,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id])->first();
                if(!empty($negotia)){
                    $negotiation = Negotiation::where('id',$negotia->id)->first();
                    $prev_price = $negotia->price;
                    $prev_bales = $negotia->bales;
                }else{
                    $prev_price = $notification_remain->price;
                    $prev_bales = $notification_remain->no_of_bales;
                }
				$negotiation->seller_id = $seller_id;
				$negotiation->buyer_id = $buyer_id;
				$negotiation->post_notification_id = $post_notification_id;
				$negotiation->negotiation_type = $negotiation_type;
                $negotiation->negotiation_by = $negotiation_by;
                $negotiation->prev_price = $prev_price;
                $negotiation->prev_bales = $prev_bales;
                $negotiation->price = $price;
				$negotiation->bales = $no_of_bales;
                $negotiation->broker_id = $broker_id;
				$negotiation->notes = $notes;
				$negotiation->header = $header;

				// $negotia = Negotiation::where(['post_notification_id'=>$post_notification_id,'is_deal'=>0,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id])->get();
				// if(count($negotia)==0){
				// 	$post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
				// 	if(!empty($post)){
				// 		$negotiation->prev_price = $post->price;
				// 		$negotiation->prev_no_of_bales = $post->no_of_bales;
				// 	}else{
				// 		$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
				// 		if(!empty($notification)){
				// 			$negotiation->prev_price = $notification->price;
				// 			$negotiation->prev_no_of_bales = $notification->no_of_bales;
				// 		}
				// 	}
				// }else{
				// 	foreach ($negotia as $value) {
				// 		$negotiation->prev_price = $value->current_price;
				// 		$negotiation->prev_no_of_bales = $value->current_no_of_bales;
				// 	}
				// }
				// $negotiation->current_price = $price;
				// $negotiation->current_no_of_bales = $no_of_bales;
				// $negotiation->payment_condition = $payment_condition;
				// $negotiation->transmit_condition = $transmit_condition;
				// $negotiation->lab = $lab;

				if($negotiation->save()){
                    $negotiation_log = new NegotiationLog();
                    $negotiation_log->negotiation_id = $negotiation->id;
                    $negotiation_log->negotiation_by = $negotiation_by;
                    $negotiation_log->price = $price;
                    $negotiation_log->bales = $no_of_bales;
                    $negotiation_log->payment_condition_id = $payment_condition;
                    $negotiation_log->transmit_condition_id = $transmit_condition;
                    $negotiation_log->lab_id = $lab;
                    $negotiation_log->seller_id = $seller_id;
					$negotiation_log->buyer_id = $buyer_id;
					$negotiation_log->broker_id = $broker_id;
                    $negotiation_log->save();

                    if($negotiation->negotiation_by == "seller"){
						$product_name = '';
						$best_bales = '';
						$best_dealer_name = '';
						$best_price = '';
						$base_price = '';
						$base_bales = '';
						$post_by='';
						$buyer_name = '';
						$seller_name = '';
						$sellers = [];
						$multiple_buyers = Negotiation::where(['post_notification_id'=>$post_notification_id ,'negotiation_type'=>$negotiation_type])->get();
						if(count($multiple_buyers)>1){
							$best_price = [];
							foreach ($multiple_buyers as $value) {
								array_push($sellers,$value->seller_id);
								$negotiation_log = NegotiationLog::select('negotiation_id','negotiation_by','price')->where(['negotiation_id'=>$value->id,'negotiation_by'=>'seller'])->orderBy('id','DESC')->first();
								if(!empty($negotiation_log)){
									array_push($best_price, $negotiation_log->price);
								}
							}
							$unique_seller = array_unique($sellers);
							if(count($unique_seller)>1){
								if($negotiation->negotiation_type == "notification"){
									$notification = Notification::select('price','no_of_bales','product_id','id')->where('id',$post_notification_id)->first();
									if(!empty($notification)){
										$base_price = $notification->price;
										$base_bales = $notification->no_of_bales;
										$product = Product::select('name','id')->where('id',$notification->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
									}
								}
								$best_dealer_data = NegotiationLog::select('price','id','bales','negotiation_id')->where('price',max($best_price))->first();
								if(!empty($best_dealer_data)){
									$best_bales = $best_dealer_data->bales;
									$best_price = $best_dealer_data->price;
									$negotiation_id = Negotiation::select('id','seller_id')->where('id',$best_dealer_data->negotiation_id)->first();
									if(!empty($negotiation_id)){
										$seller_data = Sellers::select('id','name')->where('id',$negotiation_id->seller_id)->first();
										if(!empty($seller_data)){
											$best_dealer_name = $seller_data->name;
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}
								$negotiationBuyerData = new Negotiation();
								$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationBuyerData->buyer_name = $buyer_name;
								$negotiationBuyerData->seller_id = $negotiation->seller_id;
								$negotiationBuyerData->seller_name = $seller_name;
								$negotiationBuyerData->post_notification_id = $post_notification_id;
								$negotiationBuyerData->broker_name = $broker_details->name;
								$negotiationBuyerData->negotiation_by = $negotiation_by;
								$negotiationBuyerData->negotiation_type = $negotiation_type;
								$negotiationBuyerData->prev_price = $negotiation->prev_price;
								$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationBuyerData->current_price = $price;
								$negotiationBuyerData->current_bales = $no_of_bales;
								$negotiationBuyerData->best_dealer_name = $best_dealer_name;
								$negotiationBuyerData->best_price = $best_price;
								$negotiationBuyerData->best_bales = $best_bales;
								$negotiationBuyerData->negotiation_count = count($multiple_buyers);
								event(new NegotiationBuyer($negotiationBuyerData));


								$negotiationMultipleBuyerData = new Negotiation();
								$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleBuyerData->buyer_name = $buyer_name;
								$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleBuyerData->seller_name = $seller_name;
								$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
								$negotiationMultipleBuyerData->broker_name = $broker_details->name;
								$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
								$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
								$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleBuyerData->current_price = $price;
								$negotiationMultipleBuyerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
							}else{
								if($negotiation->negotiation_type == "notification"){
									$notification = Notification::select('id','product_id','seller_buyer_id','user_type')->where('id',$negotiation->post_notification_id)->first();
									if(!empty($notification)){
										$product = Product::select('id','name')->where('id',$notification->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
										if($notification->user_type == "seller"){
											$seller = Sellers::select('id','name')->where('id',$notification->seller_buyer_id)->first();
											if(!empty($seller)){
												$post_by = $seller->name;
											}
										}else{
											$buyer = Buyers::select('id','name')->where('id',$notification->seller_buyer_id)->first();
											if(!empty($buyer)){
												$post_by = $buyer->name;
											}
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}
								$negotiationBuyerData = new Negotiation();
								$negotiationBuyerData->post_notification_id = $post_notification_id;
								$negotiationBuyerData->negotiation_type = $negotiation->negotiation_type;
								$negotiationBuyerData->broker_name = $broker_details->name;;
								$negotiationBuyerData->seller_id = $negotiation->seller_id;
								$negotiationBuyerData->seller_name = $seller_name;
								$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationBuyerData->negotiation_by = $negotiation_by;
								$negotiationBuyerData->negotiation_type = $negotiation_type;
								$negotiationBuyerData->product_name = $product_name;
								$negotiationBuyerData->post_by = $post_by;
								$negotiationBuyerData->prev_price = $negotiation->prev_price;
								$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationBuyerData->new_price = $negotiation->price;
								$negotiationBuyerData->new_bales = $negotiation->bales;
								event(new NegotiationBuyer($negotiationBuyerData));

								$negotiationMultipleBuyerData = new Negotiation();
								$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleBuyerData->buyer_name = $buyer_name;
								$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleBuyerData->seller_name = $seller_name;
								$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
								$negotiationMultipleBuyerData->broker_name = $broker_details->name;
								$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
								$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
								$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleBuyerData->current_price = $price;
								$negotiationMultipleBuyerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
							}
						}else{
							if($negotiation->negotiation_type == "notification"){
								$notification = Notification::select('id','product_id','seller_buyer_id','user_type')->where('id',$negotiation->post_notification_id)->first();
								if(!empty($notification)){
									$product = Product::select('id','name')->where('id',$notification->product_id)->first();
									if(!empty($product)){
										$product_name = $product->name;
									}
									if($notification->user_type == "seller"){
										$seller = Sellers::select('id','name')->where('id',$notification->seller_buyer_id)->first();
										if(!empty($seller)){
											$post_by = $seller->name;
										}
									}else{
										$buyer = Buyers::select('id','name')->where('id',$notification->seller_buyer_id)->first();
										if(!empty($buyer)){
											$post_by = $buyer->name;
										}
									}
								}
							}
							if(!empty($negotiation->buyer_id)){
								$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
								if(!empty($buyer)){
									$buyer_name = $buyer->name;
								}
							}
							if(!empty($negotiation->seller_id)){
								$seller =  Sellers::where('id',$negotiation->seller_id)->first();
								if(!empty($seller)){
									$seller_name = $seller->name;
								}
							}
							$negotiationBuyerData = new Negotiation();
							$negotiationBuyerData->post_notification_id = $post_notification_id;
							$negotiationBuyerData->negotiation_type = $negotiation->negotiation_type;
							$negotiationBuyerData->broker_name = $broker_details->name;;
							$negotiationBuyerData->seller_id = $negotiation->seller_id;
							$negotiationBuyerData->seller_name = $seller_name;
							$negotiationBuyerData->buyer_id = $negotiation->buyer_id;
							$negotiationBuyerData->negotiation_by = $negotiation_by;
							$negotiationBuyerData->negotiation_type = $negotiation_type;
							$negotiationBuyerData->product_name = $product_name;
							$negotiationBuyerData->post_by = $post_by;
							$negotiationBuyerData->prev_price = $negotiation->prev_price;
							$negotiationBuyerData->prev_bales = $negotiation->prev_bales;
							$negotiationBuyerData->new_price = $negotiation->price;
							$negotiationBuyerData->new_bales = $negotiation->bales;
							event(new NegotiationBuyer($negotiationBuyerData));

							$negotiationMultipleBuyerData = new Negotiation();
							$negotiationMultipleBuyerData->buyer_id = $negotiation->buyer_id;
							$negotiationMultipleBuyerData->buyer_name = $buyer_name;
							$negotiationMultipleBuyerData->seller_id = $negotiation->seller_id;
							$negotiationMultipleBuyerData->seller_name = $seller_name;
							$negotiationMultipleBuyerData->post_notification_id = $post_notification_id;
							$negotiationMultipleBuyerData->broker_name = $broker_details->name;
							$negotiationMultipleBuyerData->negotiation_by = $negotiation_by;
							$negotiationMultipleBuyerData->negotiation_type = $negotiation_type;
							$negotiationMultipleBuyerData->prev_price = $negotiation->prev_price;
							$negotiationMultipleBuyerData->prev_bales = $negotiation->prev_bales;
							$negotiationMultipleBuyerData->current_price = $price;
							$negotiationMultipleBuyerData->current_bales = $no_of_bales;
							event(new NegotiationMultipleBuyer($negotiationMultipleBuyerData));
						}
					}else{
						$product_name = '';
						$best_bales = '';
						$best_dealer_name = '';
						$best_price = '';
						$base_price = '';
						$base_bales = '';
						$post_by='';
						$buyer_name = '';
						$seller_name = '';
						$buyers = [];
						$multiple_sellers = Negotiation::where(['post_notification_id'=> $post_notification_id,'negotiation_type' => $negotiation_type])->get();
						if(count($multiple_sellers)>1){
							$best_price = [];
							foreach ($multiple_sellers as $value) {
								array_push($buyers, $value->buyer_id);
								$negotiation_log = NegotiationLog::select('negotiation_id','negotiation_by','price')->where(['negotiation_id'=>$value->id,'negotiation_by'=>'buyer'])->orderBy('id','DESC')->first();
								if(!empty($negotiation_log)){
									array_push($best_price, $negotiation_log->price);
								}
							}

							$unique_buyer = array_unique($buyers);
							if(count($unique_buyer)>1){
								if($negotiation->negotiation_type == "notification"){
									$notification = Notification::select('price','no_of_bales','product_id','id')->where('id',$post_notification_id)->first();
									if(!empty($notification)){
										$base_price = $notification->price;
										$base_bales = $notification->no_of_bales;
										$product = Product::select('name','id')->where('id',$notification->product_id)->first();
										if(!empty($product)){
											$product_name = $product->name;
										}
									}
								}
								$best_dealer_data = NegotiationLog::select('price','id','bales','negotiation_id')->where('price',max($best_price))->first();
								if(!empty($best_dealer_data)){
									$best_bales = $best_dealer_data->bales;
									$best_price = $best_dealer_data->price;
									$negotiation_id = Negotiation::select('id','buyer_id')->where('id',$best_dealer_data->negotiation_id)->first();
									if(!empty($negotiation_id)){
										$buyer_data = Buyers::select('id','name')->where('id',$negotiation_id->buyer_id)->first();
										if(!empty($buyer_data)){
											$best_dealer_name = $buyer_data->name;
										}
									}
								}

								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}

								$negotiationSellerData = new Negotiation();
								$negotiationSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationSellerData->buyer_name = $buyer_name;
								$negotiationSellerData->seller_id = $negotiation->seller_id;
								$negotiationSellerData->seller_name = $seller_name;
								$negotiationSellerData->post_notification_id = $post_notification_id;
								$negotiationSellerData->broker_name = $broker_details->name;
								$negotiationSellerData->negotiation_by = $negotiation_by;
								$negotiationSellerData->negotiation_type = $negotiation_type;
								$negotiationSellerData->prev_price = $negotiation->prev_price;
								$negotiationSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationSellerData->current_price = $price;
								$negotiationSellerData->current_bales = $no_of_bales;
								$negotiationSellerData->best_dealer_name = $best_dealer_name;
								$negotiationSellerData->best_price = $best_price;
								$negotiationSellerData->best_bales = $best_bales;
								$negotiationSellerData->negotiation_count = count($multiple_sellers);
								event(new NegotiationSeller($negotiationSellerData));

								$negotiationMultipleSellerData = new Negotiation();
								$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleSellerData->buyer_name = $buyer_name;
								$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleSellerData->seller_name = $seller_name;
								$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
								$negotiationMultipleSellerData->broker_name = $broker_details->name;
								$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
								$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
								$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleSellerData->current_price = $price;
								$negotiationMultipleSellerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
							}else{
								$post_notification = Notification::where('id',$negotiation->post_notification_id)->first();
								if(!empty($post_notification)){
									$product = Product::where('id',$post_notification->product_id)->first();
									if(!empty($product)){
										$product_name = $product->name;
									}
									if($post_notification->user_type == "seller"){
										$seller = Sellers::where('id',$post_notification->seller_buyer_id)->first();
										if(!empty($seller)){
											$post_by = $seller->name;
										}
									}else{
										$buyer = Buyers::where('id',$post_notification->seller_buyer_id)->first();
										if(!empty($buyer)){
											$post_by = $buyer->name;
										}
									}
								}
								if(!empty($negotiation->buyer_id)){
									$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
									if(!empty($buyer)){
										$buyer_name = $buyer->name;
									}
								}
								if(!empty($negotiation->seller_id)){
									$seller =  Sellers::where('id',$negotiation->seller_id)->first();
									if(!empty($seller)){
										$seller_name = $seller->name;
									}
								}

								$negotiationSellerData = new Negotiation();
								$negotiationSellerData->post_notification_id = $post_notification_id;
								$negotiationSellerData->negotiation_type = $negotiation->negotiation_type;
								$negotiationSellerData->broker_name = $broker_details->name;;
								$negotiationSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationSellerData->seller_id = $negotiation->seller_id;
								$negotiationSellerData->seller_name = $seller_name;
								$negotiationSellerData->negotiation_by = $negotiation_by;
								$negotiationSellerData->negotiation_type = $negotiation_type;
								$negotiationSellerData->product_name = $product_name;
								$negotiationSellerData->post_by = $post_by;
								$negotiationSellerData->prev_price = $negotiation->prev_price;
								$negotiationSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationSellerData->new_price = $negotiation->price;
								$negotiationSellerData->new_bales = $negotiation->bales;
								event(new NegotiationSeller($negotiationSellerData));

								$negotiationMultipleSellerData = new Negotiation();
								$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
								$negotiationMultipleSellerData->buyer_name = $buyer_name;
								$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
								$negotiationMultipleSellerData->seller_name = $seller_name;
								$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
								$negotiationMultipleSellerData->broker_name = $broker_details->name;
								$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
								$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
								$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
								$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
								$negotiationMultipleSellerData->current_price = $price;
								$negotiationMultipleSellerData->current_bales = $no_of_bales;
								event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
							}
						}else{
							$post_notification = Notification::where('id',$negotiation->post_notification_id)->first();
							if(!empty($post_notification)){
								$product = Product::where('id',$post_notification->product_id)->first();
								if(!empty($product)){
									$product_name = $product->name;
								}
								if($post_notification->user_type == "seller"){
									$seller = Sellers::where('id',$post_notification->seller_buyer_id)->first();
									if(!empty($seller)){
										$post_by = $seller->name;
									}
								}else{
									$buyer = Buyers::where('id',$post_notification->seller_buyer_id)->first();
									if(!empty($buyer)){
										$post_by = $buyer->name;
									}
								}
							}
							if(!empty($negotiation->buyer_id)){
								$buyer =  Buyers::where('id',$negotiation->buyer_id)->first();
								if(!empty($buyer)){
									$buyer_name = $buyer->name;
								}
							}
							if(!empty($negotiation->seller_id)){
								$seller =  Sellers::where('id',$negotiation->seller_id)->first();
								if(!empty($seller)){
									$seller_name = $seller->name;
								}
							}

							$negotiationSellerData = new Negotiation();
							$negotiationSellerData->post_notification_id = $post_notification_id;
							$negotiationSellerData->negotiation_type = $negotiation->negotiation_type;
							$negotiationSellerData->broker_name = $broker_details->name;;
							$negotiationSellerData->buyer_id = $negotiation->buyer_id;
							$negotiationSellerData->seller_id = $negotiation->seller_id;
							$negotiationSellerData->seller_name = $seller_name;
							$negotiationSellerData->negotiation_by = $negotiation_by;
							$negotiationSellerData->negotiation_type = $negotiation_type;
							$negotiationSellerData->product_name = $product_name;
							$negotiationSellerData->post_by = $post_by;
							$negotiationSellerData->prev_price = $negotiation->prev_price;
							$negotiationSellerData->prev_bales = $negotiation->prev_bales;
							$negotiationSellerData->new_price = $negotiation->price;
							$negotiationSellerData->new_bales = $negotiation->bales;
							event(new NegotiationSeller($negotiationSellerData));

							$negotiationMultipleSellerData = new Negotiation();
							$negotiationMultipleSellerData->buyer_id = $negotiation->buyer_id;
							$negotiationMultipleSellerData->buyer_name = $buyer_name;
							$negotiationMultipleSellerData->seller_id = $negotiation->seller_id;
							$negotiationMultipleSellerData->seller_name = $seller_name;
							$negotiationMultipleSellerData->post_notification_id = $post_notification_id;
							$negotiationMultipleSellerData->broker_name = $broker_details->name;
							$negotiationMultipleSellerData->negotiation_by = $negotiation_by;
							$negotiationMultipleSellerData->negotiation_type = $negotiation_type;
							$negotiationMultipleSellerData->prev_price = $negotiation->prev_price;
							$negotiationMultipleSellerData->prev_bales = $negotiation->prev_bales;
							$negotiationMultipleSellerData->current_price = $price;
							$negotiationMultipleSellerData->current_bales = $no_of_bales;
							event(new NegotiationMultipleSeller($negotiationMultipleSellerData));
						}
					}
					$response['status'] = 200;
					$response['message'] = 'Negotiation';
				}else{
					$response['status'] = 404;
				}
	    	}
	    }

        $fcm_token = "";
        $user = [];
        $data = [];
        if ($negotiation_by == "seller") {
            $user_type = "buyer";
            $data = [
                'navigateto' => 'DealDetails',
                'sellerId' => $seller_id,
                'post_id' => $post_notification_id,
                'type' => $negotiation_type,
                'product_name' => $product_name,
            ];
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$buyer_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Sellers::select('name')->where('id',$seller_id)->first();
        }else if($negotiation_by == 'buyer'){
            $user_type = "seller";
            $data = [
                'navigateto' => 'DealDetails',
                'buyerId' => $buyer_id,
                'post_id' => $post_notification_id,
                'type' => $negotiation_type,
                'product_name' => $product_name,
            ];
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$seller_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Buyers::select('name')->where('id',$buyer_id)->first();
        }

        if (!empty($fcm_token)) {
            $json_array = [
                "registration_ids" => [$fcm_token],
                "data" => $data,
                "notification" => [
                    "body" => "Notification send by ".$user->name,
                    "title" => "Negotiation Notification",
                    "icon" => "ic_launcher"
                ]
            ];
            NotificationHelper::notification($json_array,$user_type);
        }

		return response($response, 200);
	}

    public function make_deal_new_v2(Request $request)
	{
		// dd("sdzf");
		$response = array();
		$response['status'] = 200;
		$response['message'] = 'make deal done';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$type = isset($content->type) ? $content->type : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$done_by = isset($content->done_by) ? $content->done_by : '';
		$broker_id = isset($content->broker_id) ? $content->broker_id : '';

		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
			'type' => $type,
			// 'no_of_bales' => $no_of_bales,
			'done_by' => $done_by
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
            'buyer_id' => 'required',
            'post_notification_id' => 'required',
            'type' => 'required',
            // 'no_of_bales' => 'required',
            'done_by' => 'required'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

        $settings = Settings::first();
		$negotiation_comp = Negotiation::with('seller', 'buyer')->where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id,'negotiation_type'=>$type])->first();

        // $seller_otp = mt_rand(100000,999999);
        // $buyer_otp = mt_rand(100000,999999);
        // $broker_otp = mt_rand(100000,999999);

        $seller_otp = '123456';
        $buyer_otp = '123456';
        $broker_otp = '123456';

        // $seller_email_otp = mt_rand(100000,999999);
        // $buyer_email_otp = mt_rand(100000,999999);
        // $broker_email_otp = mt_rand(100000,999999);

        $seller_email_otp = '012345';
        $buyer_email_otp = '012345';
        $broker_email_otp = '012345';

		$deal_id = 0;

        // dd($seller_otp.' '.$buyer_otp.' '.$broker_otp);
        if (!empty($negotiation_comp)) {

            $user_data = [];
            $total_amt = 0;
            $broker_changed = 0;
            $no_of_bales = $negotiation_comp->bales;
            $total_amt = $no_of_bales * $settings->company_commission;
            $broker_commission_amt = $no_of_bales * $settings->broker_commission;
            if ($done_by == 'seller') {
            	//event
            	$makeDealSellerData = new NegotiationLog();
            	$makeDealSellerData->seller_id = $seller_id;
				event(new MakedealSeller($makeDealSellerData));
				//event
                $user_id = $seller_id;
                $user_data = Sellers::with('broker', 'broker.broker')->where('id',$seller_id)->first();

				if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
                    $response['status'] = 404;
					$response['message'] = 'Wallet amount not enough';
					return response($response, 200);
				}

				if (empty($user_data->broker->broker_id)) {
					$response['status'] = 404;
					$response['message'] = 'Please select default broker!';
					return response($response, 200);
				}

				$user_id = $buyer_id;
                $buyer_data = Buyers::where('id',$buyer_id)->first();

				if(!empty($buyer_data) && $buyer_data->wallet_amount < $total_amt){
                    $response['status'] = 404;
					$response['message'] = 'Buyer\'s Wallet amount not enough';
					return response($response, 200);
				}

                $negotiation_logs = NegotiationLog::where(['seller_id'=>$seller_id, 'negotiation_by' => 'seller','negotiation_id'=>$negotiation_comp->id])->orderBy('id', 'DESC')->first();


                if (!empty($user_data) && !empty($negotiation_logs) && $negotiation_logs->broker_id != $user_data->broker->broker_id) {
                    $broker_changed = 1;
                }
            } else {
            	//event
            	$makeDealBuyerData = new NegotiationLog();
            	$makeDealBuyerData->buyer_id = $buyer_id;
				event(new MakedealBuyer($makeDealBuyerData));
				//event
                $user_id = $buyer_id;
                $user_data = Buyers::with('broker', 'broker.broker')->where('id',$user_id)->first();

				if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
					$response['status'] = 404;
					$response['message'] = 'Wallet amount not enough';
					return response($response, 200);
				}

				if (empty($user_data->broker->broker_id)) {
					$response['status'] = 404;
					$response['message'] = 'Please select default broker!';
					return response($response, 200);
				}

				$user_id = $seller_id;
                $seller_data = Sellers::where('id',$seller_id)->first();

				if(!empty($seller_data) && $seller_data->wallet_amount < $total_amt){
					$response['status'] = 404;
					$response['message'] = 'Seller\'s Wallet amount not enough';
					return response($response, 200);
				}

                $negotiation_logs = NegotiationLog::where(['buyer_id'=>$buyer_id, 'negotiation_by' => 'buyer','negotiation_id'=>$negotiation_comp->id])->orderBy('id', 'DESC')->first();

                if (!empty($user_data) && !empty($negotiation_logs) && $negotiation_logs->broker_id != $user_data->broker->broker_id) {
                    $broker_changed = 1;
                }
            }

            if ($negotiation_comp->negotiation_type == "post") {
                $post_remain = Post::where(['id'=>$negotiation_comp->post_notification_id,'is_active'=>0])->first();

                if ($no_of_bales > $post_remain->no_of_bales) {
                    $response['status'] = 404;
                    $response['message'] = 'Please Enter Less Bales';
                } else if ($no_of_bales > $post_remain->remain_bales){
                    $response['status'] = 404;
                    $response['message'] = 'No of remain bales is not enough in post';
                    return response($response, 200);
                } else {


                    $complete = Negotiation::where('id',$negotiation_comp->id)->orderBy('id','DESC')->first();
                    if(!empty($complete)){

                        $bales_amt = $settings->company_commission * $no_of_bales;

                        if (!empty($check_data['data']) && $bales_amt > $check_data['data']->wallet_amount) {
                            $response['status'] = 404;
                            $response['message'] = ' ';
                            return response($response, 200);
                        }

                        $complete->status = 'complete';
                        $complete->updated_at = date('Y-m-d H:i:s');
                        $complete->save();

                        $response['status'] = 200;
                        $response['message'] = 'Make Deal done';
                    }
                }
            }
            $make_deal = new NegotiationComplete();

            $check = $this->check_queue($buyer_id, $seller_id, $post_notification_id, $type);

            if ($check == 2) {
                DealQueue::where(['buyer_id' => $buyer_id, 'seller_id' => $seller_id, 'post_notification_id' => $post_notification_id, 'post_type'=> $type])->delete();

                $response['status'] = 404;
                $response['message'] = 'Deal already done sorry for inconvenience!';
                return response($response, 200);
            }

            if($negotiation_comp->negotiation_type == "notification"){
                $notification_remain = Notification::where(['id'=>$negotiation_comp->post_notification_id,'is_active'=>0])->first();

                if($no_of_bales > $notification_remain->no_of_bales){
                    $response['status'] = 404;
                    $response['message'] = 'Please Enter Less Bales';
                }else{
                    $complete = Negotiation::where('id',$negotiation_comp->id)->orderBy('id','DESC')->first();
                    if(!empty($complete)){
                        $complete->status = 'complete';
                        $complete->updated_at = date('Y-m-d H:i:s');
                        $complete->save();
                        // $make_deal = new NegotiationComplete();

                        $response['status'] = 200;
                        $response['message'] = 'Make Deal done';
                    }
                }
            }

			if(!empty($negotiation_comp)){

                if($type == "post"){
                    $post = Post::where(['id'=>$post_notification_id,'is_active'=>0])->first();
                    if(!empty($post)){

                        $post->sold_bales = $post->sold_bales + $no_of_bales;
                        $post->remain_bales = $post->no_of_bales - $post->sold_bales;
                        // $make_deal->no_of_bales = $no_of_bales;
                        if($post->remain_bales <= 0){

                            $post->status = 'complete';
                            $post->updated_at = date('Y-m-d H:i:s');
                        }
                        $post->save();
                    }
                }
                if($type == "notification"){
                    $notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0])->first();
                    if(!empty($notification)){

                        $notification->sold_bales = $notification->sold_bales +  $no_of_bales;
                        $notification->remain_bales = $notification->no_of_bales - $notification->sold_bales;
                        // $make_deal->no_of_bales = $no_of_bales;
                        if($notification->remain_bales <= 0){

                            $notification->status = 'complete';
                            $notification->updated_at = date('Y-m-d H:i:s');
                        }
                        $notification->save();
                    }
                }


                // $make_deal->negotiation_id = $negotiation_comp->id;
                $history_type = 'negotiation';
                if(!empty($negotiation_comp->negotiation_complete_id) && $negotiation_comp->negotiation_complete_id != 0){
                    $history_type = 're-negotiation';
                    $make_deal = NegotiationComplete::where('id',$negotiation_comp->negotiation_complete_id)->first();
                }

                $make_deal->no_of_bales = $no_of_bales;
                $make_deal->buyer_id = $negotiation_comp->buyer_id;
                $make_deal->seller_id = $negotiation_comp->seller_id;
                $make_deal->broker_id = $negotiation_comp->broker_id;
                $make_deal->negotiation_by = $negotiation_comp->negotiation_by;
                $make_deal->post_notification_id = $negotiation_comp->post_notification_id;
                $make_deal->negotiation_type = $negotiation_comp->negotiation_type;
                $make_deal->price = $negotiation_comp->price;
                $make_deal->done_by = $done_by;
                $make_deal->payment_condition = $negotiation_comp->payment_condition;
                $make_deal->transmit_condition = $negotiation_comp->transmit_condition;
                $make_deal->lab = $negotiation_comp->lab;
                $make_deal->notes = $negotiation_comp->notes;
                $make_deal->header = $negotiation_comp->header;
                $make_deal->seller_otp = $seller_otp;
                $make_deal->buyer_otp = $buyer_otp;
                $make_deal->broker_otp = $broker_otp;
                $make_deal->seller_email_otp = $seller_email_otp;
                $make_deal->buyer_email_otp = $buyer_email_otp;
                $make_deal->broker_email_otp = $broker_email_otp;
                $make_deal->otp_time = date("Y-m-d H:i:s");
                $make_deal->deal_type = 'negotiation';
                $make_deal->status = 'pending';

                $status = "";
                if(!empty($complete)){
                    // $make_deal->status = $complete->status;
                    $status =  $complete->status;
                }else{
                    // $make_deal->status = $negotiation_comp->status;
                    $status =  $negotiation_comp->status;
                }
                $make_deal->save();

                $sellers = Sellers::select('mobile_number','email','name')->where('id',$seller_id)->first();
                $s_message = "OTP to verify make deal is ". $seller_otp ." - E - Cotton";
                NotificationHelper::send_otp($sellers->mobile_number,$s_message);

                $s_data = array('otp'=>$seller_email_otp,'name' => $sellers->name);

                // Mail::send(['html'=>'mail'], $s_data, function($message) use($sellers) {
                //     $message->to($sellers->email, 'E - Cotton')->subject('Make Deal OTP');
                // });

                $buyers = Buyers::select('mobile_number','email','name')->where('id',$buyer_id)->first();
                $by_message = "OTP to verify make deal is ". $buyer_otp ." - E - Cotton";
                NotificationHelper::send_otp($buyers->mobile_number,$by_message);

                $by_data = array('otp'=>$buyer_email_otp,'name' => $buyers->name);

                // Mail::send(['html'=>'mail'], $by_data, function($message) use($buyers) {
                //     $message->to($buyers->email, 'E - Cotton')->subject('Make Deal OTP');
                // });

                $brokers = Brokers::select('mobile_number','email','name')->where('id',$negotiation_comp->broker_id)->first();
                $br_message = "OTP to verify make deal is ". $broker_otp ." - E - Cotton";
                NotificationHelper::send_otp($brokers->mobile_number,$br_message);

                $br_data = array('otp'=>$broker_email_otp,'name' => $brokers->name);

                // Mail::send(['html'=>'mail'], $br_data, function($message) use($brokers) {
                //     $message->to($brokers->email, 'E - Cotton')->subject('Make deal OTP');
                // });

				$deal_id = $make_deal->id;

                $history = new NegotiationHistory();
                $history->negotiation_complete_id = $make_deal->id;
                $history->negotiation_id = $negotiation_comp->id;
                $history->type = $history_type;
                $history->save();

                $negotiation_log = new NegotiationLog();
                $negotiation_log->negotiation_id = $negotiation_comp->id;
                $negotiation_log->negotiation_by = $negotiation_comp->negotiation_by;
                $negotiation_log->seller_id = $negotiation_comp->seller_id;
                $negotiation_log->buyer_id = $negotiation_comp->buyer_id;
                $negotiation_log->broker_id = $negotiation_comp->broker_id;
                $negotiation_log->price = $negotiation_comp->price;
                $negotiation_log->bales = $no_of_bales;
                $negotiation_log->payment_condition_id = $negotiation_comp->payment_condition;
                $negotiation_log->transmit_condition_id = $negotiation_comp->transmit_condition;
                $negotiation_log->lab_id = $negotiation_comp->lab;
                $negotiation_log->save();

				if ($done_by == 'seller') {
					$user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
					$user_data->save();

					$transactions = new Transactions();
					$transactions->user_id = $seller_id;
					$transactions->user_type = $done_by;
					$transactions->deal_id = $deal_id;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'deal done by '.$user_data->name;
					$transactions->save();

					$buyer_data->wallet_amount = $buyer_data->wallet_amount - $total_amt;
					$buyer_data->save();

                    $transactions = new Transactions();
					$transactions->user_id = $buyer_id;
					$transactions->user_type = 'buyer';
                    $transactions->deal_id = $deal_id;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'amount withdraw';
					$transactions->save();

                    $admin_data = User::find(1);
					$admin_data->wallet_amount = $admin_data->wallet_amount + $total_amt * 2;
					$admin_data->save();

					$transactions = new Transactions();
					$transactions->user_id = 1;
					$transactions->user_type = 'admin';
                    $transactions->deal_id = $deal_id;
					$transactions->type = 'deposite';
					$transactions->amount = $total_amt * 2;
					$transactions->message = 'amount deposite';
					$transactions->save();

                    $update_deal = NegotiationComplete::find($deal_id);
                    $update_deal->seller_amount = $total_amt;
                    $update_deal->buyer_amount = $total_amt;
                    $update_deal->admin_amount = $total_amt * 2;
                    $update_deal->save();
				} else {
					$user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
					$user_data->save();

					$transactions = new Transactions();
					$transactions->user_id = $buyer_id;
					$transactions->user_type = $done_by;
                    $transactions->deal_id = $deal_id;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'deal done by '.$user_data->name;
					$transactions->save();

					$seller_data->wallet_amount = $seller_data->wallet_amount - $total_amt;
					$seller_data->save();

					$transactions = new Transactions();
					$transactions->user_id = $seller_id;
					$transactions->user_type = 'seller';
                    $transactions->deal_id = $deal_id;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'amount withdraw';
					$transactions->save();

					$user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
					$user_data->save();

                    $admin_data = User::find(1);
					$admin_data->wallet_amount = $admin_data->wallet_amount + $total_amt * 2;
					$admin_data->save();

					$transactions = new Transactions();
					$transactions->user_id = 1;
					$transactions->user_type = 'admin';
                    $transactions->deal_id = $deal_id;
					$transactions->type = 'deposite';
					$transactions->amount = $total_amt * 2;
					$transactions->message = 'amount deposite';
					$transactions->save();

                    $update_deal = NegotiationComplete::find($deal_id);
                    $update_deal->seller_amount = $total_amt;
                    $update_deal->buyer_amount = $total_amt;
                    $update_deal->admin_amount = $total_amt * 2;
                    $update_deal->save();
				}

                if ($broker_changed == 1) {

                    if($type == "post"){
                        $post = Post::with('seller', 'buyer', 'seller.broker', 'buyer.broker')->where(['id'=>$post_notification_id,'is_active'=>0])->first();

                        if(!empty($post)){

                            $transactions = new Transactions();
                            if ($post->user_type == 'seller') {
								$transactions->user_id = $post->seller->broker->broker_id;
							} else {
								$transactions->user_id = $post->buyer->broker->broker_id;
							}
                            $transactions->user_type = 'broker';
                            $transactions->type = 'deposite';
                            $transactions->amount = $broker_commission_amt;
                            $transactions->message = 'amount deposite';
                            $transactions->deal_id = $deal_id;
                            $transactions->save();

                            $update_deal = NegotiationComplete::find($deal_id);
                            $update_deal->broker_amount = $broker_commission_amt;
                            $update_deal->save();
                        }
                    }
                    if($type == "notification"){

                        $notification = Notification::with('seller', 'buyer', 'seller.broker', 'buyer.broker')->where(['id'=>$post_notification_id,'is_active'=>0])->first();

                        if(!empty($notification)){

                            $transactions = new Transactions();
                            if ($notification->user_type == 'seller') {
								$transactions->user_id = $notification->seller->broker->broker_id;
							} else {
								$transactions->user_id = $notification->buyer->broker->broker_id;
							}
                            $transactions->user_type = 'broker';
                            $transactions->type = 'deposite';
                            $transactions->amount = $broker_commission_amt;
                            $transactions->message = 'amount deposite';
                            $transactions->deal_id = $deal_id;
                            $transactions->save();

                            $update_deal = NegotiationComplete::find($deal_id);
                            $update_deal->broker_amount = $broker_commission_amt;
                            $update_deal->save();
                        }
                    }
                }

                if($status == 'complete'){
                    Negotiation::where('id',$negotiation_comp->id)->delete();
                }
                //pdf
                $broker_name = '';
                $broker_address = '';
                $broker_country = '';
                $broker_state = '';
                $broker_mobile_number = '';
                $broker_mobile_number_2 = '';
                $broker_email = '';
                $broker_url = '';
                $seller_name_address = '';
                $seller_station = '';
                $buyer_name_address = '';
                $buyer_station = '';
                $product_name_pdf = '';
                $deal_date = '';
                $deal_no_of_bales = $negotiation_comp->bales;
                $deal_price = $negotiation_comp->price;
                $ref_no = '';
                $attribute_array_pdf = '';
                $broker_stamp_image = '';
                $buyer_stamp_image = '';
                $seller_stamp_image = '';
                $broker_header_image = '';

                $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
                // $deal_price = $make_deal->price;
                $ref_no = $make_deal->id;

                $broker = Brokers::where('id',$negotiation_comp->broker_id)->first();
				$broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
				if(!empty($broker_data)){
					$country = Country::where('id',$broker_data->country_id)->first();
					if(!empty($country)){
						$state = State::where('id',$broker_data->state_id)->first();
						if(!empty($state)){
							$broker_name = $broker->name;
							$broker_address = $broker->address;
							$broker_country = $country->name;
							$broker_state = $state->name;
							$broker_mobile_number = $broker->mobile_number;
							$broker_mobile_number_2 = $broker->mobile_number_2;
							$broker_email =$broker->email;
							$broker_url =$broker->website;

							$broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);

							$broker_stamp_image = '';
							if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
								$file1 = file_get_contents($broker_stamp_img);
								$broker_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);
							}

							$broker_header_img = storage_path('app/public/broker/header_image/' . $broker->header_image);

							$broker_header_image = '';
							if (!empty($broker->header_image) && File::exists($broker_header_img)) {
								$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
								$file2 = file_get_contents($broker_header_img);
								$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
							}
						}
					}
				}

                // $deal_no_of_bales = $make_deal->no_of_bales;
                // if($done_by == "seller"){
                //     $seller = Sellers::where('id',$seller_id)->first();
                //     if(!empty($seller)){
                //         $broker = Brokers::where('code',$seller->referral_code)->first();
                //         if(!empty($broker)){
                //             $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                //             if(!empty($broker_data)){
                //                 $country = Country::where('id',$broker_data->country_id)->first();
                //                 if(!empty($country)){
                //                     $state = State::where('id',$broker_data->state_id)->first();
                //                     if(!empty($state)){
                //                         $broker_name = $broker->name;
                //                         $broker_address = $broker->address;
                //                         $broker_country = $country->name;
                //                         $broker_state = $state->name;
                //                         $broker_mobile_number = $broker->mobile_number;
                //                         $broker_mobile_number_2 = $broker->mobile_number_2;
                //                         $broker_email =$broker->email;
                //                         $broker_url =$broker->website;

                //                         $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);

                //                         $broker_stamp_image = '';
				// 					    if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
                //                             $file1 = file_get_contents($broker_stamp_img);
                //                             $broker_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);
                //                         }

                //                         $broker_header_img = storage_path('app/public/broker/header_image/' . $broker->header_image);

                //                         $broker_header_image = '';
				// 					    if (!empty($broker->header_image) && File::exists($broker_header_img)) {
                //                             $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                //                             $file2 = file_get_contents($broker_header_img);
                //                             $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // }

                // if($done_by == "buyer"){
                //     $buyer = Buyers::where('id',$buyer_id)->first();
                //     if(!empty($buyer)){
                //         $broker = Brokers::where('code',$buyer->referral_code)->first();
                //         if(!empty($broker)){
                //             $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                //             if(!empty($broker_data)){
                //                 $country = Country::where('id',$broker_data->country_id)->first();
                //                 if(!empty($country)){
                //                     $state = State::where('id',$broker_data->state_id)->first();
                //                     if(!empty($state)){
                //                         $broker_name = $broker->name;
                //                         $broker_address = $broker->address;
                //                         $broker_country = $country->name;
                //                         $broker_state = $state->name;
                //                         $broker_mobile_number = $broker->mobile_number;
                //                         $broker_mobile_number_2 =$broker->mobile_number_2;
                //                         $broker_email =$broker->email;
                //                         $broker_url =$broker->website;
                //                         // $broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
                //                         // $file1 = file_get_contents($broker_stamp_img);
                //                         // $broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

                //                         // $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                //                         // $file2 = file_get_contents($broker_header_img);
                //                         // $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);

                //                         $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);
                //                         $broker_stamp_image = '';
				// 					    if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
                //                             $file1 = file_get_contents($broker_stamp_img);
                //                             $broker_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);
                //                         }

                //                         $broker_header_img = storage_path('app/public/broker/header_image/' . $broker->header_image);
                //                         $broker_header_image = '';
				// 					    if (!empty($broker->header_image) && File::exists($broker_header_img)) {
                //                             $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                //                             $file2 = file_get_contents($broker_header_img);
                //                             $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
                //                         }
                //                     }
                //                 }
                //             }
                //         }
                //     }
                // }

                if($make_deal->negotiation_type == "post"){
                    $post_detail = Post::where('id',$post_notification_id)->first();
                    if(!empty($post_detail)){
                        $product = Product::where('id',$post_detail->product_id)->first();
                        if(!empty($product)){
                            $product_name_pdf = $product->name;
                        }
                        $attribute_array_push  = [];
                        $attribute = PostDetails::where('post_id',$post_detail->id)->get();
                        foreach ($attribute as $value) {
                            array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
                        }
                        $attribute_array_pdf = implode(",",$attribute_array_push);
                    }
                }
                if($make_deal->negotiation_type == "notification"){
                    $notification_detail = Notification::where('id',$post_notification_id)->first();
                    if(!empty($notification_detail)){
                        $product = Product::where('id',$notification_detail->product_id)->first();
                        if(!empty($product)){
                            $product_name_pdf = $product->name;
                        }
                        $attribute_array_push  = [];
                        $attribute = NotificatonDetails::where('notification_id',$notification_detail->id)->get();
                        foreach ($attribute as $value) {
                            array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
                        }
                        $attribute_array_pdf = implode(",",$attribute_array_push);
                    }
                }

                $seller_details = Sellers::where('id',$seller_id)->first();
                if(!empty($seller_details)){
                    $seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
                    if(!empty($seller_sub_details)){
                        $station = Station::where('id',$seller_sub_details->station_id)->first();
                        if(!empty($station)){
                            $seller_name_address = $seller_details->name.' '.$seller_details->address;
                            $seller_station = $station->name;
                            if(!empty($seller_details->image)){
                                $seller_stamp_img = storage_path('app/public/seller/profile/' . $seller_details->image);

                                if (File::exists($seller_stamp_img)) {
                                    $seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
                                    $file1 = file_get_contents($seller_stamp_img);
                                    $seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);
                                }
                            }
                        }
                    }
                }

                $buyer_details = Buyers::where('id',$buyer_id)->first();
                if(!empty($buyer_details)){
                    $buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
                    if(!empty($buyer_sub_details)){
                        $station = Station::where('id',$buyer_sub_details->station_id)->first();
                        if(!empty($station)){
                            $buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
                            $buyer_station = $station->name;
                            if(!empty($buyer_details->image)){

                                $buyer_stamp_img = storage_path('app/public/buyer/profile/' . $buyer_details->image);
                                if (File::exists($buyer_stamp_img)) {
                                    $buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
                                    $file1 = file_get_contents($buyer_stamp_img);
                                    $buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file1);
                                }
                            }
                        }
                    }
                }

                $pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
                $pdf->getDomPDF()->setHttpContext(
                    stream_context_create([
                        'ssl' => [
                            'allow_self_signed'=> TRUE,
                            'verify_peer' => FALSE,
                            'verify_peer_name' => FALSE,
                        ]
                    ])
                );

                $current_time = time();
                $file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
                //$pdf->save(public_path().$file_name);
                Storage::put('public/pdf/'.$file_name, $pdf->output());

                $save = new DealPdf();
                $save->deal_id = $make_deal->id;
                $save->done_by = $make_deal->done_by;
                $save->seller_id = $make_deal->seller_id;
                $save->buyer_id = $make_deal->buyer_id;
                $save->filename = $file_name;
                $save->save();
                //pdf
			}
		} else {
			if($type == "notification"){

				$notification = Notification::where(['id'=>$post_notification_id,'is_active'=>0,'status'=>'active'])->first();

                if(!empty($notification)){

                    $total_amt = $notification->no_of_bales * $settings->company_commission;
                    $broker_commission_amt = $notification->no_of_bales * $settings->broker_commission;
                    if ($done_by == 'seller') {
                        $user_id = $seller_id;
                        $user_data = Sellers::with('broker', 'broker.broker')->where('id',$seller_id)->first();

                        if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Wallet amount not enough';
                            return response($response, 200);
                        }

                        $user_id = $buyer_id;
                        $buyer_data = Buyers::where('id',$buyer_id)->first();

                        if(!empty($buyer_data) && $buyer_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Buyer\'s Wallet amount not enough';
                            return response($response, 200);
                        }


                        if (!empty($user_data) && $broker_id != $user_data->broker->broker_id) {
                            $broker_changed = 1;
                        }
                    } else {
                        $user_id = $buyer_id;
                        $user_data = Buyers::where('id',$user_id)->first();

                        if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Wallet amount not enough';
                            return response($response, 200);
                        }

                        $user_id = $seller_id;
                        $seller_data = Sellers::where('id',$seller_id)->first();

                        if(!empty($seller_data) && $seller_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Seller\'s Wallet amount not enough';
                            return response($response, 200);
                        }
                    }


                    $check = $this->check_queue($buyer_id, $seller_id, $post_notification_id, $type);

                    if ($check == 2) {
                        DealQueue::where(['buyer_id' => $buyer_id, 'seller_id' => $seller_id, 'post_notification_id' => $post_notification_id, 'post_type'=> $type])->delete();

                        $response['status'] = 404;
                        $response['message'] = 'Deal already done sorry for inconvenience!';
                        return response($response, 200);
                    }

                    $default_broker = AddBrokers::where('buyer_id',$user_id)->where('user_type',$done_by)->where('broker_type','default')->first();

					if (empty($default_broker)) {
						$response['status'] = 404;
                        $response['message'] = 'Please select default broker!';
                        return response($response, 200);
					}

                    $notification->sold_bales = $notification->no_of_bales;
                    $notification->remain_bales = 0;
                    $notification->status = 'complete';
                    $notification->updated_at = date('Y-m-d H:i:s');
                    $notification->save();


					$make_deal = new NegotiationComplete();
					$make_deal->negotiation_id = 0;
					$make_deal->done_by = $done_by;
					$without = new WithoutNegotiationMakeDeal();
					$without->post_notification_id = $notification->id;
					$without->type = 'notification';
					if($notification->user_type == "seller"){
						$without->seller_buyer_id = $buyer_id;
						$without->user_type = 'buyer';
						$make_deal->seller_id = $notification->seller_buyer_id;
						$make_deal->buyer_id = $buyer_id;
					}
					if($notification->user_type == "buyer"){
						$without->seller_buyer_id = $seller_id;
						$without->user_type = 'seller';
						$make_deal->seller_id = $seller_id;
						$make_deal->buyer_id = $notification->seller_buyer_id;
					}
					$without->save();

					$make_deal->negotiation_by = 'seller';
					$make_deal->broker_id = $default_broker->broker_id;
					$make_deal->post_notification_id = $notification->id;
					$make_deal->negotiation_type = 'notification';
					$make_deal->price = $notification->price;
					$make_deal->no_of_bales = $notification->no_of_bales;
					$make_deal->payment_condition = $notification->payment_condition;
					$make_deal->transmit_condition = $notification->transmit_condition;
					$make_deal->lab = $notification->lab;
					$make_deal->is_deal = '1';
					$make_deal->status = 'pending';
                    $make_deal->seller_otp = $seller_otp;
                    $make_deal->buyer_otp = $buyer_otp;
                    $make_deal->broker_otp = $broker_otp;
                    $make_deal->seller_email_otp = $seller_email_otp;
                    $make_deal->buyer_email_otp = $buyer_email_otp;
                    $make_deal->broker_email_otp = $broker_email_otp;
                    $make_deal->otp_time = date("Y-m-d H:i:s");
					$make_deal->save();

					$deal_id = $make_deal->id;

                    $sellers = Sellers::select('mobile_number','email','name')->where('id',$seller_id)->first();
                    $s_message = "OTP to verify make deal is ". $seller_otp ." - E - Cotton";
                    NotificationHelper::send_otp($sellers->mobile_number,$s_message);

                    $s_data = array('otp'=>$seller_email_otp,'name' => $sellers->name);

                    // Mail::send(['html'=>'mail'], $s_data, function($message) use($sellers) {
                    //     $message->to($sellers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

                    $buyers = Buyers::select('mobile_number','email','name')->where('id',$buyer_id)->first();
                    $by_message = "OTP to verify make deal is ". $buyer_otp ." - E - Cotton";
                    NotificationHelper::send_otp($buyers->mobile_number,$by_message);

                    $by_data = array('otp'=>$buyer_email_otp,'name' => $buyers->name);

                    // Mail::send(['html'=>'mail'], $by_data, function($message) use($buyers) {
                    //     $message->to($buyers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

                    $brokers = Brokers::select('mobile_number','email','name')->where('id',$default_broker->broker_id)->first();
                    $br_message = "OTP to verify make deal is ". $broker_otp ." - E - Cotton";
                    NotificationHelper::send_otp($brokers->mobile_number,$br_message);

                    $br_data = array('otp'=>$broker_email_otp,'name' => $brokers->name);

                    // Mail::send(['html'=>'mail'], $br_data, function($message) use($brokers) {
                    //     $message->to($brokers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

					$transactions = new Transactions();
					$transactions->user_id = $user_id;
					$transactions->user_type = $done_by;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'deal done by '.$user_data->name;
					$transactions->save();

                    if ($done_by == 'seller') {
                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $seller_id;
                        $transactions->user_type = $done_by;
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'deal done by '.$user_data->name;
                        $transactions->save();

                        $buyer_data->wallet_amount = $buyer_data->wallet_amount - $total_amt;
                        $buyer_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $buyer_id;
                        $transactions->user_type = 'buyer';
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'amount withdraw';
                        $transactions->save();

                        $admin_data = User::find(1);
                        $admin_data->wallet_amount = $admin_data->wallet_amount + $total_amt * 2;
                        $admin_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = 1;
                        $transactions->user_type = 'admin';
                        $transactions->type = 'deposite';
                        $transactions->amount = $total_amt * 2;
                        $transactions->message = 'amount deposite';
                        $transactions->save();
                    } else {
                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $buyer_id;
                        $transactions->user_type = $done_by;
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'deal done by '.$user_data->name;
                        $transactions->save();

                        $seller_data->wallet_amount = $seller_data->wallet_amount - $total_amt;
                        $seller_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $seller_id;
                        $transactions->user_type = 'seller';
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'amount withdraw';
                        $transactions->save();

                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $admin_data = User::find(1);
                        $admin_data->wallet_amount = $admin_data->wallet_amount + $total_amt * 2;
                        $admin_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = 1;
                        $transactions->user_type = 'admin';
                        $transactions->type = 'deposite';
                        $transactions->amount = $total_amt * 2;
                        $transactions->message = 'amount deposite';
                        $transactions->save();
                    }



                    //pdf
                    $broker_name = '';
                    $broker_address = '';
                    $broker_country = '';
                    $broker_state = '';
                    $broker_mobile_number = '';
                    $broker_mobile_number_2 = '';
                    $broker_email = '';
                    $broker_url = '';
                    $seller_name_address = '';
                    $seller_station = '';
                    $buyer_name_address = '';
                    $buyer_station = '';
                    $product_name_pdf = '';
                    $deal_date = '';
                    $deal_no_of_bales = '';
                    $deal_price = '';
                    $ref_no = '';
                    $attribute_array_pdf = '';
                    $broker_stamp_image = '';
                    $buyer_stamp_image = '';
                    $seller_stamp_image = '';
                    $broker_header_image = '';

                    $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
                    $deal_price = $make_deal->price;
                    $ref_no = $make_deal->id;
                    $deal_no_of_bales = $make_deal->no_of_bales;
                    if($done_by == "seller"){
                        $seller = Sellers::where('id',$seller_id)->first();
                        if(!empty($seller)){
                            $broker = Brokers::where('code',$seller->referral_code)->first();
                            if(!empty($broker)){
                                $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                                if(!empty($broker_data)){
                                    $country = Country::where('id',$broker_data->country_id)->first();
                                    if(!empty($country)){
                                        $state = State::where('id',$broker_data->state_id)->first();
                                        if(!empty($state)){
                                            $broker_name = $broker->name;
                                            $broker_address = $broker->address;
                                            $broker_country = $country->name;
                                            $broker_state = $state->name;
                                            $broker_mobile_number = $broker->mobile_number;
                                            $broker_mobile_number_2 =$broker->mobile_number_2;
                                            $broker_email =$broker->email;
                                            $broker_url =$broker->website;

                                            $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);
											$broker_stamp_image = '';
											if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);
											}

											$broker_header_img = storage_path('app/public/broker/header_image/' . $broker->header_image);
											$broker_header_image = '';
											if (!empty($broker->header_image) && File::exists($broker_header_img)) {
												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($done_by == "buyer"){
                        $buyer = Buyers::where('id',$buyer_id)->first();
                        if(!empty($buyer)){
                            $broker = Brokers::where('code',$buyer->referral_code)->first();
                            if(!empty($broker)){
                                $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                                if(!empty($broker_data)){
                                    $country = Country::where('id',$broker_data->country_id)->first();
                                    if(!empty($country)){
                                        $state = State::where('id',$broker_data->state_id)->first();
                                        if(!empty($state)){
                                            $broker_name = $broker->name;
                                            $broker_address = $broker->address;
                                            $broker_country = $country->name;
                                            $broker_state = $state->name;
                                            $broker_mobile_number = $broker->mobile_number;
                                            $broker_mobile_number_2 =$broker->mobile_number_2;
                                            $broker_email =$broker->email;
                                            $broker_url =$broker->website;

											$broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);
											$broker_stamp_image = '';
											if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
												$broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
												$file1 = file_get_contents($broker_stamp_img);
												$broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);
											}

											$broker_header_img = storage_path('app/public/broker/header_image/' . $broker->header_image);
											$broker_header_image = '';
											if (!empty($broker->header_image) && File::exists($broker_header_img)) {
												$broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
												$file2 = file_get_contents($broker_header_img);
												$broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
											}
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($make_deal->negotiation_type == "notification"){
                        $notification_detail = Notification::where('id',$post_notification_id)->first();
                        if(!empty($notification_detail)){
                            $product = Product::where('id',$notification_detail->product_id)->first();
                            if(!empty($product)){
                                $product_name_pdf = $product->name;
                            }
                            $attribute_array_push  = [];
                            $attribute = NotificatonDetails::where('notification_id',$notification_detail->id)->get();
                            foreach ($attribute as $value) {
                                array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
                            }
                            $attribute_array_pdf = implode(",",$attribute_array_push);
                        }
                    }

                    $seller_details = Sellers::where('id',$seller_id)->first();
                    if(!empty($seller_details)){
                        $seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
                        if(!empty($seller_sub_details)){
                            $station = Station::where('id',$seller_sub_details->station_id)->first();
                            if(!empty($station)){
                                $seller_name_address = $seller_details->name.' '.$seller_details->address;
                                $seller_station = $station->name;
                                if(!empty($seller_details->image)){

									$seller_stamp_img = storage_path('app/public/seller/profile/' . $seller_details->image);
									$seller_stamp_image = '';
									if (File::exists($seller_stamp_img)) {
										$seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
										$file3 = file_get_contents($seller_stamp_img);
										$seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file3);
									}
                                }
                            }
                        }
                    }

                    $buyer_details = Buyers::where('id',$buyer_id)->first();
                    if(!empty($buyer_details)){
                        $buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
                        if(!empty($buyer_sub_details)){
                            $station = Station::where('id',$buyer_sub_details->station_id)->first();
                            if(!empty($station)){
                                $buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
                                $buyer_station = $station->name;
                                if(!empty($buyer_details->image)){

									$buyer_stamp_img = storage_path('app/public/buyer/profile/' . $buyer_details->image);
									$buyer_stamp_image = '';
									if (File::exists($buyer_stamp_img)) {
										$buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
										$file4 = file_get_contents($buyer_stamp_img);
										$buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file4);
									}
                                }
                            }
                        }
                    }

                    $pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
                    $pdf->getDomPDF()->setHttpContext(
                        stream_context_create([
                            'ssl' => [
                                'allow_self_signed'=> TRUE,
                                'verify_peer' => FALSE,
                                'verify_peer_name' => FALSE,
                            ]
                        ])
                    );

                    $current_time = time();
                    $file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
                    //$pdf->save(public_path().$file_name);
                    Storage::put('public/pdf/'.$file_name, $pdf->output());

                    $save = new DealPdf();
                    $save->deal_id = $make_deal->id;
                    $save->done_by = $make_deal->done_by;
                    $save->seller_id = $make_deal->seller_id;
                    $save->buyer_id = $make_deal->buyer_id;
                    $save->filename = $file_name;
                    $save->save();
                    //pdf

                    $response['status'] = 200;
                    $response['message'] = 'Make Deal done';
				}
			}elseif ($type == "post") {
                $post = Post::where(['id'=>$post_notification_id,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($post)){

                    $total_amt = $post->no_of_bales * $settings->company_commission;
                    $broker_commission_amt = $post->no_of_bales * $settings->broker_commission;
                    if ($done_by == 'seller') {
                        $user_id = $seller_id;
                        $user_data = Sellers::with('broker', 'broker.broker')->where('id',$seller_id)->first();

                        if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Wallet amount not enough';
                            return response($response, 200);
                        }

                        // $user_id = $buyer_id;
                        $buyer_data = Buyers::where('id',$buyer_id)->first();

                        if(!empty($buyer_data) && $buyer_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Buyer\'s Wallet amount not enough';
                            return response($response, 200);
                        }


                        if (!empty($user_data) && $broker_id != $user_data->broker->broker_id) {
                            $broker_changed = 1;
                        }
                    } else {
                        $user_id = $buyer_id;
                        $user_data = Buyers::where('id',$user_id)->first();

                        if(!empty($user_data) && $user_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Wallet amount not enough';
                            return response($response, 200);
                        }

                        // $user_id = $seller_id;
                        $seller_data = Sellers::where('id',$seller_id)->first();

                        if(!empty($seller_data) && $seller_data->wallet_amount < $total_amt){
                            $response['status'] = 404;
                            $response['message'] = 'Seller\'s Wallet amount not enough';
                            return response($response, 200);
                        }
                    }

                    $check = $this->check_queue($buyer_id, $seller_id, $post_notification_id, $type);

                    if ($check == 2) {
                        DealQueue::where(['buyer_id' => $buyer_id, 'seller_id' => $seller_id, 'post_notification_id' => $post_notification_id, 'post_type'=> $type])->delete();

                        $response['status'] = 404;
                        $response['message'] = 'Deal already done sorry for inconvenience!';
                        return response($response, 200);
                    }

                    $default_broker = AddBrokers::where('buyer_id',$user_id)->where('user_type',$done_by)->where('broker_type','default')->first();

					if (empty($default_broker)) {
						$response['status'] = 404;
                        $response['message'] = 'Please select default broker!';
                        return response($response, 200);
					}


                    $post->sold_bales = $post->no_of_bales;
                    $post->remain_bales = 0;
                    $post->status = 'complete';
                    $post->updated_at = date('Y-m-d H:i:s');
                    $post->save();

					$make_deal = new NegotiationComplete();
					$make_deal->negotiation_id = 0;
					$make_deal->done_by = $done_by;
					$without = new WithoutNegotiationMakeDeal();
					$without->post_notification_id = $post->id;
					$without->type = 'post';
					if($post->user_type == "seller"){
						$without->seller_buyer_id = $buyer_id;
						$without->user_type = 'buyer';
						$make_deal->seller_id = $post->seller_buyer_id;
						$make_deal->buyer_id = $buyer_id;
					}
					if($post->user_type == "buyer"){
						$without->seller_buyer_id = $seller_id;
						$without->user_type = 'seller';
						$make_deal->seller_id = $seller_id;
						$make_deal->buyer_id = $post->seller_buyer_id;
					}
					$without->save();
					$make_deal->negotiation_by = 'seller';
					$make_deal->broker_id = $default_broker->broker_id;
					$make_deal->post_notification_id = $post->id;
					$make_deal->negotiation_type = 'post';
					$make_deal->price = $post->price;
					$make_deal->no_of_bales = $post->no_of_bales;
					$make_deal->payment_condition = $post->payment_condition;
					$make_deal->transmit_condition = $post->transmit_condition;
					$make_deal->lab = $post->lab;
					$make_deal->is_deal = '1';
					$make_deal->status = 'pending';
                    $make_deal->seller_otp = $seller_otp;
                    $make_deal->buyer_otp = $buyer_otp;
                    $make_deal->broker_otp = $broker_otp;
                    $make_deal->seller_email_otp = $seller_email_otp;
                    $make_deal->buyer_email_otp = $buyer_email_otp;
                    $make_deal->broker_email_otp = $broker_email_otp;
                    $make_deal->otp_time = date("Y-m-d H:i:s");
					$make_deal->save();

					$deal_id = $make_deal->id;

                    $sellers = Sellers::select('mobile_number','email','name')->where('id',$seller_id)->first();
                    $s_message = "OTP to verify make deal is ". $seller_otp ." - E - Cotton";
                    NotificationHelper::send_otp($sellers->mobile_number,$s_message);

                    $s_data = array('otp'=>$seller_email_otp,'name' => $sellers->name);

                    // Mail::send(['html'=>'mail'], $s_data, function($message) use($sellers) {
                    //     $message->to($sellers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

                    $buyers = Buyers::select('mobile_number','email','name')->where('id',$buyer_id)->first();
                    $by_message = "OTP to verify make deal is ". $buyer_otp ." - E - Cotton";
                    NotificationHelper::send_otp($buyers->mobile_number,$by_message);

                    $by_data = array('otp'=>$buyer_email_otp,'name' => $buyers->name);

                    // Mail::send(['html'=>'mail'], $by_data, function($message) use($buyers) {
                    //     $message->to($buyers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

                    $brokers = Brokers::select('mobile_number','email','name')->where('id',$default_broker->broker_id)->first();
                    $br_message = "OTP to verify make deal is ". $broker_otp ." - E - Cotton";
                    NotificationHelper::send_otp($brokers->mobile_number,$br_message);

                    $br_data = array('otp'=>$broker_email_otp,'name' => $brokers->name);

                    // Mail::send(['html'=>'mail'], $br_data, function($message) use($brokers) {
                    //     $message->to($brokers->email, 'E - Cotton')->subject('Make deal OTP');
                    // });

					$transactions = new Transactions();
					$transactions->user_id = $user_id;
					$transactions->user_type = $done_by;
					$transactions->type = 'withdraw';
					$transactions->amount = $total_amt;
					$transactions->message = 'deal done by '.$user_data->name;
					$transactions->save();

					if ($done_by == 'seller') {
                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $seller_id;
                        $transactions->user_type = $done_by;
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'deal done by '.$user_data->name;
                        $transactions->save();

                        $buyer_data->wallet_amount = $buyer_data->wallet_amount - $total_amt;
                        $buyer_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $buyer_id;
                        $transactions->user_type = 'buyer';
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'amount withdraw';
                        $transactions->save();

                        $admin_data = User::find(1);
                        $admin_data->wallet_amount = $total_amt * 2;
                        $admin_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = 1;
                        $transactions->user_type = 'admin';
                        $transactions->type = 'deposite';
                        $transactions->amount = $total_amt * 2;
                        $transactions->message = 'amount deposite';
                        $transactions->save();
                    } else {
                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $buyer_id;
                        $transactions->user_type = $done_by;
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'deal done by '.$user_data->name;
                        $transactions->save();

                        $seller_data->wallet_amount = $seller_data->wallet_amount - $total_amt;
                        $seller_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = $seller_id;
                        $transactions->user_type = 'seller';
                        $transactions->type = 'withdraw';
                        $transactions->amount = $total_amt;
                        $transactions->message = 'amount withdraw';
                        $transactions->save();

                        $user_data->wallet_amount = $user_data->wallet_amount - $total_amt;
                        $user_data->save();

                        $admin_data = User::find(1);
                        $admin_data->wallet_amount = $admin_data->wallet_amount + $total_amt * 2;
                        $admin_data->save();

                        $transactions = new Transactions();
                        $transactions->user_id = 1;
                        $transactions->user_type = 'admin';
                        $transactions->type = 'deposite';
                        $transactions->amount = $total_amt * 2;
                        $transactions->message = 'amount deposite';
                        $transactions->save();
                    }

                    //pdf
                    $broker_name = '';
                    $broker_address = '';
                    $broker_country = '';
                    $broker_state = '';
                    $broker_mobile_number = '';
                    $broker_mobile_number_2 = '';
                    $broker_email = '';
                    $broker_url = '';
                    $seller_name_address = '';
                    $seller_station = '';
                    $buyer_name_address = '';
                    $buyer_station = '';
                    $product_name_pdf = '';
                    $deal_date = '';
                    $deal_no_of_bales = '';
                    $deal_price = '';
                    $ref_no = '';
                    $attribute_array_pdf = '';
                    $broker_stamp_image = '';
                    $buyer_stamp_image = '';
                    $seller_stamp_image = '';
                    $broker_header_image = '';

                    $deal_date = date('d-M-Y', strtotime($make_deal->created_at));
                    $deal_price = $make_deal->price;
                    $ref_no = $make_deal->id;
                    $deal_no_of_bales = $make_deal->no_of_bales;
                    if($done_by == "seller"){
                        $seller = Sellers::where('id',$seller_id)->first();
                        if(!empty($seller)){
                            $broker = Brokers::where('code',$seller->referral_code)->first();
                            if(!empty($broker)){
                                $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                                if(!empty($broker_data)){
                                    $country = Country::where('id',$broker_data->country_id)->first();
                                    if(!empty($country)){
                                        $state = State::where('id',$broker_data->state_id)->first();
                                        if(!empty($state)){
                                            $broker_name = $broker->name;
                                            $broker_address = $broker->address;
                                            $broker_country = $country->name;
                                            $broker_state = $state->name;
                                            $broker_mobile_number = $broker->mobile_number;
                                            $broker_mobile_number_2 =$broker->mobile_number_2;
                                            $broker_email =$broker->email;
                                            $broker_url =$broker->website;

                                            $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);

                                            $broker_stamp_image = '';
                                            if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
                                                $broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
                                                $file1 = file_get_contents($broker_stamp_img);
                                                $broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);
                                            }

                                            $broker_header_img = storage_path('app/public/broker/stamp_image/' . $broker->header_image);
                                            if (!empty($broker->header_image) && File::exists($broker_header_img)) {
                                                $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                                                $file2 = file_get_contents($broker_header_img);
                                                $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($done_by == "buyer"){
                        $buyer = Buyers::where('id',$buyer_id)->first();
                        if(!empty($buyer)){
                            $broker = Brokers::where('code',$buyer->referral_code)->first();
                            if(!empty($broker)){
                                $broker_data = UserDetails::where(['user_type'=>'broker','user_id'=>$broker->id])->first();
                                if(!empty($broker_data)){
                                    $country = Country::where('id',$broker_data->country_id)->first();
                                    if(!empty($country)){
                                        $state = State::where('id',$broker_data->state_id)->first();
                                        if(!empty($state)){
                                            $broker_name = $broker->name;
                                            $broker_address = $broker->address;
                                            $broker_country = $country->name;
                                            $broker_state = $state->name;
                                            $broker_mobile_number = $broker->mobile_number;
                                            $broker_mobile_number_2 =$broker->mobile_number_2;
                                            $broker_email =$broker->email;
                                            $broker_url =$broker->website;

                                            // $broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
                                            // $file1 = file_get_contents($broker_stamp_img);
                                            // $broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);

                                            // $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                                            // $file2 = file_get_contents($broker_header_img);
                                            // $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);

                                            $broker_stamp_img = storage_path('app/public/broker/stamp_image/' . $broker->stamp_image);

                                            $broker_stamp_image = '';
                                            if (!empty($broker->stamp_image) && File::exists($broker_stamp_img)) {
                                                $broker_stamp_img = asset('storage/app/public/broker/stamp_image/' . $broker->stamp_image);
                                                $file1 = file_get_contents($broker_stamp_img);
                                                $broker_stamp_image ='data:image/jpeg;base64,'.base64_encode($file1);
                                            }

                                            $broker_header_img = storage_path('app/public/broker/stamp_image/' . $broker->header_image);
                                            if (!empty($broker->header_image) && File::exists($broker_header_img)) {
                                                $broker_header_img = asset('storage/app/public/broker/header_image/' . $broker->header_image);
                                                $file2 = file_get_contents($broker_header_img);
                                                $broker_header_image = 'data:image/jpeg;base64,'.base64_encode($file2);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if($make_deal->negotiation_type == "post"){
                        $post_detail = Post::where('id',$post_notification_id)->first();
                        if(!empty($post_detail)){
                            $product = Product::where('id',$post_detail->product_id)->first();
                            if(!empty($product)){
                                $product_name_pdf = $product->name;
                            }
                            $attribute_array_push  = [];
                            $attribute = PostDetails::where('post_id',$post_detail->id)->get();
                            foreach ($attribute as $value) {
                                array_push($attribute_array_push, $value->attribute.':'.$value->attribute_value);
                            }
                            $attribute_array_pdf = implode(",",$attribute_array_push);
                        }
                    }

                    $seller_details = Sellers::where('id',$seller_id)->first();
                    if(!empty($seller_details)){
                        $seller_sub_details = UserDetails::where(['user_type'=>'seller','user_id'=>$seller_details->id])->first();
                        if(!empty($seller_sub_details)){
                            $station = Station::where('id',$seller_sub_details->station_id)->first();
                            if(!empty($station)){
                                $seller_name_address = $seller_details->name.' '.$seller_details->address;
                                $seller_station = $station->name;
                                if(!empty($seller_details->image)){
                                    $seller_stamp_img = storage_path('app/public/seller/profile/' . $seller_details->image);
									$seller_stamp_image = '';
									if (File::exists($seller_stamp_img)) {
										$seller_stamp_img = asset('storage/app/public/seller/profile/' . $seller_details->image);
										$file3 = file_get_contents($seller_stamp_img);
										$seller_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file3);
									}
                                }
                            }
                        }
                    }

                    $buyer_details = Buyers::where('id',$buyer_id)->first();
                    if(!empty($buyer_details)){
                        $buyer_sub_details = UserDetails::where(['user_type'=>'buyer','user_id'=>$buyer_details->id])->first();
                        if(!empty($buyer_sub_details)){
                            $station = Station::where('id',$buyer_sub_details->station_id)->first();
                            if(!empty($station)){
                                $buyer_name_address = $buyer_details->name.' '.$buyer_details->address;
                                $buyer_station = $station->name;
                                if(!empty($buyer_details->image)){
                                    $buyer_stamp_img = storage_path('app/public/buyer/profile/' . $buyer_details->image);
									$buyer_stamp_image = '';
									if (File::exists($buyer_stamp_img)) {
										$buyer_stamp_img = asset('storage/app/public/buyer/profile/' . $buyer_details->image);
										$file4 = file_get_contents($buyer_stamp_img);
										$buyer_stamp_image = 'data:image/jpeg;base64,'.base64_encode($file4);
									}
                                }
                            }
                        }
                    }

                    $pdf = PDF::loadView('download', ['broker_name' => $broker_name,'broker_address'=>$broker_address,'broker_country'=>$broker_country,'broker_state'=>$broker_state,'broker_mobile_number'=>$broker_mobile_number,'broker_email'=>$broker_email,'seller_name_address'=>$seller_name_address,'seller_station'=>$seller_station,'buyer_name_address'=>$buyer_name_address,'buyer_station'=>$buyer_station,'broker_url'=>$broker_url,'broker_mobile_number_2'=>$broker_mobile_number_2,'deal_date'=>$deal_date,'product_name_pdf'=>$product_name_pdf,'deal_no_of_bales'=>$deal_no_of_bales,'deal_price'=>$deal_price,'ref_no'=>$ref_no,'attribute_array_pdf'=>$attribute_array_pdf,'broker_stamp_image'=>$broker_stamp_image,'broker_header_image'=>$broker_header_image,'seller_stamp_image'=>$seller_stamp_image,'buyer_stamp_image'=>$buyer_stamp_image])->setOptions(['defaultFont' => 'sans-serif']);
                    $pdf->getDomPDF()->setHttpContext(
                        stream_context_create([
                            'ssl' => [
                                'allow_self_signed'=> TRUE,
                                'verify_peer' => FALSE,
                                'verify_peer_name' => FALSE,
                            ]
                        ])
                    );

                    $current_time = time();
                    $file_name = $make_deal->id.'_'.$current_time.'_deal.pdf';
                    //$pdf->save(public_path().$file_name);
                    Storage::put('public/pdf/'.$file_name, $pdf->output());

                    $save = new DealPdf();
                    $save->deal_id = $make_deal->id;
                    $save->done_by = $make_deal->done_by;
                    $save->seller_id = $make_deal->seller_id;
                    $save->buyer_id = $make_deal->buyer_id;
                    $save->filename = $file_name;
                    $save->save();
                    //pdf

                    $response['status'] = 200;
                    $response['message'] = 'Make Deal done';
                }
			}else{
				$response['status'] = 404;
			}

		}

        $fcm_token = "";
        $user = [];
        if ($done_by == "seller") {
            $user_type = "buyer";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$buyer_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Sellers::select('name')->where('id',$seller_id)->first();
        }else if($done_by == 'buyer'){
            $user_type = "seller";
            $seller_data = DeviceDetails::select('fcm_token')->where('user_type',$user_type)->where('user_id',$seller_id)->first();

            if (!empty($seller_data->fcm_token)) {
                $fcm_token = $seller_data->fcm_token;
            }
            $user = Buyers::select('name')->where('id',$buyer_id)->first();
        }

        if (!empty($fcm_token)) {
            $json_array = [
                "registration_ids" => [$fcm_token],
                "data" => [
                    'user_type' => $done_by,
                ],
                "notification" => [
                    "body" => "Notification send by ".$user->name,
                    "title" => "Deal Done Notification",
                    "icon" => "ic_launcher"
                ]
            ];
            NotificationHelper::notification($json_array,$user_type);
        }

        // Remove deal queue
        DealQueue::where(['buyer_id' => $buyer_id, 'seller_id' => $seller_id, 'post_notification_id' => $post_notification_id, 'post_type'=> $type])->delete();

		$response['data'] = ['deal_id' => $deal_id];

		return response($response, 200);
	}
   public function negotiation_list_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

		$negotiation_array = [];
		$negotiation = Negotiation::with('buyer')->where(['seller_id'=>$seller_id])->orderBy('id','DESC')->skip($offset)->take($limit)->get();

        $post_ids = [];
        $notification_ids = [];
        $post_negotiation_buyer_ids = [];
        $notification_negotiation_buyer_ids = [];

        if(count($negotiation)>0){

            foreach($negotiation as $value){
                if($value->negotiation_type == 'post'){
                    // $post = Post::with('product')->where('id',$value->post_notification_id)->first();
                    // $product_id = $post->product_id;
                    // $product_name = $post->product->name;

                    $post =  Post::where('id',$value->post_notification_id)->where('status', '<>', 'cancel')->first();
                    if(!empty($post)){
                    	if($post->remain_bales <> 0){
                    		array_push($post_ids, $post->id);
                    	}
                    }
                    array_push($post_negotiation_buyer_ids, $value->buyer_id);
                }
                if($value->negotiation_type == 'notification'){
                    // $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();
                    // $product_id = $notification->product_id;
                    // $product_name = $notification->product->name;

                     $notification =  Notification::where('id',$value->post_notification_id)->where('status', '<>', 'cancel')->first();
                    if(!empty($notification)){
                    	if($notification->remain_bales <> 0){
                    		array_push($notification_ids, $notification->id);
                    	}
                    }
					array_push($notification_negotiation_buyer_ids, $value->buyer_id);
                }
                // $negotiation_array [] = [
                //     'negotiation_id' => $value->id,
                //     'post_notification_id' => $value->post_notification_id,
                //     'product_id' => $product_id,
                //     'product_name' => $product_name,
                //     'buyer_id' => $value->buyer_id,
                //     'buyer_name' => $value->buyer->name,
                //     'prev_price' => $value->prev_price,
                //     'new_price' => $value->price,
                //     'negotiation_by' => $value->negotiation_by,
                //     'best_price' => "",
                //     'best_bales' => "",
                //     'best_name' => "",
                // ];
            }

            $negotiation_post_arr_temp = [];
            $negotiation_post_arr = [];
			$negotiation_notification_arr = [];
			$negotiation_notification_arr_temp = [];
            $unique_post_ids = array_unique($post_ids);
			$unique_notification_ids = array_unique($notification_ids);
			$unique_post_negotiation_buyer_ids = array_unique($post_negotiation_buyer_ids);
			$unique_notification_negotiation_buyer_ids = array_unique($notification_negotiation_buyer_ids);

			foreach ($unique_post_ids as $i1) {
                $best_price = [];

				$post_data = Post::with('product','seller','buyer')->where(['id'=>$i1,'is_active'=>0])->where('status', '<>', 'cancel')->first();
				if(!empty($post_data)){
                    $post_array = [];
                        $product_name = $post_data->product->name;
				       $name = '';
				       $broker_name ='';
				        if($post_data->user_type == "seller"){
					        $name = $post_data->seller->name;
                            $broker_data = Brokers::where('code',$post_data->seller->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }
				        if($post_data->user_type == "buyer"){
                            $name = $post_data->buyer->name;
                            $broker_data = Brokers::where('code',$post_data->buyer->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }

						foreach ($unique_post_negotiation_buyer_ids as $i11) {
						$negotiation_post = Negotiation::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['buyer_id'=>$i11,'post_notification_id'=>$i1,'seller_id'=>$seller_id,'negotiation_type'=>'post'])->orderBy('id','DESC')->first();

						if(!empty($negotiation_post)){
                            $seller_name = !empty($negotiation_post->seller->name) ? $negotiation_post->seller->name :'';
                            $buyer_name = !empty($negotiation_post->buyer->name) ? $negotiation_post->buyer->name :'';

                            $transmit_condition_name = !empty($negotiation_post->transmit_conditions->name) ? $negotiation_post->transmit_conditions->name :'';

                            $payment_condition_name = !empty($negotiation_post->payment_conditions->name) ? $negotiation_post->payment_conditions->name :'';

                            $lab_name = !empty($negotiation_post->labs->name) ? $negotiation_post->labs->name :'';

							$post_array[] = [
                                'negotiation_id' => $negotiation_post->id,
								'post_notification_id' => $negotiation_post->post_notification_id,
								'buyer_id' => $negotiation_post->buyer_id,
								'buyer_name' => $buyer_name,
								'seller_id' => $negotiation_post->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $negotiation_post->negotiation_by,
								'negotiation_type' => $negotiation_post->negotiation_type,
								'current_price' => $negotiation_post->price,
								'prev_price' => $negotiation_post->prev_price,
								'current_no_of_bales' => $negotiation_post->bales,
								'prev_no_of_bales' => $negotiation_post->prev_bales,
								'negotiation_type' => $negotiation_post->negotiation_type,
								'transmit_condition' => $transmit_condition_name,
								'payment_condition' => $payment_condition_name,
								'lab' => $lab_name,
								'broker_name' => $broker_name,
							];

                            array_push($best_price, $negotiation_post->price);
						}
					}

					if(count($best_price)>1){
						$max = max($best_price);
						foreach ($post_array as $post_value) {
							if($post_value['current_price'] == $max){
								$negotiation_post_arr_temp[] = [
									'post_id' => $post_data->id,
									'product_id' => $post_data->product_id,
									'product_name' => $product_name,
									'seller_buyer_id' => $post_data->seller_buyer_id,
									'status' => $post_data->status,
									'name' => $name,
									'broker_name' => $broker_name,
									'user_type' => $post_data->user_type,
									'no_of_bales' => $post_data->no_of_bales,
									'price' => $post_data->price,
									'address' => $post_data->address,
									'd_e' => $post_data->d_e,
									'buy_for' => $post_data->buy_for,
									'negotiation_type' => 'post',
									'spinning_meal_name' => $post_data->spinning_meal_name,
									'best_price' => $post_value['current_price'],
									'best_bales' => $post_value['current_no_of_bales'],
									'best_name' => $post_value['buyer_name'],
									'count' => count($post_array),
									'post_detail' => $post_array,
								];
							}
						}
					}else{
						$negotiation_post_arr_temp[] = [
							'post_id' => $post_data->id,
							'status' => $post_data->status,
							'seller_buyer_id' => $post_data->seller_buyer_id,
							'name' => $name,
							'broker_name'=>$broker_name,
							'user_type' => $post_data->user_type,
							'product_id' => $post_data->product_id,
							'product_name' => $product_name,
							'no_of_bales' => $post_data->no_of_bales,
							'price' => $post_data->price,
							'address' => $post_data->address,
							'd_e' => $post_data->d_e,
							'buy_for' => $post_data->buy_for,
							'negotiation_type' => 'post',
							'spinning_meal_name' => $post_data->spinning_meal_name,
							'best_price' => '',
							'best_bales' => '',
							'best_name' => '',
							'count' => '',
							'post_detail' => $post_array,
						];
					}
				}
			}
			foreach ($unique_notification_ids as $i2) {
                    $best_price = [];
					$notification = Notification::with('product','seller','buyer')->where(['id'=>$i2,'is_active'=>0])->where('status', '<>', 'cancel')->first();
					if(!empty($notification)){
						$notification_array = [];
						$product_name = $notification->product->name;
				       $name = '';
				       $broker_name ='';
				        if($notification->user_type == "seller"){
					        $name = !empty($notification->seller->name) ? $notification->seller->name : '';
                            $broker_data = Brokers::where('code',$notification->seller->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }
				        if($notification->user_type == "buyer"){
                            $name = !empty($notification->buyer->name) ? $notification->buyer->name : '';
                            $broker_data = Brokers::where('code',$notification->buyer->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }
				        foreach ($unique_notification_negotiation_buyer_ids as $i22) {
						$negotiation_notification = Negotiation::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['buyer_id'=>$i22,'post_notification_id'=>$i2,'seller_id'=>$seller_id,'negotiation_type'=>'notification'])->orderBy('id','DESC')->first();

						if(!empty($negotiation_notification)){
                            $seller_name = !empty($negotiation_notification->seller->name) ? $negotiation_notification->seller->name :'';
                            $buyer_name = !empty($negotiation_notification->buyer->name) ? $negotiation_notification->buyer->name :'';

                            $transmit_condition_name = !empty($negotiation_notification->transmit_conditions->name) ? $negotiation_notification->transmit_conditions->name :'';

                            $payment_condition_name = !empty($negotiation_notification->payment_conditions->name) ? $negotiation_notification->payment_conditions->name :'';

                            $lab_name = !empty($negotiation_notification->labs->name) ? $negotiation_notification->labs->name :'';

                            $notification_array[] = [
                                'negotiation_id' => $negotiation_notification->id,
                                'buyer_id' => $negotiation_notification->buyer_id,
                                'buyer_name' => $buyer_name,
                                'seller_id' => $negotiation_notification->seller_id,
                                'seller_name' => $seller_name,
                                'negotiation_by' => $negotiation_notification->negotiation_by,
                                'negotiation_type' => $negotiation_notification->negotiation_type,
                                'current_price' => $negotiation_notification->price,
                                'prev_price' => $negotiation_notification->prev_price,
                                'current_no_of_bales' => $negotiation_notification->bales,
                                'negotiation_type' => $negotiation_notification->negotiation_type,
                                'prev_no_of_bales' => $negotiation_notification->prev_bales,
                                'transmit_condition' => $transmit_condition_name,
                                'payment_condition' => $payment_condition_name,
                                'lab' => $lab_name,
                                'post_notification_id' => $negotiation_notification->post_notification_id,
                                'broker_name' => isset($negotiation_notification->broker->name) ? $negotiation_notification->broker->name : '',
                            ];

                            array_push($best_price, $negotiation_notification->price);
						}
					}

					if(count($best_price)>1){
						$max = max($best_price);
						foreach ($notification_array as $notification_value) {
							if($notification_value['current_price'] == $max){
								$negotiation_notification_arr_temp[] = [
									'notification_id' => $notification->id,
									'status' => $notification->status,
									'seller_buyer_id' => $notification->seller_buyer_id,
									'name' => $name,
									'broker_name' => $broker_name,
									'user_type' => $notification->user_type,
									'product_id' => $notification->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $notification->no_of_bales,
									'price' => $notification->price,
									'address' => $notification->address,
									'd_e' => $notification->d_e,
									'buy_for' => $notification->buy_for,
									'spinning_meal_name' => $notification->spinning_meal_name,
									'negotiation_type' => 'notification',
									'best_price' => $notification_value['current_price'],
									'best_bales' => $notification_value['current_no_of_bales'],
									'best_name' => $notification_value['buyer_name'],
									'count' => count($notification_array),
									'notification_detail' =>(!empty($notification_array))?$notification_array:''
								];
							}
						}
					}else{
						$negotiation_notification_arr_temp[] = [
							'notification_id' => $notification->id,
							'status' => $notification->status,
							'seller_buyer_id' => $notification->seller_buyer_id,
							'name' => $name,
							'broker_name'=>$broker_name,
							'user_type' => $notification->user_type,
							'product_id' => $notification->product_id,
							'product_name' => $product_name,
							'no_of_bales' => $notification->no_of_bales,
							'price' => $notification->price,
							'address' => $notification->address,
							'd_e' => $notification->d_e,
							'buy_for' => $notification->buy_for,
							'spinning_meal_name' => $notification->spinning_meal_name,
							'negotiation_type' => 'notification',
							'best_price' => '',
							'best_bales' => '',
							'best_name' => '',
							'count' => '',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}

			//when same price then multi-dimensional array unique based on post_id
			$final_post = array_unique(array_column($negotiation_post_arr_temp, 'post_id'));
			$negotiation_post_arr = array_intersect_key($negotiation_post_arr_temp, $final_post);

			$final_notification = array_unique(array_column($negotiation_notification_arr_temp, 'notification_id'));
			$negotiation_notification_arr = array_intersect_key($negotiation_notification_arr_temp, $final_notification);

			//end
            $negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);

			$response['status'] = 200;
			$response['message'] = 'Negotiation list';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}

     public function negotiation_list_buyer_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

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

		$negotiation_array = [];
		$negotiation = Negotiation::with('seller')->where(['buyer_id'=>$buyer_id])->orderBy('id','DESC')->skip($offset)->take($limit)->get();
        $post_ids = [];
        $notification_ids = [];
        $post_negotiation_seller_ids = [];
        $notification_negotiation_seller_ids = [];
		if(count($negotiation)>0){
            foreach($negotiation as $value){
                if($value->negotiation_type == 'post'){
                    // $post = Post::with('product')->where('id',$value->post_notification_id)->first();
                    // $product_id = $post->product_id;
                    // $product_name = $post->product->name;
                    $post =  Post::where('id',$value->post_notification_id)->first();
                    if(!empty($post)){
                    	if($post->remain_bales <> 0){
                    		array_push($post_ids, $post->id);
                    	}
                    }
					array_push($post_negotiation_seller_ids, $value->seller_id);
                }
                if($value->negotiation_type == 'notification'){
                    // $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();
                    // $product_id = $notification->product_id;
                    // $product_name = $notification->product->name;
                    $notification =  Notification::where('id',$value->post_notification_id)->first();
                    if(!empty($notification)){
                    	if($notification->remain_bales <> 0){
                    		array_push($notification_ids, $notification->id);
                    	}
                    }
					array_push($notification_negotiation_seller_ids, $value->seller_id);
                }
                // $negotiation_array [] = [
                //     'negotiation_id' => $value->id,
                //     'post_notification_id' => $value->post_notification_id,
                //     'product_id' => $product_id,
                //     'product_name' => $product_name,
                //     'seller_id' => $value->seller_id,
                //     'seller_name' => $value->buyer->name,
                //     'prev_price' => $value->prev_price,
                //     'new_price' => $value->price,
                //     'negotiation_by' => $value->negotiation_by,
                //     'best_price' => "",
                //     'best_bales' => "",
                //     'best_name' => "",
                // ];
            }

            $negotiation_post_arr_temp = [];
            $negotiation_post_arr = [];
			$negotiation_notification_arr = [];
			$negotiation_notification_arr_temp = [];
			$unique_post_ids = array_unique($post_ids);
			$unique_notification_ids = array_unique($notification_ids);
			$unique_post_negotiation_seller_ids = array_unique($post_negotiation_seller_ids);
			$unique_notification_negotiation_seller_ids = array_unique($notification_negotiation_seller_ids);

            foreach ($unique_post_ids as $i1) {
                $best_price = [];
				$post_data = Post::with('product','seller','buyer')->where(['id'=>$i1,'is_active'=>0])->where('status', '<>', 'cancel')->first();
				if(!empty($post_data)){
                        $best_price = [];
                        $post_array = [];
                        $product_name = $post_data->product->name;
				       $name = '';
				       $broker_name ='';
				        if($post_data->user_type == "seller"){
					        $name = $post_data->seller->name;
                            $broker_data = Brokers::where('code',$post_data->seller->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }
				        if($post_data->user_type == "buyer"){
                            $name = $post_data->buyer->name;
                            $broker_data = Brokers::where('code',$post_data->buyer->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }

						foreach ($unique_post_negotiation_seller_ids as $i11) {
						$negotiation_post = Negotiation::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['seller_id'=>$i11,'post_notification_id'=>$i1,'buyer_id'=>$buyer_id,'negotiation_type'=>'post'])->orderBy('id','DESC')->first();
						if(!empty($negotiation_post)){
							$seller_name = !empty($negotiation_post->seller->name) ? $negotiation_post->seller->name :'';
                            $buyer_name = !empty($negotiation_post->buyer->name) ? $negotiation_post->buyer->name :'';

                            $transmit_condition_name = !empty($negotiation_post->transmit_conditions->name) ? $negotiation_post->transmit_conditions->name :'';

                            $payment_condition_name = !empty($negotiation_post->payment_conditions->name) ? $negotiation_post->payment_conditions->name :'';

                            $lab_name = !empty($negotiation_post->labs->name) ? $negotiation_post->labs->name :'';

							$post_array[] = [
								'negotiation_id' => $negotiation_post->id,
								'buyer_id' => $negotiation_post->buyer_id,
								'buyer_name' => $buyer_name,
								'seller_id' => $negotiation_post->seller_id,
								'seller_name' => $seller_name,
								'negotiation_by' => $negotiation_post->negotiation_by,
								'negotiation_type' => $negotiation_post->negotiation_type,
								'current_price' => $negotiation_post->price,
								'prev_price' => $negotiation_post->prev_price,
								'current_no_of_bales' => $negotiation_post->bales,
								'prev_no_of_bales' => $negotiation_post->prev_bales,
								'transmit_condition' => $transmit_condition_name,
								'payment_condition' => $payment_condition_name,
								'lab' => $lab_name,
								'post_notification_id' => $negotiation_post->post_notification_id,
								'broker_name' => isset($negotiation_post->broker->name) ? $negotiation_post->broker->name : '',
							];
                            array_push($best_price, $negotiation_post->price);
                        }
					}

					if(count($best_price)>1){
						$max = max($best_price);
						foreach ($post_array as $post_value) {
							if($post_value['current_price'] == $max){

								$negotiation_post_arr_temp[] = [
									'post_id' => $post_data->id,
									'status' => $post_data->status,
									'seller_buyer_id' => $post_data->seller_buyer_id,
									'name' => $name,
									'broker_name' =>$broker_name,
									'user_type' => $post_data->user_type,
									'product_id' => $post_data->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $post_data->no_of_bales,
									'price' => $post_data->price,
									'address' => $post_data->address,
									'd_e' => $post_data->d_e,
									'buy_for' => $post_data->buy_for,
									'negotiation_type' => 'post',
									'spinning_meal_name' => $post_data->spinning_meal_name,
									'best_price' => $post_value['current_price'],
									'best_bales' => $post_value['current_no_of_bales'],
									'best_name' => $post_value['seller_name'],
									'count' => count($post_array),
									'post_detail' => $post_array,
								];
							}
						}
					}else{
						$negotiation_post_arr_temp[] = [
							'post_id' => $post_data->id,
							'status' => $post_data->status,
							'seller_buyer_id' => $post_data->seller_buyer_id,
							'name' => $name,
							'broker_name'=>$broker_name,
							'user_type' => $post_data->user_type,
							'product_id' => $post_data->product_id,
							'product_name' => $product_name,
							'no_of_bales' => $post_data->no_of_bales,
							'price' => $post_data->price,
							'address' => $post_data->address,
							'd_e' => $post_data->d_e,
							'buy_for' => $post_data->buy_for,
							'spinning_meal_name' => $post_data->spinning_meal_name,
							'negotiation_type' => 'post',
							'best_price' => '',
							'best_bales' => '',
							'best_name' => '',
							'count' => '',
							'post_detail' => $post_array,
						];
					}
				}
			}
			foreach ($unique_notification_negotiation_seller_ids as $i22) {
				foreach ($unique_notification_ids as $i2) {
                    $best_price = [];
					$notification = Notification::with('product','seller','buyer')->where(['id'=>$i2,'is_active'=>0])->first();
					if(!empty($notification)){
						$notification_array = [];
						$notification_array = [];
						$product_name = $notification->product->name;
				       $name = '';
				       $broker_name ='';
				        if($notification->user_type == "seller"){
					        $name = !empty($notification->seller->name) ? $notification->seller->name : '';
                            $broker_data = Brokers::where('code',$notification->seller->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }
				        if($notification->user_type == "buyer"){
                            $name = !empty($notification->buyer->name) ? $notification->buyer->name : '';
                            $broker_data = Brokers::where('code',$notification->buyer->referral_code)->first();
                            if(!empty($broker_data)){
                                $broker_name = $broker_data->name;
                            }
				        }

						$negotiation_notification = Negotiation::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['seller_id'=>$i22,'post_notification_id'=>$i2,'buyer_id'=>$buyer_id,'negotiation_type'=>'notification'])->orderBy('id','DESC')->first();
						if(!empty($negotiation_notification)){
                            $seller_name = !empty($negotiation_notification->seller->name) ? $negotiation_notification->seller->name :'';
                            $buyer_name = !empty($negotiation_notification->buyer->name) ? $negotiation_notification->buyer->name :'';

                            $transmit_condition_name = !empty($negotiation_notification->transmit_conditions->name) ? $negotiation_notification->transmit_conditions->name :'';

                            $payment_condition_name = !empty($negotiation_notification->payment_conditions->name) ? $negotiation_notification->payment_conditions->name :'';

                            $lab_name = !empty($negotiation_notification->labs->name) ? $negotiation_notification->labs->name :'';

                            $notification_array[] = [
                                'negotiation_id' => $negotiation_notification->id,
                                'buyer_id' => $negotiation_notification->buyer_id,
                                'buyer_name' => $buyer_name,
                                'seller_id' => $negotiation_notification->seller_id,
                                'seller_name' => $seller_name,
                                'negotiation_by' => $negotiation_notification->negotiation_by,
                                'negotiation_type' => $negotiation_notification->negotiation_type,
                                'current_price' => $negotiation_notification->price,
                                'prev_price' => $negotiation_notification->prev_price,
                                'current_no_of_bales' => $negotiation_notification->bales,
                                'prev_no_of_bales' => $negotiation_notification->prev_bales,
                                'transmit_condition' => $transmit_condition_name,
                                'payment_condition' => $payment_condition_name,
                                'lab' => $lab_name,
                                'post_notification_id' => $negotiation_notification->post_notification_id,
								'broker_name' => isset($negotiation_notification->broker->name) ? $negotiation_notification->broker->name : '',
                            ];
                            array_push($best_price, $negotiation_notification->price);
                    }
				}

				if(count($best_price)>1){
						$max = max($best_price);
						foreach ($notification_array as $notification_value) {
							if($notification_value['current_price'] == $max){
								$negotiation_notification_arr_temp[] = [
									'notification_id' => $notification->id,
									'status' => $notification->status,
									'seller_buyer_id' => $notification->seller_buyer_id,
									'name' => $name,
									'broker_name'=>$broker_name,
									'user_type' => $notification->user_type,
									'product_id' => $notification->product_id,
									'product_name' => $product_name,
									'no_of_bales' => $notification->no_of_bales,
									'price' => $notification->price,
									'address' => $notification->address,
									'd_e' => $notification->d_e,
									'buy_for' => $notification->buy_for,
									'negotiation_type' => 'notification',
									'spinning_meal_name' => $notification->spinning_meal_name,
									'best_price' => $notification_value['current_price'],
									'best_bales' => $notification_value['current_no_of_bales'],
									'best_name' => $notification_value['seller_name'],
									'count' => count($post_array),
									'notification_detail' =>(!empty($notification_array))?$notification_array:''
								];
							}
						}
					}else{
						$negotiation_notification_arr_temp[] = [
							'notification_id' => $notification->id,
							'status' => $notification->status,
							'seller_buyer_id' => $notification->seller_buyer_id,
							'name' => $name,
							'broker_name'=>$broker_name,
							'user_type' => $notification->user_type,
							'product_id' => $notification->product_id,
							'product_name' => $product_name,
							'no_of_bales' => $notification->no_of_bales,
							'price' => $notification->price,
							'address' => $notification->address,
							'd_e' => $notification->d_e,
							'buy_for' => $notification->buy_for,
							'spinning_meal_name' => $notification->spinning_meal_name,
							'negotiation_type' => 'notification',
							'best_price' => '',
							'best_bales' => '',
							'best_name' => '',
							'count' => '',
							'notification_detail' =>(!empty($notification_array))?$notification_array:''
						];
					}
				}
			}
			//when same price then multi-dimensional array unique based on post_id
			$final_post = array_unique(array_column($negotiation_post_arr_temp, 'post_id'));
			$negotiation_post_arr = array_intersect_key($negotiation_post_arr_temp, $final_post);

			$final_notification = array_unique(array_column($negotiation_notification_arr_temp, 'notification_id'));
			$negotiation_notification_arr = array_intersect_key($negotiation_notification_arr_temp, $final_notification);
			//end
			$negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);

			$response['status'] = 200;
			$response['message'] = 'Negotiation list';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}

	public function negotiation_detail_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$type = isset($content->type) ? $content->type : '';

		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
			'type' => $type
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
            'buyer_id' => 'required',
            'post_notification_id' => 'required',
            'type' => 'required'
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

		$negotiation_array = [];
		$negotiation = Negotiation::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id])->first();
        // dd($negotiation);
		if(!empty($negotiation)) {
            $seller_name = !empty($negotiation->seller->name) ? $negotiation->seller->name :'';
            $buyer_name = !empty($negotiation->buyer->name) ? $negotiation->buyer->name :'';
            $broker_name = !empty($negotiation->broker->name) ? $negotiation->broker->name :'';

            $transmit_condition_name = !empty($negotiation->transmit_conditions->name) ? $negotiation->transmit_conditions->name :'';

            $payment_condition_name = !empty($negotiation->payment_conditions->name) ? $negotiation->payment_conditions->name :'';

            $lab_name = !empty($negotiation->labs->name) ? $negotiation->labs->name :'';

            $attribute_array = [];
            $post_price = "";
            $post_bales = "";
			$sold_bales = 0;
            $remain_bales = 0;
            if($negotiation->negotiation_type == 'post'){
                $post = Post::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $post->product_id;
                $product_name = $post->product->name;
                $post_price = $post->price;
                $post_bales = $post->no_of_bales;
				$sold_bales = $post->sold_bales;
				$remain_bales = $post->remain_bales;

                $attribute = PostDetails::where('post_id',$post->id)->get();
                foreach ($attribute as $val) {
                    $attribute_array[] = [
                        'id' => $val->id,
                        'post_id' => $val->post_id,
                        'attribute' => $val->attribute,
                        'attribute_value' => $val->attribute_value,
                    ];
                }
            }
            if($negotiation->negotiation_type == 'notification'){
                $notification = Notification::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $notification->product_id;
                $product_name = $notification->product->name;
                $post_price = $notification->price;
                $post_bales = $notification->no_of_bales;

                $attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
                foreach ($attribute as $val) {
                    $attribute_array[] = [
                        'id' => $val->id,
                        'notification_id' => $val->notification_id,
                        'attribute' => $val->attribute,
                        'attribute_value' => $val->attribute_value,
                    ];
                }

                // $attrinutes = ProductAttribute::select('label')->where('product_id',$product_id)->get();
            }
            if($negotiation->header == 1){
                $header_name = "Subject To";
            }else{
                $header_name = "Confirm To";
            }

			$negotiation_array = [
                'negotiation_id' => $negotiation->id,
                'seller_id' => $negotiation->seller_id,
                'seller_name' => $seller_name,
                'buyer_id' => $negotiation->buyer_id,
                'buyer_name' => $buyer_name,
                'broker_id' => $negotiation->broker_id,
                'broker_name' => $broker_name,
                'negotiation_by' => $negotiation->negotiation_by,
                'post_notification_id' => $negotiation->post_notification_id,
                'negotiation_type' => $negotiation->negotiation_type,
                'current_price' => $negotiation->price,
                'prev_price' => $negotiation->prev_price,
                'current_no_of_bales' => $negotiation->bales,
                'prev_no_of_bales' => $negotiation->prev_bales,
                'transmit_condition' => $transmit_condition_name,
                'payment_condition' => $payment_condition_name,
                'lab' => $lab_name,
                'notes' => $negotiation->notes,
                'header' => $negotiation->header,
                'header_name' => $header_name,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'post_price' => $post_price,
                'post_bales' => $post_bales,
				'sold_bales' => $sold_bales,
		        'remain_bales' => $remain_bales,
                'attribute_array' => $attribute_array,
			];
			$response['status'] = 200;
			$response['message'] = 'Negotiation Detail';
			$response['data'] = $negotiation_array;
		}else{
			if($type == "post"){
				$post = Post::with('product')->where('id',$post_notification_id)->first();
				if(!empty($post)){
					$seller_name = '';
					$seller_id = '';
					$broker_id = '';
					$broker_name = '';
					if($post->user_type == "seller"){
						$seller_data = Sellers::with('broker.broker')->where('id',$post->seller_buyer_id)->first();
						if(!empty($seller_data)){
							$seller_name = $seller_data->name;
							$seller_id = $seller_data->id;
							$broker_id = isset($seller_data->broker->broker->id) ? $seller_data->broker->broker->id : '';
							$broker_name = isset($seller_data->broker->broker->name) ? $seller_data->broker->broker->name : '';
						}
					}
					$buyer_name = '';
					$buyer_id = '';
					if($post->user_type == "buyer"){
						$buyer_data = Buyers::with('broker.broker')->where('id',$post->seller_buyer_id)->first();
						if(!empty($buyer_data)){
							$buyer_name = $buyer_data->name;
							$buyer_id = $buyer_data->id;
                            $broker_id = isset($buyer_data->broker->broker->id) ? $buyer_data->broker->broker->id : '';
							$broker_name = isset($buyer_data->broker->broker->name) ? $buyer_data->broker->broker->name : '';
						}
					}
					$attribute = PostDetails::where('post_id',$post->id)->get();
	                foreach ($attribute as $val) {
	                    $attribute_array[] = [
	                        'id' => $val->id,
	                        'post_id' => $val->post_id,
	                        'attribute' => $val->attribute,
	                        'attribute_value' => $val->attribute_value,
	                    ];
	                }
					$negotiation_array = [
		                'negotiation_id' => '',
		                'seller_id' => $seller_id,
		                'seller_name' => $seller_name,
		                'buyer_id' => $buyer_id,
		                'buyer_name' => $buyer_name,
		                'broker_id' => $broker_id,
		                'broker_name' => $broker_name,
		                'negotiation_by' => '',
		                'post_notification_id' => $post->id,
		                'negotiation_type' => 'post',
		                'current_price' => '',
		                'prev_price' => '',
		                'current_no_of_bales' => '',
		                'prev_no_of_bales' => '',
		                'transmit_condition' => '',
		                'payment_condition' => '',
		                'lab' => '',
		                'notes' => '',
		                'header' => '',
		                'header_name' => '',
		                'product_id' => $post->id,
		                'product_name' => $post->product->name,
		                'post_price' => (Int)$post->price,
		                'post_bales' => (Int)$post->no_of_bales,
		                'sold_bales' => (Int)$post->sold_bales,
		                'remain_bales' => (Int)$post->remain_bales,
		                'attribute_array' => $attribute_array,
					];
					$response['status'] = 200;
					$response['message'] = 'Negotiation Detail';
					$response['data'] = $negotiation_array;
				}
			}else{
				$notification =Notification::with('product')->where('id',$post_notification_id)->first();
				if(!empty($notification)){
					$seller_name = '';
					$seller_id = '';
					$broker_id = '';
					$broker_name = '';
					if($notification->user_type == "seller"){
						$seller_data = Sellers::with('broker.broker')->where('id',$notification->seller_buyer_id)->first();
						if(!empty($seller_data)){
							$seller_name = $seller_data->name;
							$seller_id = $seller_data->id;
							$broker_id = isset($seller_data->broker->broker->id) ? $seller_data->broker->broker->id : '';
							$broker_name = isset($seller_data->broker->broker->name) ? $seller_data->broker->broker->name : '';
						}
					}
					$buyer_name = '';
					$buyer_id = '';
					if($notification->user_type == "buyer"){
						$buyer_data = Buyers::with('broker.broker')->where('id',$notification->seller_buyer_id)->first();
						if(!empty($buyer_data)){
							$buyer_name = $buyer_data->name;
							$buyer_id = $buyer_data->id;
                            $broker_id = isset($buyer_data->broker->broker->id) ? $buyer_data->broker->broker->id : '';
							$broker_name = isset($buyer_data->broker->broker->name) ? $buyer_data->broker->broker->name : '';
						}
					}
					$attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
	                foreach ($attribute as $val) {
	                    $attribute_array[] = [
	                        'id' => $val->id,
	                        'notification_id' => $val->notification_id,
	                        'attribute' => $val->attribute,
	                        'attribute_value' => $val->attribute_value,
	                    ];
	                }
					$negotiation_array = [
		                'negotiation_id' => '',
		                'seller_id' => $seller_id,
		                'seller_name' => $seller_name,
		                'buyer_id' => $buyer_id,
		                'buyer_name' => $buyer_name,
		                'broker_id' => $broker_id,
		                'broker_name' => $broker_name,
		                'negotiation_by' => '',
		                'post_notification_id' => $notification->id,
		                'negotiation_type' => 'notification',
		                'current_price' => '',
		                'prev_price' => '',
		                'current_no_of_bales' => '',
		                'prev_no_of_bales' => '',
		                'transmit_condition' => '',
		                'payment_condition' => '',
		                'lab' => '',
		                'notes' => '',
		                'header' => '',
		                'header_name' => '',
		                'product_id' => $notification->id,
		                'product_name' => $notification->product->name,
		                'post_price' => (Int)$notification->price,
		                'post_bales' => (Int)$notification->no_of_bales,
						'sold_bales' => 0,
						'remain_bales' => 0,
		                'attribute_array' => $attribute_array,
					];
					$response['status'] = 200;
					$response['message'] = 'Negotiation Detail';
					$response['data'] = $negotiation_array;
				}
			}
		}
		return response($response, 200);
	}

    public function lab_report_status(Request $request){
        $data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$lab_report_status = isset($content->lab_report_status) ? $content->lab_report_status : '';

        $params = [
			'deal_id' => $deal_id,
			'lab_report_status' => $lab_report_status,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            'lab_report_status' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }

        $negotiation_comp = NegotiationComplete::find($deal_id);

        //when status fail_with_renegotiation no_of_bales add
        if($lab_report_status == "fail_with_renegotiation"){
            if ($negotiation_comp->lab_report_status == "fail_with_renegotiation") {
                $response['data'] = (object)[];
                $response['status'] = 404;
                $response['message'] = 'Lab report status already updated with fail negotiation';
                return response($response, 200);
            }

            $negotiation_comp = NegotiationComplete::find($deal_id);

            $negotiation_comp->lab_report_status = $lab_report_status;
            $negotiation_comp->save();

        	if($negotiation_comp->negotiation_type == "post"){
	        	$post_add = Post::where('id',$negotiation_comp->post_notification_id)->first();
	        	if(!empty($post_add)){
	        		$post_add->remain_bales = $post_add->remain_bales + $negotiation_comp->no_of_bales;
	        		$post_add->sold_bales = $post_add->sold_bales - $negotiation_comp->no_of_bales;
	        		$post_add->status = 'active';
	        		$post_add->save();
	        	}
	        }
	        if($negotiation_comp->negotiation_type == "notification"){
                $notification_add = Notification::where('id',$negotiation_comp->post_notification_id)->first();
	        	if(!empty($notification_add)){
                    $notification_add->remain_bales = $notification_add->remain_bales + $negotiation_comp->no_of_bales;
	        		$notification_add->sold_bales = $notification_add->sold_bales - $negotiation_comp->no_of_bales;
                    $notification_add->status = 'active';
	        		$notification_add->save();
	        	}
	        }

            $user_data = Sellers::where('id',$negotiation_comp->seller_id)->first();
            $user_data->wallet_amount = $user_data->wallet_amount + $negotiation_comp->seller_amount;
            $user_data->save();

            $transactions = new Transactions();
            $transactions->user_id = $negotiation_comp->seller_id;
            $transactions->user_type = 'seller';
            $transactions->deal_id = $deal_id;
            $transactions->type = 'deposite';
            $transactions->amount = $negotiation_comp->seller_amount;
            $transactions->message = 'Deal cancelled amount';
            $transactions->save();

            $buyer_data = Buyers::where('id',$negotiation_comp->buyer_id)->first();
            $buyer_data->wallet_amount = $buyer_data->wallet_amount + $negotiation_comp->buyer_amount;
            $buyer_data->save();

            $transactions = new Transactions();
            $transactions->user_id = $negotiation_comp->buyer_id;
            $transactions->user_type = 'buyer';
            $transactions->deal_id = $deal_id;
            $transactions->type = 'deposite';
            $transactions->amount = $negotiation_comp->buyer_amount;
            $transactions->message = 'Deal cancelled amount';
            $transactions->save();

            $admin_data = User::find(1);
            $admin_data->wallet_amount = $admin_data->wallet_amount - $negotiation_comp->buyer_amount * 2;
            $admin_data->save();

            $transactions = new Transactions();
            $transactions->user_id = 1;
            $transactions->user_type = 'admin';
            $transactions->deal_id = $deal_id;
            $transactions->type = 'withdraw';
            $transactions->amount = $negotiation_comp->buyer_amount * 2;
            $transactions->message = 'Deal cancelled withdraw amount';
            $transactions->save();

            if ($negotiation_comp->broker_amount > 0) {

                $broker_data = Brokers::where('id',$negotiation_comp->broker_id)->first();
                $broker_data->wallet_amount = $broker_data->wallet_amount - $negotiation_comp->broker_amount;
                $broker_data->save();

                $transactions = new Transactions();
                $transactions->user_id = 1;
                $transactions->user_type = 'broker';
                $transactions->deal_id = $deal_id;
                $transactions->type = 'withdraw';
                $transactions->amount = $negotiation_comp->broker_amount * 2;
                $transactions->message = 'Deal cancelled withdraw amount';
                $transactions->save();
            }
        } else {
            $negotiation_comp->lab_report_status = $lab_report_status;
            $negotiation_comp->save();
        }
        //when status fail_with_renegotiation no_of_bales add


        $data = [];
        $data = [
            'seller_id' => $negotiation_comp->seller_id,
            'buyer_id' => $negotiation_comp->buyer_id,
            'post_notification_id' => $negotiation_comp->post_notification_id,
        ];

        $response['status'] = 200;
		$response['message'] = 'Status Updated Successfully';
		$response['data'] = (object)$data;
		return response($response, 200);
    }

    public function search_to_sell_new_v2(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$required = isset($content->required) ? $content->required : '';
		// $non_required = isset($content->non_required) ? $content->non_required : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];

        // dd($required);

        $cnt_required = count($required);
        if (!empty($required)) {
            $temp = 0;
            foreach ($required as $val) {

            	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e])->where('tbl_post_details.attribute',$val->attribute);

                 $attr = explode(',',$val->attribute_value);

                if(count($attr) > 1){
                    $query->whereBetween('tbl_post_details.attribute_value', [$attr[0],$attr[1]]);
                }else{
                    $query->where('tbl_post_details.attribute_value', $val->attribute_value);
                }
                $query = $query->get();

            	 if(count($query)>0){
            	 	$temp++;
            	 }
            }
            if($cnt_required == $temp){
            	 foreach ($query as $value) {
	                $post_arr[] = $value->id;
	                $country_arr[] = $value->user_detail->country->id;
	                $state_arr[] = $value->user_detail->state->id;
	                $city_arr[] = $value->user_detail->city->id;
	                $station_arr[] = $value->user_detail->station->id;
	            }
            }
        }

        // if(empty($required)){
        // 	foreach ($non_required as $key => $val) {
        // 		$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0])->where('tbl_post_details.attribute',$val->attribute)->where('tbl_post_details.attribute_value', $val->attribute_value)->get();

	    //     	if(count($query)>0){
	    //         	 foreach ($query as $value) {
		//                 $post_arr[] = $value->id;
		//                 $country_arr[] = $value->user_detail->country->id;
		//                 $state_arr[] = $value->user_detail->state->id;
		//                 $city_arr[] = $value->user_detail->city->id;
		//                 $station_arr[] = $value->user_detail->station->id;
		//             }
	    //         }
        // 	}
        // }
        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);
			$search_array = [];
            foreach($country_arr as $country_val) {
                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.buyer')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'buyer','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {

                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->buyer->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to sell';
        $response['data'] = $final_arr;

		return response($response, 200);
    }

    public function search_to_buy_new_v2(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$product_id = isset($content->product_id) ? $content->product_id : '';
		$no_of_bales = isset($content->no_of_bales) ? $content->no_of_bales : '';
		$required = isset($content->required) ? $content->required : '';
		// $non_required = isset($content->non_required) ? $content->non_required : '';

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];

		$cnt_required = count($required);
        if (!empty($required)) {
            $temp = 0;
            foreach ($required as $val) {
            	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute]);

                $attr = explode(',',$val->attribute_value);

                if(count($attr) > 1){
                    $query->whereBetween('tbl_post_details.attribute_value', [$attr[0],$attr[1]]);
                }else{
                    $query->where('tbl_post_details.attribute_value', $val->attribute_value);
                }
                $query = $query->get();

            	 if(count($query)>0){
            	 	$temp++;
            	 }
            }
            if($cnt_required == $temp){
            	 foreach ($query as $value) {
	                $post_arr[] = $value->id;
	                $country_arr[] = $value->user_detail->country->id;
	                $state_arr[] = $value->user_detail->state->id;
	                $city_arr[] = $value->user_detail->city->id;
	                $station_arr[] = $value->user_detail->station->id;
	            }
            }
        }

        $final_arr = [];
        $state_arr_count = [];
        $city_arr_count = [];
        $station_arr_count = [];
        if (count($country_arr) > 0) {
            $country_arr = array_unique($country_arr);
            $state_arr = array_unique($state_arr);
            $city_arr = array_unique($city_arr);
            $station_arr = array_unique($station_arr);

			$search_array = [];
            foreach($country_arr as $country_val) {

                $country_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                    $query->where('id', $country_val);
                                })
                                ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                ->whereIn('tbl_post.id', $post_arr)
                                ->groupBy('tbl_post.id')
                                ->get();

                if (!empty($country_result) && count($country_result) > 0) {
                    $state_arr_count = [];
                    foreach($state_arr as $state_val) {
                        $state_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                        $query->where('id', $country_val);
                                    })
                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                        $query->where('id', $state_val);
                                    })
                                    ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                    ->whereIn('tbl_post.id', $post_arr)
                                    ->groupBy('tbl_post.id')
                                    ->get();

                        if (!empty($state_result) && count($state_result) > 0) {
                            $city_arr_count = [];
                            foreach($city_arr as $city_val) {
                                $city_result = Post::whereHas('user_detail.country', function($query)  use ($country_val) {
                                                $query->where('id', $country_val);
                                            })
                                            ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                $query->where('id', $state_val);
                                            })
                                            ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                $query->where('id', $city_val);
                                            })
                                            ->select('tbl_post.id','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                            ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                            ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                            ->whereIn('tbl_post.id', $post_arr)
                                            ->groupBy('tbl_post.id')
                                            ->get();

                                if (!empty($city_result) && count($city_result)> 0) {
                                    $station_arr_count = [];
                                    foreach($station_arr as $station_val) {

                                        $station_result = Post::with('user_detail.seller')
                                                    ->whereHas('user_detail.country', function($query)  use ($country_val) {
                                                        $query->where('id', $country_val);
                                                    })
                                                    ->whereHas('user_detail.state', function($query)  use ($state_val) {
                                                        $query->where('id', $state_val);
                                                    })
                                                    ->whereHas('user_detail.city', function($query)  use ($city_val) {
                                                        $query->where('id', $city_val);
                                                    })
                                                    ->whereHas('user_detail.station', function($query)  use ($station_val) {
                                                        $query->where('id', $station_val);
                                                    })
                                                    ->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')
                                                    ->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')
                                                    ->where(['tbl_post.user_type'=>'seller','tbl_post.is_active'=>0,'tbl_post.status'=>'active'])
                                                    ->whereIn('tbl_post.id', $post_arr)
                                                    ->groupBy('tbl_post.id')
                                                    ->orderBy('tbl_post.id','DESC')
                                                    ->get();

                                        if (!empty($station_result) && count($station_result)> 0) {

                                            $search_array = [];
                                            foreach($station_result as $station_result_val) {
                                                $search_array[] = [
                                                    'post_id' => $station_result_val->id,
                                                    'name' => $station_result_val->user_detail->seller->name,
                                                    'status' => $station_result_val->status,
                                                    'seller_buyer_id' => $station_result_val->seller_buyer_id,
                                                    'user_type' => $station_result_val->user_type,
                                                    'product_id' => $station_result_val->product_id,
                                                    'no_of_bales' => $station_result_val->no_of_bales,
                                                    'price' => $station_result_val->price,
                                                    'address' => $station_result_val->address,
                                                    'd_e' => $station_result_val->d_e,
                                                    'buy_for' => $station_result_val->buy_for,
                                                    'spinning_meal_name' => $station_result_val->spinning_meal_name,
                                                ];
                                            }

                                            $station_arr_count[] = [
                                                'name' => $station_result[0]->user_detail->station->name,
                                                'count' => count($station_result),
                                                'data' => $search_array
                                            ];
                                        }

                                    }
                                    $city_arr_count[] = [
                                        'name' => $city_result[0]->user_detail->city->name,
                                        'count' => count($city_result),
                                        'station' => $station_arr_count
                                    ];
                                }

                            }
                            $state_arr_count[] = [
                                'name' => $state_result[0]->user_detail->state->name,
                                'count' => count($state_result),
                                'city' => $city_arr_count
                            ];
                        }
                    }
                    $final_arr[] = [
                        'name' => $country_result[0]->user_detail->country->name,
                        'count' => count($country_result),
                        'state' => $state_arr_count
                    ];
                }
            }
        }
        $response['status'] = 200;
        $response['message'] = 'Search to buy';
        $response['data'] = $final_arr;

		return response($response, 200);

	}

    public function completed_deal_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$product_id = isset($content->product_id) ? $content->product_id : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

		$params = [
			'seller_id' => $seller_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $negotiation_array = [];
        $negotiation = $negotiation = NegotiationComplete::with('buyer')->where(['seller_id'=>$seller_id,'status' => 'complete','lab_report_status'=>'pass']);

        // if(!empty($product_id)){
        //     $negotiation = $negotiation->oWhereHas('post.product', function($query) use ($product_id) {
        //         $query->where('product_id', $product_id);
        //     })->where(['seller_id'=>$seller_id,'status' => 'complete','lab_report_status'=>'pass','negotiation_type'=>'post']);


        //     $negotiation = $negotiation->WhereHas('notification.product', function($query) use ($product_id) {
        //         $query->where('product_id', $product_id);
        //     })->where(['seller_id'=>$seller_id,'status' => 'complete','lab_report_status'=>'pass','negotiation_type'=>'notification']);
        // }
        $negotiation = $negotiation->skip($offset)->take($limit)->get();

        if(count($negotiation) > 0 && !empty($negotiation)){
            foreach($negotiation as $value){
                $buyer_name = !empty($value->buyer) ? $value->buyer->name :'';

                if($value->negotiation_type == 'post'){
                    $post = Post::with('product')->where('id',$value->post_notification_id)->first();
                    $product_id = $post->product_id;
                    $product_name = $post->product->name;
                }
                if($value->negotiation_type == 'notification'){
                    $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();
                    $product_id = $notification->product_id;
                    $product_name = $notification->product->name;
                }

                $negotiation_array[] = [
                    'deal_id' => $value->id,
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'seller_id' => $value->seller_id,
                    'buyer_id' => $value->buyer_id,
                    'buyer_name' => $buyer_name,
                    'post_notification_id' => $value->post_notification_id,
                    'negotiation_type'=> $value->negotiation_type,
                    'price'=>$value->price,
                    'no_of_bales'=>$value->no_of_bales,
                    'lab_report_status'=>$value->lab_report_status,
                ];
            }
        }

        $response['status'] = 200;
        $response['message'] = 'completed deal';
        $response['data'] = $negotiation_array;
        return response($response, 200);
	}

	public function completed_deal_buyer_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

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

        $negotiation_array = [];
        $negotiation = NegotiationComplete::with('seller')->where(['buyer_id'=>$buyer_id,'status' => 'complete','lab_report_status'=>'pass'])->skip($offset)->take($limit)->get();
        if(count($negotiation) > 0 && !empty($negotiation)){
            foreach($negotiation as $value){
                $seller_name = !empty($value->seller) ? $value->seller->name :'';

                if($value->negotiation_type == 'post'){
                    $post = Post::with('product')->where('id',$value->post_notification_id)->first();
                    $product_id = $post->product_id;
                    $product_name = $post->product->name;
                }
                if($value->negotiation_type == 'notification'){
                    $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();
                    $product_id = $notification->product_id;
                    $product_name = $notification->product->name;
                }

                $negotiation_array[] = [
                    'deal_id' => $value->id,
                    'product_id' => $product_id,
                    'product_name' => $product_name,
                    'buyer_id' => $value->buyer_id,
                    'seller_id' => $value->seller_id,
                    'seller_name' => $seller_name,
                    'post_notification_id' => $value->post_notification_id,
                    'negotiation_type'=> $value->negotiation_type,
                    'price'=>$value->price,
                    'no_of_bales'=>$value->no_of_bales,
                    'lab_report_status'=>$value->lab_report_status,
                ];
            }
        }

		$response['status'] = 200;
		$response['message'] = 'completed deal buyer';
		$response['data'] = $negotiation_array;

		return response($response, 200);
	}

    public function completed_deal_detail_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';

		$params = [
			'deal_id' => $deal_id,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $negotiation_array = [];
        $negotiation = NegotiationComplete::with('seller','buyer')->where(['id'=>$deal_id])->first();
        if(!empty($negotiation)){
            $seller_name = !empty($negotiation->seller) ? $negotiation->seller->name :'';
            $buyer_name = !empty($negotiation->buyer) ? $negotiation->buyer->name :'';

            if($negotiation->negotiation_type == 'post'){
                $post = Post::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $post->product_id;
                $product_name = $post->product->name;
            }
            if($negotiation->negotiation_type == 'notification'){
                $notification = Notification::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $notification->product_id;
                $product_name = $notification->product->name;
            }

            $negotiation_array = [
                'product_id' => $product_id,
                'product_name' => $product_name,
                'seller_id' => $negotiation->seller_id,
                'seller_name' => $seller_name,
                'buyer_id' => $negotiation->buyer_id,
                'buyer_name' => $buyer_name,
                'post_notification_id' => $negotiation->post_notification_id,
                'negotiation_type'=> $negotiation->negotiation_type,
                'price'=>$negotiation->price,
                'no_of_bales'=>$negotiation->no_of_bales,
                'lab_report_status'=>$negotiation->lab_report_status,
                'status'=>$negotiation->status,
            ];
        }

        $response['status'] = 200;
        $response['message'] = 'completed deal';
        $response['data'] = $negotiation_array;
        return response($response, 200);
	}

    public function negotiation_detail_by_deal_new_v2(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_id = isset($content->seller_id) ? $content->seller_id : '';
		$buyer_id = isset($content->buyer_id) ? $content->buyer_id : '';
		$post_notification_id = isset($content->post_notification_id) ? $content->post_notification_id : '';
		$deal_id = isset($content->deal_id) ? $content->deal_id : '';

		$params = [
			'seller_id' => $seller_id,
			'buyer_id' => $buyer_id,
			'post_notification_id' => $post_notification_id,
			'deal_id' => $deal_id,
		];

		$validator = Validator::make($params, [
            'seller_id' => 'required|exists:tbl_sellers,id',
            'buyer_id' => 'required|exists:tbl_buyers,id',
            'post_notification_id' => 'required',
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
            $response['message'] =$validator->errors()->first();
            return response($response, 200);
	    }

		$negotiation_array = [];
		$negotiation = NegotiationComplete::with('seller','buyer','broker','transmit_conditions','payment_conditions','labs')->where(['id'=>$deal_id,'seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id])->first();

		if(!empty($negotiation)) {
            $seller_name = !empty($negotiation->seller->name) ? $negotiation->seller->name :'';
            $buyer_name = !empty($negotiation->buyer->name) ? $negotiation->buyer->name :'';
            $broker_name = !empty($negotiation->broker->name) ? $negotiation->broker->name :'';

            $transmit_condition_name = !empty($negotiation->transmit_conditions->name) ? $negotiation->transmit_conditions->name :'';

            $payment_condition_name = !empty($negotiation->payment_conditions->name) ? $negotiation->payment_conditions->name :'';

            $lab_name = !empty($negotiation->labs->name) ? $negotiation->labs->name :'';

            $attribute_array = [];
            $post_price = "";
            $post_bales = "";

            if($negotiation->negotiation_type == 'post'){
                $post = Post::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $post->product_id;
                $product_name = $post->product->name;
                $post_price = $post->price;
                $post_bales = $post->no_of_bales;

                $attribute = PostDetails::where('post_id',$post->id)->get();
                foreach ($attribute as $val) {
                    $attribute_array[] = [
                        'id' => $val->id,
                        'post_id' => $val->post_id,
                        'attribute' => $val->attribute,
                        'attribute_value' => $val->attribute_value,
                    ];
                }
            }
            if($negotiation->negotiation_type == 'notification'){
                $notification = Notification::with('product')->where('id',$negotiation->post_notification_id)->first();
                $product_id = $notification->product_id;
                $product_name = $notification->product->name;
                $post_price = $notification->price;
                $post_bales = $notification->no_of_bales;

                $attribute = NotificatonDetails::where('notification_id',$notification->id)->get();
                foreach ($attribute as $val) {
                    $attribute_array[] = [
                        'id' => $val->id,
                        'notification_id' => $val->notification_id,
                        'attribute' => $val->attribute,
                        'attribute_value' => $val->attribute_value,
                    ];
                }

            }
            if($negotiation->header == 1){
                $header_name = "Subject To";
            }else{
                $header_name = "Confirm To";
            }

			$negotiation_array = [
                'negotiation_id' => $negotiation->id,
                'seller_id' => $negotiation->seller_id,
                'seller_name' => $seller_name,
                'buyer_id' => $negotiation->buyer_id,
                'buyer_name' => $buyer_name,
                'broker_id' => $negotiation->broker_id,
                'broker_name' => $broker_name,
                'negotiation_by' => $negotiation->negotiation_by,
                'post_notification_id' => $negotiation->post_notification_id,
                'negotiation_type' => $negotiation->negotiation_type,
                'current_price' => $negotiation->price,
                'prev_price' => $negotiation->prev_price,
                'current_no_of_bales' => $negotiation->no_of_bales,
                'prev_no_of_bales' => $negotiation->prev_bales,
                'transmit_condition' => $transmit_condition_name,
                'payment_condition' => $payment_condition_name,
                'lab' => $lab_name,
                'notes' => $negotiation->notes,
                'header' => $negotiation->header,
                'header_name' => $header_name,
                'product_id' => $product_id,
                'product_name' => $product_name,
                'post_price' => $post_price,
                'post_bales' => $post_bales,
                'attribute_array' => $attribute_array,
			];
			$response['status'] = 200;
			$response['message'] = 'Negotiation Detail';
			$response['data'] = $negotiation_array;
		}
		return response($response, 200);
	}

    private function check_queue($buyer_id, $seller_id, $post_notification_id, $type) {
        $check = DealQueue::where(['post_notification_id' => $post_notification_id, 'post_type'=> $type])->orderBy('id', 'DESC')->first();
        if (!empty($check) ) {
            if ($check->status == 1) {
                $deal_queue = new DealQueue();
                $deal_queue->post_notification_id =  $post_notification_id;
                $deal_queue->post_type = $type;
                $deal_queue->seller_id = $seller_id;
                $deal_queue->buyer_id = $buyer_id;
                $deal_queue->status = 0;
                $deal_queue->save();
                sleep(3);
                $this->check_queue($buyer_id, $seller_id, $post_notification_id, $type);
            } else {
                if ($check->seller_id == $seller_id && $check->buyer_id == $buyer_id) {
                    return 2;
                } else {
                    sleep(3);
                    $this->check_queue($buyer_id, $seller_id, $post_notification_id, $type);
                }
            }
        } else {
            $deal_queue = new DealQueue();
            $deal_queue->post_notification_id =  $post_notification_id;
            $deal_queue->post_type = $type;
            $deal_queue->seller_id = $seller_id;
            $deal_queue->buyer_id = $buyer_id;
            $deal_queue->status = 1;
            $deal_queue->save();

            return 1;
        }
    }

    public function upload_debit_note(Request $request)
    {
		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$upload_by = isset($content->upload_by) ? $content->upload_by : '';

		$params = [
			'deal_id' => $deal_id,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }

        if($request->hasFile('file')) {
            foreach($request->file('file') as $key => $image)
            {
                $destinationPath = storage_path('app/public/content_images/');
                $filename = time().$key.'.'.$image->getClientOriginalExtension();
                $image->move($destinationPath, $filename);

                $debit_note = new NegotiationDebitNote();
                $debit_note->negotiation_complete_id = $deal_id;
                $debit_note->upload_by = $upload_by;
                $debit_note->file_name = $filename;
                $debit_note->save();

            }
        }

		$response['status'] = 200;
		$response['message'] = 'Save Successfully';
		$response['data'] = (object)[];
		return response($response, 200);
	}

    public function update_transaction_sample(Request $request)
    {
		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$sample = isset($content->sample) ? $content->sample : '';


		$params = [
			'deal_id' => $deal_id,
			'sample' => $sample,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            'sample' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }
    	$negotiation_comp = NegotiationComplete::find($deal_id);


        $negotiation_comp->is_sample = $sample;
        $negotiation_comp->save();

		$response['status'] = 200;
		$response['message'] = 'Updated Successfully';
		$response['data'] = (object)[];
		return response($response, 200);
	}

    public function my_contract_list_v1(Request $request)
	{
		$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$seller_buyer_id = isset($content->seller_buyer_id) ? $content->seller_buyer_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
        $offset = isset($content->offset) ? $content->offset : 0;
		$limit = isset($content->limit) ? $content->limit : 10;

		$final_arr = [];
		$dates = [];
		if($user_type == "seller"){
		    $make_deals = NegotiationComplete::where(['seller_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));
				}
			}
			$unique_date = array_unique($dates);
			foreach ($unique_date as $date) {

				$negotiation_data = NegotiationComplete::with('seller', 'buyer', 'deal_pdf')->whereDate('updated_at',$date)->where('seller_id',$seller_buyer_id)->orderBy('id','DESC')->get();

                foreach ($negotiation_data as $value) {

                    $url = '';
                    if (!empty($value->deal_pdf->filename)) {
                        $filename = storage_path('app/public/pdf/' . $value->deal_pdf->filename);
                        if (File::exists($filename)) {
                            $url = asset('storage/app/public/pdf/' . $value->deal_pdf->filename);
                        }
                    }

                    $dates = date('d-m-Y', strtotime($value->updated_at));

					if($value->negotiation_type=="post"){
                        $post = Post::with('product')->where('id',$value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($post->product->name) ? $post->product->name : '',
							'url'=>$url
						];
					}

					if($value->negotiation_type=="notification"){
                        $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($notification->product->name) ? $notification->product->name : '',
							'url'                  => $url,
							'post_notification_id' => $value->post_notification_id,
						];
					}
				}
			}
		}
		if($user_type == "buyer"){
		    $make_deals = NegotiationComplete::where(['buyer_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));
				}
			}
			$unique_date = array_unique($dates);

			foreach ($unique_date as $date) {

				$negotiation_data = NegotiationComplete::with('seller', 'buyer', 'deal_pdf')->whereDate('updated_at',$date)->where('buyer_id',$seller_buyer_id)->orderBy('id','DESC')->get();

				foreach ($negotiation_data as $value) {

                    $dates = date('d-m-Y', strtotime($value->updated_at));

                    $url = '';
                    if (!empty($value->deal_pdf->filename)) {
                        $filename = storage_path('app/public/pdf/' . $value->deal_pdf->filename);
                        if (File::exists($filename)) {
                            $url = asset('storage/app/public/pdf/' . $value->deal_pdf->filename);
                        }
                    }

					if ($value->negotiation_type=="post") {
                        $post = Post::with('product')->where('id', $value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($post->product->name) ? $post->product->name : '',
							'url'=>$url
						];
					}
					if($value->negotiation_type=="notification"){
                        $notification = Notification::with('product')->where('id',$value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($notification->product->name) ? $notification->product->name : '',
							'url'                  => $url,
							'post_notification_id' => $value->post_notification_id,
						];
					}
				}
			}
		}
        if($user_type == "broker"){
		    $make_deals = NegotiationComplete::where(['broker_id'=>$seller_buyer_id])->get();
			if(count($make_deals)>0){
				foreach ($make_deals as $make_deal) {
					array_push($dates,date('Y-m-d', strtotime($make_deal->updated_at)));
				}
			}
			$unique_date = array_unique($dates);
			foreach ($unique_date as $date) {

				$negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('broker_id',$seller_buyer_id)->orderBy('id','DESC')->get();
				foreach ($negotiation_data as $value) {
					$dates = date('d-m-Y', strtotime($value->updated_at));

                    $url = '';
                    if (!empty($value->deal_pdf->filename)) {
                        $filename = storage_path('app/public/pdf/' . $value->deal_pdf->filename);
                        if (File::exists($filename)) {
                            $url = asset('storage/app/public/pdf/' . $value->deal_pdf->filename);
                        }
                    }


					if($value->negotiation_type=="post"){
						$post = Post::with('product')->where('id', $value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'post_notification_id' => $value->post_notification_id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($post->product->name) ? $post->product->name : '',
							'url'=>$url
						];
					}
					if($value->negotiation_type=="notification"){
						$notification = Notification::with('product')->where('id',$value->post_notification_id)->first();

						$final_arr[$dates][]  = [
							'deal_id'              => $value->id,
							'buyer_id'             => $value->buyer_id,
							'buyer_name'           => isset($value->buyer->name) ? $value->buyer->name : '',
							'seller_id'            => $value->seller_id,
							'seller_name'          => isset($value->seller->name) ? $value->seller->name : '',
							'sell_bales'           => $value->no_of_bales,
							'sell_price'           => $value->price,
							'product_name'         => isset($notification->product->name) ? $notification->product->name : '',
							'url'                  => $url,
							'post_notification_id' => $value->post_notification_id,
						];
					}
				}
			}
		}
		$test_arr = [];
		foreach ($final_arr as $key => $value) {
			$test_arr[] = [
				'deal_date' => $key,
				'deal_details' => $value
			];
		}
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = $test_arr;
		return response($response, 200);
	}

    public function contract_details(Request $request)
	{
		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';

        $params = [
			'deal_id' => $deal_id,
		];

		$validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
			$response['message'] =$validator->errors()->first();
			return response($response, 200);
	    }

        $negotiation_data = NegotiationComplete::with('debit_note', 'seller', 'buyer', 'deal_pdf')->where('id',$deal_id)->first();


        $negotiation_debit_file = [];
		if(!empty($negotiation_data->debit_note)){
            foreach($negotiation_data->debit_note as $val){
                $_file = storage_path('app/public/content_images/' . $val->file_name);
                if (File::exists($_file) && !empty($val->file_name)) {
                    $negotiation_debit_file [] = [
                        'file_name' => asset('storage/app/public/content_images/' . $val->file_name),
                    ];
                }
            }
        }

        $negotiation_array = [];

        if($negotiation_data->negotiation_type=="post"){
            $post = Post::with('product', 'post_details')->where(['id' => $negotiation_data->post_notification_id])->first();

            $product_name = '';
            if(!empty($post->product->name)){
                $product_name = $post->product->name;
            }
            // dd($post->post_details);
            $attribute_array = [];
            foreach ($post->post_details as $attr) {
                $attribute_array[] = [
                    'id' => $attr->id,
                    'attribute' => $attr->attribute,
                    'attribute_negotiation_data' => $attr->attribute_negotiation_data,
                ];
            }

            $post_price = '';
            if(!empty($post->price)){
                $post_price = $post->price;
            }
            $post_no_of_bales = '';
            if(!empty($post->no_of_bales)){
                $post_no_of_bales = $post->no_of_bales;
            }
            $post_date ='';
            if(!empty($post->created_at)){
                $post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
            }

            $seller_name = $negotiation_data->seller->name;
            $buyer_name = $negotiation_data->buyer->name;

            $transmit_condition_name = '';
            $is_dispatch = 0;
            $transmit_condition = TransmitCondition::where('id',$negotiation_data->transmit_condition)->first();
            if(!empty($transmit_condition)){
                $transmit_condition_name = $transmit_condition->name;
                $is_dispatch = $transmit_condition->is_dispatch;
            }
            $payment_condition_name = '';
            $payment_condition = PaymentCondition::where('id',$negotiation_data->payment_condition)->first();
            if(!empty($payment_condition)){
                $payment_condition_name = $payment_condition->name;
            }
            $lab_name = '';
            $lab = Lab::where('id',$negotiation_data->lab)->first();
            if(!empty($lab)){
                $lab_name = $lab->name;
            }

            $url = '';
            if (!empty($negotiation_data->deal_pdf->filename)) {
                $filename = storage_path('app/public/pdf/' . $negotiation_data->deal_pdf->filename);
                if (File::exists($filename)) {
                    $url = asset('storage/app/public/pdf/' . $negotiation_data->deal_pdf->filename);
                }
            }

            $lab_report = '';
            $lab_report_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->lab_report);
            if (File::exists($lab_report_file) && !empty($negotiation_data->lab_report)) {
                $lab_report = asset('storage/app/public/transaction_tracking/' . $negotiation_data->lab_report);
            }

            $transmit_deal = '';
            $transmit_deal_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->transmit_deal);
            if (File::exists($transmit_deal_file) && !empty($negotiation_data->transmit_deal)) {
                $transmit_deal = asset('storage/app/public/transaction_tracking/' . $negotiation_data->transmit_deal);
            }

            $without_gst = '';
            $without_gst_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->without_gst);
            if (File::exists($without_gst_file) && !empty($negotiation_data->without_gst)) {
                $without_gst = asset('storage/app/public/transaction_tracking/' . $negotiation_data->without_gst);
            }

            $gst_reciept = '';
            $gst_reciept_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->gst_reciept);
            if (File::exists($gst_reciept_file) && !empty($negotiation_data->gst_reciept)) {
                $gst_reciept = asset('storage/app/public/transaction_tracking/' . $negotiation_data->gst_reciept);
            }

            $negotiation_array  = [
                'deal_id' => $negotiation_data->id,
                'post_notification_id' => $negotiation_data->post_notification_id,
                'post_date' => $post_date,
                'buyer_id' => $negotiation_data->buyer_id,
                'buyer_name' =>$buyer_name,
                'seller_id' => $negotiation_data->seller_id,
                'seller_name' => $seller_name,
                'negotiation_by' => $negotiation_data->negotiation_by,
                'negotiation_type' => $negotiation_data->negotiation_type,
                'post_price' => $post_price,
                'post_bales' => $post_no_of_bales,
                'sell_bales' => $negotiation_data->no_of_bales,
                'sell_price' => $negotiation_data->price,
                'payment_condition' => $payment_condition_name,
                'transmit_condition' => $transmit_condition_name,
                'lab' => $lab_name,
                'lab_report' => $lab_report,
                'transmit_deal' => $transmit_deal,
                'without_gst' => $without_gst,
                'gst_reciept' => $gst_reciept,
                'lab_report_mime' => !empty($negotiation_data->lab_report_mime) ? $negotiation_data->lab_report_mime : '',
                'transmit_deal_mime' => !empty($negotiation_data->transmit_deal_mime) ? $negotiation_data->transmit_deal_mime : '',
                'without_gst_mime' => !empty($negotiation_data->without_gst_mime) ? $negotiation_data->without_gst_mime : '',
                'gst_reciept_mime' => !empty($negotiation_data->gst_reciept_mime) ? $negotiation_data->gst_reciept_mime : '',
                'product_name' => $product_name,
                'attribute_array' => $attribute_array,
                'url'=>$url,
                'lab_report_status' => $negotiation_data->lab_report_status,
                'debit_note_array' =>  $negotiation_debit_file,
                'is_dispatch' =>  $is_dispatch,
                'is_sample' =>  $negotiation_data->is_sample,
            ];
        }
        if($negotiation_data->negotiation_type=="notification"){
            $post = Notification::with('notification_details')->where('id',$negotiation_data->post_notification_id)->first();

            $product_name = '';
            if(!empty($post->product->name)){
                $product_name = $post->product->name;
            }

            $attribute_array = [];
            foreach ($post->notification_details as $val) {
                $attribute_array[] = [
                    'id' => $val->id,
                    'attribute' => $val->attribute,
                    'attribute_negotiation_data' => $val->attribute_negotiation_data,
                ];
            }

            $post_price = '';
            if(!empty($post->price)){
                $post_price = $post->price;
            }
            $post_no_of_bales = '';
            if(!empty($post->no_of_bales)){
                $post_no_of_bales = $post->no_of_bales;
            }
            $post_date ='';
            if(!empty($post->created_at)){
                $post_date = date('d-m-Y, h:i a', strtotime($post->created_at));
            }

            $seller_name = $negotiation_data->seller->name;
            $buyer_name = $negotiation_data->buyer->name;

            $transmit_condition_name = '';
            $is_dispatch = 0;
            $transmit_condition = TransmitCondition::where('id',$negotiation_data->transmit_condition)->first();
            if(!empty($transmit_condition)){
                $transmit_condition_name = $transmit_condition->name;
                $is_dispatch = $transmit_condition->is_dispatch;
            }
            $payment_condition_name = '';
            $payment_condition = PaymentCondition::where('id',$negotiation_data->payment_condition)->first();
            if(!empty($payment_condition)){
                $payment_condition_name = $payment_condition->name;
            }
            $lab_name = '';
            $lab = Lab::where('id',$negotiation_data->lab)->first();
            if(!empty($lab)){
                $lab_name = $lab->name;
            }

            $url = '';
            $url = DealPdf::where('deal_id',$negotiation_data->id)->first();
            if(!empty($url)){
                if (!empty($url->filename)) {

                    $filename = storage_path('app/public/pdf/' . $url->filename);

                    if (File::exists($filename)) {
                        $url = asset('storage/app/public/pdf/' . $url->filename);
                    }
                }
            }

            $lab_report = '';
            $lab_report_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->lab_report);
            if (File::exists($lab_report_file) && !empty($negotiation_data->lab_report)) {
                $lab_report = asset('storage/app/public/transaction_tracking/' . $negotiation_data->lab_report);
            }

            $transmit_deal = '';
            $transmit_deal_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->transmit_deal);
            if (File::exists($transmit_deal_file) && !empty($negotiation_data->transmit_deal)) {
                $transmit_deal = asset('storage/app/public/transaction_tracking/' . $negotiation_data->transmit_deal);
            }

            $without_gst = '';
            $without_gst_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->without_gst);
            if (File::exists($without_gst_file) && !empty($negotiation_data->without_gst)) {
                $without_gst = asset('storage/app/public/transaction_tracking/' . $negotiation_data->without_gst);
            }

            $gst_reciept = '';
            $gst_reciept_file = storage_path('app/public/transaction_tracking/' . $negotiation_data->gst_reciept);
            if (File::exists($gst_reciept_file) && !empty($negotiation_data->gst_reciept)) {
                $gst_reciept = asset('storage/app/public/transaction_tracking/' . $negotiation_data->gst_reciept);
            }

            $negotiation_array  = [
                'deal_id' => $negotiation_data->id,
                'post_notification_id' => $negotiation_data->post_notification_id,
                'post_date' => $post_date,
                'buyer_id' => $negotiation_data->buyer_id,
                'buyer_name' =>$buyer_name,
                'seller_id' => $negotiation_data->seller_id,
                'seller_name' => $seller_name,
                'negotiation_by' => $negotiation_data->negotiation_by,
                'negotiation_type' => $negotiation_data->negotiation_type,
                'post_price' => $post_price,
                'post_bales' => $post_no_of_bales,
                'sell_bales' => $negotiation_data->no_of_bales,
                'sell_price' => $negotiation_data->price,
                'payment_condition' => $payment_condition_name,
                'transmit_condition' => $transmit_condition_name,
                'lab' => $lab_name,
                'lab_report' => $lab_report,
                'transmit_deal' => $transmit_deal,
                'without_gst' => $without_gst,
                'gst_reciept' => $gst_reciept,
                'lab_report_mime' => !empty($negotiation_data->lab_report_mime) ? $negotiation_data->lab_report_mime : '',
                'transmit_deal_mime' => !empty($negotiation_data->transmit_deal_mime) ? $negotiation_data->transmit_deal_mime : '',
                'without_gst_mime' => !empty($negotiation_data->without_gst_mime) ? $negotiation_data->without_gst_mime : '',
                'gst_reciept_mime' => !empty($negotiation_data->gst_reciept_mime) ? $negotiation_data->gst_reciept_mime : '',
                'product_name' => $product_name,
                'attribute_array' => $attribute_array,
                'url'=>$url,
                'lab_report_status' => $negotiation_data->lab_report_status,
                'debit_note_array' =>  $negotiation_debit_file,
                'is_dispatch' =>  $is_dispatch,
                'is_sample' =>  $negotiation_data->is_sample,
            ];
        }

		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = $negotiation_array;
		return response($response, 200);
	}

    public function test_api(Request $request)
	{
		$by_data = array('otp'=>456987,'name' => "Test Name");

		Mail::send(['html'=>'mail'], $by_data, function($message) {
			$message->to('nileshbavliya@gmail.com', 'E - Cotton')->subject('Make deal done');
		});

		$response['data'] = (object)[];
		return response($response, 200);
	}

    public function make_deal_otp_verify(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
        $email_otp = isset($content->email_otp) ? $content->email_otp : '';
        $mobile_otp = isset($content->mobile_otp) ? $content->mobile_otp : '';

        $params = [
            'mobile_otp' => $mobile_otp,
            'email_otp' => $email_otp,
            'deal_id' => $deal_id,
            'user_type' => $user_type
        ];

        $validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            'user_type' => 'required',
            'email_otp' => 'required',
            'mobile_otp' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $complete_deal = NegotiationComplete::where('id',$deal_id)->first();

        $verify_mobile_otp = "";
        $verify_email_otp = "";

		if ($user_type == 'seller') {
			$verify_mobile_otp = $complete_deal->seller_otp;
			$verify_email_otp = $complete_deal->seller_email_otp;
		} else if ($user_type == 'buyer') {
			$verify_mobile_otp = $complete_deal->buyer_otp;
			$verify_email_otp = $complete_deal->buyer_email_otp;
		} else if ($user_type == 'broker') {
			$verify_mobile_otp = $complete_deal->broker_otp;
			$verify_email_otp = $complete_deal->broker_email_otp;
		}

		if(!empty($verify_mobile_otp)){
			if($mobile_otp == $verify_mobile_otp){
				$current = date("Y-m-d H:i:s");
				$otp_time = $complete_deal->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 180)) {

                    if($user_type == 'seller'){
						$complete_deal->is_seller_otp_verify = 1;
                        $complete_deal->save();
                    }else if($user_type == 'buyer'){
						$complete_deal->is_buyer_otp_verify = 1;
                        $complete_deal->save();
                    }else if($user_type == 'broker'){
						$complete_deal->is_broker_otp_verify = 1;
                        $complete_deal->save();
                    }

                    if($complete_deal->is_seller_otp_verify == 1 && $complete_deal->is_buyer_otp_verify == 1 && $complete_deal->is_broker_otp_verify == 1 && $complete_deal->is_seller_email_otp_verify == 1 && $complete_deal->is_buyer_email_otp_verify == 1 && $complete_deal->is_broker_email_otp_verify == 1){
                        $complete_deal->status = 'complete';
                        $complete_deal->save();
                    }

                    $response['status'] = 200;
					$response['message'] = 'Your OTP has been verified successfully';

				}else{
						$response['status'] = 200;
						$response['message'] = 'Mobile OTP expired';
					}
			}else{
					$response['status'] = 200;
					$response['message'] = 'Mobile OTP is not valid';
			}
		}else{
			$response['status'] = 200;
			$response['message'] = 'Mobile number not found';
		}

		if(!empty($verify_email_otp)){
			if($email_otp == $verify_email_otp){
				$current = date("Y-m-d H:i:s");
				$otp_time = $complete_deal->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 180)) {

                    if($user_type == 'seller'){
						$complete_deal->is_seller_email_otp_verify = 1;
                        $complete_deal->save();
                    }else if($user_type == 'buyer'){
						$complete_deal->is_buyer_email_otp_verify = 1;
                        $complete_deal->save();
                    }else if($user_type == 'broker'){
						$complete_deal->is_broker_email_otp_verify = 1;
                        $complete_deal->save();
                    }

                    if($complete_deal->is_seller_otp_verify == 1 && $complete_deal->is_buyer_otp_verify == 1 && $complete_deal->is_broker_otp_verify == 1 && $complete_deal->is_seller_email_otp_verify == 1 && $complete_deal->is_buyer_email_otp_verify == 1 && $complete_deal->is_broker_email_otp_verify == 1){
                        $complete_deal->status = 'complete';
                        $complete_deal->save();
                    }

                    $response['status'] = 200;
					$response['message'] = 'Your OTP has been verified successfully';

				}else{
						$response['status'] = 200;
						$response['message'] = 'Email OTP expired';
					}
			}else{
					$response['status'] = 200;
					$response['message'] = 'Email OTP is not valid';
			}
		}else{
			$response['status'] = 200;
			$response['message'] = 'Email not found';
		}

		return response($response, 200);
    }

	public function make_deal_otp_verify1(Request $request)
    {
    	$response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';
        $email_otp = isset($content->email_otp) ? $content->email_otp : '';
        $mobile_otp = isset($content->mobile_otp) ? $content->mobile_otp : '';

        // dd($mobile_otp);
        $otp = "";
        if(empty($email_otp) && empty($mobile_otp)){
            $response['status'] = 404;
            $response['message'] = "Email or Sms otp required";
            return response($response, 200);

        }

        $params = [
            'deal_id' => $deal_id,
            'user_type' => $user_type
        ];

        $validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $complete_deal = NegotiationComplete::where('id',$deal_id)->first();

        $verify_otp = "";

        if(!empty($mobile_otp)){
            $otp = $mobile_otp;
            if ($user_type == 'seller') {
                $verify_otp = $complete_deal->seller_otp;
            } else if ($user_type == 'buyer') {
                $verify_otp = $complete_deal->buyer_otp;
            } else if ($user_type == 'broker') {
                $verify_otp = $complete_deal->broker_otp;
            }
        }else{
            $otp = $email_otp;
            if ($user_type == 'seller') {
                $verify_otp = $complete_deal->seller_email_otp;
            } else if ($user_type == 'buyer') {
                $verify_otp = $complete_deal->buyer_email_otp;
            } else if ($user_type == 'broker') {
                $verify_otp = $complete_deal->broker_email_otp;
            }
        }

		if(!empty($verify_otp)){
			if($otp == $verify_otp){
				$current = date("Y-m-d H:i:s");
				$otp_time = $complete_deal->otp_time;
				$diff = strtotime($current) - strtotime($otp_time);
				$days    = floor($diff / 86400);
				$hours   = floor(($diff - ($days * 86400)) / 3600);
				$minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
				if (($diff > 0) && ($minutes <= 180)) {

                    if($user_type == 'seller'){
                        if (!empty($mobile_otp)) {
                            $complete_deal->is_seller_otp_verify = 1;
                        } else {
                            $complete_deal->is_seller_email_otp_verify = 1;
                        }
                        $complete_deal->save();
                    }else if($user_type == 'buyer'){
                        if (!empty($mobile_otp)) {
                            $complete_deal->is_buyer_otp_verify = 1;
                        } else {
                            $complete_deal->is_buyer_email_otp_verify = 1;
                        }
                        $complete_deal->save();
                    }else if($user_type == 'broker'){
                        if (!empty($mobile_otp)) {
                            $complete_deal->is_broker_otp_verify = 1;
                        } else {
                            $complete_deal->is_broker_email_otp_verify = 1;
                        }
                        $complete_deal->save();
                    }

                    if($complete_deal->is_seller_otp_verify == 1 && $complete_deal->is_buyer_otp_verify == 1 && $complete_deal->is_broker_otp_verify == 1 && $complete_deal->is_seller_email_otp_verify == 1 && $complete_deal->is_buyer_email_otp_verify == 1 && $complete_deal->is_broker_email_otp_verify == 1){
                        $complete_deal->status = 'complete';
                        $complete_deal->save();
                    }

                    $response['status'] = 200;
					$response['message'] = 'Your OTP has been verified successfully';

				}else{
						$response['status'] = 200;
						$response['message'] = 'OTP expired';
					}
			}else{
					$response['status'] = 200;
					$response['message'] = 'OTP is not valid';
			}
		}else{
			$response['status'] = 200;
			$response['message'] = 'Mobile number not found';
		}
		return response($response, 200);
    }

    public function resend_deal_otp(Request $request){
        $response = array();
		$response['status'] = 200;
		$response['message'] = '';
		$response['data'] = (object)array();

		$data = $request->input('data');
		$content = json_decode($data);

		$deal_id = isset($content->deal_id) ? $content->deal_id : '';
		$user_type = isset($content->user_type) ? $content->user_type : '';

        $params = [
            'deal_id' => $deal_id,
            'user_type' => $user_type
        ];

        $validator = Validator::make($params, [
            'deal_id' => 'required|exists:tbl_negotiation_complete,id',
            'user_type' => 'required',
        ]);

        if ($validator->fails()) {
	        $response['status'] = 404;
				$response['message'] =$validator->errors()->first();
				return response($response, 200);
	    }

        $complete_deal = NegotiationComplete::where('id',$deal_id)->first();

        if($user_type == "seller"){
            $sellers = Sellers::select('mobile_number','email','name')->where('id',$complete_deal->seller_id)->first();
            $s_message = "OTP to verify make deal is ". $complete_deal->seller_otp ." - E - Cotton";
            NotificationHelper::send_otp($sellers->mobile_number,$s_message);

            $s_data = array('otp'=>$complete_deal->seller_email_otp,'name' => $sellers->name);

            // Mail::send(['html'=>'mail'], $s_data, function($message) use($sellers) {
            //     $message->to($sellers->email, 'E - Cotton')->subject('Make Deal OTP');
            // });

            $response['status'] = 200;
			$response['message'] = 'OTP sent successfully';
        }else if($user_type == "buyer"){

            $buyers = Buyers::select('mobile_number','email','name')->where('id',$complete_deal->buyer_id)->first();
            $by_message = "OTP to verify make deal is ". $complete_deal->buyer_otp ." - E - Cotton";
            NotificationHelper::send_otp($buyers->mobile_number,$by_message);

            $by_data = array('otp'=>$complete_deal->buyer_email_otp,'name' => $buyers->name);

            // Mail::send(['html'=>'mail'], $by_data, function($message) use($buyers) {
            //     $message->to($buyers->email, 'E - Cotton')->subject('Make deal OTP');
            // });

            $response['status'] = 200;
			$response['message'] = 'OTP sent successfully';
        }else if($user_type == "broker"){

            $brokers = Brokers::select('mobile_number','email','name')->where('id',$complete_deal->default_broker->broker_id)->first();
            $br_message = "OTP to verify make deal is ". $complete_deal->broker_otp ." - E - Cotton";
            NotificationHelper::send_otp($brokers->mobile_number,$br_message);

            $br_data = array('otp'=>$complete_deal->broker_email_otp,'name' => $brokers->name);

            // Mail::send(['html'=>'mail'], $br_data, function($message) use($brokers) {
            //     $message->to($brokers->email, 'E - Cotton')->subject('Make deal OTP');
            // });
            $response['status'] = 200;
			$response['message'] = 'OTP sent successfully';
        }

		return response($response, 200);

    }
}
