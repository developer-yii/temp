<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

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
    public function validateLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|exists:users,email',
            'password' => 'required'
        ]);
    }

    public function login(Request $request)
    {
        $this->validateLogin($request);

        if ($this->hasTooManyLoginAttempts($request)) 
        {
            $this->fireLockoutEvent($request);
            return $this->sendLockoutResponse($request);
        }

        $user = User::where('email', $request->email)->first();
        if($user->role_type == 2 && $user->is_approve == 0) 
        {
            return redirect('login')->withErrors(['approve' => 'Your account is not approved.'])->withInput();
        }
        else
        {
            if ($user && Hash::check($request->password, $user->password)) 
            {
                $redirectRoute = ($user->role_type == '1') ? 'admin.home' : 'home';
                Auth::login($user);           
                return redirect()->intended(route($redirectRoute));
            }
            return redirect('login')->withErrors(['password' => 'The Password is wrong.'])->withInput();
        }
    }

    // need 
    // if role_type=1 then redirect route=admin.home 
    // if role_type!=1 and is_approve=0 then need to display error message your account is not approved
    // if role_type!=1 and is_approve=1 then need to redirect route= home
    public function logout(Request $request) 
    {
        Auth::logout();
        Session::forget('url.intended');
        return redirect('login')->with('message','You have been successfully logout!');
    }
}
