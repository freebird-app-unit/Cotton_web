<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
// use Illuminate\Support\Facades\Auth;
use Auth;
use Validator;
use Response;
use App\Models\User;
use Mail;
use Carbon\Carbon;

class AdminLoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = '/dashboard';
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        if(!Auth::guard('web')->check()) {
            return view('auth.login');
        }
        $this->middleware('guest:web')->except('logout');
    }
    /**
     * Show the application’s login form.
     *
     * @return \Illuminate\Http\Response
     */
    public function showLoginForm()
    {
        if(!Auth::guard('web')->check()) {
            return view('auth.login');
        }else{
            return redirect('/dashboard');
        }
    }

    protected function guard() 
    {   
        return Auth::guard('web');
    }

    private function validator(Request $request)
    {
        //validation rules.
        $rules = [
            'email'    => 'required|email|exists:users|min:5|max:191',
            'password' => 'required|string|min:8|max:255',
        ];

        //custom validation error messages.
        $messages = [
            'email.exists' => 'These credentials do not match our records.',
        ];

        //validate the request.
        $request->validate($rules,$messages);
    }

    /**
     * Login the admin.
     * 
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function login(Request $request)
    {
    //    dd(bcrypt("admin@123"));
        // dd($request->all);
        if (Auth::guard('web')->attempt(['status' => '1','email' => $request->email, 'password' => $request->password])) {
            return Response::json([
                "code" => 200,
                "response_status" => "success",
                "message"         => "Login successfully",
                "data"            => []
            ]);    
        }else{
            return Response::json([
                "code" => 404,
                "response_status" => "error",
                "message"         => "Invalid Authentication",
                "data"            => []
            ]); 
        } 
        //Authentication failed...
        // return $this->loginFailed();
    }

    private function loginFailed() 
    {
        return redirect()
            ->back()
            ->withInput()
            ->with('error','Login failed, please try again!');
    }

    public function logout() 
    {
        Auth::guard('web')->logout();
        
        return redirect('/login')->with('status','Admin has been logged out!');
    }

    public function  forget_password(Request $request){

        $data['otp'] = rand(1000,9999);
        $data['email'] = $request->email;
        $current = Carbon::now();
        $otp_time = $current->toDateTimeString();   
        $msg = "";
        $user = User::where('email', $data['email'])->where('user_type','admin')->first();
        if ($user != null) {
            $save_otp = User::updateOrCreate(
                [
                    'email' => $data['email'],
                ],
                [
                    'otp'  => $data['otp'],
                    'otp_time' => $otp_time,
                    'email' =>  $data['email'],
                ]
                );
            $save_otp->save();
            
            Mail::send('forget_password', $data, function($message) use ($data)
            {
                $message->to($data['email']);
                $message->subject('Contact Details');
            });
            $msg = "Mail Sent Sucessfully.";
        } else {
            $msg = "Email is not registerd";
        }
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         => $msg,
            "data"            => $data
        ]);
    }

    public function conform_otp(Request $request){

       $entered_otp = $request->otp;
       $user = User::where('otp',$entered_otp)->first();
       if(!empty($user)){
            $otp = $user->otp;
            $otp_validity_date = $user->otp_time;
            $msg = "";
            if ($otp == $entered_otp) {
                
                $current = date("Y-m-d H:i:s");
                $diff    = strtotime($current) - strtotime($otp_validity_date);
                $days    = floor($diff / 86400);
                $hours   = floor(($diff - ($days * 86400)) / 3600);
                $minutes = floor(($diff - ($days * 86400) - ($hours * 3600)) / 60);
                if (($diff > 0) && ($minutes <= 1)) {
                    $msg = "Verification sucessfully" ;
                } else {
                    $msg = 'OTP expired';
                }
            } 
       } else {
        $msg = 'OTP does not match.';
        }   

        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         =>  $msg,
        ]);

    }

    public function change_password(Request $request){

        $email = $request->hidden_email;
        $new_password = $request->new_password;
        $confirm_password = $request->confirm_password;

        if($new_password == $confirm_password){
            $save_password = User::updateOrCreate(
                [
                    'email' => $email,
                ],
                [
                    'password'  => bcrypt($new_password),
                ]
                );
            $save_password->save();
            $msg = "password change sucessfully";
        } else {
            $msg = "password does not match";
        }
        
        return Response::json([
            "code" => 200,
            "response_status" => "success",
            "message"         =>  $msg,
        ]);
    }
}
