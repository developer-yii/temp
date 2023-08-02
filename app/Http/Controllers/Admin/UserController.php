<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Message;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function userlist(Request $request)
    {
        $loginUser = Auth::user();
        if($request->ajax())
        {            
            $data = User::where("id", "!=", Auth::user()->id);
            
            return DataTables::of($data)
                                ->addColumn('action', function ($data) {
                return '<a href="javascript:void(0);" data-toggle="modal" data-target="#edit-modal" id="edituser" class="btn btn-sm btn-primary mr-1 edit-user"  data-id="'.$data->id.'"><i class="mdi mdi-pencil"></i></a></a><a href="javascript:void(0);" class="btn btn-sm btn-primary mr-1 delete-user"  data-id="'.$data->id.'"><i class="mdi mdi-delete"></i></a>';
            })           
           
            ->rawColumns(['action'])
            ->toJson();              
        }
        return view('admin.userlist');              
    }

    public function userdetail(Request $request)
    {
        $user = User::find($request->id); 

        $rolesMap = [
                1 => 'Admin',
                2 => 'User',                
            ];

        $temp=[];
        $temp['user'] = $user;        
        $result = ['status' => true, 'message' => '', 'data' => $temp, '$rolesMap' => $rolesMap];
        return response()->json($result);
    }

    public function userupdate(Request $request)
    {
        $user = User::find($request->id);

        $validator = Validator::make($request->all(), [
            'email' => ['required','string','email','max:255','unique:users,email,' . $request->id],
            'password' => 'sometimes|nullable|min:8|confirmed'
        ]);
              
        if($validator->fails())
        {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];    
            
        }
        else
        {   
            $user->email = $request->input('email');
            $user->role_type = $request->input('role_type');            
            if ($request->input('password')) 
            {
                $user->password = Hash::make($request->input('password'));
            }
            
            if($user->save())
            {
                $result = ['status' => true, 'message' => 'User update successfully.', 'data' => []];
                
            }
            else
            {
                $result = ['status' => false, 'message' => 'User update fail!', 'data' => []];
                
            }
        }

        return response()->json($result);

    }

    public function userdelete(Request $request)
    {        
        $user = User::find($request->id);
        //$user->deleted_at = null;
        $user->delete();
        $msg = "Records Delete successfully";
        $result = ["status" => true, "message" => $msg];
        return response()->json($result);
    }
}
