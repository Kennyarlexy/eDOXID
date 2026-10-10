@extends('layouts.panel.index')
@section('title', 'Ubah Pengaturan Grading Penilaian ' . $grading->grade)

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.grading.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Pengaturan Grading Penilaian</h3>
                    <p class="text-subtitle text-muted">Grade {{ $grading->grade }}</p>
                </div>
            </div>
        </div>

        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.grading.update', $grading->id) }}" method="post">
                @csrf
                @method('PUT')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label for="nama_grade" class="control-label">Nama Grade</label>
                                    <input type="text" class="form-control @error('nama_grade') is-invalid @enderror" id="nama_grade"
                                        name="nama_grade" value="{{ $grading->grade }}" placeholder="Nama Grade" required>
                                    @error('nama_grade')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label for="maksimal_skor" class="control-label">Maksimal Skor</label>
                                    <input type="number" min="0" max="100" step="0.01" class="form-control @error('maksimal_skor') is-invalid @enderror" id="maksimal_skor"
                                        name="maksimal_skor" value="{{ $grading->maksimal_score }}" placeholder="Maksimal Skor" required>
                                    @error('maksimal_skor')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required">
                                    <label for="minimal_skor" class="control-label">Minimal Skor</label>
                                    <input type="number" min="0" max="100" step="0.01" class="form-control @error('minimal_skor') is-invalid @enderror" id="minimal_skor"
                                        name="minimal_skor" value="{{ $grading->minimal_score }}" placeholder="Minimal Skor" required>
                                    @error('minimal_skor')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group required mb-4">
                                    <label class="control-label">Status Pengajuan</label>
                                    <select class="form-select" name="status_pengajuan" required>
                                        <option value="repeated">Diulang</option>
                                        <option value="approved" {{ $grading->submission_status == 'approved' ? 'selected' : '' }}>Diterima</option>
                                        <option value="rejected" {{ $grading->submission_status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="deskripsi">Deskripsi</label>
                                    <textarea class="form-control @error('deskripsi') is-invalid @enderror" id="deskripsi" name="deskripsi" placeholder="Deskripsi terkait grade">{{ $grading->description }}</textarea>
                                    @error('deskripsi')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
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
