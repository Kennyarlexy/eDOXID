@extends('layouts.panel.index')
@section('title', 'Pengaturan Grading Penilaian')

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
                    <h3 class="mb-4">Pengaturan Grading Penilaian</h3>
                    <a href="{{ route('panel.admin.grading.create') }}" class="btn btn-success">Tambah</a>
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
                                    <th scope="col">Grade</th>
                                    <th scope="col">Maksimal - Minimal Skor Penilaian</th>
                                    <th scope="col" style="width: 10%">Deskripsi</th>
                                    <th class="text-end" style="width: 10%">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($gradings->count() === 0)
                                    <tr>
                                        <td colspan="5" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($gradings as $i => $grading)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>{{ $grading->grade }}</td>
                                            <td>{{ $grading->maksimal_score }} - {{ $grading->minimal_score }}</td>
                                            <td style="width: 10%" class="text-capitalize">
                                                {{ $grading->description }}
                                            </td>
                                            <td style="width: 10%" class="text-end">
                                                <a href="{{ route('panel.admin.grading.edit', $grading) }}"
                                                    class="btn icon btn-sm btn-warning">Ubah</a>
                                                <button id="deleteBtn" onclick="deleteBtn(this)" class="btn icon btn-sm btn-danger"
                                                    data-bs-toggle="modal" data-bs-target="#delete"
                                                    data-url="{{ route('panel.admin.grading.destroy', $grading) }}"
                                                    data-name="{{ $grading->grade }}"
                                                    data-max-min-score="{{ $grading->maksimal_score }} - {{ $grading->minimal_score }}"
                                                    data-description="{{ $grading->description }}">
                                                    Hapus
                                                </button>
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
                    <div class="form-group">
                        <label>Nama Grade</label>
                        <input type="text" class="form-control mb-2 disabled" id="grade_name">
                    </div>
                    <div class="form-group">
                        <label>Maksimal - Minimal Skor</label>
                        <input type="text" class="form-control mb-2 disabled" id="grade_max_min_score">
                    </div>
                    <div class="form-group">
                        <label>Deskripsi</label>
                        <textarea class="form-control mb-2 disabled" id="grade_description" rows="4"></textarea>
                    </div>
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
            document.getElementById('grade_name').value = event.getAttribute('data-name');
            document.getElementById('grade_max_min_score').value = event.getAttribute('data-max-min-score');
            document.getElementById('grade_description').value = event.getAttribute('data-description');
            document.getElementById('deletedUrl').action = event.getAttribute('data-url');
        }
    </script>
@endpush
