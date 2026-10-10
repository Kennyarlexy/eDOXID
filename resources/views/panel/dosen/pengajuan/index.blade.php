    @extends('layouts.panel.index')
@section('title', 'Daftar Pengajuan: ' . $type->name)

@include('components.function')

@push('styles')
    <script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables/css/style.css') }}">
    <script type="text/javascript" charset="utf8" src="{{ asset('plugins/datatables/js/script.js') }}"></script>

    <script>
        @if (auth()->user()->position !== null)
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex ) {
                    // is checked
                    var filterVerifikator = document.getElementById('filterVerifikator');

                    // get column data
                    var data2 = data[2];

                    if (filterVerifikator.checked === false) {
                        return true;
                    } else {
                        if (data2.includes(filterVerifikator.value)) {
                            return true;
                        }
                    }
                }
            );
        @endif

        $(document).ready(function() {
            var table = $('#table1').DataTable({
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

            @if (auth()->user()->position !== null)
                // Event listener to the two range filtering inputs to redraw on input
                $('#filterVerifikator').click(function() {
                    table.draw();
                });
            @endif
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
                                    <option value="">Tampilkan Semua</option>
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
                                <label for="sebagai" class="control-label">Sebagai</label>
                                <select class="form-select @error('sebagai') is-invalid @enderror" name="sebagai">
                                    <option value="">Tampilkan Semua</option>
                                    <option value="Pembimbing" {{ Request::get('sebagai') == 'Pembimbing' ? 'selected' : '' }}>Pemimbing</option>
                                    <option value="Penguji" {{ Request::get('sebagai') == 'Penguji' ? 'selected' : '' }}>Penguji</option>
                                    @if (auth()->user()->position !== null)
                                        <option value="Tidak Keduanya" {{ Request::get('sebagai') == 'Tidak Keduanya' ? 'selected' : '' }}>Tidak Keduanya</option>
                                    @endif
                                </select>
                            </div>
                        </div>

                        @if (auth()->user()->position !== null)
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
                        @endif

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
                                    $is_readed = $submission->whereHas('verificators', function($q) use ($dosen, $submission) {
                                                    $q->where('submission_id', $submission->id);
                                                    $q->where('user_id', $dosen->id);
                                                    $q->where('is_read_by_dosen', 0);
                                                })->first();
                                @endphp
                                <div class="col-12">
                                    <div class="row">
                                        <a href="{{ route('panel.dosen.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}?from=<?php echo urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" class="display-4">
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
                                                <br/> Jadwal Seminar: {!! $span_scheduler !!} | Ruangan: {{ $submission->room ?: '-' }}
                                                @if ($submission->ready_at)
                                                    <br/>
                                                    Mahasiswa meminta pengajuan ini agar dapat diproses pada tanggal: {{ $submission->ready_at }}
                                                @endif
                                            </div>
                                            @if ($is_readed)
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
                                                                        </span> |
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
                                                                        </span> |
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
                                            <a href="{{ route('panel.dosen.pengajuan.show', ['type_slug' => $type->slug, 'id' => $submission->id]) }}?from=<?php echo urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" class="btn my-1 btn-sm btn-info">Detail</a>
                                            <a href="{{ route('panel.pengajuan.log', ['id' => $submission->id]) }}" target="_blank" class="btn my-1 btn-sm btn-secondary">Lihat Log</a>
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
@endsection

@push('scripts')
@endpush
