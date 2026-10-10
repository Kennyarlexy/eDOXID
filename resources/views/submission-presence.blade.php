<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Pengisian Daftar Kehadiran -
        {{ \App\Models\Setting::where('key', 'site_name')->first() ? \App\Models\Setting::where('key', 'site_name')->first()->value : 'Laravel' }}
    </title>
    <link rel="stylesheet" href="{{ asset('/css/main/app.css') }}">
    <link rel="shortcut icon" href="assets/images/logo/favicon.svg" type="image/x-icon">
    <link rel="shortcut icon" href="assets/images/logo/favicon.png" type="image/png">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('/css/bootstrap.css') }}">

    <link rel="stylesheet" href="{{ asset('/plugins/iconly/bold.css') }}">

    <link rel="stylesheet" href="{{ asset('/plugins/perfect-scrollbar/perfect-scrollbar.css') }}">
    <link rel="stylesheet" href="{{ asset('/plugins/bootstrap-icons/bootstrap-icons.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('/plugins/fontawesome/all.min.css') }}"> --}}
    <script src="https://kit.fontawesome.com/8086b26683.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" href="{{ asset('/css/app.css') }}">
    <link rel="shortcut icon" href="{{ asset('/images/favicon.svg') }}" type="image/x-icon">
</head>

<body>
    <nav class="navbar navbar-light">
        <div class="container d-block">
            <a class="navbar-brand d-flex justify-content-center">
                <img src="{{ \App\Models\Setting::where('key', 'site_logo')->first() ? asset('storage/settings/' . \App\Models\Setting::where('key', 'site_logo')->first()->value) : asset('/img/logo/logo.png') }}">
            </a>
        </div>
    </nav>


    <div class="container">
        @include('includes.form-alert')
        <form action="{{ route('isi-daftar-hadir.store', ['type_slug' => $type->slug, 'submission_id' => $submission->id ]) }}" method="POST" id="form">
            @csrf

            <div class="card mt-5">
                <div class="card-header pb-2">
                    <h4>{{ $submission->title }}</h4>
                    <div class="d-flex mb-4">
                        <span class="text-muted">
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
                </div>
                <div class="card-header">
                    <h4 class="card-title">Form Pengisian Daftar Kehadiran</h4>
                    <p class="mb-0">Dibuka pada: {{ date('d-m-Y H:i', strtotime($submission->presence_public_url_open_datetime)) }}</p>
                    <p class="mb-0">Ditutup pada: {{ date('d-m-Y H:i', strtotime($submission->presence_public_url_close_datetime)) }}</p>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label for="nama">Nama</label>
                        <input type="text" class="form-control" id="nama" name="nama" placeholder="Nama" autocomplete="on">
                    </div>
                    <div class="form-group">
                        <label for="nim_nip_nidk">NIM/NIP/NIDK/NIDN</label>
                        <input type="text" class="form-control" id="nim_nip_nidk" name="nim_nip_nidk" placeholder="NIM/NIP/NIDK/NIDN" autocomplete="on">
                    </div>
                    <div class="form-group">
                        <label for="keterangan">Keterangan</label>
                        <select class="form-select" id="keterangan" name="keterangan">
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="dosen">Dosen</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="w-100 text-end">
                        <button type="submit" class="btn btn-primary ml-1" onclick="onLoading(this)">
                            Simpan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <script>
        const onLoading = (e) => {
            e.setAttribute('disabled', true);
            e.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Loading';
            const form = document.getElementById('form');

            form.submit();
        }
    </script>

</body>
</html>
