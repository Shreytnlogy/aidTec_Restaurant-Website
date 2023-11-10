<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Session;
use App\Models\User;
use App\Models\UserVerify;
use Hash;
use Mail;
use Str;


class AuthController extends Controller
{
    public function index()
    {
        return view('home');
    }
    public function login()
    {
        return view('auth.login');
    }

    public function register()
    {
        return view('auth.register');
    }
    public function about()
    {
        return view('about');
    }
    public function service()
    {
        return view('service');
    }
    public function menu()
    {
        return view('menu');
    }
    public function booking()
    {
        return view('booking');
    }
    public function contact()
    {
        return view('contact');
    }
   /* public function saveBooking(Request $request)
    {
       // dd($request->all());

        $request->validate(
            [
                'name'=>'required',
                'email'=>'required|email',
                'date_and_time'=>'required',
                'no_of_people'=>'required',
                'special_request'=>'required',
            ]
            );
    
    
            $booking = new Booking();
            $booking->name = $request->name;
            $booking->email = $request->email;
            $booking->date_and_time = $request->date_and_time;
            $booking->no_of_people = $request->no_of_people;
            $booking->service_request = $request->special_request;
            $booking->save();
           //return redirect()->back()->with('success','you have booked successfully.');
    }   */
    public function saveUser(Request $request)
    {
        //dd($request->all());

        $request->validate(
            [
                 'name'=>'required',
                 'email'=>'required|email|max:50|unique:users',
                 'password'=>'required|min:6|same:confirm_password',
                // 'confirm_password'=>"required|same:password",
            ] 
        );
        $data = $request->all();
        $createuser = $this->create($data);
        $token = Str::random(64);
        UserVerify::create([
            'user_id' => $createuser->id,
            'token' => $token,
            
        ]);

        Mail::send('emails.activationEmail',['token'=>$token],function($message) use($request){
            $message->to($request->email);
            $message->subject('Activation Account Email from ShopserviceCrud');
        });

        return redirect('login')->withSuccess('you have registered successfully. Please Activate your account before login');
       
    }
    public function create(array $data)
    {
        return user::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => $data['password'],
        ]);
    }
    public function postLogin(Request $request)
    {
        //dd($request->all());

        $request->validate(
            [
                'email'=>'required|email',
                'password'=>'required',
                 
             ]);
             $checkLoginCredentials = $request->only('email','password');
             if(Auth::attempt($checkLoginCredentials)){
                 return redirect('myprofile')->withSuccess('Loggedin successfully.');
             }
             return redirect('login')->withSuccess('please provide correct login credentials');
    
    }
    public function myprofile()
    {
        if(Auth::check()){
            $user = Auth::user();
            return view('auth.myprofile',compact('user'));
        }
        return redirect('login')->withSuccess('Not authorized to access this page without login.');
    }
    public function logout()
    {
        Session::flush();
        Auth::logout();
        return redirect('login')->with('success', 'You are logout successfully-shreya.');
    }
    public function VerifyAccount($token)
    {
        $verifyUser = UserVerify::where('token',$token)->first();
        $message = "Your detail is not registered with us.";
        if(!is_null($verifyUser)){
            $user = $verifyUser->user;
            if(!$user->is_active) {
                $verifyUser->user->is_active=1;
                $verifyUser->user->save();
                $message = "Your account is activated successfully.";
            } else {
                $message = "Your account is already activated.";
            }
        }
        return redirect('login')->with('success',$message);
    }
}
