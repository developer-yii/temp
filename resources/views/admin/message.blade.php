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
                        <li class="breadcrumb-item active"><a href="">Message list</a></li>
                    </ol>
                </div>
                <h4 class="page-title">Message list</h4>
            </div>
        </div>
    </div>
    
    <span id="success"></span>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title">Message List</h4>
                    <div class="tab-content">
                        <div class="tab-pane show active" id="basic-datatable-preview">
                            <table id="message_datatable" class="table dt-responsive nowrap w-100">
                                <thead>
                                    <tr>                                        
                                        <th>date</th>                                        
                                        <th>User Email</th>
                                        <th>Token</th>
                                        <th>Action</th>
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
@section('js')
<script src="{{$baseUrl}}js/custom.js"></script>
@endsection
