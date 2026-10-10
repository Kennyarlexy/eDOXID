@extends('layouts.panel.index')
@section('title', 'Detail Pengajuan: ' . $submission->title)

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
    <script src="https://code.jquery.com/jquery-3.4.1.min.js"></script>
    <link href="{{ asset('/plugins/datetimepicker/jquery.datetimepicker.css') }}" rel="stylesheet">
@endpush

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <a href="{{ route('panel.admin.pengajuan.index', $type->slug) }}"
                        class="btn btn-secondary">Kembali</a>
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
                            <form
                                action="{{ route('panel.admin.pengajuan.evaluation.update', ['type_slug' => $submission->type->slug,'id' => $submission->id]) }}"
                                method="post">
                                @csrf
                                @method('PUT')

                                @php
                                    $pembimbing_count = 0;
                                    $penguji_count = 0;
                                @endphp

                                @foreach ($verificators as $i => $verificator)
                                    @php
                                        if ($verificator->type == 'Pembimbing') {
                                            $pembimbing_count++;
                                        }
                                        if ($verificator->type == 'Penguji') {
                                            $penguji_count++;
                                        }
                                    @endphp
                                    <input type="hidden" name="verificators[{{ $i }}][id]"
                                        value="{{ $verificator->id }}">

                                    <div class="form-group mb-2">
                                        <h5 class="capitalize">{{ $verificator->type }}
                                            {{ $verificator->type == 'Pembimbing' ? $pembimbing_count : $penguji_count }}
                                            - {{ $verificator->user->name }}</h5>

                                        @if ($verificator->is_need_to_change_trial_status)
                                            <div class="form-group required mb-4">
                                                <label class="control-label">Status Pengajuan</label>
                                                <select class="form-select"
                                                    name="verificators[{{ $i }}][status]">
                                                    <option value="pending">Pilih Status</option>
                                                    <option value="approved"
                                                        {{ $verificator->status == 'approved' ? 'selected' : '' }}>Diterima
                                                    </option>
                                                    <option value="rejected"
                                                        {{ $verificator->status == 'rejected' ? 'selected' : '' }}>Ditolak
                                                    </option>
                                                </select>
                                            </div>
                                        @endif

                                        <div class="form-group mb-2">
                                            <label>Catatan Review</label>
                                            <textarea name="verificators[{{ $i }}][note]" class="form-control" rows="3"
                                                placeholder="Catatan Review">{{ $verificator->note }}</textarea>
                                        </div>

                                        @if ($submission->format->required_values->count() > 0)
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
                                                        ->where('verificator_id', $verificator->id)
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
                                                        <label class="col-form-label">Nilai (0-100)</label>
                                                        <input type="number"
                                                            onchange="calculateAverage('{{ $i }}', this)"
                                                            name="verificators[{{ $i }}][required_values][{{ $submission_required_value->id }}]"
                                                            class="form-control" min="0" max="100" step="0.1"
                                                            data-weight="{{ $submission_required_value->weight }}" required
                                                            value="{{ $valueFromVerificator ? $valueFromVerificator->value : '' }}">
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
                                                        id="totalAverage-{{ $i }}"
                                                        value="{{ $averageValue != 0 ? number_format($averageValue, 3, ',' , ' ') : 0 }}">
                                                </div>
                                                <div class="col-md-9 text-end">
                                                    <b>Total Nilai</b>
                                                </div>
                                                <div class="col-md-3">
                                                    <input type="text" disabled class="form-control"
                                                        id="totalNilai-{{ $i }}"
                                                        value="{{ $totalValue != 0 ? number_format($totalValue, 3, ',' , ' ') : 0 }}">
                                                </div>
                                            </div>

                                            <hr/>
                                        @endif
                                    </div>
                                @endforeach

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
    <script src="{{ asset('plugins/datetimepicker/jquery.js') }}"></script>
    <script src="{{ asset('plugins/datetimepicker/jquery.datetimepicker.full.min.js') }}"></script>
    <script>
        jQuery.datetimepicker.setLocale('id');
        jQuery('#rekomendasi_waktu_sidang_ulang').datetimepicker({
            format:'d-m-Y H:i',
            allowTimes:[
                '07:00',
                '08:00',
                '09:00',
                '10:00',
                '11:00',
                '12:00',
                '13:00',
                '14:00',
                '15:00',
                '16:00',
            ]
        });

        const calculateAverage = (ind, inpObj) => {
            let averageInputForm = document.getElementById(`totalAverage-${ind}`);
            let totalNilaiInputForm = document.getElementById(`totalNilai-${ind}`);

            let tBodyEvaluation = inpObj.parentNode.parentNode.parentNode;
            let totalFormInput = tBodyEvaluation.querySelectorAll('input[type="number"]');
            let total = 0;
            let totalNilai = 0;

            for (let i=0;i<totalFormInput.length;i++) {
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
