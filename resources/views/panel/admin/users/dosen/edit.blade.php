@extends('layouts.panel.index')
@section('title', 'Ubah ' . $dosen->name)

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.users.dosen.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Dosen</h3>
                    <p class="text-subtitle text-muted">{{ $dosen->name }}</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.users.dosen.update', $dosen->id) }}" method="post">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label for="nama" class="control-label">Nama</label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                        name="nama" value="{{ $dosen->name }}" placeholder="Nama" required>
                                    @error('nama')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group required">
                                    <label for="email" class="control-label">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" autocomplete="false"
                                        name="email" value="{{ $dosen->email }}" placeholder="Email" required>
                                    @error('email')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password" class="control-label">Password</label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" autocomplete="false"
                                        name="password" placeholder="Password">
                                    @error('password')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah password</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group required">
                                    <label for="nip" class="control-label">NIP / NIDN</label>
                                    <input type="text" class="form-control @error('nip') is-invalid @enderror" id="nip" required
                                        name="nip" value="{{ $dosen->unique_id }}" placeholder="Masukkan NIP / NIDN">
                                    @error('nip')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group required">
                                    <label class="control-label">Jabatan</label>
                                    <select class="form-control @error('jabatan') is-invalid @enderror" name="jabatan">
                                        <option value="">Tidak Ada Jabatan</option>
                                        <option {{ $dosen->position == 'Ketua Jurusan' ? 'selected' : '' }}>Ketua Jurusan</option>
                                        <option {{ $dosen->position == 'Ketua Prodi' ? 'selected' : '' }}>Ketua Prodi</option>
                                    </select>
                                    @error('jabatan')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group required">
                                    <label class="control-label">Status</label>
                                    <ul class="list-unstyled mb-0">
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="active" class="form-check-input"
                                                value="active" {{ $dosen->status === 'active' ? 'checked' : '' }}>
                                            <label for="active">Aktif</label>
                                        </li>
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="inactive" class="form-check-input"
                                                value="inactive" {{ $dosen->status === 'inactive' ? 'checked' : '' }}>
                                            <label for="inactive">Non Aktif</label>
                                        </li>
                                    </ul>
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

@endpush
