@php
    $userid = Auth::user()->id;
@endphp
@extends('layouts.app')
@section('css')
    <style>
        table.data-table tbody tr.unread-row {
            background-color: #f0f0f0 !important;
            font-weight: bold !important;
        }
    </style>
@endsection
@section('content')
    <div class="panel-body" id="imagelist">
        <h4><b>Delivery Messages</b></h4>
        <div class="tab-content">
            <div class="tab-pane show active table-responsive" id="basic-datatable-preview">
                <table id="data-table" class="table table-bordered data-table">
                    <thead>
                        <tr>
                            <th style="width:40%;">Message</th>
                        </tr>
                    </thead>
                </table>
            </div> <!-- end preview-->
        </div> <!-- end tab-content-->
    </div>
@endsection
@section('script')
    <script>

        var listUrl = "{{ route('delivery.list') }}";
        var markAsReadUrl = "{{ route('delivery.markRead') }}";
    </script>
    <script src="{{ asset('js/delivery-message.js') }}"></script>
@endsection
