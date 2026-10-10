@extends('layouts.panel.index')
@section('title', 'Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
    <style>
        .ck-editor__editable_inline {
            min-height: 100px;
        }
        .load-more {
            max-height: 132px;
            overflow: hidden;
            text-align: justify;
        }
    </style>
@endpush

@section('main')    <div class="page-content">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.dosen.pengajuan-public.index') }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Pengajuan - {{ $submission->type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="row">
            <div class="col-12">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header pb-2">
                                <h4 class="mb-4">{{ $submission->title }}</h4>
                                <div class="d-flex mb-4">
                                    <span class="text-muted">
                                        <span
                                            class="badge {{ badge_class($submission->status) }} text-capitalize">{{ status_english_to_bahasa($submission->status) }}</span>
                                    </span>
                                    <span class="ms-4 text-muted">
                                        <i class="fa fa-calendar me-1"></i>
                                        {{ $submission->created_at ? $submission->created_at->format('d M Y') : '-'  }}
                                    </span>
                                    <span class="ms-4 text-muted">
                                        <i class="fa fa-folder me-1"></i>
                                        {{ $submission->type->name }}
                                    </span>
                                    <span class="ms-4 text-muted">
                                        <i class="fa-solid fa-box-archive me-1"></i>
                                        {{ $submission->skill_group_id ? $submission->skill_group->name : '-' }}
                                    </span>
                                </div>
                                <div class="mb-2 d-flex">
                                    <p class="text-muted">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar">
                                                <a>
                                                    <img src="{{ $submission->student->profile_image == null ||$submission->student->profile_image == '//spota.untan.ac.id/img/'? asset('/img/user.png'): $submission->student->profile_image }}"
                                                        alt="{{ $submission->student->name }}">
                                                    <span class="ms-2">{{ $submission->student->name }} - {{ $submission->student->unique_id }}</span>
                                                </a>
                                            </div>
                                        </div>
                                    </p>
                                </div>
                                <div class="d-flex mb-4">
                                    <b>Informasi lainnya:</b>
                                    <span class="ms-2 badge bg-light-info">
                                        Jumlah SKS: {{ $submission->amount_sks_now ?: '-' }}
                                    </span>
                                    <span class="ms-2 badge bg-light-info">
                                        IPK: {{ $submission->ipk_now ?: '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">

                                <div class="form-group mb-2 load-more" id="load-more-text">
                                    @if ($submission->description)
                                        {!! $submission->description !!}
                                    @else
                                        <p class="text-muted">Belum ada deskripsi.</p>
                                    @endif
                                </div>
                                <button class="btn btn-sm btn-secondary text-center w-100 mb-4" id="load-more-button" onclick="loadMore()" type="button">Tampilkan lebih banyak</button>

                                @if ($submission->skill_group_id)
                                    <div class="form-group mb-4">
                                        <label class="mb-2">Bidang Keahlian</label>
                                        <input type="text" class="form-control" value="{{ $submission->skill_group_id ? $submission->skill_group->name  : '-' }}" readonly disabled>
                                    </div>
                                @endif

                                @if ($submission->format->is_need_trial_schedule)
                                    <div class="form-group required mb-4">
                                        <label class="control-label">Jadwal Sidang</label>
                                        <input type="text" class="form-control" value="{{ $submission->schedule ? date('Y-m-d\TH:i', strtotime($submission->schedule)) : 'Belum ditentukan' }}" disabled readonly>
                                    </div>
                                    <div class="form-group required mb-4">
                                        <label class="control-label">Ruangan Sidang</label>
                                        <input type="text" class="form-control" value="{{ $submission->room }}" disabled readonly>
                                    </div>
                                @endif

                                @if ($submission->format->required_files->count() > 0)
                                    <div class="w-100">
                                        <div class="divider">
                                            <div class="divider-text">Berkas pengajuan mahasiswa</div>
                                        </div>
                                    </div>
                                    @foreach ($submission->required_files as $required_file)
                                        <div class="form-group mb-4">
                                            <label>
                                                {{ $required_file->name }}
                                            </label>
                                            <br/>
                                            @if ($required_file->url !== null)
                                                <div class="badge {{ badge_class($required_file->status) }} text-capitalize me-2">{{ status_english_to_bahasa($required_file->status) }}</div>
                                                <a href="{{ $required_file->url }}" target="_blank" class="badge bg-success">Lihat & Download Berkas</a>
                                            @else
                                                <div class="badge bg-warning text-capitalize me-2">Belum diupload</div>
                                            @endif
                                        </div>
                                    @endforeach
                                @endif

                                <div class="row mt-4">
                                    <div class="col-12 col-md-6">
                                        <h5>Ditetapkan</h5>
                                        <ul>
                                            <li>Tanggal: {{ $submission->status === 'approved' ? $submission->updated_at->format('d-m-Y') : '' }}</li>
                                            <li>Waktu: {{ $submission->status === 'approved' ? $submission->updated_at->format('H:i') : '' }}</li>
                                            <li>Semester: {{ $submission->semester }}</li>
                                            <li>Tahun Akademik: {{ $submission->academic_year }}</li>
                                        </ul>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <h5>Pihak Verifikasi</h5>
                                        <ul class="list-group">
                                            @php
                                                $verificator_pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Pembimbing')->get();
                                                $verificator_pengujis = \App\Models\Verificator::where('submission_id', $submission->id)->orderBy('id', 'asc')->where('type', 'Penguji')->get();
                                            @endphp

                                            @if ($submission->format->is_pembimbing_required)
                                                @if ($verificator_pembimbings->count() > 0)
                                                    @foreach ($verificator_pembimbings as $i => $pembimbing)
                                                        <li class="list-group-item">
                                                            {{ $pembimbing->user->name }} <small class="text-muted">(Pembimbing {{ $i + 1 }})</small>
                                                            @if ($pembimbing->is_need_to_change_trial_status)
                                                                <span
                                                                    class="badge text-capitalize ms-2 {{ badge_class($pembimbing->status) }}">
                                                                    Seminar/Sidang {{ status_english_to_bahasa($pembimbing->status) }}
                                                                </span>
                                                            @endif
                                                            <span
                                                                class="badge text-capitalize ms-2 {{ badge_class($pembimbing->final_status) }}">
                                                                Final Revisi {{ status_english_to_bahasa($pembimbing->final_status) }}
                                                            </span>
                                                            <br />
                                                            @if ($submission->format->required_values->count() > 0)
                                                                @if (\App\Models\SubmissionRequiredValue::where('verificator_id', $pembimbing->id)->where('submission_id', $submission->id)->count() > 0)
                                                                    <small class="text-success" style="font-size:12px">Telah selesai melakukan penilaian</small>
                                                                @else
                                                                    <small class="text-warning" style="font-size:12px">Penilaian belum
                                                                        selesai
                                                                        dilakukan</small>
                                                                @endif
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item">
                                                        Pembimbing
                                                        <span class="badge bg-warning">
                                                            Belum Dikonfirmasi
                                                        </span>
                                                        <br/>
                                                        <small class="text-muted">Belum ada pembimbing</small>
                                                    </li>
                                                @endif
                                            @endif

                                            @if ($submission->format->is_penguji_required)
                                                @if ($verificator_pengujis->count() > 0)
                                                    @foreach ($verificator_pengujis as $i => $penguji)
                                                        <li class="list-group-item">
                                                            {{ $penguji->user->name }} <small class="text-muted">(Penguji {{ $i + 1 }})</small>
                                                            @if ($penguji->is_need_to_change_trial_status)
                                                                <span
                                                                    class="badge text-capitalize ms-2 {{ badge_class($penguji->status) }}">
                                                                    Seminar/Sidang {{ status_english_to_bahasa($penguji->status) }}
                                                                </span>
                                                            @endif
                                                            <span
                                                                class="badge text-capitalize ms-2 {{ badge_class($penguji->final_status) }}">
                                                                Final Revisi {{ status_english_to_bahasa($penguji->final_status) }}
                                                            </span>
                                                            <br />
                                                            @if ($submission->format->required_values->count() > 0)
                                                                @if (\App\Models\SubmissionRequiredValue::where('verificator_id', $penguji->id)->where('submission_id', $submission->id)->count() > 0)
                                                                    <small class="text-success" style="font-size:12px">Telah selesai melakukan penilaian</small>
                                                                @else
                                                                    <small class="text-warning" style="font-size:12px">Penilaian belum selesai dilakukan</small>
                                                                @endif
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                @else
                                                    <li class="list-group-item">
                                                        Penguji
                                                        <span class="badge bg-warning">
                                                            Belum Dikonfirmasi
                                                        </span>
                                                        <br/>
                                                        <small class="text-muted">Belum ada penguji</small>
                                                    </li>
                                                @endif
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer bg-light-secondary" style="border-top: transparent">
                                <h5 class="mb-2">Komentar</h5>
                                @if ($submission->status == 'processed' || $submission->status == 'pending')
                                    <form
                                        action="{{ route('panel.dosen.pengajuan-public.add-comment', ['id' => $submission->id]) }}"
                                        method="post">
                                        @csrf
                                        <div class="form-floating">
                                            <textarea name="komentar" id="editor"
                                                placeholder="Tambah komentar jika memang diperlukan" rows="4"></textarea>
                                        </div>
                                        <div class="text-end mt-2">
                                            <button type="submit" class="btn btn-primary mb-3">
                                                Kirim
                                            </button>
                                        </div>
                                    </form>
                                @endif
                                @if ($submission->consultations->count() === 0)
                                    <div class="text-secondary">Belum ada komentar</div>
                                @else
                                    @foreach ($submission->consultations()->orderBy('id', 'desc')->get()
        as $consultation)
                                        <div class="media">
                                            <div class="media-body">
                                                <div class="media-heading mb-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar d-flex align-items-center">
                                                            <a @if ($consultation->user->role === \App\Models\User::ROLE_MAHASISWA) href="{{ route('panel.dosen.mahasiswa.bimbingan-akademik.show', $consultation->user->id) }}" @endif>
                                                                <img src="{{ $consultation->user->profile_image ? asset('/storage/profiles/' . $consultation->user->profile_image) : asset('/img/user.png') }}"
                                                                    alt="{{ $consultation->user->name }}">
                                                                <span
                                                                    class="ms-2">{{ $consultation->user->name }}</span>
                                                            </a>
                                                        </div>
                                                        <span class="ms-4 text-muted">
                                                            <i class="fa fa-calendar me-1"></i>
                                                            {{ $consultation->created_at->diffForHumans() }}
                                                        </span>
                                                    </div>
                                                </div>
                                                <p>{!! $consultation->description !!}</p>
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </div>
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
                    items: [ 'bold', 'italic', '|', 'undo', 'redo', '|', 'numberedList', 'bulletedList' ]
                }
            })
            .catch(error => {
                console.error(error);
            });

            const loadMore = () => {
                const loadMoreText = document.getElementById('load-more-text');
                const loadMoreButton = document.getElementById('load-more-button');

                loadMoreText.classList.toggle('load-more');
                if (loadMoreText.classList.contains('load-more')) {
                    loadMoreButton.innerText = 'Tampilkan lebih banyak';
                } else {
                    loadMoreButton.innerText = 'Tampilkan lebih sedikit';
                }
            }
    </script>
@endpush
