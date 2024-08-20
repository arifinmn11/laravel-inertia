<?php

namespace App\Http\Controllers\Cms;

use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as RequestValidate;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use App\Http\Libraries\ManaCms;
use App\Models\RoleMenu;
use App\Models\RoleModuleTask;

class RolesController extends Controller
{

    protected $manaCms;

    const MSG_ERROR_UNAUTHORIZED = 'Anda tidak memiliki akses untuk halaman Roles!';
    const MSG_ERROR_UNAUTHORIZED_CREATE = 'Anda tidak memiliki akses untuk Membuat Roles!';
    const MSG_ERROR_UNAUTHORIZED_UPDATE = 'Anda tidak memiliki akses untuk Ubah Roles!';
    const MSG_ERROR_UNAUTHORIZED_READ = 'Anda tidak memiliki akses untuk Melihat Roles!';
    const MSG_ERROR_UNAUTHORIZED_DELETE = 'Anda tidak memiliki akses untuk Hapus Roles!';
    const MSG_ERROR_UNAUTHORIZED_RESTORE = 'Anda tidak memiliki akses untuk Restore Roles!';
    const TABLE = 'roles';
    const INDEX = 'page';


    public function __construct(ManaCms $manaCms)
    {
        $this->manaCms = $manaCms;
    }

    public function index(): Response|RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, self::INDEX)) {
            return Redirect::route('dashboard')->with('error', self::MSG_ERROR_UNAUTHORIZED);
        }

        $limit = Request::get('limit', 10);
        $sortBy = Request::get('sortBy', null);

        return Inertia::render('Roles/Index', [
            'filters' => Request::all('search', 'trashed', 'limit'),
            'actions' => $this->manaCms->checkAccessAction(self::TABLE),
            'roles' => Role::filter(Request::only('search', 'trashed'))
                ->when($sortBy, function ($query) use ($sortBy) {
                    $sort = explode('|', $sortBy);
                    $sortKey = $sort[0];
                    $sort = $sort[1];
                    $query->orderBy($sortKey, $sort);
                })
                ->paginate($limit)
                ->withQueryString()
        ]);
    }

    public function create(): Response
    {

        if (!$this->manaCms->checkAccess(self::TABLE, 'create')) {
            return Redirect::route(self::TABLE)->with('error', self::MSG_ERROR_UNAUTHORIZED_CREATE);
        }

        $roleMenu       = [];
        $roleModuleTask = [];
        $dataMenu       = [];
        $dataModule = [];

        $menu           = json_decode(json_encode(config('cms-menu')));
        foreach ($menu as $mn) {
            $menuChild  = [];
            $listCode   = [];
            $countCheck = 0;
            foreach ($mn->child as $cm) {
                $listCode[]  = $cm->code;
                $countCheck += in_array($cm->code, $roleMenu) ? 1 : 0;

                $childModuleTask = [];
                foreach ($cm->module->task as $task) {
                    $moduleTaskcode = $cm->module->code . "." . $task->code;

                    $childModuleTask[] = (object) [
                        'code'  => $moduleTaskcode,
                        'name'  => $task->name,
                        'check' => in_array($moduleTaskcode, $roleModuleTask) ? 1 : 0
                    ];
                }

                $menuChild[] = (object) [
                    'code'        => $cm->code,
                    'name'        => $cm->name,
                    'check'       => in_array($cm->code, $roleMenu) ? 1 : 0,
                    'module_code' => $cm->module->code,
                    'module_task' => $childModuleTask
                ];
            }

            $code = $mn->code;

            $check = in_array($mn->code, $roleMenu) || ($countCheck > 0 && $countCheck == count($listCode))  ? 1 : 0;

            $moduleCode = "";
            $moduleTask = [];

            if (!empty($mn->module)) {
                $moduleCode = $mn->module->code;

                foreach ($mn->module->task as $task) {
                    $moduleTaskCode = $mn->module->code . "." . $task->code;

                    $moduleTask[] = (object) [
                        'code'  => $moduleTaskCode,
                        'name'  => $task->name,
                        'check' => in_array($moduleTaskCode, $roleModuleTask) ? 1 : 0
                    ];
                }
            }

            $dataMenu[] = (object) [
                'code'        => $code,
                'name'        => $mn->name,
                'check'       => $check,
                'module_code' => $moduleCode,
                'module_task' => $moduleTask,
                'child'       => $menuChild
            ];
        }

        $moduleTask = json_decode(json_encode(config('cms-module-task')));
        foreach ($moduleTask as $data) {
            $task       = [];
            $listCode   = [];
            $countCheck = 0;
            foreach ($data->task as $dataTask) {
                $listCode[] = $dataTask->code;
                $countCheck += in_array($dataTask->code, $roleModuleTask) ? 1 : 0;
                $task[]     = (object) [
                    'code'  => $dataTask->code,
                    'name'  => $dataTask->name,
                    'check' => in_array($dataTask->code, $roleModuleTask) ? 1 : 0
                ];
            }

            $dataModule[] = (object) [
                'name'  => $data->name,
                'check' => ($countCheck > 0 && $countCheck == count($listCode))  ? 1 : 0,
                'task'  => $task
            ];
        }

        return Inertia::render('Roles/Create', [
            'menu'   => $dataMenu,
            'module' => $dataModule,
        ]);
    }

    public function store(RequestValidate $request): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'create')) {
            return Redirect::route(self::TABLE)->with('error', 'Unauthorized!');
        }

        $validateRole =  $request->validate([
            'role.code' => ['required', 'max:50', 'unique:roles,code,'],
            'role.name' => ['required', 'max:50', 'unique:roles,name,'],
        ]);

        $validateRoleMenu = $request->validate([
            'role_menus.*' => 'required',
            'role_menus.*.code' => 'required',
            'role_menus.*.check' => ['required', 'boolean'],
            'role_menus.*.module_code' => 'nullable',
            'role_menus.*.module_task.*' => ['nullable', 'array'],
            'role_menus.*.module_task.*.code' => 'required',
            'role_menus.*.module_task.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*' => ['nullable', 'array'],
            'role_menus.*.child.*.code' => 'required',
            'role_menus.*.child.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*.module_code' => 'required',
            'role_menus.*.child.*.module_task.*' => ['nullable', 'array'],
            'role_menus.*.child.*.module_task.*.code' => 'required',
            'role_menus.*.child.*.module_task.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*.module_task.*.name' => ['required'],
        ]);

        $role = Role::create([
            'code' => $request->role['code'],
            'name' => $request->role['name'],
        ]);

        $menus = [];
        $moduleTask = [];
        foreach ($validateRoleMenu['role_menus'] as $keyMenu => $valMenu) {
            if ($valMenu['check'] == true) {
                $menus[] = [
                    'role_id' => $role->id,
                    'menu' => $valMenu['code'],
                ];
                $moduleTask[] = [
                    'role_id' => $role->id,
                    'module_task_code' => $valMenu['code'] . "." . self::INDEX,
                ];
            }

            if (count($valMenu['module_task']) > 0) {
                foreach ($valMenu['module_task'] as $keyTask => $valTask) {
                    if ($valTask['check'] == true)
                        $moduleTask[] = [
                            'role_id' => $role->id,
                            'module_task_code' => $valTask['code'],
                        ];
                }
            }

            if (count($valMenu['child']) > 0) {
                foreach ($valMenu['child'] as $keyChild => $valChild) {
                    if ($valChild['check'] == true) {
                        $menus[] = [
                            'role_id' => $role->id,
                            'menu' => $valChild['code'],
                        ];

                        $moduleTask[] = [
                            'role_id' => $role->id,
                            'module_task_code' => $valChild['code'] . "." . self::INDEX,
                        ];
                    }
                    if (count($valChild['module_task']) > 0) {
                        foreach ($valChild['module_task'] as $keyTask => $valTask) {
                            if ($valTask['check'] == true) {
                                $moduleTask[] = [
                                    'role_id' => $role->id,
                                    'module_task_code' => $valTask['code'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        RoleMenu::where('role_id', $role->id,)->delete();
        RoleModuleTask::where('role_id', $role->id)->delete();

        RoleMenu::insert($menus);
        RoleModuleTask::insert($moduleTask);

        return Redirect::route('roles')->with('success', 'Role created.');
    }

    public function edit(Role $role): Response|RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'read')) {
            return Redirect::route(self::TABLE)->with('error', self::MSG_ERROR_UNAUTHORIZED_READ);
        }

        $roleMenu       = RoleMenu::where('role_id', $role->id)->pluck('menu')->toArray();
        $roleModuleTask = RoleModuleTask::where('role_id', $role->id)->pluck('module_task_code')->toArray();

        $menu           = json_decode(json_encode(config('cms-menu')));
        $dataMenu       = [];
        foreach ($menu as $mn) {
            $menuChild  = [];
            $listCode   = [];
            $countCheck = 0;
            foreach ($mn->child as $cm) {
                $listCode[]  = $cm->code;
                $countCheck += in_array($cm->code, $roleMenu) ? 1 : 0;

                $childModuleTask = [];
                foreach ($cm->module->task as $task) {
                    $moduleTaskcode = $cm->module->code . "." . $task->code;

                    $childModuleTask[] = (object) [
                        'code'  => $moduleTaskcode,
                        'name'  => $task->name,
                        'check' => in_array($moduleTaskcode, $roleModuleTask) ? 1 : 0
                    ];
                }

                $menuChild[] = (object) [
                    'code'        => $cm->code,
                    'name'        => $cm->name,
                    'check'       => in_array($cm->code, $roleMenu) ? 1 : 0,
                    'module_code' => $cm->module->code,
                    'module_task' => $childModuleTask
                ];
            }

            $code = $mn->code;

            $check = in_array($mn->code, $roleMenu) || ($countCheck > 0 && $countCheck == count($listCode))  ? 1 : 0;

            $moduleCode = "";
            $moduleTask = [];

            if (!empty($mn->module)) {
                $moduleCode = $mn->module->code;

                foreach ($mn->module->task as $task) {
                    $moduleTaskCode = $mn->module->code . "." . $task->code;

                    $moduleTask[] = (object) [
                        'code'  => $moduleTaskCode,
                        'name'  => $task->name,
                        'check' => in_array($moduleTaskCode, $roleModuleTask) ? 1 : 0
                    ];
                }
            }

            $dataMenu[] = (object) [
                'code'        => $code,
                'name'        => $mn->name,
                'check'       => $check,
                'module_code' => $moduleCode,
                'module_task' => $moduleTask,
                'child'       => $menuChild
            ];
        }

        $moduleTask = json_decode(json_encode(config('cms-module-task')));

        $dataModule = [];
        foreach ($moduleTask as $data) {
            $task       = [];
            $listCode   = [];
            $countCheck = 0;
            foreach ($data->task as $dataTask) {
                $listCode[] = $dataTask->code;
                $countCheck += in_array($dataTask->code, $roleModuleTask) ? 1 : 0;
                $task[]     = (object) [
                    'code'  => $dataTask->code,
                    'name'  => $dataTask->name,
                    'check' => in_array($dataTask->code, $roleModuleTask) ? 1 : 0
                ];
            }

            $dataModule[] = (object) [
                'name'  => $data->name,
                'check' => ($countCheck > 0 && $countCheck == count($listCode))  ? 1 : 0,
                'task'  => $task
            ];
        }

        return Inertia::render('Roles/Edit', [
            'role' => [
                'id' => $role->id,
                'code' => $role->code,
                'name' => $role->name,
                'deleted_at' => $role->deleted_at,
            ],
            'menu'   => $dataMenu,
            'module' => $dataModule,
            'actions' => $this->manaCms->checkAccessAction(self::TABLE),
        ]);
    }

    public function update(RequestValidate $request): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'update')) {
            return Redirect::route('roles.edit', ['role' => $request->role['id']])->with('error', self::MSG_ERROR_UNAUTHORIZED_UPDATE);
        }

        $validateRole =  $request->validate([
            'role.code' => ['required', 'max:50', 'unique:roles,code,' . $request->role['id']],
            'role.name' => ['required', 'max:50', 'unique:roles,name,' . $request->role['id']],
        ]);

        $validateRoleMenu = $request->validate([
            'role_menus.*' => 'required',
            'role_menus.*.code' => 'required',
            'role_menus.*.check' => ['required', 'boolean'],
            'role_menus.*.module_code' => 'nullable',
            'role_menus.*.module_task.*' => ['nullable', 'array'],
            'role_menus.*.module_task.*.code' => 'required',
            'role_menus.*.module_task.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*' => ['nullable', 'array'],
            'role_menus.*.child.*.code' => 'required',
            'role_menus.*.child.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*.module_code' => 'required',
            'role_menus.*.child.*.module_task.*' => ['nullable', 'array'],
            'role_menus.*.child.*.module_task.*.code' => 'required',
            'role_menus.*.child.*.module_task.*.check' => ['required', 'boolean'],
            'role_menus.*.child.*.module_task.*.name' => ['required'],
        ]);

        $role = Role::find($request->role['id']);

        $role->update([
            'code' => $request->role['code'],
            'name' => $request->role['name'],
        ]);

        $menus = [];
        $moduleTask = [];
        foreach ($validateRoleMenu['role_menus'] as $keyMenu => $valMenu) {
            if ($valMenu['check'] == true) {
                $menus[] = [
                    'role_id' => $request->role['id'],
                    'menu' => $valMenu['code'],
                ];

                $moduleTask[] = [
                    'role_id' => $role->id,
                    'module_task_code' => $valMenu['code'] . "." . self::INDEX,
                ];
            }

            if (count($valMenu['module_task']) > 0) {
                foreach ($valMenu['module_task'] as $keyTask => $valTask) {
                    if ($valTask['check'] == true)
                        $moduleTask[] = [
                            'role_id' => $request->role['id'],
                            'module_task_code' => $valTask['code'],
                        ];
                }
            }

            if (count($valMenu['child']) > 0) {
                foreach ($valMenu['child'] as $keyChild => $valChild) {
                    if ($valChild['check'] == true) {
                        $menus[] = [
                            'role_id' => $request->role['id'],
                            'menu' => $valChild['code'],
                        ];

                        $moduleTask[] = [
                            'role_id' => $role->id,
                            'module_task_code' => $valChild['code'] . "." . self::INDEX,
                        ];
                    }
                    if (count($valChild['module_task']) > 0) {
                        foreach ($valChild['module_task'] as $keyTask => $valTask) {
                            if ($valTask['check'] == true) {
                                $moduleTask[] = [
                                    'role_id' => $request->role['id'],
                                    'module_task_code' => $valTask['code'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        RoleMenu::where('role_id', $request->role['id'],)->delete();
        RoleModuleTask::where('role_id', $request->role['id'])->delete();

        RoleMenu::insert($menus);
        RoleModuleTask::insert($moduleTask);

        return Redirect::back()->with('success', 'Role updated.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'delete')) {
            return Redirect::route('users.edit', ['role' => $role->id])->with('error', self::MSG_ERROR_UNAUTHORIZED_DELETE);
        }

        $role->delete();

        return Redirect::back()->with('success', 'Role deleted.');
    }

    public function restore(Role $role): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'restore')) {
            return Redirect::route('users.edit', ['role' => $role->id])->with('error', self::MSG_ERROR_UNAUTHORIZED_RESTORE);
        }

        $role->restore();

        return Redirect::back()->with('success', 'Role restored.');
    }
}
