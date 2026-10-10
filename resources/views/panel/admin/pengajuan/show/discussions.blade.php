@extends('layouts.panel.index')
@section('title', 'Detail Deskripsi Pengajuan: ' . $submission->title)

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

    <link href="https://unpkg.com/filepond@^4/dist/filepond.css" rel="stylesheet" />
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a>
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.admin.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.admin.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <div class="tab-pane fade show active">
                            @if ($discussions->count() === 0)
                                <div class="text-secondary p-3 bg-light rounded text-center">Belum ada komentar</div>
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
            .create(document.querySelector('#deskripsi_pengajuan'), {
                toolbar: {
                    items: ['bold', 'italic', '|', 'undo', 'redo', '|', 'numberedList', 'bulletedList']
                }
            })
            .catch(error => {
                console.error(error);
            });

        // choices js
        let choices = document.querySelectorAll('.choices');
        let initChoice;
        for (let i = 0; i < choices.length; i++) {
            if (choices[i].classList.contains("multiple-remove")) {
                initChoice = new Choices(choices[i], {
                    delimiter: ',',
                    editItems: true,
                    maxItemCount: -1,
                    removeItemButton: true,
                });
            } else {
                initChoice = new Choices(choices[i]);
            }
        }

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
