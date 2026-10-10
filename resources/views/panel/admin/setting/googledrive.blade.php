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
            <div class="card mt-4">
                <div class="card-body">
                    <form action="{{ route('panel.admin.setting.google.drive.update') }}" method="post">
                        @csrf
                        @method('PUT')
                        <h5>Pengaturan Google Drive</h5>
                        <div class="form-group">
                            <label for="" class="">Client ID</label>
                            <input type="text" class="form-control @error('google_client_id') is-invalid @enderror" id="google_client_id" name="google_client_id" value="{{ $settings->where('key', 'google_client_id')->first()->value }}" placeholder="Masukkan Google Client ID">
                            @error('google_client_id')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="" class="">Client Secret</label>
                            <input type="text" class="form-control @error('google_client_secret') is-invalid @enderror" id="google_client_secret" name="google_client_secret" value="{{ $settings->where('key', 'google_client_secret')->first()->value }}" placeholder="Masukkan Google Client Secret">
                            @error('google_client_secret')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="" class="">Refresh Token</label>
                            <input type="text" class="form-control @error('google_refresh_token') is-invalid @enderror" id="google_refresh_token" name="google_refresh_token" value="{{ $settings->where('key', 'google_refresh_token')->first()->value }}" placeholder="Masukkan Google Refresh Token">
                            @error('google_refresh_token')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-group">
                            <label for="" class="">Google Drive Folder ID</label>
                            <input type="text" class="form-control @error('google_drive_folder_id') is-invalid @enderror" id="google_drive_folder_id" name="google_drive_folder_id" value="{{ $settings->where('key', 'google_drive_folder_id')->first()->value }}" placeholder="Masukkan Google Drive Folder ID">
                            @error('google_drive_folder_id')
                                <div class="invalid-feedback">
                                    <i class="bx bx-radio-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <small class="text-muted text-sm">Berikut cara mendapatkan token-token tersebut, <a target="_blank" href="https://github.com/ivanvermeyen/laravel-google-drive-demo/blob/master/README.md#create-your-google-drive-api-keys">klik disini</a></small>
                        <br/>
                        <button type="submit" class="btn btn-primary me-1 mb-1 ms-auto">Submit</button>
                    </form>
                </div>
            </div>
        </section>
    </div>
@endsection
