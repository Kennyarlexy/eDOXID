@extends('layouts.panel.index')
@section('title', 'Detail Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <?php
                        $urlKembali = route('panel.dosen.pengajuan.index', $type->slug);
                        if (isset($_GET['from'])) {
                            $urlKembali = $_GET['from'];
                        }
                    ?>
                    <a href="{{ $urlKembali }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.dosen.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.dosen.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <!-- Deskripsi -->
                        <div class="tab-pane fade show active" id="deskripsi" role="tabpanel" aria-labelledby="deskripsi-tab">
                            <div class="row">                
                                @php
                                if(in_array($type->slug, ['proposal', 'sidang-akhir'])){
                                    echo "<div>Banyak kehadiran mahasiswa ini untuk ".$type->name." : $banyakKehadiran</div>"; 
                                }
                                    
                                @endphp

                                @php
                                    if ($auth_dosen->position == "Ketua Jurusan" || $auth_dosen->position == "Ketua Prodi"){
                                        $checkedBypass = '';
                                        if($submission->bypass_pengajuan_kaprodi == "1"){
                                            $checkedBypass = " checked='checked' ";
                                        }

                                        
                                @endphp         
                                       
                                        <form style="margin-bottom:1rem" action="{{ route('panel.dosen.pengajuan.updateBypass', ['type_slug' => $submission->type->slug, 'id' => $submission->id]) }}" method='post'>
                                        @csrf
                                        @method('PUT')        
                                        <div><input type='checkbox' name='checkboxBypass' style='width:25px;margin:1rem 0rem;' {{ $checkedBypass }}>Bypass pengecekan syarat presensi seminar/sidang untuk pengajuan ini </div>
                                                <input type='submit' style='float:right' class='btn btn-primary' value='Simpan'>
                                        </form>
                                        
                                @php
                                    }
                                @endphp
                                @if ($submission->skill_group_id)
                                    <div class="col-md-12">
                                        <div class="form-group mb-4">
                                            <label class="mb-2">Bidang Keahlian</label>
                                            <input type="text" class="form-control"
                                                value="{{ $submission->skill_group_id ? $submission->skill_group->name  : '-' }}" readonly disabled>
                                        </div>
                                    </div>
                                @endif
                                @if ($submission->format->is_need_trial_schedule)
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label for="permintaan-jadwal-seminar" class="mb-2">Permintaan Jadwal Seminar</label>
                                            <input type="text" class="form-control" value="{{ $submission->schedule_request ? date('d-m-Y H:i', strtotime($submission->schedule_request)) : '' }}" disabled>
                                            <small class="text-sm text-muted">Merupakan permintaan jadwal seminar oleh mahasiswa.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="mb-2">Jadwal Sidang</label>
                                            <input type="text" class="form-control"
                                                value="{{ $submission->schedule ? date('d-m-Y H:i', strtotime($submission->schedule)) : 'Belum ditentukan' }}"
                                                disabled readonly>
                                            <small class="text-sm text-muted">Merupakan penetapan jadwal seminar oleh bagian administrasi.</small>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-4">
                                            <label class="mb-2">Ruangan Sidang</label>
                                            <input type="text" class="form-control"
                                                value="{{ $submission->room ? $submission->room : 'Belum ditentukan' }}"
                                                disabled readonly>
                                            @if ($submission->room != null && str_contains($submission->room, 'http'))
                                                <a href="{{ $submission->room }}" class="badge bg-info mt-2" target="_blank">Klik disini untuk masuk ke meeting room</a>
                                            @endif
                                        </div>
                                    </div>
                                @endif
                            </div>
                            @if ($verificator != null && $verificator->status == 'pending')
                                    <div class="">
                                        <p class="mb-0">Tombol berikut untuk memberikan pernyataan bahwa anda sebagai Tim {{ $verificator->type }} <span class="text-danger font-bold">Menolak</span>/<span class="text-primary font-bold">Menyetujui</span> pengajuan ini untuk dilanjutkan ketahap seminar/sidang & penilaian.</p>
                                    </div>
                                    <div class="d-flex">
                                        <button type="button" class="btn btn-danger me-2" data-bs-toggle="modal" data-bs-target="#modalUpdate" onclick="changeStatus('rejected')">
                                            Ditolak
                                        </button>
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalUpdate" onclick="changeStatus('approved')">
                                            Disetujui
                                        </button>
                                    </div>
                                </form>
                            @endif

                            <div class="row mt-4">
                                <div class="col-12 col-md-6">
                                    <h5>Ditetapkan</h5>
                                    <ul>
                                        <li>Tanggal:
                                            {{ $submission->status === 'approved' ? $submission->updated_at->format('d-m-Y') : '' }}
                                        </li>
                                        <li>Waktu:
                                            {{ $submission->status === 'approved' ? $submission->updated_at->format('H:i') : '' }}
                                        </li>
                                        <li>Semester: {{ $submission->semester }}</li>
                                        <li>Tahun Akademik: {{ $submission->academic_year }}</li>
                                    </ul>
                                </div>
                                <div class="col-12 col-md-6">
                                    <h5>Pihak Verifikasi</h5>
                                    <ul class="list-group">
                                        @php
                                            $verificator_pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)
                                                ->orderBy('id', 'asc')
                                                ->where('type', 'Pembimbing')
                                                ->get();
                                            $verificator_pengujis = \App\Models\Verificator::where('submission_id', $submission->id)
                                                ->orderBy('id', 'asc')
                                                ->where('type', 'Penguji')
                                                ->get();
                                        @endphp

                                        @if ($submission->format->is_pembimbing_required)
                                            @if ($verificator_pembimbings->count() > 0)
                                                @foreach ($verificator_pembimbings as $i => $pembimbing)
                                                    <li class="list-group-item">
                                                        {{ $pembimbing->user->name }} <small class="text-muted">(Pembimbing {{ $i + 1 }})</small>
                                                        @if ($pembimbing->is_need_to_change_trial_status)
                                                            <span
                                                                class="badge text-capitalize ms-2 {{ badge_class($pembimbing->status) }}">
                                                                Seminar/Sidang {{ status_english_to_bahasa($pembimbing->status) }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            class="badge text-capitalize ms-2 {{ badge_class($pembimbing->final_status) }}">
                                                            Final Revisi {{ status_english_to_bahasa($pembimbing->final_status) }}
                                                        </span>
                                                        <br />
                                                        @if ($submission->format->required_values->count() > 0)
                                                            @if (\App\Models\SubmissionRequiredValue::where('verificator_id', $pembimbing->id)->where('submission_id', $submission->id)->count() > 0)
                                                                <small class="text-success" style="font-size:12px">Telah selesai melakukan penilaian</small>
                                                            @else
                                                                <small class="text-warning" style="font-size:12px">Penilaian belum
                                                                    selesai
                                                                    dilakukan</small>
                                                            @endif
                                                        @endif
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="list-group-item">
                                                    Pembimbing
                                                    <span class="badge bg-warning">
                                                        Belum Dikonfirmasi
                                                    </span>
                                                    <br />
                                                    <small class="text-muted">Belum ada pembimbing</small>
                                                </li>
                                            @endif
                                        @endif

                                        @if ($submission->format->is_penguji_required)
                                            @if ($verificator_pengujis->count() > 0)
                                                @foreach ($verificator_pengujis as $i => $penguji)
                                                    <li class="list-group-item">
                                                        {{ $penguji->user->name }} <small class="text-muted">(Penguji {{ $i + 1 }})</small>
                                                        @if ($penguji->is_need_to_change_trial_status)
                                                            <span
                                                                class="badge text-capitalize ms-2 {{ badge_class($penguji->status) }}">
                                                                Seminar/Sidang {{ status_english_to_bahasa($penguji->status) }}
                                                            </span>
                                                        @endif
                                                        <span
                                                            class="badge text-capitalize ms-2 {{ badge_class($penguji->final_status) }}">
                                                            Final Revisi {{ status_english_to_bahasa($penguji->final_status) }}
                                                        </span>
                                                        <br />
                                                        @if ($submission->format->required_values->count() > 0)
                                                            @if (\App\Models\SubmissionRequiredValue::where('verificator_id', $penguji->id)->where('submission_id', $submission->id)->count() > 0)
                                                                <small class="text-success" style="font-size:12px">Telah selesai melakukan penilaian</small>
                                                            @else
                                                                <small class="text-warning" style="font-size:12px">Penilaian belum selesai dilakukan</small>
                                                            @endif
                                                        @endif
                                                    </li>
                                                @endforeach
                                            @else
                                                <li class="list-group-item">
                                                    Penguji
                                                    <span class="badge bg-warning">
                                                        Belum Dikonfirmasi
                                                    </span>
                                                    <br />
                                                    <small class="text-muted">Belum ada penguji</small>
                                                </li>
                                            @endif
                                        @endif
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if ($verificator !== null && $verificator->status == 'pending')
        <div class="modal fade" id="modalUpdate" tabindex="-1" aria-labelledby="modalUpdateTitle" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
                <form action="{{ route('panel.dosen.pengajuan.update', ['type_slug' => $submission->type->slug, 'id' => $submission->id]) }}" method="post" id="formModal">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="status" name="status" value="" />
                    <div class="modal-content">
                        <div class="modal-header" id="headerModal">
                            <h5 class="modal-title text-white" id="headerModalUpdateTitle">Apakah anda yakin?</h5>
                            <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                                <i data-feather="x"></i>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group required mb-4">
                                <h5 class="font-bold">Peringatan</h5>
                                <p>
                                    Status pengajuan bertujuan untuk memberikan pernyataan bahwa anda sebagai tim {{ $verificator->type }} <span class="font-bold" id="statusTextModal"></span> pengajuan ini dilanjutkan ketahap seminar/sidang & penilaian.
                                </p>
                                <p class="text-danger text-sm">Perlu diingat bahwa pengubahan status diatas hanya dapat dilakukan sekali, jika terdapat kesalahan maka harap hubungi admin.</p>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                                <span class="d-sm-block">Tutup</span>
                            </button>
                            <button type="button" class="btn btn-primary ml-1" id="btnModal" onclick="onLoading(this)">
                                <span class="d-sm-block">Ya</span>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endsection

@push('scripts')
    <script>
        const changeStatus = (value) => {
            document.getElementById('headerModal').classList.remove('bg-primary', 'bg-danger');
            document.getElementById('btnModal').classList.remove('btn-primary', 'btn-danger');
            document.getElementById('statusTextModal').classList.remove('text-primary', 'text-danger');
            document.getElementById('status').value = value;

            if (value === 'approved') {
                document.getElementById('headerModal').classList.add('bg-primary');
                document.getElementById('btnModal').classList.add('btn-primary');
                document.getElementById('statusTextModal').innerHTML = 'Menyetujui';
                document.getElementById('statusTextModal').classList.add('text-primary');
            } else {
                document.getElementById('headerModal').classList.add('bg-danger');
                document.getElementById('btnModal').classList.add('btn-danger');
                document.getElementById('statusTextModal').innerHTML = 'Menolak';
                document.getElementById('statusTextModal').classList.add('text-danger');
            }
        }

        const onLoading = (e) => {
            e.setAttribute('disabled', true);
            e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
            document.getElementById('formModal').submit();
        }
    </script>
@endpush
