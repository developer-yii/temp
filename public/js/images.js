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
            {data: 'image_name'},
            {data : 'created_at_formatted',name:'created_at'},  
            {data: 'action', name: 'action', orderable: false }
            ]
    });

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