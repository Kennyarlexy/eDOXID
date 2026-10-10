<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\ActivitySubmissionLog;
use App\Models\ActivityUserLog;
use App\Models\Submission;
use App\Models\SubmissionPresence;
use App\Models\Type;
use App\Models\User;
use App\Models\Verificator;
use Carbon\Carbon;
use Illuminate\Http\Request;

class SubmissionPresenceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $pembimbings = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
        $pengujis = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PENGUJI)->get();

        $presences = SubmissionPresence::where('submission_id', $submission_id)->get();

        $idSidangHasil = 0;
        if ($type_slug == 'sidang-akhir') {
            $idSpota = $submission->spota_submission_id;
            $submissionSidangHasil = Submission::where('spota_submission_id', $idSpota)->where('type_id', '2')->orderBy('id', 'desc')->first();
            if ($submissionSidangHasil != null) {
                $idSidangHasil = $submissionSidangHasil->id;
            }
        }

        $idSeminarProposal = 0;
        if ($type_slug == 'sidang-hasil') {
            $idSpota = $submission->spota_submission_id;
            $submissionSeminarProposal = Submission::where('spota_submission_id', $idSpota)->where('type_id', '1')->orderBy('id', 'desc')->first();
            if ($submissionSeminarProposal != null) {
                $idSeminarProposal = $submissionSeminarProposal->id;
            }
        }

        return view('panel.dosen.pengajuan.show.presence', compact(
            'presences',
            'submission',
            'verificator',
            'type',
            'auth_dosen',
            'pembimbings',
            'pengujis',
            'idSidangHasil',
            'idSeminarProposal',
        ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function store($type_slug, $submission_id, Request $request)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();

            if ($verificator->type != 'Pembimbing') {
                return redirect()->back()->with('error', 'Hanya pembimbing yang dapat hak akses untuk mengisi daftar hadir');
            }
        }

        $request->validate([
            'nama' => 'required',
            'nim_nip_nidk' => 'required',
            'keterangan' => 'required',
        ]);

        $unique_ids = [];
        $unique_ids[] = $submission->student->unique_id;

        $pengujis = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PENGUJI)->get();
        foreach ($pengujis as $penguji) {
            $unique_ids[] = $penguji->user->unique_id;
        }

        $pembimbings = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
        foreach ($pembimbings as $pembimbing) {
            $unique_ids[] = $pembimbing->user->unique_id;
        }

        $submission_presences = SubmissionPresence::where('submission_id', $submission_id)->get();

        // check if request nim_nip_nidk was in SubmissionPresence
        foreach ($unique_ids as $unique_id) {
            if ($request->nim_nip_nidk == $unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk nidn sudah ada sebelumnya.');
            }
        }

        foreach ($submission_presences as $submission_presence) {
            if ($request->nim_nip_nidk == $submission_presence->unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk nidn sudah ada sebelumnya.');
            }
        }

        SubmissionPresence::create([
            'submission_id' => $submission_id,
            'name' => $request->nama,
            'unique_id' => $request->nim_nip_nidk,
            'keterangan' => $request->keterangan,
        ]);

        $details = 'Tambah data kehadiran baru pada submission id: '.$submission_id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Daftar kehadiran berhasil ditambahkan');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function update($type_slug, $submission_id, Request $request, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();

            if ($verificator->type != 'Pembimbing') {
                return redirect()->back()->with('error', 'Hanya pembimbing yang dapat hak akses untuk mengubah daftar hadir');
            }
        }

        $submission_presence = SubmissionPresence::where('id', $id)->where('submission_id', $submission_id)->firstOrFail();

        $request->validate([
            'nama' => 'required',
            'nim_nip_nidk' => 'required',
            'keterangan' => 'required',
        ]);

        $unique_ids = [];
        $unique_ids[] = $submission->student->unique_id;

        $pengujis = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PENGUJI)->get();
        foreach ($pengujis as $penguji) {
            $unique_ids[] = $penguji->user->unique_id;
        }

        $pembimbings = Verificator::where('submission_id', $submission_id)->where('type', Verificator::TYPE_PEMBIMBING)->get();
        foreach ($pembimbings as $pembimbing) {
            $unique_ids[] = $pembimbing->user->unique_id;
        }

        $submission_presences_db = SubmissionPresence::where('submission_id', $submission_id)->where('id', '!=', $submission_presence->id)->get();

        // check if request nim_nip_nidk was in SubmissionPresence
        foreach ($unique_ids as $unique_id) {
            if ($request->nim_nip_nidk == $unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk nidn sudah ada sebelumnya.');
            }
        }

        foreach ($submission_presences_db as $submission_presence_db) {
            if ($request->nim_nip_nidk == $submission_presence_db->unique_id) {
                return redirect()->back()->with('failed', 'nim nip nidk nidn sudah ada sebelumnya.');
            }
        }

        $submission_presence->update([
            'submission_id' => $submission_id,
            'name' => $request->nama,
            'unique_id' => $request->nim_nip_nidk,
            'keterangan' => $request->keterangan,
        ]);

        $details = 'Perbaharui data kehadiran pada submission id: '.$submission_id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Daftar kehadiran berhasil diperbaharui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy($type_slug, $submission_id, $id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();

            if ($verificator->type != 'Pembimbing') {
                return redirect()->back()->with('error', 'Hanya pembimbing yang dapat hak akses untuk menghapus daftar hadir');
            }
        }

        SubmissionPresence::where('id', $id)->where('submission_id', $submission_id)->delete();

        $details = 'Hapus data kehadiran pada submission id: '.$submission_id;

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => $details,
        ]);

        ActivitySubmissionLog::create([
            'submission_id' => $submission->id,
            'details' => $details,
            'user_id' => auth()->user()->id,
        ]);

        return redirect()->back()->with('success', 'Daftar kehadiran berhasil dihapus');
    }

    public function updatePublicUrlPresence(Request $request, $type_slug, $submission_id)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $auth_dosen = User::where('id', auth()->user()->id)->where('role', User::ROLE_DOSEN)->firstOrFail();

        $submission = Submission::where('is_ready_to_be_processed', 1)->where('id', $submission_id)->where('type_id', $type->id)->whereHas('verificators', function ($q) use ($auth_dosen) {
            $q->where('user_id', $auth_dosen->id);
        })->firstOrFail();

        $verificator = Verificator::where('submission_id', $submission_id)->where('user_id', $auth_dosen->id)->first();
        if ($verificator) {
            $verificator->is_read_by_dosen = 1;
            $verificator->save();
        }

        $request->validate([
            'status' => 'required|in:active,inactive',
        ], [
            'status.in' => 'status harus diisi dengan aktif atau tidak aktif',
        ]);

        $presence_public_url_open_datetime = $submission->presence_public_url_open_datetime;
        $presence_public_url_close_datetime = $submission->presence_public_url_close_datetime;

        if ($request->status == 'active') {
            $request->validate([
                'tanggal_dan_jam_dibuka' => 'required|date_format:d-m-Y H:i',
                'tanggal_dan_jam_ditutup' => 'required|date_format:d-m-Y H:i|after:tanggal_dan_jam_dibuka',
            ]);

            $presence_public_url_open_datetime = Carbon::parse($request->tanggal_dan_jam_dibuka)->format('Y-m-d H:i:s');
            $presence_public_url_close_datetime = Carbon::parse($request->tanggal_dan_jam_ditutup)->format('Y-m-d H:i:s');
        } else {
            if ($request->tanggal_dan_jam_dibuka) {
                $request->validate([
                    'tanggal_dan_jam_dibuka' => 'required|date_format:d-m-Y H:i',
                ]);

                $presence_public_url_open_datetime = Carbon::parse($request->tanggal_dan_jam_dibuka)->format('Y-m-d H:i:s');
            } else {
                $presence_public_url_open_datetime = null;
            }

            if ($request->tanggal_dan_jam_ditutup) {
                $request->validate([
                    'tanggal_dan_jam_ditutup' => 'required|date_format:d-m-Y H:i|after:tanggal_dan_jam_dibuka',
                ]);

                $presence_public_url_close_datetime = Carbon::parse($request->tanggal_dan_jam_ditutup)->format('Y-m-d H:i:s');
            } else {
                $presence_public_url_close_datetime = null;
            }
        }

        $submission->update([
            'presence_public_url_open_datetime' => $presence_public_url_open_datetime,
            'presence_public_url_close_datetime' => $presence_public_url_close_datetime,
            'presence_public_url_status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Publik URL untuk pengisian daftar kehadiran berhasil diperbaharui');
    }
}
