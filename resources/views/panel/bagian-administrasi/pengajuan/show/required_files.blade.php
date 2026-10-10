@extends('layouts.panel.index')
@section('title', 'Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<style>
    .load-more {
        max-height: 132px;
        overflow: hidden;
        text-align: justify;
    }
</style>
@endpush

@section('main')
<div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.bagian-administrasi.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Pengajuan {{ $submission->type->name }}</h3>
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

                            @php
                                if(in_array($type->slug, ['proposal', 'sidang-akhir'])){
                                    echo "<div style='font-weight:bold;margin:1rem 1rem'>Banyak kehadiran mahasiswa ini untuk ".$type->name." : $banyakKehadiran</div>"; 
                                }
                                    
                                @endphp
                                
                                <form action="{{ route('panel.bagian-administrasi.pengajuan.required_files.update', [
                                    'type_slug' => $submission->type->slug,
                                    'id' => $submission->id
                                    ]) }}" method="post" id="formSubmit">

                                    @csrf
                                    @method('PUT')

                                    <input type="hidden" name="catatan_penolakan" id="hiddenRejectedNote" />

                                    @if ($submission->format->required_files->count() > 0)
                                        @foreach ($submission->required_files as $i => $submission_required_file)
                                            @php
                                                $accepted_file_types = 'image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.slideshow,application/vnd.openxmlformats-officedocument.presentationml.presentation,zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed';
                                                $accepted_file_type_noted = 'File dapat berupa: Gambar, PDF, Word, Powerpoint, Slideshow, Presentation, Zip, dan File lainnya';
                                                if ($submission_required_file->required_file->type_file == 'document_word') {
                                                    $accepted_file_types = 'application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                                                    $accepted_file_type_noted = 'File hanya dapat berupa: Word';
                                                } else if ($submission_required_file->required_file->type_file == 'image') {
                                                    $accepted_file_types = 'image/*';
                                                    $accepted_file_type_noted = 'File hanya dapat berupa: Gambar';
                                                } else if ($submission_required_file->required_file->type_file == 'pdf') {
                                                    $accepted_file_types = 'application/pdf';
                                                    $accepted_file_type_noted = 'File hanya dapat berupa: PDF';
                                                }
                                            @endphp

                                            <div class="row align-items-center">

                                                <div class="col-md-4">
                                                    #{{ $i + 1 }} - <b>{{ $submission_required_file->required_file->name }}</b>
                                                    <br/>
                                                    <small class="text-muted">
                                                        <span class="text-muted">Terakhir update: {{ $submission_required_file->updated_at->format('d M Y H:i') }}</span>
                                                        @if ($submission_required_file->url !== null)
                                                            <div class="badge {{ badge_class($submission_required_file->status) }} text-capitalize me-2">{{ status_english_to_bahasa($submission_required_file->status) }}</div>
                                                        @else
                                                            <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                                        @endif
                                                    </small>
                                                </div>

                                                <div class="col-md-4">
                                                    <div class="form-group required">
                                                        <select class="form-select" name="required_file_status[{{ $submission_required_file->required_file_id }}]" id="required_file_status_{{ $i }}">
                                                            <option value="pending">Pilih Status</option>
                                                            <option value="approved" {{ $submission_required_file->status == "approved" ? 'selected' : '' }}>Diterima</option>
                                                            <option value="rejected" {{ $submission_required_file->status == "rejected" ? 'selected' : '' }}>Ditolak</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div class="col-md-4 text-end">
                                                    @if ($submission_required_file->url !== null)
                                                        @if (str_contains($submission_required_file->url, 'drive.google.com'))
                                                            <div class="d-flex flex-wrap justify-content-end">
                                                                <a class="badge bg-success me-2" style="cursor: pointer" onclick="showModal('{{ $submission_required_file->url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                                <a class="badge bg-info me-2" style="cursor: pointer" href="{{ $submission_required_file->url }}">Download</a>
                                                            </div>
                                                        @else
                                                            <a class="badge bg-info" href="{{ $submission_required_file->url }}">Download</a>
                                                        @endif
                                                    @else
                                                        <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                                    @endif
                                                </div>

                                            </div>

                                            <hr/>
                                        @endforeach

                                        <div class="row align-items-center">
                                            <div class="col-md-4">
                                                #{{ $i + 2 }} - Foto Tandatangan
                                                <br/>
                                                <small class="text-muted">
                                                    @if ($submission->student->signature_image !== null)
                                                        <div class="badge {{ badge_class($submission->student->signature_status) }} text-capitalize me-2">{{ status_english_to_bahasa($submission->student->signature_status) }}</div>
                                                    @else
                                                        <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                                    @endif
                                                </small>
                                            </div>

                                            <div class="col-md-4">
                                                <div class="form-group required">
                                                    <select class="form-select" name="status_tandatangan" id="status_tandatangan">
                                                        <option value="pending" {{ $submission->student->signature_status == "approved" ? 'selected' : '' }}>Pilih Status</option>
                                                        <option value="approved" {{ $submission->student->signature_status == "approved" ? 'selected' : '' }}>Diterima</option>
                                                        <option value="rejected" {{ $submission->student->signature_status == "rejected" ? 'selected' : '' }}>Ditolak</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-4 text-end">
                                                @if ($submission->student->signature_image !== null)
                                                    <a href="{{ asset('storage/signatures/' . $submission->student->signature_image) }}" target="_blank" class="badge bg-success">Lihat & Download Berkas</a>
                                                @else
                                                    <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                                @endif
                                            </div>
                                        </div>

                                        <hr/>

                                        <div class="form-group mt-4 text-end">
                                            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#onSubmit" onclick="showOnSubmitModal()">
                                                Simpan Perubahan
                                            </button>
                                        </div>
                                    @endif
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

    <div class="modal fade" id="onSubmit" tabindex="-1" aria-labelledby="onSubmitModal" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title white" id="onSubmitModal">Apakah anda yakin?</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body" id="onSubmitModalBody">

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <span class="">Tutup</span>
                    </button>
                    <button type="button" class="btn btn-primary" onclick="onLoading(this)">
                        <span class="">Yakin</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const loadMore = () => {
        const loadMoreText = document.getElementById('load-more-text');
        const loadMoreButton = document.getElementById('load-more-button');

        loadMoreText.classList.toggle('load-more');
        if (loadMoreText.classList.contains('load-more')) {
            loadMoreButton.innerText = 'Tampilkan lebih banyak';
        } else {
            loadMoreButton.innerText = 'Tampilkan lebih sedikit';
        }
    }

    const isRejectedStatusExists = () => {
        let total_submission_required_files = {{ $submission->format->required_files->count() }};

        for (let i = 0;i<total_submission_required_files;i++) {
            let submission_required_files_by_index = document.getElementById(`required_file_status_${i}`);

            if (submission_required_files_by_index.options[submission_required_files_by_index.selectedIndex].value == 'rejected') {
                return true;
            }
        }

        let status_tandatangan = document.getElementById('status_tandatangan');

        if (status_tandatangan.options[status_tandatangan.selectedIndex].value == 'rejected') {
            return true;
        }
    }

    const btnSubmit = document.getElementById('btnSubmit');
    const showModal = (url) => {
        const modal = document.getElementById('modal-body');
        modal.innerHTML = '';
        modal.innerHTML = `<iframe width="100%" height="630" src="${url}/preview"></iframe>`;
    }

    const showOnSubmitModal = () => {
        const onSubmitModalBody = document.getElementById('onSubmitModalBody');
        onSubmitModalBody.innerHTML = '';

        onSubmitModalBody.innerHTML += `<p>Apakah anda yakin untuk melakukan penyimpanan perubahan yang terjadi?</p>`;

        if (isRejectedStatusExists()) {
            onSubmitModalBody.innerHTML += `<div class="form-group mb-3">
                                                <label for="onSubmitModalRejectedNote" class="form-label">Tambahkan catatan alasan penolakan pada berkas yang ditolak <span class="text-danger">*</span></label>
                                                <textarea class="form-control" id="onSubmitModalRejectedNote" rows="3"></textarea>
                                            </div>`;
        }
    }

    const onLoading = (e) => {
        const onSubmitModalRejectedNote = document.getElementById('onSubmitModalRejectedNote');

        if (isRejectedStatusExists()) {
            if (onSubmitModalRejectedNote.value == '') {
                alert('Tambahkan catatan alasan penolakan pada berkas yang ditolak.');
                return;
            }
        }

        const hiddenRejectedNote = document.getElementById('hiddenRejectedNote');

        if (onSubmitModalRejectedNote !== null) {
            hiddenRejectedNote.value = onSubmitModalRejectedNote.value;
        }

        e.setAttribute('disabled', true);
        e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
        document.getElementById('formSubmit').submit();
    }
</script>
@endpush
