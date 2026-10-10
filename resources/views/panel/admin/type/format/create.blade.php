@extends('layouts.panel.index')
@section('title', 'Tambah Format ' . $type->name)

@push('styles')
<script>
    var indexRequiredFile = 0;
    var indexRequiredValue = 0;

    @if (old('requiredValues'))
        var indexRequiredValue = {{ count(old('requiredValues')) }};
    @endif

    @if (old('requiredFiles'))
        var indexRequiredFile = {{ count(old('requiredFiles')) }};
    @endif

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
                        </td>`;

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
                    <a href="{{ route('panel.admin.type.format.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Tambah Format - Tipe Pengajuan: {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.type.format.store', $type->slug) }}" method="post" enctype="multipart/form-data">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label for="nama" class="control-label">Nama <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" placeholder="Nama" required>
                                    @error('nama')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                <div class="form-group mb-4">
                                    <label class="control-label">Jadikan Format Utama <span class="text-danger">*</span></label>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="true" class="form-check-input" value=true required {{ old('status') == true ? 'checked' : '' }}>
                                            <label for="true">Ya</label>
                                        </li>
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="false" class="form-check-input" value=false required {{ old('status') == false ? 'checked' : '' }}>
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
                                                    Format dokumen digunakan untuk keperluan <code>output/penilaian</code> dan format file
                                                    haruslah <code>doc, atau docx</code> yang sudah disesuaikan dengan <code>Key Meta Penilaian</code>.
                                                </p>

                                                <div class="bg-light text-center rounded" id="inputFile">
                                                    <div class="text-white">
                                                        <p class="py-3 text-secondary mb-0" onclick="addDocument()"><u>Browse</u> your file to input your format document</p>
                                                    </div>
                                                </div>

                                                <!-- File uploader with image preview -->
                                                <input type="file" id="document_format_file" style="display:none" name="document_format_file"
                                                    accept="application/msword, application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-4">
                                    <label class="mb-1">Jika lulus maka mahasiswa akan diberikan hak akses terhadap tipe pengajuan</label>
                                    @foreach ($types as $type)
                                        <div class="form-check">
                                            <div class="checkbox">
                                                <input type="checkbox" id="{{ $type->id }}" name="access_types[{{ $type->id }}]" value=true class="form-check-input">
                                                <label for="{{ $type->id }}">{{ $type->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="form-group mb-4">
                                    <label class="mb-1">Opsi Verifikator Pembimbing</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_pembimbing_required" name="memerlukan_pembimbing" value=true class="form-check-input" {{ old('memerlukan_pembimbing') == true ? 'checked' : '' }} onclick="turnOnOffCheckboxIsPembimbingNeedChangeStatusTrial(this)">
                                            <label for="is_pembimbing_required">Apakah pembimbing diperlukan ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_pembimbing_need_change_trial_status" name="apakah_pembimbing_perlu_menyetujui_seminar" value=true class="form-check-input" @if (old('memerlukan_pembimbing') == false) disabled @else {{ old('apakah_pembimbing_perlu_menyetujui_seminar') == true ? 'checked' : '' }} @endif>
                                            <label for="is_pembimbing_need_change_trial_status">Apakah pembimbing perlu menyetujui seminar ?</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="mb-1">Opsi Verifikator Penguji</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_penguji_required" name="memerlukan_penguji" value=true class="form-check-input" {{ old('memerlukan_penguji') == true ? 'checked' : '' }} onclick="turnOnOffCheckboxIsPengujiNeedChangeStatusTrial(this)">
                                            <label for="is_penguji_required">Apakah penguji diperlukan ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_penguji_need_change_trial_status" name="apakah_penguji_perlu_menyetujui_seminar" value=true class="form-check-input" @if (old('memerlukan_penguji') == false) disabled @else {{ old('apakah_penguji_perlu_menyetujui_seminar') == true ? 'checked' : '' }} @endif>
                                            <label for="is_penguji_need_change_trial_status">Apakah penguji perlu menyetujui seminar ?</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-4">
                                    <label class="mb-1">Opsi Lainnya</label>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_kelompok_keahlian_required" name="memerlukan_kelompok_keahlian" value=true class="form-check-input" {{ old('memerlukan_kelompok_keahlian') == true ? 'checked' : '' }}>
                                            <label for="is_kelompok_keahlian_required">Memerlukan Kelompok Keahlian ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="is_need_trial_schedule" name="memerlukan_jadwal_sidang" value=true class="form-check-input" {{ old('memerlukan_jadwal_sidang') == true ? 'checked' : '' }}>
                                            <label for="is_need_trial_schedule">Memerlukan Jadwal Sidang ?</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="if_pass_use_the_previous_verificators" name="jika_lulus_menggunakan_verifikator_sebelumnya" value=true class="form-check-input" {{ old('jika_lulus_menggunakan_verifikator_sebelumnya') == true ? 'checked' : '' }}>
                                            <label for="if_pass_use_the_previous_verificators">Pengajuan selanjutnya menggunakan pihak verifikator pada pengajuan sebelumnya</label>
                                        </div>
                                    </div>
                                    <div class="form-check">
                                        <div class="checkbox">
                                            <input type="checkbox" id="all_lecturer_can_discussion" name="semua_dosen_bisa_berkomentar" value=true class="form-check-input" {{ old('semua_dosen_bisa_berkomentar') == true ? 'checked' : '' }}>
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
                                                @if (old('requiredFiles'))
                                                    @foreach (old('requiredFiles') as $j => $requiredFile)
                                                        <tr>
                                                            <td>
                                                                <input type="text" class="form-control" id="nama"
                                                                    name="requiredFiles[{{ $j }}][nama]"
                                                                    value="{{ $requiredFile["nama"] }}" placeholder="Nama" required>
                                                            </td>
                                                            <td>
                                                                <input type="text" class="form-control" id="catatan"
                                                                    name="requiredFiles[{{ $j }}][catatan]"
                                                                    value="{{ $requiredFile["catatan"] }}" placeholder="catatan">
                                                            </td>
                                                            <td>
                                                                <select class="form-select" id="tipe_file" name="requiredFiles[{{ $j }}][tipe_file]" placeholder="tipe file" required>
                                                                    <option value="all" {{ $requiredFile["tipe_file"] == 'all' ? 'selected' : '' }}>Semua Tipe</option>
                                                                    <option value="image" {{ $requiredFile["tipe_file"] == 'image' ? 'selected' : '' }}>Gambar</option>
                                                                    <option value="document_word" {{ $requiredFile["tipe_file"] == 'document_word' ? 'selected' : '' }}>Dokumen Word</option>
                                                                    <option value="pdf" {{ $requiredFile["tipe_file"] == 'pdf' ? 'selected' : '' }}>PDF</option>
                                                                </select>
                                                            </td>
                                                            <td>
                                                                <div class="form-check">
                                                                    <div class="checkbox">
                                                                        <input type="checkbox" id="requiredFiles_{{ $j }}_opsi1" name="requiredFiles[{{ $j }}][dapat_dilihat_tim_peneiliti]" value=true class="form-check-input" {{ $requiredFile["dapat_dilihat_tim_peneiliti"] == true ? 'checked' : '' }}>
                                                                        <label for="requiredFiles_{{ $j }}_opsi1">Dapat dilihat tim peneliti</label>
                                                                    </div>
                                                                </div>
                                                                <div class="form-check">
                                                                    <div class="checkbox">
                                                                        <input type="checkbox" id="requiredFiles_{{ $j }}_opsi2" name="requiredFiles[{{ $j }}][dapat_diedit_tim_peneliti]" value=true class="form-check-input" {{ $requiredFile["dapat_diedit_tim_peneliti"] == true ? 'checked' : '' }}>
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
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="w-100">
                                        <button type="button" onclick="addRequiredFileColumn()" class="btn my-3 py-2 w-100 px-3 text-center btn-sm rounded btn-primary">Tambah Kolom Baru</button>
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
                                                    <th>Kunci (Tidak boleh ada yang sama)<span class="text-danger">*</span></th>
                                                    <th>Bobot (0-100) & Total Keseluruhan harus 100% <span class="text-danger">*</span></th>
                                                    <th>Catatan</th>
                                                    <th class="text-end">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody id="requiredValueBox">
                                                @if (old('requiredValues'))
                                                    @foreach (old('requiredValues') as $j => $requiredValue)
                                                    <tr>
                                                        <td>
                                                            <input type="text" class="form-control" id="nama"
                                                                name="requiredValues[{{ $j }}][nama]"
                                                                value="{{ $requiredValue["nama"] }}" placeholder="Nama" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" id="kunci"
                                                                name="requiredValues[{{ $j }}][kunci]"
                                                                value="{{ $requiredValue["kunci"] }}" placeholder="kunci" required>
                                                        </td>
                                                        <td>
                                                            <input type="number" step="0.1" min="0" max="100" class="form-control" id="bobot"
                                                                name="requiredValues[{{ $j }}][bobot]"
                                                                value="{{ $requiredValue["bobot"] }}" placeholder="bobot" required>
                                                        </td>
                                                        <td>
                                                            <input type="text" class="form-control" id="catatan"
                                                                name="requiredValues[{{ $j }}][catatan]"
                                                                value="{{ $requiredValue["catatan"] }}" placeholder="catatan">
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" onclick="deleteThisRow(this)"
                                                                class="btn btn-sm btn-danger">Hapus</button>
                                                        </td>
                                                    </tr>
                                                    @endforeach
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="w-100">
                                        <button type="button" onclick="addRequiredValueColumn()" class="btn my-3 py-2 w-100 px-3 text-center btn-sm rounded btn-success">Tambah Kolom Baru</button>
                                    </div>
                                </div>
                            </div>
                            <div class="col-sm-12 d-flex justify-content-end">
                                <button type="submit"
                                    class="btn btn-primary me-1 mb-1">Submit</button>
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
                                                <p class="text-sm warning mb-0 text-white">${formatBytes(file.size, 2)}</p>
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
