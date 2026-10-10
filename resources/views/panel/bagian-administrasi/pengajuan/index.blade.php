@extends('layouts.panel.index')
@section('title', 'Daftar Berkas Pengajuan: ' . $type->name)

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
                order: [],

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
                    <h3 class="mb-4">Daftar - Pengajuan {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
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
                                <label for="status_lihat" class="control-label">Status Lihat</label>
                                <select class="form-select @error('status_lihat') is-invalid @enderror" name="status_lihat">
                                    <option value="">Semua</option>
                                    <option value="Belum Dilihat" {{ Request::get('status_lihat') == "Belum Dilihat" ? 'selected' : '' }}>Belum Dilihat</option>
                                    <option value="Telah Dilihat" {{ Request::get('status_lihat') == "Telah Dilihat" ? 'selected' : '' }}>Telah Dilihat</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="tim" class="control-label">Tim</label>
                                <select class="form-select @error('tim') is-invalid @enderror" name="tim">
                                    <option value="">Tampilkan Semua</option>
                                        <option value="Belum Memiliki Pembimbing" {{ Request::get('tim') == 'Belum Memiliki Pembimbing' ? 'selected' : '' }}>Belum Memiliki Pembimbing</option>
                                        <option value="Belum Memiliki Penguji" {{ Request::get('tim') == 'Belum Memiliki Penguji' ? 'selected' : '' }}>Belum Memiliki Penguji</option>
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4 col-lg-2">
                            <div class="form-group">
                                <label for="urut_berdasarkan" class="control-label">Urut Berdasarkan</label>
                                <select class="form-select @error('urut_berdasarkan') is-invalid @enderror" name="urut_berdasarkan">
                                    <option value="asc" {{ Request::get('urut_berdasarkan') == 'asc' ? 'selected' : '' }}>Pengajuan Terlama</option>
                                    <option value="desc" {{ Request::get('urut_berdasarkan') == 'desc' ? 'selected' : '' }}>Pengajuan Terbaru</option>
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
                                        <h4>
                                            {{ $submission->title }}
                                        </h4>
                                        @php
                                                $totalBerkasYangBelumDiapprove = $submission_required_file_pending_count + $submission_required_file_rejected_count;
                                                if($totalBerkasYangBelumDiapprove > 0){
                                                    echo '<div class="alert alert-danger" role="alert">
                                                            Masih ada berkas yang belum diproses/ditolak. 
                                                        </div>';
                                                }
                                            @endphp
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
                                                @endif
                                            </div>
                                            @if ($submission->is_read_by_tata_usaha === 0)
                                                <br/>
                                                <small class="text-danger"><i class="fa-solid fa-exclamation"></i> Belum anda lihat.</small>
                                            @endif
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
                                                                        @if ($pembimbing->status == 'pending')
                                                                            <span class="badge bg-success align-middle" onclick="requestApproval(this, '{{ route('panel.bagian-administrasi.pengajuan.request_approval', ['type_slug' => $type->slug, 'id' => $submission->id, 'verificator_id' => $pembimbing->id]) }}')" style="cursor: pointer"><i class="fa-brands fa-whatsapp"></i></span>
                                                                        @endif
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
                                                                <br/>
                                                                <span class="badge bg-success align-middle" onclick="requestPembimbing(this, '{{ route('panel.bagian-administrasi.pengajuan.request_pembimbing', ['type_slug' => $type->slug, 'id' => $submission->id]) }}')" style="cursor: pointer"><i class="fa-brands fa-whatsapp"></i> Request Pembimbing</span>
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
                                                                        @if ($penguji->status == 'pending')
                                                                            <span class="badge bg-success align-middle" onclick="requestApproval(this, '{{ route('panel.bagian-administrasi.pengajuan.request_approval', ['type_slug' => $type->slug, 'id' => $submission->id, 'verificator_id' => $penguji->id]) }}')" style="cursor: pointer"><i class="fa-brands fa-whatsapp"></i></span>
                                                                        @endif
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
                                                                <br/>
                                                                <span class="badge bg-success align-middle" onclick="requestPenguji(this, '{{ route('panel.bagian-administrasi.pengajuan.request_penguji', ['type_slug' => $type->slug, 'id' => $submission->id]) }}')" style="cursor: pointer"><i class="fa-brands fa-whatsapp"></i> Request Penguji</span>
                                                            </div>
                                                        @endif
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 mt-2">
                                            <a href="{{ route('panel.bagian-administrasi.pengajuan.required_files.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" class="btn my-1 btn-sm btn-info">Detail</a>
                                            <a href="{{ route('panel.pengajuan.log', ['id' => $submission->id]) }}" target="_blank" class="btn my-1 btn-sm btn-secondary">Lihat Log</a>
                                           
                                            @if ($submission->schedule != null && $submission->schedule >= now())
                                                <button type="button"
                                                    class="btn my-1 btn-sm btn-info"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#modalUpdate"
                                                    onclick="bulkSchedule(this, '{{ route('panel.bagian-administrasi.pengajuan.bulk_schedule', ['type_slug' => $type->slug, 'id' => $submission->id]) }}')"
                                                    >
                                                        <i class="fa-solid fa-calendar"></i>
                                                    Kirim jadwal
                                                </button>
                                            @endif
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

    <form style="display: none" method="post" id="form_post">
        @csrf
    </form>

    <div class="modal fade" id="modalUpdate" tabindex="-1" aria-labelledby="modalUpdateTitle" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <form method="post" id="formModalPost">
                @csrf
                <div class="modal-content">
                    <div class="modal-header" id="headerModal">
                        <h5 class="modal-title" id="headerModalUpdateTitle">Apakah anda yakin?</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <i data-feather="x"></i>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group required mb-4">
                            <h5 class="font-bold">Apakah anda yakin ingin membagikan jadwal ini kepada semua tim?</h5>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <span class="d-sm-block">Tutup</span>
                        </button>
                        <button type="button" class="btn btn-primary ml-1" id="btnModal" onclick="onScheduleClickLoading(this)">
                            <span class="d-sm-block">Ya</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
   const requestPembimbing = (e, url) => {
        const form = document.getElementById('form_post');
        form.action = url;
        onLoading(e);
        form.submit();
   }

   const requestPenguji = (e, url) => {
        const form = document.getElementById('form_post');
        form.action = url;
        onLoading(e);
        form.submit();
   }

   const bulkSchedule = (e, url) => {
        const formModalPost = document.getElementById('formModalPost');
        formModalPost.action = url;
   }

   const requestApproval = (e, url) => {
        const form = document.getElementById('form_post');
        form.action = url;
        onLoading(e);
        form.submit();
   }

    const onLoading = (e) => {
        e.setAttribute('disabled', true);
        e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
    }

    const onScheduleClickLoading = (e) => {
        onLoading(e);
        document.getElementById('formModalPost').submit();
    }
</script>
@endpush
