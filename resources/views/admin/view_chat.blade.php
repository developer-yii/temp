@php
    $userid = Auth::user()->id;
    $baseUrl = asset('backend')."/";
@endphp

@extends('layouts.admin-app')

@section('title','Admin | Chat')
@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('admin.home') }}">Temp</a></li>
                        <li class="breadcrumb-item active">Chatboard</li>
                    </ol>
                </div>
                <h4 class="page-title">Chatboard</h4>
            </div>
        </div>
    </div>

    <span id="success"></span>
    <div class="row">
        <div class="col-12">
            <div class="card">
               <div class="card">
                    <div class="card-body">
                      <div class="modal-body append-modal-body">
                         <div class="col-md-12">
                            <div class="row">
                               <div class="col-md-12  text-left">
                                  <div class="row">
                                     <div class="col-md-12"><label>Email </label>:
                                       {{ $uniqueUserEmails }}
                                    </div>
                                  </div>
                               </div>

                            </div>
                         </div>
                      </div>
                      <div class="col-xl-12 col-lg-12 order-lg-2 order-xl-1">
                        <div class="card">
                           <div class="card-body">

                            <ul class="conversation-list" data-simplebar style="max-height: 537px">
                              @foreach($messages as $data)
                              <li class="clearfix" >
                                 <div class="chat-avatar">
                                    <img src="{{ $baseUrl}}assets/images/blank.png" alt="user-image" class="rounded-circle">
                                 </div>
                                 <div class="conversation-text conversation-text-W-100">
                                    <div class="ctext-wrap message-format">
                                       <i style="font-size: 15px;">{{ $data->user->email}}</i>
                                          <pre style="white-space: pre-wrap; background-color: #f1f3fa !important;">{{ $data->message }}</pre>
                                       <p style="color: #927c8f">{{ $data->created_at}}</p>
                                    </div>
                                 </div>
                              </li>
                              @endforeach
                            </ul>

                            </div>
                            <!-- end card-body -->
                         </div>
                         <!-- end card -->
                      </div>
                    </div>
                </div>
            </div> <!-- end card -->
        </div><!-- end col-->
    </div>
    <!-- end row-->
</div>
@endsection


