@extends('layouts.panel.index')
@section('title', 'Memutuskan Hasil Pengajuan Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
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
                    <!-- <a href="{{ route('panel.dosen.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a> -->
                    <h3 class="mt-4">Memutuskan Hasil Pengajuan - {{ $type->name }}</h3>
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
                        <div class="tab-pane fade show active">

                            <div class="row mt-3">
                                <h5 class="mb-0">Penentuan status hasil sidang/seminar dan penilaian</h5>
                                <small class="text-danger">Hanya tim pembimbing yang dapat memutuskan hasil.</small>
                                <br/>
                                <br/>
                                @if ($verificator->status == \App\Models\Verificator::STATUS_PENDING)
                                    <div class="alert alert-warning">
                                        <p>
                                            <i class="fa fa-info-circle"></i>
                                            Anda belum memberikan persetujuan seminar/sidang dan penilaian untuk pengajuan ini
                                        </p>
                                    </div>
                                @else
                                    @if (
                                        ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                        $verificator->type == 'Pembimbing'
                                    )
                                        <form action="{{ route('panel.dosen.pengajuan.make-result.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" method="post">
                                            @csrf
                                            @method('PUT')
                                    @endif

                                        <div class="row mb-4">
                                            <div class="col-12 col-md-6">
                                                <div class="form-group mb-4">
                                                    <label class="">Hasil keputusan <span class="text-danger">*</span></label>
                                                    <select class="form-select"
                                                        @if (
                                                            ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                                            $verificator->type == 'Pembimbing'
                                                        )
                                                            name="hasil_sidang" onchange="changeResultStatus(this)"
                                                        @else
                                                            disabled
                                                        @endif
                                                    >
                                                        <option value="pending" {{ $submission->result_status == 'pending' ? 'selected' : '' }}>Menunggu Keputusan</option>
                                                        <option value="approved_with_fixed" {{ $submission->result_status == 'approved_with_fixed' ? 'selected' : '' }}>Diterima dengan perbaikan</option>
                                                        <option value="approved" {{ $submission->result_status == 'approved' ? 'selected' : '' }}>Diterima</option>
                                                        <option value="repeated" {{ $submission->result_status == 'repeated' ? 'selected' : '' }}>Diulang, rekomendasi waktu seminar ulang</option>
                                                        <option value="rejected" {{ $submission->result_status == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                                                    </select>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-info-circle"></i> Pilih hasil keputusan sidang
                                                        <ol>
                                                            <li>Menunggu Keputusan</li>
                                                            <li>Diterima dengan perbaikan (mahasiswa mengupload dokumen final revisi, dan perlu persetujuan diterima/ditolak oleh tim)</li>
                                                            <li>Diterima (Mahasiswa tidak perlu upload revisi, dan berhak lanjut ke tahapan selanjutnya)</li>
                                                            <li>Diulang, rekomendasi waktu seminar ulang (mahasiswa akan mengulang seminar dengan memberikan rekomendasi jadwal)</li>
                                                            <li>Ditolak (Gugur sehingga mahasiswa tidak dapat revisi ataupun melanjutkan ke tahapan selanjutnya)</li>
                                                        </ol>
                                                    </small>
                                                </div>
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <div class="form-group mb-4">
                                                    <label class="">Rekomendasi waktu seminar hasil ulang</label>
                                                    <input type="text" class="form-control"
                                                        @if (
                                                            ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                                            $verificator->type == 'Pembimbing'
                                                        )
                                                            name="rekomendasi_waktu_sidang_ulang"
                                                        @else
                                                            disabled
                                                        @endif
                                                        id="rekomendasi_waktu_sidang_ulang"
                                                        {{ $submission->result_status == 'repeated' ? '' : 'disabled required' }}
                                                        value="{{ $submission->recomendation_repeated_schedule ? date('d-m-Y H:i', strtotime($submission->recomendation_repeated_schedule)) : '' }}" />
                                                </div>
                                            </div>

                                            <div class="col-md-12">
                                                <div class="form-group mb-4">
                                                    <label class="">Rekomendasi Perubahan Judul</label>
                                                    <input type="text" class="form-control" id="rekomendasi_perubahan_judul_field"
                                                        @if (
                                                            ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                                            $verificator->type == 'Pembimbing'
                                                        )
                                                            name="rekomendasi_perubahan_judul"
                                                        @else
                                                            disabled
                                                        @endif
                                                        value="{{ $submission->next_title_recomendation }}" />
                                                    <small class="form-text text-muted">
                                                        <i class="fa fa-info-circle"></i>
                                                        Jika terdapat rekomendasi perubahan judul dari hasil sidang/seminar yang diterima, maka judul pada proses/tahapan selanjutnya akan diubah menjadi judul yang diinput di sini, namun jika tidak ada maka cukup biarkan kosong.
                                                    </small>

                                                    <div style="display:block">
                                                    <button type="button" class="btn btn-danger" onClick="uppercaseJudul()" style="border-color:#a333c8 !important; background-color:#a333c8 !important">UPPERCASE</button>
                                                    <button type="button" class="btn btn-success" style="border-color:#a5673f !important;background-color:#a5673f !important; margin-left:1rem" onClick="capitalizeJudul()">TitleCase</button>
                                              
                                                    </div>
                                                </div>

                                                @if ($submission->format->is_need_trial_schedule)
                                                    <div class="form-group mb-4">
                                                        <label class="">Hasil Rekaman Sidang</label>
                                                        <input type="text" class="form-control"
                                                            @if (
                                                                ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                                                $verificator->type == 'Pembimbing'
                                                            )
                                                                name="hasil_rekaman_sidang"
                                                            @else
                                                                disabled
                                                            @endif
                                                            value="{{ $submission->record_url }}">
                                                        <small class="form-text text-muted">
                                                            <i class="fa fa-info-circle"></i>
                                                            Berguna untuk menyimpan link hasil rekaman sidang/seminar, agar dapat dilihat kembali oleh mahasiswa ataupun dosen
                                                        </small>
                                                    </div>
                                                @endif
                                            </div>

                                            @if ($submission->verificator_id_who_submit_result != null)
                                                <div class="col-12">
                                                    <p class="mb-0">Ditetapkan oleh: <b>{{ \App\Models\Verificator::where('id', $submission->verificator_id_who_submit_result)->first()->user->name }}</b></p>
                                                    <small class="text-muted text-sm">Terakhir update pada: {{ $submission->updated_submit_result_at ?: '-' }}</small>
                                                </div>
                                            @endif
                                        </div>

                                    @if (
                                        ($submission->verificator_id_who_submit_result == null || $submission->verificator_id_who_submit_result == $verificator->id) &&
                                        $verificator->type == 'Pembimbing'
                                    )
                                        <div class="row text-end">
                                            <button type="submit" class="btn btn-primary">Tetapkan Hasil Sidang</button>
                                        </div>
                                    </form>
                                    @endif
                                @endif
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="show" tabindex="-1" aria-labelledby="showModal" aria-hidden="true"
        style="display: none;">
        <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title white" id="showModal">Lihat</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <i data-feather="x"></i>
                    </button>
                </div>
                <div class="modal-body">
                    <iframe width="100%" height="630" src="" ></iframe>
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
    <script src="{{ asset('plugins/datetimepicker/jquery.js') }}"></script>
    <script src="{{ asset('plugins/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script>
        jQuery.datetimepicker.setLocale('id');
        jQuery('#rekomendasi_waktu_sidang_ulang').datetimepicker({
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
            ]
        });

        const showModal = (url) => {
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            iframe.src = `${url}/preview`;
        }

        const changeResultStatus = (e) => {
            const resultStatus = e.value;
            let rekomendasi_waktu_sidang_ulang = document.getElementById('rekomendasi_waktu_sidang_ulang');
            if (resultStatus == 'repeated') {
                rekomendasi_waktu_sidang_ulang.disabled = false;
                rekomendasi_waktu_sidang_ulang.required = true;
            } else {
                rekomendasi_waktu_sidang_ulang.disabled = true;
                rekomendasi_waktu_sidang_ulang.required = false;
                rekomendasi_waktu_sidang_ulang.value = '';
            }
        }

        $("textarea").each(function () {
            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });

        function uppercaseJudul(){
            let text = $('#rekomendasi_perubahan_judul_field').val();

            text = text.toUpperCase();
            
            $('#rekomendasi_perubahan_judul_field').val(text);
        }

        function capitalizeJudul(){
            let text = $('#rekomendasi_perubahan_judul_field').val();

            text = toTitleCase(text);

            $('#rekomendasi_perubahan_judul_field').val(text);
        }

        function toTitleCase(str) {
            return str.replace(
                /\w\S*/g,
                text => text.charAt(0).toUpperCase() + text.substring(1).toLowerCase()
            );
            }
    </script>
@endpush
