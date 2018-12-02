<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" @if (config('iwan.multilingual.rtl')) dir="rtl" @endif>
<head>
    <title>@yield('page_title', setting('admin.title') . " - " . setting('admin.description'))</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}"/>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700" rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" href="{{ iwan_asset('images/logo-icon.png') }}" type="image/x-icon">



    <!-- App CSS -->
    <link rel="stylesheet" href="{{ iwan_asset('css/app.css') }}">

    @yield('css')
    @if(config('iwan.multilingual.rtl'))
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css">
        <link rel="stylesheet" href="{{ iwan_asset('css/rtl.css') }}">
    @endif

    <!-- Few Dynamic Styles -->
    <style type="text/css">
        .iwan .side-menu .navbar-header {
            background:{{ config('iwan.primary_color','#22A7F0') }};
            border-color:{{ config('iwan.primary_color','#22A7F0') }};
        }
        .widget .btn-primary{
            border-color:{{ config('iwan.primary_color','#22A7F0') }};
        }
        .widget .btn-primary:focus, .widget .btn-primary:hover, .widget .btn-primary:active, .widget .btn-primary.active, .widget .btn-primary:active:focus{
            background:{{ config('iwan.primary_color','#22A7F0') }};
        }
        .iwan .breadcrumb a{
            color:{{ config('iwan.primary_color','#22A7F0') }};
        }
    </style>

    @if(!empty(config('iwan.additional_css')))<!-- Additional CSS -->
        @foreach(config('iwan.additional_css') as $css)<link rel="stylesheet" type="text/css" href="{{ asset($css) }}">@endforeach
    @endif

    @yield('head')
</head>

<body class="iwan @if(isset($dataType) && isset($dataType->slug)){{ $dataType->slug }}@endif">

<div id="iwan-loader">
    <?php $admin_loader_img = Iwan::setting('admin.loader', ''); ?>
    @if($admin_loader_img == '')
        <img src="{{ iwan_asset('images/logo-icon.png') }}" alt="Iwan Loader">
    @else
        <img src="{{ Iwan::image($admin_loader_img) }}" alt="Iwan Loader">
    @endif
</div>

<?php
if (starts_with(Auth::user()->avatar, 'http://') || starts_with(Auth::user()->avatar, 'https://')) {
    $user_avatar = Auth::user()->avatar;
} else {
    $user_avatar = Iwan::image(Auth::user()->avatar);
}
?>

<div class="app-container">
    <div class="fadetoblack visible-xs"></div>
    <div class="row content-container">
        @include('iwan::dashboard.navbar')
        @include('iwan::dashboard.sidebar')
        <script>
            (function(){
                    var appContainer = document.querySelector('.app-container'),
                        sidebar = appContainer.querySelector('.side-menu'),
                        navbar = appContainer.querySelector('nav.navbar.navbar-top'),
                        loader = document.getElementById('iwan-loader'),
                        hamburgerMenu = document.querySelector('.hamburger'),
                        sidebarTransition = sidebar.style.transition,
                        navbarTransition = navbar.style.transition,
                        containerTransition = appContainer.style.transition;

                    sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition =
                    appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition =
                    navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = 'none';

                    if (window.localStorage && window.localStorage['iwan.stickySidebar'] == 'true') {
                        appContainer.className += ' expanded no-animation';
                        loader.style.left = (sidebar.clientWidth/2)+'px';
                        hamburgerMenu.className += ' is-active no-animation';
                    }

                   navbar.style.WebkitTransition = navbar.style.MozTransition = navbar.style.transition = navbarTransition;
                   sidebar.style.WebkitTransition = sidebar.style.MozTransition = sidebar.style.transition = sidebarTransition;
                   appContainer.style.WebkitTransition = appContainer.style.MozTransition = appContainer.style.transition = containerTransition;
            })();
        </script>
        <!-- Main Content -->
        <div class="container-fluid">
            <div class="side-body padding-top">
                @yield('page_header')
                <div id="iwan-notifications"></div>
                @yield('content')
            </div>
        </div>
    </div>
</div>
@include('iwan::partials.app-footer')

<!-- Javascript Libs -->


<script type="text/javascript" src="{{ iwan_asset('js/app.js') }}"></script>


<script>
    @if(Session::has('alerts'))
        let alerts = {!! json_encode(Session::get('alerts')) !!};
        helpers.displayAlerts(alerts, toastr);
    @endif

    @if(Session::has('message'))

    // TODO: change Controllers to use AlertsMessages trait... then remove this
    var alertType = {!! json_encode(Session::get('alert-type', 'info')) !!};
    var alertMessage = {!! json_encode(Session::get('message')) !!};
    var alerter = toastr[alertType];

    if (alerter) {
        alerter(alertMessage);
    } else {
        toastr.error("toastr alert-type " + alertType + " is unknown");
    }

    @endif
</script>
@yield('javascript')

@if(!empty(config('iwan.additional_js')))<!-- Additional Javascript -->
    @foreach(config('iwan.additional_js') as $js)<script type="text/javascript" src="{{ asset($js) }}"></script>@endforeach
@endif

<script type="text/javascript" src="/admin-resources/js/admin.js"></script>
<style>
    .OpenningHours {
        margin: 0 0 30px;
    }

    .OpenningHours .OpenningHoursHeader{
        display: flex;
    }

    .OpenningHours .OpenningHoursHeader .ColTitle {
        width: 100px;
    }

    .OpenningHours .OpenningHoursHeader .ColBody {
        width: calc(100% - 100px);
        display: flex;
    }

    .OpenningHours .OpenningHoursHeader .Hour {
        width: 4.166666666666667%;
        text-align: center;
    }

    .OpenningHours .OpenningHoursBody .DayRow{
        display: flex;
        margin-bottom: 1px;
    }

    .OpenningHours .OpenningHoursBody .ColTitle {
        width: 100px;
    }

    .OpenningHours .OpenningHoursBody .ColBody {
        width: calc(100% - 100px);
        display: flex;
    }

    .OpenningHours .OpenningHoursBody .Hour {
        width: 4.166666666666667%;
        text-align: center;
        display: flex;
    }

    .OpenningHours .OpenningHoursBody .Hour .HourPart{
        width: 25%;
        border-right: 0.5px solid #fff;
        background: #ccc;
    }

    .OpenningHours .OpenningHoursBody .Hour:nth-child(even) .HourPart{
        background: #e0e0e0;
    }

    .OpenningHours .OpenningHoursBody .Hour .HourPart.ui-selected{
        background: #38b0f1;
    }
</style>
</body>
</html>
