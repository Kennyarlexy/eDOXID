<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\Grading;
use Illuminate\Http\Request;

class GradingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $gradings = Grading::orderBy('maksimal_score', 'desc')->get();
        return view('panel.admin.grading.index', compact('gradings'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('panel.admin.grading.create');
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
            'nama_grade' => 'required|unique:gradings,grade',
            'minimal_skor' => 'required|min:0|lt:maksimal_skor',
            'maksimal_skor' => 'required|max:100|gt:minimal_skor',
            'status_pengajuan' => 'required|in:approved,rejected,repeated'
        ]);

        $grading = Grading::create([
            'grade' => $request->nama_grade,
            'maksimal_score' => $request->maksimal_skor,
            'minimal_score' => $request->minimal_skor,
            'description' => $request->deskripsi,
            'submission_status' => $request->status_pengajuan
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah Grading baru dengan nilai id: ' . $grading->id,
        ]);

        return redirect()->route('panel.admin.grading.index')->with('success', 'Grading berhasil ditambahkan');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $grading = Grading::where('id', $id)->firstOrFail();
        return view('panel.admin.grading.edit', compact('grading'));
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
        $grading = Grading::where('id', $id)->firstOrFail();

        $request->validate([
            'nama_grade' => 'required|unique:gradings,grade,' . $grading->id,
            'minimal_skor' => 'required|min:0|lt:maksimal_skor',
            'maksimal_skor' => 'required|max:100|gt:minimal_skor',
            'status_pengajuan' => 'required|in:approved,rejected,repeated'
        ]);

        $grading->update([
            'grade' => $request->nama_grade,
            'maksimal_score' => $request->maksimal_skor,
            'minimal_score' => $request->minimal_skor,
            'description' => $request->deskripsi,
            'submission_status' => $request->status_pengajuan
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui Grading dengan nilai id: ' . $grading->id,
        ]);

        return redirect()->back()->with('success', 'Grading berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $grading = Grading::where('id', $id)->firstOrFail();

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus Grading dengan nilai id: ' . $grading->id,
        ]);

        $grading->delete();
        return redirect()->back()->with('success', 'Grading berhasil dihapus');
    }
}
