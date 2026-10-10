@extends('layouts.panel.index')
@section('title', 'Detail Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.mahasiswa.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.mahasiswa.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.mahasiswa.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">
                            @if ($submission->is_ready_to_be_processed == 0)
                                <form
                                    action="{{ route('panel.mahasiswa.pengajuan.finish.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                    method="post">
                                    @csrf
                                    @method('PUT')
                            @endif

                            @if ($submission->is_ready_to_be_processed)
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    <strong>Anda telah menyetujui proses skripsi dibawah ini!</strong>
                                    <br/>
                                    Proses lebih lanjut akan diberitahukan kemudian.
                                </div>
                            @else
                                <div class="alert alert-secondary alert-dismissible fade show" role="alert">
                                    <strong>Anda belum menyetujui syarat dan ketentuan proses skripsi dibawah ini!</strong>
                                    <br/>
                                    Agar proses skripsi dapat dilanjutkan, anda harus menyetujui syarat dan ketentuan proses skripsi ini.
                                </div>
                            @endif

                            <div>
                                {!! \App\Models\Setting::where('key', 'terms_of_service')->first() ? \App\Models\Setting::where('key', 'terms_of_service')->first()->value : '' !!}
                            </div>

                            @if ($submission->format->is_need_trial_schedule)
                                <div class="form-group">
                                    <label for="permintaan_jadwal_pelaksanaan_seminar">Permintaan jadwal pelaksanaan seminar</label>
                                    <input type="text" class="form-control" id="permintaan_jadwal_pelaksanaan_seminar" min="" name="permintaan_jadwal_pelaksanaan_seminar" value="{{ $submission->schedule_request ? date('d-m-Y H:i', strtotime($submission->schedule_request)) : '' }}" {{ $submission->is_ready_to_be_processed ? 'disabled readonly' : '' }}>
                                    <small class="text-sm text-muted">Anda dapat melakukan permintaan jadwal pelaksanaan seminar anda dengan mengisi input diatas, jika anda tidak menginginkannya maka cukup biarkan kosong.</small>
                                    <br/>
                                    <small class="text-sm text-danger">Rekomendasi jadwal yang dapat dipilih hanyalah satu minggu kedepan dari hari ini, namun hal tersebut tidak menutup kemungkinan bahwa jadwal pelaksanaan seminar anda dapat diputuskan oleh staff bagian administrasi lebih awal.</small>
                                </div>

                                <div class="form-group">
                                    <label for="jadwal_pelaksanaan_seminar">Penetapan jadwal pelaksanaan seminar</label>
                                    <input type="text" class="form-control" id="jadwal_pelaksanaan_seminar" value="{{ $submission->schedule ? date('d-m-Y H:i', strtotime($submission->schedule)) : '' }}" disabled>
                                    <small class="text-sm text-muted">Berikut adalah penetapan jadwal pelaksanaan seminar anda berdasarkan keputusan pihak Bagian Administrasi.</small>
                                </div>
                            @endif

                            <h6>Dengan ini menyatakan bahwa:</h6>
                            <div class="form-check">
                                <div class="checkbox">
                                    <input type="checkbox" id="setuju_dengan_syarat_dan_ketentuan" name="setuju_dengan_syarat_dan_ketentuan" value=true class="form-check-input" {{ $submission->is_ready_to_be_processed ? 'checked disabled' : 'required' }}>
                                    <label for="setuju_dengan_syarat_dan_ketentuan">Setuju terhadap syarat dan ketentuan yang berlaku diatas.</label>
                                </div>
                            </div>

                            @if ($submission->is_ready_to_be_processed == 1)
                                <small class="text-sm text-muted mt-3">
                                    Anda telah menyetujui syarat dan ketentuan ini pada tanggal {{ date('d-m-Y H:i', strtotime($submission->ready_at)) }}
                                </small>
                            @endif

                        </div>
                        @if ($submission->is_ready_to_be_processed == 0)
                            <div class="col-sm-12 d-flex justify-content-end">
                                <button type="submit" class="btn btn-primary me-1 mb-1">Ajukan Proses</button>
                            </div>
                        @endif
                        @if ($submission->is_ready_to_be_processed == 0)
                            </form>
                        @endif
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
        jQuery('#permintaan_jadwal_pelaksanaan_seminar').datetimepicker({
            format:'d-m-Y H:i',
            allowTimes:[
                '07:00',
                '08:00',
                '09:00',
                '10:00',
                '11:00',
                '12:00',
                '13:00',
                '14:00',
                '15:00',
                '16:00',
            ],
            minDate: '{{ now()->addDays(6)->format('Y-m-d\TH:i') }}',

        });
    </script>
@endpush
