$(document).ready(function(){
    $.ajaxSetup({
        headers : {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var table = $('#data-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: imagelist,
        columns: [
            {
                data: 'file_name',
                render: function(data, type, row, meta) {
                    return '<div class="file-name">' + data + '</div>';
                }
            },
            {
                data: 'image',  // Assuming this is where the path is stored
                render: function(data, type, row, meta) {
                    var imageTitle = row.file_name || 'File';
                    var fileExtension = data.split('.').pop().toLowerCase();  // Extract file extension

                    // Check file type and render accordingly
                    if(row.password == null){
                        if (['jpg', 'jpeg', 'png', 'gif', 'bmp', 'svg', 'jfif'].includes(fileExtension)) {
                            // Render thumbnail for image files
                            return '<a href="' + data + '" data-lightbox="image-set" data-title="' + imageTitle + '">' +
                                '<img src="' + data + '" class="img-thumbnail" width="100" alt="' + imageTitle + '" />' +
                                '</a>';
                        } else {
                            // Render "View File" button for non-image files
                            return '<a href="' + data + '" target="_blank" class="btn btn-primary btn-sm">' +
                                'View File' +
                                '</a>';
                        }
                    }else{
                        // var pwdProtectedUrl = '/image_action/' + row.short_link_token;
                        var pwdProtectedUrl = basePwdProtectedUrl.replace('__TOKEN__', row.short_link_token);
                        return '<a href="' + pwdProtectedUrl + '" target="_blank" class="btn btn-primary btn-sm">' +
                                'View File' +
                                '</a>';
                    }
                },
                orderable: false
            },
            {data : 'created_at_formatted',name:'created_at'},
            {data: 'action', name: 'action', orderable: false }
        ],
        order: [[2, 'desc']]
    });

    $(document).on('click', '.copy-url', function () {
        var copyUrl = $(this).data('url');
        copyToClipboard(copyUrl);
    });

    function copyToClipboard(text) {
        var dummy = document.createElement("textarea");
        document.body.appendChild(dummy);
        dummy.value = text;
        dummy.select();
        document.execCommand("copy");
        document.body.removeChild(dummy);
        toastr.success('Data copied!');
    }


    $('body').on('click', '.delete-image', function(e) {
        e.preventDefault();

        var id = $(this).attr('data-id');
        if (confirm('Are you sure you want to delete this image?'))
        {
            $.ajax({
                url: imagedelete + '?id=' + id,
                type: 'POST',
                dataType: 'json',
                success: function(result) {
                    toastr.success(result.message);
                    $('#data-table').DataTable().ajax.reload();
                },
                error: function(error) {
                    toastr.error('An error occurred while deleting the image.');
                }
            });
        }
    });


});