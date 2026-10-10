@extends('layouts.panel.index')
@section('title', 'Daftar Kelompok Keahlian')

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
                    <h3 class="mb-4">Daftar Kelompok Keahlian</h3>
                    {{-- <a href="{{ route('panel.admin.kelompok-keahlian.create') }}" class="btn btn-success">Tambah</a> --}}
                </div>
            </div>
        </div>
        <section class="section">
            <div class="my-4">
                @include('includes.form-alert')
            </div>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered" id="table1">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center" style="width: 2%">No</th>
                                    <th scope="col">Nama</th>
                                    <th scope="col" style="width: 10%">Status</th>
                                    <th class="text-end" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($skill_groups->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($skill_groups as $i => $skill_group)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>{{ $skill_group->name }}</td>
                                            <td style="width: 10%" class="text-capitalize">
                                                <span class="badge {{ badge_class($skill_group->status) }}">{{ status_english_to_bahasa($skill_group->status) }}</span>
                                            </td>
                                            <td style="width: 10%" class="text-end">
                                                {{-- <a href="{{ route('panel.admin.kelompok-keahlian.edit', $skill_group) }}"
                                                    class="btn icon btn-sm btn-warning">Ubah</a>
                                                @if ($skill_group->submissions->count() === 0)
                                                    <button id="deleteBtn" onclick="deleteBtn(this)" class="btn icon btn-sm btn-danger"
                                                        data-bs-toggle="modal" data-bs-target="#delete"
                                                        data-url="{{ route('panel.admin.kelompok-keahlian.destroy', $skill_group) }}"
                                                        data-name="{{ $skill_group->name }}">
                                                        Hapus
                                                    </button>
                                                @endif --}}
                                                <a href="{{ route('panel.admin.kelompok-keahlian.daftar-dosen.index', $skill_group->id) }}" class="btn icon btn-sm btn-info">Daftar Dosen ({{ $skill_group->skill_group_lecturers->count() }})</a>
                                            </td>
                                        </tr>
                                    @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>

                    <h6 class="mt-4">Catatan.</h6>
                    <small class="text-danger">Kelompok keahlian tidak akan bisa dihapus jika telah memiliki data pengajuan, sehingga jika ingin menonaktifkan cukup dengan mengubah status saja.</small>

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
