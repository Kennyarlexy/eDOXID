@extends('layouts.panel.index')
@section('title', 'Profile Saya')

@include('components.function')

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <h3>Profile Saya</h3>
                </div>
            </div>
        </div>

        <section id="basic-horizontal-layouts">
            @include('includes.form-alert')
            <div class="row match-height">
                <div class="col-md-6 col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">Informasi Utama</h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">
                                <div class="form-body">
                                    <div class="row">
                                        @if ($user->role == \App\Models\User::ROLE_DOSEN)
                                            <div class="col-md-4">
                                                <label>NIP/NIDN</label>
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <input type="text" id="nip_nidn" class="form-control disabled" disabled
                                                    placeholder="NIP/NIDN" value="{{ $user->unique_id }}" disabled>
                                            </div>
                                        @elseif ($user->role == \App\Models\User::ROLE_MAHASISWA)
                                            <div class="col-md-4">
                                                <label>Nim</label>
                                            </div>
                                            <div class="col-md-8 form-group">
                                                <input type="text" id="nim" class="form-control disabled" disabled
                                                    placeholder="NIM" value="{{ $user->unique_id }}">
                                            </div>
                                        @endif
                                        <div class="col-md-4 required">
                                            <label class="control-label">Nama Lengkap</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="text" id="fullname" class="form-control" name="fullname" disabled
                                                placeholder="Nama Lengkap" value="{{ $user->name }}" required>
                                        </div>
                                        <div class="col-md-4 required">
                                            <label class="control-label">Email</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="email" id="email" class="form-control" disabled
                                                placeholder="Email" value="{{ $user->email }}">
                                        </div>
                                        <div class="col-md-4 required">
                                            <label class="control-label">Nomor Handphone Whatsapp</label>
                                        </div>
                                        <div class="col-md-8 form-group">
                                            <input type="number" id="phone_number" class="form-control" required disabled
                                                name="phone_number" placeholder="" minlength="9" maxlength="13"
                                                value="{{ $user->phone_number }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <div class="w-100 text-center rounded text-white position-relative">
                                        <div class="w-100 my-3" id="previewImage">
                                            <img src="{{ $user->profile_image == null || $user->profile_image == '//spota.untan.ac.id/img/' ? asset('/img/user.png') : $user->profile_image }}" id="previewImageSrc" class="rounded img-fluid w-25 h-25 mx-auto d-block">
                                        </div>
                                    </div>
                                </div>
                                <br/>
                                <small class="text-sm text-muted">Data diatas merupakan data dari aplikasi SPOTA, jika ingin mengubah data diatas maka lakukan pada platform sistem SPOTA.</small>
                                <br/>
                                <br/>
                                <a href="{{ route('panel.profile.sync-spota') }}" class="btn btn-sm btn-primary me-1 mb-1">Klik disini untuk sinkronisasi data profile SPOTA</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-12">
                    @if ($user->role == \App\Models\User::ROLE_DOSEN || $user->role == \App\Models\User::ROLE_MAHASISWA)
                        @if ($user->signature_status == \App\Models\User::SIGNATURE_APPROVED && $user->role == \App\Models\User::ROLE_MAHASISWA)
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title">Foto Tandatangan</h5>
                                </div>
                                <div class="card-content">
                                    <div class="card-body">
                                        <div class="w-100 text-center rounded text-white position-relative">
                                            <div class="w-100 my-3" id="previewImage">
                                                <img src="{{ $user->signature_image ? asset('/storage/signatures/' . $user->signature_image) : '' }}" id="previewImageSignatureSrc" class="rounded img-fluid w-25 h-25 mx-auto d-block">
                                            </div>
                                        </div>

                                        <br/>
                                        <span class="badge bg-success">Sudah diverifikasi</span>
                                        <br/>
                                        <small class="text-sm text-muted">Foto tandatangan anda telah diverifikasi untuk keperluan pemberkasan, jika terdapat kesalahan harap hubungi admin.</small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <form class="form form-horizontal" method="POST" action="{{ route('panel.profile.signature.update') }}"
                                enctype="multipart/form-data">
                                @csrf
                                @method('PUT')
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="card-title">Foto Tandatangan</h5>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-body">
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
                                                    <img src="{{ $user->signature_image ? asset('/storage/signatures/' . $user->signature_image) : '' }}" id="previewImageSignatureSrc" class="rounded img-fluid w-25 h-25 mx-auto d-block">
                                                </div>
                                            </div>

                                            <!-- File uploader with image preview -->
                                            <input type="file" id="tandatangan_image" style="display:none" name="tandatangan_image"
                                                accept="image/png, image/jpg, image/jpeg">

                                            <br/>

                                            @if ($user->role == \App\Models\User::ROLE_MAHASISWA)
                                                @if ($user->signature_image)
                                                    <span class="badge {{ badge_class($user->signature_status) }}">{{ status_english_to_bahasa($user->signature_status) }}</span>
                                                    <br/>
                                                    @if ($user->signature_status == \App\Models\User::SIGNATURE_PENDING)
                                                        <small class="text-sm text-muted">Foto tandatangan anda sedang dalam proses verifikasi, jika terdapat kesalahan anda dapat melakukan upload ulang.</small>
                                                    @elseif($user->signature_status == \App\Models\User::SIGNATURE_REJECTED)
                                                        <small class="text-sm text-muted">Foto tandatangan anda telah ditolak, harap lakukan upload ulang foto tandatangan agar dapat diproses kembali oleh bagian administrasi.</small>
                                                    @endif
                                                @else
                                                    <span class="badge bg-danger">Belum diupload</span>
                                                @endif
                                            @endif
                                            <div class="d-flex justify-content-end">
                                                <button type="submit" class="btn btn-primary me-1 mb-1">Simpan</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        </section>
    </div>

@endsection

@push('scripts')
    <script>
        const addImage = () => {
            var profile_image = document.getElementById('profile_image');
            profile_image.click();

            const previewImageSrc = document.getElementById("previewImageSrc");
            profile_image.onchange = evt => {
                const [image] = profile_image.files
                if (image) {
                    previewImageSrc.src = URL.createObjectURL(image)
                }
            }
        }

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
