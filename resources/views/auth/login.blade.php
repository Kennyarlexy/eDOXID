@extends('layouts.auth.index')

@section('title', 'Login')

@section('main')
    <div id="auth">

        <div class="row h-100">
            <div class="col-lg-5 col-12">
                <div id="auth-left">
                    <div class="auth-logo">
                        <a href="{{ route('index') }}"><img
                                src="{{ \App\Models\Setting::where('key', 'site_logo')->first() ? asset('storage/settings/' . \App\Models\Setting::where('key', 'site_logo')->first()->value) : asset('/img/logo/logo.png') }}"
                                alt="Logo"></a>
                    </div>
                    <div class="mt-2 mb-2">
                        @include('includes.form-alert')
                    </div>
                    <h1 class="auth-title">Log in.</h1>
                    <p class="auth-subtitle mb-5">Log in with your data from SPOTA</p>

                    <form action="{{ route('login') }}" method="post">
                        @csrf
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="text" class="form-control form-control-xl" placeholder="Username"
                                value="{{ old('username') }}" name="username">
                            <div class="form-control-icon">
                                <i class="bi bi-person"></i>
                            </div>
                        </div>
                        <div class="form-group position-relative has-icon-left mb-4">
                            <input type="password" class="form-control form-control-xl" placeholder="Password"
                                name="password">
                            <div class="form-control-icon">
                                <i class="bi bi-shield-lock"></i>
                            </div>
                        </div>
                        <div class="form-check">
                            <div class="checkbox">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me" value="1">
                                <label for="remember_me">Remember me!</label>
                            </div>
                        </div>
                        <button class="btn btn-primary btn-block btn-lg shadow-lg mt-5">Log in</button>
                    </form>
                </div>
            </div>
            <div class="col-lg-7 col-12">
                <div class="row align-items-center bg-light" style="height: 100vh">
                    <div class="col-12 mx-auto">
                        <div class="card">
                            <div class="card-body">
                                <h3>Total Pengajuan</h3>
                                <form action="" class="row mb-4 align-middle items-center d-flex justify-content-end">

                                    <div class="col-md-4 col-ld-2">
                                        <div class="form-group">
                                            <label for="tahun_statistik">Tahun</label>
                                            <select class="form-select" id="tahun_statistik" name="tahun_statistik">
                                                @for ($i = date('Y'); $i >= date('Y') - 5; $i--)
                                                    <option value="{{ $i }}" {{ Request::get('tahun_statistik') == $i ? 'selected' : '' }}>
                                                        {{ $i }}
                                                    </option>
                                                @endfor
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-md-4 col-lg-2">
                                        <div class="form-group">
                                            <label for="filter"></label>
                                            <button type="submit" class="form-control btn btn-primary text-light">Filter</button>
                                        </div>
                                    </div>

                                </form>
                                <div id="chart-submissions"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('scripts')
    <script src="{{ asset('/plugins/apexcharts/apexcharts.js') }}"></script>

    <script>
        var proposalSubmissions = @json($proposal_submissions);
        var sidangHasilSubmissions = @json($sidang_hasil_submissions);
        var sidangAkhirSubmissions = @json($sidang_akhir_submissions);

        var optionsSubmissions = {
            colors: [
                '#3D9400',
                '#FFB848',
                '#E6674A'
            ],
            series: [
                {
                    name: "Proposal",
                    data: proposalSubmissions,
                },
                {
                    name: "Sidang Hasil",
                    data: sidangHasilSubmissions,
                },
                {
                    name: "Sidang Akhir",
                    data: sidangAkhirSubmissions,
                },
            ],
            chart: {
                type: "bar",
                height: 500,
                width: "100%",
            },
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: "100%",
                    endingShape: "rounded",
                },
            },
            dataLabels: {
                enabled: false,
            },
            stroke: {
                show: true,
                width: 2,
                colors: ["transparent"],
            },
            xaxis: {
                categories: ["Jan", "Feb", "Mar", "Apr", "Mei", "Jun", "Jul", "Agu", "Sep", "Okt", "Nov", "Des"],
            },
            yaxis: {
                title: {
                    text: "Total Pengajuan",
                },
            },
            fill: {
                opacity: 1,
            },
            tooltip: {
                y: {
                    formatter: function(val) {
                        return val + " Total Pengajuan";
                    },
                },
            },
            responsive: [
                {
                    breakpoint: 1000,
                    options: {
                        plotOptions: {
                            bar: {
                                horizontal: true
                            }
                        },
                        legend: {
                            position: "bottom"
                        }
                    }
                }
            ]
        }


        var chartSubmissions = new ApexCharts(document.querySelector("#chart-submissions"), optionsSubmissions);
        chartSubmissions.render();
    </script>
@endpush
