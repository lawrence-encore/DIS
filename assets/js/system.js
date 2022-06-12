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
        
        var role_id = $('#role-id').text();
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {role_id : role_id, transaction : transaction},
            success: function(response) {
                var userArray = new Array();
                userArray = response.toString().split(',');

                $('.role-permissions').each(function(index) {
                    var val = $(this).val();
                    if (userArray.includes(val)) {
                        $(this).prop('checked', true);
                    }
                });
            }
        });
    }
    else if(form_type == 'system code form'){
        transaction = 'system code details';
        
        var system_type = sessionStorage.getItem('system_type');
        var system_code = sessionStorage.getItem('system_code');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {system_type : system_type, system_code : system_code, transaction : transaction},
            success: function(response) {
                $('#system_description').val(response[0].DESCRIPTION);
                $('#system_code').val(system_code);

                check_option_exist('#system_type', system_type, '');
            },
            complete: function(){
                document.getElementById('system_type').disabled = true;
                document.getElementById('system_code').readOnly = true;
            }
        });
    }
    else if(form_type == 'user interface setting form'){
        transaction = 'user interface settings details';

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                $('#login-bg').attr('src', response[0].LOGIN_BG + '?' + d.getMilliseconds());
                $('#logo-light').attr('src', response[0].LOGO_LIGHT + '?' + d.getMilliseconds());
                $('#logo-dark').attr('src', response[0].LOGO_DARK + '?' + d.getMilliseconds());
                $('#logo-icon-light').attr('src', response[0].LOGO_ICON_LIGHT + '?' + d.getMilliseconds());
                $('#logo-icon-dark').attr('src', response[0].LOGO_ICON_DARK + '?' + d.getMilliseconds());
                $('#favicon-image').attr('src', response[0].FAVICON + '?' + d.getMilliseconds());
            }
        });
    }
    else if(form_type == 'email configuration form'){
        transaction = 'email configuration details';
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                $('#mail_host').val(response[0].MAIL_HOST);
                $('#port').val(response[0].PORT);
                $('#mail_user').val(response[0].USERNAME);
                $('#mail_password').val(response[0].PASSWORD);
                $('#mail_from_name').val(response[0].MAIL_FROM_NAME);
                $('#mail_from_email').val(response[0].MAIL_FROM_EMAIL);

                check_empty(response[0].MAIL_ENCRYPTION, '#mail_encryption', 'select');
                check_empty(response[0].SMTP_AUTH, '#smtp_auth', 'select');
                check_empty(response[0].SMTP_AUTO_TLS, '#smtp_auto_tls', 'select');
            }
        });
    }
    else if(form_type == 'notification type form'){
        transaction = 'notification type details';
        
        var notification_id = sessionStorage.getItem('notification_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {notification_id : notification_id, transaction : transaction},
            success: function(response) {
                $('#notification').val(response[0].NOTIFICATION);
                $('#description').val(response[0].DESCRIPTION);
                $('#notification_id').val(notification_id);
            }
        });
    }
    else if(form_type == 'notification details form'){
        transaction = 'notification details';
        
        var notification_id = $('#notification-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {notification_id : notification_id, transaction : transaction},
            success: function(response) {
                $('#notification_title').val(response[0].NOTIFICATION_TITLE);
                $('#notification_message').val(response[0].NOTIFICATION_MESSAGE);
                $('#system_link').val(response[0].SYSTEM_LINK);
                $('#web_link').val(response[0].WEB_LINK);

                check_empty(response[0].RECIPIENT.split(','), '#notification_recipient', 'select');
            }
        });
    }
    else if(form_type == 'application notification form'){
        transaction = 'application notification details';
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                var userArray = new Array();
                userArray = response.toString().split(',');

                $('.application-notification').each(function(index) {
                    var val = $(this).val();
                    if (userArray.includes(val)) {
                        $(this).prop('checked', true);
                    }
                    else{
                        $(this).prop('checked', false);
                    }
                });
            }
        });
    }
    else if(form_type == 'company setting form'){
        transaction = 'company setting details';

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                $('#company_name').val(response[0].COMPANY_NAME);
                $('#email').val(response[0].EMAIL);
                $('#phone').val(response[0].PHONE);
                $('#telephone').val(response[0].TELEPHONE);
                $('#website').val(response[0].WEBSITE);
                $('#address').val(response[0].ADDRESS);

                $('#company-logo').attr('src', response[0].COMPANY_LOGO + '?' + d.getMilliseconds());

                check_option_exist('#province', response[0].PROVINCE_ID, '');

                generate_city_option(response[0].PROVINCE_ID, response[0].CITY_ID);
            }
        });
    }
    else if(form_type == 'department form'){
        transaction = 'department details';
        
        var department_id = sessionStorage.getItem('department_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {department_id : department_id, transaction : transaction},
            success: function(response) {
                $('#department_id').val(department_id);
                $('#department').val(response[0].DEPARTMENT);
                $('#description').val(response[0].DESCRIPTION);

                check_option_exist('#department_head', response[0].DEPARTMENT_HEAD, '');
                check_option_exist('#parent_department', response[0].PARENT_DEPARTMENT, '');
            }
        });
    }
    else if(form_type == 'designation form'){
        transaction = 'designation details';
        
        var designation_id = sessionStorage.getItem('designation_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {designation_id : designation_id, transaction : transaction},
            success: function(response) {
                $('#designation_id').val(designation_id);
                $('#designation').val(response[0].DESIGNATION);
                $('#description').val(response[0].DESCRIPTION);
            }
        });
    }
    else if(form_type == 'branch form'){
        transaction = 'branch details';
        
        var branch_id = sessionStorage.getItem('branch_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {branch_id : branch_id, transaction : transaction},
            success: function(response) {
                $('#branch_id').val(branch_id);
                $('#branch').val(response[0].BRANCH);
                $('#email').val(response[0].EMAIL);
                $('#phone').val(response[0].PHONE);
                $('#telephone').val(response[0].TELEPHONE);
                $('#address').val(response[0].ADDRESS);
            }
        });
    }
    else if(form_type == 'branch details'){
        transaction = 'branch details';

        var branch_id = sessionStorage.getItem('branch_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {branch_id : branch_id, transaction : transaction},
            success: function(response) {
                $('#branch').text(response[0].BRANCH);
                $('#email').text(response[0].EMAIL);
                $('#phone').text(response[0].PHONE);
                $('#telephone').text(response[0].TELEPHONE);
                $('#address').text(response[0].ADDRESS);
            }
        });
    }
    else if(form_type == 'upload setting form'){
        transaction = 'upload setting details';
        
        var upload_setting_id = sessionStorage.getItem('upload_setting_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {upload_setting_id : upload_setting_id, transaction : transaction},
            success: function(response) {
                $('#upload_setting_id').val(upload_setting_id);
                $('#upload_setting').val(response[0].UPLOAD_SETTING);
                $('#max_file_size').val(response[0].MAX_FILE_SIZE);
                $('#description').val(response[0].DESCRIPTION);
               
                check_empty(response[0].FILE_TYPE.split(','), '#file_type', 'select');
            }
        });
    }
    else if(form_type == 'employment status form'){
        transaction = 'employment status details';

        var employment_status_id = sessionStorage.getItem('employment_status_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {employment_status_id : employment_status_id, transaction : transaction},
            success: function(response) {
                $('#employment_status').val(response[0].EMPLOYMENT_STATUS);
                $('#description').val(response[0].DESCRIPTION);
                $('#employment_status_id').val(employment_status_id);

                check_option_exist('#color_value', response[0].COLOR_VALUE, '');
            }
        });
    }
    else if(form_type == 'employee form'){
        transaction = 'employee details';

        var employee_id = sessionStorage.getItem('employee_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {employee_id : employee_id, transaction : transaction},
            success: function(response) {
                check_option_exist('#employment_status', response[0].EMPLOYMENT_STATUS, '');
                check_option_exist('#suffix', response[0].SUFFIX, '');
                check_option_exist('#department', response[0].DEPARTMENT, '');
                check_option_exist('#designation', response[0].DESIGNATION, '');
                check_option_exist('#branch', response[0].BRANCH, '');
                check_option_exist('#gender', response[0].GENDER, '');

                $('#id_number').val(response[0].ID_NUMBER);
                $('#joining_date').val(response[0].JOIN_DATE);
                $('#permanency_date').val(response[0].PERMANENCY_DATE);
                $('#exit_date').val(response[0].EXIT_DATE);
                $('#exit_reason').val(response[0].EXIT_REASON);
                $('#first_name').val(response[0].FIRST_NAME);
                $('#middle_name').val(response[0].MIDDLE_NAME);
                $('#last_name').val(response[0].LAST_NAME);
                $('#email').val(response[0].EMAIL);
                $('#phone').val(response[0].PHONE);
                $('#telephone').val(response[0].TELEPHONE);
                $('#birthday').val(response[0].BIRTHDAY);
                $('#employee_id').val(employee_id);
            },
            complete: function(){
                document.getElementById('id_number').readOnly = true;
            }
        });
    }
    else if(form_type == 'emergency contact form'){
        transaction = 'emergency contact details';

        var contact_id = sessionStorage.getItem('contact_id');
        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {contact_id : contact_id, transaction : transaction},
            success: function(response) {
                $('#contact_name').val(response[0].NAME);
                $('#address').val(response[0].ADDRESS);
                $('#phone').val(response[0].PHONE);
                $('#email').val(response[0].EMAIL);
                $('#telephone').val(response[0].TELEPHONE);
                $('#contact_id').val(contact_id);
                $('#employee_id').val(employee_id);

                check_option_exist('#relationship', response[0].RELATIONSHIP, '');
                check_option_exist('#province', response[0].PROVINCE, '');

                generate_city_option(response[0].PROVINCE, response[0].CITY);
            }
        });
    }
    else if(form_type == 'employee address form'){
        transaction = 'employee address details';

        var address_id = sessionStorage.getItem('address_id');
        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {address_id : address_id, transaction : transaction},
            success: function(response) {
                $('#address').val(response[0].ADDRESS);
                $('#address_id').val(address_id);
                $('#employee_id').val(employee_id);

                check_option_exist('#address_type', response[0].ADDRESS_TYPE, '');
                check_option_exist('#province', response[0].PROVINCE, '');

                generate_city_option(response[0].PROVINCE, response[0].CITY);
            }
        });
    }
    else if(form_type == 'employee social form'){
        transaction = 'employee social details';

        var social_id = sessionStorage.getItem('social_id');
        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {social_id : social_id, transaction : transaction},
            success: function(response) {
                $('#link').val(response[0].LINK);
                $('#social_id').val(social_id);
                $('#employee_id').val(employee_id);

                check_option_exist('#social_type', response[0].SOCIAL_TYPE, '');
            }
        });
    }
    else if(form_type == 'work shift form'){
        transaction = 'work shift details';

        var work_shift_id = sessionStorage.getItem('work_shift_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {work_shift_id : work_shift_id, transaction : transaction},
            success: function(response) {
                $('#work_shift').val(response[0].WORK_SHIFT);
                $('#description').val(response[0].DESCRIPTION);
                $('#work_shift_id').val(work_shift_id);

                check_option_exist('#work_shift_type', response[0].WORK_SHIFT_TYPE, '');
            }
        });
    }
    else if(form_type == 'work shift regular details' || form_type == 'work shift scheduled details'){
        transaction = 'work shift summary details';

        var work_shift_id = sessionStorage.getItem('work_shift_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {work_shift_id : work_shift_id, transaction : transaction},
            success: function(response) {
                $('#work_shift').text(response[0].WORK_SHIFT);
                $('#description').text(response[0].DESCRIPTION);
                $('#work_shift_type').text(response[0].WORK_SHIFT_TYPE);

                if($('#start_date').length){
                    $('#start_date').text(response[0].START_DATE);
                }

                if($('#end_date').length){
                    $('#end_date').text(response[0].END_DATE);
                }

                document.getElementById('monday').innerHTML = response[0].MONDAY;
                document.getElementById('tuesday').innerHTML = response[0].TUESDAY;
                document.getElementById('wednesday').innerHTML = response[0].WEDNESDAY;
                document.getElementById('thursday').innerHTML = response[0].THURSDAY;
                document.getElementById('friday').innerHTML = response[0].FRIDAY;
                document.getElementById('saturday').innerHTML = response[0].SATURDAY;
                document.getElementById('sunday').innerHTML = response[0].SUNDAY;
                document.getElementById('assigned_to').innerHTML = response[0].EMPLOYEE;
            }
        });
    }
    else if(form_type == 'regular work shift schedule form'){
        transaction = 'work shift schedule details';

        var work_shift_id = sessionStorage.getItem('work_shift_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {work_shift_id : work_shift_id, transaction : transaction},
            success: function(response) {
                $('#monday_start_time').val(response[0].MONDAY_START_TIME);
                $('#monday_end_time').val(response[0].MONDAY_END_TIME);
                $('#monday_lunch_start_time').val(response[0].MONDAY_LUNCH_START_TIME);
                $('#monday_lunch_end_time').val(response[0].MONDAY_LUNCH_END_TIME);
                $('#monday_half_day_mark').val(response[0].MONDAY_HALF_DAY_MARK);
                $('#monday_late_mark').val(response[0].MONDAY_LATE_MARK);

                $('#tuesday_start_time').val(response[0].TUESDAY_START_TIME);
                $('#tuesday_end_time').val(response[0].TUESDAY_END_TIME);
                $('#tuesday_lunch_start_time').val(response[0].TUESDAY_LUNCH_START_TIME);
                $('#tuesday_lunch_end_time').val(response[0].TUESDAY_LUNCH_END_TIME);
                $('#tuesday_half_day_mark').val(response[0].TUESDAY_HALF_DAY_MARK);
                $('#tuesday_late_mark').val(response[0].TUESDAY_LATE_MARK);

                $('#wednesday_start_time').val(response[0].WEDNESDAY_START_TIME);
                $('#wednesday_end_time').val(response[0].WEDNESDAY_END_TIME);
                $('#wednesday_lunch_start_time').val(response[0].WEDNESDAY_LUNCH_START_TIME);
                $('#wednesday_lunch_end_time').val(response[0].WEDNESDAY_LUNCH_END_TIME);
                $('#wednesday_half_day_mark').val(response[0].WEDNESDAY_HALF_DAY_MARK);
                $('#wednesday_late_mark').val(response[0].WEDNESDAY_LATE_MARK);

                $('#thursday_start_time').val(response[0].THURSDAY_START_TIME);
                $('#thursday_end_time').val(response[0].THURSDAY_END_TIME);
                $('#thursday_lunch_start_time').val(response[0].THURSDAY_LUNCH_START_TIME);
                $('#thursday_lunch_end_time').val(response[0].THURSDAY_LUNCH_END_TIME);
                $('#thursday_half_day_mark').val(response[0].THURSDAY_HALF_DAY_MARK);
                $('#thursday_late_mark').val(response[0].THURSDAY_LATE_MARK);

                $('#friday_start_time').val(response[0].FRIDAY_START_TIME);
                $('#friday_end_time').val(response[0].FRIDAY_END_TIME);
                $('#friday_lunch_start_time').val(response[0].FRIDAY_LUNCH_START_TIME);
                $('#friday_lunch_end_time').val(response[0].FRIDAY_LUNCH_END_TIME);
                $('#friday_half_day_mark').val(response[0].FRIDAY_HALF_DAY_MARK);
                $('#friday_late_mark').val(response[0].FRIDAY_LATE_MARK);

                $('#saturday_start_time').val(response[0].SATURDAY_START_TIME);
                $('#saturday_end_time').val(response[0].SATURDAY_END_TIME);
                $('#saturday_lunch_start_time').val(response[0].SATURDAY_LUNCH_START_TIME);
                $('#saturday_lunch_end_time').val(response[0].SATURDAY_LUNCH_END_TIME);
                $('#saturday_half_day_mark').val(response[0].SATURDAY_HALF_DAY_MARK);
                $('#saturday_late_mark').val(response[0].SATURDAY_LATE_MARK);

                $('#sunday_start_time').val(response[0].SUNDAY_START_TIME);
                $('#sunday_end_time').val(response[0].SUNDAY_END_TIME);
                $('#sunday_lunch_start_time').val(response[0].SUNDAY_LUNCH_START_TIME);
                $('#sunday_lunch_end_time').val(response[0].SUNDAY_LUNCH_END_TIME);
                $('#sunday_half_day_mark').val(response[0].SUNDAY_HALF_DAY_MARK);
                $('#sunday_late_mark').val(response[0].SUNDAY_LATE_MARK);

                $('#work_shift_id').val(work_shift_id);
            }
        });
    }
    else if(form_type == 'scheduled work shift schedule form'){
        transaction = 'work shift schedule details';

        var work_shift_id = sessionStorage.getItem('work_shift_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {work_shift_id : work_shift_id, transaction : transaction},
            success: function(response) {
                $('#start_date').val(response[0].START_DATE);
                $('#end_date').val(response[0].END_DATE);

                $('#monday_start_time').val(response[0].MONDAY_START_TIME);
                $('#monday_end_time').val(response[0].MONDAY_END_TIME);
                $('#monday_lunch_start_time').val(response[0].MONDAY_LUNCH_START_TIME);
                $('#monday_lunch_end_time').val(response[0].MONDAY_LUNCH_END_TIME);
                $('#monday_half_day_mark').val(response[0].MONDAY_HALF_DAY_MARK);
                $('#monday_late_mark').val(response[0].MONDAY_LATE_MARK);

                $('#tuesday_start_time').val(response[0].TUESDAY_START_TIME);
                $('#tuesday_end_time').val(response[0].TUESDAY_END_TIME);
                $('#tuesday_lunch_start_time').val(response[0].TUESDAY_LUNCH_START_TIME);
                $('#tuesday_lunch_end_time').val(response[0].TUESDAY_LUNCH_END_TIME);
                $('#tuesday_half_day_mark').val(response[0].TUESDAY_HALF_DAY_MARK);
                $('#tuesday_late_mark').val(response[0].TUESDAY_LATE_MARK);

                $('#wednesday_start_time').val(response[0].WEDNESDAY_START_TIME);
                $('#wednesday_end_time').val(response[0].WEDNESDAY_END_TIME);
                $('#wednesday_lunch_start_time').val(response[0].WEDNESDAY_LUNCH_START_TIME);
                $('#wednesday_lunch_end_time').val(response[0].WEDNESDAY_LUNCH_END_TIME);
                $('#wednesday_half_day_mark').val(response[0].WEDNESDAY_HALF_DAY_MARK);
                $('#wednesday_late_mark').val(response[0].WEDNESDAY_LATE_MARK);

                $('#thursday_start_time').val(response[0].THURSDAY_START_TIME);
                $('#thursday_end_time').val(response[0].THURSDAY_END_TIME);
                $('#thursday_lunch_start_time').val(response[0].THURSDAY_LUNCH_START_TIME);
                $('#thursday_lunch_end_time').val(response[0].THURSDAY_LUNCH_END_TIME);
                $('#thursday_half_day_mark').val(response[0].THURSDAY_HALF_DAY_MARK);
                $('#thursday_late_mark').val(response[0].THURSDAY_LATE_MARK);

                $('#friday_start_time').val(response[0].FRIDAY_START_TIME);
                $('#friday_end_time').val(response[0].FRIDAY_END_TIME);
                $('#friday_lunch_start_time').val(response[0].FRIDAY_LUNCH_START_TIME);
                $('#friday_lunch_end_time').val(response[0].FRIDAY_LUNCH_END_TIME);
                $('#friday_half_day_mark').val(response[0].FRIDAY_HALF_DAY_MARK);
                $('#friday_late_mark').val(response[0].FRIDAY_LATE_MARK);

                $('#saturday_start_time').val(response[0].SATURDAY_START_TIME);
                $('#saturday_end_time').val(response[0].SATURDAY_END_TIME);
                $('#saturday_lunch_start_time').val(response[0].SATURDAY_LUNCH_START_TIME);
                $('#saturday_lunch_end_time').val(response[0].SATURDAY_LUNCH_END_TIME);
                $('#saturday_half_day_mark').val(response[0].SATURDAY_HALF_DAY_MARK);
                $('#saturday_late_mark').val(response[0].SATURDAY_LATE_MARK);

                $('#sunday_start_time').val(response[0].SUNDAY_START_TIME);
                $('#sunday_end_time').val(response[0].SUNDAY_END_TIME);
                $('#sunday_lunch_start_time').val(response[0].SUNDAY_LUNCH_START_TIME);
                $('#sunday_lunch_end_time').val(response[0].SUNDAY_LUNCH_END_TIME);
                $('#sunday_half_day_mark').val(response[0].SUNDAY_HALF_DAY_MARK);
                $('#sunday_late_mark').val(response[0].SUNDAY_LATE_MARK);

                $('#work_shift_id').val(work_shift_id);
            }
        });
    }
    else if(form_type == 'assign work shift form'){
        transaction = 'work shift assignment details';

        var work_shift_id = sessionStorage.getItem('work_shift_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {work_shift_id : work_shift_id, transaction : transaction},
            success: function(response) {
                $('#work_shift_id').val(work_shift_id);

                check_empty(response[0].EMPLOYEE_ID.split(','), '#employee', 'select');
            },
            complete: function(){
                reset_element_validation('#employee');
            }
        });
    }
    else if(form_type == 'employee attendance form'){
        transaction = 'employee attendance details';

        var attendance_id = sessionStorage.getItem('attendance_id');
        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE);
                $('#time_in').val(response[0].TIME_IN);
                $('#time_out_date').val(response[0].TIME_OUT_DATE);
                $('#time_out').val(response[0].TIME_OUT);
                $('#remarks').val(response[0].REMARKS);
                $('#attendance_id').val(attendance_id);
                $('#employee_id').val(employee_id);
            }
        });
    }
    else if(form_type == 'leave type form'){
        transaction = 'leave type details';
        
        var leave_type_id = sessionStorage.getItem('leave_type_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {leave_type_id : leave_type_id, transaction : transaction},
            success: function(response) {
                $('#leave_type_id').val(leave_type_id);
                $('#leave_name').val(response[0].LEAVE_NAME);
                $('#no_leaves').val(response[0].NO_LEAVES);
                $('#description').val(response[0].DESCRIPTION);

                check_option_exist('#paid_status', response[0].PAID_STATUS, '');
            }
        });
    }
    else if(form_type == 'update leave entitlement form'){
        transaction = 'leave entitlement details';
        
        var leave_entitlement_id = sessionStorage.getItem('leave_entitlement_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {leave_entitlement_id : leave_entitlement_id, transaction : transaction},
            success: function(response) {
                $('#leave_entitlement_id').val(leave_entitlement_id);
                $('#no_leaves').val(response[0].NO_LEAVES);
                $('#start_date').val(response[0].START_DATE);
                $('#end_date').val(response[0].END_DATE);

                check_option_exist('#leave_type', response[0].LEAVE_TYPE, '');
                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
            }
        });
    }
    else if(form_type == 'employee leave entitlement form'){
        transaction = 'leave entitlement details';
        
        var leave_entitlement_id = sessionStorage.getItem('leave_entitlement_id');
        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {leave_entitlement_id : leave_entitlement_id, transaction : transaction},
            success: function(response) {
                $('#leave_entitlement_id').val(leave_entitlement_id);
                $('#employee_id').val(employee_id);
                $('#no_leaves').val(response[0].NO_LEAVES);
                $('#start_date').val(response[0].START_DATE);
                $('#end_date').val(response[0].END_DATE);

                check_option_exist('#leave_type', response[0].LEAVE_TYPE, '');
            },
            complete: function(){
                document.getElementById('leave_type').disabled = true;
            }
        });
    }
    else if(form_type == 'leave details'){
        transaction = 'leave details';
        
        var leave_id = sessionStorage.getItem('leave_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {leave_id : leave_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE);
                $('#leave_type').text(response[0].LEAVE_TYPE);
                $('#leave_date').text(response[0].LEAVE_DATE);
                $('#leave_time').text(response[0].LEAVE_TIME);
                $('#leave_reason').text(response[0].LEAVE_REASON);
                $('#leave_status').text(response[0].LEAVE_STATUS);
                $('#decision_by').text(response[0].DECISION_BY);
                $('#decision_remarks').text(response[0].DECISION_REMARKS);
                $('#decision_date').text(response[0].DECISION_DATE);
                $('#decision_time').text(response[0].DECISION_TIME);
            }
        });
    }
    else if(form_type == 'employee file management form'){
        transaction = 'employee file details';
        
        var file_id = sessionStorage.getItem('file_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {file_id : file_id, transaction : transaction},
            success: function(response) {
                $('#update').val('1');
                $('#file_id').val(file_id);
                $('#file_name').val(response[0].FILE_NAME);
                $('#file_date').val(response[0].FILE_DATE);
                $('#remarks').val(response[0].REMARKS);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#file_category', response[0].FILE_CATEGORY, '');
            }
        });
    }
    else if(form_type == 'employee file details'){
        transaction = 'employee file summary details';
        
        var file_id = sessionStorage.getItem('file_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {file_id : file_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#file_name').text(response[0].FILE_NAME);
                $('#file_category').text(response[0].FILE_CATEGORY);
                $('#file').html(response[0].FILE_PATH);
                $('#file_date').text(response[0].FILE_DATE);
                $('#upload_date').text(response[0].UPLOAD_DATE);
                $('#upload_time').text(response[0].UPLOAD_TIME);
                $('#upload_by').text(response[0].UPLOAD_BY);
                $('#remarks').text(response[0].REMARKS);
            }
        });
    }
    else if(form_type == 'employee file form'){
        transaction = 'employee file details';
        
        var file_id = sessionStorage.getItem('file_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {file_id : file_id, transaction : transaction},
            success: function(response) {
                $('#update').val('1');
                $('#file_id').val(file_id);
                $('#employee_id').val(response[0].EMPLOYEE_ID);
                $('#file_name').val(response[0].FILE_NAME);
                $('#file_date').val(response[0].FILE_DATE);
                $('#remarks').val(response[0].REMARKS);

                check_option_exist('#file_category', response[0].FILE_CATEGORY, '');
            }
        });
    }
    else if(form_type == 'employee qr code'){
        transaction = 'employee details';

        var employee_id = $('#employee-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {employee_id : employee_id, transaction : transaction},
            success: function(response) {
                create_employee_qr_code('qr-code', response[0].FIRST_NAME + ' ' + response[0].LAST_NAME, employee_id, response[0].EMAIL, response[0].PHONE);
            },
        });
    }
    else if(form_type == 'user account update form'){
        transaction = 'user account details';

        var user_code = sessionStorage.getItem('user_code');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {user_code : user_code, transaction : transaction},
            success: function(response) {
                $('#user_code').val(user_code);

                check_empty(response[0].ROLES.split(','), '#role', 'select');
            },
            complete: function(){
                document.getElementById('user_code').readOnly = true;
            }
        });
    }
    else if(form_type == 'user account details'){
        transaction = 'view user account details';
        
        var user_code = sessionStorage.getItem('user_code');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {user_code : user_code, transaction : transaction},
            success: function(response) {
                $('#user_code').text(user_code);
                $('#employee').text(response[0].FILE_AS);
                $('#active').text(response[0].ACTIVE);
                $('#password_expiry_date').html(response[0].PASSWORD_EXPIRY_DATE);
                $('#failed_login').text(response[0].FAILED_LOGIN);
                $('#last_failed_login').text(response[0].LAST_FAILED_LOGIN);
                $('#roles').text(response[0].ROLES);
            }
        });
    }
    else if(form_type == 'holiday form'){
        transaction = 'holiday details';

        var holiday_id = sessionStorage.getItem('holiday_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {holiday_id : holiday_id, transaction : transaction},
            success: function(response) {
                $('#holiday_id').val(holiday_id);
                $('#holiday').val(response[0].HOLIDAY);
                $('#holiday_date').val(response[0].HOLIDAY_DATE);

                check_option_exist('#holiday_type', response[0].HOLIDAY_TYPE, '');

                check_empty(response[0].BRANCH.split(','), '#branch', 'select');
            }
        });
    }
    else if(form_type == 'attendance setting form'){
        transaction = 'attendance setting details';
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                $('#maximum_attendance').val(response[0].MAX_ATTENDANCE);
                $('#time_out_allowance').val(response[0].TIME_OUT_ALLOWANCE);
                $('#late_allowance').val(response[0].LATE_ALLOWANCE);
                $('#late_policy').val(response[0].LATE_POLICY);
                $('#early_leaving_policy').val(response[0].EARLY_LEAVING_POLICY);
                $('#overtime_policy').val(response[0].OVERTIME_POLICY);

                if(response[0].ATTENDANCE_CREATION_RECOMMENDATION == 1){
                    $('#attendance_creation_recommendation').prop('checked', true);
                }
                else{
                    $('#attendance_creation_recommendation').prop('checked', false);
                }

                if(response[0].ATTENDANCE_ADJUSTMENT_RECOMMENDATION == 1){
                    $('#attendance_adjustment_recommendation').prop('checked', true);
                }
                else{
                    $('#attendance_adjustment_recommendation').prop('checked', false);
                }
               
                check_empty(response[0].CREATION.split(','), '#attendance_creation_approval', 'select');
                check_empty(response[0].ADJUSTMENT.split(','), '#attendance_adjustment_approval', 'select');
            },
            complete: function(){
                if($('#attendance_creation_recommendation').is(':checked')){
                    document.getElementById('attendance_creation_approval').disabled = false;
                }
                else{
                    document.getElementById('attendance_creation_approval').disabled = true;
                }

                if($('#attendance_adjustment_recommendation').is(':checked')){
                    document.getElementById('attendance_adjustment_approval').disabled = false;
                }
                else{
                    document.getElementById('attendance_adjustment_approval').disabled = true;
                }
            }
        });
    }
    else if(form_type == 'time out form'){
        transaction = 'record attendance details';

        var attendance_id = sessionStorage.getItem('attendance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#time-in-record').text(response[0].TIME_IN_DATE);
                $('#attendance_id').val(attendance_id);
                get_location('');
            }
        });
    }
    else if(form_type == 'employee attendance details'){
        transaction = 'employee attendance summary details';

        var attendance_id = sessionStorage.getItem('attendance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#time_in_date').text(response[0].TIME_IN_DATE);
                $('#time_in').text(response[0].TIME_IN);
                document.getElementById('time_in_location').innerHTML = response[0].TIME_IN_LOCATION;
                $('#time_in_ip_address').text(response[0].TIME_IN_IP_ADDRESS);
                $('#time_in_by').text(response[0].TIME_IN_BY);
                document.getElementById('time_in_behavior').innerHTML = response[0].TIME_IN_BEHAVIOR;
                $('#time_in_note').text(response[0].TIME_IN_NOTE);
                $('#time_out_date').text(response[0].TIME_OUT_DATE);
                $('#time_out').text(response[0].TIME_OUT);
                document.getElementById('time_out_location').innerHTML = response[0].TIME_OUT_LOCATION;
                $('#time_out_ip_address').text(response[0].TIME_OUT_IP_ADDRESS);
                $('#time_out_by').text(response[0].TIME_OUT_BY);
                document.getElementById('time_out_behavior').innerHTML = response[0].TIME_OUT_BEHAVIOR;
                $('#time_out_note').text(response[0].TIME_OUT_NOTE);
                $('#late').text(response[0].LATE);
                $('#early_leaving').text(response[0].EARLY_LEAVING);
                $('#overtime').text(response[0].OVERTIME);
                $('#total_working_hours').text(response[0].TOTAL_WORKING_HOURS);
                $('#remarks').text(response[0].REMARKS);
            }
        });
    }
    else if(form_type == 'attendance record form'){
        transaction = 'attendance record details';

        var attendance_id = sessionStorage.getItem('attendance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE);
                $('#time_in').val(response[0].TIME_IN);
                $('#time_out_date').val(response[0].TIME_OUT_DATE);
                $('#time_out').val(response[0].TIME_OUT);
                $('#remarks').val(response[0].REMARKS);
                $('#attendance_id').val(attendance_id);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
            },
            complete: function(){
                document.getElementById('employee_id').disabled = true;
            }
        });
    }
    else if(form_type == 'attendance adjustment full form'){
        transaction = 'attendance record details';

        var attendance_id = sessionStorage.getItem('attendance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE);
                $('#time_in').val(response[0].TIME_IN);
                $('#time_out_date').val(response[0].TIME_OUT_DATE);
                $('#time_out').val(response[0].TIME_OUT);
                $('#attendance_id').val(attendance_id);
            }
        });
    }
    else if(form_type == 'attendance adjustment partial form'){
        transaction = 'attendance record details';

        var attendance_id = sessionStorage.getItem('attendance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {attendance_id : attendance_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE);
                $('#time_in').val(response[0].TIME_IN);
                $('#attendance_id').val(attendance_id);
            }
        });
    }
    else if(form_type == 'attendance creation form'){
        transaction = 'attendance creation details';

        var request_id = sessionStorage.getItem('request_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {request_id : request_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE);
                $('#time_in').val(response[0].TIME_IN);
                $('#time_out_date').val(response[0].TIME_OUT_DATE);
                $('#time_out').val(response[0].TIME_OUT);
                $('#reason').val(response[0].REASON);
                $('#request_id').val(request_id);
                $('#update').val('1');
            }
        });
    }
    else if(form_type == 'attendance creation details'){
        transaction = 'attendance creation summary details';

        var request_id = sessionStorage.getItem('request_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {request_id : request_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#time_in_date').text(response[0].TIME_IN_DATE);
                $('#time_in').text(response[0].TIME_IN);
                $('#time_out_date').text(response[0].TIME_OUT_DATE);
                $('#time_out').text(response[0].TIME_OUT);
                document.getElementById('attachment').innerHTML = response[0].ATTACHMENT;
                document.getElementById('creation_status').innerHTML = response[0].STATUS;
                document.getElementById('creation_sanction').innerHTML = response[0].SANCTION;
                $('#reason').text(response[0].REASON);
                $('#request_date').text(response[0].REQUEST_DATE);
                $('#request_time').text(response[0].REQUEST_TIME);
                $('#for_recommendation_date').text(response[0].FOR_RECOMMENDATION_DATE);
                $('#for_recommendation_time').text(response[0].FOR_RECOMMENDATION_TIME);
                $('#recommendation_date').text(response[0].RECOMMENDATION_DATE);
                $('#recommendation_time').text(response[0].RECOMMENDATION_TIME);
                $('#recommendation_by').text(response[0].RECOMMENDATION_BY);
                $('#decision_date').text(response[0].DECISION_DATE);
                $('#decision_time').text(response[0].DECISION_TIME);
                $('#decision_remarks').text(response[0].DECISION_REMARKS);
                $('#decision_by').text(response[0].DECISION_BY);
            }
        });
    }
    else if(form_type == 'attendance adjustment details'){
        transaction = 'attendance adjustment summary details';

        var request_id = sessionStorage.getItem('request_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {request_id : request_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#time_in_date').text(response[0].TIME_IN_DATE);
                $('#time_in').text(response[0].TIME_IN);
                $('#time_in_date_adjustment').text(response[0].TIME_IN_DATE_ADJUSTMENT);
                $('#time_in_adjustment').text(response[0].TIME_IN_ADJUSTMENT);
                $('#time_out_date').text(response[0].TIME_OUT_DATE);
                $('#time_out').text(response[0].TIME_OUT);
                $('#time_out_date_adjustment').text(response[0].TIME_OUT_DATE_ADJUSTMENT);
                $('#time_out_adjustment').text(response[0].TIME_OUT_ADJUSTMENT);
                document.getElementById('attachment').innerHTML = response[0].ATTACHMENT;
                document.getElementById('adjustment_status').innerHTML = response[0].STATUS;
                document.getElementById('adjustment_sanction').innerHTML = response[0].SANCTION;
                $('#reason').text(response[0].REASON);
                $('#request_date').text(response[0].REQUEST_DATE);
                $('#request_time').text(response[0].REQUEST_TIME);
                $('#for_recommendation_date').text(response[0].FOR_RECOMMENDATION_DATE);
                $('#for_recommendation_time').text(response[0].FOR_RECOMMENDATION_TIME);
                $('#recommendation_date').text(response[0].RECOMMENDATION_DATE);
                $('#recommendation_time').text(response[0].RECOMMENDATION_TIME);
                $('#recommendation_by').text(response[0].RECOMMENDATION_BY);
                $('#decision_date').text(response[0].DECISION_DATE);
                $('#decision_time').text(response[0].DECISION_TIME);
                $('#decision_remarks').text(response[0].DECISION_REMARKS);
                $('#decision_by').text(response[0].DECISION_BY);
            }
        });
    }
    else if(form_type == 'attendance adjustment full update form'){
        transaction = 'attendance adjustment details';

        var request_id = sessionStorage.getItem('request_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {request_id : request_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE_ADJUSTED);
                $('#time_in').val(response[0].TIME_IN_ADJUSTED);
                $('#time_out_date').val(response[0].TIME_OUT_DATE_ADJUSTED);
                $('#time_out').val(response[0].TIME_OUT_ADJUSTED);
                $('#reason').val(response[0].REASON);
                $('#request_id').val(request_id);
            }
        });
    }
    else if(form_type == 'attendance adjustment partial update form'){
        transaction = 'attendance adjustment details';

        var request_id = sessionStorage.getItem('request_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {request_id : request_id, transaction : transaction},
            success: function(response) {
                $('#time_in_date').val(response[0].TIME_IN_DATE_ADJUSTED);
                $('#time_in').val(response[0].TIME_IN_ADJUSTED);
                $('#reason').val(response[0].REASON);
                $('#request_id').val(request_id);
            }
        });
    }
    else if(form_type == 'allowance type form'){
        transaction = 'allowance type details';
        
        var allowance_type_id = sessionStorage.getItem('allowance_type_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {allowance_type_id : allowance_type_id, transaction : transaction},
            success: function(response) {
                $('#allowance_type_id').val(allowance_type_id);
                $('#allowance_type').val(response[0].ALLOWANCE_TYPE);
                $('#description').val(response[0].DESCRIPTION);

                check_option_exist('#taxable', response[0].TAXABLE, '');
            }
        });
    }
    else if(form_type == 'allowance update form'){
        transaction = 'allowance details';
        
        var allowance_id = sessionStorage.getItem('allowance_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {allowance_id : allowance_id, transaction : transaction},
            success: function(response) {
                $('#allowance_id').val(allowance_id);
                $('#amount').val(response[0].AMOUNT);
                $('#payroll_date').val(response[0].PAYROLL_DATE);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#allowance_type', response[0].ALLOWANCE_TYPE, '');
            }
        });
    }
    else if(form_type == 'allowance details'){
        transaction = 'allowance summary details';

        var allowance_id = sessionStorage.getItem('allowance_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {allowance_id : allowance_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#allowance_type').text(response[0].ALLOWANCE_TYPE);
                $('#payroll_date').text(response[0].PAYROLL_DATE);
                $('#amount').text(response[0].AMOUNT);
                document.getElementById('payroll').innerHTML = response[0].PAYROLL;
            }
        });
    }
    else if(form_type == 'other income type form'){
        transaction = 'other income type details';
        
        var other_income_type_id = sessionStorage.getItem('other_income_type_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {other_income_type_id : other_income_type_id, transaction : transaction},
            success: function(response) {
                $('#other_income_type_id').val(other_income_type_id);
                $('#other_income_type').val(response[0].OTHER_INCOME_TYPE);
                $('#description').val(response[0].DESCRIPTION);

                check_option_exist('#taxable', response[0].TAXABLE, '');
            }
        });
    }
    else if(form_type == 'other income update form'){
        transaction = 'other income details';
        
        var other_income_id = sessionStorage.getItem('other_income_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {other_income_id : other_income_id, transaction : transaction},
            success: function(response) {
                $('#other_income_id').val(other_income_id);
                $('#amount').val(response[0].AMOUNT);
                $('#payroll_date').val(response[0].PAYROLL_DATE);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#other_income_type', response[0].OTHER_INCOME_TYPE, '');
            }
        });
    }
    else if(form_type == 'other income details'){
        transaction = 'other income summary details';

        var other_income_id = sessionStorage.getItem('other_income_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {other_income_id : other_income_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#other_income_type').text(response[0].OTHER_INCOME_TYPE);
                $('#payroll_date').text(response[0].PAYROLL_DATE);
                $('#amount').text(response[0].AMOUNT);
                document.getElementById('payroll').innerHTML = response[0].PAYROLL;
            }
        });
    }
    else if(form_type == 'deduction type form'){
        transaction = 'deduction type details';
        
        var deduction_type_id = sessionStorage.getItem('deduction_type_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {deduction_type_id : deduction_type_id, transaction : transaction},
            success: function(response) {
                $('#deduction_type_id').val(deduction_type_id);
                $('#deduction_type').val(response[0].DEDUCTION_TYPE);
                $('#description').val(response[0].DESCRIPTION);
            }
        });
    }
    else if(form_type == 'government contribution form'){
        transaction = 'government contribution details';
        
        var government_contribution_id = sessionStorage.getItem('government_contribution_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {government_contribution_id : government_contribution_id, transaction : transaction},
            success: function(response) {
                $('#government_contribution_id').val(government_contribution_id);
                $('#government_contribution').val(response[0].GOVERNMENT_CONTRIBUTION);
                $('#description').val(response[0].DESCRIPTION);
            }
        });
    }
    else if(form_type == 'contribution bracket form'){
        transaction = 'contribution bracket details';
        
        var contribution_bracket_id = sessionStorage.getItem('contribution_bracket_id');
        var government_contribution_id = $('#government-contribution-id').text();
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {contribution_bracket_id : contribution_bracket_id, transaction : transaction},
            success: function(response) {
                $('#contribution_bracket_id').val(contribution_bracket_id);
                $('#government_contribution_id').val(government_contribution_id);
                $('#start_range').val(response[0].START_RANGE);
                $('#end_range').val(response[0].END_RANGE);
                $('#deduction_amount').val(response[0].DEDUCTION_AMOUNT);
            }
        });
    }
    else if(form_type == 'deduction update form'){
        transaction = 'deduction details';
        
        var deduction_id = sessionStorage.getItem('deduction_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {deduction_id : deduction_id, transaction : transaction},
            success: function(response) {
                $('#deduction_id').val(deduction_id);
                $('#amount').val(response[0].AMOUNT);
                $('#payroll_date').val(response[0].PAYROLL_DATE);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#deduction_type', response[0].DEDUCTION_TYPE, '');
            }
        });
    }
    else if(form_type == 'deduction details'){
        transaction = 'deduction summary details';

        var deduction_id = sessionStorage.getItem('deduction_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {deduction_id : deduction_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#deduction_type').text(response[0].DEDUCTION_TYPE);
                $('#payroll_date').text(response[0].PAYROLL_DATE);
                $('#amount').text(response[0].AMOUNT);
                document.getElementById('payroll').innerHTML = response[0].PAYROLL;
            }
        });
    }
    else if(form_type == 'contribution deduction update form'){
        transaction = 'contribution deduction details';
        
        var contribution_deduction_id = sessionStorage.getItem('contribution_deduction_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {contribution_deduction_id : contribution_deduction_id, transaction : transaction},
            success: function(response) {
                $('#contribution_deduction_id').val(contribution_deduction_id);
                $('#payroll_date').val(response[0].PAYROLL_DATE);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#government_contribution', response[0].GOVERNMENT_CONTRIBUTION_TYPE, '');
            }
        });
    }
    else if(form_type == 'contribution deduction details'){
        transaction = 'contribution deduction summary details';

        var contribution_deduction_id = sessionStorage.getItem('contribution_deduction_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {contribution_deduction_id : contribution_deduction_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#government_contribution_type').text(response[0].GOVERNMENT_CONTRIBUTION_TYPE);
                $('#payroll_date').text(response[0].PAYROLL_DATE);
                document.getElementById('payroll').innerHTML = response[0].PAYROLL;
            }
        });
    }
    else if(form_type == 'salary update form'){
        transaction = 'salary details';
        
        var salary_id = sessionStorage.getItem('salary_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {salary_id : salary_id, transaction : transaction},
            success: function(response) {
                $('#salary_id').val(salary_id);
                $('#salary_amount').val(response[0].SALARY_AMOUNT);
                $('#hours_per_week').val(response[0].HOURS_PER_WEEK);
                $('#hours_per_day').val(response[0].HOURS_PER_DAY);
                $('#minute_rate').val(response[0].MINUTE_RATE);
                $('#hourly_rate').val(response[0].HOURLY_RATE);
                $('#daily_rate').val(response[0].DAILY_RATE);
                $('#weekly_rate').val(response[0].WEEKLY_RATE);
                $('#bi_weekly_rate').val(response[0].BI_WEEKLY_RATE);
                $('#monthly_rate').val(response[0].MONTHLY_RATE);
                $('#effectivity_date').val(response[0].EFFECTIVITY_DATE);
                $('#remarks').val(response[0].REMARKS);

                check_option_exist('#employee_id', response[0].EMPLOYEE_ID, '');
                check_option_exist('#salary_frequency', response[0].SALARY_FREQUENCY, '');
            }
        });
    }
    else if(form_type == 'salary details'){
        transaction = 'salary summary details';
        
        var salary_id = sessionStorage.getItem('salary_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {salary_id : salary_id, transaction : transaction},
            success: function(response) {
                $('#employee').text(response[0].EMPLOYEE_ID);
                $('#salary_amount').text(response[0].SALARY_AMOUNT);
                $('#salary_frequency').text(response[0].SALARY_FREQUENCY);
                $('#hours_per_week').text(response[0].HOURS_PER_WEEK);
                $('#hours_per_day').text(response[0].HOURS_PER_DAY);
                $('#minute_rate').text(response[0].MINUTE_RATE);
                $('#hourly_rate').text(response[0].HOURLY_RATE);
                $('#daily_rate').text(response[0].DAILY_RATE);
                $('#weekly_rate').text(response[0].WEEKLY_RATE);
                $('#bi_weekly_rate').text(response[0].BI_WEEKLY_RATE);
                $('#monthly_rate').text(response[0].MONTHLY_RATE);
                $('#effectivity_date').text(response[0].EFFECTIVITY_DATE);
                $('#remarks').text(response[0].REMARKS);
            }
        });
    }
    else if(form_type == 'payroll setting form'){
        transaction = 'payroll setting details';
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {transaction : transaction},
            success: function(response) {
                $('#late_deduction_rate').val(response[0].LATE_DEDUCTION_RATE);
                $('#early_leaving_deduction_rate').val(response[0].EARLY_LEAVING_DEDUCTION_RATE);
                $('#overtime_rate').val(response[0].OVERTIME_RATE);
                $('#night_differential_rate').val(response[0].NIGHT_DIFFERENTIAL_RATE);
            }
        });
    }
    else if(form_type == 'payroll group form'){
        transaction = 'payroll group details';
        
        var payroll_group_id = sessionStorage.getItem('payroll_group_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {payroll_group_id : payroll_group_id, transaction : transaction},
            success: function(response) {
                $('#payroll_group_id').val(payroll_group_id);
                $('#payroll_group').val(response[0].PAYROLL_GROUP);
                $('#description').val(response[0].DESCRIPTION);
               
                check_empty(response[0].EMPLOYEE_ID.split(','), '#employee_id', 'select');
            }
        });
    }
    else if(form_type == 'payroll group details'){
        transaction = 'payroll group summary details';
        
        var payroll_group_id = sessionStorage.getItem('payroll_group_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {payroll_group_id : payroll_group_id, transaction : transaction},
            success: function(response) {
                document.getElementById('employee').innerHTML = response[0].EMPLOYEE;
                $('#payroll_group').text(response[0].PAYROLL_GROUP);
                $('#description').text(response[0].DESCRIPTION);
            }
        });
    }
    else if(form_type == 'pay run details'){
        transaction = 'pay run summary details';
        
        var pay_run_id = sessionStorage.getItem('pay_run_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {pay_run_id : pay_run_id, transaction : transaction},
            success: function(response) {
                document.getElementById('payees').innerHTML = response[0].PAYEE;
                document.getElementById('consider_overtime').innerHTML = response[0].CONSIDER_OVERTIME;
                document.getElementById('consider_withholding_tax').innerHTML = response[0].CONSIDER_WITHHOLDING_TAX;
                document.getElementById('pay_run_status').innerHTML = response[0].STATUS;

                $('#pay_run_id').text(pay_run_id);
                $('#start_date').text(response[0].START_DATE);
                $('#end_date').text(response[0].END_DATE);
                $('#payslip_note').text(response[0].PAYSLIP_NOTE);
                $('#generated_date').text(response[0].GENERATION_DATE);
                $('#generated_time').text(response[0].GENERATION_TIME);
                $('#generated_by').text(response[0].GENERATED_BY);
            }
        });
    }
    else if(form_type == 'withholding tax form'){
        transaction = 'withholding tax details';
        
        var withholding_tax_id = sessionStorage.getItem('withholding_tax_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {withholding_tax_id : withholding_tax_id, transaction : transaction},
            success: function(response) {
                $('#withholding_tax_id').val(withholding_tax_id);
                $('#start_range').val(response[0].START_RANGE);
                $('#end_range').val(response[0].END_RANGE);
                $('#fix_compensation_level').val(response[0].FIX_COMPENSATION_LEVEL);
                $('#base_tax').val(response[0].BASE_TAX);
                $('#percent_over').val(response[0].PERCENT_OVER);

                check_option_exist('#salary_frequency', response[0].SALARY_FREQUENCY, '');
            }
        });
    }
    else if(form_type == 'payslip details'){
        transaction = 'payslip summary details';
        
        var payslip_id = sessionStorage.getItem('payslip_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {payslip_id : payslip_id, transaction : transaction},
            success: function(response) {
                document.getElementById('company_details').innerHTML = response[0].COMPANY_DETAILS;
                document.getElementById('generated_date').innerHTML = response[0].GENERATED_DATE;
                document.getElementById('employee_details').innerHTML = response[0].EMPLOYEE_DETAILS;
                document.getElementById('payrun_details').innerHTML = response[0].PAYRUN_DETAILS;
                document.getElementById('earnings_table').innerHTML = response[0].EARNINGS_TABLE;
                document.getElementById('deductions_table').innerHTML = response[0].DEDUCTIONS_TABLE;
                document.getElementById('company_logo').innerHTML = response[0].COMPANY_LOGO;

                $('#payslip_id').text('# ' + payslip_id);
            }
        });
    }
    else if(form_type == 'job category form'){
        transaction = 'job category details';

        var job_category_id = sessionStorage.getItem('job_category_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {job_category_id : job_category_id, transaction : transaction},
            success: function(response) {
                $('#job_category').val(response[0].JOB_CATEGORY);
                $('#description').val(response[0].DESCRIPTION);
                $('#job_category_id').val(job_category_id);
            }
        });
    }
    else if(form_type == 'job type form'){
        transaction = 'job type details';

        var job_type_id = sessionStorage.getItem('job_type_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {job_type_id : job_type_id, transaction : transaction},
            success: function(response) {
                $('#job_type').val(response[0].JOB_TYPE);
                $('#description').val(response[0].DESCRIPTION);
                $('#job_type_id').val(job_type_id);
            }
        });
    }
    else if(form_type == 'recruitment pipeline form'){
        transaction = 'recruitment pipeline details';

        var recruitment_pipeline_id = sessionStorage.getItem('recruitment_pipeline_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {recruitment_pipeline_id : recruitment_pipeline_id, transaction : transaction},
            success: function(response) {
                $('#recruitment_pipeline').val(response[0].RECRUITMENT_PIPELINE);
                $('#description').val(response[0].DESCRIPTION);
                $('#recruitment_pipeline_id').val(recruitment_pipeline_id);
            }
        });
    }
    else if(form_type == 'recruitment pipeline stage form'){
        transaction = 'recruitment pipeline stage details';

        var recruitment_pipeline_stage_id = sessionStorage.getItem('recruitment_pipeline_stage_id');
        var recruitment_pipeline_id = $('#recruitment-pipeline-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {recruitment_pipeline_stage_id : recruitment_pipeline_stage_id, transaction : transaction},
            success: function(response) {
                $('#recruitment_pipeline_stage').val(response[0].RECRUITMENT_PIPELINE_STAGE);
                $('#description').val(response[0].DESCRIPTION);
                $('#recruitment_pipeline_id').val(recruitment_pipeline_id);
                $('#recruitment_pipeline_stage_id').val(recruitment_pipeline_stage_id);
            }
        });
    }
    else if(form_type == 'recruitment scorecard form'){
        transaction = 'recruitment scorecard details';

        var recruitment_scorecard_id = sessionStorage.getItem('recruitment_scorecard_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {recruitment_scorecard_id : recruitment_scorecard_id, transaction : transaction},
            success: function(response) {
                $('#recruitment_scorecard').val(response[0].RECRUITMENT_SCORECARD);
                $('#description').val(response[0].DESCRIPTION);
                $('#recruitment_scorecard_id').val(recruitment_scorecard_id);
            }
        });
    }
    else if(form_type == 'recruitment scorecard section form'){
        transaction = 'recruitment scorecard section details';

        var recruitment_scorecard_section_id = sessionStorage.getItem('recruitment_scorecard_section_id');
        var recruitment_scorecard_id = $('#recruitment-scorecard-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {recruitment_scorecard_section_id : recruitment_scorecard_section_id, transaction : transaction},
            success: function(response) {
                $('#recruitment_scorecard_section').val(response[0].RECRUITMENT_SCORECARD_SECTION);
                $('#description').val(response[0].DESCRIPTION);
                $('#recruitment_scorecard_id').val(recruitment_scorecard_id);
                $('#recruitment_scorecard_section_id').val(recruitment_scorecard_section_id);
            }
        });
    }
    else if(form_type == 'recruitment scorecard section option form'){
        transaction = 'recruitment scorecard section option details';

        var recruitment_scorecard_section_option_id = sessionStorage.getItem('recruitment_scorecard_section_option_id');
        var recruitment_scorecard_section_id = $('#recruitment-scorecard-section-id').text();

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {recruitment_scorecard_section_option_id : recruitment_scorecard_section_option_id, transaction : transaction},
            success: function(response) {
                $('#recruitment_scorecard_section_option').val(response[0].RECRUITMENT_SCORECARD_SECTION_OPTION);
                $('#recruitment_scorecard_section_id').val(recruitment_scorecard_section_id);
                $('#recruitment_scorecard_section_option_id').val(recruitment_scorecard_section_option_id);
            }
        });
    }
    else if(form_type == 'job form'){
        transaction = 'job details';

        var job_id = sessionStorage.getItem('job_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {job_id : job_id, transaction : transaction},
            success: function(response) {
                $('#job_id').val(job_id);
                $('#job_title').val(response[0].JOB_TITLE);
                $('#salary_amount').val(response[0].SALARY);
                $('#description').val(response[0].DESCRIPTION);

                check_empty(response[0].JOB_CATEGORY, '#job_category', 'select');
                check_empty(response[0].JOB_TYPE, '#job_type', 'select');
                check_empty(response[0].PIPELINE, '#recruitment_pipeline', 'select');
                check_empty(response[0].SCORECARD, '#recruitment_scorecard', 'select');

                check_empty(response[0].TEAM_MEMBER.split(','), '#team_member', 'select');
                check_empty(response[0].BRANCH.split(','), '#branch_id', 'select');
            }
        });
    }
    else if(form_type == 'job details'){
        transaction = 'job summary details';

        var job_id = sessionStorage.getItem('job_id');

        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {job_id : job_id, transaction : transaction},
            success: function(response) {
                $('#job_title').text(response[0].JOB_TITLE);
                $('#job_category').text(response[0].JOB_CATEGORY);
                $('#job_type').text(response[0].JOB_TYPE);
                $('#pipeline').text(response[0].PIPELINE);
                $('#scorecard').text(response[0].SCORECARD);
                $('#salary').text(response[0].SALARY);
                $('#description').text(response[0].DESCRIPTION);
                $('#created_date').text(response[0].CREATED_DATE);
                $('#created_time').text(response[0].CREATED_TIME);
                $('#created_by').text(response[0].CREATED_BY);

                document.getElementById('job_status').innerHTML = response[0].STATUS;
                document.getElementById('branch').innerHTML = response[0].BRANCH;
                document.getElementById('team_member').innerHTML = response[0].TEAM_MEMBER;
            }
        });
    }
    else if(form_type == 'job applicant form'){
        transaction = 'job applicant details';
        
        var applicant_id = sessionStorage.getItem('applicant_id');
  
        $.ajax({
            url: 'controller.php',
            method: 'POST',
            dataType: 'JSON',
            data: {applicant_id : applicant_id, transaction : transaction},
            success: function(response) {
                $('#update').val('1');
                $('#applicant_id').val(applicant_id);
                $('#first_name').val(response[0].FIRST_NAME);
                $('#middle_name').val(response[0].MIDDLE_NAME);
                $('#last_name').val(response[0].LAST_NAME);
                $('#application_date').val(response[0].APPLICATION_DATE);
                $('#birthday').val(response[0].BIRTHDAY);
                $('#email').val(response[0].EMAIL);
                $('#phone').val(response[0].PHONE);
                $('#telephone').val(response[0].TELEPHONE);

                check_option_exist('#suffix', response[0].SUFFIX, '');
                check_option_exist('#applied_for', response[0].APPLIED_FOR, '');
                check_option_exist('#gender', response[0].GENDER, '');
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