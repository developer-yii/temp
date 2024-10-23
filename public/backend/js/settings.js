$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$('body').on('click', '#register_setting', function (e) {
    e.preventDefault();
    var checkbox = $(this);

    var originalState = checkbox.prop("checked");
    var status = checkbox.prop("checked") ? 1 : 0;

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
                checkbox.prop('checked', status === 1);
                toastr.success(result.message);
            } else {
                checkbox.prop('checked', originalState);
                toastr.error(result.message);
            }
        },
        error: function (error) {
            checkbox.prop('checked', originalState);
            toastr.error('An error occurred. Please try again.');
        }
    })
});
