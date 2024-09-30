@php
    $userid = Auth::user()->id;
    $baseUrl = asset('backend')."/";
@endphp

@extends('layouts.admin-app')

@section('title','Admin | Settings')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Temp</a></li>
                        <li class="breadcrumb-item active">Settings</li>
                    </ol>
                </div>
                <h4 class="page-title">Settings</h4>
            </div>
        </div>
    </div>

    <span id="success"></span>

    <div class="row">
      <div class="col-12">
          <div class="card">
              <div class="card-body">
               <form class="form" id="edit-setting" method="post">
                  @csrf
                  <div class="form-group row">
                     <div class="col-2">
                        <label for="register">User Register</label>
                     </div>

                     <div class="col-4">
                        <input type="checkbox" id="register_setting" data-switch="bool" {{ $setting->param_value == 0 ? '' : 'checked' }} data-id="{{ $setting->id }}" data-param="{{ $setting->param_name }}"/>
                        <label for="register_setting" data-on-label="On" data-off-label="Off"></label>
                     </div>
                  </div>
              </form>
              </div> <!-- end card-body -->
          </div> <!-- end card-->
      </div> <!-- end col -->
  </div>
    <!-- end row-->
</div>
@endsection
@section('js')
<script>
    var settingUpdateUrl = "{{ route('admin.setting.update') }}";
</script>
<script src="{{$baseUrl}}js/settings.js"></script>
@endsection



