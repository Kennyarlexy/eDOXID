@extends('layouts.panel.index')
@section('title', 'Tambah Dosen ke Kelompok Keahlian: ' . $skill_group->name)

@push('styles')
<!-- Include Choices JavaScript -->
<link rel="stylesheet" href="{{ asset('/plugins/choices.js/choices.min.css') }}" />
<script src="{{ asset('/plugins/choices.js/choices.min.js') }}"></script>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.kelompok-keahlian.daftar-dosen.index', $skill_group->id) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Tambah Dosen - Kelompok Keahlian: {{ $skill_group->name }}</h3>
                </div>
            </div>
        </div>

        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.kelompok-keahlian.daftar-dosen.store', $skill_group->id) }}" method="post">
                @csrf
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Pilih Dosen</label>
                                    <select class="choices form-select @error('dosen') is-invalid @enderror" name="dosen" required>
                                        <option value="">Pilih Dosen</option>
                                        @foreach ($lecturers as $lecturer)
                                            <option value="{{ $lecturer->id }}">{{ $lecturer->name }} ({{ $lecturer->unique_id }})</option>
                                        @endforeach
                                    </select>
                                    @error('dosen')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                                <div class="form-group required">
                                    <label class="control-label">Jabatan</label>
                                    <select class="form-select @error('jabatan') is-invalid @enderror" name="jabatan" required>
                                        <option value="Anggota">Anggota</option>
                                        <option value="Sekretaris">Sekretaris</option>
                                        <option value="Ketua">Ketua</option>
                                    </select>
                                    @error('jabatan')
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
<script>
    // choices js
    let choices = document.querySelectorAll('.choices');
        let initChoice;
        for (let i = 0; i < choices.length; i++) {
            if (choices[i].classList.contains("multiple-remove")) {
                initChoice = new Choices(choices[i], {
                    delimiter: ',',
                    editItems: true,
                    maxItemCount: -1,
                    removeItemButton: true,
                });
            } else {
                initChoice = new Choices(choices[i]);
            }
        }
</script>
@endpush
