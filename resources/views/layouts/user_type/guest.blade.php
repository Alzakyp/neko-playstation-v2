@extends('layouts.app')

@section('guest')
    @if(\Request::is('login/forgot-password'))
        @include('layouts.navbars.guest.nav')
        @yield('content')
    @elseif(\Request::is('login'))
        {{-- Halaman login tanpa navigasi --}}
        <div class="container">
            @yield('content')
        </div>
        @include('layouts.footers.guest.footer')
    @else
        <div class="container position-sticky z-index-sticky top-0">
            <div class="row">
                <div class="col-12">
                    @include('layouts.navbars.guest.nav')
                </div>
            </div>
        </div>
        @yield('content')
        @include('layouts.footers.guest.footer')
    @endif
@endsection
