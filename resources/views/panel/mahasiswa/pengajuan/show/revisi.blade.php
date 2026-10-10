@extends('layouts.panel.index')
@section('title', 'Revisi Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<style>
    .filepond--credits{
        display: none;
    }
</style>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
@endpush


@section('main')
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

    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.mahasiswa.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Revisi Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.mahasiswa.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.mahasiswa.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">
                            <h5>Revisi Seminar Pengajuan</h5>
                            <div class="alert alert-{{ result_class_by_status($submission->result_status) }}">
                                <h4 class="alert-heading">Hasil Status Seminar Pengajuan: {{ result_status_english_to_bahasa($submission->result_status) }}</h4>
                                @if ($submission->result_status != \App\Models\Submission::RESULT_PENDING)
                                    <p>Hasil status seminar pengajuan mahasiswa sudah bersifat final dengan status: {{ result_status_english_to_bahasa($submission->result_status) }}</p>
                                    @if ($submission->result_status == \App\Models\Submission::RESULT_REPEATED)
                                        <p>Berikut merupakan rekomendasi tanggal seminar ulang yang direkomendasikan: {{ $submission->recomendation_repeated_schedule }}</p>
                                    @endif
                                @else
                                    <p>Hasil status seminar pengajuan mahasiswa belum bersifat final dengan status: {{ result_status_english_to_bahasa($submission->result_status) }}</p>
                                @endif
                            </div>

                            @if ($submission->result_status == \App\Models\Submission::RESULT_APPROVED_WITH_FIXED)
                                <form action="{{ route('panel.mahasiswa.pengajuan.revisi.update', [
                                    'type_slug' => $type->slug,
                                    'id' => $submission->id
                                ]) }}" method="post" enctype="multipart/form-data">
                                    @csrf
                                    @method('PUT')
                                    <h5>Berkas Revisi</h5>
                                    <p>Harap mahasiswa melakukan upload file berkas hasil revisi berdasarkan komentar para tim peneliti disini.</p>
                                    @if ($submission->status !== 'approved')
                                        <div class="form-group">
                                            <label for="berkas_final_revisi">Berkas Revisi Skripsi</label>
                                            <input type="file"
                                                class="form-control @error('berkas_final_revisi') is-invalid @enderror"
                                                id="berkas_final_revisi" name="berkas_final_revisi"
                                                accept="application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document"
                                                required>
                                            @error('berkas_final_revisi')
                                                <div class="invalid-feedback">
                                                    <i class="bx bx-radio-circle"></i>
                                                    {{ $message }}
                                                </div>
                                            @enderror
                                        </div>
                                    @endif
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
                                </form>
                            @endif
                        </div>
                    </div>
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
                url: "{{ route('panel.mahasiswa.pengajuan.result.upload', ['type_slug' => $type->slug, 'id' => $submission->id]) }}",
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
    </script>
@endpush
