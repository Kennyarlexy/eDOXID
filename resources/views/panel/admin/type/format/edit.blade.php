@extends('layouts.panel.index')
@section('title', 'Edit Format ' . $format->name)

@php
function filesize_formatted($size)
{
    $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
    $power = $size > 0 ? floor(log($size, 1024)) : 0;
    return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
}
@endphp

@push('styles')
    <script>
        var indexRequiredFile = {{ $format->required_files->count() }};
        var indexRequiredValue = {{ $format->required_values->count() }};

        const addRequiredValueColumn = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>
                                <input type="text" class="form-control" id="nama" name="requiredValues[${indexRequiredValue}][nama]" placeholder="Nama" required>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="kunci" name="requiredValues[${indexRequiredValue}][kunci]" placeholder="kunci" required>
                            </td>
                            <td>
                                <input type="number" step="0.1" min="0" max="100" class="form-control" id="bobot" name="requiredValues[${indexRequiredValue}][bobot]" placeholder="bobot" required>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="catatan" name="requiredValues[${indexRequiredValue}][catatan]" placeholder="catatan">
                            </td>
                            <td class="text-end">
                                <button type="button" onclick="deleteThisRow(this)" class="btn btn-sm btn-danger">Hapus</button>
                            </td>`;

            document.getElementById("requiredValueBox").appendChild(tr);
            indexRequiredValue++;
        }

        const addRequiredFileColumn = () => {
            const tr = document.createElement('tr');
            tr.innerHTML = `<td>
                                <input type="text" class="form-control" id="nama" name="requiredFiles[${indexRequiredFile}][nama]" placeholder="Nama" required>
                            </td>
                            <td>
                                <input type="text" class="form-control" id="catatan" name="requiredFiles[${indexRequiredFile}][catatan]" placeholder="catatan">
                            </td>
                            <td>
                                <select class="form-select" id="tipe_file" name="requiredFiles[${indexRequiredFile}][tipe_file]" placeholder="tipe file" required>
                                    <option value="all">Semua Tipe</option>
                                    <option value="image">Gambar</option>
                                    <option value="document_word">Dokumen Word</option>
                                    <option value="pdf">PDF</option>
                                </select>
                            </td>
                            <td>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="requiredFiles_${indexRequiredFile}_opsi1" name="requiredFiles[${indexRequiredFile}][dapat_dilihat_tim_peneiliti]" value=true class="form-check-input">
                                        <label for="requiredFiles_${indexRequiredFile}_opsi1">Dapat dilihat tim peneliti</label>
                                    </div>
                                </div>
                                <div class="form-check">
                                    <div class="checkbox">
                                        <input type="checkbox" id="requiredFiles_${indexRequiredFile}_opsi2" name="requiredFiles[${indexRequiredFile}][dapat_diedit_tim_peneliti]" value=true class="form-check-input">
                                        <label for="requiredFiles_${indexRequiredFile}_opsi2">Dapat diedit tim peneliti</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-end">
                                <button type="button" onclick="deleteThisRow(this)" class="btn btn-sm btn-danger">Hapus</button>
                            </td>
                            `;

            document.getElementById("requiredFileBox").appendChild(tr);
            indexRequiredFile++;
        }

        const deleteThisRow = (e) => {
            e.parentNode.parentNode.remove();
        }
    </script>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.type.format.index', $type->slug) }}"
                        class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah Format - Tipe Pengajuan: {{ $type->name }}</h3>
                    <p class="text-subtitle text-muted">{{ $format->name }}</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.type.format.update', ['type' => $type->slug, 'format' => $format]) }}"
                method="post" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="nama" class="control-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                        name="nama" value="{{ $format->name }}" placeholder="Nama" required>
                                    @error('nama')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="control-label">Jadikan Format Utama</label>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="true" class="form-check-input" value=true
                                                {{ $format->is_primary ? 'checked' : '' }}>
                                            <label for="true">Ya</label>
                                        </li>
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="false" class="form-check-input"
                                                value=false {{ !$format->is_primary ? 'checked' : '' }}>
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
                                                        <div
                                                            class="bg-secondary text-white row rounded py-2 justify-content-center align-items-center">
                                                            <div class="col-md-2 text-center align-middle">
                                                                <i class="fas fa-times-circle"
                                                                    onclick="cancelAddDocument()"></i>
                                                            </div>
                                                            <div class="col-md-10 text-start">
                                                                <p class="text-sm text-white mb-0">
                                                                    {{ $format->document_format_name }}</p>
                                                                <p class="text-sm warning mb-0 text-white">
                                                                    {{ filesize_formatted($format->document_format_size) }}
                                                                </p>
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
                                                <input type="checkbox" id="{{ $list_type->id }}"
                                                    name="access_types[{{ $list_type->id }}]" value=true
                                                    class="form-check-input"
                                                    {{ \App\Models\GetTypeAccessPermission::where('format_id', $format->id)->where('type_id', $type->id)->where('access_type_id', $list_type->id)->first() ? 'checked' : '' }}>
                                                <label for="{{ $list_type->id }}">{{ $list_type->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-group mb-4">
                                    <label class="mb-1">Opsi Verifikator Pembimbing</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_pembimbing_required" name="memerlukan_pembimbing" value=true class="form-check-input" {{ $format->is_pembimbing_required ? 'checked' : '' }} onclick="turnOnOffCheckboxIsPembimbingNeedChangeStatusTrial(this)">
                                            <label for="is_pembimbing_required">Apakah pembimbing diperlukan ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_pembimbing_need_change_trial_status" name="apakah_pembimbing_perlu_menyetujui_seminar" value=true class="form-check-input" @if (!$format->is_pembimbing_required) disabled @else {{ $format->is_pembimbing_need_to_change_trial_status ? 'checked' : '' }} @endif>
                                            <label for="is_pembimbing_need_change_trial_status">Apakah pembimbing perlu menyetujui seminar ?</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="mb-1">Opsi Verifikator Penguji</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_penguji_required" name="memerlukan_penguji" value=true class="form-check-input" {{ $format->is_penguji_required ? 'checked' : '' }} onclick="turnOnOffCheckboxIsPengujiNeedChangeStatusTrial(this)">
                                            <label for="is_penguji_required">Apakah penguji diperlukan ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_penguji_need_change_trial_status" name="apakah_penguji_perlu_menyetujui_seminar" value=true class="form-check-input" @if (!$format->is_penguji_required) disabled @else {{ $format->is_penguji_need_to_change_trial_status ? 'checked' : '' }} @endif>
                                            <label for="is_penguji_need_change_trial_status">Apakah penguji perlu menyetujui seminar ?</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="mb-1">Opsi Tambahan</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_kelompok_keahlian_required"
                                                name="memerlukan_kelompok_keahlian"
                                                {{ $format->is_skill_group_required ? 'checked' : '' }} value=true
                                                class="form-check-input">
                                            <label for="is_kelompok_keahlian_required">Memerlukan Kelompok Keahlian
                                                ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_need_trial_schedule"
                                                name="memerlukan_jadwal_sidang" value=true class="form-check-input"
                                                {{ $format->is_need_trial_schedule ? 'checked' : '' }}>
                                            <label for="is_need_trial_schedule">Memerlukan Jadwal Sidang ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="if_pass_use_the_previous_verificators"
                                                name="jika_lulus_menggunakan_verifikator_sebelumnya" value=true
                                                class="form-check-input"
                                                {{ $format->if_approved_next_submission_using_previous_verificators ? 'checked' : '' }}>
                                            <label for="if_pass_use_the_previous_verificators">Pengajuan selanjutnya
                                                menggunakan pihak verifikator pada pengajuan sebelumnya</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="all_lecturer_can_discussion"
                                                name="semua_dosen_bisa_berkomentar" value=true class="form-check-input"
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
                                                    <th>Nama <span class="text-danger">*</span></th>
                                                    <th>Catatan</th>
                                                    <th>Tipe File <span class="text-danger">*</span></th>
                                                    <th>Opsi</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="requiredFileBox">
                                                @foreach ($format->required_files as $i => $requiredFile)
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" id="nama" required
                                                                name="requiredFiles[{{ $i }}][nama]"
                                                                value="{{ $requiredFile->name }}" placeholder="Nama">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" id="catatan"
                                                                name="requiredFiles[{{ $i }}][catatan]"
                                                                value="{{ $requiredFile->note }}" placeholder="catatan">
                                                        </td>
                                                        <td>
                                                            <select class="form-select" id="tipe_file" name="requiredFiles[{{ $i }}][tipe_file]" placeholder="tipe file" required>
                                                                <option value="all" {{ $requiredFile->type_file == 'all' ? 'selected' : '' }}>Semua Tipe</option>
                                                                <option value="image" {{ $requiredFile->type_file == 'image' ? 'selected' : '' }}>Gambar</option>
                                                                <option value="document_word" {{ $requiredFile->type_file == 'document_word' ? 'selected' : '' }}>Dokumen Word</option>
                                                                <option value="pdf" {{ $requiredFile->type_file == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                            </select>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                <div class="checkbox">
                                                                    <input type="checkbox" id="requiredFiles_{{ $j }}_opsi1" name="requiredFiles[{{ $j }}][dapat_dilihat_tim_peneiliti]" value=true class="form-check-input" {{ $requiredFile->is_verificator_can_view ? 'checked' : '' }}>
                                                                    <label for="requiredFiles_{{ $j }}_opsi1">Dapat dilihat tim peneliti</label>
                                                                </div>
                                                            </div>
                                                            <div class="form-check">
                                                                <div class="checkbox">
                                                                    <input type="checkbox" id="requiredFiles_{{ $j }}_opsi2" name="requiredFiles[{{ $j }}][dapat_diedit_tim_peneliti]" value=true class="form-check-input" {{ $requiredFile->is_verificator_can_edit ? 'checked' : '' }}>
                                                                    <label for="requiredFiles_{{ $j }}_opsi2">Dapat diedit tim peneliti</label>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" onclick="deleteThisRow(this)"
                                                                class="btn btn-sm btn-danger">Hapus</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="w-100">
                                        <button type="button" onclick="addRequiredFileColumn()"
                                            class="btn my-3 py-2 w-100 px-3 text-center btn-sm rounded btn-primary">Tambah
                                            Kolom Baru</button>
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
                                                    <th>Nama <span class="text-danger">*</span></th>
                                                    <th>Kunci <span class="text-danger">*</span></th>
                                                    <th>Bobot (0-100) & Total Keseluruhan harus 100% <span class="text-danger">*</span></th>
                                                    <th>Catatan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="requiredValueBox">
                                                @foreach ($format->required_values as $i => $requiredValue)
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" id="nama" required
                                                                name="requiredValues[{{ $i }}][nama]"
                                                                value="{{ $requiredValue->name }}" placeholder="Nama">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" id="kunci" required
                                                                name="requiredValues[{{ $i }}][kunci]"
                                                                value="{{ $requiredValue->key }}" placeholder="kunci">
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.1" min="0" max="100" class="form-control" id="bobot" required
                                                                name="requiredValues[{{ $i }}][bobot]"
                                                                value="{{ $requiredValue->weight }}" placeholder="bobot">
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" id="catatan"
                                                                name="requiredValues[{{ $i }}][catatan]"
                                                                value="{{ $requiredValue->note }}" placeholder="catatan">
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" onclick="deleteThisRow(this)"
                                                                class="btn btn-sm btn-danger">Hapus</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="w-100">
                                        <button type="button" onclick="addRequiredValueColumn()"
                                            class="btn my-3 py-2 w-100 px-3 text-center btn-sm rounded btn-success">Tambah
                                            Kolom Baru</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        var document_format_file = document.getElementById('document_format_file');
        const inputFile = document.getElementById("inputFile");

        const addDocument = () => {
            document_format_file.click();

            const inputFile = document.getElementById("inputFile");
            document_format_file.onchange = evt => {
                const [file] = document_format_file.files
                if (file) {
                    inputFile.innerHTML = `<div class="bg-secondary text-white row rounded py-2 justify-content-center align-items-center">
                                            <div class="col-md-2 text-center align-middle">
                                                <i class="fas fa-times-circle" onclick="cancelAddDocument()"></i>
                                            </div>
                                            <div class="col-md-10 text-start">
                                                <p class="text-sm text-white mb-0">${file.name}</p>
                                                <p class="text-sm warning text-white mb-0">${formatBytes(file.size, 2)}</p>
                                            </div>
                                        </div>`;
                }
            }
        }

        const cancelAddDocument = () => {
            document_format_file.value = '';
            inputFile.innerHTML = `<div class="text-white">
                                    <p class="py-3 text-secondary mb-0" onclick="addDocument()"><u>Browse</u> your file to input your format document</p>
                                </div>`;
            document.getElementById('deletePreviousDocumentFile').value = 1;
        }

        const formatBytes = (bytes, decimals = 2) => {
            if (bytes === 0) return '0 Bytes';

            const k = 1024;
            const dm = decimals < 0 ? 0 : decimals;
            const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];

            const i = Math.floor(Math.log(bytes) / Math.log(k));

            return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
        }

        const turnOnOffCheckboxIsPembimbingNeedChangeStatusTrial = (inpObj) => {
            var is_pembimbing_need_change_trial_status = document.getElementById('is_pembimbing_need_change_trial_status');

            if (inpObj.checked === true) {
                is_pembimbing_need_change_trial_status.disabled = false;
            } else {
                is_pembimbing_need_change_trial_status.disabled = true;
                is_pembimbing_need_change_trial_status.checked = false;
            }
        }

        const turnOnOffCheckboxIsPengujiNeedChangeStatusTrial = (inpObj) => {
            var is_penguji_need_change_trial_status = document.getElementById('is_penguji_need_change_trial_status');

            if (inpObj.checked === true) {
                is_penguji_need_change_trial_status.disabled = false;
            } else {
                is_penguji_need_change_trial_status.disabled = true;
                is_penguji_need_change_trial_status.checked = false;
            }
        }
    </script>
@endpush
