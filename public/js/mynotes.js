$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var table = $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: notelist,
        order: [0, 'desc'],
        columns: [
            { data: 'pin_note', name: 'pin_note', visible: false },
            {
                data: 'note',
                name: 'note',
                // render: function (data, type, full, meta) {
                //     var formattedData = data ? data.replace(/\n/g, '<br>') : '';
                //     return '<pre>' + formattedData + '</pre>';
                // }
                render: function (data, type, full, meta) {
                    return renderContentWithReadMoreAndLess(data, 'note-' + full.id);
                }
            },
            {
                data: 'message',
                name: 'message',
                // render: function (data, type, full, meta) {
                //     var formattedData = data ? data.replace(/\n/g, '<br>') : '';
                //     return '<pre>' + formattedData + '</pre>';
                // }
                render: function (data, type, full, meta) {
                    return renderContentWithReadMoreAndLess(data, 'message-' + full.id, full.sender_email);
                }
            },
            { data: 'action', name: 'action', orderable: false }
        ]
    });

    function renderContentWithReadMoreAndLess(content, elementId, senderName = null) {
        var shortText = content ? content.replace(/\n/g, '<br>').substring(0, 100) : '';
        var fullText = content ? content.replace(/\n/g, '<br>') : '';
        var sentBy = senderName ? '<div style="text-align: right; font-style: italic; margin-top: 5px; color:blue;">Sent By: ' + senderName + '</div>' : '';

        if (content && content.length > 100) {
            return '<div class="file-name"><span class="short-text" id="' + elementId + '-short">' + shortText + '... ' +
                '<a href="#" class="read-more" data-id="' + elementId + '" style="font-weight: 600;">Read More</a>' + sentBy + '</span>' +
                '<span class="full-text" id="' + elementId + '" style="display:none;">' + fullText +
                ' <a href="#" class="read-less" data-id="' + elementId + '" style="font-weight: 600;">Read Less</a>' + sentBy + '</span></div>';
        }
        return fullText + sentBy;  // Return full content if less than 100 characters
    }

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

    $('body').on('click', '.delete-note', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-id');
        if (confirm('Are you sure you want to delete this note?')) {
            $.ajax({
                url: notedelete + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    toastr.success(result.message);
                    $('#data-table').DataTable().ajax.reload();
                },
                error: function (error) {
                    toastr.error('An error occurred while deleting the note.');
                }
            });
        }
    });

    $('#message-notes').submit(function (e) {
        e.preventDefault();
        $('.error').html("");

        var $this = $(this);
        var formData = $(this).serialize();

        $.ajax({
            url: notesUrl,
            type: "post",
            data: formData,
            success: function (response) {

                $($this).find('button[type="submit"]').prop('disabled', false);
                if (response.status == true) {
                    $this[0].reset();
                    toastr.success(response.message);
                    $('#notesModal').modal('hide');
                    $('#data-table').DataTable().ajax.reload();
                } else {

                    first_input = "";
                    $.each(response.errors, function (key) {
                        if (first_input == "") first_input = key;
                        $('#' + key).closest('.form-input').find('.error').html(
                            response.errors[key]);
                    });
                    $('#message-notes').find("#" + first_input).focus();

                }
            },
            error: function (xhr, status, error) {
                alert('Something went wrong!', 'error');
            }
        });
    });

    $('body').on('click','.edit-note',function()
    {
        var id = $(this).data('id');
        $(".note_id").val(id);
        $.ajax({
            type : "POST",
            url : getnote,
            data : {id : id},
            dataType : 'json',
            success : function(data)
            {
                $('#notes').val(data.note);
                var formattedMessage = data.message ? '<pre>' + data.message.replace(/\n/g, '<br>') + '</pre>' : '';
                // $('#message').html(formattedMessage);
                $('#messages').val(data.message);
                $('#messagelabel').attr('style', 'display:block');
                $('#notesModal').find('button[type="submit"]').html("Update");
                $('#notesModal').find('#exampleModalLabel').html("Edit Note");
            }
        });
    });

    $('#notesModal').on('hidden.bs.modal', function () {
        $('.error').html("");
        $('#message-notes')[0].reset();
        $('#note_id').val("");
        $('#message').html("");
        $('#messagelabel').attr('style', 'display:none');
        $('#notesModal').find('button[type="submit"]').html("Save");
        $('#notesModal').find('#exampleModalLabel').html("Add Note");

    });

    $('body').on('click', '.pin-note', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-id');

            $.ajax({
                url: pinnote + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    toastr.success(result.message);
                    $('#data-table').DataTable().ajax.reload();
                },
                error: function (error) {
                    toastr.error('An error occurred while pin the note.');
                }
            });

    });
});