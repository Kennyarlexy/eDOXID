<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityUserLog;
use App\Models\Format;
use App\Models\GetTypeAccessPermission;
use App\Models\MahasiswaTypeAccessPermission;
use App\Models\RequiredFile;
use App\Models\RequiredValue;
use App\Models\Type;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FormatController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($type_slug)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $formats = Format::where('type_id', $type->id)->orderBy('id', 'desc')->get();
        $types = Type::all();
        return view('panel.admin.type.format.index', compact('type', 'formats', 'types'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($type_slug)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $types = Type::where('id', '!=', $type->id)->get();
        return view('panel.admin.type.format.create', compact('type', 'types'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $type_slug)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required'
        ]);

        if (Type::where('id', $type->id)->where('name', $request->nama)->count() > 0) {
            return redirect()->back()->with('failed', 'Nama format sudah ada sebelumnya')->withInput();;
        }

        // check if required values are filled
        if ($request->requiredValues) {
            $request->validate([
                'requiredValues.*.nama' => 'required|string|max:255',
                'requiredValues.*.kunci' => 'required|string|max:255',
                'requiredValues.*.bobot' => 'required|numeric|min:0|max:100',
            ]);

            $required_value_keys = [];
            $weightSum = 0;
            foreach($request->requiredValues as $requiredValue) {
                $weightSum += $requiredValue['bobot'];
                $required_value_keys[] = $requiredValue['kunci'];
            }

            if ($weightSum != 100) {
                return redirect()->back()->with('failed', 'Total bobot harus 100%')->withInput();;
            }

            if (count(array_unique($required_value_keys)) != count($required_value_keys)) {
                return redirect()->back()->with('failed', 'Kunci penilaian tidak boleh ada yang sama')->withInput();;
            }
        }

        // check if required files are filled
        if ($request->requiredFiles) {
            $request->validate([
                'requiredFiles.*.nama' => 'required|string|max:255',
                'requiredFiles.*.kunci' => 'required|string|max:255',
                'requiredFiles.*.tipe_file' => 'required|string|max:255',
            ]);

            $required_file_keys = [];
            foreach($request->requiredFiles as $requiredFile) {
                $required_file_keys[] = $requiredFile['kunci'];
            }

            if (count(array_unique($required_file_keys)) != count($required_file_keys)) {
                return redirect()->back()->with('failed', 'Kunci file tidak boleh ada yang sama')->withInput();;
            }
        }

        $document_format = null;
        $document_format_name = null;
        $document_format_size = null;

        if ($request->has('document_format_file')) {
            $request->validate([
                'document_format_file' => 'mimes:doc,pdf,docx,zip',
            ]);

            $document_format = $type->slug . '-' . Str::slug($request->nama) . '-' . time() . '.' . $request->document_format_file->getClientOriginalExtension();
            $document_format_name = $request->document_format_file->getClientOriginalName();
            $document_format_size = $request->document_format_file->getSize();
            $request->document_format_file->storeAs('format_documents', $document_format, 'public');
        }

        if ($request->status == "true") {
            Format::where('type_id', $type->id)->update(['is_primary' => false]);
        }

        $pembimbing_perlu_menyetujui_seminar = 0;
        $penguji_perlu_menyetujui_semianar = 0;

        if ($request->memerlukan_pembimbing) {
            $pembimbing_perlu_menyetujui_seminar = $request->apakah_pembimbing_perlu_menyetujui_seminar == "true" ? 1 : 0;
        }

        if ($request->memerlukan_penguji) {
            $penguji_perlu_menyetujui_semianar = $request->apakah_penguji_perlu_menyetujui_seminar == "true" ? 1 : 0;
        }

        $format = Format::create([
            'name' => $request->nama,
            'is_primary' => $request->status == "true" ? 1 : 0,
            'document_format' => $document_format,
            'document_format_name' => $document_format_name,
            'document_format_size' => $document_format_size,
            'is_pembimbing_required' => $request->memerlukan_pembimbing == "true" ? 1 : 0,
            'is_penguji_required' => $request->memerlukan_penguji == "true" ? 1 : 0,
            'is_skill_group_required' => $request->memerlukan_kelompok_keahlian == "true" ? 1 : 0,
            'is_tata_usaha_required' => 1,
            'is_additional_format_required' => $request->memerlukan_format_tambahan == "true" ? 1 : 0,
            'is_need_trial_schedule' => $request->memerlukan_jadwal_sidang == "true" ? 1 : 0,
            'is_all_lecturers_can_consultations' => $request->semua_dosen_bisa_berkomentar == "true" ? 1 : 0,
            'if_approved_next_submission_using_previous_verificators' => $request->jika_lulus_menggunakan_verifikator_sebelumnya == "true" ? 1 : 0,
            'type_id' => $type->id,
            'is_pembimbing_need_to_change_trial_status' => $pembimbing_perlu_menyetujui_seminar,
            'is_penguji_need_to_change_trial_status' => $penguji_perlu_menyetujui_semianar,
        ]);

        if ($request->access_types) {
            foreach($request->access_types as $key => $access_type) {
                if($access_type == "true") {
                    GetTypeAccessPermission::create([
                        'type_id' => $type->id,
                        'access_type_id' => $key,
                        'format_id' => $format->id
                    ]);
                }
            }
        }

        if ($request->requiredFiles) {
            foreach($request->requiredFiles as $requiredFile) {
                RequiredFile::create([
                    'name' => $requiredFile['nama'],
                    'key' => $requiredFile['kunci'],
                    'note' => $requiredFile['catatan'],
                    'type_file' => $requiredFile['tipe_file'],
                    'is_verificator_can_edit' => $requiredFile['dapat_diedit_tim_peneiliti'] == "true" ? 1 : 0,
                    'is_verificator_can_view' => $requiredFile['dapat_dilihat_tim_peneiliti'] == "true" ? 1 : 0,
                    'format_id' => $format->id
                ]);
            }
        }

        if ($request->requiredValues) {
            foreach($request->requiredValues as $requiredValue) {
                RequiredValue::create([
                    'name' => $requiredValue['nama'],
                    'key' => $requiredValue['kunci'],
                    'weight' => $requiredValue['bobot'],
                    'note' => $requiredValue['catatan'],
                    'format_id' => $format->id
                ]);
            }
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menambah Format baru dengan nilai id: ' . $format->id,
        ]);

        return redirect()->route('panel.admin.type.format.index', $type->slug)->with('success', 'Format berhasil ditambahkan');
    }

    public function show($type_slug, Format $format)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $types = Type::where('id', '!=', $type->id)->get();
        return view('panel.admin.type.format.show', compact('type', 'format', 'types'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($type_slug, Format $format)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        $types = Type::where('id', '!=', $type->id)->get();
        return view('panel.admin.type.format.edit', compact('type', 'format', 'types'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $type_slug, Format $format)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        if ($format->submissions->count() > 0) {
            return redirect()->back()->with('failed', 'Format tidak bisa dihapus karena memiliki pengajuan');
        }

        $request->validate([
            'nama' => 'required|string|max:255',
            'status' => 'required',
        ]);

        if (Type::where('id', $type->id)->where('name', $request->nama)->where('id', '!=', $format->id)->count() > 0) {
            return redirect()->back()->with('failed', 'Nama format sudah ada sebelumnya');
        }

        // check if required values are filled
        if ($request->requiredValues) {
            $request->validate([
                'requiredValues.*.nama' => 'required|string|max:255',
                'requiredValues.*.kunci' => 'required|string|max:255',
                'requiredValues.*.bobot' => 'required|numeric|min:0|max:100',
            ]);

            $required_value_keys = [];
            $weightSum = 0;
            foreach($request->requiredValues as $requiredValue) {
                $weightSum += $requiredValue['bobot'];
                $required_value_keys[] = $requiredValue['kunci'];
            }

            if ($weightSum != 100) {
                return redirect()->back()->with('failed', 'Total bobot harus 100%');
            }

            if (count(array_unique($required_value_keys)) != count($required_value_keys)) {
                return redirect()->back()->with('failed', 'Kunci penilaian tidak boleh ada yang sama');
            }
        }

        // check if required files are filled
        if ($request->requiredFiles) {
            $request->validate([
                'requiredFiles.*.nama' => 'required|string|max:255',
                'requiredFiles.*.kunci' => 'required|string|max:255',
                'requiredFiles.*.tipe_file' => 'required|string|max:255',
            ]);

            $required_file_keys = [];
            foreach($request->requiredFiles as $requiredFile) {
                $required_file_keys[] = $requiredFile['kunci'];
            }

            if (count(array_unique($required_file_keys)) != count($required_file_keys)) {
                return redirect()->back()->with('failed', 'Kunci file tidak boleh ada yang sama');
            }
        }

        $document_format = $format->document_format;
        $document_format_name = $format->document_format_name;
        $document_format_size = $format->document_format_size;

        if ($request->deletePreviousDocumentFile == 1) {
            $document_format = null;
            $document_format_name = null;
            $document_format_size = null;

            if (!$request->has('document_format_file')) {
                Storage::disk('public')->delete('format_documents/' . $format->document_format);
            }
        }

        if ($request->has('document_format_file')) {
            $request->validate([
                'document_format_file' => 'mimes:doc,pdf,docx,zip',
            ]);

            $document_format = $type->slug . '-' . Str::slug($request->nama) . '-' . time() . '.' . $request->document_format_file->getClientOriginalExtension();
            $document_format_name = $request->document_format_file->getClientOriginalName();
            $document_format_size = $request->document_format_file->getSize();
            $request->document_format_file->storeAs('format_documents', $document_format, 'public');
        }

        if ($request->status == true && $format->is_primary == false) {
            Format::where('type_id', $type->id)->update(['is_primary' => false]);
        }

        $pembimbing_perlu_menyetujui_seminar = 0;
        $penguji_perlu_menyetujui_seminar = 0;

        if ($request->memerlukan_pembimbing) {
            $pembimbing_perlu_menyetujui_seminar = $request->apakah_pembimbing_perlu_menyetujui_seminar == true ? 1 : 0;
        }

        if ($request->memerlukan_penguji) {
            $penguji_perlu_menyetujui_seminar = $request->apakah_penguji_perlu_menyetujui_seminar == true ? 1 : 0;
        }

        $format->update([
            'name' => $request->nama,
            'is_primary' => $request->status == true ? 1 : 0,
            'document_format' => $document_format,
            'document_format_name' => $document_format_name,
            'document_format_size' => $document_format_size,
            'type_id' => $type->id,
            'is_pembimbing_required' => $request->memerlukan_pembimbing == true ? 1 : 0,
            'is_penguji_required' => $request->memerlukan_penguji == true ? 1 : 0,
            'is_skill_group_required' => $request->memerlukan_kelompok_keahlian == true ? 1 : 0,
            'is_tata_usaha_required' => 1,
            'is_all_lecturers_can_consultations' => $request->semua_dosen_bisa_berkomentar == true? 1 : 0,
            'if_approved_next_submission_using_previous_verificators' => $request->jika_lulus_menggunakan_verifikator_sebelumnya == true? 1 : 0,
            'is_pembimbing_need_to_change_trial_status' => $pembimbing_perlu_menyetujui_seminar,
            'is_penguji_need_to_change_trial_status' => $penguji_perlu_menyetujui_seminar,
        ]);

        $format->get_type_access_permission()->delete();
        if ($request->access_types) {
            foreach($request->access_types as $key => $access_type) {
                if($access_type == true) {
                    GetTypeAccessPermission::create([
                        'type_id' => $type->id,
                        'access_type_id' => $key,
                        'format_id' => $format->id
                    ]);
                }
            }
        }

        $format->required_files()->delete();
        if ($request->requiredFiles) {
            foreach($request->requiredFiles as $requiredFile) {
                RequiredFile::create([
                    'name' => $requiredFile['nama'],
                    'key' => $requiredFile['kunci'],
                    'note' => $requiredFile['catatan'],
                    'type_file' => $requiredFile['tipe_file'],
                    'is_verificator_can_edit' => $requiredFile['dapat_diedit_tim_peneiliti'] == "true" ? 1 : 0,
                    'is_verificator_can_view' => $requiredFile['dapat_dilihat_tim_peneiliti'] == "true" ? 1 : 0,
                    'format_id' => $format->id
                ]);
            }
        }

        $format->required_values()->delete();
        if ($request->requiredValues) {
            foreach($request->requiredValues as $requiredValue) {
                RequiredValue::create([
                    'name' => $requiredValue['nama'],
                    'key' => $requiredValue['kunci'],
                    'weight' => $requiredValue['bobot'],
                    'note' => $requiredValue['catatan'],
                    'format_id' => $format->id
                ]);
            }
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui Format dengan nilai id: ' . $format->id,
        ]);

        return redirect()->back()->with('success', 'Format berhasil diperbarui');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($type_slug, Format $format)
    {
        $type = Type::where('slug', $type_slug)->firstOrFail();
        if ($format->submissions->count() > 0) {
            return redirect()->back()->with('failed', 'Format tidak bisa dihapus karena memiliki pengajuan');
        }
        if ($format->is_primary && $type->status != "inactive") {
            return redirect()->back()->with('failed', 'Format utama tidak dapat dihapus, dikarenakan merupakan format utama dan type tidak dalam keadaan inactive');
        }

        if ($format->document_format) {
            Storage::disk('public')->delete('format_documents/' . $format->document_format);
        }

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Menghapus Format dengan nilai id: ' . $format->id,
        ]);

        $format->delete();
        return redirect()->back()->with('success', 'Format berhasil dihapus');
    }

    public function changeToPrimary($format_id)
    {
        $format = Format::where('id', $format_id)->firstOrFail();

        Format::where('is_primary', 1)->where('type_id', $format->type_id)->update([
            'is_primary' => 0
        ]);

        $format->update([
            'is_primary' => 1
        ]);

        ActivityUserLog::create([
            'user_id' => auth()->user()->id,
            'details' => 'Memperbarui status utama Format dengan nilai id: ' . $format->id,
        ]);

        return redirect()->back()->with('success', 'Format berhasil diperbarui menjadi format utama');
    }
}
