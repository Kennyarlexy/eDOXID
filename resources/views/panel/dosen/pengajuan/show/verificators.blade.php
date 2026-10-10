@extends('layouts.panel.index')
@section('title', 'Detail Verifikator Pengajuan: ' . $submission->title)

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

    @php
        $pembimbings = \App\Models\Verificator::where('submission_id', $submission->id)
            ->where('type', 'Pembimbing')
            ->get();
        $pengujis = \App\Models\Verificator::where('submission_id', $submission->id)
            ->where('type', 'Penguji')
            ->get();
    @endphp

    <script>
        let indexPembimbing = {{ $pembimbings->count() }};
        let indexPenguji = {{ $pengujis->count() }};

        const addPembimbing = () => {
            let tr = document.createElement('tr');
            tr.innerHTML = `<tr>
                                <td>
                                    <select class="form-select" name="pembimbing_ids[${ indexPembimbing + 1 }]" required>
                                        <option value="">Pilih Pembimbing</option>
                                        @foreach ($dosens as $dosen)
                                            <option value="{{ $dosen->id }}">{{ $dosen->name }} ({{ $dosen->unique_id }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-danger me-1 mb-1" onclick="removePembimbing(this)">Hapus</button>
                                </td>
                            </tr>`;

            document.getElementById("pembimbings").appendChild(tr);
            indexPembimbing++;
        }

        const addPenguji = () => {
            let tr = document.createElement('tr');
            tr.innerHTML = `<tr>
                                <td>
                                    <select class="form-select" name="penguji_ids[${ indexPenguji + 1 }]" required>
                                        <option value="">Pilih Penguji</option>
                                        @foreach ($dosens as $dosen)
                                            <option value="{{ $dosen->id }}">{{ $dosen->name }} ({{ $dosen->unique_id }})</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-danger me-1 mb-1" onclick="removePenguji(this)">Hapus</button>
                                </td>
                            </tr>`;

            document.getElementById("pengujis").appendChild(tr);
            indexPenguji++;
        }

        const removePembimbing = (element) => {
            element.parentElement.parentElement.remove();
        }

        const removePenguji = (element) => {
            element.parentElement.parentElement.remove();
        }
    </script>
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <?php
                        $urlKembali = route('panel.dosen.pengajuan.index', $type->slug);
                        if (isset($_GET['from'])) {
                            $urlKembali = $_GET['from'];
                        }
                    ?>
                    <a href="{{ $urlKembali }}" class="btn btn-secondary">Kembali</a>
                    <!-- <a href="{{ route('panel.dosen.pengajuan.index', $type->slug) }}" class="btn btn-secondary">Kembali</a> -->
                    <h3 class="mt-4">Detail Verifikator Pengajuan - {{ $type->name }}</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                @include('panel.dosen.pengajuan.show.components.card-header')
                <div class="card-body">
                    @include('panel.dosen.pengajuan.show.tab-menu')

                    <div class="tab-content text-justify mt-3">
                        <!-- Deskripsi -->
                        <div class="tab-pane fade show active" id="deskripsi" role="tabpanel" aria-labelledby="deskripsi-tab">
                            <form action="{{ route('panel.dosen.pengajuan.verifikators.update', ['type_slug' => $submission->type->slug, 'id' => $submission->id]) }}" method="post">
                                @csrf
                                @method('PUT')

                                @if ($submission->format->is_pembimbing_required)
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Pilihan Pembimbing
                                                    </th>
                                                    <th scope="col" style="width: 10%" class="text-end">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="pembimbings">
                                                @foreach ($pembimbings as $index => $pembimbing)
                                                    <tr>
                                                        <td>
                                                            <select class="form-select"
                                                                name="pembimbing_ids[{{ $index }}]" required>
                                                                <option value="">Pilih Pembimbing</option>
                                                                @foreach ($dosens as $dosen)
                                                                    <option value="{{ $dosen->id }}"
                                                                        {{ $pembimbing->user_id === $dosen->id ? 'selected' : '' }}>
                                                                        {{ $dosen->name }} ({{ $dosen->unique_id }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-danger me-1 mb-1"
                                                                onclick="removePembimbing(this)">Hapus</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3">
                                                        <button type="button" class="btn btn-primary w-100"
                                                            onclick="addPembimbing()">Tambah Pembimbing</button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif

                                @if ($submission->format->is_penguji_required)
                                    <div class="table-responsive mb-4">
                                        <table class="table table-bordered table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th scope="col">
                                                        Pilihan Penguji
                                                    </th>
                                                    <th scope="col" style="width: 10%" class="text-end">
                                                        Aksi
                                                    </th>
                                                </tr>
                                            </thead>
                                            <tbody id="pengujis">
                                                @foreach ($pengujis as $index => $penguji)
                                                    <tr>
                                                        <td>
                                                            <select class="form-select"
                                                                name="penguji_ids[{{ $index }}]" required>
                                                                <option value="">Pilih Penguji</option>
                                                                @foreach ($dosens as $dosen)
                                                                    <option value="{{ $dosen->id }}"
                                                                        {{ $penguji->user_id === $dosen->id ? 'selected' : '' }}>
                                                                        {{ $dosen->name }} ({{ $dosen->unique_id }})
                                                                    </option>
                                                                @endforeach
                                                            </select>
                                                        </td>
                                                        <td class="text-end">
                                                            <button type="button" class="btn btn-danger me-1 mb-1"
                                                                onclick="removePenguji(this)">Hapus</button>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                            <tfoot>
                                                <tr>
                                                    <td colspan="3">
                                                        <button type="button" class="btn btn-primary w-100"
                                                            onclick="addPenguji()">Tambah penguji</button>
                                                    </td>
                                                </tr>
                                            </tfoot>
                                        </table>
                                    </div>
                                @endif

                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
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
    </script>
@endpush
