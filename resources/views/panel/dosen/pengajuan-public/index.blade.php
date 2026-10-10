@extends('layouts.panel.index')
@section('title', 'Daftar Pengajuan yang bisa dikomentari ')

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
                "order": []
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
                    <h3>Daftar Pengajuan</h3>
                    <p class="text-subtitle text-muted">yang bisa dikomentari</p>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover table-bordered" id="table1">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center" style="width: 2%">No</th>
                                    <th scope="col">
                                        Judul
                                        <small class="text-muted" style="font-size:12px;">
                                            Mahasiswa - NIM | Status
                                        </small>
                                    </th>
                                    <th scope="col" style="20%">
                                        Tipe Pengajuan
                                    </th>
                                    <th scope="col" style="20%">
                                        Pihak Verifikasi
                                    </th>
                                    <th scope="col" style="width: 10%" class="text-end">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($submissions->count() === 0)
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($submissions as $i => $submission)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $submission->title }}
                                                <div class="mt-2" style="font-size:12px;">
                                                    {{ $submission->student->name }} - {{ $submission->student->unique_id }} | Status Pengajuan : <span class="badge {{ badge_class($submission->status) }}">{{ status_english_to_bahasa($submission->status) }}</span>
                                                </div>
                                                <div class="mt-2" style="font-size:12px">
                                                    Kelompok keahlian : {{ $submission->skill_group_id ? $submission->skill_group->name : '-' }}
                                                    <br/> Tahun akademik : {{ $submission->academic_year ?: '-' }} <strong>|</strong> semester : {{ $submission->semester ?: '-' }}
                                                    <br/> Pengajuan diupdate : {{ $submission->updated_at->format('d/m/Y H:i') }} <strong>|</strong> dibuat : {{ $submission->created_at->format('d/m/Y H:i') }}
                                                </div>
                                                <div class="mt-1" style="font-size:12px">
                                                    Jadwal Seminar:
                                                    @php
                                                        if ($submission->schedule) {
                                                            $schedule_day = translateDayToIndonesian(date('w', strtotime($submission->schedule)));
                                                            $schedule_date = date('d', strtotime($submission->schedule));
                                                            $schedule_month = translateMonthToIndonesian(date('n', strtotime($submission->schedule)));
                                                            $schedule_year = date('Y', strtotime($submission->schedule));
                                                            $schedule_time = date('H:i', strtotime($submission->schedule));
                                                            echo "<span class='badge bg-light-success mx-1'>$schedule_day, $schedule_date $schedule_month $schedule_year $schedule_time</span>";
                                                        } else {
                                                            echo '<span class="badge bg-light-danger mx-1">Belum ditentukan</span>';
                                                        }
                                                    @endphp
                                                    | Ruangan: {{ $submission->room ?: '-' }}
                                                </div>
                                            </td>
                                            <td class="text-capitalize" style="width: 20%">
                                                {{ $submission->type->name }}
                                            </td>
                                            <td class="text-capitalize" style="width: 20%">
                                                <ul class="list-group">
                                                    @php
                                                        $verificator_pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Pembimbing')->get();
                                                        $verificator_pengujis = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Penguji')->get();
                                                    @endphp

                                                    @if ($submission->format->is_pembimbing_required)
                                                        @if ($verificator_pembimbings->count() > 0)
                                                            @foreach ($verificator_pembimbings as $i => $pembimbing)
                                                                <li class="list-group-item">
                                                                    Pembimbing {{ $i + 1 }}
                                                                    @if ($pembimbing->is_need_to_change_trial_status)
                                                                        <span class="badge {{ badge_class($pembimbing->status) }}">
                                                                            {{ status_english_to_bahasa($pembimbing->status) }}
                                                                        </span>
                                                                    @endif
                                                                    <br/>
                                                                    <small class="text-muted">{{ $pembimbing->user->name }}</small>
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            <li class="list-group-item">
                                                                Pembimbing
                                                                <span class="badge bg-warning">
                                                                    Belum Dikonfirmasi
                                                                </span>
                                                                <br/>
                                                                <small class="text-muted">Belum ada pembimbing</small>
                                                            </li>
                                                        @endif
                                                    @endif

                                                    @if ($submission->format->is_penguji_required)
                                                        @if ($verificator_pengujis->count() > 0)
                                                            @foreach ($verificator_pengujis as $i => $penguji)
                                                                <li class="list-group-item">
                                                                    Penguji {{ $i + 1 }}
                                                                    @if ($penguji->is_need_to_change_trial_status)
                                                                        <span class="badge {{ badge_class($penguji->status) }}">
                                                                            {{ status_english_to_bahasa($penguji->status) }}
                                                                        </span>
                                                                    @endif
                                                                    <br/>
                                                                    <small class="text-muted">{{ $penguji->user->name }}</small>
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            <li class="list-group-item">
                                                                Penguji
                                                                <span class="badge bg-warning">
                                                                    Belum Dikonfirmasi
                                                                </span>
                                                                <br/>
                                                                <small class="text-muted">Belum ada penguji</small>
                                                            </li>
                                                        @endif
                                                    @endif
                                                </ul>
                                            </td>
                                            <td class="text-end" style="width: 10%">
                                                <a href="{{ route('panel.dosen.pengajuan-public.show', ['id' => $submission->id]) }}"
                                                    class="btn icon btn-sm btn-info">Diskusi</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </section>
    </div>
@endsection

@push('scripts')
@endpush
