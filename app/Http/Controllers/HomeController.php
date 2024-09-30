<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        if($user->role_type == 1){
            return view('admin.home', compact('user'));
        }else{
            return view('home');
        }
    }
    public function viewProfile(Request $request)
    {
        $user = Auth::user();
        if ($user){
            return view('profile', compact('user'));
        }else{
            return redirect()->route('login');
        }
    }
    public function updateProfile(Request $request)
    {
        $user = User::find(auth()->user()->id);
        $validatedData = Validator::make($request->all(),[
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => [
            function ($attribute, $value, $fail) use ($request)
            {
                if (!empty($value)){
                    if (strlen($value) < 8){
                        $fail('The password must be at least 8 characters.');
                    }elseif ($value !== $request->input('password_confirmation')){
                        $fail('The password confirmation does not match.');
                    }
                }
            },
        ],
        ]);

        if ($validatedData->fails()){
            $result = ['status' => false,'errors' => $validatedData->errors()];
            return response()->json($result);
        }else{
            $user->email = $request->email;
            if(!empty($request->password)){
                $user->password = Hash::make($request->password);
            }

            if($user->save()){
                $result = ['status' => true, 'message' => 'Profile update successfully.', 'data' => $user];
            }else{
                $result = ['status' => false, 'message' => 'Profile update fail!', 'data' => []];
            }
            return response()->json($result);
        }
    }
}
