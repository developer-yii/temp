$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('body').on('click', '#register_setting', function (e) {
    e.preventDefault();
    var checkbox = $(this);
    if (checkbox.prop("checked") == true) {
        var status = 1;
    } else {
        var status = 0;
    }

    var id = checkbox.data('id');
    var param_name = checkbox.data('param');

    $.ajax({
        url: settingUpdateUrl,
        method: 'POST',
        data: {
            id: id,
            param_name: param_name,
            status: status,
        },
        dataType: 'json',
        success: function (result) {
            if (result.status) {
                toastr.success(result.message);
                checkbox.prop('disabled', true);
                setTimeout(function() {
                    window.location.reload();
                }, 2000);
            } else {
                toastr.error(result.message);
            }
        },
        error: function (error) {
            checkbox.prop('disabled', false);
            toastr.error('An error occurred. Please try again.');
        }
    })
});
