@extends('layouts.panel.index')
@section('title', 'Ubah Mahasiswa ' . $mahasiswa->name)

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.users.mahasiswa.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Mahasiswa</h3>
                    <p class="text-subtitle text-muted">{{ $mahasiswa->name }}</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.users.mahasiswa.update', $mahasiswa->id) }}" method="post" enctype="multipart/form-data">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group mb-2 required">
                                    <label for="nama" class="control-label">Nama</label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                        value="{{ $mahasiswa->name }}" placeholder="Nama" disabled>
                                    @error('nama')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group mb-2 required">
                                    <label for="nim" class="control-label">Nomor Induk Mahasiswa (NIM)</label>
                                    <input type="text" class="form-control @error('nim') is-invalid @enderror" id="nim"
                                        value="{{ $mahasiswa->unique_id }}" placeholder="Nomor Induk Mahasiswa (NIM)" disabled>
                                    @error('nim')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group mb-2 required">
                                    <label class="control-label">Status</label>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="active" class="form-check-input"
                                                value="active" {{ $mahasiswa->status === 'active' ? 'checked' : '' }}>
                                            <label for="active">Aktif</label>
                                        </li>
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="inactive" class="form-check-input"
                                                value="inactive" {{ $mahasiswa->status === 'inactive' ? 'checked' : '' }}>
                                            <label for="inactive">Non Aktif</label>
                                        </li>
                                    </ul>
                                </div>

                                <div class="form-group mb-2 required">
                                    <label for="email" class="control-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" autocomplete="false"
                                        name="email" value="{{ $mahasiswa->email }}" placeholder="Email" disabled>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group mb-2">
                                    <label class="mb-1">Hak akses</label>
                                    @foreach ($types as $type)
                                        <div class="form-check">
                                            <div class="checkbox">
                                                <input type="checkbox" id="{{ $type->id }}" name="mahasiswa_type_access_permissions[{{ $type->id }}]" value=true class="form-check-input" {{ \App\Models\MahasiswaTypeAccessPermission::where('mahasiswa_id', $mahasiswa->id)->where('type_id', $type->id)->first() ? 'checked' : '' }}>
                                                <label for="{{ $type->id }}">{{ $type->name }}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                            </div>

                            <div class="col-12 col-md-6">
                                <h4>Foto Tandatangan</h5>
                                <p class="card-text">
                                    Foto tandatangan yang anda masukkan maksimal berukuran <code>2MB</code> dan format file
                                    haruslah <code>jpg, jpeg, atau png</code>. Pastikan foto tandatangan yang anda upload <code>jelas</code> untuk keperluan proses pengajuan.
                                </p>

                                <div class="w-full text-center rounded text-white">
                                    <div class="bg-secondary" id="inputImage">
                                        <p class="py-3" onclick="addImageTandatangan()"><u>Pilih</u> foto tandatangan kamu untuk menggantikan foto tandatangan sekarang</p>
                                    </div>
                                </div>

                                <div class="w-100 text-center rounded text-white position-relative">
                                    <div class="w-100 my-3" id="previewImage">
                                        <img src="{{ $mahasiswa->signature_image ? asset('/storage/signatures/' . $mahasiswa->signature_image) : '' }}" id="previewImageSignatureSrc" class="rounded img-fluid w-25 h-25 mx-auto d-block">
                                    </div>
                                </div>

                                <!-- File uploader with image preview -->
                                <input type="file" id="tandatangan_image" style="display:none" name="tandatangan_image"
                                    accept="image/png, image/jpg, image/jpeg">

                                <div class="form-group mb-2 required">
                                    <label class="control-label">Status Verifikasi Tandatangan</label>
                                    <select class="form-select" name="status_tandatangan" required>
                                        <option value="pending" {{ $mahasiswa->signature_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                        <option value="approved" {{ $mahasiswa->signature_status === 'approved' ? 'selected' : '' }}>Disetujui</option>
                                        <option value="rejected" {{ $mahasiswa->signature_status === 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
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
    const addImageTandatangan = () => {
            var tandatangan_image = document.getElementById('tandatangan_image');
            tandatangan_image.click();

            const previewImageSignatureSrc = document.getElementById("previewImageSignatureSrc");
            tandatangan_image.onchange = evt => {
                const [image] = tandatangan_image.files
                if (image) {
                    previewImageSignatureSrc.src = URL.createObjectURL(image)
                }
            }
        }
</script>
@endpush
