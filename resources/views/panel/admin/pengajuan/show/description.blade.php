@extends('layouts.panel.index')
@section('title', 'Detail Deskripsi Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
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
                        <div class="tab-pane fade show active">
                                @php
                                if(in_array($type->slug, ['proposal', 'sidang-akhir'])){
                                    echo "<div style='font-weight:bold;margin:1rem 1rem'>Banyak kehadiran mahasiswa ini untuk ".$type->name." : $banyakKehadiran</div>"; 
                                }
                                    
                                @endphp
                            <form
                                action="{{ route('panel.admin.pengajuan.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                method="post" enctype="multipart/form-data">
                                @csrf
                                @method('PUT')

                                <div class="form-group required">
                                    <label for="judul" class="control-label">Judul</label>
                                    <input type="text" class="form-control @error('judul') is-invalid @enderror" id="judul"
                                        name="judul" value="{{ $submission->title }}" placeholder="Judul Pengajuan Anda"
                                        required>
                                    @error('judul')
                                        <div class="invalid-feedback">
                                            <i class="bx bx-radio-circle"></i>
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>

                                @if ($submission->format->is_skill_group_required)
                                    <div class="form-group required">
                                        <label for="bidang_keahlian" class="control-label">Bidang Keahlian</label>
                                        <select class="form-control @error('bidang_keahlian') is-invalid @enderror"
                                            id="bidang_keahlian" name="bidang_keahlian">
                                            <option value="">Tidak ada Kelompok Keahlian</option>
                                            @foreach ($skill_groups as $skill_group)
                                                <option value="{{ $skill_group->id }}"
                                                    {{ $skill_group->id == $submission->skill_group_id ? 'selected' : '' }}>
                                                    {{ $skill_group->name }}</option>
                                            @endforeach
                                        </select>
                                        @error('bidang_keahlian')
                                            <div class="invalid-feedback">
                                                <i class="bx bx-radio-circle"></i>
                                                {{ $message }}
                                            </div>
                                        @enderror
                                    </div>
                                @endif

                                <div class="form-group required">
                                    <label for="siap_di_proses" class="control-label">Sudah siap diproses ?</label>
                                    <select class="form-select" id="siap_di_proses" name="sudah_siap_di_proses" required>
                                        <option value="true" {{ $submission->is_ready_to_be_processed ? 'selected' : '' }}>Saya sudah siap diproses.</option>
                                        <option value="false" {{ $submission->is_ready_to_be_processed == false ? 'selected' : '' }}>Saya belum siap diproses.</option>
                                    </select>
                                    <small class="text-sm text-muted mt-3">
                                            Mahasiswa telah menyetujui syarat dan ketentuan ini pada tanggal {{ $submission->ready_at ? date('d-m-Y H:i', strtotime($submission->ready_at)) : '-' }}
                                    </small>
                                    <br/>
                                    <small class="text-danger">Ubah opsi menjadi sudah siap diproses agar proses verifikasi pemberkasan bisa dilakukan oleh pihak bagian administrasi. Ingat jika sudah siap diproses maka opsi tidak dapat dirubah kembali.</small>
                                </div>

                                @if ($submission->format->is_need_trial_schedule)
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="permintaan-jadwal-seminar">Permintaan Jadwal Seminar</label>
                                                <input type="text" class="form-control" value="{{ $submission->schedule_request ? date('d-m-Y H:i', strtotime($submission->schedule_request)) : '' }}" disabled>
                                                <small class="text-sm text-muted">Merupakan permintaan jadwal seminar oleh mahasiswa.</small>
                                            </div>
                                        </div>
                                        <hr />
                                        <h5>Penetapan Jadwal Seminar</h5>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="">Tentukan jadwal sidang</label>
                                                <input type="text" class="form-control" name="jadwal_sidang" id="jadwal_sidang"
                                                    value="{{ $submission->schedule ? date('d-m-Y H:i', strtotime($submission->schedule)) : '' }}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group mb-4">
                                                <label class="">Tentukan Ruangan Offline / Online</label>
                                                <input type="text" class="form-control" name="ruangan"
                                                    value="{{ $submission->room }}">
                                                <small class="text-danger">Catatan: jika ruangan berupa online maka
                                                    sertakan url meeting room</small>
                                                <br/>
                                                @if ($submission->room != null && str_contains($submission->room, 'http'))
                                                    <a href="{{ $submission->room }}" class="badge bg-info mt-2" target="_blank">Klik disini untuk masuk ke meeting room</a>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <div class="form-group required mb-4">
                                    <label class="control-label">Status Pengajuan</label>
                                    <select class="form-select" name="status">
                                        <option value="pending">Pending</option>
                                        <option value="processed"
                                            {{ $submission->status == 'processed' ? 'selected' : '' }}>Sedang Proses
                                        </option>
                                        <option value="approved"
                                            {{ $submission->status == 'approved' ? 'selected' : '' }}>Diterima</option>
                                        <option value="repeated"
                                            {{ $submission->status == 'repeated' ? 'selected' : '' }}>Diulang</option>
                                        <option value="rejected"
                                            {{ $submission->status == 'rejected' ? 'selected' : '' }}>Gugur/Ditolak</option>
                                    </select>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="">Jumlah SKS saat ini</label>
                                            <input type="number" class="form-control" name="jumlah_sks"
                                                value="{{ $submission->amount_sks_now }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="">IPK saat ini</label>
                                            <input type="number" step="0.01" class="form-control" name="ipk"
                                                value="{{ $submission->ipk_now }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group required mb-4">
                                            <label class="control-label">Tahun Akademik</label>
                                            <input type="text" class="form-control" name="tahun_akademik"
                                                value="{{ $submission->academic_year }}" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group required mb-4">
                                            <label class="control-label">Semester</label>
                                            <input type="text" class="form-control" name="semester"
                                                value="{{ $submission->semester }}" required>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-group required mb-4">
                                            <label class="control-label">Format Pemberkasan & Penilaian</label>
                                            <select class="form-select" name="format_pemberkasan_penilaian" required>
                                                @foreach ($formats as $format)
                                                    <option value="{{ $format->id }}" {{ $submission->format_id == $format->id ? 'selected' : '' }}>{{ $format->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-sm-12 d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
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
        jQuery('#jadwal_sidang').datetimepicker({
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
    </script>
@endpush
