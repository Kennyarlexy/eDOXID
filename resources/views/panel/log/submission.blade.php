@extends('layouts.panel.index')
@section('title', 'Log Aktifitas')

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
                    <a href="{{ url()->previous() }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Log Aktifitas</h3>
                    <p class="text-subtitle text-muted mb-4">{{ $submission->title }}</p>
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
                                    <th class="text-center" style="width:2%">No</th>
                                    <th>Deskripsi</th>
                                    <th>Oleh</th>
                                    <th class="text-end">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($logs->count() === 0)
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($logs as $i => $log)
                                        <tr>
                                            <td class="text-center" style="width:2%">{{ $i + 1 }}</td>
                                            <td>{{ $log->details }}</td>
                                            <td>{{ $log->user->name }}</td>
                                            <td class="text-end">{{ $log->created_at->format('d M Y H:i') }}</td>
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
