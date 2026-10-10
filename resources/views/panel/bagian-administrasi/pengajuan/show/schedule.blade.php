@extends('layouts.panel.index')
@section('title', 'Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('main')
<div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.bagian-administrasi.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Ubah - Pengajuan {{ $submission->type->name }}</h3>
                </div>
            </div>
        </div>

        <section class="section">
            @include('includes.form-alert')
                <div class="card">
                    @include('panel.bagian-administrasi.pengajuan.show.components.card-header')
                    <div class="card-body">
                        @include('panel.bagian-administrasi.pengajuan.show.tab-menu')

                        <div class="tab-content text-justify mt-3">
                            <div class="tab-pane fade show active">
                                <form action="{{ route('panel.bagian-administrasi.pengajuan.schedule.update', ['type_slug' => $submission->type->slug, 'id' => $submission->id]) }}" method="post" id="formSubmit">
                                    @csrf
                                    @method('PUT')
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="permintaan-jadwal-seminar">Permintaan Jadwal Seminar</label>
                                                <input type="text" class="form-control datepicker" value="{{ $submission->schedule_request ? date('d-m-Y H:i', strtotime($submission->schedule_request)) : '' }}" disabled>
                                                <small class="text-sm text-muted">Merupakan permintaan jadwal seminar oleh mahasiswa.</small>
                                            </div>
                                        </div>
                                        <hr />
                                        <h5>Silahkan tetapkan jadwal seminar mahasiswa</h5>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4 required">
                                                <label class="control-label">Jumlah SKS saat ini</label>
                                                <input type="number" class="form-control" name="jumlah_sks" required
                                                    value="{{ $submission->amount_sks_now }}">
                                            </div>
                                            <div class="form-group mb-4 required">
                                                <label class="control-label">IPK saat ini</label>
                                                <input type="number" step="0.01" class="form-control" name="ipk" required
                                                    value="{{ $submission->ipk_now }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="control-label">Tentukan jadwal sidang</label>
                                                <input type="text" class="form-control" id="tentukan_jadwal_sidang" name="jadwal_sidang" value="{{ $submission->schedule ? date('d-m-Y H:i', strtotime($submission->schedule)) : '' }}">
                                            </div>
                                            <div class="form-group mb-4 required">
                                                <label class="control-label">Tentukan Ruangan Offline / Online</label>
                                                <input type="text" class="form-control" name="ruangan" value="{{ $submission->room }}" required>
                                                <small class="text-danger">Catatan: jika ruangan berupa online maka sertakan url meeting room</small>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end mt-4">
                                        <button type="button" class="btn btn-primary me-1 mb-1" onclick="onLoading(this)">Simpan Perubahan</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
        </section>
    </div>
@endsection

@push('scripts')
<script src="{{ asset('plugins/datetimepicker/jquery.js') }}"></script>
<script src="{{ asset('plugins/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
<script>
    jQuery.datetimepicker.setLocale('id');
    jQuery('#tentukan_jadwal_sidang').datetimepicker({
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

    const onLoading = (e) => {
        e.setAttribute('disabled', true);
        e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
        document.getElementById('formSubmit').submit();
    }
</script>
@endpush
