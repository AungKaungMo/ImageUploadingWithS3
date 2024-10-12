<?php

namespace App\Http\Controllers\API\Admin;

use App\ApiResponse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
// use App\Http\Middleware\CustomValidation;

class RoleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:role-list', ['only' => ['index']]);
        $this->middleware('permission:role-create', ['only' => ['store']]);
        $this->middleware('permission:role-edit', ['only' => ['update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    public function index(Request $request)
    {
        $page = $request->query('page');
        $perPage = $request->query('per_page');

        $query = Role::select(['id', 'name', 'status', 'is_deleted'])
            ->orderBy('id', 'desc');

        if ($page && $perPage) {
            $roles = $query->paginate($perPage, $page);
        } else {
            $roles = $query->get();
        }

        return ApiResponse::success($roles, 'Success', 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:roles,name',
            // 'permissions' => 'required|array',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(null, "Field validation error", 400);
        }

        $data = $request->only('name', 'permissions');
        $data = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'api'
        ]);

        if (!empty($request->permissions)) {
            $data->syncPermissions($request->input('permissions'));
        }

        return ApiResponse::success('Role create successful', 200);
    }

    public function show($id)
    {
        $data['permissions'] = Permission::latest()->pluck('name');
        $data['role'] = Role::with('permissions:id,name')->findOrFail($id);

        if (!empty($data)) {
            return ApiResponse::success($data, 'Get role detail successul', 200);
        } else {
            return ApiResponse::error(null, 'Role not found with your provided id', 404);
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required',
        ]);

        $role = Role::findOrFail($id);

        if ($role) {
            $role->update([
                'name' => $request->name,
                'status' => 1,
                'is_deleted' > 0
            ]);

            if ($request->permissions) {
                $role->syncPermissions($request->permissions);
            }

            return ApiResponse::success('Successfully updated', 200);
        } else {
            return ApiResponse::error(null, 'Role not found with your provided id', 404);
        }
    }

    public function destroy($id)
    {
        $role = Role::find($id);
        if ($role) {
            $role->update([
                'is_deleted' => 1
            ]);
            return ApiResponse::success(null, 'Role deleted successfully', 200);
        } else {
            return ApiResponse::error(null, 'Role not found with your provided id', 404);
        }
    }
}
