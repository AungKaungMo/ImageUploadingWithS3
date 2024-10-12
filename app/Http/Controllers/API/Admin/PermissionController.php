<?php

namespace App\Http\Controllers\API\Admin;

use App\ApiResponse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
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

        $query = Permission::select(['id', 'name', 'status', 'is_deleted'])
            ->orderBy('id', 'desc');

        if ($page && $perPage) {
            $permissions = $query->paginate($perPage, $page);
        } else {
            $permissions = $query->get();
        }

        return ApiResponse::success($permissions, 'Get all premissions successful', 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name',
        ]);

        if ($validator->fails()) {
            return ApiResponse::error(null, 'Field validation error', 400);
        }

        Permission::create([
            'name' => $request->name,
            'guard_name' => "api",
        ]);

        return ApiResponse::success('Permission created successfully.', 201);
    }

    public function show(string $id)
    {
        $permission = Permission::with('roles:id,name')->findOrFail($id);

        if (!empty($permission)) {
            return ApiResponse::success($permission, 'Get Permission detail is Successful', 200);
        } else {
            return ApiResponse::error(null, "Permission not found with your provided id", 404);
        }
    }

    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|unique:permissions,name,' . $id,
        ]);

        if ($validator->fails()) {
            return ApiResponse::error($validator->errors(), 'Field validation error.', 400);
        }

        $permission = Permission::findOrFail($id);

        if (!empty($permission)) {
            $permission->name = $request->has('name') ? $request->name : $permission->name;
            $permission->status = $request->has('status') ? $request->status : $permission->status;
            $permission->syncRoles($request->input('roles'));
            $permission->save();
        } else {
            return ApiResponse::error(null, "Permission not found with your provided id", 404);
        }

        return ApiResponse::success('Permission updated successfully.', 200);
    }

    public function destroy($id)
    {
        $permission = Permission::find($id);
        if ($permission) {
            $permission->update([
                'is_deleted' => 1
            ]);
            return ApiResponse::success(null, 'User deleted successfully', 200);
        } else {
            return ApiResponse::error(null, 'User not found with your provided id', 404);
        }
    }
}
