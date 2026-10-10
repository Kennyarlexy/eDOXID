@extends('layouts.panel.index')
@section('title', 'Detail Diskusi Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <style>
        .ck-editor__editable_inline {
            min-height: 100px;
        }
    </style>
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
                    <h3 class="mt-4">Detail Diskusi Pengajuan - {{ $type->name }}</h3>
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
                            <h5 class="mb-2">Diskusi</h5>
                            <p class="text-muted text-sm font-bold">Catatan: <br/>
                                Diskusi yang anda lakukan akan otomatis terkirimkan notifikasi via <span class="text-success">Whatsapp</span> ke Mahasiswa, Staff Bagian Administrasi, Tim Dosen Pembimbing dan Tim Dosen Penguji (Jika ada) yang bersangkutan pada pengajuan ini.
                            </p>
                            <form
                                action="{{ route('panel.dosen.pengajuan.discussions.add', ['type_slug' => $type->slug, 'id' => $submission->id]) }}"
                                method="post">
                                @csrf
                                <div class="form-floating">
                                    <textarea name="komentar" id="editor" placeholder="Tambah diskusi jika memang diperlukan" rows="4"></textarea>
                                </div>
                                <div class="text-end mt-2">
                                    <button type="submit" class="btn btn-primary mb-3">
                                        Kirim
                                    </button>
                                </div>
                            </form>
                            @if ($discussions->count() === 0)
                                <div class="text-secondary p-3 bg-light rounded text-center">Belum ada diskusi</div>
                            @else
                                @foreach ($discussions as $discussion)
                                    <div class="media py-2 bg-white px-4 rounded my-4">
                                        <div class="media-body">
                                            <div class="media-heading mb-2">
                                                <div class="d-md-flex align-items-center justify-content-between">
                                                    <div class="avatar flex-column flex-md-row align-items-md-center d-flex flex-wrap">
                                                        <div class="d-flex align-items-md-center">
                                                            <img src="{{ $discussion->user->profile_image == null || $discussion->user->profile_image == '//spota.untan.ac.id/img/' ? asset('/img/user.png') : $discussion->user->profile_image }}"
                                                                alt="{{ $discussion->user->name }}">
                                                            <span class="ms-2">
                                                                {{ $discussion->user->name }}
                                                            </span>
                                                        </div>
                                                        <small class="text-muted text-sm ms-md-2">Role: {{ $discussion->user->role }}</small>
                                                    </div>
                                                    <div class="">
                                                        <span class="text-muted text-sm">
                                                            <i class="fa fa-calendar me-1"></i>
                                                            {{ $discussion->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                            <p>{!! $discussion->description !!}</p>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('/plugins/ckeditor/ckeditor.js') }}"></script>

    <script>
        ClassicEditor
            .create(document.querySelector('#editor'), {
                toolbar: {
                    items: ['bold', 'italic', '|', 'undo', 'redo', '|', 'numberedList', 'bulletedList']
                }
            })
            .catch(error => {
                console.error(error);
            });
    </script>
@endpush
