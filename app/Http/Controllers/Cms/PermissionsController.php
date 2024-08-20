<?php

namespace App\Http\Controllers\Cms;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\RoleMenu;
use App\Models\RoleModuleTask;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request as RequestValidate;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class PermissionsController extends Controller
{

    public function index(): Response
    {
        return Inertia::render('Permissions/Index', [
            'filters' => Request::all('search'),
            'roles' => Role::filter(Request::only('search'))
                ->paginate(10)
                ->withQueryString()
        ]);
    }

    public function edit($id): Response
    {
        $dataRole       = Role::where('id', $id)->first();
        $roleMenu       = RoleMenu::where('role_id', $id)->pluck('menu')->toArray();
        $roleModuleTask = RoleModuleTask::where('role_id', $id)->pluck('module_task_code')->toArray();

        $name = $dataRole->name;
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
        // dd($moduleTask);
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

        return Inertia::render('Permissions/Edit', [
            'menu'   => $dataMenu,
            'module' => $dataModule,
            'name' => $name,
            'id' => $id
        ]);
    }

    public function update(RequestValidate $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
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

        $menus = [];
        $moduleTask = [];

        foreach ($validatedData['role_menus'] as $keyMenu => $valMenu) {
            if ($valMenu['check'] == true)
                $menus[] = [
                    'role_id' => $id,
                    'menu' => $valMenu['code'],
                ];

            if (count($valMenu['module_task']) > 0) {
                foreach ($valMenu['module_task'] as $keyTask => $valTask) {
                    if ($valTask['check'] == true)
                        $moduleTask[] = [
                            'role_id' => $id,
                            'module_task_code' => $valTask['code'],
                        ];
                }
            }

            if (count($valMenu['child']) > 0) {
                foreach ($valMenu['child'] as $keyChild => $valChild) {
                    if ($valChild['check'] == true)
                        $menus[] = [
                            'role_id' => $id,
                            'menu' => $valChild['code'],
                        ];
                    if (count($valChild['module_task']) > 0) {
                        foreach ($valChild['module_task'] as $keyTask => $valTask) {
                            if ($valTask['check'] == true) {
                                $moduleTask[] = [
                                    'role_id' => $id,
                                    'module_task_code' => $valTask['code'],
                                ];
                            }
                        }
                    }
                }
            }
        }

        RoleMenu::where('role_id', $id)->delete();
        RoleModuleTask::where('role_id', $id)->delete();

        RoleMenu::insert($menus);
        RoleModuleTask::insert($moduleTask);

        return Redirect::back()->with('success', 'Permission updated.');
    }
}
