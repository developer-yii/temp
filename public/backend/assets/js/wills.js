$(document).ready(function () {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(document).on('submit', '#aboutform', function (e) {
        e.preventDefault();
        $('.error').html("");
        $('#aboutform .error').show();
        var insert = new FormData($('#aboutform')[0]);
        var $this = $(this);
        $.ajax({
            type: "POST",
            url: addWill,
            data: insert,
            contentType: false,
            processData: false,
            cache: false,
            async: false,
            beforeSend: function () {
                $($this).find('button[type="submit"]').prop('disabled', true);
            },
            success: function (result) {
                $($this).find('button[type="submit"]').prop('disabled', false);
                if (result.status == true) {
                    $this[0].reset();
                    // toastr.success(result.message);
                    $('#success').append('<div class=" alert alert-success  alert-dismissible fade show " role="alert"><div class="alert-content"><p>' + result.message + '</p><button type="button" class="close text-capitalize" data-dismiss="alert" aria-label="Close"><span data-feather="x" aria-hidden="true"></span></button></div></div>')
                    window.location.href = result.data;
                    $('.error').html("");
                } else {
                    first_input = "";
                    $('.error').html("");
                    $.each(result.error, function (key) {
                        if (first_input == "") first_input = key;
                        $('#aboutform .error-' + key).html(result.error[key]);
                        $('#' + key).addClass('is-invalid ih-medium ip-light radius-xs b-light');
                    });
                    $('#aboutform').find("#" + first_input).focus();
                }
            },
            error: function (error) {
                $($this).find('button[type="submit"]').prop('disabled', false);
                alert('Something went wrong!', 'error');
            }
        });
    });

    $(document).on('submit', '#addexecutor', function (e) {
        e.preventDefault();
        var insert = new FormData($('#addexecutor')[0]);
        var $this = $(this);
        $.ajax({
            type: "POST",
            url: addexecutor,
            data: insert,
            contentType: false,
            processData: false,
            cache: false,
            async: false,
            beforeSend: function () {
                $($this).find('button[type="submit"]').prop('disabled', true);
            },
            success: function (result) {
                $($this).find('button[type="submit"]').prop('disabled', false);
                if (result.status == true) {
                    $this[0].reset();
                    // toastr.success(result.message);
                    $('#success').append('<div class=" alert alert-success  alert-dismissible fade show " role="alert"><div class="alert-content"><p>' + result.message + '</p><button type="button" class="close text-capitalize" data-dismiss="alert" aria-label="Close"><span data-feather="x" aria-hidden="true"></span></button></div></div>')
                    window.location.href = result.data;
                    $('.error').html("");
                } else {
                    first_input = "";
                    $('.error').html("");
                    $.each(result.error, function (key) {
                        if (first_input == "") first_input = key;
                        $('#addexecutor .error-' + key).html(result.error[key]);
                        $('#' + key).addClass('is-invalid ih-medium ip-light radius-xs b-light');
                    });
                    $('#addexecutor').find("#" + first_input).focus();
                }
            },
            error: function (error) {
                $($this).find('button[type="submit"]').prop('disabled', false);
                alert('Something went wrong!', 'error');
            }
        });
    });

 

    $('.address-select2').select2();

    var select_state_after_load = select_city_after_load = '';
    $('#sameaddress').change(function () {
        if ($(this).is(":checked")) {

            var id = $(this).attr('data-id');
            $.ajax({
                type: "GET",
                url: willaddress,
                data: { id: id },
                dataType: 'json',
                success: function (data) {
                    // $('#country').val(data.country);
                    $('#address_1').val(data.address_1);
                    $('#address_2').val(data.address_2);
                    $('#state').val(data.state_province_region);
                    $('#city').val(data.city);
                    $('#zip_code').val(data.zip_code);
                    $("#country").select2("val", data.country);
                    select_state_after_load = data.state_province_region;
                    select_city_after_load = data.city;
                    // getState(data.country,data.state_province_region,data.city);
                    // $('.address-select2').trigger('change');
                    // $("#country").trigger('change');
                }
            });


        } else {
            $('#clearaddress input').val('').end().find('select').val('');
            $('.address-select2').trigger('change');
        }
    })

    $('#country').on('change', function () {
        var countryId = $(this).val();
        console.log(countryId);
        $('#state').html('');
        $.ajax({
            type: "POST",
            url: statelist,
            data: { country_id: countryId },
            dataType: 'json',
            success: function (result) {
                $('#state').html('<option value="">--Select State--</option>');
                $.each(result.states, function (key, value) {
                    checked = '';
                    if (value.id == select_state_after_load) {
                        select_state_after_load = '';
                        checked = "selected='selected'";
                    }
                    $('#state').append('<option value="' + value.id + '" ' + checked + '>' + value.name + '</option>');
                });
                $("#state").select2();
                $("#state").trigger('change');
                $('#city').html('<option value="">--Select City--</option>')
            }
        });
    })
    $('#state').on('change', function () {
        var stateId = $(this).val();
        $('#city').html('');
        $.ajax({
            type: "POST",
            url: citieslist,
            data: { state_id: stateId },
            dataType: 'json',
            success: function (result) {
                $('#city').html('<option value="">--Select City--</option>');
                $.each(result.cities, function (key, value) {
                    checked = '';
                    if (value.id == select_city_after_load) {
                        select_city_after_load = '';
                        checked = "selected='selected'";
                    }
                    $('#city').append('<option value="' + value.id + '" ' + checked + '>' + value.name + '</option>');
                });
                // $('#state').trigger('change');                
            }
        });
    })

    // function for call a function in success response
    function getState(country_id,selected_state_id=null,selected_city_id=null){
        var countryId = country_id;
        console.log(countryId);
        $('#state').html('');
        $.ajax({
            type: "POST",
            url: statelist,
            data: { country_id: countryId },
            dataType: 'json',
            success: function (result) {
                $('#state').html('<option value="">--Select State--</option>');
                $.each(result.states, function (key, value) {
                    let checked='';
                    if(value.id==selected_state_id){
                        checked="selected='selected'";
                    }
                    $('#state').append('<option1 value="' + value.id + '" '+checked+'>' + value.name + '</option>');
                });
                $('#state').select2();
                console.log('selected_state_id'+selected_state_id);
                $("#state").select2("val", selected_state_id);
                getCities(selected_state_id,selected_city_id);
                $('#city').html('<option value="">--Select City--</option>');

            }
        });
    }
    function getCities(state_id,selected_city_id){
        var stateId = state_id;
        $('#city').html('');
        $.ajax({
            type: "POST",
            url: citieslist,
            data: { state_id: stateId },
            dataType: 'json',
            success: function (result) {
                $('#city').html('<option value="">--Select City--</option>');
                $.each(result.cities, function (key, value) {
                    let checked='';
                    if(value.id==selected_city_id){
                        checked="selected='selected'";
                    }
                    $('#city').append('<option value="' + value.id + '" '+checked+'>' + value.name + '</option>');
                });
                $('#city').select2();
            }
        });
    }


});
