@extends('layouts.panel.index')
@section('title', 'Ubah Dosen pada Kelompok Keahlian: ' . $skill_group->name)

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
                    <h3 class="mt-4">Ubah Dosen - Kelompok Keahlian: {{ $skill_group->name }}</h3>
                </div>
            </div>
        </div>

        <section class="section">
            @include('includes.form-alert')
            <form action="{{ route('panel.admin.kelompok-keahlian.daftar-dosen.update', ['daftar_dosen' => $skill_group_lecturer->id, 'skill_group_id' => $skill_group->id]) }}" method="post">
                @csrf
                @method('put')
                <div class="card">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label>Pilih Dosen</label>
                                    <select class="choices form-select @error('dosen') is-invalid @enderror" name="dosen" required>
                                        @foreach ($lecturers as $lecturer)
                                            <option value="{{ $lecturer->id }}" {{ $lecturer->id == $skill_group_lecturer->lecturer_id ? 'selected' : '' }}>{{ $lecturer->name }} ({{ $lecturer->unique_id }})</option>
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
                                        <option value="Anggota" {{ $skill_group_lecturer->position == 'Anggota' ? 'selected' : '' }}>Anggota</option>
                                        <option value="Sekretaris" {{ $skill_group_lecturer->position == 'Sekretaris' ? 'selected' : '' }}>Sekretaris</option>
                                        <option value="Ketua" {{ $skill_group_lecturer->position == 'Ketua' ? 'selected' : '' }}>Ketua</option>
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
