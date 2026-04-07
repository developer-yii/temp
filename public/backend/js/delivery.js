$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#email').select2({
        placeholder: "Select users by email",
        ajax: {
            url: getUserListUrl,
            dataType: 'json',
            delay: 250,
            data: function (params) {
                return { q: params.term };
            },
            processResults: function (data) {
                var selected = $('#email').val() || [];
                var filtered = data.filter(function (item) {
                    return selected.indexOf(item.id.toString()) === -1;
                });

                // return in Select2 format
                var results = $.map(filtered, function (item) {
                    var displayText = item.nickname ? item.nickname + ' (' + item.email + ')' : item.email;
                    return { id: item.id, text: displayText, email: item.email, nickname: item.nickname };
                });

                return { results: results };
            },
            cache: true
        }
    });

    var deliveryDatatable = $('#delivery_datatable').DataTable({
        processing: true,
        serverSide: true,
        bStateSave: true,
        pageLength: 25,
        ajax: {
            type: "GET",
            url: deliveryMsglistUrl,
        },
        columns: [
            { data: 'id', name: 'id' },
            { data: 'type', name: 'type' },
            { data: 'delivery_message', name: 'delivery_message' },
            { data: 'user_list', name: 'user_list', orderable: false },
            { data: 'action', name: 'action', orderable: false }
        ],
    });
});

$(document).on('submit', '.add-form', function (e) {

    event.preventDefault();
    var $this = $(this);
    var dataString = new FormData($('#add-form')[0]);

    $.ajax({
        url: addUpdateDeliveryMsgUrl,
        type: 'POST',
        data: dataString,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $($this).find('button[type="submit"]').prop('disabled', true);
        },
        success: function (result) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            if (result.status == true) {
                toastr.success(result.message);
                $('#btn-edit-close').click();
                $('#delivery_datatable').DataTable().ajax.reload();
                $('.error').html("");
            } else if (result.status == false && result.validationError == true) {
                toastr.error(result.message);
            } else {
                first_input = "";
                $('.error').html("");
                $.each(result.message, function (key) {
                    if (first_input == "") first_input = key;

                    var fieldId = key.indexOf('.') !== -1 ? key.split('.')[0] : key;
                    var errorHtml = $($this).find('#' + fieldId).closest('.form-input').find('.error').html();
                    var newError = Array.isArray(result.message[key]) ? result.message[key][0] : result.message[key];
                    if (errorHtml != "") {
                        if (errorHtml.indexOf(newError) === -1) {
                            $($this).find('#' + fieldId).closest('.form-input').find('.error').html(errorHtml + "<br>" + newError);
                        }
                    } else {
                        $($this).find('#' + fieldId).closest('.form-input').find('.error').html(newError);
                    }
                });

                var focusId = first_input.indexOf('.') !== -1 ? first_input.split('.')[0] : first_input;
                $('#add-form').find("#" + focusId).focus();
            }
        },
        error: function (error) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            alert('Something want wrong!', 'error');
            location.reload();
        }
    });
});

$('body').on('click', '.edit-message', function () {

    var id = $(this).attr('data-id');
    $.ajax({
        url: getDetailsUrl + '?id=' + id,
        type: 'GET',
        dataType: 'json',
        success: function (result) {
            $('.error').html("");
            $('#add-form')[0].reset();

            if (result.status && result.data) {
                $('#update-id').val(id);
                let data = result.data;
                $('input[name="type"][value="' + data.type + '"]').prop('checked', true);
                $('#delivery_message').val(data.delivery_message);

                let invited = data.invited_users || [];
                if (!Array.isArray(invited)) {
                    invited = data.invitedUsers;
                }

                $('#email').empty();
                invited.forEach(function (user) {
                    var displayText = user.nickname ? user.nickname + ' (' + user.email + ')' : user.email;
                    var option = new Option(displayText, user.id, true, true);
                    $('#email').append(option).trigger('change');
                });

                if (data.type === 'notification') {
                    $('#file-upload-wrapper').show();
                    // Handle existing files
                    $('#existing-files').empty();
                    if (data.images && data.images.length > 0) {
                        data.images.forEach(function (image) {
                            let imageUrl = storageUrl + 'delivery_images/' + image.image_path;
                            let isPdf = image.image_path.toLowerCase().endsWith('.pdf');
                            let mediaHtml = isPdf 
                                ? `<div style="height: 100px; display: flex; align-items: center; justify-content: center; background: #f8f9fa;"><i class="mdi mdi-file-pdf text-danger" style="font-size: 40px;"></i></div><span style="font-size: 12px;">PDF File</span>`
                                : `<img src="${imageUrl}" class="img-fluid" style="height: 100px; object-fit: cover;">`;

                            let html = `
                                <div class="col-md-3 mb-2 text-center" id="file-${image.id}">
                                    <div class="card p-1">
                                        <a href="${imageUrl}" target="_blank">
                                            ${mediaHtml}
                                        </a>
                                        <button type="button" class="btn btn-sm btn-danger mt-1 delete-file-btn" data-id="${image.id}">Delete</button>
                                    </div>
                                </div>
                            `;
                            $('#existing-files').append(html);
                        });
                    }
                } else {
                    $('#file-upload-wrapper').hide();
                    $('#existing-files').empty();
                }

                $('#add-modal').modal('show');
            }
        }
    });
});

$('body').on('click', '.delete-file-btn', function (e) {
    e.preventDefault();
    let imageId = $(this).data('id');

    Swal.fire({
        title: 'Delete File?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: deleteImageMsgUrl,
                type: 'POST',
                data: { id: imageId },
                success: function (response) {
                    if (response.status) {
                        $('#file-' + imageId).remove();
                        toastr.success(response.message);
                    } else {
                        toastr.error(response.message);
                    }
                },
                error: function () {
                    toastr.error('Error deleting file.');
                }
            });
        }
    });
});

$('body').on('click', '.delete-message', function (e) {
    e.preventDefault();
    var id = $(this).attr('data-id');

    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure want to delete?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: deleteMsgUrl + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    toastr.success(result.message);
                    $('#delivery_datatable').DataTable().ajax.reload();
                },
                error: function (error) {
                    toastr.error('An error occurred while deleting the user.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });
});

function resetAddForm() {
    $('#add-form')[0].reset();
    $('#update-id').val('');
    $('#email').val(null).trigger('change');
    $('.error').html('');
    $('#file-upload-wrapper').hide();
    $('#existing-files').empty();
}

$('#btn-cancel').on('click', function () {
    resetAddForm();
    $('#add-modal').modal('hide');
});

$('#add-modal').on('hidden.bs.modal', function () {
    resetAddForm();
});

$('input[name="type"]').on('change', function () {
    if ($(this).val() === 'notification') {
        $('#file-upload-wrapper').show();
    } else {
        $('#file-upload-wrapper').hide();
    }
});
