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
        columns: [
            { data: 'note' },
            { data: 'message', name: 'message' },
            { data: 'action', name: 'action', orderable: false }
        ]
    });

    $('body').on('click', '.delete-note', function (e) {
        e.preventDefault();

        var id = $(this).attr('data-id');
        if (confirm('Are you sure you want to delete this image?')) {
            $.ajax({
                url: notedelete + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function (result) {
                    toastr.success(result.message);
                    $('#data-table').DataTable().ajax.reload();
                },
                error: function (error) {
                    toastr.error('An error occurred while deleting the image.');
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
            type: "get",
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
                $('#notesModal').find('button[type="submit"]').html("Update");
                $('#notesModal').find('#exampleModalLabel').html("Edit Note");
            }
        });
    });

    $('#notesModal').on('hidden.bs.modal', function () {
        $('.error').html("");
        $('#message-notes')[0].reset();
        $('#note_id').val("");
        $('#notesModal').find('button[type="submit"]').html("Save");
        $('#notesModal').find('#exampleModalLabel').html("Add Note");

    });
});