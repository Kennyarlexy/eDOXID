@extends('layouts.panel.index')
@section('title', 'Detail Kebutuhan File Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
    <style>
        .ck-editor__editable_inline {
            min-height: 100px;
        }

        .load-more {
            max-height: 132px;
            overflow: hidden;
            text-align: justify;
        }
        .filepond--credits {
            display: none;
        }
    </style>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">

            @include('includes.form-alert')
            <div class="card">
                @include('panel.admin.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.admin.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">
                            <form
                                action="{{ route('panel.admin.pengajuan.required_files.update', ['type_slug' => $submission->type->slug,'id' => $submission->id]) }}"
                                method="post">
                                @csrf
                                @method('PUT')

                                @if ($submission->format->required_files->count() > 0)

                                    @foreach ($submission->required_files as $i => $submission_required_file)
                                        @php
                                            $accepted_file_types = 'image/*,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.slideshow,application/vnd.openxmlformats-officedocument.presentationml.presentation,zip,application/octet-stream,application/zip,application/x-zip,application/x-zip-compressed';
                                            $accepted_file_type_noted = 'File dapat berupa: Gambar, PDF, Word, Powerpoint, Slideshow, Presentation, Zip, dan File lainnya';
                                            if ($submission_required_file->required_file->type_file == 'document_word') {
                                                $accepted_file_types = 'application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document';
                                                $accepted_file_type_noted = 'File hanya dapat berupa: Word, dan Pdf';
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
                                                <small class="text-muted text-sm">{{ $submission_required_file->required_file->note }}</small>
                                                <br/>
                                                <small class="text-muted text-sm" style="font-size:10px">{{ $accepted_file_type_noted }}</small>
                                                <br/>
                                                <small class="text-muted text-sm" style="font-size:10px">Terakhir update: {{ $submission_required_file->updated_at->format('d M Y H:i') }}</small>
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-select mt-2"
                                                    name="required_file_status[{{ $submission_required_file->required_file_id }}]">
                                                    <option value="pending">Pilih Status</option>
                                                    <option value="approved"
                                                        {{ $submission_required_file->status == 'approved' ? 'selected' : '' }}>
                                                        Diterima</option>
                                                    <option value="rejected"
                                                        {{ $submission_required_file->status == 'rejected' ? 'selected' : '' }}>
                                                        Ditolak</option>
                                                </select>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="form-group">
                                                    <input type="file"
                                                        class="form-control mt-2 mb-0 @error('{{ $submission_required_file->required_file_id }}') is-invalid @enderror"
                                                        id="{{ $submission_required_file->required_file_id }}" name="{{ $submission_required_file->required_file_id }}"
                                                        accept="{{ $accepted_file_types }}">
                                                    @error('{{ $submission_required_file->required_file_id }}')
                                                        <div class="invalid-feedback">
                                                            <i class="bx bx-radio-circle"></i>
                                                            {{ $message }}
                                                        </div>
                                                    @enderror
                                                    <div class="d-flex flex-wrap">
                                                        @if ($submission_required_file->url !== null)
                                                            <span class="text-muted me-2 mt-2">Terakhir update:
                                                                {{ $submission_required_file->updated_at->format('d M Y H:i') }}</span>
                                                            <div class="badge {{ badge_class($submission_required_file->status) }} text-capitalize me-2 mt-2">
                                                                {{ status_english_to_bahasa($submission_required_file->status) }}</div>
                                                            @if (str_contains($submission_required_file->url, 'drive.google.com'))
                                                                <a class="badge bg-success me-2 mt-2" style="cursor: pointer" onclick="showModal('{{ $submission_required_file->url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                                @if ($submission_required_file->required_file->is_verificator_can_edit)
                                                                    @php
                                                                        $url = $submission_required_file->url;
                                                                    @endphp
                                                                    <a target="_blank" href="{{ $url }}" class="badge bg-info me-2 mt-2">Edit</a>
                                                                @endif
                                                                <a target="_blank" href="{{ $submission_required_file->url }}" class="badge bg-info me-2 mt-2">Download</a>
                                                            @else
                                                                <a class="badge bg-info me-2 mt-2" href="{{ $submission_required_file->url }}">Download</a>
                                                            @endif
                                                        @else
                                                            <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                        </div>

                                        <hr />
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
                                                <select class="form-select" name="status_tandatangan">
                                                    <option value="pending" {{ $submission->student->signature_status == "approved" ? 'selected' : '' }}>Pilih Status</option>
                                                    <option value="approved" {{ $submission->student->signature_status == "approved" ? 'selected' : '' }}>Diterima</option>
                                                    <option value="rejected" {{ $submission->student->signature_status == "rejected" ? 'selected' : '' }}>Ditolak</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            @if ($submission->student->signature_image !== null)
                                                <a href="{{ asset('storage/signatures/' . $submission->student->signature_image) }}" target="_blank" class="badge bg-success">Lihat & Download Berkas</a>
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                            @endif
                                        </div>
                                    </div>

                                    
                                    <hr />
                                    <div class="row align-items-center">
                                        <div class="col-md-4">
                                            #{{ $i + 3 }} - SK
                                            <br/>
                                            <small class="text-muted">
                                                @if ($submission->file_sk_url !== null)
                                                    <div class="badge bg-success text-capitalize me-2 mt-2">Sudah Diupload</div>
                                                @else
                                                    <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                                @endif
                                            </small>
                                        </div>
                                        <div class="col-md-4">
                                            
                                        </div>
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                    <input type="file"
                                                        class="form-control mt-2 mb-0"
                                                        id="fileSKSidang" name="fileSKSidang"
                                                        accept="application/pdf">
                                                    @if ($submission->file_sk_url !== null)
                                                        <a href="{{ asset( $submission->file_sk_url) }}" target="_blank" class="badge bg-success">Lihat & Download Berkas</a>
                                                    @else
                                                        <div class="badge bg-warning text-capitalize me-2 mt-2">Belum ada berkas</div>
                                                    @endif
                                                </div>
                                        </div>
                                    </div>

                                    <hr />

                                    <small class="text-muted text-sm">Jika ingin <b>"Lihat Berkas"</b> atau <b>"Edit"</b> maka pastikan anda telah login ke akun gmail <b>UNTAN.</b></small>
                                    <br/>
                                    <small class="text-muted text-sm">Browser yang disarankan adalah: <b>Google Chrome</b></small>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="submit" class="btn btn-primary me-1 mb-1" id="btnSubmit">Submit</button>
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
@endsection

@push('scripts')
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <script>
        const btnSubmit = document.getElementById('btnSubmit');
        FilePond.registerPlugin(FilePondPluginFileValidateType);

        // Get a reference to the file input element
        const inputElements = document.querySelectorAll('input[type="file"]');
        // console.log(inputElement);
        // Create a FilePond instance
        // const pond = FilePond.create(inputElement);
        // loop over input elements
        let index = 0;
        let ponds = [];
        let pondsProcessed = 0;
        Array.from(inputElements).forEach(inputElement => {
            // create a FilePond instance at the input element location
            const pond = FilePond.create(inputElement, {
                allowRemove: false,
                allowRevert: false,
                allowReplace: false,
                allowReorder: false,
                acceptedFileTypes: inputElement.accept.split(',')
                // instantUpload: false,
                // chunkUploads: true,
                // chunkSize: 5000,
            });
            pond.setOptions({
                server: {
                    url: "{{ route('panel.admin.pengajuan.updateFile', ['type_slug' => $type->slug, 'id' => $submission->id]) }}",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                },
            });

            // when file is added
            pond.on('addfile', (error, file) => {
                btnSubmit.disabled = true;
                index++;
            });

            // when file is processed
            pond.on('processfiles', (error, file) => {
                pondsProcessed++;
                checkAllFileUploaded();
            });

            // when file is abort
            pond.on('processfileabort', (error, file) => {
                index--;
                checkAllFileUploaded();
            });

            ponds.push(pond);
        });

        // check all file if has been uploaded
        function checkAllFileUploaded() {
            if (index === pondsProcessed) {
                btnSubmit.disabled = false;
            }
        }

        const showModal = (url) => {
            const modal = document.getElementById('modal-body');
            modal.innerHTML = '';
            modal.innerHTML = `<iframe width="100%" height="630" src="${url}/preview"></iframe>`;
        }
    </script>
@endpush
