@extends('layouts.panel.index')
@section('title', 'Pengaturan')

@section('main')
    <div class="page-heading">
        <div class="page-title mb-4">
            <div class="row">
                <div class="col-12">
                    <h3>Pengaturan</h3>
                </div>
            </div>
        </div>
        <section class="section">
            @include('includes.form-alert')
            <div class="card">
                <div class="card-body">
                    <form action="{{ route('panel.admin.setting.whatsapp.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <h5>Pengaturan Integrasi Whatsapp</h5>
                        <div class="form-group">
                            <label for="" class="">API KEY</label>
                            <input type="text" class="form-control @error('wa_api_key') is-invalid @enderror" id="wa_api_key" name="wa_api_key" value="{{ $settings->where('key', 'wa_api_key')->first()->value }}" placeholder="Masukkan API KEY">
                            @error('wa_api_key')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="" class="">Nomor HP pengirim</label>
                            <input type="text" class="form-control @error('wa_sender_phone_number') is-invalid @enderror" id="wa_sender_phone_number" name="wa_sender_phone_number" value="{{ $settings->where('key', 'wa_sender_phone_number')->first()->value }}" placeholder="Masukkan nomor HP WA pengirim">
                            @error('wa_sender_phone_number')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary me-1 mb-1 ms-auto">Submit</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
