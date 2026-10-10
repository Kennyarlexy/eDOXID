<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\User;
use App\Models\Verificator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class DosenController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $dosens = User::where('role', User::ROLE_DOSEN)->orderBy('id', 'desc')->get();
        return view('panel.admin.users.dosen.index', compact('dosens'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.users.dosen.create');
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
            'nip' => 'required|unique:users,unique_id',
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->jabatan) {
            $request->validate([
                'jabatan' => 'required|in:Ketua Jurusan,Ketua Prodi',
            ]);

            if ($request->jabatan == 'Ketua Jurusan') {
                $dosen_ketua_jurusan = User::where('role', User::ROLE_DOSEN)->where('position', 'Ketua Jurusan')->first();
                if ($dosen_ketua_jurusan) {
                    return redirect()->back()->with('failed', 'Ketua Jurusan sudah terdaftar');
                }
            }

            if ($request->jabatan == 'Ketua Prodi') {
                $dosen_ketua_prodi = User::where('role', User::ROLE_DOSEN)->where('position', 'Ketua Prodi')->first();
                if ($dosen_ketua_prodi) {
                    return redirect()->back()->with('failed', 'Ketua Prodi sudah terdaftar');
                }
            }
        }

        $dosen = User::create([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => User::ROLE_DOSEN,
            'unique_id' => $request->nip,
            'status' => $request->status,
            'position' => $request->jabatan
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah akun Dosen baru dengan nilai id: ' . $dosen->id,
        ]);

        return redirect()->route('panel.admin.users.dosen.index')->with('success', 'Dosen berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $dosen = User::where('id', $id)->where('role', User::ROLE_DOSEN)->firstOrFail();
        return view('panel.admin.users.dosen.show', compact('dosen'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $dosen = User::where('id', $id)->where('role', User::ROLE_DOSEN)->firstOrFail();
        return view('panel.admin.users.dosen.edit', compact('dosen'));
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
        $dosen = User::where('id', $id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $request->validate([
            'nama' => 'required|max:255',
            'email' => 'required|email|unique:users,email,' . $dosen->id,
            'password' => 'nullable|min:6',
            'nip' => 'required|unique:users,unique_id,' . $dosen->id,
            'status' => 'required|in:active,inactive',
        ]);

        if ($request->jabatan) {
            $request->validate([
                'jabatan' => 'required|in:Ketua Jurusan,Ketua Prodi',
            ]);

            if ($request->jabatan == 'Ketua Jurusan') {
                $dosen_ketua_jurusan = User::where('id', '!=', $dosen->id)->where('role', User::ROLE_DOSEN)->where('position', 'Ketua Jurusan')->first();
                if ($dosen_ketua_jurusan) {
                    return redirect()->back()->with('failed', 'Ketua Jurusan sudah terdaftar');
                }
            }

            if ($request->jabatan == 'Ketua Prodi') {
                $dosen_ketua_prodi = User::where('id', '!=', $dosen->id)->where('role', User::ROLE_DOSEN)->where('position', 'Ketua Prodi')->first();
                if ($dosen_ketua_prodi) {
                    return redirect()->back()->with('failed', 'Ketua Prodi sudah terdaftar');
                }
            }
        }

        $dosen->update([
            'name' => $request->nama,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'unique_id' => $request->nip,
            'status' => $request->status,
            'position' => $request->jabatan
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui akun Dosen dengan nilai id: ' . $dosen->id,
        ]);

        return redirect()->back()->with('success', 'Dosen berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $dosen = User::where('id', $id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $verificators = Verificator::where('user_id', $dosen->id)->get();
        if ($verificators->count() > 0) {
            return redirect()->back()->with('failed', 'Dosen ini merupakan verifikator pada sebuah pengajuan');
        }

        if ($dosen->profile_image != null) {
            Storage::disk('public')->delete('profiles/' . $dosen->profile_image);
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus akun Dosen dengan nilai id: ' . $dosen->id,
        ]);

        $dosen->delete();
        return redirect()->back()->with('success', 'Dosen berhasil dihapus');
    }
}
