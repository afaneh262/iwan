<!DOCTYPE html>
<html lang="{{ config('app.locale') }}" @if (config('iwan.multilingual.rtl')) dir="rtl" @endif>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="none" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimal-ui">
    <meta name="description" content="admin login">
    <title>Admin - {{ Iwan::setting("admin.title") }}</title>
    <link rel="stylesheet" href="{{ iwan_asset('css/app.css') }}">
    @if (config('iwan.multilingual.rtl'))
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap-rtl/3.4.0/css/bootstrap-rtl.css">
    <link rel="stylesheet" href="{{ iwan_asset('css/rtl.css') }}">
    @endif
    <style>
        body {
            background-image:url('{{ Iwan::image( Iwan::setting("admin.bg_image"), iwan_asset("images/bg.jpg") ) }}');
        background-color: {{ Iwan::setting("admin.bg_color", "#FFFFFF" ) }};
        }

        body.login .login-sidebar {
        border-top:5px solid {{ config('iwan.primary_color','#22A7F0') }};
        }

        @media (max-width: 767px) {
            body.login .login-sidebar {
                border-top:0px !important;
            border-left:5px solid {{ config('iwan.primary_color','#ffb119') }};
        }
        }

        body.login .form-group-default.focused{
        border-color:{{ config('iwan.primary_color','#22A7F0') }};
        }

        .reminder-button,
        .bar:before,
        .bar:after{
            background: #ffb119;
        }
    </style>

    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,700"
          rel="stylesheet">
</head>
<body class="forget-password">
<div class="container-fluid">
    <div class="row">
        <div class="faded-bg animated"></div>
        <div class="hidden-xs col-sm-7 col-md-8">
            <div class="clearfix">
                <div class="col-sm-12 col-md-10 col-md-offset-2">
                    <div class="logo-title-container">
                        <?php $admin_logo_img = Iwan::setting('admin.icon_image', ''); ?>
                        @if($admin_logo_img == '')
                        <img class="img-responsive pull-left flip logo hidden-xs animated fadeIn"
                             src="{{ iwan_asset('images/logo-icon-light.png') }}"
                             alt="Logo Icon">
                        @else
                        <img class="img-responsive pull-left flip logo hidden-xs animated fadeIn"
                             src="{{ Iwan::image($admin_logo_img) }}"
                             alt="Logo Icon">
                        @endif
                        <div class="copy animated fadeIn">
                            <h1>{{ Iwan::setting('admin.title', 'Iwan') }}</h1>
                            <p>{{ Iwan::setting('admin.description', __('iwan::login.welcome')) }}</p>
                        </div>
                    </div> <!-- .logo-title-container -->
                </div>
            </div>
        </div>
        <div class="col-xs-12 col-sm-5 col-md-4 login-sidebar">
            <div class="login-container">
                @if(!$errors->isEmpty())
                <div class="alert alert-red">
                    <ul class="list-unstyled">
                        @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif
                @if (session('status'))
                <div class="alert alert-success"
                     role="alert">
                    {{ session('status') }}
                </div>
                @endif
                <p>{{ __('iwan::login.fill_in_details_below') }}</p>
                <form action="{{ route('iwan.login') }}"
                      method="POST">
                    {{ csrf_field() }}
                    <div class="form-group form-group-default"
                         id="emailGroup">
                        <label>{{ __('iwan::generic.email') }}</label>
                        <div class="controls">
                            <input type="text"
                                   name="email"
                                   id="email"
                                   value="{{ old('email') }}"
                                   placeholder="{{ __('iwan::generic.email') }}"
                                   class="form-control"
                                   required>
                        </div>
                    </div>
                    <button type="submit"
                            class="btn btn-block reminder-button">
                        <span class="processing hidden">
                            <span class="iwan-refresh"></span>
                            {{ __('iwan::password_reminder.processing') }}...
                        </span>
                        <span class="send">
                            {{ __('Send Password Reset Link') }}
                        </span>
                    </button>
                </form>

            </div> <!-- .login-container -->

        </div> <!-- .login-sidebar -->
    </div> <!-- .row -->
</div> <!-- .container-fluid -->
<script>
    var btn = document.querySelector('button[type="submit"]');
    var form = document.forms[0];
    var email = document.querySelector('[name="email"]');
    btn.addEventListener('click', function(ev){
        if (form.checkValidity()) {
            btn.querySelector('.processing').className = 'processing';
            btn.querySelector('.send').className = 'send hidden';
        } else {
            ev.preventDefault();
        }
    });
    email.focus();
    document.getElementById('emailGroup').classList.add("focused");

    // Focus events for email and password fields
    email.addEventListener('focusin', function(e){
        document.getElementById('emailGroup').classList.add("focused");
    });
    email.addEventListener('focusout', function(e){
        document.getElementById('emailGroup').classList.remove("focused");
    });
</script>
</body>
</html>
