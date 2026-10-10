@extends('layouts.panel.index')
@section('title', 'Detail Revisi Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
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
                    <a href="{{ route('panel.bagian-administrasi.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Revisi Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.bagian-administrasi.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.bagian-administrasi.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">
                            <h5>Hasil Seminar Pengajuan</h5>
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
                                <hr />
                                <h5>Berkas Revisi</h5>
                                <p>Mahasiswa melakukan upload file berkas hasil revisi berdasarkan komentar para tim peneliti disini.</p>
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
    <script>
        const showModal = (url) => {
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            iframe.src = `${url}/preview`;
        }

        $("textarea").each(function () {
            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });
    </script>
@endpush
