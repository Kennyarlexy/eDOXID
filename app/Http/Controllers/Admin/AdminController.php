<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $admins = User::where('role', User::ROLE_ADMIN)->orderBy('id', 'desc')->get();
        return view('panel.admin.users.admin.index', compact('admins'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.users.admin.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'status' => 'required'
        ]);

        $admin = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'role' => User::ROLE_ADMIN,
            'password' => Hash::make($request->password),
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah akun Admin baru dengan nilai id: ' . $admin->id,
        ]);

        return redirect()->route('panel.admin.users.admin.index')->with('success', 'Admin berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();
        return view('panel.admin.users.admin.edit', compact('admin'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();

        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $admin->id,
            'status' => 'required'
        ]);

        $password = $admin->password;
        if ($request->password) $password = Hash::make($request->password);

        $admin->update([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => $password,
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui akun Admin dengan nilai id: ' . $admin->id,
        ]);

        return redirect()->back()->with('success', 'Admin berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $admin = User::where('id', $id)->where('role', User::ROLE_ADMIN)->firstOrFail();

        if ($admin->id == auth()->user()->id) {
            return redirect()->back()->with('failed', 'Anda tidak diizinkan untuk menghapus diri anda sendiri.');
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus akun Admin dengan nilai id: ' . $admin->id,
        ]);

        $admin->delete();
        return redirect()->back()->with('success', 'Admin berhasil dihapus.');
    }
}
