@extends('layouts.panel.index')
@section('title', 'Daftar Mahasiswa')

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
                    <h3 class="mb-4">Daftar Mahasiswa</h3>
                    {{-- <a href="{{ route('panel.admin.users.mahasiswa.create') }}" class="btn btn-success">Tambah</a> --}}
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">
                    <table class="table table-striped" id="table1">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 2%">No</th>
                                <th>
                                    Nama
                                    <br />
                                    <small class="text-muted">NIM</small>
                                </th>
                                <th>Email</th>
                                <th>Nomor Whatsapp</th>
                                <th>Status</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if ($mahasiswas->count() === 0)
                                <tr>
                                    <td colspan="7" class="text-center">Tidak ada data</td>
                                </tr>
                            @else
                                @foreach ($mahasiswas as $i => $mahasiswa)
                                    <tr>
                                        <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                        <td>
                                            {{ $mahasiswa->name }}
                                            <br />
                                            <small class="text-muted">{{ $mahasiswa->unique_id }}</small>
                                        </td>
                                        <td>{{ $mahasiswa->email ? $mahasiswa->email : '-' }}</td>
                                        <td>{{ $mahasiswa->phone_number }}</td>
                                        <td class="text-capitalize">
                                            <span class="badge {{ badge_class($mahasiswa->status) }}">
                                                {{ status_english_to_bahasa($mahasiswa->status) }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('panel.admin.users.mahasiswa.edit', $mahasiswa) }}"
                                                class="btn icon btn-sm btn-warning">Ubah</a>
                                            {{-- <button id="deleteBtn" onclick="deleteBtn(this)" class="btn icon btn-sm btn-danger"
                                                data-bs-toggle="modal" data-bs-target="#delete"
                                                data-url="{{ route('panel.admin.users.mahasiswa.destroy', $mahasiswa->id) }}"
                                                data-name="{{ $mahasiswa->name }}" a>Hapus</button> --}}
                                            <a href="{{ route('panel.admin.users.log', $mahasiswa->id) }}" class="btn icon btn-sm btn-secondary">Lihat Log</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
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
