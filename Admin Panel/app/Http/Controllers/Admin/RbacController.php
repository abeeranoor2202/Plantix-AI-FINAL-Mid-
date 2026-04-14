<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\Admin\RbacService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

/**
 * RbacController — Admin Panel Role & Permission Management
 *
 * Thin HTTP layer: validates input, delegates to RbacService for all
 * business logic, and returns views or redirects.
 *
 * Routes are declared in routes/panels/admin.php under:
 *   /admin/role/*         — role CRUD
 *   /admin/permissions/*  — permission CRUD + role sync
 */
class RbacController extends Controller
{
    public function __construct(
        private readonly RbacService $rbac
    ) {}

    // ──────────────────────────────────────────────────────────────────────────
    // ROLE LISTING
    // ──────────────────────────────────────────────────────────────────────────

    public function index(): View
    {
        $roles = $this->rbac->allRoles(withPermissions: true);

        return view('admin.rbac.roles.index', compact('roles'));
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROLE CREATE / STORE
    // ──────────────────────────────────────────────────────────────────────────

    public function save(): View
    {
        $permissions = $this->rbac->allPermissions(grouped: true);

        return view('admin.rbac.roles.save', compact('permissions'));
    }

    public function editPermission(int $id): View
    {
        $permission = \App\Models\Permission::withCount('roles')->findOrFail($id);

        $modules = $this->permissionModules();
        if ($permission->module && ! in_array($permission->module, $modules, true)) {
            $modules[] = $permission->module;
        }

        return view('admin.rbac.permissions.edit', [
            'permission' => $permission,
            'groups' => $this->permissionGroups(),
            'modules' => $modules,
            'actions' => $this->permissionActions(),
        ]);
    }

    public function createPermission(): View
    {
        return view('admin.rbac.permissions.create', [
            'groups' => $this->permissionGroups(),
            'modules' => $this->permissionModules(),
            'actions' => $this->permissionActions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'role_name'     => 'required|string|max:100|unique:role,role_name',
            'is_active'     => 'boolean',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $role = $this->rbac->createRole($data);

        if (! empty($data['permissions'])) {
            $this->rbac->syncRolePermissions($role->id, $data['permissions']);
        }

        return redirect()->route('admin.role.index')
            ->with('success', "Role \"{$role->role_name}\" created successfully.");
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROLE EDIT / UPDATE
    // ──────────────────────────────────────────────────────────────────────────

    public function edit(int $id): View
    {
        $role            = $this->rbac->findRole($id);
        $permissions     = $this->rbac->allPermissions(grouped: true);
        $assignedIds     = $this->rbac->rolePermissionIds($id);
        $usersCount      = \App\Models\User::where('role_id', $id)->count();

        return view('admin.rbac.roles.edit', compact('role', 'permissions', 'assignedIds', 'usersCount'));
    }

    public function update(Request $request, int $id): RedirectResponse
    {
        $data = $request->validate([
            'role_name'     => "required|string|max:100|unique:role,role_name,{$id}",
            'is_active'     => 'boolean',
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $this->rbac->updateRole($id, $data);
        $this->rbac->syncRolePermissions($id, $data['permissions'] ?? []);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role updated successfully.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // ROLE DELETE
    // ──────────────────────────────────────────────────────────────────────────

    public function delete(int $id): RedirectResponse
    {
        $this->rbac->deleteRole($id);

        return redirect()->route('admin.role.index')
            ->with('success', 'Role deleted. Affected staff users have been unassigned.');
    }

    // ──────────────────────────────────────────────────────────────────────────
    // PERMISSION MANAGEMENT
    // ──────────────────────────────────────────────────────────────────────────

    public function permissions(): View
    {
        // Flat collection with roles count for the table
        $permissions = \App\Models\Permission::withCount('roles')
            ->orderBy('group')
            ->orderBy('display_name')
            ->get();

        // Unique group names for the filter dropdown and datalist
        $groups = $permissions->pluck('group')->unique()->sort()->values()->toArray();
        $modules = $permissions->pluck('module')->filter()->unique()->sort()->values()->toArray();

        return view('admin.rbac.permissions.index', [
            'permissions' => $permissions,
            'groups' => $groups,
            'modules' => $modules,
        ]);
    }

    public function storePermission(Request $request): RedirectResponse
    {
        $actionKeys = array_keys($this->permissionActions());

        $data = Validator::make($request->all(), [
            'system_key'       => 'required|string|max:100|unique:permissions,name',
            'module'           => ['required', 'string', 'max:100', Rule::in($this->permissionModules())],
            'action'           => ['required', 'string', Rule::in($actionKeys)],
            'group'            => ['required', 'string', 'max:100', Rule::in($this->permissionGroups())],
            'human_name'       => ['required', 'string', 'max:150', 'regex:/^Can\s+[a-z]+(?:\s+[a-z]+)+$/i'],
            'description'      => 'required|string|max:1000',
            'is_active'        => 'nullable|boolean',
            'advanced_mode'    => 'nullable|boolean',
        ])->after(function ($validator) use ($request): void {
            $existsModuleAction = \App\Models\Permission::query()
                ->where('module', $request->input('module'))
                ->where('action', $request->input('action'))
                ->exists();

            if ($existsModuleAction) {
                $validator->errors()->add('module', 'The selected module and action already exist.');
            }

            $humanDuplicate = \App\Models\Permission::query()
                ->where('module', $request->input('module'))
                ->whereRaw('LOWER(display_name) = ?', [Str::lower((string) $request->input('human_name'))])
                ->exists();

            if ($humanDuplicate) {
                $validator->errors()->add('human_name', 'This permission label already exists in the selected module.');
            }
        })->validate();

        if (! filter_var($data['advanced_mode'] ?? false, FILTER_VALIDATE_BOOL)) {
            $data['human_name'] = self::buildHumanPermissionLabel($data['module'], $data['action']);
            $data['system_key'] = self::buildSystemKey($data['module'], $data['action']);
        }

        $this->rbac->createPermission([
            'name'         => $data['system_key'],
            'slug'         => $data['system_key'],
            'group'        => $data['group'],
            'display_name' => $data['human_name'],
            'module'       => $data['module'],
            'action'       => $data['action'],
            'description'  => $data['description'],
            'is_active'    => (bool) ($data['is_active'] ?? true),
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', "Permission \"{$data['human_name']}\" created.");
    }

    public function updatePermission(Request $request, int $id): RedirectResponse
    {
        $permission = \App\Models\Permission::findOrFail($id);
        $actionKeys = array_keys($this->permissionActions());

        $data = Validator::make($request->all(), [
            'system_key'    => [
                'required',
                'string',
                'max:100',
                Rule::unique('permissions', 'name')->ignore($id),
            ],
            'module'        => ['required', 'string', 'max:100', Rule::in($this->permissionModules())],
            'action'        => ['required', 'string', Rule::in($actionKeys)],
            'group'         => ['required', 'string', 'max:100', Rule::in($this->permissionGroups())],
            'human_name'    => ['required', 'string', 'max:150', 'regex:/^Can\s+[a-z]+(?:\s+[a-z]+)+$/i'],
            'description'   => 'required|string|max:1000',
            'is_active'     => 'nullable|boolean',
            'advanced_mode' => 'nullable|boolean',
        ])->after(function ($validator) use ($request, $id): void {
            $existsModuleAction = \App\Models\Permission::query()
                ->where('module', $request->input('module'))
                ->where('action', $request->input('action'))
                ->where('id', '!=', $id)
                ->exists();

            if ($existsModuleAction) {
                $validator->errors()->add('module', 'The selected module and action already exist.');
            }

            $humanDuplicate = \App\Models\Permission::query()
                ->where('module', $request->input('module'))
                ->where('id', '!=', $id)
                ->whereRaw('LOWER(display_name) = ?', [Str::lower((string) $request->input('human_name'))])
                ->exists();

            if ($humanDuplicate) {
                $validator->errors()->add('human_name', 'This permission label already exists in the selected module.');
            }
        })->validate();

        if (! filter_var($data['advanced_mode'] ?? false, FILTER_VALIDATE_BOOL)) {
            $data['human_name'] = self::buildHumanPermissionLabel($data['module'], $data['action']);
            $data['system_key'] = self::buildSystemKey($data['module'], $data['action']);
        }

        $this->rbac->updatePermission($id, array_filter([
            'name'         => $data['system_key'],
            'slug'         => $data['system_key'],
            'group'        => $data['group'],
            'display_name' => $data['human_name'],
            'module'       => $data['module'],
            'action'       => $data['action'],
            'description'  => $data['description'],
            'is_active'    => (bool) ($data['is_active'] ?? false),
        ], fn ($value) => $value !== null));

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission updated.');
    }

    public function togglePermissionStatus(int $id): RedirectResponse
    {
        if (! Schema::hasColumn('permissions', 'is_active')) {
            return redirect()->route('admin.permissions.index')
                ->with('error', 'Permission status toggle requires the latest RBAC migration (missing is_active column).');
        }

        $permission = \App\Models\Permission::findOrFail($id);
        $nextStatus = ! (bool) $permission->is_active;

        $this->rbac->updatePermission($id, [
            'is_active' => $nextStatus,
        ]);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission status updated.');
    }

    public function destroyPermission(int $id): RedirectResponse
    {
        $this->rbac->destroyPermission($id);

        return redirect()->route('admin.permissions.index')
            ->with('success', 'Permission deleted and detached from all roles.');
    }

    /**
     * Sync the full permission set for a role (AJAX-friendly).
     * Called from the role edit form checkboxes.
     */
    public function syncRolePermissions(Request $request, int $roleId): RedirectResponse
    {
        $data = $request->validate([
            'permissions'   => 'nullable|array',
            'permissions.*' => 'integer|exists:permissions,id',
        ]);

        $this->rbac->syncRolePermissions($roleId, $data['permissions'] ?? []);

        return redirect()->route('admin.role.edit', $roleId)
            ->with('success', 'Permissions updated.');
    }

    private function permissionGroups(): array
    {
        return [
            'User Management',
            'Product Catalog',
            'Forum Moderation',
            'Appointments',
            'Payments',
        ];
    }

    private function permissionModules(): array
    {
        return ['Users', 'Products', 'Forum', 'Appointments', 'Payments'];
    }

    /**
     * @return array<string, string>
     */
    private function permissionActions(): array
    {
        return [
            'view' => 'View',
            'create' => 'Create',
            'edit' => 'Edit',
            'delete' => 'Delete',
            'manage' => 'Manage',
        ];
    }

    public static function buildHumanPermissionLabel(string $module, string $action): string
    {
        $modulePhrase = Str::of($module)
            ->lower()
            ->replace(['_', '.'], ' ')
            ->squish()
            ->toString();

        $verb = match (Str::lower($action)) {
            'view' => 'view',
            'create' => 'create',
            'edit' => 'edit',
            'delete' => 'delete',
            default => 'manage',
        };

        return 'Can ' . $verb . ' ' . $modulePhrase;
    }

    public static function buildSystemKey(string $module, string $action): string
    {
        $keyParts = [
            'admin',
            Str::of($module)->lower()->replace([' ', '_'], '.')->replace('..', '.')->trim('.'),
            Str::of($action)->lower()->replace([' ', '_'], '.')->trim('.'),
        ];

        return collect($keyParts)
            ->map(fn ($part) => trim((string) $part, '.'))
            ->filter()
            ->implode('.');
    }
}
