@extends('layouts.panel.index')
@section('title', 'Detail Hasil Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<style>
    .filepond--credits{
        display: none;
    }
</style>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

@php
    $submission_required_values = $submission->format->required_values;

    $submission_score = 0;

    if ($submission_required_values->count() == 0) {
        $submission_score = 'Tidak ada penilaian pada pengajuan ini';
    } else {
        if ($submission->score) {
            $submission_score = number_format($submission->score, 2) . ' (' . $submission->grade . ')';
        } else  {
            $submission_score = 'Belum ada penilaian';
        }
    }
@endphp
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Hasil Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.admin.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.admin.pengajuan.show.tab-menu')

                    <form action="{{ route('panel.admin.pengajuan.result.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" method="post" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="tab-content text-justify mt-3">
                            <div class="tab-pane fade show active">
                                <div class="alert alert-{{ class_by_status($submission->status) }}">
                                    <h4 class="alert-heading">Status Pengajuan: {{ status_english_to_bahasa($submission->status) }}
                                    </h4>
                                    @if ($submission->status == 'approved' || $submission->status == 'rejected' || $submission->status == 'repeated')
                                        <p>Hasil pengajuan mahasiswa sudah bersifat final dengan status: {{ status_english_to_bahasa($submission->status) }}</p>
                                    @else
                                        <p>Hasil pengajuan mahasiswa belum bersifat final dengan status: {{ status_english_to_bahasa($submission->status) }}</p>
                                    @endif
                                </div>

                                <div class="row mt-3">
                                    <div class="col-6 mt-2">
                                        <div class="form-group">
                                            <label for="bidang_keahlian">Hasil Penilaian</label>
                                            <input type="text" class="form-control"
                                                value="{{ $submission_score }}"
                                                disabled readonly>
                                            <small class="text-muted">{{ $submission->grade_description }}</small>
                                        </div>
                                    </div>
                                    <div class="col-6 mt-2">
                                        <div class="form-group">
                                            <label for="bidang_keahlian">Hasil Rekaman Google Meet</label>
                                            <input type="text" class="form-control"
                                                value="{{ $submission->record_url ? $submission->record_url : 'Rekaman seminar belum dilampirkan' }}"
                                                disabled readonly>
                                        </div>
                                    </div>
                                    @php
                                        $verificator_pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)
                                            ->orderBy('id', 'asc')
                                            ->where('type', 'Pembimbing')
                                            ->get();
                                        $verificator_pengujis = \App\Models\Verificator::where('submission_id', $submission->id)
                                            ->orderBy('id', 'asc')
                                            ->where('type', 'Penguji')
                                            ->get();
                                        $index = 0;
                                    @endphp

                                    @if ($submission->format->is_pembimbing_required)
                                        @if ($verificator_pembimbings->count() > 0)
                                            @foreach ($verificator_pembimbings as $i => $pembimbing)
                                                <input type="hidden" name="verificators[{{ $index }}][id]"
                                                value="{{ $pembimbing->id }}">

                                                @php
                                                    $already_given_value = true;
                                                    $averageValue = 0;

                                                    if ($submission_required_values->count() > 0) {
                                                        $submission_required_values_from_verificators = \App\Models\SubmissionRequiredValue::where('submission_id', $submission->id)->where('verificator_id', $pembimbing->id)->get();

                                                        if ($submission_required_values_from_verificators->count() == $submission_required_values->count()) {
                                                            foreach ($submission_required_values as $submission_required_value) {
                                                                $valueFromVerificator = $submission
                                                                                ->required_values()
                                                                                ->where('required_value_id', $submission_required_value->id)
                                                                                ->where('verificator_id', $pembimbing->id)
                                                                                ->first();
                                                                $averageValue += $valueFromVerificator ? $valueFromVerificator->value * ($valueFromVerificator->required_value->weight / 100) : 0;
                                                            }
                                                        } else {
                                                            $already_given_value = false;
                                                        }
                                                    }
                                                @endphp

                                                <div class="col-12 mt-2">
                                                    Pembimbing {{ $i + 1 }}
                                                    @if ($pembimbing->is_need_to_change_trial_status)
                                                        <span class="badge {{ badge_class($pembimbing->status) }}">
                                                            Seminar/Sidang {{ status_english_to_bahasa($pembimbing->status) }}
                                                        </span>
                                                    @endif
                                                    @if ($submission_required_values->count() > 0)
                                                        @if ($already_given_value)
                                                            <span class="badge text-capitalize ms-2 bg-success">
                                                                Nilai: {{ $averageValue }}
                                                            </span>
                                                        @else
                                                            <span class="badge text-capitalize ms-2 bg-secondary">
                                                                Nilai: Belum diberikan
                                                            </span>
                                                        @endif
                                                    @endif
                                                    @if (!($submission->status == 'rejected' || $submission->status == 'repeated'))
                                                        <span
                                                            class="badge text-capitalize ms-2 {{ badge_class($pembimbing->final_status) }}">
                                                            Final Revisi {{ status_english_to_bahasa($pembimbing->final_status) }}
                                                        </span>
                                                    @endif
                                                    <br />
                                                    <small class="text-muted">{{ $pembimbing->user->name }}</small>
                                                    <textarea class="form-control" readonly disabled>{!! $pembimbing->note !!}</textarea>
                                                    <div class="form-group required mb-4">
                                                        <label class="control-label">Status Final Revisi</label>
                                                        <select class="form-select"
                                                            name="verificators[{{ $index }}][final_status]">
                                                            <option value="pending">Pilih Status</option>
                                                            <option value="approved"
                                                                {{ $pembimbing->final_status == 'approved' ? 'selected' : '' }}>Diterima
                                                            </option>
                                                            <option value="rejected"
                                                                {{ $pembimbing->final_status == 'rejected' ? 'selected' : '' }}>Ditolak
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>

                                            @php $index++ @endphp
                                            @endforeach
                                        @else
                                            <div class="col-12 mt-2">
                                                Pembimbing
                                                <span class="badge bg-warning">
                                                    Belum Dikonfirmasi
                                                </span>
                                                <br />
                                                <small class="text-muted">Belum ada pembimbing</small>
                                            </div>
                                        @endif
                                    @endif

                                    @if ($submission->format->is_penguji_required)
                                        @if ($verificator_pengujis->count() > 0)
                                            @foreach ($verificator_pengujis as $i => $penguji)
                                                <input type="hidden" name="verificators[{{ $index }}][id]"
                                                value="{{ $penguji->id }}">

                                                @php
                                                    $already_given_value = true;
                                                    $averageValue = 0;

                                                    if ($submission_required_values->count() > 0) {
                                                        $submission_required_values_from_verificators = \App\Models\SubmissionRequiredValue::where('submission_id', $submission->id)->where('verificator_id', $penguji->id)->get();

                                                        if ($submission_required_values_from_verificators->count() == $submission_required_values->count()) {
                                                            foreach ($submission_required_values as $submission_required_value) {
                                                                $valueFromVerificator = $submission
                                                                                ->required_values()
                                                                                ->where('required_value_id', $submission_required_value->id)
                                                                                ->where('verificator_id', $penguji->id)
                                                                                ->first();
                                                                $averageValue += $valueFromVerificator ? $valueFromVerificator->value * ($valueFromVerificator->required_value->weight / 100) : 0;
                                                            }
                                                        } else {
                                                            $already_given_value = false;
                                                        }
                                                    }
                                                @endphp

                                                <div class="col-12 mt-2">
                                                    Penguji {{ $i + 1 }}
                                                    @if ($penguji->is_need_to_change_trial_status)
                                                        <span class="badge {{ badge_class($penguji->status) }}">
                                                            Seminar/Sidang {{ status_english_to_bahasa($penguji->status) }}
                                                        </span>
                                                    @endif
                                                    @if ($submission_required_values->count() > 0)
                                                        @if ($already_given_value)
                                                            <span class="badge text-capitalize ms-2 bg-success">
                                                                Nilai: {{ $averageValue }}
                                                            </span>
                                                        @else
                                                            <span class="badge text-capitalize ms-2 bg-secondary">
                                                                Nilai: Belum diberikan
                                                            </span>
                                                        @endif
                                                    @endif
                                                    @if (!($submission->status == 'rejected' || $submission->status == 'repeated'))
                                                        <span
                                                            class="badge text-capitalize ms-2 {{ badge_class($penguji->final_status) }}">
                                                            Final Revisi {{ status_english_to_bahasa($penguji->final_status) }}
                                                        </span>
                                                    @endif
                                                    <br />
                                                    <small class="text-muted">{{ $penguji->user->name }}</small>
                                                    <textarea class="form-control" readonly disabled>{!! $penguji->note !!}</textarea>
                                                    <div class="form-group required mb-4">
                                                        <label class="control-label">Status Final Revisi</label>
                                                        <select class="form-select"
                                                            name="verificators[{{ $index }}][final_status]">
                                                            <option value="pending">Pilih Status</option>
                                                            <option value="approved"
                                                                {{ $penguji->final_status == 'approved' ? 'selected' : '' }}>Diterima
                                                            </option>
                                                            <option value="rejected"
                                                                {{ $penguji->final_status == 'rejected' ? 'selected' : '' }}>Ditolak
                                                            </option>
                                                        </select>
                                                    </div>
                                                </div>
                                            @php $index++; @endphp
                                            @endforeach
                                        @else
                                            <div class="col-12 mt-2">
                                                Penguji
                                                <span class="badge bg-warning">
                                                    Belum Dikonfirmasi
                                                </span>
                                                <br />
                                                <small class="text-muted">Belum ada penguji</small>
                                            </div>
                                        @endif
                                    @endif
                                </div>

                                @if ($submission->format->document_format !== null)
                                    <hr />
                                    <a href="{{ route('panel.pengajuan.download', ['id' => $submission->id]) }}" target="_blank" class="btn my-1 icon btn-sm btn-success">
                                        Download Berita Acara
                                    </a>
                                    <button onClick="showModalPreviewBA(event, '{{$submission->id}}')" class="btn my-1 icon btn-sm btn-primary">
                                       Preview Berita Acara
                                    </button>
                                    <br/>
                                    <small class="text-sm text-muted">Berita acara merupakan sebuah dokumen hasil daripada acara, penilaian dan review dari semua tim.</small>
                                @endif

                                <hr />
                                <h5>Berkas Revisi</h5>
                                <p>Harap mahasiswa melakukan upload file berkas hasil revisi berdasarkan komentar para tim peneliti disini.</p>
                                <div class="form-group">
                                    <label for="berkas_final_revisi">Berkas Revisi Skripsi</label>
                                    <input type="file"
                                        class="form-control @error('berkas_final_revisi') is-invalid @enderror"
                                        id="berkas_final_revisi" name="berkas_final_revisi"
                                        accept="application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document">
                                    @error('berkas_final_revisi')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    @if ($submission->final_document_url !== null)
                                        <span class="text-muted">Terakhir update:
                                            {{ $submission->final_document_created_at ? $submission->final_document_created_at : '-' }}</span>
                                        <br/>
                                        @if (str_contains($submission->final_document_url, 'drive.google.com'))
                                            <a class="badge bg-success" style="cursor: pointer" onclick="showModal('{{ $submission->final_document_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                        @else
                                            <a class="badge bg-info" href="{{ $submission->final_document_url }}">Download</a>
                                        @endif
                                    @else
                                        <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                    @endif
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1" id="btnSubmit">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="show" tabindex="-1" aria-labelledby="showModal" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title white" id="showModal">Lihat</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe width="100%" height="630" src="" ></iframe>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <span class="">Tutup</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <script>
        const btnSubmit = document.getElementById('btnSubmit');
        const showModal = (url) => {
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            iframe.src = `${url}/preview`;
        }

        FilePond.registerPlugin(FilePondPluginFileValidateType);

        const pond = FilePond.create(document.getElementById('berkas_final_revisi'), {
            allowRemove: false,
            allowRevert: false,
            allowReplace: false,
            allowReorder: false,
            // instantUpload: false,
            // chunkUploads: true,
            // chunkSize: 5000,
        });
        pond.setOptions({
            server: {
                url: "{{ route('panel.admin.pengajuan.result.upload', ['type_slug' => $type->slug, 'id' => $submission->id]) }}",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                }
            },
        });

        // when file is added
        pond.on('addfile', (error, file) => {
            btnSubmit.disabled = true;
        })

        // when file is processed
        pond.on('processfile', (error, file) => {
            btnSubmit.disabled = false;
        })

        $("textarea").each(function () {
            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });

        function showModalPreviewBA(e, idBA){
            e.preventDefault();
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            iframe.src = `https://docs.google.com/viewer?embedded=true&url=https://edoxid.untan.ac.id/downloadbaforce/${idBA}`;
            $('#show').modal('show');
        }
    </script>
@endpush
