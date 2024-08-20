<?php

namespace App\Http\Libraries;

use App\Models\RoleMenu;
use App\Models\Role;
use App\Models\RoleModuleTask;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Route;
use App\Http\Inertia\Inertia;

class ManaCms
{
    //ACTION
    public const DELETE = 'delete';
    public const ADD = 'add';
    public const EDIT = 'edit';
    public const PRINT = 'print';
    public const SHARE_WA = 'export';

    public static function listMenu()
    {
        $userLogin = Auth::user();
        $role = $userLogin->role_id;

        $menus = json_decode(json_encode(config('cms-menu')));

        $roleMenuCodes  = RoleMenu::where('role_id', $role)->pluck('menu')->toArray();

        $listMenu = [];
        foreach ($menus as $menu) {
            $dataMenuChild = $menu->child;

            $listMenuChild   = array();
            $activeMenuChild = false;
            foreach ($dataMenuChild as $child) {
                if ($role == null || in_array($child->code, $roleMenuCodes)) {
                    $url = !empty($child->route_name) && Route::has($child->route_name) ?  route($child->route_name) : '#';
                    $checkActiveMenuChild = ManaCms::checkMenuActive($child->route_name);
                    $listMenuChild[] = (object) [
                        'name'     => $child->name,
                        'label'  => $child->label,
                        'icon'      => $child->icon,
                        'url'      => $child->url,
                        'active' => $checkActiveMenuChild ? "active" : "",
                    ];

                    if (!$activeMenuChild && $checkActiveMenuChild) {
                        $activeMenuChild = $checkActiveMenuChild;
                    }
                }
            }

            if ($role == null || in_array($menu->code, $roleMenuCodes) || sizeof($listMenuChild) > 0) {
                $icon = $menu->icon && view()->exists('components.icons.' . $menu->icon) ? $menu->icon : "";

                $url = !empty($menu->route_name) && Route::has($menu->route_name) ?  route($menu->route_name) : '#';

                $active = $activeMenuChild || ManaCms::checkMenuActive($menu->route_name) ? "active" : "";

                $listMenu[] = (object) [
                    'name'     => $menu->name,
                    'label'  => $menu->label,
                    'icon'      => $menu->icon,
                    'url'      => $menu->url,
                    'active' => $active,
                    'show'     => $activeMenuChild ? "show" : "",
                    'childs' => $listMenuChild,
                ];
            }
        }


        return $listMenu;
    }

    public static function checkMenuActive($routeMenu)
    {
        $routeName = Route::current()->getName();
        $groupName = explode('.', $routeName)[0];
        $routeMenu = explode('.', $routeMenu)[0];

        $result = false;
        if (!empty($routeMenu) && $groupName == $routeMenu) {
            $result = true;
        }

        return $result;
    }

    public static function checkAccess($module, $task)
    {
        $userLogin = Auth::user();
        $role = $userLogin->role_id;

        $listRoleTask = RoleModuleTask::where('role_id', $role)->pluck('module_task_code')->toArray();
        if ((sizeof($listRoleTask) && in_array($module . "." . $task, $listRoleTask)) || $role == null) {
            return true;
        }

        return false;
    }

    public static function checkAccessAction($module)
    {
        $userLogin = Auth::user();
        $role = $userLogin->role_id;

        $listRoleTask = [];
        if ($role == null) {

            $menus = json_decode(json_encode(config('cms-menu')));

            $menuFiler = array_filter($menus, function ($menu) use ($module) {
                return $menu->code == $module;
            });

            $menuFiler = array_values($menuFiler);
            if (count($menuFiler)) {
                //mengembalikan semua actions dari module
                return array_map(function ($value) {
                    return $value->code;
                }, $menuFiler[0]->module->task);
            }

            foreach ($menus as $menu) {
                //mengambil semua child dari menu
                $dataMenuChild = $menu->child;

                $menuFiler = array_filter($dataMenuChild, function ($filter) use ($module) {
                    return $filter->code == $module;
                });

                $menuFiler = array_values($menuFiler);
                if (count($menuFiler)) {
                    //mengembalikan semua actions dari module
                    return array_map(function ($value) {
                        return $value->code;
                    }, $menuFiler[0]->module->task);
                }
            }
        } else {

            $listRoleTask = RoleModuleTask::where('role_id', $role)
                ->whereLike('module_task_code', '%' . $module . '%')
                ->pluck('module_task_code')
                ->toArray();
        }

        $listRoleTask = array_map(function ($value) {
            return explode(".", $value)[1];
        }, $listRoleTask);

        return $listRoleTask;
    }
}
