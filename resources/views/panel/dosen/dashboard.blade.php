@extends('layouts.panel.index')
@section('title', 'Dashboard')

@push('styles')
    <!-- Fullcalendar -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.css">
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/main.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@5.10.1/locales-all.js"></script>

    <style>
        .fc-event-time {
            display: none;
        }
    </style>
@endpush

@section('main')
    <div class="page-heading">
        <h3>Dashboard</h3>
    </div>

    @include('includes.form-alert')

    <div class="page-content">

        @if (Auth::user()->signature_image == null)
            <div class="alert alert-warning">
                <h4 class="alert-heading">Peringatan!</h4>
                <p>Anda belum melakukan upload TTD, silahkan upload TTD anda terlebih dahulu pada menu <b><a href="{{ route('panel.profile') }}">PROFILE</a></b> untuk keperluan penyisipan pada Dokumen Berita Acara.</p>
            </div>
        @endif

        <section class="row">
            <div class="col-12">
                <div class="mb-4">
                    <h5 class="">Tutorial Penggunaan Aplikasi</h5>
                    <a href="https://drive.google.com/file/d/16YybEoEB3VnW-63v-ZCutPy5sZVT9kLe/view?usp=sharing" target="_blank" class="badge bg-info">Klik Disini</a>
                </div>
                <div class="row">
                    <div class="col-12 col-lg-12 col-md-12">
                        <div class="card">
                            <div class="card-body py-4 px-5">
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-xl">
                                        <img src="{{ Auth::user()->profile_image == null || Auth::user()->profile_image == '//spota.untan.ac.id/img/' ? asset('/img/user.png') : Auth::user()->profile_image }}" alt="{{ Auth::user()->name }}">
                                    </div>
                                    <div class="ms-3 name">
                                        <h5 class="font-bold">{{ Auth::user()->name }}</h5>
                                        <h6 class="text-muted mb-0">{{ Auth::user()->email }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon blue">
                                            <i class="iconly-boldProfile"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Pembimbing Pengajuan</h6>
                                        <h6 class="font-extrabold mb-0">{{ $total_pengajuan_dibimbing }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6 col-md-6">
                        <div class="card">
                            <div class="card-body px-3 py-4-5">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="stats-icon green">
                                            <i class="iconly-boldAdd-User"></i>
                                        </div>
                                    </div>
                                    <div class="col-md-8">
                                        <h6 class="text-muted font-semibold">Penguji Pengajuan</h6>
                                        <h6 class="font-extrabold mb-0">{{ $total_pengajuan_diuji }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Statistik Pengajuan</h4>
                            </div>
                            <div class="card-body">
                                <form>
                                    <div class="row">
                                        <div class="col-md-3 col-12">
                                            <select class="form-select" name="tahun_statistik">
                                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                    <option value="{{ $i }}" {{ Request::get('tahun_statistik') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                        <div class="col-md-7 col-12">
                                            <select class="form-select" name="tampilkan_data_milik_sendiri">
                                                <option value="false" {{ Request::get('tampilkan_data_milik_sendiri') === "false" ? 'selected' : '' }}>Tampilkan Semua Data</option>
                                                <option value="true" {{ Request::get('tampilkan_data_milik_sendiri') === "true" ? 'selected' : '' }}>Tampilkan Data Sendiri</option>
                                            </select>
                                        </div>
                                        <div class="col-md-2 col-12">
                                            <button type="submit" class="btn btn-primary float-right">Filter</button>
                                        </div>
                                    </div>
                                </form>
                                <div id="chart-submissions"></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h4>Jadwal</h4>
                            </div>
                            <div class="card-body">
                                <div id="calendar-submissions"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

<button type="button" class="btn btn-outline-primary" style="display: none" id="showSheduleDetail" data-bs-toggle="modal" data-bs-target="#infoSchedule">
    Launch Modal
</button>

<div class="modal fade" id="infoSchedule" tabindex="-1" aria-labelledby="infoScheduleTitle" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="infoScheduleType"></h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" id="infoScheduleBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <span class="">Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="qrCodeSchedule" tabindex="-1" aria-labelledby="qrCodeScheduleTitle" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="qrCodeScheduleType">QR Code</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body" id="qrCodeScheduleBody">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <span class="">Tutup</span>
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var calendarEl = document.getElementById('calendar-submissions');
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            locale: 'id',
            events: [
            <?php foreach ($submission_schedules as $i => $submission_schedule) {
    $pembimbings = $submission_schedule->verificators()->where('type', 'Pembimbing')->get();
    $pengujis = $submission_schedule->verificators()->where('type', 'Penguji')->get();

    $pembimbings_str = '';
    foreach ($pembimbings as $pembimbing) {
        $pembimbings_str .= $pembimbing->name.'<br/> ';
    }

    $pengujis_str = '';
    foreach ($pengujis as $penguji) {
        $pengujis_str .= $penguji->name.'<br/> ';
    } ?>
                {
                    title: `<?php echo ucwords(strtolower($submission_schedule->student->name)); ?>`,
                    start: '<?php echo $submission_schedule->schedule; ?>',
                    extendedProps: {
                        title: `<?php echo $submission_schedule->title; ?>`,
                        nama: "<?php echo $submission_schedule->student->name.' ('.$submission_schedule->student->unique_id.')'; ?>",
                        jadwal: '<?php echo $submission_schedule->schedule; ?>',
                        ruangan: '<?php echo $submission_schedule->room; ?>',
                        url_public_presensi: '<?php echo route('isi-daftar-hadir.index', ['type_slug' => $submission_schedule->type->slug, 'submission_id' => $submission_schedule->id]); ?>',
                        type_submission: '<?php echo $submission_schedule->type->name; ?>',
                        description: '<?php echo $submission_schedule->description; ?>',
                        pembimbing: '<?php echo $pembimbings_str; ?>',
                        penguji: '<?php echo $pengujis_str; ?>'
                    },
                    color: '<?php echo $submission_schedule->type->color; ?>',
                    display: 'block',
                },
            <?php
} ?>
            ],
            eventClick: function (info) {
            info.jsEvent.preventDefault();

            let btnShowSheduleDetail = document.getElementById('showSheduleDetail');

            let infoScheduleType = document.getElementById('infoScheduleType');
            let infoScheduleBody = document.getElementById('infoScheduleBody');

            if (info.event.extendedProps.type_submission) {
                infoScheduleType.innerHTML = info.event.extendedProps.type_submission;
            }

            let templateHtml = '';

            if (info.event.extendedProps.nama) {
                templateHtml += `<div class="form-group">
                    <label>Nama (Nim)</label>
                    <div class="form-control bg-light">${info.event.extendedProps.nama}</div>
                </div>`;
            }

            if (info.event.extendedProps.title) {
                templateHtml += `<div class="form-group">
                    <label>Judul</label>
                    <div class="form-control bg-light">${info.event.extendedProps.title}</div>
                </div>`;
            }

            if (info.event.extendedProps.jadwal) {
                templateHtml += `<div class="form-group">
                    <label>Jadwal</label>
                    <div class="form-control bg-light">${info.event.extendedProps.jadwal}</div>
                </div>`;
            }

            if (info.event.extendedProps.ruangan) {
                if (info.event.extendedProps.ruangan.includes('http')) {
                    templateHtml += `<div class="form-group">
                        <label>Ruangan</label>
                        <div class="form-control bg-light">${info.event.extendedProps.ruangan}</div>
                        <a href="${info.event.extendedProps.ruangan}" target="_blank" class="badge bg-info mt-2" target="_blank">Klik untuk buka link ruangan</a>
                    </div>`;
                } else {
                    templateHtml += `<div class="form-group">
                        <label>Ruangan</label>
                        <div class="form-control bg-light">${info.event.extendedProps.ruangan}</div>
                    </div>`;
                }
            }

            if (info.event.extendedProps.url_public_presensi) {
                templateHtml += `<div class="form-group">
                                    <label>URL Publik Presensi</label>
                                    <div class="form-control bg-light">${info.event.extendedProps.url_public_presensi}</div>
                                </div>`;

                templateHtml += `<div style="margin-bottom:2rem"><button class="btn btn-sm btn-primary" style="float:right" onClick="generateQRCode('${info.event.extendedProps.url_public_presensi}')" data-bs-toggle="modal" data-bs-target="#qrCodeSchedule">Buat QRCode</button></div>`;
            }

            if (info.event.extendedProps.pembimbing) {
                templateHtml += `<div class="form-group">
                    <label>Pembimbing</label>
                    <div class="form-control bg-light">${info.event.extendedProps.pembimbing}</div>
                </div>`;
            }

            if (info.event.extendedProps.penguji) {
                templateHtml += `<div class="form-group">
                    <label>Penguji</label>
                    <div class="form-control bg-light">${info.event.extendedProps.penguji}</div>
                </div>`;
            }

            infoScheduleBody.innerHTML = templateHtml;
            btnShowSheduleDetail.click();
            }
        });
        calendar.render();
    });

    function generateQRCode(url){
        let urlQR = `https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${url}`;
        document.getElementById('qrCodeScheduleBody').innerHTML = `<div style="text-align:center"><img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=${url}">
        <br><br>
        <a target="_blank" href="<?php echo env('APP_URL'); ?>download-qr-code.php?url=${encodeURIComponent(urlQR)}" title="Qr Code ${url}"><button style="font-size:12px" class="btn btn-primary">Unduh QR Code dan tambahkan pada file presentasi (PPT) pada slide pertama atau terakhir untuk ditampilkan saat presentasi</button>
        </a></div>`;
    }
</script>

<script src="{{ asset('/plugins/apexcharts/apexcharts.js') }}"></script>

<script>
    var dataSubmissions = @json($submissions);

    var optionsSubmissions = {
        annotations: {
            position: 'back'
        },
        dataLabels: {
            enabled:false
        },
        chart: {
            type: 'bar',
            height: 300,
            toolbar: {
                show: true,
                tools:{
                    download:false // <== line to add
                }
            },
        },
        fill: {
            opacity:1
        },
        plotOptions: {
        },
        series: [{
            name: 'total',
            data: dataSubmissions
        }],
        colors: '#435ebe',
        xaxis: {
            categories: ["Jan","Feb","Mar","Apr","May","Jun","Jul", "Aug","Sep","Oct","Nov","Dec"],
        },
    }


    var chartSubmissions = new ApexCharts(document.querySelector("#chart-submissions"), optionsSubmissions);
    chartSubmissions.render();
</script>
@endpush
