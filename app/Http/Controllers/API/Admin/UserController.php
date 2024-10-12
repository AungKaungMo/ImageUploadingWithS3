<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\ApiResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:user-list', ['only' => ['index']]);
        $this->middleware('permission:user-create', ['only' => ['store']]);
        $this->middleware('permission:user-edit', ['only' => ['update']]);
        $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $query = User::select(['id', 'name', 'email', 'phone', 'status', 'is_deleted'])
            ->orderBy('id', 'desc');

        if ($page && $perPage) {
            $paginatedUsers = $query->paginate($perPage, $page);
        } else {
            $paginatedUsers = $query->get();
        }

        if ($paginatedUsers) {

            $paginatedUsers->transform(function ($user) {
                $child_result['id'] = $user->id;
                $child_result['name'] = $user->name;
                $child_result['email'] = $user->email;
                $child_result['status'] = $user->status;
                $child_result['is_deleted'] = $user->is_deleted;
                $child_result['permissions'] = $user->getPermissionsViaRoles()->pluck('name');
                $child_result['roles'] = $user->getRoleNames();

                return $child_result;
            });

            return ApiResponse::success($paginatedUsers, 'Get all users successful', 200);
        }

        return ApiResponse::error(null, 'User data is empty', 204);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required',
            'password' => 'required|same:confirm-password',
            // 'roles' => 'required'
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(null, "Field validation Error", 400);
        }

        $data = $request->only('name', 'email', 'password', 'phone');

        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        if (!empty($request->roles)) {
            $user->assignRole($request->input('roles'));
        }

        return ApiResponse::success('User created successfully.', 200);
    }

    public function show($id)
    {
        $user = User::with('roles:id,name')->findOrFail($id);
        $user['permissions'] = $user->getPermissionsViaRoles()->select(['id', 'name']);

        if (!empty($user)) {
            return ApiResponse::success($user, 'Get User detail is Successful', 200);
        } else {
            return ApiResponse::error(null, "User not found with your provided id", 404);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!empty($user)) {
            $user->name = $request->has('name') ? $request->name : $user->name;
            $user->email = $request->has('email') ? $request->email : $user->email;
            $user->phone = $request->has('phone') ? $request->phone : $user->phone;
            $user->type = $request->has('type') ? $request->type : $user->type;
            $user->status = 1;
            $user->is_deleted = 0;
            $user->save();

            if (!empty($request->roles)) {
                $user->syncRoles($request->roles);
            }

            return ApiResponse::success('User updated successfully.', 200);
        }

        return ApiResponse::error(null, 'User not found with your provided ID.', 404);
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if ($user) {
            $user->update([
                'is_deleted' => 1
            ]);
            return ApiResponse::success(null, 'User deleted successfully', 200);
        } else {
            return ApiResponse::error(null, 'User not found with your provided id', 404);
        }
    }

    public function changePassword(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required',
            'new_password' => 'required',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(null, 'Field validation error', 400);
        }

        $user = User::find($id);

        if (!$user) {
            return ApiResponse::error(null, 'User not found with your provided id', 404);
        }

        if (!Hash::check($request->current_password, $user->password)) {
            return ApiResponse::error(null, 'Your current password is not valid', 401);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return ApiResponse::success(null, 'Change Password is successful', 200);
    }
}
