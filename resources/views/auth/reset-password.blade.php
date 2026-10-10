@extends('layouts.auth.index')

@section('title', 'Reset Password')

@section('main')

    <div class="shadow-sm bg-white rounded-md w-full md:w-4/5 xl:w-3/5 mx-4 md:mx-0 overflow-hidden">
        <div class="flex flex-wrap">
            <div class="w-full md:w-2/4">
                <div class="p-6 sm:p-10">
                    <div class="text-center mb-8">
                        <h2 class="text-2xl font-medium leading-tight">{{ __('Forgot Password') }}</h2>
                    </div>
                    <div class="mb-4 block">
                        @include('includes.form-alert')
                    </div>
                    <form action="{{ route('password.update') }}" method="POST">
                        @csrf
                        <input type="hidden" name="token" value="{{ $request->route('token') }}">
                        <div class="form-group relative mb-4">
                            <input required type="email" name="email" value="{{ old('email', $request->email) }}"
                                placeholder="Email Address"
                                class="pr-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="iconify absolute right-3 top-2 text-gray-300" data-width="23"
                                data-icon="foundation:mail" data-inline="false"></span>
                        </div>

                        <div class="form-group relative mb-4">
                            <input required type="password" name="password" placeholder="New Password"
                                class="pr-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="iconify absolute right-3 top-2 text-gray-300" data-width="23"
                                data-icon="dashicons:lock" data-inline="false"></span>
                        </div>

                        <div class="form-group relative mb-4">
                            <input required type="password" name="password_confirmation" placeholder="Confirm Password"
                                class="pr-10 mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                            <span class="iconify absolute right-3 top-2 text-gray-300" data-width="23"
                                data-icon="dashicons:lock" data-inline="false"></span>
                        </div>

                        <div class="form-group relative mb-4">
                            <button type="submit"
                                class="w-full text-white text-md rounded-md py-2 focus:outline-none bg-indigo-500 transition-all duration-300 hover:bg-indigo-700">
                                {{ __('Update Password') }}
                            </button>
                        </div>

                        <div class="form-group relative text-center mb-4">
                            <a href="{{ route('login') }}"
                                class="w-full text-sm rounded-md flex justify-center items-stretch">
                                <span class="iconify mr-1" data-width="18" data-icon="eva:arrow-back-fill"
                                    data-inline="false"></span>
                                <span>
                                    {{ __('Login') }}
                                </span>
                            </a>
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
                                <h3 class="text-2xl font-medium mb-4">{{ __('Reset Password') }}</h3>
                                <p class="text-base text-white text-opacity-80 font-light">
                                    {{ __('Please enter your email to reset password.') }}
                                </p>
                                <p class="text-base text-white text-opacity-80 font-light">
                                    {{ __('We\'ll send password reset link to your email.') }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
