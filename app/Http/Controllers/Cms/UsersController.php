<?php

namespace App\Http\Controllers\Cms;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use App\Http\Libraries\ManaCms;
use App\Models\Role;
use Illuminate\Support\Facades\DB;

class UsersController extends Controller
{

    protected $manaCms;

    const MSG_ERROR_UNAUTHORIZED = 'Anda tidak memiliki akses untuk Users!';
    const MSG_ERROR_UNAUTHORIZED_CREATE = 'Anda tidak memiliki akses untuk Membuat Users!';
    const MSG_ERROR_UNAUTHORIZED_UPDATE = 'Anda tidak memiliki akses untuk Ubah Users!';
    const MSG_ERROR_UNAUTHORIZED_READ = 'Anda tidak memiliki akses untuk Melihat Users!';
    const MSG_ERROR_UNAUTHORIZED_DELETE = 'Anda tidak memiliki akses untuk Hapus Users!';
    const MSG_ERROR_UNAUTHORIZED_RESTORE = 'Anda tidak memiliki akses untuk Restore Users!';
    const TABLE = 'users';

    public function __construct(ManaCms $manaCms)
    {
        $this->manaCms = $manaCms;
    }

    public function index()
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'page')) {
            return Redirect::route('dashboard')->with('error', self::MSG_ERROR_UNAUTHORIZED);
        }

        $limit = Request::get('limit', 10);
        $sortBy = Request::get('sortBy', null);

        $users = User::leftJoin('roles', 'users.role_id', '=', 'roles.id')
            ->filter(Request::only('search', 'role', 'trashed'))
            ->select(
                'users.*',
                DB::raw('CASE WHEN roles.name IS NULL THEN "Super Admin" ELSE roles.name END as role'),
            )
            ->when($sortBy, function ($query) use ($sortBy) {
                $sort = explode('|', $sortBy);
                $sortKey = $sort[0];
                $sort = $sort[1];
                $query->orderBy($sortKey, $sort);
            })
            ->paginate($limit)
            ->withQueryString();

        $users->getCollection()->transform(function ($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'photo' => $user->photo_path ? URL::route('image', ['path' => $user->photo_path, 'w' => 40, 'h' => 40, 'fit' => 'crop']) : null,
                'deleted_at' => $user->deleted_at,
                'role' => $user->role,
            ];
        });

        return Inertia::render('Users/Index', [
            'filters' => Request::all('search', 'role', 'trashed', 'limit', 'sortBy'),
            'actions' => $this->manaCms->checkAccessAction(self::TABLE),
            'roles' => Role::select('id', 'name')->get(),
            self::TABLE => $users
        ]);
    }

    public function create()
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'create')) {
            return Redirect::route(self::TABLE)->with('error', self::MSG_ERROR_UNAUTHORIZED_CREATE);
        }

        $roles = Role::select('id', 'name')->get();

        return Inertia::render('Users/Create', [
            'roles' => $roles,
            'actions' => $this->manaCms->checkAccessAction(self::TABLE),
        ]);
    }

    public function store(): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'create')) {
            return Redirect::route(self::TABLE)->with('error', 'Unauthorized!');
        }

        Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', Rule::unique(self::TABLE)],
            'password' => ['nullable'],
            'photo' => ['nullable', 'image'],
            'role_id' => ["nullable", "exists:roles,id"]
        ]);

        User::create([
            'first_name' => Request::get('first_name'),
            'last_name' => Request::get('last_name'),
            'email' => Request::get('email'),
            'photo_path' => Request::file('photo') ? Request::file('photo')->store(self::TABLE) : null,
            'role_id' => Request::get('role_id')
        ]);

        return Redirect::route(self::TABLE)->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'read')) {
            return Redirect::route(self::TABLE)->with('error', self::MSG_ERROR_UNAUTHORIZED_READ);
        }

        $roles = Role::select('id', 'name')->get();

        return Inertia::render('Users/Edit', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'photo' => $user->photo_path ? URL::route('image', ['path' => $user->photo_path, 'w' => 60, 'h' => 60, 'fit' => 'crop']) : null,
                'deleted_at' => $user->deleted_at,
                'role_id' => $user->role_id
            ],
            'roles' => $roles,
            'actions' => $this->manaCms->checkAccessAction(self::TABLE),
        ]);
    }

    public function update(User $user): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'update')) {
            return Redirect::route('users.edit', ['user' => $user->id])->with('error', self::MSG_ERROR_UNAUTHORIZED_UPDATE);
        }

        if (App::environment('demo') && $user->isDemoUser()) {
            return Redirect::back()->with('error', 'Updating the demo user is not allowed.');
        }

        Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', Rule::unique(self::TABLE)->ignore($user->id)],
            'password' => ['nullable'],
            'photo' => ['nullable', 'image'],
        ]);

        $user->update(Request::only('first_name', 'last_name', 'email'));

        if (Request::file('photo')) {
            $user->update(['photo_path' => Request::file('photo')->store(self::TABLE)]);
        }

        if (Request::get('password')) {
            $user->update(['password' => Request::get('password')]);
        }

        return Redirect::back()->with('success', 'User updated.');
    }

    public function destroy(User $user)
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'delete')) {
            return Redirect::route('users.edit', ['user' => $user->id])->with('error', self::MSG_ERROR_UNAUTHORIZED_DELETE);
        }

        if (App::environment('demo') && $user->isDemoUser()) {
            return Redirect::back()->with('error', 'Deleting the demo user is not allowed.');
        }

        $user->delete();

        return Redirect::back()->with('success', 'User deleted.');
    }

    public function restore(User $user): RedirectResponse
    {
        if (!$this->manaCms->checkAccess(self::TABLE, 'restore')) {
            return Redirect::route('users.edit', ['user' => $user->id])->with('error', self::MSG_ERROR_UNAUTHORIZED_RESTORE);
        }

        $user->restore();

        return Redirect::back()->with('success', 'User restored.');
    }
}
