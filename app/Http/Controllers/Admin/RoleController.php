<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions', 'users')->orderBy('name')->paginate(20);

        return view('admin.roles.index', compact('roles'));
    }

    public function create()
    {
        return view('admin.roles.form', [
            'role' => new Role(),
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
        ]);

        $role = Role::create(['name' => $data['name'], 'guard_name' => 'web']);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role created.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => $this->permissionGroups(),
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$role->id],
        ]);

        // Guard: never allow removing/renaming the Super Admin role away from
        // itself in a way that could lock out the currently logged-in admin.
        $isOwnSuperAdmin = $role->name === 'Super Admin' && auth()->user()->hasRole('Super Admin');

        if ($isOwnSuperAdmin) {
            return back()->with('error', 'The Super Admin role cannot be modified from here to prevent locking yourself out.');
        }

        $role->update(['name' => $data['name']]);
        $role->syncPermissions($request->input('permissions', []));

        return redirect()->route('admin.roles.index')->with('success', 'Role updated.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'Super Admin') {
            return back()->with('error', 'The Super Admin role cannot be deleted.');
        }

        if ($role->users()->exists()) {
            return back()->with('error', 'Cannot delete: this role is still assigned to one or more users.');
        }

        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', 'Role deleted.');
    }

    /** Group all permissions by their module prefix (before the dot) for the checkbox matrix. */
    private function permissionGroups()
    {
        return Permission::orderBy('name')->get()->groupBy(function (Permission $permission) {
            return Str::before($permission->name, '.');
        });
    }
}
