@extends('layouts.panel.index')
@section('title', 'Format Pengajuan Tipe ' . $type->name)

@php
    function filesize_formatted($size)
    {
        $units = array( 'B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
        $power = $size > 0 ? floor(log($size, 1024)) : 0;
        return number_format($size / pow(1024, $power), 2, '.', ',') . ' ' . $units[$power];
    }
@endphp

@php
    function formatBytes($bytes, $precision = 2) {
        $units = array('B', 'KB', 'MB', 'GB', 'TB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
@endphp

@push('styles')
    <script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/datatables/css/style.css') }}">
    <script type="text/javascript" charset="utf8" src="{{ asset('plugins/datatables/js/script.js') }}"></script>

    <script>
        $(document).ready(function() {
            $('#table1').DataTable({
                searching: true,
                ordering: true,
                "order": []
            });
        });
    </script>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.type.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="my-4">Daftar Format - Tipe Pengajuan: {{ $type->name }}</h3>
                    <a href="{{ route('panel.admin.type.format.create', $type->slug) }}" class="btn btn-success mb-4">Tambah</a>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 2%">No</th>
                                    <th>
                                        Nama
                                        <br/>
                                        <small class="text-muted" style="font-size:11px">Dokumen File (Size)</small>
                                    </th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($formats->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($formats as $i => $format)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $format->name }}
                                                <br/>
                                                @if ($format->document_format !== null)
                                                    <small class="text-muted" style="font-size:11px"><a href="{{ asset('/storage/app/public/format_documents/' . $format->document_format) }}">{{ $format->document_format_name }} ({{ filesize_formatted($format->document_format_size) }})</a></small>
                                                @else
                                                    <small class="text-muted" style="font-size:11px">-</small>
                                                @endif
                                            </td>
                                            <td class="text-{{ $format->is_primary ? 'primary' : 'danger' }}">
                                                {{ $format->is_primary ? 'Format Utama' : 'Format Cadangan' }}
                                            </td>
                                            <td class="text-end">

                                                @php
                                                    $requiredFiles = "";
                                                    $requiredValues = "";

                                                    if ($format->required_files->count() > 0) {
                                                        $requiredFiles = '<div class="list-group-item list-group-item-action active"><div class="d-flex w-100 justify-content-between"><h5 class="mb-1 text-white">File yang dibutuhkan</h5></div></div>';
                                                        $requiredFiles .= '<div class="list-group-item list-group-item-action">';
                                                        foreach ($format->required_files as $required_file) {
                                                            $requiredFiles .= '<div class="d-flex w-100 justify-content-between">
                                                                                    <h5 class="mb-1">'. $required_file->name .'</h5>
                                                                                    <small>'. $required_file->key .'</small>
                                                                                </div>
                                                                                <p class="mb-1">
                                                                                    '. $required_file->note  .'
                                                                                </p>';
                                                        }
                                                        $requiredFiles .= "</div>";
                                                    }

                                                    if ($format->required_values->count() > 0) {
                                                        if ($format->required_values->count() > 0) {
                                                            $requiredValues = '<div class="list-group-item list-group-item-action active"><div class="d-flex w-100 justify-content-between"><h5 class="mb-1 text-white">Penilaian yang dibutuhkan</h5></div></div>';
                                                            $requiredValues .= '<div class="list-group-item list-group-item-action">';
                                                            foreach ($format->required_values as $required_value) {
                                                                $requiredValues .= '<div class="d-flex w-100 justify-content-between">
                                                                                        <h5 class="mb-1">'. $required_value->name .'</h5>
                                                                                        <small>'. $required_value->key .'</small>
                                                                                    </div>
                                                                                    <p class="mb-1">
                                                                                        '. $required_value->note  .'
                                                                                    </p>';
                                                            }
                                                            $requiredValues .= "</div>";
                                                        }
                                                    }
                                                @endphp

                                                <a href="{{ route('panel.admin.type.format.show', ['type' => $type->slug, 'format' => $format]) }}"
                                                    class="btn icon btn-sm btn-info">
                                                    Detail
                                                </a>
                                                @if ($format->is_primary == 0)
                                                    <button id="changeToPrimaryBtn"
                                                        onclick="changeToPrimaryBtn(this)"
                                                        class="btn icon btn-sm btn-primary"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#changeToPrimary"
                                                        data-name="{{ $format->name }}"
                                                        data-url="{{ route('panel.admin.type.format.change-to-primary', $format->id) }}">
                                                        Jadikan Format Utama
                                                    </button>
                                                @endif
                                                @if ($format->submissions->count() === 0)
                                                    <a href="{{ route('panel.admin.type.format.edit', ['type' => $type->slug, 'format' => $format]) }}" class="btn icon btn-sm btn-warning">Ubah</a>
                                                    <button id="deleteBtn"
                                                        onclick="deleteBtn(this)"
                                                        class="btn icon btn-sm btn-danger"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#delete"
                                                        data-name="{{ $format->name }}"
                                                        data-url="{{ route('panel.admin.type.format.destroy', ['type' => $type->slug, 'format' => $format]) }}">
                                                        Hapus
                                                    </button>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4">Catatan.</h6>
                    <small class="text-danger">Format yang telah dipakai pada pengajuan mahasiswa maka tidak bisa dilakukan edit atau dihapus, tetapi bisa diubah statusnya.</small>
                </div>
            </div>

        </section>
    </div>


<div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title white" id="deleteModal">Apakah Anda Yakin Menghapus ini?</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="name"></h5>
                <p id="description"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <span class="">Tutup</span>
                </button>
                <form action="" method="post" id="deletedUrl">
                    @csrf
                    @method('delete')
                    <button type="submit" class="btn btn-danger ml-1">

                        <span class="d-none d-sm-block">Iya</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="changeToPrimary" tabindex="-1" aria-labelledby="changeToPrimaryModal" aria-hidden="true" style="display: none;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="changeToPrimaryModal">Apakah anda yakin?</h5>
                <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                    <i data-feather="x"></i>
                </button>
            </div>
            <div class="modal-body">
                <h5 id="changeToPrimary-name"></h5>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                    <span class="">Tutup</span>
                </button>
                <form action="" method="post" id="changeToPrimaryUrl">
                    @csrf
                    @method('PUT')
                    <button type="submit" class="btn btn-primary ml-1">

                        <span class="d-none d-sm-block">Iya</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
    <script>
        const deleteBtn = (event) => {
            document.getElementById('name').innerHTML = event.getAttribute('data-name');
            var url = event.getAttribute('data-url');
            document.getElementById('deletedUrl').action = url;
        }

        const changeToPrimaryBtn = (event) => {
            document.getElementById('changeToPrimary-name').innerHTML = event.getAttribute('data-name');
            var url = event.getAttribute('data-url');
            document.getElementById('changeToPrimaryUrl').action = url;
        }
    </script>
@endpush
