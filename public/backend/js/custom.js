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
            { data: 'first_message_date', name: 'first_message_date' },
            { data: 'user_emails', name: 'user_emails' },
            { data: 'conversation_token', name: 'conversation_token' },
            { data: 'action', name: 'action', orderable: false }
        ],
    });

    var messagetable = $('#user_datatable').DataTable({
        processing : true,
        serverSide : true,
        bStateSave: true,
        pageLength: 25,
        ajax : {
            type : "GET",
            url : userlist,
        },
        columns : [
            { data: 'id', name: 'id' },
            { data: 'email', name: 'email' },
            { data: 'approve', name: 'is_approve' },
            { data: 'action', name: 'action', orderable: false }
        ],
    });

    var messagetable = $('#note_datatable').DataTable({
        // responsive: true,

        processing : true,
        serverSide : true,
        bStateSave: true,
        pageLength: 25,
        // scrollX: true,
        // scrollY: 400,
        ajax : {
            type : "GET",
            url : notelist,
        },
        order: [0, 'desc'],
        columns : [
            { data: 'id', name: 'id'},
            {
                data: 'note',
                name: 'note',
                render: function (data, type, full, meta) {
                    // Replace newline characters with HTML line break tags
                    return data ? data.replace(/\n/g, '<br>') : '';
                    // var formattedData = data ? data.replace(/\n/g, '<br>') : '';
                    // return '<pre>' + formattedData + '</pre>';
                }
            },
            {
                data: 'message',
                name: 'message',
                render: function (data, type, full, meta) {
                    // Replace newline characters with HTML line break tags
                    return data ? data.replace(/\n/g, '<br>') : '';
                    // var formattedData = data ? data.replace(/\n/g, '<br>') : '';
                    // return '<pre>' + formattedData + '</pre>';
                }
            },
            { data: 'action', name: 'action', orderable: false}
        ],
    });

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

$('#edit-modal').on('hidden.bs.modal', function () {
      $('.error').html("");
      $('#edit-form')[0].reset();
  });
//user module end

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
// note module End