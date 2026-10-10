@extends('layouts.base')

@push('styles')
<link rel="stylesheet" href="{{ asset('/css/pages/auth.css') }}">
@endpush

@section('template')
    @yield('main')
@endsection
