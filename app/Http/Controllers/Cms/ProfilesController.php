<?php

namespace App\Http\Controllers\Cms;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Http\Request as RequestVal;
use Illuminate\Support\Facades\URL;
use Inertia\Inertia;
use App\Http\Controllers\Controller;
use App\Http\Libraries\ManaCms;

class ProfilesController extends Controller
{
    protected $manaCms;

    const TABLE = 'users';

    public function __construct(ManaCms $manaCms)
    {
        $this->manaCms = $manaCms;
    }

    public function index()
    {

        $user = Auth::user();

        return Inertia::render('Profiles/Index', [
            'user' => [
                'id' => $user->id,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'owner' => $user->owner,
                'photo' => $user->photo_path ? URL::route('image', ['path' => $user->photo_path, 'w' => 60, 'h' => 60, 'fit' => 'crop']) : null,
            ],
        ]);
    }

    public function update(RequestVal $request)
    {

        $user = User::findOrFail($request->id);

        Request::validate([
            'first_name' => ['required', 'max:50'],
            'last_name' => ['required', 'max:50'],
            'email' => ['required', 'max:50', 'email', 'unique:users,email,' . $request->id . ',id'],
            'password' => ['nullable'],
            'owner' => ['required', 'boolean'],
            'photo' => ['nullable', 'image'],
        ]);

        $user->update(Request::only('first_name', 'last_name', 'email', 'owner'));

        if (Request::file('photo')) {
            $user->update(['photo_path' => Request::file('photo')->store(self::TABLE)]);
        }

        if (Request::get('password')) {
            $user->update(['password' => Request::get('password')]);
        }

        return Redirect::back()->with('success', 'Profile updated.');
    }
}
