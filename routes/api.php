<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});


Route::group(['namespace' => 'Api\seller'], function () {
	Route::post('/registration_seller', 'LoginController@registration_seller');
	Route::post('/login_seller', 'LoginController@login_seller');
	Route::post('/otp_verify_seller', 'LoginController@otp_verify_seller');
	Route::post('/resend_otp_seller', 'LoginController@resend_otp_seller');
	Route::post('/forgot_password_seller', 'LoginController@forgot_password_seller');
	Route::post('/reset_password_seller', 'LoginController@reset_password_seller');
	Route::post('/change_password_seller', 'LoginController@change_password_seller');
	Route::post('/profile_seller', 'LoginController@profile_seller');
	Route::post('/logout_seller', 'LoginController@logout_seller');
	Route::post('/business_type', 'LoginController@business_type');
	Route::post('/registration_as', 'LoginController@registration_as');
	Route::post('/country_list', 'LoginController@country_list');
	Route::post('/state_list', 'LoginController@state_list');
	Route::post('/city_list', 'LoginController@city_list');
	Route::post('/station_list', 'LoginController@station_list');
	Route::post('/buyer_type', 'LoginController@buyer_type');
	Route::post('/seller_type', 'LoginController@seller_type');
	Route::post('/sellertype_buyertype_businesstype_registrationas', 'LoginController@sellertype_buyertype_businesstype_registrationas');
	Route::post('/edit_profile_seller', 'ProfileController@edit_profile_seller');
});

Route::group(['namespace' => 'Api\buyer'], function () {
	Route::post('/registration_buyer', 'LoginController@registration_buyer');
	Route::post('/login_buyer', 'LoginController@login_buyer');
	Route::post('/otp_verify_buyer', 'LoginController@otp_verify_buyer');
	Route::post('/resend_otp_buyer', 'LoginController@resend_otp_buyer');
	Route::post('/forgot_password_buyer', 'LoginController@forgot_password_buyer');
	Route::post('/reset_password_buyer', 'LoginController@reset_password_buyer');
	Route::post('/change_password_buyer', 'LoginController@change_password_buyer');
	Route::post('/profile_buyer', 'LoginController@profile_buyer');
	Route::post('/logout_buyer', 'LoginController@logout_buyer');
	Route::post('/edit_profile_buyer', 'ProfileController@edit_profile_buyer');
});

Route::group(['namespace' => 'Api\broker'], function () {
	Route::post('/registration_broker', 'LoginController@registration_broker');
	Route::post('/login_broker', 'LoginController@login_broker');
	Route::post('/otp_verify_broker', 'LoginController@otp_verify_broker');
	Route::post('/resend_otp_broker', 'LoginController@resend_otp_broker');
	Route::post('/forgot_password_broker', 'LoginController@forgot_password_broker');
	Route::post('/reset_password_broker', 'LoginController@reset_password_broker');
	Route::post('/change_password_broker', 'LoginController@change_password_broker');
	Route::post('/profile_broker', 'LoginController@profile_broker');
	Route::post('/logout_broker', 'LoginController@logout_broker');
	Route::post('/seller_buyer_list_base_on_code', 'LoginController@seller_buyer_list_base_on_code');
	Route::post('/edit_profile_broker', 'ProfileController@edit_profile_broker');

	Route::post('/search_broker', 'SearchController@search_broker');
	Route::post('/add_broker', 'SearchController@add_broker');
	Route::post('/add_broker_verify', 'SearchController@add_broker_verify');
	Route::post('/add_broker_list', 'SearchController@add_broker_list');
	Route::post('/delete_broker', 'SearchController@delete_broker');
});


Route::group(['namespace' => 'Api\product'], function () {
	Route::post('/product_list', 'ProductController@product_list');
	Route::post('/product_attribute_list', 'ProductController@product_attribute_list');
	Route::post('/post_to_sell', 'ProductController@post_to_sell');
	Route::post('/post_to_sell_list', 'ProductController@post_to_sell_list');
	Route::post('/notification_to_buy', 'ProductController@notification_to_buy');
	Route::post('/notification_to_buy_list', 'ProductController@notification_to_buy_list');
	Route::post('/notification_post_buy_list', 'ProductController@notification_post_buy_list');
	Route::post('/post_to_buy', 'ProductController@post_to_buy');
	Route::post('/post_to_buy_list', 'ProductController@post_to_buy_list');
	Route::post('/notification_to_seller', 'ProductController@notification_to_seller');
	Route::post('/notification_to_seller_list', 'ProductController@notification_to_seller_list');
	Route::post('/notification_post_seller_list', 'ProductController@notification_post_seller_list');
	Route::post('/search_to_sell', 'ProductController@search_to_sell');
	Route::post('/search_to_buy', 'ProductController@search_to_buy');
	Route::post('/cancel_post', 'ProductController@cancel_post');
	Route::post('/transmit_condition', 'ProductController@transmit_condition');
	Route::post('/payment_condition', 'ProductController@payment_condition');
	Route::post('/transmit_payment_lab_list', 'ProductController@transmit_payment_lab_list');
	Route::post('/lab_list', 'ProductController@lab_list');
	Route::post('/negotiation', 'ProductController@negotiation');
	Route::post('/negotiation_list', 'ProductController@negotiation_list');
	Route::post('/negotiation_list_buyer', 'ProductController@negotiation_list_buyer');
	Route::post('/settings', 'ProductController@settings');
	Route::post('/make_deal', 'ProductController@make_deal');
	Route::post('/completed_deal', 'ProductController@completed_deal');
	Route::post('/completed_deal_buyer', 'ProductController@completed_deal_buyer');
	Route::post('/cancel_notification', 'ProductController@cancel_notification');
	Route::post('/negotiation_detail', 'ProductController@negotiation_detail');
	Route::post('/contract', 'ProductController@contract');
	Route::post('/search_seller', 'ProductController@search_seller');
	Route::post('/search_buyer', 'ProductController@search_buyer');
	Route::post('/post_details', 'ProductController@post_details');
	Route::post('/my_contract', 'ProductController@my_contract');
	Route::post('/my_contract_filter', 'ProductController@my_contract_filter');
	Route::post('/search_to_sell_new', 'ProductController@search_to_sell_new');
	Route::post('/search_to_buy_new', 'ProductController@search_to_buy_new');
	Route::post('/update_transaction_tracking', 'ProductController@update_transaction_tracking');

	Route::post('/negotiation_list_new', 'NegotiationController@negotiation_list_new');
	Route::post('/negotiation_list_buyer_new', 'NegotiationController@negotiation_list_buyer_new');

    //new api
    Route::post('/negotiation_new_v2', 'ProductController@negotiation_new_v2');
    Route::post('/make_deal_new_v2', 'ProductController@make_deal_new_v2');
    Route::post('/negotiation_list_new_v2', 'ProductController@negotiation_list_new_v2');
    Route::post('/negotiation_list_buyer_new_v2', 'ProductController@negotiation_list_buyer_new_v2');
    Route::post('/negotiation_detail_new_v2', 'ProductController@negotiation_detail_new_v2');
    Route::post('/lab_report_status', 'ProductController@lab_report_status');
    Route::post('/search_to_sell_new_v2', 'ProductController@search_to_sell_new_v2');
    Route::post('/completed_deal_new_v2', 'ProductController@completed_deal_new_v2');
    Route::post('/completed_deal_buyer_new_v2', 'ProductController@completed_deal_buyer_new_v2');
    Route::post('/completed_deal_detail_new_v2', 'ProductController@completed_deal_detail_new_v2');
    Route::post('/negotiation_detail_by_deal_new_v2', 'ProductController@negotiation_detail_by_deal_new_v2');

Route::post('/news', 'Api\seller\LoginController@news_list');
Route::post('/news_details', 'Api\seller\LoginController@news_details');
Route::post('/broker_list', 'Api\seller\LoginController@broker_list');
