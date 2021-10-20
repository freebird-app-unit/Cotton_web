<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

// Route::get('/', 'HomeController@index')->name('home');
Auth::routes();

Route::get('/', 'Auth\AdminLoginController@showLoginForm')->name('login');
Route::get('/login', 'Auth\AdminLoginController@showLoginForm')->name('login');
Route::post('/login_admin', 'Auth\AdminLoginController@login');
Route::get('/logout', 'Auth\AdminLoginController@logout')->name('admin.logout');
Route::post('/forget_password', 'Auth\AdminLoginController@forget_password')->name('forget_password');
Route::post('/conform_otp', 'Auth\AdminLoginController@conform_otp')->name('conform_otp');
Route::post('/change_password', 'Auth\AdminLoginController@change_password')->name('change_password');

Route::group(['middleware' => ['auth:web']], function() {

    Route::get('/dashboard', 'HomeController@index')->name('dashboard');
    Route::get('/settings', 'SettingController@index')->name('settings');
    Route::post('/setting_store', 'SettingController@store')->name('setting_store');
    Route::get('/profile', 'SettingController@profile')->name('profile');
    Route::post('/profile_store', 'SettingController@profile_store')->name('profile_store');
    Route::post('/change_password', 'SettingController@change_password')->name('change_password');

    Route::group(['prefix' => 'product'], function () {
        Route::get('/', ['as' => 'product_index', 'uses' => 'ProductController@index']);
        Route::post('list', ['as' => 'product_list', 'uses' => 'ProductController@index']);
        Route::get('add', ['as' => 'product_create', 'uses' => 'ProductController@add']);
        Route::post('store', ['as' => 'product_store', 'uses' => 'ProductController@store']);
        Route::get('{id}/edit', ['as' => 'product_edit', 'uses' => 'ProductController@edit']);
        Route::post('delete/{id}', ['as' => 'product_delete', 'uses' => 'ProductController@destroy']);
    });

    Route::group(['prefix' => 'country'], function () {
        Route::get('/', ['as' => 'country_index', 'uses' => 'CountryController@index']);
        Route::post('list', ['as' => 'country_list', 'uses' => 'CountryController@index']);
        Route::get('add', ['as' => 'country_create', 'uses' => 'CountryController@add']);
        Route::post('store', ['as' => 'country_store', 'uses' => 'CountryController@store']);
        Route::get('{id}/edit', ['as' => 'country_edit', 'uses' => 'CountryController@edit']);
        Route::post('delete/{id}', ['as' => 'country_delete', 'uses' => 'CountryController@destroy']);
    });

    Route::group(['prefix' => 'state'], function () {
        Route::get('/', ['as' => 'state_index', 'uses' => 'StateController@index']);
        Route::post('list', ['as' => 'state_list', 'uses' => 'StateController@index']);
        Route::get('add', ['as' => 'state_create', 'uses' => 'StateController@add']);
        Route::post('store', ['as' => 'state_store', 'uses' => 'StateController@store']);
        Route::get('{id}/edit', ['as' => 'state_edit', 'uses' => 'StateController@edit']);
        Route::post('delete/{id}', ['as' => 'state_delete', 'uses' => 'StateController@destroy']);
    });

    Route::group(['prefix' => 'city'], function () {
        Route::get('/', ['as' => 'city_index', 'uses' => 'CityController@index']);
        Route::post('list', ['as' => 'city_list', 'uses' => 'CityController@index']);
        Route::get('add', ['as' => 'city_create', 'uses' => 'CityController@add']);
        Route::post('store', ['as' => 'city_store', 'uses' => 'CityController@store']);
        Route::get('{id}/edit', ['as' => 'city_edit', 'uses' => 'CityController@edit']);
        Route::post('delete/{id}', ['as' => 'city_delete', 'uses' => 'CityController@destroy']);
    });

    Route::group(['prefix' => 'lab'], function () {
        Route::get('/', ['as' => 'lab_index', 'uses' => 'LabController@index']);
        Route::post('list', ['as' => 'lab_list', 'uses' => 'LabController@index']);
        Route::get('add', ['as' => 'lab_create', 'uses' => 'LabController@add']);
        Route::post('store', ['as' => 'lab_store', 'uses' => 'LabController@store']);
        Route::get('{id}/edit', ['as' => 'lab_edit', 'uses' => 'LabController@edit']);
        Route::post('delete/{id}', ['as' => 'lab_delete', 'uses' => 'LabController@destroy']);
    });

    Route::group(['prefix' => 'payment_condition'], function () {
        Route::get('/', ['as' => 'payment_condition_index', 'uses' => 'PaymentConditionController@index']);
        Route::post('list', ['as' => 'payment_condition_list', 'uses' => 'PaymentConditionController@index']);
        Route::get('add', ['as' => 'payment_condition_create', 'uses' => 'PaymentConditionController@add']);
        Route::post('store', ['as' => 'payment_condition_store', 'uses' => 'PaymentConditionController@store']);
        Route::get('{id}/edit', ['as' => 'payment_condition_edit', 'uses' => 'PaymentConditionController@edit']);
        Route::post('delete/{id}', ['as' => 'payment_condition_delete', 'uses' => 'PaymentConditionController@destroy']);
    });

    Route::group(['prefix' => 'bussiness_type'], function () {
        Route::get('/', ['as' => 'bussiness_type_index', 'uses' => 'BussinessTypeController@index']);
        Route::post('list', ['as' => 'bussiness_type_list', 'uses' => 'BussinessTypeController@index']);
        Route::get('add', ['as' => 'bussiness_type_create', 'uses' => 'BussinessTypeController@add']);
        Route::post('store', ['as' => 'bussiness_type_store', 'uses' => 'BussinessTypeController@store']);
        Route::get('{id}/edit', ['as' => 'bussiness_type_edit', 'uses' => 'BussinessTypeController@edit']);
        Route::post('delete/{id}', ['as' => 'bussiness_type_delete', 'uses' => 'BussinessTypeController@destroy']);
    });

    Route::group(['prefix' => 'seller'], function () {
        Route::get('/', ['as' => 'seller_index', 'uses' => 'SellerController@index']);
        Route::post('list', ['as' => 'seller_list', 'uses' => 'SellerController@index']);
        Route::get('add', ['as' => 'seller_create', 'uses' => 'SellerController@add']);
        Route::post('store', ['as' => 'seller_store', 'uses' => 'SellerController@store']);
        Route::get('detail/{id}', ['as' => 'seller_detail', 'uses' => 'SellerController@detail']);
        Route::post('delete/{id}', ['as' => 'seller_delete', 'uses' => 'SellerController@destroy']);
        Route::post('seller_approval', ['as' => 'seller_approval', 'uses' => 'SellerController@seller_approval']);
        Route::post('seller_status', ['as' => 'seller_status', 'uses' => 'SellerController@seller_status']);
    });

    Route::group(['prefix' => 'buyer_type'], function () {
        Route::get('/', ['as' => 'buyer_type_index', 'uses' => 'BuyerTypeController@index']);
        Route::post('list', ['as' => 'buyer_type_list', 'uses' => 'BuyerTypeController@index']);
        Route::get('add', ['as' => 'buyer_type_create', 'uses' => 'BuyerTypeController@add']);
        Route::post('store', ['as' => 'buyer_type_store', 'uses' => 'BuyerTypeController@store']);
        Route::get('{id}/edit', ['as' => 'buyer_type_edit', 'uses' => 'BuyerTypeController@edit']);
        Route::post('delete/{id}', ['as' => 'buyer_type_delete', 'uses' => 'BuyerTypeController@destroy']);
        Route::post('buyer_status', ['as' => 'buyer_type_status', 'uses' => 'BuyerTypeController@buyer_status']);
    });

    Route::group(['prefix' => 'registration_type'], function () {
        Route::get('/', ['as' => 'registration_type_index', 'uses' => 'RegistrationTypeController@index']);
        Route::post('list', ['as' => 'registration_type_list', 'uses' => 'RegistrationTypeController@index']);
        Route::get('add', ['as' => 'registration_type_create', 'uses' => 'RegistrationTypeController@add']);
        Route::post('store', ['as' => 'registration_type_store', 'uses' => 'RegistrationTypeController@store']);
        Route::get('{id}/edit', ['as' => 'registration_type_edit', 'uses' => 'RegistrationTypeController@edit']);
        Route::post('delete/{id}', ['as' => 'registration_type_delete', 'uses' => 'RegistrationTypeController@destroy']);
        Route::post('registration_status', ['as' => 'registration_type_status', 'uses' => 'RegistrationTypeController@registration_status']);
    });

    Route::group(['prefix' => 'seller_type'], function () {
        Route::get('/', ['as' => 'seller_type_index', 'uses' => 'SellerTypeController@index']);
        Route::post('list', ['as' => 'seller_type_list', 'uses' => 'SellerTypeController@index']);
        Route::get('add', ['as' => 'seller_type_create', 'uses' => 'SellerTypeController@add']);
        Route::post('store', ['as' => 'seller_type_store', 'uses' => 'SellerTypeController@store']);
        Route::get('{id}/edit', ['as' => 'seller_type_edit', 'uses' => 'SellerTypeController@edit']);
        Route::post('delete/{id}', ['as' => 'seller_type_delete', 'uses' => 'SellerTypeController@destroy']);
        Route::post('seller_status', ['as' => 'seller_type_status', 'uses' => 'SellerTypeController@seller_status']);
    });

    Route::group(['prefix' => 'transmit_condition'], function () {
        Route::get('/', ['as' => 'transmit_condition_index', 'uses' => 'TransmitConditionController@index']);
        Route::post('list', ['as' => 'transmit_condition_list', 'uses' => 'TransmitConditionController@index']);
        Route::get('add', ['as' => 'transmit_condition_create', 'uses' => 'TransmitConditionController@add']);
        Route::post('store', ['as' => 'transmit_condition_store', 'uses' => 'TransmitConditionController@store']);
        Route::get('{id}/edit', ['as' => 'transmit_condition_edit', 'uses' => 'TransmitConditionController@edit']);
        Route::post('delete/{id}', ['as' => 'transmit_condition_delete', 'uses' => 'TransmitConditionController@destroy']);
    });

    Route::group(['prefix' => 'buyer'], function () {
        Route::get('/', ['as' => 'buyer_index', 'uses' => 'BuyerController@index']);
        Route::post('list', ['as' => 'buyer_list', 'uses' => 'BuyerController@index']);
        Route::get('add', ['as' => 'buyer_create', 'uses' => 'BuyerController@add']);
        Route::post('store', ['as' => 'buyer_store', 'uses' => 'BuyerController@store']);
        Route::get('detail/{id}', ['as' => 'buyer_detail', 'uses' => 'BuyerController@detail']);
        Route::post('delete/{id}', ['as' => 'buyer_delete', 'uses' => 'BuyerController@destroy']);
        Route::post('buyer_approval', ['as' => 'buyer_approval', 'uses' => 'BuyerController@buyer_approval']);
        Route::post('buyer_status', ['as' => 'buyer_status', 'uses' => 'BuyerController@buyer_status']);
    });

	 Route::group(['prefix' => 'broker'], function () {
        Route::get('/', ['as' => 'broker_index', 'uses' => 'BrokerController@index']);
        Route::post('list', ['as' => 'broker_list', 'uses' => 'BrokerController@index']);
        Route::get('add', ['as' => 'broker_create', 'uses' => 'BrokerController@add']);
        Route::post('store', ['as' => 'broker_store', 'uses' => 'BrokerController@store']);
        Route::get('detail/{id}', ['as' => 'broker_detail', 'uses' => 'BrokerController@detail']);
        Route::post('delete/{id}', ['as' => 'broker_delete', 'uses' => 'BrokerController@destroy']);
        Route::post('broker_approval', ['as' => 'broker_approval', 'uses' => 'BrokerController@broker_approval']);
        Route::post('broker_status', ['as' => 'broker_status', 'uses' => 'BrokerController@broker_status']);
    });

	Route::group(['prefix' => 'news'], function () {
        Route::get('/', ['as' => 'news_index', 'uses' => 'NewsController@index']);
        Route::post('list', ['as' => 'news_list', 'uses' => 'NewsController@index']);
        Route::get('add', ['as' => 'news_create', 'uses' => 'NewsController@add']);
        Route::post('store', ['as' => 'news_store', 'uses' => 'NewsController@store']);
        Route::get('{id}/edit', ['as' => 'news_edit', 'uses' => 'NewsController@edit']);
        Route::post('delete/{id}', ['as' => 'news_delete', 'uses' => 'NewsController@destroy']);
    });

	Route::group(['prefix' => 'subject_to'], function () {
        Route::get('/', ['as' => 'subject_to_index', 'uses' => 'SubjectToController@index']);
        Route::post('list', ['as' => 'subject_to_list', 'uses' => 'SubjectToController@index']);
        Route::get('add', ['as' => 'subject_to_create', 'uses' => 'SubjectToController@add']);
        Route::post('store', ['as' => 'subject_to_store', 'uses' => 'SubjectToController@store']);
        Route::get('{id}/edit', ['as' => 'subject_to_edit', 'uses' => 'SubjectToController@edit']);
        Route::post('delete/{id}', ['as' => 'subject_to_delete', 'uses' => 'SubjectToController@destroy']);
    });

	Route::group(['prefix' => 'confirm_to'], function () {
        Route::get('/', ['as' => 'confirm_to_index', 'uses' => 'ConfirmToController@index']);
        Route::post('list', ['as' => 'confirm_to_list', 'uses' => 'ConfirmToController@index']);
        Route::get('add', ['as' => 'confirm_to_create', 'uses' => 'ConfirmToController@add']);
        Route::post('store', ['as' => 'confirm_to_store', 'uses' => 'ConfirmToController@store']);
        Route::get('{id}/edit', ['as' => 'confirm_to_edit', 'uses' => 'ConfirmToController@edit']);
        Route::post('delete/{id}', ['as' => 'confirm_to_delete', 'uses' => 'ConfirmToController@destroy']);
    });

    Route::resource('plan', 'PlanController');

    Route::post('check_seller_code', ['as' => 'check_seller_code', 'uses' => 'SellerController@check_seller_code']);
    Route::post('send_broker_otp', ['as' => 'send_broker_otp', 'uses' => 'SellerController@send_broker_otp']);
    Route::post('verify_broker_otp', ['as' => 'verify_broker_otp', 'uses' => 'SellerController@verify_broker_otp']);
    Route::post('check_buyer_code', ['as' => 'check_buyer_code', 'uses' => 'BuyerController@check_buyer_code']);
    Route::post('verify_buyer_broker_otp', ['as' => 'verify_buyer_broker_otp', 'uses' => 'BuyerController@verify_buyer_broker_otp']);
});
