<?php
session_start();
require('config/config.php');
require('classes/api.php');

if(isset($_POST['transaction']) && !empty($_POST['transaction'])){
    $transaction = $_POST['transaction'];
    $api = new Api;
    $system_date = date('Y-m-d');
    $current_time = date('H:i:s');

    # Authenticate
    if($transaction == 'authenticate'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['password']) && !empty($_POST['password'])){
            $username = $_POST['username'];
            $password = $api->encrypt_data($_POST['password']);

            $authenticate = $api->authenticate($username, $password);
            
            if($authenticate == 1){
                $_SESSION['lock'] = 0;
                $_SESSION['logged_in'] = 1;
                $_SESSION['username'] = $username;

                echo 'Authenticated';
            }
            else{
                echo $authenticate;
            }
        }
    }
    # -------------------------------------------------------------

    # Change password
    else if($transaction == 'change password'){
        if(isset($_POST['change_username']) && !empty($_POST['change_username']) && isset($_POST['change_password']) && !empty($_POST['change_password'])){
            $username = $_POST['change_username'];
            $password = $api->encrypt_data($_POST['change_password']);
            $password_expiry_date = $api->format_date('Y-m-d', $system_date, '+6 months');

            $check_user_account_exist = $api->check_user_account_exist($username);

            if($check_user_account_exist){
                $update_user_account_password = $api->update_user_account_password($username, $password, $password_expiry_date);

                if($update_user_account_password){
                    $update_login_attempt = $api->update_login_attempt($username, '', 0, NULL);

                    if($update_login_attempt){
                        echo 'Updated';
                    }
                    else{
                        echo $update_login_attempt;
                    }
                }
                else{
                    echo $update_user_account_password;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Submit transactions
    # -------------------------------------------------------------

    # Submit policy
    else if($transaction == 'submit policy'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['policy_id']) && isset($_POST['policy']) && !empty($_POST['policy']) && isset($_POST['policy_description']) && !empty($_POST['policy_description'])){
            $username = $_POST['username'];
            $policy_id = $_POST['policy_id'];
            $policy = $_POST['policy'];
            $policy_description = $_POST['policy_description'];

            $check_policy_exist = $api->check_policy_exist($policy_id);

            if($check_policy_exist > 0){
                $update_policy = $api->update_policy($policy, $policy_id, $policy_description, $username);

                if($update_policy){
                    echo 'Updated';
                }
                else{
                    echo $update_policy;
                }
            }
            else{
                $insert_policy = $api->insert_policy($policy, $policy_description, $username);

                if($insert_policy){
                    echo 'Inserted';
                }
                else{
                    echo $insert_policy;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit permission
    else if($transaction == 'submit permission'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['permission_id']) && isset($_POST['policy_id']) && !empty($_POST['policy_id']) && isset($_POST['permission']) && !empty($_POST['permission'])){
            $username = $_POST['username'];
            $permission_id = $_POST['permission_id'];
            $policy_id = $_POST['policy_id'];
            $permission = $_POST['permission'];

            $check_permission_exist = $api->check_permission_exist($permission_id);

            if($check_permission_exist > 0){
                $update_permission = $api->update_permission($permission_id, $policy_id, $permission, $username);

                if($update_permission){
                    echo 'Updated';
                }
                else{
                    echo $update_permission;
                }
            }
            else{
                $insert_permission = $api->insert_permission($policy_id, $permission, $username);

                if($insert_permission){
                    echo 'Inserted';
                }
                else{
                    echo $insert_permission;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit role
    else if($transaction == 'submit role'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['role_id']) && isset($_POST['role']) && !empty($_POST['role']) && isset($_POST['role_description']) && !empty($_POST['role_description'])){
            $username = $_POST['username'];
            $role_id = $_POST['role_id'];
            $role = $_POST['role'];
            $role_description = $_POST['role_description'];

            $check_role_exist = $api->check_role_exist($role_id);

            if($check_role_exist > 0){
                $update_role = $api->update_role($role_id, $role, $role_description, $username);

                if($update_role){
                    echo 'Updated';
                }
                else{
                    echo $update_role;
                }
            }
            else{
                $insert_role = $api->insert_role($role, $role_description, $username);

                if($insert_role){
                    echo 'Inserted';
                }
                else{
                    echo $insert_role;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit role permission
    else if($transaction == 'submit role permission'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['role_id']) && !empty($_POST['role_id']) && isset($_POST['permission'])){
            $error = '';
            $username = $_POST['username'];
            $role_id = $_POST['role_id'];
            $permissions = explode(',', $_POST['permission']);
            $role_details = $api->get_role_details($role_id);
            $transaction_log_id = $role_details[0]['TRANSACTION_LOG_ID'];

            $check_role_exist = $api->check_role_exist($role_id);

            if($check_role_exist){
                $delete_permission_role = $api->delete_permission_role($role_id, $username);

                if($delete_permission_role){
                    foreach($permissions as $permission){
                        $insert_permission_role = $api->insert_permission_role($role_id, $permission, $username);

                        if(!$insert_permission_role){
                            $error = $insert_permission_role;
                        }
                    }

                    if(empty($error)){
                        $insert_transaction_log = $api->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated role permission.');
                                    
                        if($insert_transaction_log){
                            echo 'Updated';
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        echo $error;
                    }
                }
                else{
                    echo $delete_permission_role;
                }
            }
            else{
               echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Submit user account
    else if($transaction == 'submit user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code']) && !empty($_POST['user_code']) && isset($_POST['password']) && isset($_POST['role'])){
            $username = $_POST['username'];
            $user_code = $_POST['user_code'];
            $password = $_POST['password'];
            $roles = explode(',', $_POST['role']);
            $password_expiry_date = $api->format_date('Y-m-d', $system_date, '+6 months');

            if(!empty($password)){
                $password = $api->encrypt_data($password);
            }

            $check_user_account_exist = $api->check_user_account_exist($user_code);

            if($check_user_account_exist > 0){
                $update_user_account = $api->update_user_account($user_code, $password, $password_expiry_date, $username);

                if($update_user_account){
                    $delete_all_user_account_role = $api->delete_all_user_account_role($user_code);

                    if($delete_all_user_account_role){
                        foreach($roles as $role){
                            $insert_user_account_role = $api->insert_user_account_role($user_code, $role, $username);

                            if(!$insert_user_account_role){
                                $error = $insert_user_account_role;
                                break;
                            }
                        }
                    }
                    else{
                        $error = $delete_all_user_account_role;
                    }                    
                }
                else{
                    $error = $update_user_account;
                }

                if(empty($error)){
                    echo 'Updated';
                }
                else{
                    echo $error;
                }
            }
            else{
                $insert_user_account = $api->insert_user_account($user_code, $password, $password_expiry_date, $username);

                if($insert_user_account){
                    foreach($roles as $role){
                        $insert_user_account_role = $api->insert_user_account_role($user_code, $role, $username);

                        if(!$insert_user_account_role){
                            $error = $insert_user_account_role;
                            break;
                        }
                    }

                    if(empty($error)){
                        echo 'Inserted';
                    }
                    else{
                        echo $error;
                    }
                }
                else{
                    $error = $insert_user_account;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit system parameter
    else if($transaction == 'submit system parameter'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['parameter_id']) && isset($_POST['parameter']) && !empty($_POST['parameter']) && isset($_POST['parameter_description']) && !empty($_POST['parameter_description']) && isset($_POST['extension']) && isset($_POST['parameter_number'])){
            $username = $_POST['username'];
            $parameter_id = $_POST['parameter_id'];
            $parameter = $_POST['parameter'];
            $parameter_description = $_POST['parameter_description'];
            $extension = $_POST['extension'];
            $parameter_number = $api->check_number($_POST['parameter_number']);

            $check_system_parameter_exist = $api->check_system_parameter_exist($parameter_id);

            if($check_system_parameter_exist > 0){
                $update_system_parameter = $api->update_system_parameter($parameter_id, $parameter, $parameter_description, $extension, $parameter_number, $username);
                                        
                if($update_system_parameter){
                    echo 'Updated';
                }
                else{
                    echo $update_system_parameter;
                }
            }
            else{
                $insert_system_parameter = $api->insert_system_parameter($parameter, $parameter_description, $extension, $parameter_number, $username);
                        
                if($insert_system_parameter){
                    echo 'Inserted';
                }
                else{
                    echo $insert_system_parameter;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit system code
    else if($transaction == 'submit system code'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['system_type']) && !empty($_POST['system_type']) && isset($_POST['system_code']) && !empty($_POST['system_code']) && isset($_POST['system_description']) && !empty($_POST['system_description'])){
            $username = $_POST['username'];
            $system_type = $_POST['system_type'];
            $system_code = $_POST['system_code'];
            $system_description = $_POST['system_description'];

            $check_system_code_exist = $api->check_system_code_exist($system_type, $system_code);
            
            if($check_system_code_exist > 0){
                $update_system_code = $api->update_system_code($system_type, $system_code, $system_description, $username);
                                    
                if($update_system_code){
                    echo 'Updated';
                }
                else{
                    echo $update_system_code;
                }
            }
            else{
                $insert_system_code = $api->insert_system_code($system_type, $system_code, $system_description, $username);
                        
                if($insert_system_code){
                    echo 'Inserted';
                }
                else{
                    echo $insert_system_code;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit upload setting
    else if($transaction == 'submit upload setting'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['upload_setting_id']) && isset($_POST['upload_setting']) && !empty($_POST['upload_setting']) && isset($_POST['description']) && !empty($_POST['description']) && isset($_POST['max_file_size']) && !empty($_POST['max_file_size']) && isset($_POST['file_type']) && !empty($_POST['file_type'])){
            $error = '';
            $username = $_POST['username'];
            $upload_setting_id = $_POST['upload_setting_id'];
            $upload_setting = $_POST['upload_setting'];
            $description = $_POST['description'];
            $max_file_size = $api->remove_comma($_POST['max_file_size']);
            $file_types = explode(',', $_POST['file_type']);

            $check_upload_setting_exist = $api->check_upload_setting_exist($upload_setting_id);

            if($check_upload_setting_exist > 0){
                $update_upload_setting = $api->update_upload_setting($upload_setting_id, $upload_setting, $description, $max_file_size, $username);

                if($update_upload_setting){
                    $delete_all_upload_file_type = $api->delete_all_upload_file_type($upload_setting_id, $username);

                    if($delete_all_upload_file_type){
                        foreach($file_types as $file_type){
                            $insert_upload_file_type = $api->insert_upload_file_type($upload_setting_id, $file_type, $username);

                            if(!$insert_upload_file_type){
                                $error = $insert_upload_file_type;
                            }
                        }
                    }
                    else{
                        $error = $delete_all_upload_file_type;
                    }

                    if(empty($error)){
                        echo 'Updated';
                    }
                    else{
                        echo $error;
                    }
                }
                else{
                    echo $update_upload_setting;
                }
            }
            else{
                $insert_upload_setting = $api->insert_upload_setting($upload_setting, $description, $max_file_size, $file_types, $username);

                if($insert_upload_setting){
                    echo 'Inserted';
                }
                else{
                    echo $insert_upload_setting;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Delete transactions
    # -------------------------------------------------------------

    # Delete policy
    else if($transaction == 'delete policy'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['policy_id']) && !empty($_POST['policy_id'])){
            $username = $_POST['username'];
            $policy_id = $_POST['policy_id'];

            $check_policy_exist = $api->check_policy_exist($policy_id);

            if($check_policy_exist > 0){
                $delete_all_permission = $api->delete_all_permission($policy_id, $username);
                                    
                if($delete_all_permission){
                    $delete_policy = $api->delete_policy($policy_id, $username);
                                    
                    if($delete_policy){
                        echo 'Deleted';
                    }
                    else{
                        echo $delete_policy;
                    }
                }
                else{
                    echo $delete_all_permission;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple policy
    else if($transaction == 'delete multiple policy'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['policy_id'])){
            $username = $_POST['username'];
            $policy_ids = $_POST['policy_id'];

            foreach($policy_ids as $policy_id){
                $check_policy_exist = $api->check_policy_exist($policy_id);

                if($check_policy_exist > 0){
                    $delete_policy = $api->delete_policy($policy_id, $username);
                                    
                    if($delete_policy){
                        $delete_all_permission = $api->delete_all_permission($policy_id, $username);
                                        
                        if(!$delete_all_permission){
                            $error = $delete_all_permission;
                        }
                    }
                    else{
                        $error = $delete_policy;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # Delete permission
    else if($transaction == 'delete permission'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['permission_id']) && !empty($_POST['permission_id'])){
            $username = $_POST['username'];
            $permission_id = $_POST['permission_id'];

            $check_permission_exist = $api->check_permission_exist($permission_id);

            if($check_permission_exist > 0){
                $delete_permission = $api->delete_permission($permission_id, $username);
                                    
                if($delete_permission){
                    echo 'Deleted';
                }
                else{
                    echo $delete_permission;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple permission
    else if($transaction == 'delete multiple permission'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['permission_id'])){
            $username = $_POST['username'];
            $permission_ids = $_POST['permission_id'];

            foreach($permission_ids as $permission_id){
                $check_permission_exist = $api->check_permission_exist($permission_id);

                if($check_permission_exist > 0){
                    $delete_permission = $api->delete_permission($permission_id, $username);
                                        
                    if(!$delete_permission){
                        $error = $delete_permission;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # Delete role
    else if($transaction == 'delete role'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['role_id']) && !empty($_POST['role_id'])){
            $username = $_POST['username'];
            $role_id = $_POST['role_id'];

            $check_role_exist = $api->check_role_exist($role_id);

            if($check_role_exist > 0){
                $delete_role = $api->delete_role($role_id, $username);
                                    
                if($delete_role){
                    $delete_permission_role = $api->delete_permission_role($role_id, $username);
                                    
                    if($delete_permission_role){
                        echo 'Deleted';
                    }
                    else{
                        echo $delete_permission_role;
                    }
                }
                else{
                    echo $delete_role;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple role
    else if($transaction == 'delete multiple role'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['role_id'])){
            $username = $_POST['username'];
            $role_ids = $_POST['role_id'];

            foreach($role_ids as $role_id){
                $check_role_exist = $api->check_role_exist($role_id);

                if($check_role_exist > 0){
                    $delete_role = $api->delete_role($role_id, $username);
                                    
                    if($delete_role){
                        $delete_permission_role = $api->delete_permission_role($role_id, $username);
                                        
                        if(!$delete_permission_role){
                            $error = $delete_permission_role;
                        }
                    }
                    else{
                        $error = $delete_role;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # Delete system parameter
    else if($transaction == 'delete system parameter'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['parameter_id']) && !empty($_POST['parameter_id'])){
            $username = $_POST['username'];
            $parameter_id = $_POST['parameter_id'];

            $check_system_parameter_exist = $api->check_system_parameter_exist($parameter_id);

            if($check_system_parameter_exist > 0){
                $delete_system_parameter = $api->delete_system_parameter($parameter_id, $username);
                                    
                if($delete_system_parameter){
                    echo 'Deleted';
                }
                else{
                    echo $delete_system_parameter;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple system parameter
    else if($transaction == 'delete multiple system parameter'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['parameter_id'])){
            $username = $_POST['username'];
            $parameter_ids = $_POST['parameter_id'];

            foreach($parameter_ids as $parameter_id){
                $check_system_parameter_exist = $api->check_system_parameter_exist($parameter_id);

                if($check_system_parameter_exist > 0){
                    $delete_system_parameter = $api->delete_system_parameter($parameter_id, $username);
                                        
                    if(!$delete_system_parameter){
                        $error = $delete_system_parameter;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # Delete system code
    else if($transaction == 'delete system code'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['system_type']) && !empty($_POST['system_type']) && isset($_POST['system_code']) && !empty($_POST['system_code'])){
            $username = $_POST['username'];
            $system_type = $_POST['system_type'];
            $system_code = $_POST['system_code'];

            $check_system_code_exist = $api->check_system_code_exist($system_type, $system_code);

            if($check_system_code_exist > 0){
                $delete_system_code = $api->delete_system_code($system_type, $system_code, $username);
                                    
                if($delete_system_code){
                    echo 'Deleted';
                }
                else{
                    echo $delete_system_code;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple system code
    else if($transaction == 'delete multiple system code'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['system_type']) && isset($_POST['system_code'])){
            $username = $_POST['username'];
            $system_type = $_POST['system_type'];
            $system_code = $_POST['system_code'];
            $system_type_length = count($system_type);

            for($i = 0; $i < $system_type_length; $i++){
                $check_system_code_exist = $api->check_system_code_exist($system_type[$i], $system_code[$i]);

                if($check_system_code_exist > 0){
                    $delete_system_code = $api->delete_system_code($system_type[$i], $system_code[$i], $username);
                                        
                    if(!$delete_system_code){
                        $error = $delete_system_code;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # Delete upload setting
    else if($transaction == 'delete upload setting'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['upload_setting_id']) && !empty($_POST['upload_setting_id'])){
            $username = $_POST['username'];
            $upload_setting_id = $_POST['upload_setting_id'];

            $check_upload_setting_exist = $api->check_upload_setting_exist($upload_setting_id);

            if($check_upload_setting_exist > 0){
                $delete_upload_setting = $api->delete_upload_setting($upload_setting_id, $username);
                                    
                if($delete_upload_setting){
                    $delete_all_upload_file_type = $api->delete_all_upload_file_type($upload_setting_id, $username);
                                    
                    if($delete_all_upload_file_type){
                        echo 'Deleted';
                    }
                    else{
                        echo $delete_all_upload_file_type;
                    }
                }
                else{
                    echo $delete_upload_setting;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Delete multiple upload setting
    else if($transaction == 'delete multiple upload setting'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['upload_setting_id'])){
            $username = $_POST['username'];
            $upload_setting_ids = $_POST['upload_setting_id'];

            foreach($upload_setting_ids as $upload_setting_id){
                $check_upload_setting_exist = $api->check_upload_setting_exist($upload_setting_id);

                if($check_upload_setting_exist > 0){
                    $delete_upload_setting = $api->delete_upload_setting($upload_setting_id, $username);
                                    
                    if($delete_upload_setting){
                        $delete_all_upload_file_type = $api->delete_all_upload_file_type($upload_setting_id, $username);
                                    
                        if(!$delete_all_upload_file_type){
                            $error = $delete_all_upload_file_type;
                        }                       
                    }
                    else{
                        $error = $delete_upload_setting;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deleted';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Unlock transactions
    # -------------------------------------------------------------

    # Unlock user account
    else if($transaction == 'unlock user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $username = $_POST['username'];
            $user_code = $_POST['user_code'];

            $check_user_account_exist = $api->check_user_account_exist($user_code);

            if($check_user_account_exist > 0){
                $update_user_account_lock_status = $api->update_user_account_lock_status($user_code, 'unlock', $system_date, $username);
    
                if($update_user_account_lock_status){
                    echo 'Unlocked';
                }
                else{
                    echo $update_user_account_lock_status;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Unlock multiple user account
    else if($transaction == 'unlock multiple user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code'])){
            $username = $_POST['username'];
            $user_codes = $_POST['user_code'];

            foreach($user_codes as $user_code){
                $check_user_account_exist = $api->check_user_account_exist($user_code);

                if($check_user_account_exist > 0){
                    $update_user_account_lock_status = $api->update_user_account_lock_status($user_code, 'unlock', $system_date, $username);
                                    
                    if(!$update_user_account_lock_status){
                        $error = $update_user_account_lock_status;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Unlocked';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Lock transactions
    # -------------------------------------------------------------

    # Lock user account
    else if($transaction == 'lock user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $username = $_POST['username'];
            $user_code = $_POST['user_code'];

            $check_user_account_exist = $api->check_user_account_exist($user_code);

            if($check_user_account_exist > 0){
                $update_user_account_lock_status = $api->update_user_account_lock_status($user_code, 'lock', $system_date, $username);
    
                if($update_user_account_lock_status){
                    echo 'Locked';
                }
                else{
                    echo $update_user_account_lock_status;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Lock multiple user account
    else if($transaction == 'lock multiple user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code'])){
            $username = $_POST['username'];
            $user_codes = $_POST['user_code'];

            foreach($user_codes as $user_code){
                $check_user_account_exist = $api->check_user_account_exist($user_code);

                if($check_user_account_exist > 0){
                    $update_user_account_lock_status = $api->update_user_account_lock_status($user_code, 'lock', $system_date, $username);
                                    
                    if(!$update_user_account_lock_status){
                        $error = $update_user_account_lock_status;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Locked';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Activate transactions
    # -------------------------------------------------------------

    # Activate user account
    else if($transaction == 'activate user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $username = $_POST['username'];
            $user_code = $_POST['user_code'];

            $check_user_account_exist = $api->check_user_account_exist($user_code);

            if($check_user_account_exist > 0){
                $update_user_account_status = $api->update_user_account_status($user_code, 'ACTIVE', $username);
    
                if($update_user_account_status){
                    echo 'Activated';
                }
                else{
                    echo $update_user_account_status;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Activate multiple user account
    else if($transaction == 'activate multiple user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code'])){
            $username = $_POST['username'];
            $user_codes = $_POST['user_code'];

            foreach($user_codes as $user_code){
                $check_user_account_exist = $api->check_user_account_exist($user_code);

                if($check_user_account_exist > 0){
                    $update_user_account_status = $api->update_user_account_status($user_code, "ACTIVE", $username);
                                    
                    if(!$update_user_account_status){
                        $error = $update_user_account_status;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Activated';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------
     
    # -------------------------------------------------------------
    #   Deactivate transactions
    # -------------------------------------------------------------

    # Deactivate user account
    else if($transaction == 'deactivate user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $username = $_POST['username'];
            $user_code = $_POST['user_code'];

            $check_user_account_exist = $api->check_user_account_exist($user_code);

            if($check_user_account_exist > 0){
                $update_user_account_status = $api->update_user_account_status($user_code, 'INACTIVE', $username);
    
                if($update_user_account_status){
                    echo 'Deactivated';
                }
                else{
                    echo $update_user_account_status;
                }
            }
            else{
                echo 'Not Found';
            }
        }
    }
    # -------------------------------------------------------------

    # Deactivate multiple user account
    else if($transaction == 'deactivate multiple user account'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['user_code'])){
            $username = $_POST['username'];
            $user_codes = $_POST['user_code'];

            foreach($user_codes as $user_code){
                $check_user_account_exist = $api->check_user_account_exist($user_code);

                if($check_user_account_exist > 0){
                    $update_user_account_status = $api->update_user_account_status($user_code, 'INACTIVE', $username);
                                    
                    if(!$update_user_account_status){
                        $error = $update_user_account_status;
                    }
                }
                else{
                    $error = 'Not Found';
                }
            }

            if(empty($error)){
                echo 'Deactivated';
            }
            else{
                echo $error;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Get details transactions
    # -------------------------------------------------------------

    # Policy details
    else if($transaction == 'policy details'){
        if(isset($_POST['policy_id']) && !empty($_POST['policy_id'])){
            $policy_id = $_POST['policy_id'];
            $policy_details = $api->get_policy_details($policy_id);

            $response[] = array(
                'POLICY' => $policy_details[0]['POLICY'],
                'POLICY_DESCRIPTION' => $policy_details[0]['POLICY_DESCRIPTION']
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # Permission details
    else if($transaction == 'permission details'){
        if(isset($_POST['permission_id']) && !empty($_POST['permission_id'])){
            $permission_id = $_POST['permission_id'];
            $permission_details = $api->get_permission_details($permission_id);

            $response[] = array(
                'PERMISSION' => $permission_details[0]['PERMISSION']
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # Role details
    else if($transaction == 'role details'){
        if(isset($_POST['role_id']) && !empty($_POST['role_id'])){
            $role_id = $_POST['role_id'];
            $role_details = $api->get_role_details($role_id);

            $response[] = array(
                'ROLE' => $role_details[0]['ROLE'],
                'ROLE_DESCRIPTION' => $role_details[0]['ROLE_DESCRIPTION']
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # Role permission details
    else if($transaction == 'role permission details'){
        if(isset($_POST['role_id']) && !empty($_POST['role_id'])){
            $response = array();

            $role_id = $_POST['role_id'];
            $role_permission_details = $api->get_role_permission_details($role_id);

            for($i = 0; $i < count($role_permission_details); $i++) {
                array_push($response, $role_permission_details[$i]['PERMISSION_ID']);
            }

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # User account details
    else if($transaction == 'user account details'){
        if(isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $roles = '';
            $user_code = $_POST['user_code'];
            $role_user_details = $api->get_user_account_role_details('', $user_code);

            for($i = 0; $i < count($role_user_details); $i++) {
                $roles .= $role_user_details[$i]['ROLE_ID'];

                if($i != (count($role_user_details) - 1)){
                    $roles .= ',';
                }
            }

            $response[] = array(
                'ROLES' => $roles
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # User account summary details
    else if($transaction == 'user account summary details'){
        if(isset($_POST['user_code']) && !empty($_POST['user_code'])){
            $roles = '';
            $user_code = $_POST['user_code'];
            
            $user_account_details = $api->get_user_account_details($user_code);
            $role_user_details = $api->get_user_account_role_details('', $user_code);

            for($i = 0; $i < count($role_user_details); $i++) {
                $role_id = $role_user_details[$i]['ROLE_ID'];
                $role_details = $api->get_role_details($role_id);
                $roles .= $role_details[0]['ROLE'];

                if($i != (count($role_user_details) - 1)){
                    $roles .= ', ';
                }
            }

            $account_status = $api->get_user_account_status($user_account_details[0]['USER_STATUS'])[0]['STATUS'];

            $response[] = array(
                'ACTIVE' => $account_status,
                'PASSWORD_EXPIRY_DATE' => $api->check_date('empty', $user_account_details[0]['PASSWORD_EXPIRY_DATE'], '', 'F d, Y', '', '', ''),
                'FAILED_LOGIN' => $user_account_details[0]['FAILED_LOGIN'],
                'LAST_FAILED_LOGIN' => $api->check_date('empty', $user_account_details[0]['LAST_FAILED_LOGIN'], '', 'F d, Y', '', '', ''),
                'ROLES' => $roles
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # System parameter details
    else if($transaction == 'system parameter details'){
        if(isset($_POST['parameter_id']) && !empty($_POST['parameter_id'])){
            $parameter_id = $_POST['parameter_id'];
            $system_parameter_details = $api->get_system_parameter_details($parameter_id);

            $response[] = array(
                'PARAMETER' => $system_parameter_details[0]['PARAMETER'],
                'PARAMETER_DESCRIPTION' => $system_parameter_details[0]['PARAMETER_DESCRIPTION'],
                'PARAMETER_EXTENSION' => $system_parameter_details[0]['PARAMETER_EXTENSION'],
                'PARAMETER_NUMBER' => $system_parameter_details[0]['PARAMETER_NUMBER']
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # System code details
    else if($transaction == 'system code details'){
        if(isset($_POST['system_type']) && !empty($_POST['system_type']) && isset($_POST['system_code']) && !empty($_POST['system_code'])){
            $response = array();

            $system_type = $_POST['system_type'];
            $system_code = $_POST['system_code'];

            $system_code_details = $api->get_system_code_details($system_type, $system_code);

            $response[] = array(
                'SYSTEM_DESCRIPTION' => $system_code_details[0]['SYSTEM_DESCRIPTION']     
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # Upload setting details
    else if($transaction == 'upload setting details'){
        if(isset($_POST['upload_setting_id']) && !empty($_POST['upload_setting_id'])){
            $file_type = '';
            $upload_setting_id = $_POST['upload_setting_id'];
            $upload_setting_details = $api->get_upload_setting_details($upload_setting_id);
            $upload_file_type_details = $api->get_upload_file_type_details($upload_setting_id);

            for($i = 0; $i < count($upload_file_type_details); $i++) {
                $file_type .= $upload_file_type_details[$i]['FILE_TYPE'];

                if($i != (count($upload_file_type_details) - 1)){
                    $file_type .= ',';
                }
            }

            $response[] = array(
                'UPLOAD_SETTING' => $upload_setting_details[0]['UPLOAD_SETTING'],
                'DESCRIPTION' => $upload_setting_details[0]['DESCRIPTION'],
                'MAX_FILE_SIZE' => $upload_setting_details[0]['MAX_FILE_SIZE'],
                'FILE_TYPE' => $file_type
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

}

?>