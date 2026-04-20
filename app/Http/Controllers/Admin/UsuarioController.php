<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

class UsuarioController extends Controller
{
    public function index()
    {
        $usuarios = User::with('perfil')->orderBy('name')->get();
        return view('admin.usuarios.index', compact('usuarios'));
    }

    public function sendResetEmail(User $usuario)
    {
        try {
            $status = Password::sendResetLink(['email' => $usuario->email]);
        } catch (\Throwable $e) {
            report($e);
            return back()->with('error', 'No se pudo enviar el email.');
        }

        return back()->with(
            $status === Password::RESET_LINK_SENT
                ? ['success' => 'Se envió el email de recuperación correctamente.']
                : ['error' => 'No se pudo enviar el email.']
        );
    }

    public function create()
    {
        $perfiles = Perfil::orderBy('nombre')->get();
        return view('admin.usuarios.create', compact('perfiles'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email',
            'perfil_id'      => 'required|exists:perfiles,id',
            'password'       => 'required|string|min:8|confirmed',
            'profile_photo'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'active'         => 'boolean',
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $file->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }


        $data['password'] = bcrypt($data['password']);
        $data['active'] = $request->has('active');

        User::create($data);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario creado correctamente.');
    }

    public function edit(User $usuario)
    {
        $perfiles = Perfil::orderBy('nombre')->get();
        return view('admin.usuarios.edit', compact('usuario', 'perfiles'));
    }

    public function update(Request $request, User $usuario)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'required|email|unique:users,email,' . $usuario->id,
            'perfil_id'      => 'required|exists:perfiles,id',
            'password'       => 'nullable|string|min:8|confirmed',
            'profile_photo'  => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'active'         => 'nullable|boolean',
        ]);

        if ($request->hasFile('profile_photo')) {
            $file = $request->file('profile_photo');
            $path = $file->store('profile_photos', 'public');
            $data['profile_photo'] = $path;
        }        

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Aseguramos que siempre se setee correctamente (esto pisa todo)
        $data['active'] = $request->boolean('active');

        $usuario->update($data);

        return redirect()->route('admin.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $usuario)
    {
        if ($usuario->perfil && strtoupper($usuario->perfil->nombre) === 'MASTER') {
            return redirect()->route('admin.usuarios.index')
                            ->with('error', 'No se puede eliminar un usuario con perfil MASTER.');
        }

        $usuario->delete();

        return redirect()->route('admin.usuarios.index')
                        ->with('success', 'Usuario eliminado correctamente.');
    }
}
