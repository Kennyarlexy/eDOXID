@extends('layouts.base')

@section('template')
    <div id="app">
        @include('layouts.panel.sidebar')
        <div id="main">
            @include('layouts.panel.header')
            @yield('main')

            <footer>
                <div class="footer clearfix mb-0 text-muted">
                    <div class="float-start">
                        <p class="mb-0 pb-0">Jika terdapat <span class="text-danger font-bold">pertanyaan</span> atau <span class="text-danger font-bold">error - bug</span> pada sistem, harap hubungi <span class="text-primary font-bold">Developer</span> dengan <a class="font-bold" style="text-decoration: underline" target="_blank" href="https://api.whatsapp.com/send?phone=6283149149457">klik disini</a></p>
                        <p>2021 &copy; Mazer</p>
                    </div>
                    <div class="float-end">
                        <p>Crafted with <span class="text-danger"><i class="bi bi-heart"></i></span> by <a
                                href="http://ahmadsaugi.com">A. Saugi</a></p>
                    </div>
                </div>
            </footer>
        </div>
    </div>
@endsection
