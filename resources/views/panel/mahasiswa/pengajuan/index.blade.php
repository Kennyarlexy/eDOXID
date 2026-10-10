@extends('layouts.panel.index')
@section('title', 'Pengajuan: ' . $type->name)

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
                    <h3 class="mb-4">Pengajuan: {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">

                    <div class="row">
                        @if ($submissions->isEmpty())
                            <div class="col-12 text-center">
                                <div class="alert alert-info">
                                    <p>Tidak ada data</p>
                                </div>
                            </div>
                        @else
                            @foreach ($submissions as $i => $submission)
                                @php
                                    $submission_required_file_pending_count = $submission->required_files()->where('status', \App\Models\SubmissionRequiredFile::STATUS_PENDING)->count();
                                    $submission_required_file_approved_count = $submission->required_files()->where('status', \App\Models\SubmissionRequiredFile::STATUS_APPROVED)->count();
                                    $submission_required_file_rejected_count = $submission->required_files()->where('status', \App\Models\SubmissionRequiredFile::STATUS_REJECTED)->count();
                                    $signature_status = $submission->student->signature_status;
                                    if ($signature_status == 'pending') {
                                        $submission_required_file_pending_count++;
                                    } else if ($signature_status == 'approved') {
                                        $submission_required_file_approved_count++;
                                    } else {
                                        $submission_required_file_rejected_count++;
                                    }
                                    $span_scheduler = '';
                                    if ($submission->schedule) {
                                        $schedule_day = translateDayToIndonesian(date('w', strtotime($submission->schedule)));
                                        $schedule_date = date('d', strtotime($submission->schedule));
                                        $schedule_month = translateMonthToIndonesian(date('n', strtotime($submission->schedule)));
                                        $schedule_year = date('Y', strtotime($submission->schedule));
                                        $schedule_time = date('H:i', strtotime($submission->schedule));
                                        $span_scheduler = "<span class='badge bg-success mx-1'>$schedule_day, $schedule_date $schedule_month $schedule_year $schedule_time</span>";
                                    } else {
                                        $span_scheduler = '<span class="badge bg-danger mx-1">Belum ditentukan</span>';
                                    }
                                @endphp

                                <div class="col-12">
                                    <div class="row">
                                        <a href="{{ route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" class="display-4">
                                            <h4>
                                                <u>{{ $submission->title }}</u>
                                            </h4>
                                        </a>

                                        <div class="col-12">
                                            @php
                                                $totalBerkasYangBelumDiapprove = $submission_required_file_pending_count + $submission_required_file_rejected_count;
                                                if($totalBerkasYangBelumDiapprove > 0){
                                                    echo '<div class="alert alert-danger" role="alert">
                                                            Masih ada berkas yang belum diterima oleh pihak Tata Usaha Informatika UNTAN, segera diperbaiki dan hubungi Tata Usaha Informatika UNTAN. 
                                                        </div>';
                                                }
                                            @endphp
                                            <div class="mt-2" style="font-size:12px;">
                                                {{ $submission->student->name }} - {{ $submission->student->unique_id }}
                                                <br/> Status Pengajuan : <span class="badge {{ badge_class($submission->status) }}">{{ status_english_to_bahasa($submission->status) }}</span> | Keputusan Seminar/Sidang: <span class="badge {{ badge_class($submission->result_status) }}">{{ result_status_english_to_bahasa($submission->result_status) }}</span>
                                                <br/> Kelompok keahlian : {{ $submission->skill_group_id ? $submission->skill_group->name : '-' }}
                                                <br/> Tahun akademik : {{ $submission->academic_year ?: '-' }} <strong>|</strong> semester : {{ $submission->semester ?: '-' }}
                                                <br/> Pengajuan diupdate : {{ $submission->updated_at->format('d/m/Y H:i') }} <strong>|</strong> dibuat : {{ $submission->created_at->format('d/m/Y H:i') }}
                                                <br/> Status Berkas: <span class="badge bg-secondary mx-1">{{ $submission_required_file_pending_count }}</span> <span class="badge bg-success mx-1">{{ $submission_required_file_approved_count }}</span> <span class="badge bg-danger mx-1">{{ $submission_required_file_rejected_count }}</span>
                                                <br/> Jadwal Seminar: {!! $span_scheduler !!} | Ruangan: {{ $submission->room ?: '-' }}
                                                <br/>
                                                @if ($submission->is_ready_to_be_processed)
                                                    <span class="badge text-wrap mt-2 bg-success">Anda telah menyetujui syarat & ketentuan, saat ini pengajuan sedang atau telah diproses</span>
                                                    @if ($submission->ready_at)
                                                        <br/>
                                                        Anda meminta pengajuan ini agar dapat diproses pada tanggal: {{ $submission->ready_at }}
                                                    @endif
                                                @else
                                                    <span class="badge text-wrap my-2 bg-secondary">Belum dapat diproses, karena anda belum menyetujui syarat dan ketentuan.</span>
                                                @endif
                                            </div>
                                        </div>

                                        <br class="mt-2 mb-2" />

                                        <div class="col-12">
                                            <div class="mt-2">

                                                @php
                                                    $verificator_pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Pembimbing')->get();
                                                    $verificator_pengujis = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Penguji')->get();
                                                @endphp

                                                <div class="row border">
                                                    @if ($submission->format->is_pembimbing_required)
                                                        @if ($verificator_pembimbings->count() > 0)
                                                            @foreach ($verificator_pembimbings as $i => $pembimbing)
                                                                <div class="col-md-6 py-2">
                                                                    Pembimbing {{ $i + 1 }}
                                                                    <small class="text-muted">{{ $pembimbing->user->name }}</small>
                                                                    <br/>
                                                                    @if ($pembimbing->is_need_to_change_trial_status)
                                                                        Seminar/Sidang: <span class="badge {{ badge_class($pembimbing->status) }}">
                                                                            {{ status_english_to_bahasa($pembimbing->status) }}
                                                                        </span>
                                                                        |
                                                                    @endif
                                                                    Revisi: <span class="badge {{ badge_class($pembimbing->final_status) }}">
                                                                        {{ status_english_to_bahasa($pembimbing->final_status) }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-md-6 py-2">
                                                                Pembimbing
                                                                <span class="badge bg-warning">
                                                                    Belum Dikonfirmasi
                                                                </span>
                                                                <br/>
                                                                <small class="text-muted">Belum ada pembimbing</small>
                                                            </div>
                                                        @endif
                                                    @endif

                                                    @if ($submission->format->is_penguji_required)
                                                        <hr class="mt-2 mb-2" />
                                                        @if ($verificator_pengujis->count() > 0)
                                                            @foreach ($verificator_pengujis as $i => $penguji)
                                                                <div class="col-md-6 py-2">
                                                                    Penguji {{ $i + 1 }}
                                                                    <small class="text-muted">{{ $penguji->user->name }}</small>
                                                                    <br/>
                                                                    @if ($penguji->is_need_to_change_trial_status)
                                                                        Seminar/Sidang: <span class="badge {{ badge_class($penguji->status) }}">
                                                                            {{ status_english_to_bahasa($penguji->status) }}
                                                                        </span>
                                                                        |
                                                                    @endif
                                                                    Revisi: <span class="badge {{ badge_class($penguji->final_status) }}">
                                                                        {{ status_english_to_bahasa($penguji->final_status) }}
                                                                    </span>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="col-md-6 py-2">
                                                                Penguji
                                                                <span class="badge bg-warning">
                                                                    Belum Dikonfirmasi
                                                                </span>
                                                                <br/>
                                                                <small class="text-muted">Belum ada penguji</small>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <a href="{{ route('panel.mahasiswa.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                                class="btn my-1 icon btn-sm btn-info inline">Detail</a>
                                            <a href="{{ route('panel.pengajuan.log', ['id' => $submission->id]) }}" target="_blank" class="btn my-1 icon btn-sm btn-secondary">
                                                Lihat Log
                                            </a>
                                        </div>

                                    </div>

                                    <hr/>
                                </div>

                            @endforeach

                        @endif
                    </div>

                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
@endpush
