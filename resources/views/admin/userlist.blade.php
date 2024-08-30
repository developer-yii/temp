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
                        <li class="breadcrumb-item active">User list</li>
                    </ol>
                </div>
                <h4 class="page-title">User list</h4>
            </div>
        </div>
    </div>

    <span id="success"></span>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="tab-content">
                        <div class="tab-pane show active" id="basic-datatable-preview">
                            <table id="user_datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <td colspan="5"><button id="delete-selected" class="btn btn-danger">Delete Selected</button></td>
                                    </tr>
                                    <tr>
                                        <th><input type="checkbox" id="select-all"></th>
                                        <th>User Id</th>
                                        <th>User Email</th>
                                        <th>Register Date</th>
                                        <th width="15%">User Approve?</th>
                                        <th><center>Action</center></th>
                                    </tr>
                                </thead>
                            </table>

                        </div> <!-- end preview-->
                    </div> <!-- end tab-content-->
                </div> <!-- end card body-->
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->
</div>
@endsection
@section('modal')
    <div id="edit-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mt-2 mb-4">
                    <h5 class="modal-title"><span id="exampleModalLabel">Update User</span>
                        <button type="button" id="btn-edit-close" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button></h5>
                    </div>
                     <form method="POST" action="{{ route('admin.user.update') }}" class="pl-3 pr-3 edit-form"  id="edit-form">
                        @csrf
                        <input type="hidden" name="id" id="update-id">

                        <div class="form-group">
                           <label for="email">Email<span class="text-danger">*</span></label>
                           <div class="form-input">
                                <input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" autocomplete="email" autofocus>
                                <span class="error text-danger"></span>
                           </div>
                        </div>

                        <div class="form-group">
                           <label for="role_type">Role<span class="text-danger">*</span></label>
                           <div class="form-input">
                                <select name="role_type" id="role_type" class="form-control">
                                        <option value="1" > Admin </option>
                                        <option value="2" > User </option>
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
                            <button class="btn btn-danger" type="button" id="btn-cancel">Cancel</button>
                        </div>

                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@section('js')
<script>
    var deleteMultipleUsersUrl = "{{ route('admin.multiple-user.delete') }}";
</script>
<script src="{{$baseUrl}}js/custom.js"></script>
@endsection
