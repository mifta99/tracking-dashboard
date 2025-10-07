<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Puskesmas;
use App\Models\User;
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
    protected function username()
    {
        return 'username'; 
    }
     public function loginProcess(Request $request){

        $credentials =  $request->only('email','password');
        $validate = Validator::make($credentials,[
            'email'=>'required',
            'password'=>'required'
        ]);

        if($validate->fails()){

            return back()->withErrors($validate)->withInput();
        }

        if(Auth::attempt($credentials)){
            return redirect()->intended('/')->with('success','Successfully Login');
        }
            $existPuskesmasUser = User::where('email', $request->email)->first();
            $loginCheck = Puskesmas::select('puskesmas.*', 'puskesmas.id as puskesmas_id')
                ->join('districts', 'districts.id', '=', 'puskesmas.district_id')
                ->where(function($query) use ($request) {
                    $query->where('puskesmas.id', $request->email)
                        ->whereRaw('LOWER(puskesmas.name) = ?', [strtolower($request->password)]);
                })
                ->orWhere(function($query) use ($request) {
                    $query->where('puskesmas.id', $request->email)
                        ->whereRaw('LOWER(districts.name) = ?', [strtolower($request->password)]);
                })
                ->first();

            if($loginCheck){
                if(!$existPuskesmasUser){
                    $newUser = new \App\Models\User();
                    $newUser->name = 'Admin ' . $loginCheck->name;
                    $newUser->instansi = 'Puskesmas ' . $loginCheck->name;
                    $newUser->email = $loginCheck->id; 
                    $newUser->password = bcrypt(strtolower($loginCheck->name)); 
                    $newUser->role_id = 1; 
                    $newUser->puskesmas_id = $loginCheck->id;
                    $newUser->must_change_password = 1;
                    $newUser->save();
                }
                if(Auth::attempt(['email' => $request->email, 'password' => strtolower($loginCheck->name)])){
                    return redirect()->intended('/')->with('success','Successfully Login');
                }
                
                
            }
            $userByPuskesmasId = User::where('puskesmas_id', $request->email)->first();
            if($userByPuskesmasId){
                if(Auth::attempt(['email' => $userByPuskesmasId->email, 'password' => $request->password])){
                    return redirect()->intended('/')->with('success','Successfully Login');
                }
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

    public function resetPassword()
    {
        return view('auth.passwords.reset');
    }

}
