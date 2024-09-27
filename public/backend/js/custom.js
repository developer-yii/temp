$(document).ready(function()
{
   $.ajaxSetup({
        headers : {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var messagetable = $('#message_datatable').DataTable({
        processing : true,
        serverSide : true,
        bStateSave: true,
        pageLength: 25,
        ajax : {
            type : "GET",
            url : adminmessagelist,
        },
        columns : [
            {
                data: 'id', name: 'id', render: function (data, type, row, meta) {
                    return '<input type="checkbox" class="message-checkbox" value="' + data + '">';
                },
                orderable: false,

            },
            { data: 'created_at', name: 'created_at' },
            { data: 'user.email', name: 'user.email' },
            { data: 'conversation_token', name: 'conversation_token' },
            { data: 'action', name: 'action', orderable: false }
        ],
    });

    var usertable = $('#user_datatable').DataTable({
        processing : true,
        serverSide : true,
        bStateSave: true,
        pageLength: 25,
        ajax : {
            type : "GET",
            url : userlist,
        },
        columns : [
            {
                data: 'id',
                name: 'id',
                render: function (data, type, row, meta) {
                    return '<input type="checkbox" class="user-checkbox" value="' + data + '">';
                },
                orderable: false,

            },
            { data: 'id', name: 'id' },
            { data: 'email', name: 'email' },
            { data: 'created_at', name: 'created_at' },
            { data: 'approve', name: 'is_approve' },
            // { data: 'blockStatus', name: 'blockStatus' },
            {
                data: null,
                name: 'is_block',
                render: function (data, type, full, meta) {
                    let switchId = `switch_${data.id}`;
                    let html = `<div>
                                    <input type="checkbox" class="block-user" id="${switchId}" ${data.is_block ? 'checked' : ''} data-switch="success" data-id="${data.id}"/>
                                    <label for="${switchId}" data-on-label="Yes" data-off-label="No" class="mb-0 d-block"></label>
                                </div>`;
                    return html;
                },
                orderable: false,
                searchable: false,
            },
            { data: 'action', name: 'action', orderable: false }
        ],
    });

    var notetable = $('#note_datatable').DataTable({
        // responsive: true,

        processing : true,
        // serverSide : true,
        // bStateSave: true,
        pageLength: 25,
        ajax : {
            type : "GET",
            url : notelist,
        },
        order: [0, 'desc'],
        columns : [
            { data: 'id', name: 'id', visible: false},
            {
                data: 'id',
                name: 'id',
                render: function (data, type, row, meta) {
                    return '<input type="checkbox" class="note-checkbox" value="' + data + '">';
                },
                orderable: false,

            },
            { data: 'user.email', name: 'user.email'},
            {
                data: 'note',
                name: 'note',
                // render: function (data, type, full, meta) {
                //     return data ? data.replace(/\n/g, '<br>') : '';
                // }
                render: function (data, type, full, meta) {
                    return renderContentWithReadMoreAndLess(data, 'note-' + full.id);
                }
            },
            {
                data: 'message',
                name: 'message',
                // render: function (data, type, full, meta) {
                //     return data ? data.replace(/\n/g, '<br>') : '';
                // }
                render: function (data, type, full, meta) {
                    return renderContentWithReadMoreAndLess(data, 'message-' + full.id, full.sender_email);
                }
            },
            { data: 'action', name: 'action', orderable: false}
        ],
    });

    function renderContentWithReadMoreAndLess(content, elementId, senderName = null) {
        var shortText = content ? content.replace(/\n/g, '<br>').substring(0, 100) : '';
        var fullText = content ? content.replace(/\n/g, '<br>') : '';
        var sentBy = senderName ? '<div style="text-align: right; font-style: italic; margin-top: 5px; color:blue;">Sent By: ' + senderName + '</div>' : '';

        if (content && content.length > 100) {
            return '<span class="short-text" id="' + elementId + '-short">' + shortText + '... ' +
                   '<a href="#" class="read-more" data-id="' + elementId + '">Read More</a>' + sentBy + '</span>' +
                   '<span class="full-text" id="' + elementId + '" style="display:none;">' + fullText +
                   ' <a href="#" class="read-less" data-id="' + elementId + '">Read Less</a>' + sentBy + '</span>';
        }
        return fullText;  // Return full content if less than 100 characters
    }
});

$(document).on('change', '.block-user', function() {
    let isChecked = $(this).is(':checked');  // Get the new status (checked or not)
    let id = $(this).data('id');  // Get the data-id of the checkbox

    // Make an AJAX request to update the status in the database
    $.ajax({
        url: bolckUserUrl,  // Replace with your server endpoint
        method: 'POST',
        data: {
            id: id,
            is_block: isChecked ? 1 : 0,  // Send the new status
        },
        dataType: 'json',
        success: function(response) {
            if (response.status) {
                toastr.success(response.message);
            } else {
                toastr.error(response.message);
            }
        },
        error: function(xhr, status, error) {
            alert('An error occurred: ' + error);
        }
    });
});


$('body').on('click', '.read-more', function (e) {
    e.preventDefault();  // Prevent the default link click behavior

    var elementId = $(this).data('id');  // Get the ID of the element to display
    $('#' + elementId + '-short').hide();  // Hide the short text with "Read More"
    $('#' + elementId).show();  // Show the full text with "Read Less"
});

$('body').on('click', '.read-less', function (e) {
    e.preventDefault();  // Prevent the default link click behavior

    var elementId = $(this).data('id');  // Get the ID of the element to hide
    $('#' + elementId).hide();  // Hide the full text with "Read Less"
    $('#' + elementId + '-short').show();  // Show the short text with "Read More"
});

//user module start
$('body').on('click','.edit-user',function(){

    var id = $(this).attr('data-id');
    $('#update-id').val(id);
    $.ajax({
        url: getuser+'?id='+id,
        type: 'GET',
        dataType: 'json',
        success: function(result) {
            $('.error').html("");
            $('#edit-form')[0].reset();
            $.each(result.data.user, function(key) {
                if($('#edit-form').find('#'+key).length)
                {
                    if (key === 'role_type')
                    {
                        $('#edit-form').find('#' + key).val(result.data.user[key]).change();;
                    }
                    else
                    {
                        $('#edit-form').find('#'+key).val(result.data.user[key]);
                    }
                }
            });
        }
    });
});

$(document).on('submit','.edit-form', function(e){

    event.preventDefault();
    var $this = $(this);
    var dataString = new FormData($('#edit-form')[0]);

        $.ajax({
        url: userupdate,
        type: 'POST',
        data: dataString,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $($this).find('button[type="submit"]').prop('disabled', true);
        },
        success: function(result)
        {
            $($this).find('button[type="submit"]').prop('disabled', false);
            if (result.status == true)
            {
                toastr.success(result.message);
                $('#btn-edit-close').click();
                $('#user_datatable').DataTable().ajax.reload();
                $('.error').html("");
            }

            else if(result.status == false && result.validationError == true)
            {

                toastr.error(result.message);
            }
            else
            {
                first_input = "";
                $('.error').html("");
                $.each(result.message, function(key)
                {
                    if(first_input=="") first_input=key;

                   $($this).find('#'+key).closest('.form-input').find('.error').html(result.message[key]);
                });

                $('#edit-form').find("#"+first_input).focus();

            }
        },
        error: function(error) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            alert('Something want wrong!', 'error');
            location.reload();
        }
    });
});

$('#btn-cancel').on('click', function()
{
    var form = $('#edit-form')[0];
    form.reset();
    $('#edit-modal').modal('hide');
});

$('#select-all').on('click', function() {
    var isChecked = $(this).prop('checked');
    $('.user-checkbox').prop('checked', isChecked);
});

$('body').on('click', '.delete-user', function(e)
{
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
            // User confirmed the action, proceed with the deletion
            $.ajax({
                url: userdelete + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#user_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the user.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });
});

$('body').on('change','.approval_status', function(event) {
    var status = $(this).val();
    var id = $(this).attr('data-id');

    $.ajax({
        url: userapproval,
        type: 'POST',
        dataType: 'json',
        data: { 'id': id, 'status': status },
        success: function(result) {
            toastr.success(result.message);
            $('#user_datatable').DataTable().ajax.reload();
        }
    });
});

$('#delete-selected').on('click', function () {
    // Collect IDs of selected rows
    var ids = [];
    $('.user-checkbox:checked').each(function () {
        ids.push($(this).val());
    });

    // If there are no selected rows, return
    if (ids.length === 0) {
        toastr.error('No user selected');
        return;
    }

    Swal.fire({
        title: 'Are you sure want to delete selected record?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed the action, proceed with the deletion
            $.ajax({
                url: deleteMultipleUsersUrl,
                data: { ids: ids },
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#user_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the user.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });

});

$('#edit-modal').on('hidden.bs.modal', function () {
      $('.error').html("");
      $('#edit-form')[0].reset();
  });
//user module end

// message module start
$('#select-all-message').on('click', function() {
    var isChecked = $(this).prop('checked');
    $('.message-checkbox').prop('checked', isChecked);
});
$('#delete-selected-messages').on('click', function () {
    // Collect IDs of selected rows
    var ids = [];
    $('.message-checkbox:checked').each(function () {
        ids.push($(this).val());
    });

    // If there are no selected rows, return
    if (ids.length === 0) {
        toastr.error('No Message selected');
        return;
    }

    Swal.fire({
        title: 'Are you sure want to delete selected Conversations?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed the action, proceed with the deletion
            $.ajax({
                url: deleteMultipleMessageUrl,
                data: { ids: ids },
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#message_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting conversations.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });

});
$('body').on('click', '.delete-conversation', function(e)
{
    e.preventDefault();
    var id = $(this).attr('data-id');

    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure want to delete the conversation?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed the action, proceed with the deletion
            $.ajax({
                url: deleteConversationUrl + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#message_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the conversation.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });
});

// message module end

//admin profile start
$(document).on('submit','.edit-profile-form', function(e)
{
    event.preventDefault();
    var $this = $(this);
    var dataString = new FormData($('#edit-profile-form')[0]);

        $.ajax({
        url: profileupdate,
        type: 'POST',
        data: dataString,
        processData: false,
        contentType: false,
        beforeSend: function() {
            $($this).find('button[type="submit"]').prop('disabled', true);
        },
        success: function(result)
        {
            $($this).find('button[type="submit"]').prop('disabled', false);
            if (result.status == true)
            {
                toastr.success(result.message);
                $('#btn-edit-close').click();
                $('.error').html("");
                location.reload();
            }

            else if(result.status == false && result.validationError == true)
            {
                toastr.error(result.message);
            }
            else
            {
                first_input = "";
                $('.error').html("");
                $.each(result.message, function(key)
                {
                    if(first_input=="") first_input=key;
                   $($this).find('#'+key).closest('.form-input').find('.error').html(result.message[key]);
                });

                $('#edit-profile-form').find("#"+first_input).focus();

            }
        },
        error: function(error) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            alert('Something want wrong!', 'error');
            location.reload();
        }
    });
});
$('#btn-edit-cancel').on('click', function()
{
    var form = $('#edit-profile-form')[0];
    form.reset();
    $('#edit-profile').modal('hide');
});
//admin profile end

// note module start
$('body').on('click', '.delete-note', function(e)
{
    e.preventDefault();
    var id = $(this).attr('data-id');

    // Show SweetAlert confirmation dialog
    Swal.fire({
        title: 'Are you sure want to delete note?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            // Note confirmed the action, proceed with the deletion
            $.ajax({
                url: notedelete + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#note_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the note.');
                }
            });
        } else {
            // Note cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });
});

$('#select-all-notes').on('click', function() {
    var isChecked = $(this).prop('checked');
    $('.note-checkbox').prop('checked', isChecked);
});
$('#delete-selected-notes').on('click', function () {
    // Collect IDs of selected rows
    var ids = [];
    $('.note-checkbox:checked').each(function () {
        ids.push($(this).val());
    });

    // If there are no selected rows, return
    if (ids.length === 0) {
        toastr.error('No notes selected');
        return;
    }

    Swal.fire({
        title: 'Are you sure want to delete selected notes?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ok, got it!',
        cancelButtonText: 'Nope, cancel it.'
    }).then((result) => {
        if (result.isConfirmed) {
            // User confirmed the action, proceed with the deletion
            $.ajax({
                url: deleteMultipleNotesUrl,
                data: { ids: ids },
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#note_datatable').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the notes.');
                }
            });
        } else {
            // User cancelled the action, do nothing
            toastr.info('Deletion cancelled.');
        }
    });
});
// note module End