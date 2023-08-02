@php
    $baseUrl = asset('backend')."/";
@endphp
@extends('layouts.admin-app')

@section('title','Admin | Dashboard')
@section('content')

<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="">Temp</a></li>
                        <li class="breadcrumb-item active">Profile</li>
                    </ol>
                </div>
                <h4 class="page-title">Profile</h4>
            </div>
        </div>
    </div>
    
    
    <div class="row">
        <div class="col-12">
            <div class="card bg-info">
                <div class="card-body profile-user-box">
                    <div class="row">
                        <div class="col-sm-8">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <div class="avatar-lg">
                                        <img src="{{$baseUrl}}assets/images/users/avatar-1.jpg" alt="" class="rounded-circle img-thumbnail">
                                    </div>
                                </div>
                                <div class="col">
                                    <div>
                                        <h4 class="mt-1 mb-1 text-white">{{ $user->name }}</h4>
                                        <p class="font-13 text-white-50">Role : 
                                             @foreach($rolesMap as $roleId => $roleName)
                                                @if($user->role_type == $roleId) 
                                                    {{ $roleName }}
                                                @endif
                                            @endforeach
                                        </p>

                                        <ul class="mb-0 list-inline text-light">
                                            <li class="list-inline-item me-3">
                                                <h5 class="mb-1 text-white">Email : {{ $user->email }}</h5>
                                            </li>
                                            
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div> <!-- end col-->

                        <div class="col-sm-4">
                            <div class="text-center mt-sm-0 mt-3 text-sm-end">
                                <button type="button" class="btn btn-light">
                                    <i class="mdi mdi-account-edit me-1"></i> Edit Profile
                                </button>
                                <a href="javascript:void(0);" data-toggle="modal" data-target="#edit-profile" id="editprofile" class="btn btn-sm btn-primary mr-1 editprofile"  data-id=""><i class="mdi mdi-pencil"></i></a>
                            </div>
                        </div> <!-- end col-->
                    </div> <!-- end row -->

                </div> <!-- end card-body/ profile-user-box-->
            </div>
        </div><!-- end col-->
    </div>
    <!-- end row-->
</div>
@endsection
@section('modal')
    <div id="edit-profile" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mt-2 mb-4">
                    <h5 class="modal-title"><span id="exampleModalLabel">Edit Profile</span>
                        <button type="button" id="btn-edit-close" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button></h5>
                    </div>
                     <form method="POST" action="{{ route('admin.profile.update') }}" class="pl-3 pr-3 edit-profile-form"  id="edit-profile-form">
                        @csrf
                        <input type="hidden" name="id" id="user-id" value="{{ $user->id }}">
                        
                        <div class="form-group">
                           <label for="email">Email<span class="text-danger">*</span></label>
                           <div class="form-input">
                                <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $user->email }}" autocomplete="email" autofocus>
                                <span class="error text-danger"></span>
                           </div>
                        </div>

                        <div class="form-group">
                           <label for="role">Role<span class="text-danger">*</span></label>
                           <div class="form-input">
                                <select name="role" id="role" class="form-control">
                                    @foreach($rolesMap as $roleId => $roleName)
                                        <option value="{{ $roleId }}" @if($user->role_type == $roleId) selected @endif>{{ $roleName }}</option>
                                    @endforeach
                                </select>     
                                <span class="error text-danger"></span>
                           </div>
                        </div>

                        <div class="form-group">
                            <label for="password">Password</label>
                            <div class="form-input">
                                <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" autocomplete="password" autofocus>
                                <span class="error text-danger"></span>
                           </div>                        
                            
                        </div>

                        <div class="form-group">
                            <label for="username">Confirm Password</label>
                            <div class="form-input">
                                <input id="password-confirm" type="password" class="form-control @error('confirm_password') is-invalid @enderror" name="password_confirmation" value="{{ old('confirm_password') }}" autocomplete="password_confirmation" autofocus>
                                <span class="error text-danger"></span>
                           </div>
                        </div>
                      
                        <div class="form-group text-center">
                            <button class="btn btn-primary" type="submit">Update</button>
                        </div>

                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@section('js')
<script src="{{$baseUrl}}js/custom.js"></script>
@endsection