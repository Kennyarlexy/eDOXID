@extends('layouts.auth.index')

@section('title', 'Verify Email')

@section('main')

    <div class="shadow-sm bg-white rounded-md w-full md:w-4/5 xl:w-3/5 mx-4 md:mx-0 overflow-hidden">
        <div class="flex flex-wrap">
            <div class="w-full md:w-2/4">
                <div class="p-6 sm:p-10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-medium leading-tight">{{ __('Verify Email') }}</h2>
                    </div>
                    <div class="mb-4 block">
                        @include('includes.form-alert')
                    </div>
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf

                        <div class="form-group relative mb-4">
                            <button type="submit"
                                class="w-full text-white text-md rounded-md py-2 focus:outline-none bg-indigo-500 transition-all duration-300 hover:bg-indigo-700">
                                {{ __('Resend Verification Email') }}
                            </button>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <div class="form-group relative text-center mb-4">
                            <button class=" w-full text-sm rounded-md flex justify-center items-stretch">
                                <span class="iconify mr-1" data-width="18" data-icon="eva:arrow-back-fill"
                                    data-inline="false"></span>
                                <span>
                                    {{ __('Log out') }}
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="w-full md:w-2/4 kiri text-white">
                <div class="relative h-full">
                    <img src="{{ asset('img/bg-auth.jpg') }}" alt="auth" class="absolute object-cover h-full w-full z-0">
                    <div class="relative z-10 h-full  flex items-center">
                        <div class="absolute inset-0 bg-indigo-500 bg-opacity-90"></div>
                        <div class="relative py-12 px-4 sm:px-10 z-20">
                            <div class="block w-full">
                                <h3 class="text-2xl font-medium mb-4">{{ __('Verify Email') }}</h3>
                                <p class="text-base text-white text-opacity-80 font-light">
                                    {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you? If you didn\'t receive the email, we will gladly send you another.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
