@extends('layouts.panel.index')
@section('title', 'Tahapan Akhir - ' . $mahasiswa->name)

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
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <h3 class="mt-4">Tahapan Akhir</h3>
                    <p>Untuk tahapan akhir, mahasiswa harus melakukan upload file berkas final yang telah disetujui oleh tim bagian administrasi.</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">

                <div class="card-body">

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">

                            <form action="{{ route('panel.bagian-administrasi.tahapan-akhir.update', [
                                'student_id' => $mahasiswa->id,
                            ]) }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        #1 - <b>Berkas Skripsi</b>
                                        <br/>
                                        <small class="text-muted">
                                            <span class="text-muted">Terakhir update: {{ $final_step_submission_file->final_thesis_file_upload_at }}</span>
                                            @if ($final_step_submission_file->validity_sheet_file_url !== null)
                                                <div class="badge {{ badge_class($final_step_submission_file->final_thesis_file_status) }} text-capitalize me-2">{{ status_english_to_bahasa($final_step_submission_file->final_thesis_file_status) }}</div>
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                            @endif
                                        </small>
                                    </div>

                                    <div class="col-md-4">
                                        <select class="form-select mt-2"
                                            name="final_thesis_file_status">
                                            <option value="pending">Pilih Status</option>
                                            <option value="approved"
                                                {{ $final_step_submission_file->final_thesis_file_status == 'approved' ? 'selected' : '' }}>
                                                Diterima</option>
                                            <option value="rejected"
                                                {{ $final_step_submission_file->final_thesis_file_status == 'rejected' ? 'selected' : '' }}>
                                                Ditolak</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 text-end">
                                        @if ($final_step_submission_file->final_thesis_file_url !== null)
                                            @if (str_contains($final_step_submission_file->final_thesis_file_url, 'drive.google.com'))
                                                <div class="d-flex flex-wrap justify-content-end">
                                                    <a class="badge bg-success me-2" style="cursor: pointer" onclick="showModal('{{ $final_step_submission_file->final_thesis_file_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                    <a class="badge bg-info me-2" style="cursor: pointer" href="{{ $final_step_submission_file->final_thesis_file_url }}">Download</a>
                                                </div>
                                            @else
                                                <a class="badge bg-info" href="{{ $final_step_submission_file->final_thesis_file_url }}">Download</a>
                                            @endif
                                        @else
                                            <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                        @endif
                                    </div>
                                </div>

                                <hr />

                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        #2 - <b>Lembar Pengesahan</b>
                                        <br/>
                                        <small class="text-muted">
                                            <span class="text-muted">Terakhir update: {{ $final_step_submission_file->validity_sheet_file_upload_at }}</span>
                                            @if ($final_step_submission_file->validity_sheet_file_url !== null)
                                                <div class="badge {{ badge_class($final_step_submission_file->validity_sheet_file_status) }} text-capitalize me-2">{{ status_english_to_bahasa($final_step_submission_file->validity_sheet_file_status) }}</div>
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                            @endif
                                        </small>
                                    </div>


                                    <div class="col-md-4">
                                        <select class="form-select mt-2"
                                            name="validity_sheet_file_status">
                                            <option value="pending">Pilih Status</option>
                                            <option value="approved"
                                                {{ $final_step_submission_file->validity_sheet_file_status == 'approved' ? 'selected' : '' }}>
                                                Diterima</option>
                                            <option value="rejected"
                                                {{ $final_step_submission_file->validity_sheet_file_status == 'rejected' ? 'selected' : '' }}>
                                                Ditolak</option>
                                        </select>
                                    </div>

                                    <div class="col-md-4 text-end">
                                        @if ($final_step_submission_file->validity_sheet_file_url !== null)
                                            @if (str_contains($final_step_submission_file->validity_sheet_file_url, 'drive.google.com'))
                                                <div class="d-flex flex-wrap justify-content-end">
                                                    <a class="badge bg-success me-2" style="cursor: pointer" onclick="showModal('{{ $final_step_submission_file->validity_sheet_file_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                    <a class="badge bg-info me-2" style="cursor: pointer" href="{{ $final_step_submission_file->validity_sheet_file_url }}">Download</a>
                                                </div>
                                            @else
                                                <a class="badge bg-info" href="{{ $final_step_submission_file->validity_sheet_file_url }}">Download</a>
                                            @endif
                                        @else
                                            <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                        @endif
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1" id="btnSubmit">Simpan Perubahan</button>
                                </div>
                            </form>
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
                <div class="modal-body" id="modal-body">

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
        const btnSubmit = document.getElementById('btnSubmit');
        const showModal = (url) => {
            const modal = document.getElementById('modal-body');
            modal.innerHTML = '';
            modal.innerHTML = `<iframe width="100%" height="630" src="${url}/preview"></iframe>`;
        }
    </script>
@endpush