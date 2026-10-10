@extends('layouts.panel.index')
@section('title', 'Detail Hasil Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
<style>
    .filepond--credits{
        display: none;
    }
</style>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>

@php
    $submission_required_values = $submission->format->required_values;

    $submission_score = 0;

    if ($submission_required_values->count() == 0) {
        $submission_score = 'Tidak ada penilaian pada pengajuan ini';
    } else {
        if ($submission->score) {
            $submission_score = number_format($submission->score, 2) . ' (' . $submission->grade . ')';
        } else  {
            $submission_score = 'Belum ada penilaian';
        }
    }
@endphp
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Hasil Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.admin.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.admin.pengajuan.show.tab-menu')

                    <form action="{{ route('panel.admin.pengajuan.make-result.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" method="post">
                        @csrf
                        @method('PUT')

                        <div class="tab-content text-justify mt-3">
                            <div class="tab-pane fade show active">
                                <h5>Penentuan status hasil sidang/seminar dan penilaian</h5>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="">Hasil keputusan sidang <span class="text-danger">*</span></label>
                                            <select class="form-select" name="hasil_sidang" onchange="changeResultStatus(this)">
                                                <option value="pending" {{ $submission->result_status == 'pending' ? 'selected' : '' }}>Menunggu keputusan</option>
                                                <option value="approved_with_fixed" {{ $submission->result_status == 'approved_with_fixed' ? 'selected' : '' }}>Diterima dengan perbaikan</option>
                                                <option value="approved" {{ $submission->result_status == 'approved' ? 'selected' : '' }}>Diterima</option>
                                                <option value="repeated" {{ $submission->result_status == 'repeated' ? 'selected' : '' }}>Diulang, rekomendasi waktu seminar hasil ulang</option>
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
                                    <div class="col-md-6">
                                        <div class="form-group mb-4">
                                            <label class="">Rekomendasi waktu seminar hasil ulang</label>
                                            <input type="text" class="form-control" name="rekomendasi_waktu_sidang_ulang"
                                                id="rekomendasi_waktu_sidang_ulang"
                                                {{ $submission->result_status == 'repeated' ? '' : 'disabled required' }}
                                                value="{{ $submission->recomendation_repeated_schedule ? date('d-m-Y H:i', strtotime($submission->recomendation_repeated_schedule)) : '' }}" />
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="form-group mb-4">
                                            <label class="">Rekomendasi Perubahan Judul</label>
                                            <input type="text" class="form-control" name="rekomendasi_perubahan_judul" value="{{ $submission->next_title_recomendation }}" />
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                Jika terdapat rekomendasi perubahan judul dari hasil sidang/seminar yang diterima, maka judul pada proses/tahapan selanjutnya akan diubah menjadi judul yang diinput di sini, jika tidak ada maka cukup biarkan kosong.
                                            </small>
                                        </div>
                                        <div class="form-group mb-4">
                                            <label class="">Hasil Rekaman Sidang</label>
                                            <input type="text" class="form-control" name="hasil_rekaman_sidang"
                                                value="{{ $submission->record_url }}">
                                            <small class="form-text text-muted">
                                                <i class="fa fa-info-circle"></i>
                                                Berguna untuk menyimpan link hasil rekaman sidang/seminar, agar dapat dilihat kembali oleh mahasiswa ataupun dosen
                                            </small>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1" id="btnSubmit">Simpan Perubahan</button>
                                </div>
                            </div>
                        </div>
                    </form>
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
    <script src="https://unpkg.com/filepond-plugin-file-validate-type/dist/filepond-plugin-file-validate-type.js"></script>
    <script src="https://unpkg.com/filepond@^4/dist/filepond.js"></script>

    <script>
        const btnSubmit = document.getElementById('btnSubmit');
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
    </script>
@endpush
