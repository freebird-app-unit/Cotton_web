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
use App\Models\WithoutNegotiationMakeDeal;
use App\Models\NegotiationComplete;
use Validator;
use Carbon\Carbon;

class NegotiationController extends Controller
{
    public function negotiation_list_new(Request $request)
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
				$post_data = Post::where(['id'=>$i1,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($post_data)){
						$post_array = [];
						$product_name = '';
				        $products = Product::where('id',$post_data->product_id)->first();
				        if(!empty($products)){
				            $product_name = $products->name;
				        }
				       $name = '';
				       $broker_name ='';
				        if($post_data->user_type == "seller"){
				            $seller = Sellers::where('id',$post_data->seller_buyer_id)->first();
					        if(!empty($seller)){
					          	$name = $seller->name;
					          	$broker_data = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
					        }
				        }
				        if($post_data->user_type == "buyer"){
				         	$buyer = Buyers::where('id',$post_data->seller_buyer_id)->first();
				         	if(!empty($buyer)){
				          		$name = $buyer->name;
				          		$broker_data = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }

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

							$buyer_id = [];
							$post_array[] = [
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
								'negotiation_type' => $negotiation_post->negotiation_type,
								'transmit_condition' => $transmit_condition_name,
								'payment_condition' => $payment_condition_name,
								'lab' => $lab_name,
								'post_notification_id' => $negotiation_post->post_notification_id,
							];

							if(count($post_array)>0){
								foreach ($post_array as $sell) {
									array_push($buyer_id, $sell['buyer_id']);
								}
							}
						}
					}

					$best_price = [];
					$unique_buyer_negotiation_ids = array_unique($buyer_id);
					if(count($unique_buyer_negotiation_ids)>1){
						foreach ($post_array as $post1) {
							array_push($best_price, $post1['current_price']);
						}
					}
					
					if(count($best_price)>0){
						$max = max($best_price);
						foreach ($post_array as $post_value) {
							if($post_value['current_price'] == $max){
								$negotiation_post_arr[] = [
									'post_id' => $post_data->id,
									'status' => $post_data->status,
									'seller_buyer_id' => $post_data->seller_buyer_id,
									'name' => $name,
									'broker_name' => $broker_name,
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
									'best_name' => $post_value['buyer_name'],
									'count' => count($post_array),
									'post_detail' => $post_array,
								];
							}
						}
					}else{
						$negotiation_post_arr[] = [
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
			foreach ($unique_notification_negotiation_buyer_ids as $i22) {
				foreach ($unique_notification_ids as $i2) {
					$notification = Notification::where(['id'=>$i2,'is_active'=>0,'status'=>'active'])->first();
					if(!empty($notification)){
						$notification_array = [];
						$product_name = '';
				        $products = Product::where('id',$notification->product_id)->first();
				        if(!empty($products)){
				            $product_name = $products->name;
				        }
				        $name = '';
				        $broker_name = '';
				        if($notification->user_type == "seller"){
				         	$seller = Sellers::where('id',$notification->seller_buyer_id)->first();
				         	if(!empty($seller)){
				          		$name = $seller->name;
				          		$broker_data = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }
				        if($notification->user_type == "buyer"){
				         	$buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
				         	if(!empty($buyer)){
				          		$name = $buyer->name;
				          		$broker_data = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }
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
						$buyer_id = [];
						$notification_array[] = [
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
							'negotiation_type' => $negotiation_notification->negotiation_type,
							'prev_no_of_bales' => $negotiation_notification->prev_no_of_bales,
							'transmit_condition' => $transmit_condition_name,
							'payment_condition' => $payment_condition_name,
							'lab' => $lab_name,
							'post_notification_id' => $negotiation_notification->post_notification_id,
						];
						if(count($notification_array)>0){
							foreach ($notification_array as $sell) {
								array_push($buyer_id, $sell['buyer_id']);
							}
						}
					}
				}
				$best_price = [];
					$unique_buyer_negotiation_ids = array_unique($buyer_id);
					if(count($unique_buyer_negotiation_ids)>1){
						foreach ($notification_array as $notification1) {
							array_push($best_price, $notification1['current_price']);
						}
					}
				if(count($best_price)>0){
						$max = max($best_price);
						foreach ($notification_array as $notification_value) {
							if($notification_value['current_price'] == $max){
								$negotiation_notification_arr[] = [
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
									'count' => count($post_array),
									'notification_detail' =>(!empty($notification_array))?$notification_array:''
								];
							}
						}
					}else{
						$negotiation_post_arr[] = [
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
			$negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);
			
			$response['status'] = 200;
			$response['message'] = 'Negotiation list';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}

		public function negotiation_list_buyer_new(Request $request)
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
				$post_data = Post::where(['id'=>$i1,'is_active'=>0,'status'=>'active'])->first();
				if(!empty($post_data)){
						$post_array = [];
						$product_name = '';
				        $products = Product::where('id',$post_data->product_id)->first();
				        if(!empty($products)){
				            $product_name = $products->name;
				        }
				       $name = '';
				       $broker_name = '';
				        if($post_data->user_type == "seller"){
				            $seller = Sellers::where('id',$post_data->seller_buyer_id)->first();
					        if(!empty($seller)){
					          	$name = $seller->name;
					          	$broker_data = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
					        }
				        }
				        if($post_data->user_type == "buyer"){
				         	$buyer = Buyers::where('id',$post_data->seller_buyer_id)->first();
				         	if(!empty($buyer)){
				          		$name = $buyer->name;
				          		$broker_data = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }
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

							$seller_id = [];
							$post_array[] = [
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
							];
							if(count($post_array)>0){
								foreach ($post_array as $sell) {
									array_push($seller_id, $sell['seller_id']);
								}
							}
						}
					}
					$best_price = [];
					$unique_seller_negotiation_ids = array_unique($seller_id);
					if(count($unique_seller_negotiation_ids)>1){
						foreach ($post_array as $post1) {
							array_push($best_price, $post1['current_price']);
						}
					}
					if(count($best_price)>0){
						$max = max($best_price);
						foreach ($post_array as $post_value) {
							if($post_value['current_price'] == $max){
								
								$negotiation_post_arr[] = [
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
						$negotiation_post_arr[] = [
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
					$notification = Notification::where(['id'=>$i2,'is_active'=>0,'status'=>'active'])->first();
					if(!empty($notification)){
						$notification_array = [];
						$product_name = '';
				        $products = Product::where('id',$notification->product_id)->first();
				        if(!empty($products)){
				            $product_name = $products->name;
				        }
				        $name = '';
				        $broker_name='';
				        if($notification->user_type == "seller"){
				         	$seller = Sellers::where('id',$notification->seller_buyer_id)->first();
				         	if(!empty($seller)){
				          		$name = $seller->name;
				          		$broker_data = Brokers::where('code',$seller->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }
				        if($notification->user_type == "buyer"){
				         	$buyer = Buyers::where('id',$notification->seller_buyer_id)->first();
				         	if(!empty($buyer)){
				          		$name = $buyer->name;
				          		$broker_data = Brokers::where('code',$buyer->referral_code)->first();
								if(!empty($broker_data)){
									$broker_name = $broker_data->name;
								}
				         	}
				        }
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
						$seller_id = [];
						$notification_array[] = [
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
						];
						if(count($notification_array)>0){
							foreach ($notification_array as $sell) {
								array_push($seller_id, $sell['seller_id']);
							}
						}
					}
				}
				$best_price = [];
					$unique_seller_negotiation_ids = array_unique($seller_id);
					if(count($unique_seller_negotiation_ids)>1){
						foreach ($notification_array as $notification1) {
							array_push($best_price, $notification1['current_price']);
						}
					}
				if(count($best_price)>0){
						$max = max($best_price);
						foreach ($notification_array as $notification_value) {
							if($notification_value['current_price'] == $max){
								$negotiation_notification_arr[] = [
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
						$negotiation_post_arr[] = [
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
			$negotiation_array = array_merge($negotiation_post_arr,$negotiation_notification_arr);
			
			$response['status'] = 200;
			$response['message'] = 'Negotiation list Buy';
			$response['data'] = $negotiation_array;
		}else{
			$response['status'] = 404;
		}

		return response($response, 200);
	}
}
