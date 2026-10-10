@extends('layouts.panel.index')
@section('title', $mahasiswa->name)

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
                    <a href="{{ route('panel.admin.users.mahasiswa.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Mahasiswa: {{ $mahasiswa->name }}</h3>
                    <p class="text-subtitle text-muted">Data Mahasiswa: {{ $mahasiswa->name }}</p>
                </div>
            </div>
        </div>
        <div class="page-content">
            <section class="row">
                <div class="col-12">
                    <div class="row">
                        <div class="col-3 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body py-4 px-5">
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-xl">
                                            <img src="{{ Auth::user()->profile_image ? asset('/storage/profiles/' . Auth::user()->profile_image) : asset('/img/user.png') }}" alt="{{ Auth::user()->name }}">
                                        </div>
                                        <div class="ms-3 name">
                                            <h5 class="font-bold">{{ $mahasiswa->name }}</h5>
                                            <h6 class="text-muted mb-0">{{ $mahasiswa->email }} <br/>
                                                 {{ $mahasiswa->phone_number }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-3 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="stats-icon purple">
                                                <i class="iconly-boldShow"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-muted font-semibold">Total Pengajuan</h6>
                                            <h6 class="font-extrabold mb-0">{{ \App\Models\Submission::where('student_id', $mahasiswa->id)->count() }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-3 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="stats-icon blue">
                                                <i class="iconly-boldProfile"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-muted font-semibold">Pengajuan yang telah selesai</h6>
                                            <h6 class="font-extrabold mb-0">{{ \App\Models\Submission::where('student_id', $mahasiswa->id)->where('status', 'approved')->orWhere('status', 'rejected')->count() }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-3 col-lg-3 col-md-6">
                            <div class="card">
                                <div class="card-body px-3 py-4-5">
                                    <div class="row">
                                        <div class="col-md-4">
                                            <div class="stats-icon green">
                                                <i class="iconly-boldAdd-User"></i>
                                            </div>
                                        </div>
                                        <div class="col-md-8">
                                            <h6 class="text-muted font-semibold">Pengajuan yang masih proses</h6>
                                            <h6 class="font-extrabold mb-0">{{ \App\Models\Submission::where('student_id', $mahasiswa->id)->where('status', 'pending')->orWhere('status', 'processed')->count() }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>
        </section>
        <section class="section">
            <div class="card">
                <div class="card-header">
                    Daftar Pengajuan
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="table1">
                            <thead>
                                <tr>
                                    <th>
                                        Tipe Pengajuan
                                        <br/>
                                        <small class="text-muted">Judul Pengajuan</small>
                                    </th>
                                    <th>Status</th>
                                    <th>Update Terakhir</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if ($submission->isEmpty())
                                    <tr>
                                        <td colspan="4" class="text-center">Tidak ada data</td>
                                    </tr>
                                @else
                                    @foreach ($submissions as $submission)
                                        <tr>
                                            <td>
                                                {{ $submission->type->name }}
                                                <br/>
                                                <small class="text-muted">{{ $submission->title }}</small>
                                            </td>
                                            <td class="text-capitalize">
                                                <span class="badge {{ badge_class($mahasiswa->status) }}">
                                                    {{ status_english_to_bahasa($mahasiswa->status) }}
                                                </span>
                                            </td>
                                            <td>
                                                {{ $submission->updated_at->diffForHumans() }}
                                            </td>
                                            <td>
                                                <a href="{{ route('panel.admin.pengajuan.edit', ['type_slug' => $submission->type->slug, 'id' => $submission->id]) }}" class="btn btn-sm btn-info">Detail</a>
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
