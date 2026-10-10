@extends('layouts.panel.index')
@section('title', 'Tahapan Akhir')

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
                    <p>Untuk tahapan akhir, mahasiswa harus melakukan upload file berkas final yang telah disetujui oleh tim peneliti.</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">

                <div class="card-body">

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">

                            <form action="{{ route('panel.mahasiswa.tahapan-akhir.update') }}" method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        #1 - <b>Berkas Skripsi</b>
                                        <br/>
                                        <small class="text-muted text-sm">Berkas Skripsi yang sudah di sah kan oleh <b>Dekan</b></small>
                                    </div>

                                    <div class="col-md-6">
                                        @if ($final_step_submission_file->final_thesis_file_status !== 'approved')
                                            <div class="form-group">
                                                <input type="file"
                                                    class="form-control @error('final_thesis_file') is-invalid @enderror"
                                                    id="final_thesis_file" name="final_thesis_file"
                                                    accept="application/pdf">

                                                @error('final_thesis_file')
                                                    <div class="invalid-feedback">
                                                        <i class="bx bx-radio-circle"></i>
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            @if ($final_step_submission_file->final_thesis_file_url !== null)
                                                <span class="text-muted">Terakhir upload: - {{ $final_step_submission_file->final_thesis_file_upload_at ?: '-' }}</span>
                                                <div
                                                    class="badge {{ badge_class($final_step_submission_file->final_thesis_file_status) }} text-capitalize me-2">
                                                    {{ status_english_to_bahasa($final_step_submission_file->final_thesis_file_status) }}</div>

                                                    @if (str_contains($final_step_submission_file->final_thesis_file_url, 'drive.google.com'))
                                                        <a class="badge bg-success" style="cursor: pointer" onclick="showModal('{{ $final_step_submission_file->final_thesis_file_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                        <a target="_blank" href="{{ $final_step_submission_file->final_thesis_file_url }}" class="badge bg-info">Download</a>
                                                    @else
                                                        <a class="badge bg-info" href="{{ $final_step_submission_file->final_thesis_file_url }}">Download</a>
                                                    @endif
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <hr />

                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        #2 - <b>Lembar Pengesahan</b>
                                        <br/>
                                        <small class="text-muted text-sm">Lembar pengesahan saja, cukup lakukan <b>Scan</b></small>
                                    </div>

                                    <div class="col-md-6">
                                        @if ($final_step_submission_file->validity_sheet_file_status !== 'approved')
                                            <div class="form-group">
                                                <input type="file"
                                                    class="form-control @error('validity_sheet_file') is-invalid @enderror"
                                                    id="validity_sheet_file" name="validity_sheet_file"
                                                    accept="application/pdf">

                                                @error('validity_sheet_file')
                                                    <div class="invalid-feedback">
                                                        <i class="bx bx-radio-circle"></i>
                                                        {{ $message }}
                                                    </div>
                                                @enderror
                                            </div>
                                        @endif

                                        <div class="form-group">
                                            @if ($final_step_submission_file->validity_sheet_file_url !== null)
                                                <span class="text-muted">Terakhir update: - {{ $final_step_submission_file->validity_sheet_file_upload_at ?: '-' }}</span>
                                                <div
                                                    class="badge {{ badge_class($final_step_submission_file->validity_sheet_file_status) }} text-capitalize me-2">
                                                    {{ status_english_to_bahasa($final_step_submission_file->validity_sheet_file_status) }}</div>

                                                    @if (str_contains($final_step_submission_file->validity_sheet_file_url, 'drive.google.com'))
                                                        <a class="badge bg-success" style="cursor: pointer" onclick="showModal('{{ $final_step_submission_file->validity_sheet_file_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                        <a target="_blank" href="{{ $final_step_submission_file->validity_sheet_file_url }}" class="badge bg-info">Download</a>
                                                    @else
                                                        <a class="badge bg-info" href="{{ $final_step_submission_file->validity_sheet_file_url }}">Download</a>
                                                    @endif
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                            @endif
                                        </div>
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
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <script>
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
                acceptedFileTypes: inputElement.accept.split(','),
                // instantUpload: false,
                // chunkUploads: true,
                // chunkSize: 5000,
            });
            pond.setOptions({
                server: {
                    url: "{{ route('panel.mahasiswa.tahapan-akhir.upload') }}",
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    }
                },
            });
            // when file is added
            pond.on('addfile', (error, file) => {
                if (!error) {
                    btnSubmit.disabled = true;
                    index++;
                }
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
            console.log('index : ' + index);
            console.log('pondsProcessed : ' + pondsProcessed);
            if (index == pondsProcessed) {
                btnSubmit.disabled = false;
            }
        }
        const btnSubmit = document.getElementById('btnSubmit');
        const showModal = (url) => {
            const modal = document.getElementById('modal-body');
            modal.innerHTML = '';
            modal.innerHTML = `<iframe width="100%" height="630" src="${url}/preview"></iframe>`;
        }
    </script>
@endpush