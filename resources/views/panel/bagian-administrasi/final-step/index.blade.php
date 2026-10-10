@extends('layouts.panel.index')
@section('title', 'Daftar Mahasiswa Tahapan Akhir')

@include('components.function')

@push('styles')
    <script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables/css/style.css') }}">
    <script type="text/javascript" charset="utf8" src="{{ asset('plugins/datatables/js/script.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#table1').DataTable({
                searching: true,
                ordering: true,
                language: {
                    "lengthMenu": "Tampilkan _MENU_ data per halaman",
                    "zeroRecords": "Tidak ada data",
                    "info": "Halaman _PAGE_ dari _PAGES_",
                    "infoEmpty": "Tidak ada data",
                    "infoFiltered": "(difilter dari _MAX_ total data)",
                    "search": "Cari",
                    "paginate": {
                        "previous": "Sebelumnya",
                        "next": "Selanjutnya"
                    }
                },
                order: []
            });
        });
    </script>

    <style>
        table.dataTable {
            border-collapse: collapse !important;
        }
    </style>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <h3>Daftar Tahapan Akhir</h3>
                </div>
            </div>
        </div>
        <section class="section">

            <div class="my-4">
                @include('includes.form-alert')
            </div>

            <div class="card">
                <div class="card-body">

                    <form class="row mb-4 align-middle items-center d-flex justify-content-end">

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="cari" class="control-label">Cari</label>
                                <input type="text" class="form-control @error('cari') is-invalid @enderror" id="cari"
                                    name="cari" value="{{ Request::get('cari') }}" placeholder="Cari">
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="angkatan" class="control-label">Angkatan</label>
                                <select class="form-select @error('angkatan') is-invalid @enderror" name="angkatan">
                                    <option value="">Semua Angkatan</option>
                                    @for ($i = date('y'); $i >= 15; $i--)
                                        <option value="{{ $i }}" {{ Request::get('angkatan') == $i ? 'selected' : '' }}>Angkatan 20{{ $i }}</option>
                                    @endfor
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="filter"></label>
                                <button type="submit" class="form-control btn btn-primary text-light">Filter</button>
                            </div>
                        </div>

                    </form>

                    <div class="row">
                        @if ($students->isEmpty())
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <p>Tidak ada data</p>
                                </div>
                            </div>
                        @else
                            @foreach ($students as $i => $student)
                                @php
                                    $sidang_akhir_submission = null;
                                    $sidang_akhir = \App\Models\Submission::where('student_id', $student->id)
                                        ->where('type_id', $sidang_akhir_type->id)
                                        ->orderBy('created_at', 'desc')
                                        ->first();
                                    if (!$sidang_akhir) continue;
                                    $final_step_submission = \App\Models\FinalStepSubmissionFile::where('mahasiswa_id', $student->id)
                                        ->first();
                                    if (!$final_step_submission) {
                                        $final_step_submission = \App\Models\FinalStepSubmissionFile::create([
                                            'mahasiswa_id' => $student->id,
                                        ]);
                                    }
                                    $pending_count = 0;
                                    $approved_count = 0;
                                    $rejected_count = 0;
                                    // check final_thesis_file_status
                                    if (
                                        $final_step_submission->final_thesis_file_status === null ||
                                        $final_step_submission->final_thesis_file_status === \App\Models\FinalStepSubmissionFile::STATUS_PENDING
                                    ) $pending_count++;
                                    else if (
                                        $final_step_submission->final_thesis_file_status === \App\Models\FinalStepSubmissionFile::STATUS_APPROVED
                                    ) $approved_count++;
                                    else if (
                                        $final_step_submission->final_thesis_file_status === \App\Models\FinalStepSubmissionFile::STATUS_REJECTED
                                    ) $rejected_count++;
                                    // check validity_sheet_file_status
                                    if (
                                        $final_step_submission->validity_sheet_file_status === null ||
                                        $final_step_submission->validity_sheet_file_status === \App\Models\FinalStepSubmissionFile::STATUS_PENDING
                                    ) $pending_count++;
                                    else if (
                                        $final_step_submission->validity_sheet_file_status === \App\Models\FinalStepSubmissionFile::STATUS_APPROVED
                                    ) $approved_count++;
                                    else if (
                                        $final_step_submission->validity_sheet_file_status === \App\Models\FinalStepSubmissionFile::STATUS_REJECTED
                                    ) $rejected_count++;
                                    $final_step_submission_status = 'pending';
                                    if ($approved_count == 2) $final_step_submission_status = 'approved';
                                    else if ($rejected_count == 2) $final_step_submission_status = 'rejected';
                                    $sidang_akhir_title = $sidang_akhir->title;
                                @endphp

                                    <div class="col-12">
                                        <div class="row">
                                            <a href="{{ route('panel.bagian-administrasi.tahapan-akhir.show', [
                                                'student_id' => $student->id
                                            ]) }}" class="display-4">
                                                <h4>
                                                    <u>{{ $sidang_akhir_title }}</u>
                                                </h4>
                                            </a>

                                            <div class="col-12">
                                                <div class="mt-2" style="font-size:12px;">
                                                    {{ $student->name }} - {{ $student->unique_id }}

                                                    <!-- Status Tahapan Akhir -->
                                                    <br/>
                                                    Status Tahapan Akhir : <span class="badge {{ badge_class($final_step_submission_status) }}">{{ status_english_to_bahasa($final_step_submission_status) }}</span>

                                                    <!-- Status Berkas -->
                                                    <br/> Status Berkas:
                                                    <span class="badge bg-secondary mx-1">{{ $pending_count }}</span>
                                                    <span class="badge bg-success mx-1">{{ $approved_count }}</span>
                                                    <span class="badge bg-danger mx-1">{{ $rejected_count }}</span>
                                                </div>
                                            </div>

                                            <div class="col-12 mt-2">
                                                <a href="{{ route('panel.bagian-administrasi.tahapan-akhir.show', [
                                                    'student_id' => $student->id
                                                ]) }}"
                                                    class="btn my-1 icon btn-sm btn-info">Detail</a>
                                            </div>

                                        </div>

                                        <hr/>
                                    </div>

                                @endforeach
                            {{ $students->links('components.pagination') }}
                        @endif
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush
