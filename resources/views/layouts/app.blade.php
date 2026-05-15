@php
    $baseUrlBackend = asset('backend') . '/';
@endphp

<!DOCTYPE html>
<!-- saved from url=(0016)https://temp.pm/ -->
<html>

<head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="index, follow">
    <meta name="application-name" content="Temp.PM">

    <meta name="description"
        content="Temporary Private Message service with encryption, self-destruction and many other security features.">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <title>Temp.PM - Temporary Private Message</title>

    <link rel="shortcut icon" href="{{ asset('/') }}images/favicon.ico">
    <link rel="stylesheet" href="{{ asset('/') }}css/bootstrap.min.css">
    @php
        $route_name = Route::currentRouteName();
        if (
            $route_name == 'notes.list' ||
            $route_name == 'image.list' ||
            $route_name == 'delivery.list' ||
            $route_name == 'notifications.list' ||
            $route_name == 'my.links'
        ) {
            $main_css = 'style_new';
        } else {
            $main_css = 'style';
        }
    @endphp
    <link rel="stylesheet" href="{{ asset('/') }}css/{{ $main_css }}.css">
    <link rel="stylesheet" href="{{ asset('/') }}css/toastr.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">


    <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"> -->

    <!-- <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.3/css/bootstrap.min.css" /> -->
    <!-- <link href="https://cdn.datatables.net/1.10.16/css/jquery.dataTables.min.css" rel="stylesheet"> -->
    <!-- <link href="https://cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css" rel="stylesheet"> -->
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
    
    <!-- GLightbox CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/css/glightbox.min.css">

    <link rel="stylesheet" href="{{ asset('/') }}backend/assets/css/custom.css">
    <style>
        .ui-autocomplete {
            z-index: 1051 !important;
            /* Ensure it is above the modal */
        }
    </style>
    @yield('css')
</head>

<body id="scrollSec">
    <div class="container">
        <div class="panel panel-default panel-background">
            <div class="panel-heading">
                @if (in_array(Route::currentRouteName(), ['home', 'profile.view', 'message.read1', 'image.action']))
                    {{-- New Flexbox Layout for Home and Profile Page with Two Rows --}}
                    <div
                        style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                        <div style="flex: 0 0 auto;">
                            <a href="{{ route('home') }}" class="sitetitle"><img
                                    src="{{ asset('/') }}images/logo.png" alt="" class="site-logo-img">
                                <b><span style="font-size: 105%;">Temp.PM</span></b><span
                                    style="font-size: 95%;">&nbsp;-&nbsp;Temporary Private Message</span></a>
                        </div>
                        <div
                            style="flex: 1 1 auto; display: flex; flex-direction: column; align-items: flex-end; gap: 5px;">
                            @guest
                                <div style="display: flex; gap: 5px;">
                                    @if (Route::has('login'))
                                        <a href="{{ route('login') }}" class="btn btn-default btn-xs">Login</a>
                                    @endif
                                    @if (Route::has('register') && $isRegisterEnabled)
                                        <a href="{{ route('register') }}" class="btn btn-default btn-xs"> Register </a>
                                    @endif
                                </div>
                            @else
                                {{-- Top Row: Standard User Links --}}
                                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                                    <a href="{{ route('image.list') }}" class="btn btn-default btn-xs">My Images</a>
                                    <a href="{{ route('notes.list') }}" class="btn btn-default btn-xs">My Notes</a>
                                    <a href="{{ route('my.links') }}" class="btn btn-default btn-xs">My Links</a>
                                    <a href="{{ route('profile.view') }}"
                                        class="btn btn-default btn-xs">&nbsp;{{ Auth::user()->email }}&nbsp;</a>
                                    <a href="{{ route('logout') }}" class="btn btn-default btn-xs"
                                        onclick="event.preventDefault(); document.getElementById('logout-form').submit();">&nbsp;Logout&nbsp;</a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>

                                {{-- Bottom Row: Admin & Notifications --}}
                                <div style="display: flex; gap: 5px; flex-wrap: wrap; justify-content: flex-end;">
                                    @if (Auth::user()->canAccessAdminPanel())
                                        <a href="{{ route('admin.home') }}" class="btn btn-default btn-xs">Go to Admin
                                            Panel</a>
                                    @endif
                                    <a href="{{ route('notifications.list') }}"
                                        class="btn btn-default btn-xs notification-btn">Notifications
                                        @if (($unreadNotificationCount ?? 0) > 0)
                                            <span class="badge">{{ $unreadNotificationCount }}</span>
                                        @endif
                                    </a>
                                    <a href="{{ route('delivery.list') }}"
                                        class="btn btn-default btn-xs notification-btn">Zustellung
                                        @if (($unreadDeliveryCount ?? 0) > 0)
                                            <span class="badge">{{ $unreadDeliveryCount }}</span>
                                        @endif
                                    </a>
                                </div>
                            @endguest
                        </div>
                    </div>
                @else
                    {{-- Original Table Layout for Other Pages --}}
                    <table style="width: 100%; border: 0px;">
                        <tbody>
                            <tr>
                                <td style="width: 36%; border: 0px; text-align: left;">
                                    <a href="{{ route('home') }}" class="sitetitle"><img
                                            src="{{ asset('/') }}images/logo.png" alt=""
                                            class="site-logo-img">
                                        <b><span style="font-size: 105%;">Temp.PM</span></b><span
                                            style="font-size: 95%;">&nbsp;-&nbsp;Temporary Private Message</span></a>
                                </td>

                                <td style="width: 64%; border: 0px; text-align: right;">
                                    @guest
                                        @if (Route::has('login'))
                                            <a href="{{ route('login') }}" class="btn btn-default btn-xs">Login</a>
                                        @endif
                                        @if (Route::has('register') && $isRegisterEnabled)
                                            <a href="{{ route('register') }}" class="btn btn-default btn-xs"> Register </a>
                                        @endif
                                    @else
                                        @if (Auth::user()->canAccessAdminPanel())
                                            <a href="{{ route('admin.home') }}" class="btn btn-default btn-xs">Go to Admin
                                                Panel</a>
                                        @endif
                                        <a href="{{ route('notifications.list') }}"
                                            class="btn btn-default btn-xs notification-btn">Notifications
                                            @if (($unreadNotificationCount ?? 0) > 0)
                                                <span class="badge">{{ $unreadNotificationCount }}</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('delivery.list') }}"
                                            class="btn btn-default btn-xs notification-btn">Zustellung
                                            @if (($unreadDeliveryCount ?? 0) > 0)
                                                <span class="badge">{{ $unreadDeliveryCount }}</span>
                                            @endif
                                        </a>
                                        <a href="{{ route('image.list') }}" class="btn btn-default btn-xs">My Images</a>
                                        <a href="{{ route('notes.list') }}" class="btn btn-default btn-xs">My Notes</a>
                                        <a href="{{ route('my.links') }}" class="btn btn-default btn-xs">My Links</a>

                                        <a href="{{ route('profile.view') }}"
                                            class="btn btn-default btn-xs">&nbsp;{{ Auth::user()->email }}&nbsp;</a>

                                        <a href="{{ route('logout') }}" class="btn btn-default btn-xs"
                                            onclick="event.preventDefault();
                                    document.getElementById('logout-form').submit();">&nbsp;Logout&nbsp;</a>&nbsp;
                                        <form id="logout-form" action="{{ route('logout') }}" method="POST"
                                            class="d-none">
                                            @csrf
                                        </form>
                                    @endguest
                                </td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            </div>
            @yield('content')
        </div>
    </div>

    <div class="container">
        <div class="row bottom2"
            style="margin-left: -12px; margin-right: -12px; margin-top: -15px; margin-bottom: -15px;">
            <div class="col-xs-6" style="text-align: left;">
                © {{ ENV('APP_NAME') }} -
                <script>
                    document.write(new Date().getFullYear())
                </script>
            </div>
            <div class="col-xs-6" style="text-align: right;">
                BTC: 1temp5Y5VzJevLpRedW8zZFrMWuadL3UR
            </div>
        </div>
    </div>

    <script src="{{ asset('/') }}js/jquery.min.js.download"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/2.0.8/clipboard.min.js"></script>

    <script src="https://cdn.datatables.net/1.10.16/js/jquery.dataTables.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js"></script>
    
    <!-- GLightbox JS -->
    <script src="https://cdn.jsdelivr.net/npm/glightbox@3.2.0/dist/js/glightbox.min.js"></script>
    
    <script src="{{ $baseUrlBackend }}assets/js/sweetalert2.js?time()"></script>

    <script src="{{ asset('/') }}js/custom.js?{{ time() }}"></script>
    <script src="{{ asset('/') }}js/toastr.js?{{ time() }}"></script>

    @yield('modal')
    @yield('script')
</body>

</html>
