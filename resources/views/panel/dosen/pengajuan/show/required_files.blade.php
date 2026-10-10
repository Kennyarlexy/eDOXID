@extends('layouts.panel.index')
@section('title', 'Detail Berkas Pengajuan: ' . $submission->title)

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
                    <!-- <a href="{{ route('panel.dosen.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a> -->
                    <h3 class="mt-4">Detail Berkas Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.dosen.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.dosen.pengajuan.show.tab-menu')

                    <div class="row py-2">
                        <small class="text-muted text-sm">Jika ingin <b>"Lihat Berkas"</b> atau <b>"Edit"</b> maka pastikan anda telah login ke akun gmail <b>UNTAN.</b></small>
                        <br/>
                        <small class="text-muted text-sm">Browser yang disarankan adalah: <b>Google Chrome</b></small>

                        <hr/>
                    </div>

                    <div class="tab-content text-justify mt-3">
                        <!-- Deskripsi -->
                        <div class="tab-pane fade show active">
                            @if ($submission->format->required_files->count() > 0)
                                @php
                                    $submission_required_files = \App\Models\SubmissionRequiredFile::where('submission_id', $submission->id)->whereHas('required_file', function($q) {
                                        $q->where('is_verificator_can_view', 1);
                                    })->get();
                                @endphp

                                @foreach ($submission_required_files as $i => $submission_required_file)
                                <div class="row align-items-center">

                                    <div class="col-md-6">
                                        #{{ $i + 1 }} - <b>{{ $submission_required_file->required_file->name }}</b>
                                        <br/>
                                        <small class="text-muted">
                                            <span class="text-muted">Terakhir update: {{ $submission_required_file->updated_at->format('d M Y H:i') }}</span>
                                        </small>
                                        @if ($submission_required_file->url !== null)
                                            <div class="badge {{ badge_class($submission_required_file->status) }} text-capitalize me-2">{{ status_english_to_bahasa($submission_required_file->status) }}</div>
                                        @else
                                            <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                        @endif
                                    </div>

                                    <div class="col-md-6 text-end">
                                        @if ($submission_required_file->url !== null)
                                            @if (str_contains($submission_required_file->url, 'drive.google.com'))
                                                <a class="badge bg-success" style="cursor: pointer" onclick="showModal('{{ $submission_required_file->url }}')" data-bs-toggle="modal" data-bs-target="#show">Lihat Berkas</a>
                                                @if ($submission_required_file->required_file->is_verificator_can_edit)
                                                    @php
                                                        $url = $submission_required_file->url;
                                                    @endphp
                                                    <a target="_blank" href="{{ $url }}" class="badge bg-info">Edit</a>
                                                @endif
                                                <a target="_blank" href="{{ $submission_required_file->url }}" class="badge bg-info">Download</a>
                                            @else
                                                <a class="badge bg-info" href="{{ $submission_required_file->url }}">Download</a>
                                            @endif
                                        @else
                                            <div class="badge bg-warning text-capitalize me-2">Belum ada berkas</div>
                                        @endif
                                    </div>

                                </div>

                                <hr/>
                                @endforeach
                            @endif
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
                <div class="modal-body" id="modal-body">

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
        const btnSubmit = document.getElementById('btnSubmit');
        const showModal = (url) => {
            const modal = document.getElementById('modal-body');
            modal.innerHTML = '';
            modal.innerHTML = `<iframe width="100%" height="630" src="${url}/preview"></iframe>`;
        }
    </script>
@endpush
