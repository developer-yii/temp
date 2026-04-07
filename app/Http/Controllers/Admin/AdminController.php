<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        return view('admin.home', compact('user'));
    }
    public function profile(Request $request)
    {
        /** @var User $user */
        $user = Auth::user();

        $rolesMap = User::ROLE_LABELS;

        // Remove Super Admin from the map unless the current user is one
        if (!$user->isSuperAdmin()) {
            unset($rolesMap[User::ROLE_SUPER_ADMIN]);
        }

        return view('admin.profile', ["user" => $user, "rolesMap" => $rolesMap]);
    }


    public function profileupdate(Request $request)
    {
        /** @var User $user */
        $user = User::find($request->id);

        $validator = Validator::make($request->all(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $request->id],
            'password' => 'sometimes|nullable|min:8|confirmed'
        ]);

        if ($validator->fails()) {
            $result = ['status' => false, 'message' => $validator->errors(), 'data' => []];
        } else {
            $user->email = $request->input('email');

            $newRole = (int) $request->input('role');
            // Prevent assigning Super Admin role unless they are already one and keeping it
            if ($newRole === User::ROLE_SUPER_ADMIN && !$user->isSuperAdmin()) {
                return response()->json(['status' => false, 'message' => 'Assigning Super Admin role is not allowed.', 'data' => []]);
            }
            $user->role_type = $newRole;

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
}
