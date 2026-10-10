@if ($errors->any())
    <div class="alert alert-danger alert-dismissible show fade">
        <h4 class="text-lg text-white">Gagal!</h4>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($message = Session::get('success'))
    <div class="alert alert-success alert-dismissible show fade">
        <h4 class="text-lg text-white">Sukses!</h4>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        {{ $message }}
    </div>
@endif

@if ($message = Session::get('failed'))
    <div class="alert alert-danger alert-dismissible show fade">
        <h4 class="text-lg text-white">Gagal!</h4>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        {{ $message }}
    </div>
@endif
