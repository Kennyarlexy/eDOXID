@extends('layouts.panel.index')
@section('title', 'Detail Pengajuan: ' . $submission->title)

@include('components.function')

@push('styles')
<style>
    .ck-editor__editable_inline {
        min-height: 100px;
    }
</style>

<script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
<link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
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
                    <h3 class="mt-4">Detail Pengajuan - {{ $type->name }}</h3>
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
                        <div class="tab-pane fade show active" id="deskripsi" role="tabpanel"
                            aria-labelledby="deskripsi-tab">

                            @if ($submission->status == 'pending' || $submission->status == 'processed')

                            <form
                                action="{{ route('panel.dosen.pengajuan.evaluation.update', ['type_slug' => $submission->type->slug,'id' => $submission->id]) }}"
                                method="post">
                                @csrf
                                @method('PUT')

                            @endif
                                @if ($submission->type->name == 'Sidang Hasil')
                                    <div class="form-group mb-4">
<pre>
<label>Aspek-aspek yang dievaluasi</label>
1.	Kesesuaian Tata Tulis, Kutipan, dan Bahasa Ilmiah (10)
2.	Penguasaan Latar Belakang, Masalah, Tujuan, Manfaat, Solusi dan Relevansi.(10)
3.	Pemahaman dan Penguasaan Teori Dasar (20)
4.	Penguasaan Metodologi Penelitian (20)
5.	Hasil Penelitian dan Pengujian (30)
6.	Kesimpulan Penelitian (10)
</pre>
                                @endif

                                @if ($submission->status == 'pending' || $submission->status == 'processed')
                                    <div class="form-group mb-4">
                                        <label>Catatan Review</label>
                                        <textarea name="note" class="form-control" style="min-height: 200px"
                                            placeholder="Catatan Review">{{ $submission->verificators()->where('user_id', auth()->user()->id)->first()->pivot->note }}</textarea>
                                    </div>
                                @else
                                    <div class="form-group mb-4">
                                        <label>Catatan Review</label>
                                        <textarea name="note" class="form-control" style="min-height: 200px" disabled
                                            placeholder="Catatan Review">{{ $submission->verificators()->where('user_id', auth()->user()->id)->first()->pivot->note }}</textarea>
                                    </div>
                                @endif

                                @if ($submission->format->required_values->count() > 0)
                                    <div class="w-100">
                                        <div class="divider">
                                            <div class="divider-text">Penilaian yang diperlukan</div>
                                        </div>
                                    </div>

                                    @php
                                        $averageValue = 0;
                                        $totalValue = 0;
                                        $submission_format_required_values = $submission->format->required_values;
                                    @endphp
                                    @foreach ($submission_format_required_values as $i => $submisison_format_required_value)
                                        @php
                                            $valueFromVerificator = $submission
                                                ->required_values()
                                                ->where('required_value_id', $submisison_format_required_value->id)
                                                ->where('verificator_id', $verificator->id)
                                                ->first();
                                            $averageValue += $valueFromVerificator ? $valueFromVerificator->value * ($valueFromVerificator->required_value->weight / 100) : 0;
                                            $totalValue += $valueFromVerificator ? $valueFromVerificator->value : 0;
                                        @endphp

                                        <div class="row align-items-center">
                                            <div class="col-md-9">
                                                #{{ $i + 1 }} - <b>{{ $submisison_format_required_value->name }}</b>
                                                    ({{ $submisison_format_required_value->weight }}%)
                                                    <br />
                                                    <small class="text-muted text-sm" style="font-size:10px">Catatan: {{ $submisison_format_required_value->note }}</small>
                                            </div>

                                            <div class="col-md-3">
                                                <label class="col-form-label">Nilai (0-100)</label>
                                                @if ($submission->status == 'pending' || $submission->status == 'processed')
                                                    <input type="number"
                                                        name="required_values[{{ $submisison_format_required_value->id }}]"
                                                        class="form-control" min="0" max="100" step="0.01"
                                                        onchange="calculateAverage(this)"
                                                        data-weight="{{ $submisison_format_required_value->weight }}" required
                                                        value="{{ $submission->required_values()->where('required_value_id', $submisison_format_required_value->id)->where('verificator_id', $verificator->id)->first()? $submission->required_values()->where('required_value_id', $submisison_format_required_value->id)->where('verificator_id', $verificator->id)->first()->value: '' }}">
                                                @else
                                                    <input type="number" class="form-control"
                                                        value="{{ $submission->required_values()->where('required_value_id', $submisison_format_required_value->id)->where('verificator_id', $verificator->id)->first()? $submission->required_values()->where('required_value_id', $submisison_format_required_value->id)->where('verificator_id', $verificator->id)->first()->value: '' }}"
                                                        readonly disabled>
                                                @endif
                                            </div>
                                        </div>

                                        <hr />
                                    @endforeach

                                    <div class="row align-items-center">
                                        <div class="col-md-9 text-end">
                                            <b>Nilai Rata-rata</b>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" disabled class="form-control"
                                                id="totalAverage"
                                                value="{{ $averageValue != 0 ? number_format($averageValue, 3, ',', ' ') : 0 }}">
                                        </div>
                                        <div class="col-md-9 text-end">
                                            <b>Total Nilai</b>
                                        </div>
                                        <div class="col-md-3">
                                            <input type="text" disabled class="form-control"
                                                id="totalNilai"
                                                value="{{ $totalValue != 0 ? number_format($totalValue, 3, ',', ' ') : 0 }}">
                                        </div>
                                    </div>

                                    <hr />
                                @endif

                            @if ($submission->status == 'pending' || $submission->status == 'processed')
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="submit" class="btn btn-primary me-1 mb-1">Simpan Penilaian</button>
                                </div>

                            </form>
                            @endif

                            <h4>Berikut penilaian yang dilakukan oleh tim lainnya.</h4>
                            <hr/>

                            @php
                                $pembimbing_count = 0;
                                $penguji_count = 0;
                            @endphp

                            @foreach ($verificators as $i => $verificator_evaluation)
                                @php
                                    if ($verificator_evaluation->type == 'Pembimbing') {
                                        $pembimbing_count++;
                                    }
                                    if ($verificator_evaluation->type == 'Penguji') {
                                        $penguji_count++;
                                    }

                                    if ($verificator_evaluation->id == $verificator->id) {
                                        continue;
                                    }
                                @endphp
                                <input type="hidden" name="verificators[{{ $i }}][id]"
                                    value="{{ $verificator_evaluation->id }}">

                                <div class="mb-2">
                                    <h5 class="capitalize">{{ $verificator_evaluation->type }}
                                        {{ $verificator_evaluation->type == 'Pembimbing' ? $pembimbing_count : $penguji_count }}
                                        - {{ $verificator_evaluation->user->name }}</h5>

                                    <div class="form-group mb-2">
                                        <label>Catatan Review</label>
                                        <textarea class="form-control" rows="3" disabled readonly
                                            placeholder="Catatan Review">{{ $verificator_evaluation->note }}</textarea>
                                    </div>

                                    @if ($submission->format->required_values->count() > 0)
                                        <small class="display-md-block d-lg-none">Jika tidak terlihat kolom input penilaian, maka cukup scroll secara horizontal ke arah kanan pada tabel penilaian.</small>

                                        @php
                                            $averageValue = 0;
                                            $totalValue = 0;
                                            $submission_required_values = $submission->format->required_values;
                                        @endphp
                                        @foreach ($submission_required_values as $j => $submission_required_value)
                                            @php
                                                $valueFromVerificator = $submission
                                                    ->required_values()
                                                    ->where('required_value_id', $submission_required_value->id)
                                                    ->where('verificator_id', $verificator_evaluation->id)
                                                    ->first();
                                                $averageValue += $valueFromVerificator ? $valueFromVerificator->value * ($valueFromVerificator->required_value->weight / 100) : 0;
                                                $totalValue += $valueFromVerificator ? $valueFromVerificator->value : 0;
                                            @endphp

                                            <div class="row align-items-center">
                                                <div class="col-md-9">
                                                    #{{ $j + 1 }} - <b>{{ $submission_required_value->name }}</b>
                                                        ({{ $submission_required_value->weight }}%)
                                                        <br />
                                                        <small class="text-muted text-sm" style="font-size:10px">Catatan: {{ $submission_required_value->note }}</small>
                                                </div>

                                                <div class="col-md-3">
                                                    <input type="number"
                                                        class="form-control {{ $valueFromVerificator ? 'bg-muted' : 'bg-danger' }}" min="0" max="100" step="0.1"
                                                        value="{{ $valueFromVerificator ? $valueFromVerificator->value : '' }}" disabled readonly>
                                                </div>
                                            </div>

                                            <hr/>
                                        @endforeach

                                        <div class="row align-items-center">
                                            <div class="col-md-9 text-end">
                                                <b>Nilai Rata-rata</b>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" disabled class="form-control"
                                                    id="totalAverage-{{ $j }}"
                                                    value="{{ $averageValue != 0 ? number_format($averageValue, 3, ',', ' ') : 0 }}">
                                            </div>
                                            <div class="col-md-9 text-end">
                                                <b>Total Nilai</b>
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" disabled class="form-control"
                                                    id="totalNilai-{{ $j }}"
                                                    value="{{ $totalValue != 0 ? number_format($totalValue, 3, ',', ' ') : 0 }}">
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <hr/>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
    <script>
        const calculateAverage = (inpObj) => {
            let averageInputForm = document.getElementById(`totalAverage`);
            let totalNilaiInputForm = document.getElementById(`totalNilai`);

            let tBodyEvaluation = inpObj.parentNode.parentNode.parentNode;
            let totalFormInput = tBodyEvaluation.querySelectorAll('input[type="number"]');

            let total = 0;
            let totalNilai = 0;

            for (let i=0;i<totalFormInput.length - 1;i++) {
                let inputWeight = totalFormInput[i].getAttribute('data-weight');
                let inputValue = totalFormInput[i].value !== '' ? totalFormInput[i].value : 0;

                let calculate = parseFloat(inputValue * (inputWeight / 100));

                total += calculate;
                totalNilai += parseFloat(inputValue);
            }

            averageInputForm.value = parseFloat(total).toFixed(3);
            totalNilaiInputForm.value = parseFloat(totalNilai).toFixed(3);
        }

        $("textarea").each(function () {
            this.setAttribute("style", "height:" + (this.scrollHeight) + "px;overflow-y:hidden;");
        }).on("input", function () {
            this.style.height = "auto";
            this.style.height = (this.scrollHeight) + "px";
        });
    </script>
@endpush
