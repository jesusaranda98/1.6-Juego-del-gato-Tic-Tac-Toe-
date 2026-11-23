<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminStoreUserRequest;
use App\Http\Requests\AdminUpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        $users = User::query()->orderBy('id')->get();

        return view('admin.users.index', [
            'users' => $users,
        ]);
    }

    public function create(): View
    {
        return view('admin.users.create');
    }

    public function store(AdminStoreUserRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        User::create([
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.edit', [
            'user' => $user,
        ]);
    }

    public function update(AdminUpdateUserRequest $request, User $user): RedirectResponse
    {
        $validated = $request->validated();

        $data = [
            'name' => trim($validated['name']),
            'email' => strtolower(trim($validated['email'])),
            'role' => $validated['role'],
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user): RedirectResponse
    {
        if ((int) Auth::id() === (int) $user->id) {
            return back()->withErrors([
                'user' => 'No puedes eliminar tu propia cuenta mientras tienes sesión activa.',
            ]);
        }

        $user->delete();

        return redirect()->route('admin.users.index')
            ->with('success', 'Usuario eliminado correctamente.');
    }
}