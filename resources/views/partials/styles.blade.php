<!-- SEO Meta Tags -->
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
<meta name="csrf-token" content="{{ csrf_token() }}">

<!-- Primary Meta Tags -->
<title>@yield('title', 'BOSCHMA EHR - Electronic Health Record System') | Borno State Government</title>
<meta name="title" content="@yield('seo_title', 'BOSCHMA EHR - Electronic Health Record System')">
<meta name="description" content="@yield('seo_description', 'BOSCHMA EHR is a comprehensive Electronic Health Record system for Borno State Government, providing efficient healthcare management, patient records, and medical services.')">
<meta name="keywords" content="@yield('seo_keywords', 'EHR, Electronic Health Record, Borno State, Healthcare, Medical Records, Patient Management, BOSCHMA, Digital Health')">
<meta name="author" content="Borno State Government">
<meta name="robots" content="index, follow">
<meta name="language" content="English">
<meta name="revisit-after" content="7 days">

<!-- Open Graph / Facebook -->
<meta property="og:type" content="website">
<meta property="og:url" content="{{ url()->current() }}">
<meta property="og:title" content="@yield('og_title', 'BOSCHMA EHR - Electronic Health Record System')">
<meta property="og:description" content="@yield('og_description', 'BOSCHMA EHR is a comprehensive Electronic Health Record system for Borno State Government, providing efficient healthcare management, patient records, and medical services.')">
<meta property="og:image" content="@yield('og_image', url('/assets/images/og-image.jpg'))">
<meta property="og:image:width" content="1200">
<meta property="og:image:height" content="630">
<meta property="og:image:alt" content="BOSCHMA EHR - Electronic Health Record System">
<meta property="og:site_name" content="BOSCHMA EHR">
<meta property="og:locale" content="en_US">

<!-- Twitter -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ url()->current() }}">
<meta property="twitter:title" content="@yield('twitter_title', 'BOSCHMA EHR - Electronic Health Record System')">
<meta property="twitter:description" content="@yield('twitter_description', 'BOSCHMA EHR is a comprehensive Electronic Health Record system for Borno State Government, providing efficient healthcare management, patient records, and medical services.')">
<meta property="twitter:image" content="@yield('twitter_image', url('/assets/images/og-image.jpg'))">
<meta property="twitter:image:alt" content="BOSCHMA EHR - Electronic Health Record System">

<!-- Additional SEO Meta Tags -->
<meta name="theme-color" content="#016634">
<meta name="msapplication-TileColor" content="#016634">
<meta name="application-name" content="BOSCHMA EHR">
<meta name="apple-mobile-web-app-title" content="BOSCHMA EHR">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-touch-fullscreen" content="yes">

<!-- Canonical URL -->
<link rel="canonical" href="{{ url()->current() }}">

<!-- Favicon and App Icons -->
<link rel="icon" type="image/x-icon" href="{{ url('/assets/images/favicon.ico') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ url('/assets/images/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ url('/assets/images/favicon-16x16.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ url('/assets/images/apple-touch-icon.png') }}">
<link rel="manifest" href="{{ url('/assets/images/site.webmanifest') }}">

<!-- Stylesheets -->
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/sidebar-menu.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/simplebar.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/apexcharts.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/prism.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/rangeslider.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/quill.snow.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/google-icon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/remixicon.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/swiper-bundle.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/fullcalendar.main.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/jsvectormap.min.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/css/lightpick.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ url('/assets/scss/style.css') }}" />