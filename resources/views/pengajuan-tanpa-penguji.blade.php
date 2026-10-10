<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="Mark Otto, Jacob Thornton, and Bootstrap contributors">
    <meta name="generator" content="Hugo 0.88.1">
    <title>Data pengajuan pada spota yang sudah sidang hasil atau sidang akhir, namun tidak ada data pengujinya....</title>

    <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/pricing/">



    <!-- Bootstrap core CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">


    <!-- Favicons -->
    <link rel="apple-touch-icon" href="/docs/5.1/assets/img/favicons/apple-touch-icon.png" sizes="180x180">
    <link rel="icon" href="/docs/5.1/assets/img/favicons/favicon-32x32.png" sizes="32x32" type="image/png">
    <link rel="icon" href="/docs/5.1/assets/img/favicons/favicon-16x16.png" sizes="16x16" type="image/png">
    <link rel="manifest" href="/docs/5.1/assets/img/favicons/manifest.json">
    <link rel="mask-icon" href="/docs/5.1/assets/img/favicons/safari-pinned-tab.svg" color="#7952b3">
    <link rel="icon" href="/docs/5.1/assets/img/favicons/favicon.ico">
    <meta name="theme-color" content="#7952b3">


    <style>
        .bd-placeholder-img {
            font-size: 1.125rem;
            text-anchor: middle;
            -webkit-user-select: none;
            -moz-user-select: none;
            user-select: none;
        }

        @media (min-width: 768px) {
            .bd-placeholder-img-lg {
                font-size: 3.5rem;
            }
        }

    </style>


    <!-- Custom styles for this template -->
    <link href="pricing.css" rel="stylesheet">
</head>

<body>

        <main class="container">
            <h2 class="display-6 text-center mb-4">Data pengajuan spota yang sudah sidang hasil atau sidang akhir, namun tidak ada data pengujinya..</h2>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>
                                ID
                            </th>
                            <th>
                                Judul
                                <br/>
                                <small class="text-muted">Nama - Nim</small>
                            </th>
                            <th>
                                Lainnya
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($submissions as $i => $submission)
                            <tr>
                                <th scope="row" class="text-start">{{ $submission['id'] }}</th>
                                <td>
                                    {{ $submission['judul'] }}
                                    <br/>
                                    <small class="text-muted">{{ $submission['mahasiswa_name'] }} - {{ $submission['mahasiswa_nim'] }}</small>
                                </td>
                                <td>
                                    <ul>
                                        Pembimbing 1
                                        <li>{{ $submission['pembimbing1_name'] ? $submission['pembimbing1_name'] : '-' }}</li>
                                        <li>{{ $submission['pembimbing1_nip'] ? $submission['pembimbing1_nip'] : '-' }}</li>
                                    </ul>
                                    <ul>
                                        Pembimbing 2
                                        <li>{{ $submission['pembimbing2_name'] ? $submission['pembimbing2_name'] : '-' }}</li>
                                        <li>{{ $submission['pembimbing2_nip'] ? $submission['pembimbing2_nip'] : '-' }}</li>
                                    </ul>
                                    <ul>
                                        Penguji 1
                                        <li>{{ $submission['penguji1_name'] ? $submission['penguji1_name'] : '-' }}</li>
                                        <li>{{ $submission['penguji1_nip'] ? $submission['penguji1_nip'] : '-' }}</li>
                                    </ul>
                                    <ul>
                                        Penguji 2
                                        <li>{{ $submission['penguji2_name'] ? $submission['penguji2_name'] : '-' }}</li>
                                        <li>{{ $submission['penguji2_nip'] ? $submission['penguji2_nip'] : '-' }}</li>
                                    </ul>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </main>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
</body>
</html>
