@php
    $userid = Auth::user()->id;    
@endphp
@extends('layouts.app')

@section('content')
<div class="panel-body" id="imagelist">  
    <div class="alert alert-default">
        <h4><b>Image List</b></h4>    
    </div> 

    <div class="tab-content">
        <div class="tab-pane show active" id="basic-datatable-preview">
            <table id="data-table" class="table table-bordered data-table">
                <thead>
                    <tr>                                
                        <th>Name</th>
                        <th>Created Date</th>
                        <th>Action</th>
                    </tr>                    
                </thead>
            </table>
        </div> <!-- end preview-->
    </div> <!-- end tab-content-->                       
</div>
@endsection
@section('script')
<script>
    var imagelist = "{{ route('image.list') }}";    
    var imagedelete = "{{ route('image.delete') }}";    
</script>
<script src="{{ asset('js/images.js') }}"></script>

@endsection

