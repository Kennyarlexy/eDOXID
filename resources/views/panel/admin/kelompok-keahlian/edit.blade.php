@extends('layouts.panel.index')
@section('title', 'Ubah Kelompok Keahlian ' . $skill_group->name)

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.kelompok-keahlian.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Kelompok Keahlian</h3>
                    <p class="text-subtitle text-muted">{{ $skill_group->name }}</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.kelompok-keahlian.update', $skill_group) }}" method="post">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label for="nama" class="control-label">Nama</label>
                                    <input type="text" class="form-control @error('nama') is-invalid @enderror" id="nama"
                                        name="nama" value="{{ $skill_group->name }}" placeholder="Nama" required>
                                    @error('nama')
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
                                                value="active" {{ $skill_group->status == 'active' ? 'checked' : '' }}>
                                            <label for="active">Aktif</label>
                                        </li>
                                        <li class="d-inline-block me-2 mb-1">
                                            <input type="radio" name="status" id="inactive" class="form-check-input"
                                                value="inactive" {{ $skill_group->status == 'inactive' ? 'checked' : '' }}>
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
