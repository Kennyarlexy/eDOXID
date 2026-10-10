@extends('layouts.panel.index')
@section('title', 'Detail Hasil Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
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
                    <h3 class="mt-4">Detail Hasil Pengajuan - {{ $type->name }}</h3>
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
                            <div class="alert alert-{{ result_class_by_status($submission->result_status) }}">
                                <h4 class="alert-heading">Hasil Status Seminar Pengajuan: {{ result_status_english_to_bahasa($submission->result_status) }}</h4>
                                @if ($submission->result_status != \App\Models\Submission::RESULT_PENDING)
                                    <p>Hasil status seminar pengajuan mahasiswa sudah bersifat final dengan status: {{ result_status_english_to_bahasa($submission->result_status) }}</p>
                                    @if ($submission->result_status == \App\Models\Submission::RESULT_REPEATED)
                                        <p>Berikut merupakan rekomendasi tanggal seminar ulang yang direkomendasikan: {{ $submission->recomendation_repeated_schedule }}</p>
                                    @endif
                                @else
                                    <p>Hasil status seminar pengajuan mahasiswa belum bersifat final dengan status: {{ result_status_english_to_bahasa($submission->result_status) }}</p>
                                @endif
                            </div>

                            @if ($submission->result_status == \App\Models\Submission::RESULT_APPROVED_WITH_FIXED)
                                <hr />
                                <h5>Berkas Revisi</h5>
                                <p>Mahasiswa melakukan upload file berkas hasil revisi berdasarkan komentar para tim peneliti disini.</p>
                                <div class="form-group">
                                    @if ($submission->final_document_url !== null)
                                        <span class="text-muted">Terakhir update:
                                            {{ $submission->final_document_created_at ? $submission->final_document_created_at : '-' }}</span>
                                        <br/>
                                        @if (str_contains($submission->final_document_url, 'drive.google.com'))
                                            <a class="badge bg-success" style="cursor: pointer" onclick="showModal('{{ $submission->final_document_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                        @else
                                            <a class="badge bg-success" style="cursor: pointer" onClick="showModalWithoutPreview('{{ $submission->final_document_url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                        @endif
                                    @else
                                        <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                    @endif
                                </div>

                                @if ($verificator != null && $verificator->final_status == 'pending')
                                    <div class="w-50 ms-auto">
                                        <p class="text-end mb-2">Tombol berikut untuk memberikan pernyataan bahwa anda sebagai Tim {{ $verificator->type }} <span class="text-danger font-bold">Menolak</span>/<span class="text-primary font-bold">Menyetujui</span> revisi dari pengajuan ini.</p>
                                    </div>
                                    <div class="d-flex justify-content-end">
                                        <button type="button" class="btn btn-danger mx-2" data-bs-toggle="modal" data-bs-target="#modalUpdate" onclick="changeStatus('rejected')">
                                            Ditolak
                                        </button>
                                        <button type="button" class="btn btn-primary mx-2" data-bs-toggle="modal" data-bs-target="#modalUpdate" onclick="changeStatus('approved')">
                                            Disetujui
                                        </button>
                                    </div>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>

    @if ($verificator !== null && $verificator->final_status == 'pending')
        <div class="modal fade" id="modalUpdate" tabindex="-1" aria-labelledby="modalUpdateTitle" style="display: none;" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable modal-lg" role="document">
                <form action="{{ route('panel.dosen.pengajuan.revision.update', ['type_slug' => $type->slug, 'id' => $submission->id]) }}" method="post" id="formModal">
                    @csrf
                    @method('PUT')
                    <input type="hidden" id="status" name="status_final_revisi" value="" />
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
                                    Status final revisi diatas bertujuan untuk memberikan pernyataan bahwa anda sebagai tim {{ $verificator->type }} <span class="font-bold" id="statusTextModal"></span> final revisi yang telah diupload diatas.
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
                    <div id="parentLihatDiGoogleDocs"></div>
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
        const showModal = (url) => {
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            iframe.src = `${url}/preview`;
            $('#parentLihatDiGoogleDocs').html('');
        }

        const showModalWithoutPreview = (url) => {
            const modal = document.getElementById('show');
            const iframe = modal.querySelector('iframe');
            const urlGdrive = `https://docs.google.com/viewer?embedded=true&url=${url}`;
            iframe.src = `${urlGdrive}`; 
            $('#parentLihatDiGoogleDocs').html(`<a href="${urlGdrive}" target="_blank"><button class="btn btn-warning">Lihat di Google Drive</button></a> <a href="${url}" target="_blank"><button class="btn btn-info" download>Download File</button></a>`)
        }

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

        $("textarea").each(function () {
            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });

        const onLoading = (e) => {
            e.setAttribute('disabled', true);
            e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
            document.getElementById('formModal').submit();
        }
    </script>
@endpush
