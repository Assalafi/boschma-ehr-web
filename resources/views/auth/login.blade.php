<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Primary Meta Tags -->
    <title>Login - BOSCHMA EHR | Borno State Government</title>
    <meta name="title" content="Login - BOSCHMA Electronic Health Record System">
    <meta name="description" content="Secure login to BOSCHMA EHR - Borno State Government's Electronic Health Record System. Access patient records, manage healthcare services, and more.">
    <meta name="keywords" content="BOSCHMA EHR login, healthcare login, patient records, Borno State, medical system, secure login">
    <meta name="author" content="Borno State Government">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="Login - BOSCHMA Electronic Health Record System">
    <meta property="og:description" content="Secure login to BOSCHMA EHR - Borno State Government's Electronic Health Record System">
    <meta property="og:image" content="{{ url('/assets/images/og-image.jpg') }}">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="BOSCHMA EHR Login">
    <meta property="og:site_name" content="BOSCHMA EHR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="Login - BOSCHMA Electronic Health Record System">
    <meta property="twitter:description" content="Secure login to BOSCHMA EHR - Borno State Government's Electronic Health Record System">
    <meta property="twitter:image" content="{{ url('/assets/images/og-image.jpg') }}">
    <meta property="twitter:image:alt" content="BOSCHMA EHR Login">
    
    <!-- Additional Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#016634">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ url('/assets/images/favicon.ico') }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    
    <!-- Styles -->
    @include('partials.styles')
</head>
<body class="boxed-size bg-gray-200">
    <div class="d-flex align-items-center justify-content-center min-vh-100">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-5 col-md-8">
                    <div class="card border-0 rounded-4 shadow-lg">
                        <div class="card-body p-4 p-md-5">
                            <div class="text-center mb-4">
                                <img src="{{ url('/assets/images/logo.png') }}" alt="logo" class="mb-3" style="width: 80px;">
                                <h4 class="fw-bold text-dark">BOSCHMA EHR</h4>
                                <p class="text-muted">Electronic Health Record</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                    @foreach ($errors->all() as $error)
                                        <div>{{ $error }}</div>
                                    @endforeach
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            @if (session('status'))
                                <div class="alert alert-success alert-dismissible fade show" role="alert">
                                    {{ session('status') }}
                                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                </div>
                            @endif

                            <form method="POST" action="{{ route('login.post') }}">
                                @csrf
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-medium">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <span class="material-symbols-outlined fs-6">mail</span>
                                        </span>
                                        <input type="email" 
                                               class="form-control @error('email') is-invalid @enderror" 
                                               id="email" 
                                               name="email" 
                                               value="{{ old('email') }}" 
                                               placeholder="Enter your email"
                                               required 
                                               autofocus>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="password" class="form-label fw-medium">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white">
                                            <span class="material-symbols-outlined fs-6">lock</span>
                                        </span>
                                        <input type="password" 
                                               class="form-control @error('password') is-invalid @enderror" 
                                               id="password" 
                                               name="password" 
                                               placeholder="Enter your password"
                                               required>
                                        <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                            <span class="material-symbols-outlined fs-6">visibility</span>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-4 d-flex justify-content-between align-items-center">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="remember">Remember me</label>
                                    </div>
                                </div>

                                <button type="submit" style="background-color: #016634" class="btn btn-primary w-100 py-2 fw-medium">
                                    <span class="material-symbols-outlined align-middle me-1">login</span>
                                    Sign In
                                </button>
                            </form>

                            <div class="text-center mt-4">
                                <small class="text-muted">&copy; {{ date('Y') }} BOSCHMA Health Insurance. All rights reserved.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ url('/assets/js/bootstrap.bundle.min.js') }}"></script>
    <script>
        document.getElementById('togglePassword').addEventListener('click', function() {
            const password = document.getElementById('password');
            const icon = this.querySelector('.material-symbols-outlined');
            if (password.type === 'password') {
                password.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                password.type = 'password';
                icon.textContent = 'visibility';
            }
        });
    </script>
</body>
</html>
