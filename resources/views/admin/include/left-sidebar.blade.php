 @php
     $baseUrl = asset('backend')."/";
 @endphp

 <!-- ========== Left Sidebar Start ========== -->
<div class="left-side-menu">

    <!-- LOGO -->
    <a href="" class="logo text-center logo-light">
        <span class="logo-lg">
            <img src="{{$baseUrl}}assets/images/superdick.png" alt="" width="150">
        </span>
        <span class="logo-sm">
            <img src="{{$baseUrl}}assets/images/superdick.png" alt="" width="150">
        </span>
    </a>

    <!-- LOGO -->
    <a href="" class="logo text-center logo-dark">
        <span class="logo-lg">
            <!-- <img src="{{$baseUrl}}assets/images/logo-dark.png" alt="" height="16"> -->
            <img src="{{$baseUrl}}assets/images/superdick.png" alt="" width="150">
        </span>
        <span class="logo-sm">
            <!-- <img src="{{$baseUrl}}assets/images/logo_sm_dark.png" alt="" height="16"> -->
            <img src="{{$baseUrl}}assets/images/superdick.png" alt="" width="150">
        </span>
    </a>

    <div class="h-100" id="left-side-menu-container" data-simplebar>

        <!--- Sidemenu -->
        <ul class="metismenu side-nav">
            @if($user->role_type== 1)
                <li class="side-nav-item">
                    <a href="{{ route('admin.home')}}" class="side-nav-link">
                        <i class="uil-home-alt"></i>
                        <span>Dashboard </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.user.list')}}" class="side-nav-link">
                        <i class="uil-user"></i>
                        <span> Users </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.message')}}" class="side-nav-link">
                        <i class="uil-message"></i>
                        <span> Messages </span>
                    </a>
                </li>

                <li class="side-nav-item">
                    <a href="{{ route('admin.note.list')}}" class="side-nav-link">
                        <i class="uil-comment-alt-notes"></i>
                        <span> Notes </span>
                    </a>
                </li>
                <li class="side-nav-item">
                    <a href="{{ route('admin.setting.register')}}" class="side-nav-link">
                        <i class="uil-comment-alt-notes"></i>
                        <span> Settings </span>
                    </a>
                </li>
             @endif

        </ul>
        <!-- End Sidebar -->
        <div class="clearfix"></div>
    </div>
    <!-- Sidebar -left -->

</div>
<!-- Left Sidebar End -->