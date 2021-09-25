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
use Validator;
use Carbon\Carbon;
use PDF;
use Storage;
use File;
use Image;

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
					$negotiation = Negotiation::where(['buyer_id'=>$buyer_id,'post_notification_id'=>$notification->id,'negotiation_type'=>'notification','is_deal'=>0])->get();
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
			if(!empty($sellers)){
				foreach ($sellers as $value) {
					$select = new SelectionSellerBuyer();
					$select->user_type = 'seller';
					$select->seller_buyer_id = $value;
					$select->notification_id = $notification->id;
					$select->save();
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
					$negotiation = Negotiation::where(['seller_id'=>$seller_id,'post_notification_id'=>$notification->id,'negotiation_type'=>'notification','is_deal'=>0])->get();
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

		$search_array = [];
		$post_arr = [];
        $country_arr = [];
        $state_arr = [];
        $city_arr = [];
        $station_arr = [];
		if(!empty($attribute_array)){
			foreach ($attribute_array as $val) {
				$search = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->get();

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
		$negotiation = Negotiation::where(['seller_id'=>$seller_id,'buyer_id'=>$buyer_id,'post_notification_id'=>$post_notification_id])->get();
		foreach ($negotiation as $value) {
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
			$negotiation_array[] = [
					'negotiation_id' => $value->id,
					'seller_id' => $value->seller_id,
					'seller_name' => $seller_name,
					'buyer_id' => $value->buyer_id,
					'buyer_name' => $buyer_name,
					'negotiation_by' => $value->negotiation_by,
					'post_notification_id' => $value->post_notification_id,
					'negotiation_type' => $value->negotiation_type,
					'current_price' => $value->current_price,
					'prev_price' => $value->prev_price,
					'current_no_of_bales' => $value->current_no_of_bales,
					'prev_no_of_bales' => $value->prev_no_of_bales,
					'transmit_condition' => $transmit_condition_name,
					'payment_condition' => $payment_condition_name,
					'lab' => $lab_name,
			];
			$response['status'] = 200;
			$response['message'] = 'Negotiation Detail';
			$response['data'] = $negotiation_array;
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
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id,'is_deal'=>1])->first();
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
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id,'is_deal'=>1])->first();
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
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id,'is_deal'=>1])->orderBy('id','DESC')->first();
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
					$negotiation = Negotiation::where(['post_notification_id'=>$value->id,'is_deal'=>1])->first();
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
		$buyer = UserDetails::where(['user_type'=>'buyer','country_id'=>$country_id,'state_id'=>$state_id,'city_id'=>$city_id,'station_id'=>$station_id])->get();
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
				
				$negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('seller_id',$seller_buyer_id)->orderBy('id','DESC')->get();
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
							'url'=>$url
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
							'url'=>$url
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
				
				$negotiation_data = NegotiationComplete::whereDate('updated_at',$date)->where('buyer_id',$seller_buyer_id)->orderBy('id','DESC')->get();
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
							'url'=>$url
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
							'url'=>$url
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

		$final_arr = [];
		$dates = [];
		if($user_type == "seller"){
				if($time_duration == "monthly"){
					$start_date = date('Y-m-01'); // hard-coded '01' for first day
               		$end_date  = date('Y-m-t');
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$start_date,$end_date])->where('seller_id',$seller_buyer_id)->get();
				}
				if($time_duration == "custom"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$date_to,$date_from])->where('seller_id',$seller_buyer_id)->get();
				}
				if($time_duration == "weekly"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('seller_id',$seller_buyer_id)->get();
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
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$start_date,$end_date])->where('buyer_id',$seller_buyer_id)->get();
				}
				if($time_duration == "custom"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[$date_to,$date_from])->where('buyer_id',$seller_buyer_id)->get();
				}
				if($time_duration == "weekly"){
					$negotiation_data = NegotiationComplete::whereBetween('updated_at',[Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->where('seller_id',$seller_buyer_id)->get();
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
		$required = isset($content->required) ? $content->required : '';
		$non_required = isset($content->non_required) ? $content->non_required : '';
		$d_e = isset($content->d_e) ? $content->d_e : '';

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
            	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0])->where('tbl_post_details.attribute',$val->attribute)->where('tbl_post_details.attribute_value', $val->attribute_value)->get();
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

        if(empty($required)){
        	foreach ($non_required as $key => $val) {
        		$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'buyer','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.d_e'=>$d_e,'tbl_post.is_active'=>0])->where('tbl_post_details.attribute',$val->attribute)->where('tbl_post_details.attribute_value', $val->attribute_value)->get();

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
		$required = isset($content->required) ? $content->required : '';
		$non_required = isset($content->non_required) ? $content->non_required : '';

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
            	$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->get();
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

        if(empty($required)){
        	foreach ($non_required as $key => $val) {
        		$query = Post::with('user_detail','user_detail.country','user_detail.state','user_detail.city','user_detail.station')->select('tbl_post.id','tbl_post.status','tbl_post.seller_buyer_id','tbl_post.user_type','tbl_post.product_id','tbl_post.no_of_bales','tbl_post.price','tbl_post.address','tbl_post.d_e','tbl_post.buy_for','tbl_post.spinning_meal_name')->leftJoin('tbl_post_details', 'tbl_post_details.post_id', '=', 'tbl_post.id')->where(['tbl_post.user_type'=>'seller','tbl_post.status'=>'active','tbl_post.is_active'=>0,'tbl_post.product_id'=>$product_id,'tbl_post.is_active'=>0,'tbl_post_details.attribute'=>$val->attribute,'tbl_post_details.attribute_value'=>$val->attribute_value])->get();

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
					'attribute_array' => $attribute_array,
					'date' => $created_at
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
					'attribute_array' => $attribute_array,
					'seller_array' => $seller_array,
					'date' => $created_at,
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
					'type' => 'post',
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
					'attribute_array' => $attribute_array,
					'buyer_array' => $buyer_array,
					'date' => $created_at,
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
		
		$response['status'] = 200;
		$response['message'] = 'Updated Successfully';
		$response['data'] = (object)[];
		return response($response, 200);
	}
}
