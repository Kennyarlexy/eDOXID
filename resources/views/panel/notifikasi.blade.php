@extends('layouts.panel.index')
@section('title', 'Notifikasi')

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
                    <h3>Daftar Notifikasi</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped mb-0" id="table1">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-center" style="width: 2%">No</th>
                                    <th scope="col">
                                        Aksi
                                        <br/>
                                        <small class="text-muted">Judul Pengajuan</small>
                                        <br/>
                                        <small class="text-muted">Nama Mahasiswa (NIM)</small>
                                    </th>
                                    <th scope="col" style="width: 10%">
                                        Tipe Pengajuan
                                    </th>
                                    <th scope="col" style="width: 10%">
                                        Tanggal
                                    </th>
                                    <th scope="col" style="width: 10%" class="text-end">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($notifications->count() === 0)
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($notifications as $i => $notification)
                                        <tr>
                                            <td class="text-center" style="width: 2%">{{ $i + 1 }}</td>
                                            <td>
                                                {{ $notification->action }}
                                                <br/>
                                                <small class="text-muted">{{ $notification->submission->title }}</small>
                                                <br/>
                                                <small class="text-muted">{{ $notification->submission->student->name }} ({{ $notification->submission->student->unique_id }})</small>
                                            </td>
                                            <td class="text-capitalize" style="width: 10%">
                                                {{ $notification->submission->type->name }}
                                            </td>
                                            <td class="text-capitalize" style="width: 10%">
                                                {{ $notification->created_at->format('d M Y H:i') }}
                                            </td>
                                            <td class="text-end" style="width: 10%">
                                                <a href="{{ $notification->url }}"
                                                    class="btn icon btn-sm btn-info">Lihat Detail</a>
                                                <br/>
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
@endsection

@push('scripts')
@endpush
