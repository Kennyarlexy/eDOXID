<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class BagianAdministrasiController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $bagianAdministrasis = User::where('role', User::ROLE_BAGIAN_ADMINISTRASI)->orderBy('id', 'desc')->get();
        return view('panel.admin.users.bagian-administrasi.index', compact('bagianAdministrasis'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.users.bagian-administrasi.create');
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

        $tataUsaha = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'role' => User::ROLE_BAGIAN_ADMINISTRASI,
            'password' => Hash::make($request->password),
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah akun Tata Usaha baru dengan nilai id: ' . $tataUsaha->id,
        ]);

        return redirect()->back()->with('success', 'Bagian Administrasi berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $bagianAdministrasi = User::where('id', $id)->where('role', User::ROLE_BAGIAN_ADMINISTRASI)->firstOrFail();
        return view('panel.admin.users.bagian-administrasi.show', compact('bagianAdministrasi'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $bagianAdministrasi = User::where('id', $id)->where('role', User::ROLE_BAGIAN_ADMINISTRASI)->firstOrFail();
        return view('panel.admin.users.bagian-administrasi.edit', compact('bagianAdministrasi'));
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
        $bagianAdministrasi = User::where('id', $id)->where('role', User::ROLE_BAGIAN_ADMINISTRASI)->firstOrFail();

        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $bagianAdministrasi->id,
            'status' => 'required'
        ]);

        $password = $bagianAdministrasi->password;
        if ($request->password) $password = Hash::make($request->password);

        $bagianAdministrasi->update([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => $password,
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui akun Tata Usaha dengan nilai id: ' . $bagianAdministrasi->id,
        ]);

        return redirect()->back()->with('success', 'Bagian Administrasi berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $bagianAdministrasi = User::where('id', $id)->where('role', User::ROLE_BAGIAN_ADMINISTRASI)->firstOrFail();

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus akun Tata Usaha dengan nilai id: ' . $bagianAdministrasi->id,
        ]);

        $bagianAdministrasi->delete();
        return redirect()->back()->with('success', 'Bagian Administrasi berhasil dihapus');
    }
}
