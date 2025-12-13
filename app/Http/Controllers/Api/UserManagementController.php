<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserManagementController extends Controller
{
    public function index()
    {
        $users = User::with('roles')->get();
        return response()->json($users);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role' => 'required|in:user,petugas,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Set default password based on role
        $defaultPassword = $request->role === 'petugas' ? 'petugas123' : 
                          ($request->role === 'admin' ? 'admin123' : '12345678');

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'password' => Hash::make($defaultPassword),
        ]);

        // Assign role
        $role = Role::where('slug', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        return response()->json([
            'message' => 'User berhasil dibuat',
            'user' => $user->load('roles'),
            'default_password' => $defaultPassword
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $id,
            'phone' => 'required|string|max:20',
            'address' => 'required|string',
            'role' => 'required|in:user,petugas,admin',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
        ]);

        // Update role
        $role = Role::where('slug', $request->role)->first();
        if ($role) {
            $user->roles()->sync([$role->id]);
        }

        return response()->json([
            'message' => 'User berhasil diupdate',
            'user' => $user->load('roles')
        ]);
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
        
        // Prevent deleting super admin
        if ($user->email === 'admin@ecocare.com') {
            return response()->json(['message' => 'Tidak dapat menghapus super admin'], 403);
        }

        $user->delete();

        return response()->json(['message' => 'User berhasil dihapus']);
    }

    public function resetPassword(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $role = $user->roles()->first();
        $defaultPassword = $role && $role->slug === 'petugas' ? 'petugas123' : 
                          ($role && $role->slug === 'admin' ? 'admin123' : '12345678');

        $user->update([
            'password' => Hash::make($defaultPassword)
        ]);

        return response()->json([
            'message' => 'Password berhasil direset',
            'default_password' => $defaultPassword
        ]);
    }
}