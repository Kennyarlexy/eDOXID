@extends('layouts.panel.index')
@section('title', 'Daftar Pengajuan')

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
                    <h3>Daftar Pengajuan - {{ $type->name }}</h3>
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
                                <label for="status_pengajuan" class="control-label">Status Pengajuan</label>
                                <select class="form-select @error('status_pengajuan') is-invalid @enderror" name="status_pengajuan">
                                    <option value="">Semua</option>
                                    <option value="pending" {{ Request::get('status_pengajuan') == "pending" ? 'selected' : '' }}>Pending</option>
                                    <option value="processed" {{ Request::get('status_pengajuan') == "processed" ? 'selected' : '' }}>Sedang Proses</option>
                                    <option value="repeated" {{ Request::get('status_pengajuan') == "repeated" ? 'selected' : '' }}>Diulang</option>
                                    <option value="rejected" {{ Request::get('status_pengajuan') == "rejected" ? 'selected' : '' }}>Ditolak/Gugur</option>
                                    <option value="approved" {{ Request::get('status_pengajuan') == "approved" ? 'selected' : '' }}>Disetujui</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="hasil_keputusan" class="control-label">Hasil Keputusan</label>
                                <select class="form-select @error('hasil_keputusan') is-invalid @enderror" name="hasil_keputusan">
                                    <option value="">Semua</option>
                                    <option value="pending" {{ Request::get('hasil_keputusan') == "pending" ? 'selected' : '' }}>Menunggu Keputusan</option>
                                    <option value="approved_with_fixed" {{ Request::get('hasil_keputusan') == "approved_with_fixed" ? 'selected' : '' }}>Diterima dengan perbaikan</option>
                                    <option value="approved" {{ Request::get('hasil_keputusan') == "approved" ? 'selected' : '' }}>Diterima</option>
                                    <option value="repeated" {{ Request::get('hasil_keputusan') == "repeated" ? 'selected' : '' }}>Diulang, rekomendasi waktu seminar ulang</option>
                                    <option value="rejected" {{ Request::get('hasil_keputusan') == "rejected" ? 'selected' : '' }}>Ditolak</option>
                                </select>
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
                                <label for="siap_diproses" class="control-label">Siap diproses</label>
                                <select class="form-select @error('siap_diproses') is-invalid @enderror" name="siap_diproses">
                                    <option value="">Semua</option>
                                    <option value="true" {{ Request::get('siap_diproses') == "true" ? 'selected' : '' }}>Siap</option>
                                    <option value="false" {{ Request::get('siap_diproses') == "false" ? 'selected' : '' }}>Tidak</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="urut_berdasarkan" class="control-label">Urut Berdasarkan</label>
                                <select class="form-select @error('urut_berdasarkan') is-invalid @enderror" name="urut_berdasarkan">
                                    <option value="">Semua</option>
                                    <option value="desc" {{ Request::get('urut_berdasarkan') == 'desc' ? 'selected' : '' }}>Pengajuan Terbaru</option>
                                    <option value="asc" {{ Request::get('urut_berdasarkan') == 'asc' ? 'selected' : '' }}>Pengajuan Terlama</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="jadwal_seminar" class="control-label">Jadwal Seminar</label>
                                <select class="form-select @error('jadwal_seminar') is-invalid @enderror" name="jadwal_seminar">
                                    <option value="">Semua</option>
                                    <option value="Belum ditentukan" {{ Request::get('jadwal_seminar') == "Belum ditentukan" ? 'selected' : '' }}>Belum ditentukan</option>
                                    <option value="Telah ditentukan" {{ Request::get('jadwal_seminar') == "Telah ditentukan" ? 'selected' : '' }}>Telah ditentukan</option>
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
                                            <a href="{{ route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" class="display-4">
                                                <h4>
                                                    <u>{{ $submission->title }}</u>
                                                </h4>
                                            </a>

                                            <div class="col-12">
                                                <div class="mt-2" style="font-size:12px;">
                                                    {{ $submission->student->name }} - {{ $submission->student->unique_id }}
                                                    <br/> Status Pengajuan : <span class="badge {{ badge_class($submission->status) }}">{{ status_english_to_bahasa($submission->status) }}</span> | Keputusan Seminar/Sidang: <span class="badge {{ badge_class($submission->result_status) }}">{{ result_status_english_to_bahasa($submission->result_status) }}</span>
                                                    <br/> Kelompok keahlian : {{ $submission->skill_group_id ? $submission->skill_group->name : '-' }}
                                                    <br/> Tahun akademik : {{ $submission->academic_year ?: '-' }} <strong>|</strong> semester : {{ $submission->semester ?: '-' }}
                                                    <br/> Pengajuan diupdate : {{ $submission->updated_at->format('d/m/Y H:i') }} <strong>|</strong> dibuat : {{ $submission->created_at->format('d/m/Y H:i') }}
                                                    <br/> Status Berkas: <span class="badge bg-secondary mx-1">{{ $submission_required_file_pending_count }}</span> <span class="badge bg-success mx-1">{{ $submission_required_file_approved_count }}</span> <span class="badge bg-danger mx-1">{{ $submission_required_file_rejected_count }}</span>
                                                    <br/> Jadwal Seminar: {!! $span_scheduler !!} | Ruangan: {{ $submission->room ?: '-' }}
                                                    @if ($submission->ready_at)
                                                        <br/>
                                                        Mahasiswa meminta pengajuan ini agar dapat diproses pada tanggal: {{ $submission->ready_at }}
                                                    @else
                                                        <br/>
                                                        Mahasiswa belum meminta pengajuan ini agar dapat diproses.
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
                                                <a href="{{ route('panel.admin.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                                    class="btn my-1 icon btn-sm btn-info">Detail</a>
                                                <a href="{{ route('panel.pengajuan.log', ['id' => $submission->id]) }}" target="_blank" class="btn my-1 icon btn-sm btn-secondary">
                                                    Lihat Log
                                                </a>

                                                <button id="deleteBtn" onclick="deleteBtn(this)" class="btn my-1 icon btn-sm btn-danger"
                                                    data-bs-toggle="modal" data-bs-target="#delete"
                                                    data-url="{{ route('panel.admin.pengajuan.destroy', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                                    data-name="{{ $submission->student->name }}"
                                                    data-unique-id="{{ $submission->student->unique_id }}"
                                                    data-title="{{ $submission->title }}"
                                                    >
                                                    Hapus
                                                </button>
                                            </div>

                                        </div>

                                        <hr/>
                                    </div>

                                @endforeach

                                {{ $submissions->links('components.pagination') }}

                        @endif
                    </div>

                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title white" id="deleteModal">Apakah Anda Yakin Menghapus ini?</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nama</label>
                        <input type="text" class="form-control mb-2 disabled" disabled id="name">
                    </div>
                    <div class="form-group">
                        <label>Nim</label>
                        <input type="text" class="form-control mb-2 disabled" disabled id="unique_id">
                    </div>
                    <div class="form-group">
                        <label>Judul</label>
                        <textarea class="form-control mb-2 disabled" rows="4" disabled id="title"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <span class="">Tutup</span>
                    </button>
                    <form action="" method="post" id="deletedUrl">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger ml-1">
                            <span class="">Ya</span>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const deleteBtn = (event) => {
            document.getElementById('name').value = event.getAttribute('data-name');
            document.getElementById('unique_id').value = event.getAttribute('data-unique-id');
            document.getElementById('title').value = event.getAttribute('data-title');
            document.getElementById('deletedUrl').action = event.getAttribute('data-url');
        }
    </script>
@endpush