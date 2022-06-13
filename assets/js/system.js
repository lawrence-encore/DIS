(function($) {
    'use strict';

    $(function() {
        initialize_global_functions();
    });
})(jQuery);

// Initialize function
function initialize_global_functions(){
    $(document).on('click','#datatable-checkbox',function() {
        var status = $(this).is(':checked') ? true : false;
        $('.datatable-checkbox-children').prop('checked',status);

        check_table_check_box();
        check_table_multiple_button();
    });
    
    $(document).on('click','.datatable-checkbox-children',function() {
        check_table_check_box();
        check_table_multiple_button();
    });

    $(document).on('click','.view-transaction-log',function() {
        var username = $('#username').text();
        var transaction_log_id = $(this).data('transaction-log-id');

        sessionStorage.setItem('transaction_log_id', transaction_log_id);

        generate_modal('transaction log', 'Transaction Log', 'XL' , '1', '0', 'element', '', '0', username);
    });

    $(document).on('click','#page-header-notifications-dropdown',function() {
        var username = $('#username').text();
        var transaction = 'partial notification status';

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'text',
            data: {transaction : transaction, username : username},
            success: function () {
                $('#page-header-notifications-dropdown').html('<i class="bx bx-bell">');
            }
        });
    });

    $(document).on('click','.notification-item',function() {
        var username = $('#username').text();
        var transaction = 'read notification status';
        var notification_id = $(this).data('notification-id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'text',
            data: {transaction : transaction, notification_id : notification_id, username : username},
            success: function () {
                $(this).removeClass('text-primary');
            }
        });
    });

    $(document).on('click','#backup-database',function() {
        var username = $('#username').text();
        generate_modal('backup database form', 'Backup Database', 'R' , '1', '1', 'form', 'backup-database-form', '1', username);
    });

    if ($('.select2').length) {
        $('.select2').select2();
    }

    if ($('.filter-select2').length) {
        $('.filter-select2').select2({
            dropdownParent: $('#filter-off-canvas')
        });
    }
}

function initialize_elements(){
    if ($('.form-maxlength').length) {
        $('.form-maxlength').maxlength({
            alwaysShow: true,
            warningClass: 'badge mt-1 bg-info',
            limitReachedClass: 'badge mt-1 bg-danger',
            validate: true
        });
    }

    if ($('.form-select2').length) {
        $('.form-select2').select2().on('change', function() {
            $(this).valid();
        });
    }

    if ($('.birthday-date-picker').length) {
        $('.birthday-date-picker').datepicker({
            endDate: '-18y'
        });
    }
}

function initialize_form_validation(form_type){
    var transaction;
    var username = $('#username').text();

    if(form_type == 'change password form'){
        $('#change-password-form').validate({
            submitHandler: function (form) {
                transaction = 'change password';

                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: $(form).serialize() + '&transaction=' + transaction,
                    beforeSend: function(){
                        document.getElementById('signin').disabled = true;
                        $('#signin').html('<div class="spinner-border spinner-border-sm text-light" role="status"><span class="sr-only"></span></div>');
                    },
                    success: function (response) {
                        if(response === 'Updated'){
                            show_alert('Change User Account Password Success', 'The user account password has been updated. You can now sign in your account.', 'success');
                            $('#System-Modal').modal('hide');

                            document.getElementById('signin').disabled = false;
                            $('#signin').html('Log In');
                        }
                        else{
                            if(response === 'Not Found'){
                                show_alert('Change User Account Password Error', 'The user account does not exist.', 'error');
                            }
                            else{
                                show_alert('Change User Account Password Error', response, 'error');
                            }                            

                            document.getElementById('submit-form').disabled = false;
                            $('#submit-form').html('Submit');
                        }
                    }
                });

                return false;
            },
            rules: {
                change_password: {
                    required: true,
                    password_strength : true
                }
            },
            messages: {
                change_password: {
                    required: 'Please enter your password',
                }
            },
            errorPlacement: function(label, element) {
                if(element.hasClass('web-select2') && element.next('.select2-container').length) {
                    label.insertAfter(element.next('.input-group'));
                }
                else if(element.parent('.input-group').length){
                    label.insertAfter(element.parent());
                }
                else{
                    label.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).parent().addClass('has-danger');
                $(element).addClass('form-control-danger');
            },
            success: function(label,element) {
                $(element).parent().removeClass('has-danger')
                $(element).removeClass('form-control-danger')
                label.remove();
            }
        });
    }
    else if(form_type == 'policy form'){
        $('#policy-form').validate({
            submitHandler: function (form) {
                transaction = 'submit policy';

                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: $(form).serialize() + '&username=' + username + '&transaction=' + transaction,
                    beforeSend: function(){
                        document.getElementById('submit-form').disabled = true;
                        $('#submit-form').html('<div class="spinner-border spinner-border-sm text-light" role="status"><span rclass="sr-only"></span></div>');
                    },
                    success: function (response) {
                        if(response === 'Updated' || response === 'Inserted'){
                            if(response === 'Inserted'){
                                show_alert('Insert Policy Success', 'The policy has been inserted.', 'success');
                            }
                            else{
                                show_alert('Update Policy Success', 'The policy has been updated.', 'success');
                            }

                            $('#System-Modal').modal('hide');
                            reload_datatable('#policy-datatable');
                        }
                        else{
                            show_alert('Policy Error', response, 'error');
                        }
                    },
                    complete: function(){
                        document.getElementById('submit-form').disabled = false;
                        $('#submit-form').html('Submit');
                    }
                });
                return false;
            },
            rules: {
                policy: {
                    required: true         
                },
                policy_description: {
                    required: true         
                }
            },
            messages: {
                policy: {
                    required: 'Please enter the policy',
                },
                policy_description: {
                    required: 'Please enter the policy description',
                }
            },
            errorPlacement: function(label, element) {
                if((element.hasClass('select2') || element.hasClass('form-select2')) && element.next('.select2-container').length) {
                    label.insertAfter(element.next('.select2-container'));
                }
                else if(element.parent('.input-group').length){
                    label.insertAfter(element.parent());
                }
                else{
                    label.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).parent().addClass('has-danger');
                $(element).addClass('form-control-danger');
            },
            success: function(label,element) {
                $(element).parent().removeClass('has-danger')
                $(element).removeClass('form-control-danger')
                label.remove();
            }
        });
    }
    else if(form_type == 'permission form'){
        $('#permission-form').validate({
            submitHandler: function (form) {
                transaction = 'submit permission';

                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: $(form).serialize() + '&username=' + username + '&transaction=' + transaction,
                    beforeSend: function(){
                        document.getElementById('submit-form').disabled = true;
                        $('#submit-form').html('<div class="spinner-border spinner-border-sm text-light" role="status"><span rclass="sr-only"></span></div>');
                    },
                    success: function (response) {
                        if(response === 'Updated' || response === 'Inserted'){
                            if(response === 'Inserted'){
                                show_alert('Insert Permission Success', 'The permission has been inserted.', 'success');
                            }
                            else{
                                show_alert('Update Permission Success', 'The permission has been updated.', 'success');
                            }

                            $('#System-Modal').modal('hide');
                            reload_datatable('#permission-datatable');
                        }
                        else{
                            show_alert('Permission Error', response, 'error');
                        }
                    },
                    complete: function(){
                        document.getElementById('submit-form').disabled = false;
                        $('#submit-form').html('Submit');
                    }
                });
                return false;
            },
            rules: {
                permission: {
                    required: true         
                }
            },
            messages: {
                permission: {
                    required: 'Please enter the permission',
                }
            },
            errorPlacement: function(label, element) {
                if((element.hasClass('select2') || element.hasClass('form-select2')) && element.next('.select2-container').length) {
                    label.insertAfter(element.next('.select2-container'));
                }
                else if(element.parent('.input-group').length){
                    label.insertAfter(element.parent());
                }
                else{
                    label.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).parent().addClass('has-danger');
                $(element).addClass('form-control-danger');
            },
            success: function(label,element) {
                $(element).parent().removeClass('has-danger')
                $(element).removeClass('form-control-danger')
                label.remove();
            }
        });
    }
    else if(form_type == 'role form'){
        $('#role-form').validate({
            submitHandler: function (form) {
                transaction = 'submit role';

                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: $(form).serialize() + '&username=' + username + '&transaction=' + transaction,
                    beforeSend: function(){
                        document.getElementById('submit-form').disabled = true;
                        $('#submit-form').html('<div class="spinner-border spinner-border-sm text-light" role="status"><span rclass="sr-only"></span></div>');
                    },
                    success: function (response) {
                        if(response === 'Updated' || response === 'Inserted'){
                            if(response === 'Inserted'){
                                show_alert('Insert Role Success', 'The role has been inserted.', 'success');
                            }
                            else{
                                show_alert('Update Role Success', 'The role has been updated.', 'success');
                            }

                            $('#System-Modal').modal('hide');
                            reload_datatable('#role-datatable');
                        }
                        else{
                            show_alert('Role Error', response, 'error');
                        }
                    },
                    complete: function(){
                        document.getElementById('submit-form').disabled = false;
                        $('#submit-form').html('Submit');
                    }
                });
                return false;
            },
            rules: {
                role: {
                    required: true
                },
                description: {
                    required: true
                }
            },
            messages: {
                role: {
                    required: 'Please enter the role',
                },
                description: {
                    required: 'Please enter the description',
                }
            },
            errorPlacement: function(label, element) {
                if((element.hasClass('select2') || element.hasClass('form-select2')) && element.next('.select2-container').length) {
                    label.insertAfter(element.next('.select2-container'));
                }
                else if(element.parent('.input-group').length){
                    label.insertAfter(element.parent());
                }
                else{
                    label.insertAfter(element);
                }
            },
            highlight: function(element) {
                $(element).parent().addClass('has-danger');
                $(element).addClass('form-control-danger');
            },
            success: function(label,element) {
                $(element).parent().removeClass('has-danger')
                $(element).removeClass('form-control-danger')
                label.remove();
            }
        });
    }
    else if(form_type == 'role permission form'){
        $('#role-permission-form').validate({
            submitHandler: function (form) {
                var transaction = 'submit role permission';
                var permission = [];
        
                $('.role-permissions').each(function(){
                    if($(this).is(':checked')){  
                        permission.push(this.value);  
                    }
                });

                $.ajax({
                    type: 'POST',
                    url: 'controller.php',
                    data: $(form).serialize() + '&username=' + username + '&transaction=' + transaction + '&permission=' + permission,
                    beforeSend: function(){
                        document.getElementById('submit-form').disabled = true;
                        $('#submit-form').html('<div class="spinner-border spinner-border-sm text-light" role="status"><span rclass="sr-only"></span></div>');
                    },
                    success: function (response) {
                        if(response === 'Updated'){
                            show_alert_event('Role Permission Update Success', 'The permission has been updated.', 'success', 'reload');
                        }
                        else if(response === 'Not Found'){
                            show_alert('Role Permission Update Error', 'The role does not exist.', 'error');
                        }
                        else{
                            show_alert('Role Permission Update Error', response, 'error');
                        }
                    },
                    complete: function(){
                        document.getElementById('submit-form').disabled = false;
                        $('#submit-form').html('Save');
                    }
                });
                return false;
            }
        });
    }
}

// Display functions
function display_form_details(form_type){
    var transaction;
    var d = new Date();

    if(form_type == 'system parameter form'){
        transaction = 'system parameter details';

        var parameter_id = sessionStorage.getItem('parameter_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {parameter_id : parameter_id, transaction : transaction},
            success: function(response) {
                $('#parameter').val(response[0].PARAMETER_DESC);
                $('#extension').val(response[0].PARAMETER_EXTENSION);
                $('#parameter_number').val(response[0].PARAMETER_NUMBER);
                $('#parameter_id').val(parameter_id);
            }
        });
    }
    else if(form_type == 'system parameter details'){
        transaction = 'system parameter details';
        
        var parameter_id = sessionStorage.getItem('parameter_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {parameter_id : parameter_id, transaction : transaction},
            success: function(response) {
                $('#parameter').text(response[0].PARAMETER_DESC);
                $('#extension').text(response[0].PARAMETER_EXTENSION);
                $('#parameter_number').text(response[0].PARAMETER_NUMBER);
            }
        });
    }
    else if(form_type == 'transaction log'){
        transaction = 'transaction log details';
        
        var transaction_log_id = sessionStorage.getItem('transaction_log_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction_log_id : transaction_log_id, transaction : transaction},
            success: function(response) {
                document.getElementById('transaction-log-timeline').innerHTML = response[0].TIMELINE;
            }
        });
    }
    else if(form_type == 'policy form'){
        transaction = 'policy details';

        var policy_id = sessionStorage.getItem('policy_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {policy_id : policy_id, transaction : transaction},
            success: function(response) {
                $('#policy').val(response[0].POLICY);
                $('#policy_description').val(response[0].POLICY_DESCRIPTION);
                $('#policy_id').val(policy_id);
            }
        });
    }
    else if(form_type == 'permission form'){
        transaction = 'permission details';
        
        var permission_id = sessionStorage.getItem('permission_id');
        var policy_id = $('#policy-id').text();
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {permission_id : permission_id, transaction : transaction},
            success: function(response) {
                $('#permission_id').val(permission_id);
                $('#policy_id').val(policy_id);
                $('#permission').val(response[0].PERMISSION);
            }
        });
    }
    else if(form_type == 'role form'){
        transaction = 'role details';
        
        var role_id = sessionStorage.getItem('role_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {role_id : role_id, transaction : transaction},
            success: function(response) {
                $('#role_id').val(role_id);
                $('#role').val(response[0].ROLE);
                $('#role_description').val(response[0].ROLE_DESCRIPTION);
            }
        });
    }
    else if(form_type == 'role permission form'){
        transaction = 'role permission details';
        
        var role_id = sessionStorage.getItem('role_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {role_id : role_id, transaction : transaction},
            success: function(response) {
                var userArray = new Array();
                userArray = response.toString().split(',');

                $('#role_id').val(role_id);

                $('.role-permissions').each(function(index) {
                    var val = $(this).val();
                    if (userArray.includes(val)) {
                        $(this).prop('checked', true);
                    }
                });
            }
        });
    }
}

function initialize_transaction_log_table(datatable_name, buttons = false, show_all = false){
    var username = $('#username').text();
    var transaction_log_id = sessionStorage.getItem('transaction_log_id');
    var type = 'transaction log table';
    var settings;

    var column = [ 
        { 'data' : 'LOG_TYPE' },
        { 'data' : 'LOG' },
        { 'data' : 'LOG_DATE' },
        { 'data' : 'LOG_BY' }
    ];

    var column_definition = [
        { 'width': '15%', 'aTargets': 0 },
        { 'width': '45%', 'aTargets': 1 },
        { 'width': '20%', 'aTargets': 2 },
        { 'width': '20%', 'aTargets': 3 },
    ];

    if(show_all){
        length_menu = [ [-1], ['All'] ];
    }
    else{
        length_menu = [ [10, 25, 50, 100, -1], [10, 25, 50, 100, 'All'] ];
    }

    if(buttons){
        settings = {
            'ajax': { 
                'url' : 'system-generation.php',
                'method' : 'POST',
                'dataType': 'JSON',
                'data': {'type' : type, 'username' : username, 'transaction_log_id' : transaction_log_id},
                'dataSrc' : ''
            },
            dom:  "<'row'<'col-sm-3'l><'col-sm-6 text-center mb-2'B><'col-sm-3'f>>" +  "<'row'<'col-sm-12'tr>>" + "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                'csv', 'excel', 'pdf'
            ],
            'order': [[ 2, 'desc' ]],
            'columns' : column,
            'scrollY': false,
            'scrollX': true,
            'scrollCollapse': true,
            'fnDrawCallback': function( oSettings ) {
                readjust_datatable_column();
            },
            'aoColumnDefs': column_definition,
            'lengthMenu': length_menu,
            'language': {
                'emptyTable': 'No data found',
                'searchPlaceholder': 'Search...',
                'search': '',
                'loadingRecords': '<div class="spinner-border spinner-border-lg text-info" role="status"><span class="sr-only">Loading...</span></div>'
            }
        };
    }
    else{
        settings = {
            'ajax': { 
                'url' : 'system-generation.php',
                'method' : 'POST',
                'dataType': 'JSON',
                'data': {'type' : type, 'username' : username, 'transaction_log_id' : transaction_log_id},
                'dataSrc' : ''
            },
            'order': [[ 2, 'desc' ]],
            'columns' : column,
            'scrollY': false,
            'scrollX': true,
            'scrollCollapse': true,
            'fnDrawCallback': function( oSettings ) {
                readjust_datatable_column();
            },
            'aoColumnDefs': column_definition,
            'lengthMenu': length_menu,
            'language': {
                'emptyTable': 'No data found',
                'searchPlaceholder': 'Search...',
                'search': '',
                'loadingRecords': '<div class="spinner-border spinner-border-lg text-info" role="status"><span class="sr-only">Loading...</span></div>'
            }
        };
    }

    destroy_datatable(datatable_name);
    
    $(datatable_name).dataTable(settings);
}

// Get location function
function get_location(map_div) {
    if(!map_div){
        if (navigator.geolocation) {
            var options = {
                enableHighAccuracy: true,
                timeout: 10000,
                maximumAge: 0
            };

            navigator.geolocation.watchPosition(show_position, show_geolocation_error, options);
        } 
        else {
            show_alert('Geolocation Error', 'Your browser does not support geolocation.', 'error');
        }
    }
    else{
        var map = new GMaps({
            div: '#' + map_div,
            lat: -12.043333,
            lng: -77.028333
        });
    
        GMaps.geolocate({
            success: function(position){
                map.setCenter(position.coords.latitude, position.coords.longitude);
                map.addMarker({
                    lat: position.coords.latitude,
                    lng: position.coords.longitude
                });

                sessionStorage.setItem('latitude', position.coords.latitude);
                sessionStorage.setItem('longitude', position.coords.longitude);
            },
            error: function(error){
                show_alert('Geolocation Error', 'Geolocation failed: ' + error.message, 'error');
            },
            not_supported: function(){
                show_alert('Geolocation Error', 'Your browser does not support geolocation.', 'error');
            },
        });
    }
}

function show_position(position) {
    sessionStorage.setItem('latitude', position.coords.latitude);
    sessionStorage.setItem('longitude', position.coords.longitude);

    if ($('#attendance_position').length) {
        $('#attendance_position').val(position.coords.longitude + ', ' + position.coords.latitude);
    }

    if ($('#position').length) {
        $('#position').val(position.coords.longitude + ', ' + position.coords.latitude);
    }
}

function show_geolocation_error(error) {
    switch(error.code) {
        case error.PERMISSION_DENIED:
            show_alert('Geolocation Error', 'User denied the request for Geolocation.', 'error');
            break;
        case error.POSITION_UNAVAILABLE:
            show_alert('Geolocation Error', 'Location information is unavailable.', 'error');
            break;
        case error.TIMEOUT:
            show_alert('Geolocation Error', 'The request to get user location timed out.', 'error');
            break;
        case error.UNKNOWN_ERROR:
            show_alert('Geolocation Error', 'An unknown error occurred.', 'error');
            break;
    }
}

// Generate function
function generate_modal(form_type, title, size, scrollable, submit_button, generate_type, form_id, add, username){
    var type = 'system modal';

    $.ajax({
        url: 'system-generation.php',
        method: 'POST',
        dataType: 'JSON',
        data: {type : type, username : username, title : title, size : size, scrollable : scrollable, submit_button : submit_button, generate_type : generate_type, form_id : form_id},
        beforeSend: function(){
            $('#System-Modal').remove();
        },
        success: function(response) {
            $('body').append(response[0].MODAL);
        },
        complete : function(){
            if(generate_type == 'form'){
                generate_form(form_type, form_id, add, username);
            }
            else{
                generate_element(form_type, '', '', '1', username);
            }
        }
    });
}

function generate_form(form_type, form_id, add, username){
    var type = 'system form';

    $.ajax({
        url: 'system-generation.php',
        method: 'POST',
        dataType: 'JSON',
        data: { type : type, username : username, form_type : form_type, form_id : form_id },
        success: function(response) {
            document.getElementById('modal-body').innerHTML = response[0].FORM;
        },
        complete: function(){
            if(add == '0'){
                display_form_details(form_type);
            }
            else{
                if(form_type == 'permission form'){
                    var policy_id = $('#policy-id').text();
                    $('#policy_id').val(policy_id);
                }
                else if(form_type == 'emergency contact form' || form_type == 'employee address form' || form_type == 'employee social form' || form_type == 'employee attendance form' || form_type == 'employee leave entitlement form' || form_type == 'employee leave form' || form_type == 'employee file form' || form_type == 'employee allowance form' || form_type == 'employee allowance update form'){
                    var employee_id = $('#employee-id').text();
                    $('#employee_id').val(employee_id);
                }
                else if(form_type == 'approve leave form' || form_type == 'reject leave form' || form_type == 'cancel leave form' || form_type == 'approve multiple leave form' || form_type == 'reject multiple leave form' || form_type == 'cancel multiple leave form' || form_type == 'approve employee leave form' || form_type == 'reject employee leave form' || form_type == 'cancel employee leave form'){
                    var leave_id = sessionStorage.getItem('leave_id');
                    $('#leave_id').val(leave_id);
                }
                else if(form_type == 'time in form' || form_type == 'get location form'){
                    get_location('');
                }
                else if(form_type == 'approve attendance creation form' || form_type == 'approve multiple attendance creation form' || form_type == 'reject attendance creation form' || form_type == 'cancel attendance creation form' || form_type == 'reject multiple attendance creation form' || form_type == 'cancel multiple attendance creation form' || form_type == 'approve attendance adjustment form' || form_type == 'approve multiple attendance adjustment form' || form_type == 'reject attendance adjustment form' || form_type == 'cancel attendance adjustment form' || form_type == 'reject multiple attendance adjustment form' || form_type == 'cancel multiple attendance adjustment form'){
                    var request_id = sessionStorage.getItem('request_id');
                    $('#request_id').val(request_id);
                }
                else if(form_type == 'contribution bracket form'){
                    var government_contribution_id = $('#government-contribution-id').text();
                    $('#government_contribution_id').val(government_contribution_id);
                }
                else if(form_type == 'tag loan details as paid form' || form_type == 'tag loan details as unpaid form' || form_type == 'tag multiple loan details as paid form' || form_type == 'tag multiple loan details as unpaid form'){
                    var loan_details_id = sessionStorage.getItem('loan_details_id');
                    $('#loan_details_id').val(loan_details_id);
                }
                else if(form_type == 'send payslip form'){
                    var pay_run_id = sessionStorage.getItem('pay_run_id');

                    $('#pay_run_id').val(pay_run_id);
                    generate_pay_run_payee_option(pay_run_id);
                }
                else if(form_type == 'recruitment pipeline stage form'){
                    var recruitment_pipeline_id = $('#recruitment-pipeline-id').text();

                    $('#recruitment_pipeline_id').val(recruitment_pipeline_id);
                }
                else if(form_type == 'recruitment scorecard section form'){
                    var recruitment_scorecard_id = $('#recruitment-scorecard-id').text();

                    $('#recruitment_scorecard_id').val(recruitment_scorecard_id);
                }
                else if(form_type == 'recruitment scorecard section option form'){
                    var recruitment_scorecard_section_id = $('#recruitment-scorecard-section-id').text();

                    $('#recruitment_scorecard_section_id').val(recruitment_scorecard_section_id);
                }
            }

            initialize_elements();
            initialize_form_validation(form_type);

            $('#System-Modal').modal('show');
        }
    });    
}

function generate_element(element_type, value, container, modal, username){
    var type = 'system element';

    $.ajax({
        url: 'system-generation.php',
        method: 'POST',
        dataType: 'JSON',
        data: { type : type, username : username, value : value, element_type : element_type },
        beforeSend : function(){
            if(container){
                document.getElementById(container).innerHTML = '';
            }
        },
        success: function(response) {
            if(!container){
                document.getElementById('modal-body').innerHTML = response[0].ELEMENT;
            }
            else{
                document.getElementById(container).innerHTML = response[0].ELEMENT;
            }
        },
        complete: function(){
            initialize_elements();

            if(modal == '1'){
                $('#System-Modal').modal('show');

                if(element_type == 'system parameter details' || element_type == 'branch details' || element_type == 'leave details' || element_type == 'employee file details' || element_type == 'employee qr code' || element_type == 'user account details' || element_type == 'employee attendance details' || element_type == 'attendance creation details' || element_type == 'attendance adjustment details' || element_type == 'work shift regular details' || element_type == 'work shift scheduled details' || element_type == 'allowance details' || element_type == 'deduction details' || element_type == 'contribution deduction details' || element_type == 'salary details' || element_type == 'payroll group details' || element_type == 'pay run details' || element_type == 'other income details' || element_type == 'payslip details' || element_type == 'job details'){
                    display_form_details(element_type);
                }
                else if(element_type == 'scan qr code form'){
                    $('#qr-code-reader').html('<div class="d-flex justify-content-center"><div class="spinner-border spinner-border-sm text-primary" role="status"><span rclass="sr-only"></span></div></div>');

                    Html5Qrcode.getCameras().then(devices => {
                        if (devices && devices.length) {
                            var camera_id = devices[0].id;
            
                            const html5QrCode = new Html5Qrcode("qr-code-reader");
                            const config = { fps: 10, qrbox: { width: 250, height: 250 } };
                            const qrCodeSuccessCallback = (decodedText, decodedResult) => {
                                var audio = new Audio('assets/audio/scan.mp3');
                                audio.play();
                                navigator.vibrate([500]);
            
                                var employee_id = decodedText.substring(
                                    decodedText.lastIndexOf("[") + 1, 
                                    decodedText.lastIndexOf("]")
                                );
            
                                var latitude = sessionStorage.getItem('latitude');
                                var longitude = sessionStorage.getItem('longitude');
                                var transaction = 'submit attendance record';
                                var username = $('#username').text();
                                    
                                $.ajax({
                                    type: 'POST',
                                    url: 'controller.php',
                                    data: {username : username, latitude : latitude, employee_id : employee_id, longitude : longitude, transaction : transaction},
                                    success: function (response) {
                                        if(response === 'Time In'){
                                            var audio = new Audio('assets/audio/attendance-clock-in-success.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                        else if(response === 'Time Out'){
                                            var audio = new Audio('assets/audio/attendance-clock-out-success.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                        else if(response === 'Max Attendance'){
                                            var audio = new Audio('assets/audio/max-attendance-error.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                        else if(response === 'Location'){
                                            var audio = new Audio('assets/audio/location-error.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                        else if(response === 'Time Allowance'){
                                            var audio = new Audio('assets/audio/clock-out-time-error.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                        else{
                                            var audio = new Audio('assets/audio/attendance-error.mp3');
                                            audio.play();
                                            navigator.vibrate([500]);
                                        }
                                    }
                                });
            
                                html5QrCode.stop().then((ignore) => {
                                    $('#qr-code-reader').html('');
                                    $('#qr-code-reader').html('<div class="d-flex justify-content-center"><div class="spinner-border spinner-border-sm text-primary" role="status"><span rclass="sr-only"></span></div></div>');
                                    
                                    setTimeout(function(){  html5QrCode.start({ deviceId: { exact: camera_id} }, config, qrCodeSuccessCallback); }, 4000);
                                }).catch((err) => {
                                    alert(err);
                                });
                            };
            
                            html5QrCode.start({ deviceId: { exact: camera_id} }, config, qrCodeSuccessCallback);
                        }
                    }).catch(err => {
                        alert(err);
                    });
                }
                else if(element_type == 'transaction log'){
                    if($('#transaction-log-datatable').length){
                        initialize_transaction_log_table('#transaction-log-datatable');
                    }
                }
                else if(element_type == 'attendance summary details'){
                    if($('#employee-attendance-datatable').length){
                        initialize_employee_attendance_table('#employee-attendance-datatable');
                    }

                    if($('#attendance-adjustment-datatable').length){
                        initialize_attendance_adjustment_table('#attendance-adjustment-datatable');
                    }

                    if($('#attendance-creation-datatable').length){
                        initialize_attendance_creation_table('#attendance-creation-datatable');
                    }
                }
            }
        }
    });
}

function generate_city_option(province, selected){
    var username = $('#username').text();
    var type = 'city options';

    $.ajax({
        url: 'system-generation.php',
        method: 'POST',
        dataType: 'JSON',
        data: {type : type, province : province, username : username},
        beforeSend: function(){
            $('#city').empty();
        },
        success: function(response) {
            var newOption = new Option('--', '', false, false);
            $('#city').append(newOption);

            for(var i = 0; i < response.length; i++) {
                newOption = new Option(response[i].CITY, response[i].CITY_ID, false, false);
                $('#city').append(newOption);
            }
        },
        complete: function(){
            if(selected != ''){
                $('#city').val(selected).change();
            }
        }
    });
}

function generate_pay_run_payee_option(pay_run_id){
    var username = $('#username').text();
    var type = 'pay run payee options';

    $.ajax({
        url: 'system-generation.php',
        method: 'POST',
        dataType: 'JSON',
        data: {type : type, pay_run_id : pay_run_id, username : username},
        beforeSend: function(){
            $('#payee').empty();
        },
        success: function(response) {
            for(var i = 0; i < response.length; i++) {
                newOption = new Option(response[i].FILE_AS, response[i].EMPLOYEE_ID, false, false);
                $('#payee').append(newOption);
            }
        }
    });
}

// Reset validation functions
function reset_element_validation(element){
    $(element).parent().removeClass('has-danger');
    $(element).removeClass('form-control-danger');
    $(element + '-error').remove();
}

// Reload functions
function reload_datatable(datatable){
    hide_multiple_buttons();
    $(datatable).DataTable().ajax.reload();
}

// Destroy functions
function destroy_datatable(datatable_name){
    $(datatable_name).DataTable().clear().destroy();
}

// Clear
function clear_datatable(datatable_name){
    $(datatable_name).dataTable().fnClearTable();
}

// Re-adjust datatable columns
function readjust_datatable_column(){
    $('a[data-bs-toggle="tab"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    });

    $('a[data-bs-toggle="pill"]').on('shown.bs.tab', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    });

    $('#System-Modal').on('shown.bs.modal', function (e) {
        $.fn.dataTable.tables( {visible: true, api: true} ).columns.adjust();
    });
}

// Truncate functions
function truncate_temporary_table(table_name){
    var transaction = 'truncate temporary table';

    $.ajax({
        url: 'controller.php',
        method: 'POST',
        dataType: 'TEXT',
        data: {table_name : table_name, transaction : transaction},
        success: function(response) {
            if($('#import-attendance-record-datatable').length){
                initialize_temporary_attendance_record_table('#import-attendance-record-datatable', false, true);
            }

            if($('#import-employee-datatable').length){
                initialize_temporary_employee_table('#import-employee-datatable', false, true);
            }

            if($('#import-leave-entitlement-datatable').length){
                initialize_temporary_leave_entitlement_table('#import-leave-entitlement-datatable', false, true);
            }

            if($('#import-leave-datatable').length){
                initialize_temporary_leave_table('#import-leave-datatable', false, true);
            }

            if($('#import-attendance-adjustment-datatable').length){
                initialize_temporary_attendance_adjustment_table('#import-attendance-adjustment-datatable', false, true);
            }

            if($('#import-attendance-creation-datatable').length){
                initialize_temporary_attendance_creation_table('#import-attendance-creation-datatable', false, true);
            }

            if($('#import-allowance-datatable').length){
                initialize_temporary_allowance_table('#import-allowance-datatable', false, true);
            }

            if($('#import-deduction-datatable').length){
                initialize_temporary_deduction_table('#import-deduction-datatable', false, true);
            }

            if($('#import-government-contribution-datatable').length){
                initialize_temporary_government_contribution_table('#import-government-contribution-datatable', false, true);
            }

            if($('#import-contribution-bracket-datatable').length){
                initialize_temporary_contribution_bracket_table('#import-contribution-bracket-datatable', false, true);
            }

            if($('#import-contribution-deduction-datatable').length){
                initialize_temporary_contribution_deduction_table('#import-contribution-deduction-datatable', false, true);
            }

            if($('#import-withholding-tax-datatable').length){
                initialize_temporary_withholding_tax_table('#import-withholding-tax-datatable', false, true);
            }

            if($('#import-other-income-datatable').length){
                initialize_temporary_other_income_table('#import-other-income-datatable', false, true);
            }
        }
    });
}

// Check functions
function check_option_exist(element, option, return_value){
    if ($(element).find('option[value="' + option + '"]').length) {
        $(element).val(option).trigger('change');
    }
    else{
        $(element).val(return_value).trigger('change');
    }
}

function check_empty(value, id, type){
    if(value != '' || value != null){
        if(type == 'select'){
            $(id).val(value).change();
        }
        else if(type == 'text'){
            $(id).text(value);
        }
        else {
            $(id).val(value);
        }
    }
}

function check_table_check_box(){
    var input_elements = [].slice.call(document.querySelectorAll('.datatable-checkbox-children'));
    var checked_value = input_elements.filter(chk => chk.checked).length;

    if(checked_value > 0){
        $('.multiple').removeClass('d-none');
    }
    else{
        $('.multiple').addClass('d-none');
    }
}

function check_table_multiple_button(){
    var input_elements = [].slice.call(document.querySelectorAll('.datatable-checkbox-children'));
    var checked_value = input_elements.filter(chk => chk.checked).length;

    if(checked_value > 0){
        var lock_array = [];
        var cancel_array = [];
        var delete_array = [];
        var reject_array = [];
        var approve_array = [];
        var for_recommendation_array = [];
        var for_approval_array = [];
        var recommend_array = [];
        var active_array = [];
        var paid_array = [];
        var unpaid_array = [];
        var send_array = [];
        var print_array = [];
        
        $(".datatable-checkbox-children").each(function () {
            var cancel_data = $(this).data('cancel');
            var delete_data = $(this).data('delete');
            var for_recommendation_data = $(this).data('for-recommendation');
            var for_approval_data = $(this).data('for-approval');
            var recommend_data = $(this).data('recommend');
            var reject_data = $(this).data('reject');
            var approve_data = $(this).data('approve');
            var lock = $(this).data('lock');
            var active = $(this).data('active');
            var paid = $(this).data('paid');
            var unpaid = $(this).data('unpaid');
            var send = $(this).data('send');
            var print = $(this).data('print');

            if($(this).prop('checked') === true){
                lock_array.push(lock);
                cancel_array.push(cancel_data);
                approve_array.push(approve_data);
                for_approval_array.push(for_approval_data);
                reject_array.push(reject_data);
                delete_array.push(delete_data);
                for_recommendation_array.push(for_recommendation_data);
                recommend_array.push(recommend_data);
                active_array.push(active);
                paid_array.push(paid);
                unpaid_array.push(unpaid);
                send_array.push(send);
                print_array.push(print);
            }
        });

        var cancel_checker = arr => arr.every(v => v === 1);
        var delete_checker = arr => arr.every(v => v === 1);
        var for_recommendation_checker = arr => arr.every(v => v === 1);
        var for_approval_checker = arr => arr.every(v => v === 1);
        var recommend_checker = arr => arr.every(v => v === 1);
        var reject_checker = arr => arr.every(v => v === 1);
        var approve_checker = arr => arr.every(v => v === 1);
        var unlock_checker = arr => arr.every(v => v === 1);
        var lock_checker = arr => arr.every(v => v === 0);
        var activate_checker = arr => arr.every(v => v === 0);
        var deactivate_checker = arr => arr.every(v => v === 1);
        var paid_checker = arr => arr.every(v => v === 1);
        var unpaid_checker = arr => arr.every(v => v === 1);
        var send_checker = arr => arr.every(v => v === 1);
        var print_checker = arr => arr.every(v => v === 1);
        
        if(lock_checker(lock_array) || unlock_checker(lock_array)){
            if(lock_checker(lock_array)){
                $('.multiple-lock').removeClass('d-none');
                $('.multiple-unlock').addClass('d-none');
            }

            if(unlock_checker(lock_array)){
                $('.multiple-lock').addClass('d-none');
                $('.multiple-unlock').removeClass('d-none');
            }
        }
        else{
            $('.multiple-lock').addClass('d-none');
            $('.multiple-unlock').addClass('d-none');
        }

        if(activate_checker(active_array) || deactivate_checker(active_array)){
            if(activate_checker(active_array)){
                $('.multiple-activate').removeClass('d-none');
                $('.multiple-deactivate').addClass('d-none');
            }

            if(deactivate_checker(active_array)){
                $('.multiple-activate').addClass('d-none');
                $('.multiple-deactivate').removeClass('d-none');
            }
        }
        else{
            $('.multiple-activate').addClass('d-none');
            $('.multiple-deactivate').addClass('d-none');
        }
        
        if(for_approval_checker(for_approval_array)){
            $('.multiple-for-approval').removeClass('d-none');
        }
        else{
            $('.multiple-for-approval').addClass('d-none');
        }
        
        if(cancel_checker(cancel_array)){
            $('.multiple-cancel').removeClass('d-none');
        }
        else{
            $('.multiple-cancel').addClass('d-none');
        }
        
        if(reject_checker(reject_array)){
            $('.multiple-reject').removeClass('d-none');
        }
        else{
            $('.multiple-reject').addClass('d-none');
        }
        
        if(approve_checker(approve_array)){
            $('.multiple-approve').removeClass('d-none');
        }
        else{
            $('.multiple-approve').addClass('d-none');
        }
        
        if(delete_checker(delete_array)){
            $('.multiple-delete').removeClass('d-none');
        }
        else{
            $('.multiple-delete').addClass('d-none');
        }
        
        if(for_recommendation_checker(for_recommendation_array)){
            $('.multiple-for-recommendation').removeClass('d-none');
        }
        else{
            $('.multiple-for-recommendation').addClass('d-none');
        }
        
        if(recommend_checker(recommend_array)){
            $('.multiple-recommendation').removeClass('d-none');
        }
        else{
            $('.multiple-recommendation').addClass('d-none');
        }

        if(paid_checker(paid_array)){
            $('.multiple-tag-loan-details-as-paid').removeClass('d-none');
        }
        else{
            $('.multiple-tag-loan-details-as-paid').addClass('d-none');
        }

        if(unpaid_checker(unpaid_array)){
            $('.multiple-tag-loan-details-as-unpaid').removeClass('d-none');
        }
        else{
            $('.multiple-tag-loan-details-as-unpaid').addClass('d-none');
        }

        if(send_checker(send_array)){
            $('.multiple-send').removeClass('d-none');
        }
        else{
            $('.multiple-send').addClass('d-none');
        }

        if(print_checker(print_array)){
            $('.multiple-print').removeClass('d-none');
        }
        else{
            $('.multiple-print').addClass('d-none');
        }
    }
    else{
        $('.multiple-delete').addClass('d-none');
        $('.multiple-cancel').addClass('d-none');
        $('.multiple-for-recommendation').addClass('d-none');
        $('.multiple-for-approval').addClass('d-none');
        $('.multiple-recommendation').addClass('d-none');
        $('.multiple-reject').addClass('d-none');
        $('.multiple-approve').addClass('d-none');
        $('.multiple-lock').addClass('d-none');
        $('.multiple-unlock').addClass('d-none');
        $('.multiple-activate').addClass('d-none');
        $('.multiple-deactivate').addClass('d-none');
        $('.multiple-tag-loan-details-as-paid').addClass('d-none');
        $('.multiple-tag-loan-details-as-unpaid').addClass('d-none');
        $('.multiple-send').addClass('d-none');
        $('.multiple-print').addClass('d-none');
    }
}

// Show alert
function show_alert(title, message, type){
    Swal.fire(title, message, type);
}

function show_alert_event(title, message, type, event){
    Swal.fire(title, message, type).then(function(){ 
            if(event == 'reload'){
                location.reload();
            }
        }
    );
}

function show_alert_confirmation(confirm_title, confirm_text, confirm_icon, confirm_button_text, button_color, confirm_type){
    Swal.fire({
        title: confirm_title,
        text: confirm_text,
        icon: confirm_icon,
        showCancelButton: !0,
        confirmButtonText: confirm_button_text,
        cancelButtonText: "Cancel",
        confirmButtonClass: "btn btn-"+ button_color +" mt-2",
        cancelButtonClass: "btn btn-secondary ms-2 mt-2",
        buttonsStyling: !1
    }).then(function(result) {
        if (result.value) {
            if(confirm_type == 'expired password'){
                var username = $('#username').val();
                
                generate_modal('change password form', 'Change Password', 'R' , '1', '1', 'form', 'change-password-form', '1', username);
            }
        }
    })
}

function create_employee_qr_code(container, name, employee_id, email, mobile){
    document.getElementById(container).innerHTML = '';

    var card = 'BEGIN:VCARD\r\n';
    card += 'VERSION:3.0\r\n';
    card += 'FN:'+ name +'\r\n';
    card += 'EMAIL:' + email +'\r\n';
    card += 'ID NO:[' + employee_id + ']\r\n';

    if(mobile){
        card += 'TEL:' + mobile +'\r\n';
    }
    
    card += 'END:VCARD';

    var qrcode = new QRCode(document.getElementById(container), {
        width: 300,
        height: 300,
        colorDark : "#000000",
        colorLight : "#ffffff",
        correctLevel : QRCode.CorrectLevel.H,
    });

    qrcode.makeCode(card);
}

// Hide
function hide_multiple_buttons(){
    $('#datatable-checkbox').prop('checked', false);

    $('.multiple').addClass('d-none');
    $('.multiple-lock').addClass('d-none');
    $('.multiple-unlock').addClass('d-none');
    $('.multiple-activate').addClass('d-none');
    $('.multiple-deactivate').addClass('d-none');
    $('.multiple-approve').addClass('d-none');
    $('.multiple-reject').addClass('d-none');
    $('.multiple-cancel').addClass('d-none');
    $('.multiple-delete').addClass('d-none');
    $('.multiple-cancel').addClass('d-none');
    $('.multiple-for-recommendation').addClass('d-none');
    $('.multiple-recommendation').addClass('d-none');
    $('.multiple-reject').addClass('d-none');
    $('.multiple-approve').addClass('d-none');
    $('.multiple-send').addClass('d-none');
    $('.multiple-print').addClass('d-none');
}

// Form validation rules
// Rule for password strength
$.validator.addMethod('password_strength', function(value) {
    if(value != ''){
        var re = /^(?=.*\d)(?=.*[!@#$%^&*])(?=.*[a-z])(?=.*[A-Z]).{8,}$/;
        return re.test(value);
    }
    else{
        return true;
    }

}, 'Password must contain at least 1 lowercase, uppercase letter, number, special character and must be 8 characters or longer');

// Rule for legal age
$.validator.addMethod('employee_age', function(value, element, min) {
    var today = new Date();
    var birthDate = new Date(value);
    var age = today.getFullYear() - birthDate.getFullYear();
  
    if (age > min+1) { return true; }
  
    var m = today.getMonth() - birthDate.getMonth();
  
    if (m < 0 || (m === 0 && today.getDate() < birthDate.getDate())) { age--; }
  
    return age >= min;
}, 'The employee must be at least 18 years old and above');