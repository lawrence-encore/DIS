<?php
require('./config/config.php');
require('./classes/api.php');

if(isset($_POST['type']) && !empty($_POST['type']) && isset($_POST['username']) && !empty($_POST['username'])){
    $api = new Api;
    $type = $_POST['type'];
    $username = $_POST['username'];
    $system_date = date('Y-m-d');
    $current_time = date('H:i:s');
    $response = array();

    # -------------------------------------------------------------
    #   Generate elements functions
    # -------------------------------------------------------------

    # System modal
    if($type == 'system modal'){
        if(isset($_POST['title']) && isset($_POST['size']) && isset($_POST['scrollable']) && isset($_POST['submit_button']) && isset($_POST['form_id'])){
            $title = $_POST['title'];
            $size = $api->check_modal_size($_POST['size']);
            $scrollable = $api->check_modal_scrollable($_POST['scrollable']);
            $form_id = $_POST['form_id'];
            $submit_button = $_POST['submit_button'];

            if($submit_button == 1){
                $button = '<button type="submit" class="btn btn-primary" id="submit-form" form="'. $form_id .'">Submit</button>';
            }
            else{
                $button = '';
            }

            $modal = '<div class="modal fade" id="System-Modal" role="dialog" aria-labelledby="modal-'. $form_id .'" aria-hidden="true">
                            <div class="modal-dialog '. $scrollable .' '. $size .'">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="modal-'. $form_id .'">'. $title .'</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body" id="modal-body"></div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                        '. $button .'
                                    </div>
                                </div>
                            </div>
                        </div>';

            $response[] = array(
                'MODAL' => $modal
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------
    
    # System form
    else if($type == 'system form'){
        if(isset($_POST['form_type']) && isset($_POST['form_id'])){
            $form_type = $_POST['form_type'];
            $form_id = $_POST['form_id'];

            $form = '<form class="cmxform" id="'. $form_id .'" method="post" action="#">';

            if($form_type == 'change password form' || $form_type == 'change profile password form'){
                $form .= '<div class="mb-3">
                                <label class="form-label" for="change_username">Password <span class="text-danger">*</span></label>
                                <input type="hidden" id="change_username" name="change_username" value="'. $username .'">
                                <div class="input-group auth-pass-inputgroup">
                                    <input type="password" id="change_password" name="change_password" class="form-control" aria-label="Password" aria-describedby="form-password-addon">
                                    <button class="btn btn-light" type="button" id="form-password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                </div>
                            </div>';
            }
            else if($form_type == 'policy form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="policy_id" name="policy_id">
                                    <label for="policy" class="form-label">Policy <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="policy" name="policy" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="policy_description" class="form-label">Policy Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-maxlength" id="policy_description" name="policy_description" maxlength="200" rows="5"></textarea>
                                </div>
                            </div>
                        </div>';
            }
            else if($form_type == 'permission form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="permission_id" name="permission_id">
                                    <input type="hidden" id="policy_id" name="policy_id">
                                    <label for="permission" class="form-label">Permission <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="permission" name="permission" maxlength="100">
                                </div>
                            </div>
                        </div>';
            }
            else if($form_type == 'role form'){
                $form .= '<div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <input type="hidden" id="role_id" name="role_id">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-maxlength" autocomplete="off" id="role" name="role" maxlength="100">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="role_description" class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control form-maxlength" id="role_description" name="role_description" maxlength="200" rows="5"></textarea>
                                    </div>
                                </div>
                            </div>';
            }
            else if($form_type == 'role permission form'){
                $form .= '<div class="row">
                                <input type="hidden" id="role_id" name="role_id">
                                '. $api->generate_role_permission_form() .'
                            </div>';
            }
            else if($form_type == 'user account form'){
                $form .= '<div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="user_code" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="user_code" name="user_code" maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-6 ">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                    <div class="input-group auth-pass-inputgroup">
                                        <input type="password" id="password" name="password" class="form-control" aria-label="Password" aria-describedby="password-addon">
                                        <button class="btn btn-light" type="button" id="password-addon"><i class="mdi mdi-eye-outline"></i></button>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="role" class="form-label">Role <span class="text-danger">*</span></label>
                                        <select class="form-control form-select2" multiple="multiple" id="role" name="role">
                                            '. $api->generate_role_options() .'
                                        </select>
                                    </div>
                                </div>
                        </div>';
            }

            $form .= '</form>';

            $response[] = array(
                'FORM' => $form
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------
    
    # System element
    else if($type == 'system element'){
        if(isset($_POST['element_type']) && !empty($_POST['element_type']) && isset($_POST['value'])){
            $element_type = $_POST['element_type'];
            $value = $_POST['value'];
            $element = '';

            if($element_type == 'system parameter details'){
                $element = '<table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Parameter :</th>
                                        <td id="parameter"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Extension :</th>
                                        <td id="extension"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Number :</th>
                                        <td id="parameter_number"></td>
                                    </tr>
                                </tbody>
                            </table>';
            }

            $response[] = array(
                'ELEMENT' => $element
            );

            echo json_encode($response);
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Generate table functions
    # -------------------------------------------------------------

    # Policy table
    else if($type == 'policy table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_policy = $api->check_role_permissions($username, 4);
            $delete_policy = $api->check_role_permissions($username, 5);
            $view_transaction_log = $api->check_role_permissions($username, 6);
            $permission_page = $api->check_role_permissions($username, 7);

            $sql = $api->db_connection->prepare('SELECT POLICY_ID, POLICY, POLICY_DESCRIPTION, TRANSACTION_LOG_ID FROM global_policy');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $policy_id = $row['POLICY_ID'];
                    $policy = $row['POLICY'];
                    $policy_description = $row['POLICY_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                    $policy_id_encrypted = $api->encrypt_data($policy_id);

                    if($permission_page == 0){
                        $permission = '<a href="permission.php?id='. $policy_id_encrypted .'" class="btn btn-success waves-effect waves-light" title="View Permission">
                                    <i class="bx bx-list-check font-size-16 align-middle"></i>
                                </a>';
                    }
                    else{
                        $permission = '';
                    }

                    if($update_policy == 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-policy" data-policy-id="'. $policy_id .'" title="Edit Policy">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_policy == 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-policy" data-policy-id="'. $policy_id .'" title="Delete Policy">
                            <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
                    }

                    if($view_transaction_log == 0 && !empty($transaction_log_id)){
                        $transaction_log = '<button type="button" class="btn btn-dark waves-effect waves-light view-transaction-log" data-transaction-log-id="'. $transaction_log_id .'" title="View Transaction Log">
                                                <i class="bx bx-detail font-size-16 align-middle"></i>
                                            </button>';
                    }
                    else{
                        $transaction_log = '';
                    }

                    $response[] = array(
                        'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $policy_id .'">',
                        'POLICY_ID' => $policy_id,
                        'POLICY' => $policy . '<p class="text-muted mb-0">'. $policy_description .'</p>',
                        'ACTION' => '<div class="d-flex gap-2">
                                            '. $update .'
                                            '. $permission .'
                                            '. $transaction_log .'
                                            '. $delete .'
                                        </div>'
                    );
                }

                echo json_encode($response);
            }
            else{
                echo $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # Permission table
    else if($type == 'permission table'){
        if(isset($_POST['policy_id']) && !empty($_POST['policy_id'])){
            if ($api->databaseConnection()) {
                $policy_id = $_POST['policy_id'];
                $policy_details = $api->get_policy_details($policy_id);
                $policy = $policy_details[0]['POLICY'];

                # Get permission
                $update_permission = $api->check_role_permissions($username, 8);
                $delete_permission = $api->check_role_permissions($username, 10);
                $view_transaction_log = $api->check_role_permissions($username, 10);
    
                $sql = $api->db_connection->prepare('SELECT PERMISSION_ID, PERMISSION, TRANSACTION_LOG_ID FROM global_permission WHERE POLICY_ID = :policy_id ORDER BY PERMISSION_ID');
                $sql->bindValue(':policy_id', $policy_id);
    
                if($sql->execute()){
                    while($row = $sql->fetch()){
                        $permission_id = $row['PERMISSION_ID'];
                        $permission = $row['PERMISSION'];
                        $transaction_log_id = $row['TRANSACTION_LOG_ID'];
    
                        if($update_permission == 0){
                            $update = '<button type="button" class="btn btn-info waves-effect waves-light update-permission" data-permission-id="'. $permission_id .'" title="Edit Permission">
                                            <i class="bx bx-pencil font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $update = '';
                        }
    
                        if($delete_permission == 0){
                            $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-permission" data-permission-id="'. $permission_id .'" title="Delete Permission">
                                            <i class="bx bx-trash font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $delete = '';
                        }

                        if($view_transaction_log == 0 && !empty($transaction_log_id)){
                            $transaction_log = '<button type="button" class="btn btn-dark waves-effect waves-light view-transaction-log" data-transaction-log-id="'. $transaction_log_id .'" title="View Transaction Log">
                                                    <i class="bx bx-detail font-size-16 align-middle"></i>
                                                </button>';
                        }
                        else{
                            $transaction_log = '';
                        }
    
                        $response[] = array(
                            'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $permission_id .'">',
                            'PERMISSION_ID' => $permission_id,
                            'PERMISSION' => $permission . '<p class="text-muted mb-0">'. $policy .'</p>',
                            'ACTION' => '<div class="d-flex gap-2">
                                '. $update .'
                                '. $transaction_log .'
                                '. $delete .'
                            </div>'
                        );
                    }
    
                    echo json_encode($response);
                }
                else{
                    echo $sql->errorInfo()[2];
                }
            }
        }
    }
    # -------------------------------------------------------------

    # Role table
    else if($type == 'role table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_role = $api->check_role_permissions($username, 13);
            $delete_role = $api->check_role_permissions($username, 14);
            $view_transaction_log = $api->check_role_permissions($username, 15);
            $update_role_permission = $api->check_role_permissions($username, 16);

            $sql = $api->db_connection->prepare('SELECT ROLE_ID, ROLE, ROLE_DESCRIPTION, TRANSACTION_LOG_ID FROM global_role');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $role_id = $row['ROLE_ID'];
                    $role = $row['ROLE'];
                    $role_description = $row['ROLE_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                    $role_id_encrypted = $api->encrypt_data($role_id);

                    if($update_role == 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-role" data-role-id="'. $role_id .'" title="Edit Role">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_role == 0 && $role_id != 'RL-1'){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-role" data-role-id="'. $role_id .'" title="Delete Role">
                                    <i class="bx bx-trash font-size-16 align-middle"></i>
                                </button>';
                    }
                    else{
                        $delete = '';
                    }

                    if($update_role_permission == 0){
                        $permission = '<button type="button" class="btn btn-success waves-effect waves-light update-role-permission" data-role-id="'. $role_id .'" title="Edit Role permission">
                                        <i class="bx bx-list-check font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $permission = '';
                    }

                    if($role_id != 'RL-1'){
                        $check_box = '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $role_id .'">';
                    }
                    else{
                        $check_box = '';
                    }

                    if($view_transaction_log == 0 && !empty($transaction_log_id)){
                        $transaction_log = '<button type="button" class="btn btn-dark waves-effect waves-light view-transaction-log" data-transaction-log-id="'. $transaction_log_id .'" title="View Transaction Log">
                                                <i class="bx bx-detail font-size-16 align-middle"></i>
                                            </button>';
                    }
                    else{
                        $transaction_log = '';
                    }

                    $response[] = array(
                        'CHECK_BOX' => $check_box,
                        'ROLE' => $role . '<p class="text-muted mb-0">'. $role_description .'</p>',
                        'ACTION' => '<div class="d-flex gap-2">
                            '. $update .'
                            '. $permission .'
                            '. $transaction_log .'
                            '. $delete .'
                        </div>'
                    );
                }

                echo json_encode($response);
            }
            else{
                echo $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # User account table
    else if($type == 'user account table'){
        if(isset($_POST['filter_user_account_lock_status']) && isset($_POST['filter_user_account_status']) && isset($_POST['filter_start_date']) && isset($_POST['filter_end_date'])){
            if ($api->databaseConnection()) {
                # Get permission
                $update_user_account = $api->check_role_permissions($username, 145);
                $lock_user_account = $api->check_role_permissions($username, 146);
                $unlock_user_account = $api->check_role_permissions($username, 147);
                $activate_user_account = $api->check_role_permissions($username, 148);
                $deactivate_user_account = $api->check_role_permissions($username, 149);
                $view_transaction_log = $api->check_role_permissions($username, 150);

                $filter_user_account_lock_status = $_POST['filter_user_account_lock_status'];
                $filter_user_account_status = $_POST['filter_user_account_status'];
                $filter_start_date = $api->check_date('empty', $_POST['filter_start_date'], '', 'Y-m-d', '', '', '');
                $filter_end_date = $api->check_date('empty', $_POST['filter_end_date'], '', 'Y-m-d', '', '', '');

                $query = 'SELECT USERNAME, USER_STATUS, PASSWORD_EXPIRY_DATE, FAILED_LOGIN, TRANSACTION_LOG_ID FROM global_user_account';

                if((!empty($filter_start_date) && !empty($filter_end_date)) || $filter_user_account_status != '' || !empty($filter_user_account_lock_status)){
                    $query .= ' WHERE ';

                    if(!empty($filter_start_date) && !empty($filter_end_date)){
                        $filter[] = 'PASSWORD_EXPIRY_DATE BETWEEN :filter_start_date AND :filter_end_date';
                    }

                    if($filter_user_account_lock_status == 'locked'){
                        $filter[] = 'FAILED_LOGIN >= 5';
                    }
                    else {
                        $filter[] = 'FAILED_LOGIN < 5';
                    }

                    if($filter_user_account_status != ''){
                        $filter[] = 'USER_STATUS = :filter_user_account_status';
                    }

                    if(!empty($filter)){
                        $query .= implode(' AND ', $filter);
                    }
                }
    
                $sql = $api->db_connection->prepare($query);

                if((!empty($filter_start_date) && !empty($filter_end_date)) || $filter_user_account_status != ''){

                    if(!empty($filter_start_date) && !empty($filter_end_date)){
                        $sql->bindValue(':filter_start_date', $filter_start_date);
                        $sql->bindValue(':filter_end_date', $filter_end_date);
                    }

                    if($filter_user_account_status != ''){
                        $sql->bindValue(':filter_user_account_status', $filter_user_account_status);
                    }
                }
    
                if($sql->execute()){
                    while($row = $sql->fetch()){
                        $username = $row['USERNAME'];
                        $user_status = $row['USER_STATUS'];
                        $password_expiry_date = $api->check_date('empty', $row['PASSWORD_EXPIRY_DATE'], '', 'm/d/Y', '', '', '');
                        $failed_login = $row['FAILED_LOGIN'];
                        $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                        $lock_status = $api->get_user_account_lock_status($failed_login)[0]['BADGE'];
                        $account_status = $api->get_user_account_status($user_status)[0]['BADGE'];
                        $password_expiry_date_difference = $api->get_date_difference($system_date, $password_expiry_date);
                        $expiry_difference = 'Expiring in ' . $password_expiry_date_difference[0]['MONTHS'] . ' ' . $password_expiry_date_difference[0]['DAYS'];
    
                        if($update_user_account > 0){
                            $update = '<button type="button" class="btn btn-info waves-effect waves-light update-user-account" data-user-code="'. $username .'" title="Edit User Account">
                                            <i class="bx bx-pencil font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $update = '';
                        }
    
                        if($failed_login >= 5){
                            if($unlock_user_account > 0){
                                $lock_unlock = '<button class="btn btn-info waves-effect waves-light unlock-user-account" title="Unlock User Account" data-user-code="'. $username .'">
                                <i class="bx bx-lock-open-alt font-size-16 align-middle"></i>
                                </button>';
                            }
                            else{
                                $lock_unlock = '';
                            }
    
                            $data_lock = '1';
                        }
                        else{
                            if($lock_user_account > 0){
                                $lock_unlock = '<button class="btn btn-warning waves-effect waves-light lock-user-account" title="Lock User Account" data-user-code="'. $username .'">
                                <i class="bx bx-lock-alt font-size-16 align-middle"></i>
                                </button>';
                            }
                            else{
                                $lock_unlock = '';
                            }
    
                            $data_lock = '0';
                        }
    
                        if($user_status == 'ACTIVE'){
                            if($deactivate_user_account > 0){
                                $active_inactive = '<button class="btn btn-danger waves-effect waves-light deactivate-user-account" title="Deactivate User Account" data-user-code="'. $username .'">
                                <i class="bx bx-x font-size-16 align-middle"></i>
                                </button>';
                            }
                            else{
                                $active_inactive = '';
                            }
    
                            $data_active = '1';
                        }
                        else{
                            if($activate_user_account > 0){
                                $active_inactive = '<button class="btn btn-success waves-effect waves-light activate-user-account" title="Activate User Account" data-user-code="'. $username .'">
                                <i class="bx bx-user-check font-size-16 align-middle"></i>
                                </button>';
                            }
                            else{
                                $active_inactive = '';
                            }
    
                            $data_active = '0';
                        }
    
                        if($view_transaction_log > 0 && !empty($transaction_log_id)){
                            $transaction_log = '<button type="button" class="btn btn-dark waves-effect waves-light view-transaction-log" data-transaction-log-id="'. $transaction_log_id .'" title="View Transaction Log">
                                                    <i class="bx bx-detail font-size-16 align-middle"></i>
                                                </button>';
                        }
                        else{
                            $transaction_log = '';
                        }
    
                        $response[] = array(
                            'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" data-lock="'. $data_lock .'" data-active="'. $data_active .'" value="'. $username .'">',
                            'FILE_AS' => $username,
                            'ACCOUNT_STATUS' => $account_status,
                            'LOCK_STATUS' => $lock_status,
                            'PASSWORD_EXPIRY_DATE' => $password_expiry_date . '<p class="text-muted mb-0">'. $expiry_difference .'</p>',
                            'ACTION' => '<div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary waves-effect waves-light view-user-account" data-user-code="'. $username .'" title="View User Account">
                                    <i class="bx bx-show font-size-16 align-middle"></i>
                                </button>
                                '. $update .'
                                '. $active_inactive .'
                                '. $lock_unlock .'
                                '. $transaction_log .'
                            </div>'
                        );
                    }
    
                    echo json_encode($response);
                }
                else{
                    echo $sql->errorInfo()[2];
                }
            }
        }
    }
    # -------------------------------------------------------------

}

?>