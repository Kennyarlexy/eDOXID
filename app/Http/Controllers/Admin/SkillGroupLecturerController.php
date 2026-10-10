<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\SkillGroup;
use App\Models\SkillGroupLecturer;
use App\Models\User;
use Illuminate\Http\Request;

class SkillGroupLecturerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($skill_group_id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();
        $skill_group_lecturers = SkillGroupLecturer::where('skill_group_id', $skill_group_id)->orderBy('id', 'desc')->get();
        return view('panel.admin.kelompok-keahlian.daftar-dosen.index', compact('skill_group', 'skill_group_lecturers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($skill_group_id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();
        $skill_group_lecturer_ids = SkillGroupLecturer::where('skill_group_id', $skill_group->id)->pluck('lecturer_id')->toArray();

        $lecturers = User::where('role', User::ROLE_DOSEN)->whereNotIn('id', $skill_group_lecturer_ids)->get();
        return view('panel.admin.kelompok-keahlian.daftar-dosen.create', compact('skill_group', 'lecturers'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $skill_group_id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();

        $request->validate([
            'jabatan' => 'required',
            'dosen' => 'required'
        ]);

        $lecturer = User::where('id', $request->dosen)->where('role', User::ROLE_DOSEN)->first();
        if (!$lecturer) {
            return redirect()->back()->with('failed', 'Dosen tidak ditemukan');
        }

        if (!($request->jabatan == 'Ketua' || $request->jabatan == 'Sekretaris' || $request->jabatan == 'Anggota')) {
            return redirect()->back()->with('failed', 'Pilihan jabatan tidak valid');
        }

        SkillGroupLecturer::create([
            'position' => $request->jabatan,
            'lecturer_id' => $lecturer->id,
            'skill_group_id' => $skill_group->id
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah dosen dengan nama: ' . $lecturer->name . ' ke kelompok keahlian dengan nilai id: ' . $skill_group->id,
        ]);

        return redirect()->route('panel.admin.kelompok-keahlian.daftar-dosen.index', $skill_group->id)->with('success', 'Berhasil menambah dosen ke Kelompok Keahlian: '. $skill_group->name);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($skill_group_id, $id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();
        $skill_group_lecturer = SkillGroupLecturer::where('id', $id)->where('skill_group_id', $skill_group->id)->firstOrFail();

        $skill_group_lecturer_ids = SkillGroupLecturer::where('skill_group_id', $skill_group->id)->where('lecturer_id', '!=', $skill_group_lecturer->lecturer_id)->pluck('lecturer_id')->toArray();
        $lecturers = User::where('role', User::ROLE_DOSEN)->whereNotIn('id', $skill_group_lecturer_ids)->get();

        return view('panel.admin.kelompok-keahlian.daftar-dosen.edit', compact('skill_group', 'skill_group_lecturer', 'lecturers', 'skill_group_lecturer_ids'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $skill_group_id, $id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();
        $skill_group_lecturer = SkillGroupLecturer::where('id', $id)->where('skill_group_id', $skill_group->id)->firstOrFail();

        $request->validate([
            'jabatan' => 'required',
            'dosen' => 'required'
        ]);

        $lecturer = User::where('id', $request->dosen)->where('role', User::ROLE_DOSEN)->first();
        if (!$lecturer) {
            return redirect()->back()->with('failed', 'Dosen tidak ditemukan');
        }

        if (!($request->jabatan == 'Ketua' || $request->jabatan == 'Sekretaris' || $request->jabatan == 'Anggota')) {
            return redirect()->back()->with('failed', 'Pilihan jabatan tidak valid');
        }

        $skill_group_lecturer->update([
            'position' => $request->jabatan,
            'lecturer_id' => $lecturer->id,
            'skill_group_id' => $skill_group->id
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Mempebarui dosen dengan nama: ' . $lecturer->name . ' pada kelompok keahlian dengan nilai id: ' . $skill_group->id,
        ]);

        return redirect()->back()->with('success', 'Berhasil mengubah dosen pada Kelompok Keahlian: '. $skill_group->name);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($skill_group_id, $id)
    {
        $skill_group = SkillGroup::where('id', $skill_group_id)->firstOrFail();
        $skill_group_lecturer = SkillGroupLecturer::where('id', $id)->where('skill_group_id', $skill_group->id)->firstOrFail();

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus dosen dengan nama: ' . $skill_group_lecturer->lecturer->name . ' dari kelompok keahlian dengan nilai id: ' . $skill_group->id,
        ]);

        $skill_group_lecturer->delete();
        return redirect()->back()->with('success', 'Berhasil menghapus dosen pada Kelompok Keahlian: ' . $skill_group->name);
    }
}
