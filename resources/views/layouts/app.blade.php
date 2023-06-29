<!DOCTYPE html>
<!-- saved from url=(0016)https://temp.pm/ -->
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">    
    <meta name="robots" content="index, follow">
    <meta name="application-name" content="Temp.PM">
    
    <meta name="description" content="Temporary Private Message service with encryption, self-destruction and many other security features.">    
    
    <title>Temp.PM - Temporary Private Message</title>
    
    <link rel="shortcut icon" href="{{ asset('/')}}images/favicon.ico">
    <link rel="stylesheet" href="{{ asset('/')}}css/bootstrap.min.css" >
    <link rel="stylesheet" href="{{ asset('/')}}css/bootstrap-theme.min.css" >
    <link rel="stylesheet" href="{{ asset('/')}}css/style.css" >
    
</head>

<body>      
    <div class="container">
        <div class="panel panel-default panel-background">
            <div class="panel-heading">
                <table style="width: 100%; border: 0px;">
                    <tbody>
                        <tr>
                            <td style="width: 50%; border: 0px; text-align: left;">
                                <a href="{{ route('home') }}" class="sitetitle"><img src="{{ asset('/')}}images/logo.png" alt="" class="site-logo-img">

                                <b><span style="font-size: 105%;">Temp.PM</span></b><span style="font-size: 95%;">&nbsp;-&nbsp;Temporary Private Message</span></a>
                            </td>

                            <td style="width: 50%; border: 0px; text-align: right;">
                            @guest
                                @if (Route::has('login'))
                                    <a href="{{ route('login') }}" class="btn btn-default btn-xs">&nbsp;Login&nbsp;</a>&nbsp; 
                                @endif
                                @if (Route::has('register'))    
                                    <a href="{{ route('register') }}" class="btn btn-default btn-xs">&nbsp;Register&nbsp;</a>&nbsp;
                                @endif
                            @else

                               <a href="" class="btn btn-default btn-xs">&nbsp;{{ Auth::user()->email }}&nbsp;</a>

                                <a href="{{ route('logout') }}" class="btn btn-default btn-xs" onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">&nbsp;Logout&nbsp;</a>&nbsp;
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            @endguest
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            @yield('content')
        </div>
    </div>

    <div class="container">
        <div class="row bottom2" style="margin-left: -12px; margin-right: -12px; margin-top: -15px; margin-bottom: -15px;">
            <div class="col-xs-6" style="text-align: left;">
                © Temp - 2023
            </div>
            <div class="col-xs-6" style="text-align: right;">
                BTC: 1temp5Y5VzJevLpRedW8zZFrMWuadL3UR
            </div>
        </div>
    </div>

<script src="{{ asset('/')}}js/jquery.min.js.download" ></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>
<script src="{{ asset('/')}}js/custom.js?{{time()}}" ></script>

@yield('script')
</body>
</html>