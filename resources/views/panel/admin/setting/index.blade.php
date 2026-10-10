@extends('layouts.panel.index')
@section('title', 'Pengaturan')

@push('styles')
    <style>
        .ck-editor__editable_inline {
            min-height: 100px;
        }
    </style>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <h3>Pengaturan</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="row">
                <form action="{{ route('panel.admin.setting.update') }}" method="post" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-9">
                                    <div class="form-group required">
                                        <label for="site_name" class="control-label">Nama Situs</label>
                                        <input type="text" class="form-control @error('site_name') is-invalid @enderror" id="site_name" name="site_name" value="{{ $settings->where('key', 'site_name')->first()->value }}" placeholder="Site Name" required>
                                        @error('site_name')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-radio-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group required">
                                        <label for="semester" class="control-label">Semester Saat Ini</label>
                                        <input type="text" class="form-control @error('semester') is-invalid @enderror" id="semester" name="semester" value="{{ $settings->where('key', 'semester')->first()->value }}" placeholder="Semester" required>
                                        @error('semester')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-radio-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group required">
                                        <label for="academic_year" class="control-label">Tahun Akademik Saat Ini</label>
                                        <input type="text" class="form-control @error('academic_year') is-invalid @enderror" id="academic_year" name="academic_year" value="{{ $settings->where('key', 'academic_year')->first()->value }}" placeholder="Academic Year" required>
                                        @error('academic_year')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-radio-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="academic_year" class="control-label">Teks Syarat dan Ketentuan Proses Pengajuan</label>
                                        <textarea name="syarat_dan_ketentuan" id="syarat_dan_ketentuan"
                                            placeholder="Tambahkan teks syarat dan ketentuan"
                                            rows="4">{{ $settings->where('key', 'terms_of_service')->first() ? $settings->where('key', 'terms_of_service')->first()->value : '' }}</textarea>
                                        <small class="text-muted">Berguna untuk meletakkan teks syarat dan ketentuan proses pengajuan pada saat mahasiswa ingin melakukan proses pengajuan.</small>
                                    </div>


                                </div>
                                <div class="col-md-3">
                                    <div class="mb-3">
                                        <label class="form-label">Logo</label>
                                        <input type="file" accept="image/*" name="logo" class="form-control" onchange="previewImage(this)" id="imageInput">
                                    </div>

                                    <div class="border border-3 p-4 rounded d-flex justify-content-center align-items-center w-full block">
                                        <img src="{{ $settings->where('key', 'site_logo')->first() ? asset('storage/settings/' . $settings->where('key', 'site_logo')->first()->value) : asset('/img/logo/logo.png') }}" class="card-img-top" id="image-preview" onclick="document.getElementById('imageInput').click();">
                                    </div>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary me-1 mb-1 ms-auto">Submit</button>
                        </div>
                    </div>
                </form>
                <div class="card mt-4">
                    <div class="card-body">
                        <a href="{{ route('panel.admin.setting.sync-spota') }}" class="btn btn-primary mb-1 w-100">Sinkronisasi Data SPOTA</a>
                        <br/>
                        <small class="text-sm text-muted">Tanggal terakhir sinkronisasi : {{ $settings->where('key', 'sync_spota_data')->first() ? $settings->where('key', 'sync_spota_data')->first()->value : '-' }}</small>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('/plugins/ckeditor/ckeditor.js') }}"></script>

    <script>
        ClassicEditor
            .create(document.querySelector('#syarat_dan_ketentuan'), {
                toolbar: {
                    items: ['bold', 'italic', '|', 'undo', 'redo', '|', 'numberedList', 'bulletedList']
                }
            })
            .catch(error => {
                console.error(error);
            });

        const imagePreview = document.getElementById('image-preview');

        const previewImage = (imgInp) => {
            const [file] = imgInp.files
            if (file) {
                console.log(URL.createObjectURL(file));
                imagePreview.src = URL.createObjectURL(file)
            }
        }
    </script>
@endpush
