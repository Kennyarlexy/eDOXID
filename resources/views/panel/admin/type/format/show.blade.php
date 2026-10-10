@extends('layouts.panel.index')
@section('title', 'Detail Format ' . $format->name)

@php
function filesize_formatted($size)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}
@endphp

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.type.format.index', $type->slug) }}"
                        class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Format - Tipe Pengajuan: {{ $type->name }}</h3>
                    <p class="text-subtitle text-muted">{{ $format->name }}</p>
                </div>
            </div>
        </div>
        <section class="section">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group required mb-4">
                                <label for="nama" class="control-label">Nama</label>
                                <input type="text" class="form-control" value="{{ $format->name }}" placeholder="Nama"
                                    disabled>
                            </div>

                            <div class="form-group required mb-4">
                                <label class="control-label">Jadikan Format Utama</label>
                                <ul class="list-unstyled mb-0">
                                    <li class="d-inline-block me-2 mb-1">
                                        <input type="radio" name="status" id="true" class="form-check-input" value=true
                                            disabled {{ $format->is_primary ? 'checked' : '' }}>
                                        <label for="true">Ya</label>
                                    </li>
                                    <li class="d-inline-block me-2 mb-1">
                                        <input type="radio" name="status" id="false" class="form-check-input" value=false
                                            disabled {{ !$format->is_primary ? 'checked' : '' }}>
                                        <label for="false">Tidak</label>
                                    </li>
                                </ul>
                            </div>

                            <div class="form-group mb-4">
                                <label>Format Dokumen</label>
                                <div class="card">
                                    <div class="card-content">
                                        <div class="card-body">
                                            <p class="card-text">
                                                Format dokumen digunakan untuk keperluan <code>output/penilaian</code>
                                                dan format file
                                                haruslah <code>doc, atau docx</code> yang sudah disesuaikan dengan
                                                <code>Key Meta Penilaian</code>.
                                            </p>

                                            <div class="bg-light text-center rounded" id="inputFile">
                                                @if ($format->document_format)
                                                    <div class="bg-secondary text-white row rounded py-2 justify-content-center align-items-center">
                                                        <div class="text-start">
                                                            <p class="text-sm text-white mb-0">
                                                                {{ $format->document_format_name }}</p>
                                                            <p class="text-sm warning mb-0 text-white">
                                                                {{ filesize_formatted($format->document_format_size) }}
                                                            </p>
                                                            <a class="badge bg-info" href="{{ asset('/storage/format_documents/' . $format->document_format) }}">
                                                                <i class="fa fa-download"></i> Download
                                                            </a>
                                                        </div>
                                                    </div>
                                                @else
                                                    <div class="text-white">
                                                        <p class="py-3 text-secondary mb-0" onclick="addDocument()">
                                                            <u>Browse</u> your file to input your format document
                                                        </p>
                                                    </div>
                                                @endif
                                            </div>

                                            <!-- File uploader with image preview -->
                                            <input type="file" id="document_format_file" style="display:none"
                                                name="document_format_file"
                                                accept="application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document">

                                            <input type="hidden" name="deletePreviousDocumentFile"
                                                id="deletePreviousDocumentFile" value=0 />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="mb-1">Jika lulus maka mahasiswa akan diberikan hak akses
                                    terhadap tipe pengajuan</label>
                                @foreach ($types as $list_type)
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="{{ $list_type->id }}" disabled value=true
                                                class="form-check-input"
                                                {{ \App\Models\GetTypeAccessPermission::where('format_id', $format->id)->where('type_id', $type->id)->where('access_type_id', $list_type->id)->first()? 'checked': '' }}>
                                            <label for="{{ $list_type->id }}">{{ $list_type->name }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-1">Opsi Verifikator Pembimbing</label>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_pembimbing_required" name="memerlukan_pembimbing" value=true class="form-check-input" {{ $format->is_pembimbing_required ? 'checked' : '' }} disabled>
                                        <label for="is_pembimbing_required">Apakah pembimbing diperlukan ?</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_pembimbing_need_change_trial_status" name="apakah_pembimbing_perlu_menyetujui_seminar" value=true class="form-check-input" {{ $format->is_pembimbing_need_to_change_trial_status ? 'checked' : '' }} disabled>
                                        <label for="is_pembimbing_need_change_trial_status">Apakah pembimbing perlu menyetujui seminar ?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group mb-4">
                                <label class="mb-1">Opsi Verifikator Penguji</label>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_penguji_required" name="memerlukan_penguji" value=true class="form-check-input" {{ $format->is_penguji_required ? 'checked' : '' }} disabled>
                                        <label for="is_penguji_required">Apakah penguji diperlukan ?</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_penguji_need_change_trial_status" name="apakah_penguji_perlu_menyetujui_seminar" value=true class="form-check-input"{{ $format->is_penguji_need_to_change_trial_status ? 'checked' : '' }} disabled>
                                        <label for="is_penguji_need_change_trial_status">Apakah penguji perlu menyetujui seminar ?</label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label class="mb-1">Opsi Tambahan</label>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_kelompok_keahlian_required"
                                            disabled
                                            {{ $format->is_skill_group_required ? 'checked' : '' }} value=true
                                            class="form-check-input">
                                        <label for="is_kelompok_keahlian_required">Memerlukan Kelompok Keahlian
                                            ?</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="is_need_trial_schedule" disabled
                                            value=true class="form-check-input"
                                            {{ $format->is_need_trial_schedule ? 'checked' : '' }}>
                                        <label for="is_need_trial_schedule">Memerlukan Jadwal Sidang ?</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="if_pass_use_the_previous_verificators" value=true disabled
                                            class="form-check-input"
                                            {{ $format->if_approved_next_submission_using_previous_verificators ? 'checked' : '' }}>
                                        <label for="if_pass_use_the_previous_verificators">Pengajuan selanjutnya
                                            menggunakan pihak verifikator pada pengajuan sebelumnya</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="all_lecturer_can_discussion" disabled
                                            value=true class="form-check-input"
                                            {{ $format->is_all_lecturers_can_consultations ? 'checked' : '' }}>
                                        <label for="all_lecturer_can_discussion">Semua dosen bisa berkomentar</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <div class="mb-4">
                                <div class="w-100">
                                    <div class="divider">
                                        <div class="divider-text">File Yang dibutuhkan</div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Catatan</th>
                                                <th>Tipe File</th>
                                                <th>Opsi</th>
                                            </tr>
                                        </thead>
                                        <tbody id="requiredFileBox">
                                            @foreach ($format->required_files as $i => $requiredFile)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" id="nama" disabled
                                                            value="{{ $requiredFile->name }}" placeholder="Nama">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" id="catatan" disabled
                                                            value="{{ $requiredFile->note }}" placeholder="catatan">
                                                    </td>
                                                    <td>
                                                        <select class="form-select" id="tipe_file" disabled
                                                            placeholder="tipe file" required>
                                                            <option value="all"
                                                                {{ $requiredFile->type_file == 'all' ? 'selected' : '' }}>
                                                                Semua Tipe</option>
                                                            <option value="image"
                                                                {{ $requiredFile->type_file == 'image' ? 'selected' : '' }}>
                                                                Gambar</option>
                                                            <option value="document_word"
                                                                {{ $requiredFile->type_file == 'document_word' ? 'selected' : '' }}>
                                                                Dokumen Word</option>
                                                            <option value="pdf"
                                                                {{ $requiredFile->type_file == 'pdf' ? 'selected' : '' }}>
                                                                PDF</option>
                                                        </select>
                                                    </td>
                                                    <td>
                                                        <div class="form-check">
                                                            <div class="checkbox">
                                                                <input type="checkbox" required disabled class="form-check-input" {{ $requiredFile->is_verificator_can_view ? 'checked' : '' }}>
                                                                <label>Dapat dilihat tim peneliti</label>
                                                            </div>
                                                        </div>
                                                        <div class="form-check">
                                                            <div class="checkbox">
                                                                <input type="checkbox" required disabled value=true class="form-check-input" {{ $requiredFile->is_verificator_can_edit ? 'checked' : '' }}>
                                                                <label>Dapat diedit tim peneliti</label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="mb-4">
                                <div class="w-100">
                                    <div class="divider">
                                        <div class="divider-text">Penilaian Yang dibutuhkan</div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered">
                                        <thead>
                                            <tr>
                                                <th>Nama</th>
                                                <th>Kunci</th>
                                                <th>Bobot (0-100)</th>
                                                <th>Catatan</th>
                                            </tr>
                                        </thead>
                                        <tbody id="requiredValueBox">
                                            @foreach ($format->required_values as $i => $requiredValue)
                                                <tr>
                                                    <td>
                                                        <input type="text" class="form-control" disabled
                                                            value="{{ $requiredValue->name }}" placeholder="Nama">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" disabled
                                                            value="{{ $requiredValue->key }}" placeholder="kunci">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" disabled
                                                            value="{{ $requiredValue->weight }}" placeholder="bobot">
                                                    </td>
                                                    <td>
                                                        <input type="text" class="form-control" disabled
                                                            value="{{ $requiredValue->note }}" placeholder="catatan">
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        var document_format_file = document.getElementById('document_format_file');

        const formatBytes = (bytes, decimals = 2) => {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }
    </script>
@endpush
