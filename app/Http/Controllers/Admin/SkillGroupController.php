<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\SkillGroup;
use Illuminate\Http\Request;

class SkillGroupController extends Controller
{
    public function index()
    {
        $skill_groups = SkillGroup::orderBy('id', 'desc')->get();
        return view('panel.admin.kelompok-keahlian.index', compact('skill_groups'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.kelompok-keahlian.create');
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
            'nama' => 'required|string|max:255|unique:skill_groups,name',
            'status' => 'required'
        ]);

        $skill_group = SkillGroup::create([
            'name' => $request->nama,
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah kelompok keahlian baru dengan nilai id: ' . $skill_group->id,
        ]);

        return redirect()->route('panel.admin.kelompok-keahlian.index')->with('success', 'Kelompok keahlian berhasil ditambah');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $skill_group = SkillGroup::where('id', $id)->firstOrFail();
        return view('panel.admin.kelompok-keahlian.edit', compact('skill_group'));
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
        $skill_group = SkillGroup::where('id', $id)->firstOrFail();
        $request->validate([
            'nama' => 'required|string|max:255|unique:skill_groups,name,' . $skill_group->id,
            'status' => 'required',
        ]);

        $skill_group->update([
            'name' => $request->nama,
            'status' => $request->status
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui kelompok keahlian dengan nilai id: ' . $skill_group->id,
        ]);

        return redirect()->back()->with('success', 'Kelompok keahlian berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $skill_group = SkillGroup::where('id', $id)->firstOrFail();

        if ($skill_group->submissions->count() > 0) {
            return redirect()->back()->with('failed', 'Kelompok keahlian tidak dapat dihapus karena memiliki pengajuan');
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus kelompok keahlian dengan nilai id: ' . $skill_group->id,
        ]);

        $skill_group->delete();
        return redirect()->back()->with('success', 'Kelompok keahlian berhasil dihapus');
    }
}
