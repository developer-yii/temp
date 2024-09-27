<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserApprovalMail;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userlist(Request $request)
    {
        $loginUser = Auth::user();
        if($request->ajax())
        {
            $data = User::where("id", "!=", Auth::user()->id)->orderBy('id', 'desc');

            return DataTables::of($data)
                ->editColumn('blockStatus', function($row) use($loginUser) {
                    if ($row->is_block == 0){
                        $approve_status = '<div><input type="checkbox" id="switch03" data-switch="success"/><label for="switch03" data-on-label="Yes" data-off-label="No" class="mb-0 d-block"></label></div>';
                    }elseif ($row->is_block == 1){
                        $approve_status = '<div><input type="checkbox" id="switch01" checked data-switch="success"/><label for="switch01" data-on-label="Yes" data-off-label="No" class="mb-0 d-block"></label></div>';
                    }
                    return $approve_status;
                })
                ->editColumn('approve', function($row) use($loginUser) {

                    $selected1 = '';
                    $selected2 = '';

                    if ($row->is_approve == 0)
                    {
                        $selected1 = 'selected';
                        $approve_status = '<select name="approval_status" class="form-control approval_status" data-id="'.$row->id.'"><option value="0" '.$selected1.'>Pending</option><option value="1">Approve</option></select>';
                    }
                    elseif ($row->is_approve == 1)
                    {
                        $selected2 = 'selected';
                        $approve_status = '<center><span class="badge badge-success-lighten" style="padding:10px;">Approved</span></center>';
                    }
                    return $approve_status;
                })

                ->addColumn('action', function ($data) {
                return '<center><a href="javascript:void(0);" data-toggle="modal" data-target="#edit-modal" id="edituser" class="btn btn-sm btn-primary mr-1 edit-user" data-id="'.$data->id.'" title="Edit"><i class="mdi mdi-pencil"></i></a></a><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-user" data-id="'.$data->id.'" title="Delete"><i class="mdi mdi-delete"></i></a></center>';
            })

            ->rawColumns(['approve','action', 'blockStatus'])
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
        $user->delete();
        $msg = "Records Delete successfully";
        $result = ["status" => true, "message" => $msg];
        return response()->json($result);
    }
    public function approve_user(Request $request)
    {
        $user = User::find($request->id);
        if($user){
            $user->is_approve = $request->status;
            $user->save();

            Mail::to($user->email)->send(new UserApprovalMail($user));
            $result = ['status' => true, 'message' => 'Status changed successfully', 'data' => []];
        }else{
            $result = ['status' => false, 'message' => 'Something went wrong'];
        }
        return response()->json($result);
    }

    public function deleteMultipleUsers(Request $request)
    {
        $ids = $request->input('ids');
        $users = User::whereIn('id', $ids)->get();

        foreach ($users as $user) {
            $user->delete();
        }

        $msg = "Records Delete successfully";
        $result = ["status" => true, "message" => $msg];
        return response()->json($result);
    }

    public function userStatusUpdate(Request $request)
    {
        $id = $request->id;
        $status = $request->is_block;
        $message = $status ? "block" : "unblock";
        $user = User::where('id', $id)->update(['is_block' => $status]);
        if ($user) {
            $result = ['status' => true, 'message' => 'User '.$message.' successfully.'];
        } else {
            $result = ['status' => false, 'message' => 'Invalid request.'];
        }
        return response()->json($result);
    }

}
