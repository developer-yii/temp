@php
    $baseUrl = asset('backend') . '/';
@endphp
@extends('layouts.admin-app')

@section('title', 'Admin | Delivery Message')
@section('css')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endsection
@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="page-title-box">
                    <div class="page-title-right">
                        <ol class="breadcrumb m-0">
                            <li class="breadcrumb-item"><a href="">Temp</a></li>
                            <li class="breadcrumb-item active">Delivery Message List</li>
                        </ol>
                    </div>
                    <h4 class="page-title">Delivery list</h4>
                </div>
            </div>
        </div>
        <style>
            .table-responsive {
                overflow: auto;
            }

            #delivery_datatable td {
                max-width: 400px;
                /* Adjust the max-width as needed */
                word-wrap: break-word;
                white-space: normal;
            }
        </style>
        <span id="success"></span>
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <button data-toggle="modal" data-target="#add-modal" class="btn btn-info mb-2">Add New</button>
                        <div class="tab-content">
                            <div class="tab-pane show active" id="basic-datatable-preview">
                                <div class="table-responsive">
                                    <table id="delivery_datatable" class="table w-100">
                                        <thead>
                                            <tr>
                                                <th width="10%">Id</th>
                                                <th width="10%">Type</th>
                                                <th>Delivery Message</th>
                                                <th>User List</th>
                                                <th width="10%">
                                                    <center>Action</center>
                                                </th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
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
    <div id="add-modal" class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <div class="text-center mt-2 mb-4">
                        <h5 class="modal-title"><span id="exampleModalLabel">Add Delivery</span>
                            <button type="button" id="btn-edit-close" class="close" data-dismiss="modal"
                                aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </h5>
                    </div>
                    <form method="POST" action="{{ route('admin.delivery.addupdate') }}" class="pl-3 pr-3 add-form"
                        id="add-form">
                        @csrf
                        <input type="hidden" name="id" id="update-id">

                        <div class="form-group">
                            <label>Type<span class="text-danger">*</span></label>
                            <div class="form-input">
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="type_delivery" name="type" value="delivery"
                                        class="custom-control-input" checked>
                                    <label class="custom-control-label" for="type_delivery">Delivery</label>
                                </div>
                                <div class="custom-control custom-radio custom-control-inline">
                                    <input type="radio" id="type_notification" name="type" value="notification"
                                        class="custom-control-input">
                                    <label class="custom-control-label" for="type_notification">Notification</label>
                                </div>
                                <span class="error text-danger"></span>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="delivery_message">Message<span class="text-danger">*</span></label>
                            <div class="form-input">
                                <textarea id="delivery_message" name="delivery_message"
                                    class="form-control @error('delivery_message') is-invalid @enderror" rows="5"></textarea>
                                <span class="error text-danger"></span>
                            </div>
                        </div>

                        <div class="form-group" id="image-upload-wrapper" style="display:none;">
                            <label for="images">Images</label>
                            <div class="form-input">
                                <input type="file" id="images" name="images[]" class="form-control" multiple>
                                <span class="error text-danger"></span>
                            </div>
                            <div id="existing-images" class="mt-2 row"></div>
                        </div>

                        <div class="form-group">
                            <label for="email">Users<span class="text-danger">*</span></label>
                            <div class="form-input">
                                <select id="email" name="email[]"
                                    class="form-control @error('email') is-invalid @enderror" multiple>
                                </select>
                                <span class="error text-danger"></span>
                            </div>
                        </div>

                        <div class="form-group text-center">
                            <button class="btn btn-primary" type="submit">Save</button>
                            <button class="btn btn-danger" type="button" id="btn-cancel">Cancel</button>
                        </div>
                    </form>

                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->
@endsection
@section('js')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // var deleteMultipleNotesUrl = "{{ route('admin.multiple-notes.delete') }}";
        var deliveryMsglistUrl = "{{ route('admin.delivery.list') }}";
        var getUserListUrl = "{{ route('admin.delivery.users.list') }}";
        var getDetailsUrl = "{{ route('admin.delivery.details') }}";
        var addUpdateDeliveryMsgUrl = "{{ route('admin.delivery.addupdate') }}";
        var deleteMsgUrl = "{{ route('admin.delivery.delete') }}";
        var deleteImageMsgUrl = "{{ route('admin.delivery.image.delete') }}";
    </script>
    <script src="{{ $baseUrl }}js/delivery.js"></script>
@endsection
