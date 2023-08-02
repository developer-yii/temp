
@php
    $baseUrl = asset('backend')."/";
@endphp
<!-- Topbar Start -->
<div class="navbar-custom">
    <ul class="list-unstyled topbar-right-menu float-right mb-0">
        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle arrow-none" data-toggle="dropdown" href="#" role="button" aria-haspopup="false" aria-expanded="false" >
                <i class="dripicons-bell noti-icon"></i>                    
                   <div id="noti_count">  </div>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated dropdown-lg">
                <div class="dropdown-item noti-title">                        
                    <h5 class="m-0">
                        <span class="float-right">
                            <a href="javascript: void(0);" id="clear_noti" class="text-dark clear_all_noti" data-user="{{ Auth::user()->id }}">
                                
                            </a>
                        </span>Notifications
                    </h5>                       
                </div>

                <div id="notificationContainer" class="px-3" data-simplebar>
                </div>

                <!-- All-->
                <a href="javascript:void(0);" id="notify-item" class="dropdown-item text-center text-primary notify-item border-top border-light py-2">                    
                </a>
            </div>
        </li>

        <li class="dropdown notification-list">
            <a class="nav-link dropdown-toggle nav-user arrow-none mr-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="false"
                aria-expanded="false">
                <span class="account-user-avatar"> 
                    <img src="{{$baseUrl}}assets/images/users/avatar-1.jpg" alt="user-image" class="rounded-circle">
                </span>
                <span>
                    <span class="account-user-name">{{ $user->name }}</span>
                    <span class="account-position">Founder</span>
                </span>
            </a>
            <div class="dropdown-menu dropdown-menu-right dropdown-menu-animated topbar-dropdown-menu profile-dropdown">
                <!-- item-->
                <div class=" dropdown-header noti-title">
                    <h6 class="text-overflow m-0">Welcome !</h6>
                </div>

                <!-- item-->
                <a href="{{ route('admin.profile') }}" class="dropdown-item notify-item">
                    <i class="mdi mdi-account-circle mr-1"></i>
                    <span>My Account</span>
                </a>
                <a href="{{ route('logout') }}" class="dropdown-item notify-item" onclick="event.preventDefault();
                                document.getElementById('logout-form').submit();">
                    <i class="mdi mdi-logout mr-1"></i>
                    <span>Logout</span>
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>

            </div>
        </li>
    </ul> 
</div>
<!-- end Topbar -->
