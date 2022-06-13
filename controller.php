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

    # Submit system parameter
    else if($transaction == 'submit system parameter'){
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['parameter_id']) && isset($_POST['parameter']) && !empty($_POST['parameter']) && isset($_POST['extension']) && isset($_POST['parameter_number'])){
            $username = $_POST['username'];
            $parameter_id = $_POST['parameter_id'];
            $parameter = $_POST['parameter'];
            $extension = $_POST['extension'];
            $parameter_number = $api->check_number($_POST['parameter_number']);

            $check_system_parameter_exist = $api->check_system_parameter_exist($parameter_id);

            if($check_system_parameter_exist > 0){
                $update_system_parameter = $api->update_system_parameter($parameter_id, $parameter, $extension, $parameter_number, $username);
                                        
                if($update_system_parameter){
                    echo 'Updated';
                }
                else{
                    echo $update_system_parameter;
                }
            }
            else{
                $insert_system_parameter = $api->insert_system_parameter($parameter, $extension, $parameter_number, $username);
                        
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
        if(isset($_POST['username']) && !empty($_POST['username']) && isset($_POST['employee_id']) && isset($_POST['user_code']) && !empty($_POST['user_code']) && isset($_POST['password']) && isset($_POST['role'])){
            $username = $_POST['username'];
            $employee_id = $_POST['employee_id'];
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
                    $delete_all_role_users = $api->insert_user_account_role($user_code);

                    if($delete_all_role_users){
                        foreach($roles as $role){
                            $insert_user_role = $api->insert_user_role($user_code, $role, $username);

                            if(!$insert_user_role){
                                $error = $insert_user_role;
                                break;
                            }
                        }
                    }
                    else{
                        $error = $delete_all_role_users;
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
                $insert_user_account_role = $api->insert_user_account_role($user_code, $password, $password_expiry_date, $username);

                if($insert_user_account_role){
                    foreach($roles as $role){
                        $insert_user_role = $api->insert_user_role($user_code, $role, $username);

                        if(!$insert_user_role){
                            $error = $insert_user_role;
                            break;
                        }
                    }

                    if(empty($error)){
                        if(!empty($employee_id)){
                            $update_employee_user_account = $api->update_employee_user_account($employee_id, $user_code, $username);

                            if($update_employee_user_account){
                                echo 'Inserted';
                            }
                            else{
                                echo $update_employee_user_account;
                            }
                        }
                        else{
                            echo 'Inserted';
                        }
                    }
                    else{
                        echo $error;
                    }
                }
                else{
                    $error = $insert_user_account_role;
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Submit user account update
    else if($transaction == 'submit user account update'){
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
                    $delete_all_role_users = $api->insert_user_account_role($user_code);

                    if($delete_all_role_users){
                        foreach($roles as $role){
                            $insert_user_role = $api->insert_user_role($user_code, $role, $username);

                            if(!$insert_user_role){
                                $error = $insert_user_role;
                                break;
                            }
                        }
                    }
                    else{
                        $error = $delete_all_role_users;
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
                echo 'Not Found';
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
                $update_user_account_status = $api->update_user_account_status($user_code, 1, $username);
    
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
                    $update_user_account_status = $api->update_user_account_status($user_code, 1, $username);
                                    
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
                $update_user_account_status = $api->update_user_account_status($user_code, 0, $username);
    
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
                    $update_user_account_status = $api->update_user_account_status($user_code, 0, $username);
                                    
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

    # View user account details
    else if($transaction == 'view user account details'){
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
                'FILE_AS' => $user_code,
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

}

?>