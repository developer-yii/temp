$(document).on('click', '.copy-btn', function () {
    $('.copy-btn').text('Copy').prop('disabled', false);

    var $btn = $(this);
    var url = $btn.data('url');

    function copiedSuccess() {
        $btn.text('Copied').prop('disabled', true);
        toastr.success('Link copied!');
    }

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(url).then(function () {
            copiedSuccess();
        });

    } else {
        var $temp = $('<input>');
        $('body').append($temp);
        $temp.val(url).select();
        document.execCommand('copy');
        $temp.remove();
        copiedSuccess();
    }
});