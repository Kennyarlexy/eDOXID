@extends('layouts.panel.index')
@section('title', 'Daftar Bagian Administrasi')

@include('components.function')

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
                    <h3 class="mb-4">Daftar - Bagian Administrasi</h3>
                    <a href="{{ route('panel.admin.users.bagian-administrasi.create') }}" class="btn btn-success">Tambah</a>
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
                                        <small class="text-muted">Email</small>
                                    </th>
                                    <th>Status</th>
                                    <th class="text-end">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($bagianAdministrasis->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            <h5>Tidak ada data</h5>
                                        </td>
                                    </tr>
                                @else
                                    @foreach ($bagianAdministrasis as $i => $bagianAdministrasi)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $bagianAdministrasi->name }}
                                                <br/>
                                                <small class="text-muted">{{ $bagianAdministrasi->email }}</small>
                                            </td>
                                            <td class="text-capitalize">
                                                <span class="badge {{ badge_class($bagianAdministrasi->status) }}">
                                                    {{ status_english_to_bahasa($bagianAdministrasi->status) }}
                                                </span>
                                            </td>
                                            <td class="text-end">
                                                <a href="{{ route('panel.admin.users.bagian-administrasi.edit', $bagianAdministrasi) }}"
                                                    class="btn icon btn-sm btn-warning">Ubah</a>
                                                <button id="deleteBtn" onclick="deleteBtn(this)" class="btn icon btn-sm btn-danger"
                                                    data-bs-toggle="modal" data-bs-target="#delete"
                                                    data-url="{{ route('panel.admin.users.bagian-administrasi.destroy', $bagianAdministrasi->id) }}"
                                                    data-name="{{ $bagianAdministrasi->name }}">Hapus</button>
                                                <a href="{{ route('panel.admin.users.log', $bagianAdministrasi->id) }}" class="btn icon btn-sm btn-secondary">Lihat Log</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </section>
    </div>

    <div class="modal fade" id="delete" tabindex="-1" aria-labelledby="deleteModal" aria-hidden="true"
        style="display: none;">
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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light-secondary" data-bs-dismiss="modal">
                        <span class="">Tutup</span>
                    </button>
                    <form action="" method="post" id="deletedUrl">
                        @csrf
                        @method('delete')
                        <button type="submit" class="btn btn-danger ml-1">
                            <span class="">Ya</span>
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
            document.getElementById('deletedUrl').action = event.getAttribute('data-url');
        }
    </script>
@endpush
