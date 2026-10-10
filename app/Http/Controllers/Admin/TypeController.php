<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\GetTypeAccessPermission;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TypeController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $types = Type::orderBy('id', 'desc')->get();
        return view('panel.admin.type.index', compact('types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.type.create');
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
            'nama' => 'required|string|max:255|unique:types,name',
            'status' => 'required',
            'warna' => 'required|string|max:7',
        ]);

        $type = Type::create([
            'name' => $request->nama,
            'slug' => Str::slug($request->nama),
            'status' => $request->status,
            'color' => $request->warna,
            'description' => $request->deskripsi,
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah tipe pengajuan baru dengan nilai id: ' . $type->id,
        ]);

        return redirect()->route('panel.admin.type.index')->with('success', 'Tipe Pengajuan berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Type $type)
    {
        return view('panel.admin.type.show', compact('type'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Type $type)
    {
        return view('panel.admin.type.edit', compact('type'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Type $type)
    {
        $request->validate([
            'status' => 'required',
            'warna' => 'required|string|max:7',
        ]);

        $name = $type->name;
        $slug = $type->slug;

        if (!($name == 'Proposal' || $name == 'Sidang Hasil' || $name == 'Sidang Akhir')) {
            $request->validate([
                'nama' => 'required|string|max:255|unique:types,name,' . $type->id,
            ]);

            $name = $request->nama;
            $slug = Str::slug($request->nama);

            $type->update([
                'name' => $request->nama,
                'slug' => Str::slug($request->nama),
            ]);
        }

        $type->update([
            'name' => $name,
            'slug' => $slug,
            'status' => $request->status,
            'color' => $request->warna,
            'description' => $request->deskripsi,
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui tipe pengajuan dengan nilai id: ' . $type->id,
        ]);

        return redirect()->back()->with('success', 'Tipe Pengajuan berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Type $type)
    {
        if ($type->submissions->count() > 0) {
            return redirect()->back()->with('failed', 'Tipe Pengajuan tidak dapat dihapus karena masih memiliki pengajuan');
        }

        if ($type->name == 'Proposal' || $type->name == 'Sidang Hasil' || $type->name == 'Sidang Akhir') {
            return redirect()->route('panel.admin.type.index')->with('failed', 'Pengajuan ini tidak dapat dihapus.');
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus tipe pengajuan dengan nilai id: ' . $type->id,
        ]);

        $type->delete();
        return redirect()->back()->with('success', 'Tipe Pengajuan berhasil dihapus');
    }
}
