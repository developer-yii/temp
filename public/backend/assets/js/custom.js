$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$("#user").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(jQuery('#user')[0]);

    $.ajax({

        type: 'POST',
        url: saveuserUrl,
        data: formData,
        contentType: false,
        processData: false,

        success: function (data) {
            console.log(data);
            $("#success").append(
                "<li class='alert alert-success'>" + data.message + "</li>"
            );
            $("#email").val('');
            $("#password").val('');
            $("#name").val('');
            $("#user_datatable").DataTable().draw();
        },
        error: function (xhr, status, error) {

            $.each(xhr.responseJSON.errors, function (key, item) {
                // console.log(key)
                $("#errors-" + key).empty();
                $("#errors-" + key).append("<li class='alert alert-danger m-2'>" +
                    item + "</li>")
            });

        }

    });

});

$(document).on('click', '.edit-user', function (e) {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var id = $(this).attr('data-id');
    // console.log(id);
    $.ajax({

        type: "POST",
        url: userdetailsUrl,
        data: {
            id: id
        },
        dataType: 'json',
        success: function (data) {
            $("#edituser-modal").modal('show');
            $("#editname").val(data.name);
            $("#editemail").val(data.email);
            $("#id").val(data.id);
            $("select#Status").val(data.status).change();


        }
    });

})

$(document).on('click', '.delete-user', function (e) {
    var id = $(this).attr('data-id')
    if(confirm('Are you sure delete?')){
        $.ajax({
            type: "GET",
            url: userdeleteUrl,
            data: { id: id },
            dataType: 'json',
            success: function (data) {
                $("#delete").append(
                    "<div class='alert alert-success alert-dismissible fade show' role='alert'>" + data.message + "<button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button></div>"
                );
                $("#user_datatable").DataTable().draw();
            }
        })
    }
})


$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$("#edituser").submit(function (e) {
    e.preventDefault();
    var formData = new FormData(jQuery('#edituser')[0]);
    $.ajax({

        type: 'POST',
        url: edituserUrl,
        data: formData,
        contentType: false,
        processData: false,
        success: function (data) {
            $("#message").append(
                "<li class='alert alert-success'>" + data.message + "</li>"
            );
            $("#user_datatable").DataTable().draw();
        },
        error: function (xhr, status, error) {

            $.each(xhr.responseJSON.errors, function (key, item) {
                $("#error-" + key).empty();
                $("#error-" + key).append("<li class='alert alert-danger m-2'>" + item +
                    "</li>")
            });

        }

    });

});
// $(function () {

//     var table = $('#user_datatable').DataTable({
//         processing: true,
//         serverSide: true,
//         ajax: {
//           url: getUserUrl,
//           data: function (d) {
//                 d.status = $('#Status').val()

//             }
//         },
//         columns: [
//             {data: 'id', name: 'id'},
//             {data: 'name', name: 'name'},
//             {data: 'email', name: 'email'},
//             {data: 'status', name: 'status'},
//         ]
//     });

//     $('#Status').change(function(){
//         alert('hello')
//         $("#user_datatable").DataTable().draw();
//     });

//   });

$(document).ready(function () {

    $("#user_datatable").DataTable({

        "processing": true,
        "serverSide": true,
        "ajax": {
            url: getUserUrl,
            data: function (d) {
                d.status = $("#Status").val()
            }
        },
        "columns": [
            { "data": 'id' },
            { "data": 'name' },
            { "data": 'email' },
            {
                data: null,
                name: 'status',
                render: function(data, type, row) {
                    
                  return row.status == 'active' ? 'Active' : '<span class="text-danger">Deactive</span>';
                }
              },
            { "data": 'id',
               sortable: false,
               render: function(_,_, full){
                var userId = full['id'];
                if(userId){
                    var actions='<button class="btn btn-primary btn-sm edit-user m-1" type="button"data-id="'+ userId + '">Edit</button><a class="btn btn-primary btn-sm delete-user m-1"data-id="'+ userId + '">delete</a>';
                    return actions;
                    }
                    return '';
               } 
            },
           

        ]

    });
    
    $('#Status').change(function () {

        $("#user_datatable").DataTable().draw();
    });
});



// function callback(){
//     var dataString = '';
// $.ajax({
//     type: "POST",
//     headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//     },
//     url: "/update/user/list",
//     data: dataString,
//     dataType:"json",
//     cache: false,
//     sucesss: function(data){
//         var FinalResult = data.CallDetails
//         num_row = data.length;
//         console.log(data)
//     }
// });
// }