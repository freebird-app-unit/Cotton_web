<?php

namespace App\Http\Controllers;

use App\Models\Settings;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Response;
use Validator;
use Storage;
use Image;
use Hash;

class SettingController extends Controller
{
    public function index()
    {
        $setting = Settings::first();
    	return view('setting.form',compact('setting'));
    }

    public function store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
        ]);

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        $site_logo = "";
        if ($request->hasFile('site_logo')) {
            $path = Storage::disk('public')->getAdapter()->applyPathPrefix('profile/'.$request->hidden_site_logo);
            if(file_exists($path)) {
            @unlink($path);
            }
            $image = $request->file('site_logo');
            $site_logo = time() . '.' . $image->getClientOriginalExtension();
            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point
            Storage::disk('public')->put('profile/' . $site_logo, $img, 'public');
        }else{
            $site_logo = $request->hidden_site_logo;
        }

        // dd($site_logo);
        $setting = Settings::updateOrCreate(
            [
                'id' => $request->setting_id,
            ],
            [
                'site_name'    => $request->site_name,
                'site_email'    => $request->site_email,
                'site_contact'    => $request->site_contact,
                'site_address'    => $request->site_address,
                'site_logo'    =>  $site_logo,
                'negotiation_count'    => $request->negotiation_count,
                'bunch'    => $request->bunch,
            ]
        );
        $setting->save();

        if (!empty($request->setting_id)) {
            $message = "Record Update successfully";
        } else {
            $message = "Record Save successfully";
        }

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => $message,
            "data"            => []
        ]);
    }

    public function profile(){
        $user = User::where('id',Auth::user()->id)->first();
        return view('profile.form',compact('user'));
    }

    public function profile_store(Request $request)
    {
        // dd($request->all());

    	$validator = Validator::make($request->all(), [
            'name' => 'required',
            'mobile_no' => 'required|unique:users,mobile_no,'.$request->user_id
        ]);

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        $profile_image = "";
        if ($request->hasFile('image')) {
            $path = Storage::disk('public')->getAdapter()->applyPathPrefix('profile/'.$request->hidden_image);
            if(file_exists($path)) {
            @unlink($path);
            }
            $image = $request->file('image');
            $profile_image = time() . '.' . $image->getClientOriginalExtension();
            $img = Image::make($image->getRealPath());
            $img->stream(); // <-- Key point
            Storage::disk('public')->put('profile/' . $profile_image, $img, 'public');
        }else{
            $profile_image = $request->hidden_image;
        }

        // dd($site_logo);
        $user = User::updateOrCreate(
            [
                'id' => $request->user_id,
            ],
            [
                'name'    => $request->name,
                'mobile_no'    => $request->mobile_no,
                'image'    =>  $profile_image,
            ]
        );
        $user->save();

        if (!empty($request->user_id)) {
            $message = "Record Update successfully";
        } else {
            $message = "Record Save successfully";
        }

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => $message,
            "data"            => $user
        ]);
    }

    public function change_password(Request $request){

        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
            'confirm_password' => 'required|same:new_password',
        ]);

        $check = Hash::check($request->current_password, auth()->user()->password);

        if(!$check){
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => "Current password does not match",
                "data"            => []
            ]);
        }

        if ($validator->fails()) {
            return Response::json([
                "code" => 200,
                "response_status" => "error",
                "message"         => $validator->errors()->first(),
                "data"            => []
            ]);
        }

        User::find(auth()->user()->id)->update(['password'=>bcrypt($request->new_password)]);
        $msg = "Password change sucessfully";

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         =>  $msg,
        ]);
    }
}
