@extends('layouts.panel.index')
@section('title', 'Detail Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 100px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Daftar Kehadiran Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.admin.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.admin.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <!-- Deskripsi -->
                        <div class="tab-pane fade show active" id="deskripsi" role="tabpanel"
                            aria-labelledby="deskripsi-tab">

                            <div class="d-flex justify-content-end mb-2">
                                <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#presence" onclick="modalAction('Simpan Kehadiran', 'Tambah Data Kehadiran', 'post', 'primary', '{{ route('panel.admin.pengajuan.presences.store', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}', '', '', '')">URL Publik Presensi</button>
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#primary" onclick="modalAction('Simpan Kehadiran', 'Tambah Data Kehadiran', 'post', 'primary', '{{ route('panel.admin.pengajuan.presences.store', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}', '', '', '')">Tambah baru</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-hover table-bordered" id="table1">
                                    <thead>
                                        <tr>
                                            <th scope="col" class="text-center" style="width: 2%">No</th>
                                            <th scope="col">
                                                Nama - NIM/NIP/NIDK/NIDN
                                                <br/>
                                                <small class="text-muted">Keterangan</small>
                                            </th>
                                            <th scope="col" style="width: 10%" class="text-end">
                                                Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @php
                                            $index = 1;
                                        @endphp

                                        @foreach ($pembimbings as $i => $pembimbing)
                                            <tr>
                                                <td class="text-center" style="width: 2%">{{ $index++ }}</td>
                                                <td>
                                                    {{ $pembimbing->user->name }} - {{ $pembimbing->user->unique_id }}
                                                    <br/>
                                                    <small class="text-muted">Pembimbing {{ $i + 1 }}</small>
                                                </td>
                                                <td class="text-end" style="width: 10%">
                                                    <button class="btn my-1 icon btn-sm btn-info" disabled>Ubah</button>
                                                    <button class="btn my-1 icon btn-sm btn-danger" disabled>Hapus</button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        @foreach ($pengujis as $i => $penguji )
                                            <tr>
                                                <td class="text-center" style="width: 2%">{{ $index++ }}</td>
                                                <td>
                                                    {{ $penguji->user->name }} - {{ $penguji->user->unique_id }}
                                                    <br/>
                                                    <small class="text-muted">Penguji {{ $i + 1 }}</small>
                                                </td>
                                                <td class="text-end" style="width: 10%">
                                                    <button class="btn my-1 icon btn-sm btn-info" disabled>Ubah</button>
                                                    <button class="btn my-1 icon btn-sm btn-danger" disabled>Hapus</button>
                                                </td>
                                            </tr>
                                        @endforeach

                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $index++ }}</td>
                                            <td>
                                                {{ $submission->student->name }} - {{ $submission->student->unique_id }}
                                                <br/>
                                                <small class="text-muted">Mahasiswa</small>
                                                <br/>
                                                <small class="text-muted">Otomatis dari sistem</small>
                                            </td>
                                            <td class="text-end" style="width: 10%">
                                                <button class="btn my-1 icon btn-sm btn-info" disabled>Ubah</button>
                                                <button class="btn my-1 icon btn-sm btn-danger" disabled>Hapus</button>
                                            </td>
                                        </tr>

                                        @foreach ($presences as $presence)
                                            <tr>
                                                <td class="text-center" style="width: 2%">{{ $index++ }}</td>
                                                <td>
                                                    {{ $presence->name }} - {{ $presence->unique_id }}
                                                    <br/>
                                                    <small class="text-muted">{{ $presence->keterangan }}</small>
                                                </td>
                                                <td class="text-end" style="width: 10%">
                                                    <button class="btn my-1 icon btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#primary" onclick="modalAction('Simpan Perubahan', 'Ubah Data Kehadiran', 'put', 'info', '{{ route('panel.admin.pengajuan.presences.update', ['type_slug' => $type->slug, 'submission_id' => $submission->id, 'id' => $presence->id]) }}', '{{ $presence->name }}', '{{ $presence->unique_id }}', '{{ $presence->keterangan }}')">Ubah</button>
                                                    <button class="btn my-1 icon btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#primary" onclick="modalAction('Hapus Data', 'Hapus Data Kehadiran', 'delete', 'danger', '{{ route('panel.admin.pengajuan.presences.destroy', ['type_slug' => $type->slug, 'submission_id' => $submission->id, 'id' => $presence->id]) }}', '{{ $presence->name }}', '{{ $presence->unique_id }}', '{{ $presence->keterangan }}')">Hapus</button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade text-left" id="primary" tabindex="-1" aria-labelledby="myModalLabel160" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <form id="modalForm" method="post">
                    @csrf
                    <input type="hidden" name="_method" id="modalMethod">
                    <div class="modal-header bg-primary" id="modalHeader">
                        <h5 class="modal-title white" id="myModalLabel160">Tambah Kehadiran</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="nama">Nama</label>
                            <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama">
                        </div>
                        <div class="form-group">
                            <label for="nim_nip_nidk">NIM/NIP/NIDK/NIDN</label>
                            <input type="text" class="form-control" id="nim_nip_nidk" name="nim_nip_nidk" placeholder="NIM/NIP/NIDK/NIDN">
                        </div>
                        <div class="form-group">
                            <label for="keterangan">Keterangan</label>
                            <select class="form-select" id="keterangan" name="keterangan">
                                <option value="mahasiswa">Mahasiswa</option>
                                <option value="dosen">Dosen</option>
                                <option value="lainnya">Lainnya</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <span class="d-block">Tidak</span>
                        </button>
                        <button type="submit" class="btn btn-primary ml-1" onclick="onLoading(this)" id="modalButton">
                            <span class="d-block">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade text-left" id="presence" tabindex="-1" aria-labelledby="presenceModalLabel" style="display: none;" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
            <div class="modal-content">
                <form action="{{ route('panel.admin.pengajuan.presences.public-url.update', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}" id="modalFormPresence" method="post">
                    @csrf
                    @method('PUT')

                    <div class="modal-header bg-primary" id="presenceModalHeader">
                        <h5 class="modal-title white" id="presenceModalLabel">Ubah URL Publik Presensi</h5>
                        <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="formInputLinkPresensi">Link Presensi</label>
                            <input type="text" class="form-control" id="formInputLinkPresensi" value="{{ route('isi-daftar-hadir.index', ['type_slug' => $type->slug, 'submission_id' => $submission->id]) }}" disabled>
                            <span class="form-text text-muted">Link ini akan digunakan oleh pengguna publik untuk mengisi daftar hadir.</span>
                            <br/>
                            <button type="button" class="btn btn-sm btn-info" onclick="copyLink()">Klik disini untuk salin link diatas</butt>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="presenceModalInputTanggaldanJamDibuka">Tanggal dan Jam dibuka</label>
                                    <input type="text" class="form-control" id="presenceModalInputTanggaldanJamDibuka" name="tanggal_dan_jam_dibuka" value="{{ $submission->presence_public_url_open_datetime ? date('d-m-Y H:i', strtotime($submission->presence_public_url_open_datetime)) : '' }}" {{ $submission->presence_public_url_status == 'active' ? 'required' : '' }}>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="presenceModalInputTanggaldanJamDitutup">Tanggal dan Jam ditutup</label>
                                    <input type="text" class="form-control" id="presenceModalInputTanggaldanJamDitutup" name="tanggal_dan_jam_ditutup" value="{{ $submission->presence_public_url_close_datetime ? date('d-m-Y H:i', strtotime($submission->presence_public_url_close_datetime)) : '' }}" {{ $submission->presence_public_url_status == 'active' ? 'required' : '' }}>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Status</label>
                            <div class="d-flex">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="status" id="status_active" value="active" onclick="changeStatusScheduleActive(this)" {{ $submission->presence_public_url_status == 'active' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="status_active">
                                        Aktif
                                    </label>
                                </div>
                                <div class="form-check ms-2">
                                    <input class="form-check-input" type="radio" name="status" id="status_inactive" value="inactive" onclick="changeStatusScheduleActive(this)" {{ $submission->presence_public_url_status == 'inactive' ? 'checked' : '' }} required>
                                    <label class="form-check-label" for="status_inactive">
                                        Tidak Aktif
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                            <span class="d-block">Tidak</span>
                        </button>
                        <button type="submit" class="btn btn-primary ml-1" onclick="onLoadingPresenceModal(this)" id="modalButton">
                            <span class="d-block">Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datetimepicker/jquery.js') }}"></script>
<script src="{{ asset('plugins/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script>
    jQuery.datetimepicker.setLocale('id');
    jQuery('#presenceModalInputTanggaldanJamDibuka').datetimepicker({
        format:'d-m-Y H:i',
        allowTimes:[
            '07:00',
            '07:15',
            '07:30',
            '07:45',
            '08:00',
            '08:15',
            '08:30',
            '08:45',
            '09:00',
            '09:15',
            '09:30',
            '09:45',
            '10:00',
            '10:15',
            '10:30',
            '10:45',
            '11:00',
            '11:15',
            '11:30',
            '11:45',
            '12:00',
            '12:15',
            '12:30',
            '12:45',
            '13:00',
            '13:15',
            '13:30',
            '13:45',
            '14:00',
            '14:15',
            '14:30',
            '14:45',
            '15:00',
            '15:15',
            '15:30',
            '15:45',
            '16:00',
            '16:15',
            '16:30',
            '16:45',
        ]
    });

    jQuery.datetimepicker.setLocale('id');
    jQuery('#presenceModalInputTanggaldanJamDitutup').datetimepicker({
        format:'d-m-Y H:i',
        allowTimes:[
            '07:00',
            '07:15',
            '07:30',
            '07:45',
            '08:00',
            '08:15',
            '08:30',
            '08:45',
            '09:00',
            '09:15',
            '09:30',
            '09:45',
            '10:00',
            '10:15',
            '10:30',
            '10:45',
            '11:00',
            '11:15',
            '11:30',
            '11:45',
            '12:00',
            '12:15',
            '12:30',
            '12:45',
            '13:00',
            '13:15',
            '13:30',
            '13:45',
            '14:00',
            '14:15',
            '14:30',
            '14:45',
            '15:00',
            '15:15',
            '15:30',
            '15:45',
            '16:00',
            '16:15',
            '16:30',
            '16:45',
        ]
    });

    const modalAction = (modalButtonLabel, modalLabel, method, bg_class, url, name, unique_id, keterangan) => {
        const modalHeader = document.getElementById('modalHeader');
        const modalForm = document.getElementById('modalForm');
        const modalMethod = document.getElementById('modalMethod');
        const myModalLabel = document.getElementById('myModalLabel160');
        const modalButton = document.getElementById('modalButton');

        const formNama = document.getElementById('nama');
        const formUnique_id = document.getElementById('nim_nip_nidk');
        const formKeterangan = document.getElementById('keterangan');

        modalHeader.setAttribute('class', ``);
        modalHeader.setAttribute('class', `modal-header bg-${bg_class}`);

        modalForm.setAttribute('action', url);
        modalMethod.setAttribute('value', method);

        formNama.setAttribute('value', name);
        formUnique_id.setAttribute('value', unique_id);
        if (keterangan == '') {
            keterangan = 'mahasiswa';
        }
        formKeterangan.value = keterangan;
        myModalLabel.innerHTML = modalLabel;
        modalButton.innerHTML = modalButtonLabel;
    }

    const onLoading = (e) => {
        e.setAttribute('disabled', true);
        e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
        const modalForm = document.getElementById('modalForm');

        modalForm.submit();
    }

    const modalPresenceSharingAction = () => {
        const modalHeader = document.getElementById('presenceModalHeader');
        const modalForm = document.getElementById('modalFormPresence');
        const modalMethod = document.getElementById('modalFormPresenceMethod');

        const formSlug = document.getElementById('presenceModalInputSlug');
        const formTanggaldanJamDibuka = document.getElementById('presenceModalInputTanggaldanJamDibuka');
        const formTanggaldanJamDitutup = document.getElementById('presenceModalInputTanggaldanJamDitutup');
    }

    const changeStatusScheduleActive = (e) => {
        const presenceModalInputTanggaldanJamDibuka = document.getElementById('presenceModalInputTanggaldanJamDibuka');
        const presenceModalInputTanggaldanJamDitutup = document.getElementById('presenceModalInputTanggaldanJamDitutup');

        if (e.value == 'active') {
            presenceModalInputTanggaldanJamDibuka.setAttribute('required', true);
            presenceModalInputTanggaldanJamDitutup.setAttribute('required', true);
        } else {
            presenceModalInputTanggaldanJamDibuka.setAttribute('required', false);
            presenceModalInputTanggaldanJamDitutup.setAttribute('required', false);
        }
    }

    const onLoadingPresenceModal = (e) => {
        e.setAttribute('disabled', true);
        e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
        const modalForm = document.getElementById('modalFormPresence');

        modalForm.submit();
    }

    const copyLink = () => {
        /* Get the text field */
        var copyText = document.getElementById("formInputLinkPresensi");

        /* Select the text field */
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* For mobile devices */

        /* Copy the text inside the text field */
        navigator.clipboard.writeText(copyText.value);

        /* Alert the copied text */
        alert("Berhasil salin link: " + copyText.value);
    }
</script>
@endpush
