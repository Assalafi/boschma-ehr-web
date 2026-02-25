<!DOCTYPE html>
<html lang="en">
<head>
    <!-- SEO Meta Tags -->
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Primary Meta Tags -->
    <title>@yield('title', 'Dashboard') - BOSCHMA EHR | Borno State Government</title>
    <meta name="title" content="@yield('seo_title', 'Dashboard - BOSCHMA Electronic Health Record System')">
    <meta name="description" content="@yield('seo_description', 'BOSCHMA EHR dashboard - Comprehensive Electronic Health Record system for Borno State Government. Manage patients, appointments, medical records, and healthcare services.')">
    <meta name="keywords" content="@yield('seo_keywords', 'BOSCHMA EHR, EHR dashboard, healthcare management, patient records, Borno State, medical dashboard, Electronic Health Record')">
    <meta name="author" content="Borno State Government">
    <meta name="robots" content="index, follow">
    
    <!-- Open Graph / Facebook -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('og_title', 'Dashboard - BOSCHMA Electronic Health Record System')">
    <meta property="og:description" content="@yield('og_description', 'BOSCHMA EHR dashboard - Comprehensive Electronic Health Record system for Borno State Government')">
    <meta property="og:image" content="@yield('og_image', url('/assets/images/og-image.jpg'))">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="BOSCHMA EHR Dashboard">
    <meta property="og:site_name" content="BOSCHMA EHR">
    
    <!-- Twitter -->
    <meta property="twitter:card" content="summary_large_image">
    <meta property="twitter:url" content="{{ url()->current() }}">
    <meta property="twitter:title" content="@yield('twitter_title', 'Dashboard - BOSCHMA Electronic Health Record System')">
    <meta property="twitter:description" content="@yield('twitter_description', 'BOSCHMA EHR dashboard - Comprehensive Electronic Health Record system for Borno State Government')">
    <meta property="twitter:image" content="@yield('twitter_image', url('/assets/images/og-image.jpg'))">
    <meta property="twitter:image:alt" content="BOSCHMA EHR Dashboard">
    
    <!-- Additional Meta Tags -->
    <meta name="theme-color" content="#016634">
    <meta name="application-name" content="BOSCHMA EHR">
    
    <!-- Canonical URL -->
    <link rel="canonical" href="{{ url()->current() }}">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@100..900&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" rel="stylesheet">
    
    <!-- Styles -->
    @include('partials.styles')
    @stack('styles')
</head>
<body class="boxed-size">
    @include('partials.preloader')
    
    <div class="container-fluid">
        <div class="main-wrapper d-flex">
            @include('partials.sidebar')

            <div class="main-content flex-grow-1">
                @include('partials.header')

                <div class="main-content-inner p-4">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </div>
        </div>
    </div>

    @include('partials.scripts')
    @stack('scripts')
</body>
</html>
