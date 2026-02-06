<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserApprovalMail;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function userlist(Request $request)
    {
        /** @var User $loginUser */
        $loginUser = Auth::user();
        if ($request->ajax()) {
            $data = User::where("id", "!=", $loginUser->id);

            // Hide Super Admins from regular Admins
            if (!$loginUser->isSuperAdmin()) {
                $data->where('role_type', '!=', User::ROLE_SUPER_ADMIN);
            }

            $data->orderBy('id', 'desc');

            return DataTables::of($data)
                ->editColumn('blockStatus', function ($row) use ($loginUser) {
                    if ($row->is_block == 0) {
                        $approve_status = '<div><input type="checkbox" id="switch03" data-switch="success"/><label for="switch03" data-on-label="Yes" data-off-label="No" class="mb-0 d-block"></label></div>';
                    } elseif ($row->is_block == 1) {
                        $approve_status = '<div><input type="checkbox" id="switch01" checked data-switch="success"/><label for="switch01" data-on-label="Yes" data-off-label="No" class="mb-0 d-block"></label></div>';
                    }
                    return $approve_status;
                })
                ->editColumn('approve', function ($row) use ($loginUser) {

                    $selected1 = '';
                    $selected2 = '';

                    if ($row->is_approve == 0) {
                        $selected1 = 'selected';
                        $approve_status = '<select name="approval_status" class="form-control approval_status" data-id="' . $row->id . '"><option value="0" ' . $selected1 . '>Pending</option><option value="1">Approve</option></select>';
                    } elseif ($row->is_approve == 1) {
                        $selected2 = 'selected';
                        $approve_status = '<center><span class="badge badge-success-lighten" style="padding:10px;">Approved</span></center>';
                    }
                    return $approve_status;
                })

                ->addColumn('action', function ($data) {
                    return '<center><a href="javascript:void(0);" data-toggle="modal" data-target="#edit-modal" id="edituser" class="btn btn-sm btn-primary mr-1 edit-user" data-id="' . $data->id . '" title="Edit"><i class="mdi mdi-pencil"></i></a></a><a href="javascript:void(0);" class="btn btn-sm btn-danger mr-1 delete-user" data-id="' . $data->id . '" title="Delete"><i class="mdi mdi-delete"></i></a></center>';
                })

                ->rawColumns(['approve', 'action', 'blockStatus'])
                ->toJson();
        }
        return view('admin.userlist');
    }

    public function userdetail(Request $request)
    {
        $user = User::find($request->id);

        $temp = [];
        $temp['user'] = $user;
        $result = ['status' => true, 'message' => '', 'data' => $temp, 'rolesMap' => User::ROLE_LABELS];
        return response()->json($result);
    }

    public function userupdate(Request $request)
    {
        $user = User::find($request->id);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->id],
            'password' => 'sometimes|nullable|min:8|confirmed',
            'nickname' => 'required|string|max:255|unique:users,nickname,' . $request->id,
        ]);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
        } else {
            $user->nickname = $request->input('nickname');
            $user->email = $request->input('email');

            /** @var User $currentUser */
            $currentUser = Auth::user();

            if ($request->has('role_type')) {
                $newRole = (int) $request->input('role_type');
                if ($newRole !== (int)$user->role_type) {
                    if (!$currentUser->canAssignAdminRoles()) {
                        return response()->json(['status' => false, 'message' => 'You do not have permission to change roles.', 'data' => []]);
                    }
                    if ($newRole === User::ROLE_SUPER_ADMIN) {
                        return response()->json(['status' => false, 'message' => 'Assigning Super Admin role is not allowed.', 'data' => []]);
                    }
                    $user->role_type = $newRole;
                }
            }

            $user->is_suggestable = $request->has('is_suggestable') ? 1 : 0;
            if ($request->input('password')) {
                $user->password = Hash::make($request->input('password'));
            }

            if ($user->save()) {
                $result = ['status' => true, 'message' => 'User update successfully.', 'data' => []];
            } else {
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
        if ($user) {
            $user->is_approve = $request->status;
            $user->save();

            Mail::to($user->email)->send(new UserApprovalMail($user));
            $result = ['status' => true, 'message' => 'Status changed successfully', 'data' => []];
        } else {
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
            $result = ['status' => true, 'message' => 'User ' . $message . ' successfully.'];
        } else {
            $result = ['status' => false, 'message' => 'Invalid request.'];
        }
        return response()->json($result);
    }

    public function userSuggestableUpdate(Request $request)
    {
        $id = $request->id;
        $status = $request->is_suggestable;
        $message = $status ? "enabled" : "disabled";
        $user = User::where('id', $id)->update(['is_suggestable' => $status]);
        if ($user) {
            $result = ['status' => true, 'message' => 'User suggestion ' . $message . ' successfully.'];
        } else {
            $result = ['status' => false, 'message' => 'Invalid request.'];
        }
        return response()->json($result);
    }

    public function userRoleUpdate(Request $request)
    {
        /** @var User|null $currentUser */
        $currentUser = Auth::user();

        if (!$currentUser->canAssignAdminRoles()) {
            return response()->json(['status' => false, 'message' => 'You do not have permission to change roles.']);
        }

        $id = $request->id;
        $newRole = (int) $request->role_type;

        if ($newRole === User::ROLE_SUPER_ADMIN) {
            return response()->json(['status' => false, 'message' => 'Assigning Super Admin role is not allowed.']);
        }

        if ($id == Auth::id()) {
            return response()->json(['status' => false, 'message' => 'You cannot change your own role.']);
        }

        if (!array_key_exists($newRole, User::ROLE_LABELS)) {
            return response()->json(['status' => false, 'message' => 'Invalid role type.']);
        }

        $user = User::find($id);
        if (!$user) {
            return response()->json(['status' => false, 'message' => 'User not found.']);
        }

        $user->role_type = $newRole;
        $user->save();

        $roleLabel = User::ROLE_LABELS[$newRole];
        return response()->json(['status' => true, 'message' => "User role updated to {$roleLabel} successfully."]);
    }
}
