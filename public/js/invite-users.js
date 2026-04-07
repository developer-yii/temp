$('#invite-button').on('click', function () {
    let userId = $(this).data('user-id');
    let conversationId = $(this).data('conversation-id');
    let creatorId = $(this).data('creator');

    $('#inviteModal #user-id').val(userId);
    $('#inviteModal #conversation-id').val(conversationId);

    $.ajax({
        type: 'GET',
        url: getInviteUserUrl,
        data: { user_id: userId, conversation_id: conversationId },
        success: function (result) {
            if (result.status == true) {
                $('#inviteModal input[type="email"]').val('').prop('disabled', false);

                if (result.firstVisitor) {
                    $("#firstVisitorRow").show();
                    $("#firstVisitorEmail").text(result.firstVisitor.user.email);
                } else {
                    $("#firstVisitorRow").hide();
                }

                result.inviteUser.forEach(function (invite, index) {
                    if (invite.user && invite.user.email) {
                        let input = $('#email_' + (index + 1));
                        let hidden = $('#user_id_' + (index + 1));
                        
                        // Show nickname if available, otherwise email
                        if (invite.user.nickname) {
                            input.val(invite.user.nickname);
                        } else {
                            input.val(invite.user.email);
                        }

                        if (invite.has_message || invite.is_creator || creatorId != loggedInUser) {
                            input.prop('disabled', true);
                            hidden.prop('disabled', true);
                        } else {
                            hidden.val(invite.user_id);
                            input.prop('disabled', false);
                            hidden.prop('disabled', false);
                        }
                    }
                });
            }
        },
        error: function (error) {
            alert('Something went wrong!', 'error');
        }
    });

});

// Initialize autocomplete for email fields
function initEmailAutocomplete() {
    // Only initialize if the current user has permission to receive suggestions
    if (typeof canGetSuggestions === 'undefined' || !canGetSuggestions) {
        return;
    }

    if (typeof suggestableUsersUrl !== 'undefined') {
        $('#inviteModal input[type="text"]').each(function() {
            var $input = $(this);
            var inputId = $input.attr('id');
            var hiddenId = inputId.replace('email_', 'user_id_');
            
            $input.autocomplete({
                source: function(request, response) {
                    $.ajax({
                        url: suggestableUsersUrl,
                        type: 'GET',
                        data: { q: request.term },
                        success: function(data) {
                            response($.map(data, function(user) {
                                var label = user.email;
                                if (user.nickname) {
                                    label = user.nickname + ' (' + user.email + ')';
                                }
                                return {
                                    label: label,
                                    value: user.email,
                                    id: user.id
                                };
                            }));
                        }
                    });
                },
                minLength: 1,
                select: function(event, ui) {
                    $input.val(ui.item.value);
                    $('#' + hiddenId).val(ui.item.id);
                    return false;
                }
            });
        });
    }
}

// Initialize autocomplete when modal is shown
$('#inviteModal').on('shown.bs.modal', function() {
    initEmailAutocomplete();
});

$('#invite-user').submit(function (e) {
    e.preventDefault();

    $('.error').html("");

    let emails = [];
    let duplicateFound = false;

    $('#invite-user input[type="text"]').each(function () {
        let val = $(this).val().trim();
        let $errorField = $(this).closest('.form-group').find('.error');

        if (val !== "") {
            if (emails.includes(val)) {
                duplicateFound = true;
                $errorField.text("Duplicate user not allowed.");
            }
            emails.push(val);

            if (val.toLowerCase() == loggedInEmail.toLowerCase()) {
                duplicateFound = true;
                $errorField.text("You cannot invite yourself.");
            }
        }
    });

    if (duplicateFound) {
        return false;
    }

    var dataString = new FormData($('#invite-user')[0]);
    var $this = $(this);

    $.ajax({
        type: 'POST',
        url: inviteUserUrl,
        data: dataString,
        processData: false,
        contentType: false,
        beforeSend: function () {
            $($this).find('button[type="submit"]').prop('disabled', true);
        },
        success: function (result) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            if (result.status == true) {
                $('#inviteModal').modal('hide');
                toastr.success(result.message);
                $this[0].reset();

            } else {
                first_input = "";
                $('.error').html("");
                $.each(result.errors, function (key) {
                    if (first_input == "") first_input = key;
                    if (key.includes(".")) {
                        let main_key = key.split('.')[0];
                        $('#' + main_key).closest('.form-input').find(
                            '.error').html(result.errors[key]);
                    } else {
                        $('#' + key).closest('.form-group').find('.error')
                            .html(result.errors[key]);
                    }
                });
                $('#invite-user').find("#" + first_input).focus();
            }
        },
        error: function (error) {
            $($this).find('button[type="submit"]').prop('disabled', false);
            alert('Something went wrong!', 'error');
        }
    });
});