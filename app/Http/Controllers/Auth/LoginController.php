<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class LoginController extends Controller
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
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    function login() {
        return view('auth.login');
    }
     public function loginProcess(Request $request){

        $credentials =  $request->only('email','password');
        $validate = Validator::make($credentials,[
            'email'=>'required|email',
            'password'=>'required'
        ]);

        if($validate->fails()){
            return back()->withErrors($validate)->withInput();
        }

        if(Auth::attempt($credentials)){
            return redirect()->intended('/')->with('success','Successfully Login');
        }
        return redirect('login')->withInput()->withErrors(['login_message'=>'Email atau Password Salah !']);
      }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect('/login')->with('success', 'Successfully logged out');
    }

}
