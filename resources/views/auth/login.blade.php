<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png') }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/favicon.png') }}">
    <title>Login | Neko PlayStation</title>

    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    <!-- CSS Files -->
    <link href="{{ asset('assets/css/soft-ui-dashboard.css?v=1.0.0') }}" rel="stylesheet" />

    <!-- PlayStation-specific styles -->
    <style>
        :root {
            --ps-blue: #006FCD;
            --ps-light-blue: #00a7e0;
            --ps-black: #0e0e0e;
        }
        .bg-gradient-playstation {
            background: linear-gradient(310deg, var(--ps-blue) 0%, var(--ps-light-blue) 100%);
        }
        .text-gradient-playstation {
            background: linear-gradient(310deg, var(--ps-blue), var(--ps-light-blue));
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
            z-index: 1;
        }
        .card-playstation {
            border-top: 3px solid var(--ps-blue);
        }
        .ps-logo {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            letter-spacing: 1px;
        }
        .ps-icons i {
            font-size: 1.2rem;
            margin-right: 0.5rem;
            color: var(--ps-blue);
        }
        .btn-playstation {
            background: linear-gradient(310deg, var(--ps-blue) 0%, var(--ps-light-blue) 100%);
            color: white;
        }
        .gaming-bg {
            background-image: linear-gradient(180deg, rgba(0,0,0,0.7) 0%, rgba(0,0,0,0.4) 100%), url('../assets/img/curved-images/curved-gaming.jpg');
            background-size: cover;
            background-position: center;
        }
    </style>
</head>

<body class="g-sidenav-show bg-gray-100">
    <main class="main-content mt-0">
        <section>
            <div class="page-header min-vh-100">
                <div class="container">
                    <div class="row">
                        <div class="col-xl-4 col-lg-5 col-md-6 d-flex flex-column mx-auto">
                            <div class="card card-plain card-playstation mt-8">
                                <div class="card-header pb-0 text-left bg-transparent">
                                    <div class="ps-logo text-center mb-3">
                                        <i class="fas fa-gamepad me-2"></i> NEKO PLAYSTATION
                                    </div>
                                    <h3 class="font-weight-bolder text-gradient-playstation">Welcome back</h3>
                                    <p class="mb-0">Enter your email and password to sign in</p>
                                </div>
                                <div class="card-body">
                                    <form role="form" method="POST" action="{{ route('login') }}">
                                        @csrf
                                        <label>Email</label>
                                        <div class="mb-3">
                                            <input type="email" class="form-control" name="email" id="email"
                                                placeholder="Email" value="{{ old('email') }}" required autofocus>
                                            @error('email')
                                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <label>Password</label>
                                        <div class="mb-3">
                                            <input type="password" class="form-control" name="password" id="password"
                                                placeholder="Password" required>
                                            @error('password')
                                                <p class="text-danger text-xs mt-2">{{ $message }}</p>
                                            @enderror
                                        </div>
                                        <div class="text-center">
                                            <button type="submit" class="btn btn-playstation w-100 mt-4 mb-0">
                                                <i class="fas fa-sign-in-alt me-2"></i> Sign in
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="oblique position-absolute top-0 h-100 d-md-block d-none me-n8">
                                <div class="oblique-image bg-cover position-absolute fixed-top ms-auto h-100 z-index-0 ms-n6 gaming-bg"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <!-- Core JS Files -->
    <script src="{{ asset('assets/js/core/popper.min.js') }}"></script>
    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/js/soft-ui-dashboard.min.js') }}"></script>
</body>
</html>
