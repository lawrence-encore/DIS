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
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="update" value="0">
                                    <label for="user_code" class="form-label">Username <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="user_code" name="user_code" maxlength="50">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Password</label>
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
            else if($form_type == 'system parameter form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="parameter_id" name="parameter_id">
                                    <label for="parameter" class="form-label">Parameter <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="parameter" name="parameter" maxlength="100">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="extension" class="form-label">Extension</label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="extension" name="extension" maxlength="10">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="parameter_number" class="form-label">Number</label>
                                    <input id="parameter_number" name="parameter_number" class="form-control" type="number" min="0">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label for="parameter_description" class="form-label">Parameter Description <span class="text-danger">*</span></label>
                                    <textarea class="form-control form-maxlength" id="parameter_description" name="parameter_description" maxlength="100" rows="5"></textarea>
                                </div>
                            </div>
                        </div>';
                
            }
            else if($form_type == 'system code form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">System Type <span class="text-danger">*</span></label>
                                    <select class="form-control form-select2" id="system_type" name="system_type">
                                    <option value="">--</option>';
                                    $form .= $api->generate_system_code_options('SYSTYPE');
                                    $form .='</select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="system_code" class="form-label">System Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-maxlength" autocomplete="off" id="system_code" name="system_code" maxlength="20">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="system_description" class="form-label">System Description <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-maxlength" autocomplete="off" id="system_description" name="system_description" maxlength="100">
                                    </div>
                                </div>
                            </div>';
            }
            else if($form_type == 'upload setting form'){
                $form .= '<div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="upload_setting" class="form-label">Upload Setting <span class="text-danger">*</span></label>
                                        <input type="hidden" id="upload_setting_id" name="upload_setting_id">
                                        <input type="text" class="form-control form-maxlength" autocomplete="off" id="upload_setting" name="upload_setting" maxlength="200">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <input type="hidden" id="settingid" name="settingid">
                                        <label for="max_file_size" class="form-label">Max File Size (Megabytes) <span class="text-danger">*</span></label>
                                        <input id="max_file_size" name="max_file_size" class="form-control" type="number" min="0">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="file_type" class="form-label">Allowed File Type <span class="text-danger">*</span></label>
                                        <select class="form-control form-select2" multiple="multiple" id="file_type" name="file_type">
                                            '. $api->generate_system_code_options('FILETYPE') .'
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="mb-3">
                                        <label for="description" class="form-label">Description <span class="text-danger">*</span></label>
                                        <textarea class="form-control form-maxlength" id="description" name="description" maxlength="200" rows="5"></textarea>
                                    </div>
                                </div>
                            </div>';
            }
            else if($form_type == 'company form'){
                $form .= '<div class="row mb-3">
                                <input type="hidden" id="company_id" name="company_id">
                                <label for="company_name" class="col-sm-3 col-form-label">Company Name <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="company_name" name="company_name" maxlength="100">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="company_logo" class="col-sm-3 col-form-label">Company Logo</label>
                                <div class="col-sm-9">
                                    <input class="form-control" type="file" name="company_logo" id="company_logo">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="street_1" class="col-sm-3 col-form-label">Address</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="street_1" name="street_1" placeholder="Street" maxlength="200">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="street_2" name="street_2" placeholder="Street 2" maxlength="200">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-sm-3"></div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="city" name="city" placeholder="City" maxlength="100">
                                </div>
                                <div class="col-sm-3">
                                    <select class="form-control form-select2" id="state" name="state">
                                    <option value="">State</option>';
                                    $form .= $api->generate_state_options();
                                    $form .='</select>
                                </div>
                                <div class="col-sm-3">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="zip_code" name="zip_code" placeholder="Zip Code" maxlength="10">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="tax_id" class="col-sm-3 col-form-label">Tax ID</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="tax_id" name="tax_id" maxlength="100">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="email" class="col-sm-3 col-form-label">Email</label>
                                <div class="col-sm-9">
                                    <input id="email" name="email" class="form-control form-maxlength" maxlength="100" autocomplete="off">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="email" class="col-sm-3 col-form-label">Mobile Number</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="mobile" name="mobile" maxlength="30">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="telephone" class="col-sm-3 col-form-label">Telephone</label>
                                <div class="col-sm-9">
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="telephone" name="telephone" maxlength="30">
                                </div>
                            </div>
                            <div class="row mb-3">
                                <label for="website" class="col-sm-3 col-form-label">Website</label>
                                <div class="col-sm-9">
                                    <input type="url" class="form-control form-maxlength" autocomplete="off" id="website" name="website" maxlength="100">
                                </div>
                            </div>';
            }
            else if($form_type == 'country form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="country_id" name="country_id">
                                    <label for="country_name" class="form-label">Country <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="country_name" name="country_name" maxlength="200">
                                </div>
                            </div>
                        </div>';
            }
            else if($form_type == 'state form'){
                $form .= '<div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <input type="hidden" id="state_id" name="state_id">
                                    <label for="state_name" class="form-label">State <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-maxlength" autocomplete="off" id="state_name" name="state_name" maxlength="200">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="mb-3">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select class="form-control form-select2" id="country" name="country">
                                    <option value="">--</option>';
                                    $form .= $api->generate_country_options();
                                    $form .='</select>
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

            if($element_type == 'user account details'){
                $element = '<table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Username :</th>
                                        <td id="user_code"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">User Acount Status :</th>
                                        <td id="active"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Password Expiry Date :</th>
                                        <td id="password_expiry_date"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Failed Login :</th>
                                        <td id="failed_login"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Last Failed Login :</th>
                                        <td id="last_failed_login"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Roles :</th>
                                        <td id="roles"></td>
                                    </tr>
                                </tbody>
                            </table>';
            }
            else if($element_type == 'transaction log'){
                $element = '<table id="transaction-log-datatable" class="table table-bordered align-middle mb-0 table-hover table-striped dt-responsive nowrap w-100">
                                <thead>
                                    <tr>
                                        <th class="all">Log Type</th>
                                        <th class="all">Log</th>
                                        <th class="all">Log Date</th>
                                        <th class="all">Log By</th>
                                    </tr>
                                    </thead>
                                    <tbody></tbody>
                            </table>';
            }
            else if($element_type == 'system parameter details'){
                $element = '<table class="table table-nowrap mb-0">
                                <tbody>
                                    <tr>
                                        <th scope="row">Parameter :</th>
                                        <td id="parameter"></td>
                                    </tr>
                                    <tr>
                                        <th scope="row">Parameter Description :</th>
                                        <td id="parameter_description"></td>
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

    # Transaction log table
    else if($type == 'transaction log table'){
        if(isset($_POST['transaction_log_id']) && !empty($_POST['transaction_log_id'])){
            if ($api->databaseConnection()) {
                $transaction_log_id = $_POST['transaction_log_id'];
    
                $sql = $api->db_connection->prepare('SELECT USERNAME, LOG_TYPE, LOG_DATE, LOG FROM global_transaction_log WHERE TRANSACTION_LOG_ID = :transaction_log_id');
                $sql->bindValue(':transaction_log_id', $transaction_log_id);
    
                if($sql->execute()){
                    while($row = $sql->fetch()){
                        $username = $row['USERNAME'];
                        $log_type = $row['LOG_TYPE'];
                        $log = $row['LOG'];
                        $log_date = $api->check_date('empty', $row['LOG_DATE'], '', 'm/d/Y h:i:s a', '', '', '');
    
                        $response[] = array(
                            'LOG_TYPE' => $log_type,
                            'LOG' => $log,
                            'LOG_DATE' => $log_date,
                            'LOG_BY' => $username
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

    # Policy table
    else if($type == 'policy table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_policy = $api->check_role_permissions($username, 3);
            $delete_policy = $api->check_role_permissions($username, 4);
            $view_transaction_log = $api->check_role_permissions($username, 5);
            $permission_page = $api->check_role_permissions($username, 6);

            $sql = $api->db_connection->prepare('SELECT POLICY_ID, POLICY, POLICY_DESCRIPTION, TRANSACTION_LOG_ID FROM global_policy');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $policy_id = $row['POLICY_ID'];
                    $policy = $row['POLICY'];
                    $policy_description = $row['POLICY_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                    $policy_id_encrypted = $api->encrypt_data($policy_id);

                    if($permission_page > 0){
                        $permission = '<a href="permission.php?id='. $policy_id_encrypted .'" class="btn btn-success waves-effect waves-light" title="View Permission">
                                    <i class="bx bx-list-check font-size-16 align-middle"></i>
                                </a>';
                    }
                    else{
                        $permission = '';
                    }

                    if($update_policy > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-policy" data-policy-id="'. $policy_id .'" title="Edit Policy">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_policy > 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-policy" data-policy-id="'. $policy_id .'" title="Delete Policy">
                            <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
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
                $delete_permission = $api->check_role_permissions($username, 9);
                $view_transaction_log = $api->check_role_permissions($username, 10);
    
                $sql = $api->db_connection->prepare('SELECT PERMISSION_ID, PERMISSION, TRANSACTION_LOG_ID FROM global_permission WHERE POLICY_ID = :policy_id ORDER BY PERMISSION_ID');
                $sql->bindValue(':policy_id', $policy_id);
    
                if($sql->execute()){
                    while($row = $sql->fetch()){
                        $permission_id = $row['PERMISSION_ID'];
                        $permission = $row['PERMISSION'];
                        $transaction_log_id = $row['TRANSACTION_LOG_ID'];
    
                        if($update_permission > 0){
                            $update = '<button type="button" class="btn btn-info waves-effect waves-light update-permission" data-permission-id="'. $permission_id .'" title="Edit Permission">
                                            <i class="bx bx-pencil font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $update = '';
                        }
    
                        if($delete_permission > 0){
                            $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-permission" data-permission-id="'. $permission_id .'" title="Delete Permission">
                                            <i class="bx bx-trash font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $delete = '';
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
            $update_role_permission = $api->check_role_permissions($username, 15);
            $view_transaction_log = $api->check_role_permissions($username, 16);

            $sql = $api->db_connection->prepare('SELECT ROLE_ID, ROLE, ROLE_DESCRIPTION, TRANSACTION_LOG_ID FROM global_role');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $role_id = $row['ROLE_ID'];
                    $role = $row['ROLE'];
                    $role_description = $row['ROLE_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                    $role_id_encrypted = $api->encrypt_data($role_id);

                    if($update_role > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-role" data-role-id="'. $role_id .'" title="Edit Role">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_role > 0 && $role_id != 'RL-1'){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-role" data-role-id="'. $role_id .'" title="Delete Role">
                                    <i class="bx bx-trash font-size-16 align-middle"></i>
                                </button>';
                    }
                    else{
                        $delete = '';
                    }

                    if($update_role_permission > 0){
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

                    if($view_transaction_log > 0 && !empty($transaction_log_id)){
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
                $update_user_account = $api->check_role_permissions($username, 19);
                $lock_user_account = $api->check_role_permissions($username, 20);
                $unlock_user_account = $api->check_role_permissions($username, 21);
                $activate_user_account = $api->check_role_permissions($username, 22);
                $deactivate_user_account = $api->check_role_permissions($username, 23);
                $view_transaction_log = $api->check_role_permissions($username, 24);

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

    # System parameter table
    else if($type == 'system parameter table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_system_parameter = $api->check_role_permissions($username, 21);
            $delete_system_parameter = $api->check_role_permissions($username, 22);
            $view_transaction_log = $api->check_role_permissions($username, 23);

            $sql = $api->db_connection->prepare('SELECT PARAMETER_ID, PARAMETER, PARAMETER_DESCRIPTION, TRANSACTION_LOG_ID FROM global_system_parameters ORDER BY PARAMETER_ID');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $parameter_id = $row['PARAMETER_ID'];
                    $parameter = $row['PARAMETER'];
                    $parameter_description = $row['PARAMETER_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];

                    if($update_system_parameter > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-system-parameter" data-parameter-id="'. $parameter_id .'" title="Edit System Parameter">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_system_parameter > 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-system-parameter" data-parameter-id="'. $parameter_id .'" title="Delete System Parameter">
                        <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
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
                        'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $parameter_id .'">',
                        'PARAMETER_ID' => $parameter_id,
                        'PARAMETER' => $parameter . '<p class="text-muted mb-0">'. $parameter_description .'</p>',
                        'ACTION' => '<div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary waves-effect waves-light view-system-parameter" data-parameter-id="'. $parameter_id .'" title="View System Parameter">
                                <i class="bx bx-show font-size-16 align-middle"></i>
                            </button>
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
    # -------------------------------------------------------------

    # System code table
    else if($type == 'system code table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_system_code = $api->check_role_permissions($username, 32);
            $delete_system_code = $api->check_role_permissions($username, 33);
            $view_transaction_log = $api->check_role_permissions($username, 34);

            $sql = $api->db_connection->prepare('SELECT SYSTEM_TYPE, SYSTEM_CODE, SYSTEM_DESCRIPTION, TRANSACTION_LOG_ID FROM global_system_code ORDER BY SYSTEM_TYPE');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $system_type = $row['SYSTEM_TYPE'];
                    $system_code = $row['SYSTEM_CODE'];
                    $system_description = $row['SYSTEM_DESCRIPTION'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];

                    if($update_system_code > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-system-code" data-system-type="'. $system_type .'" data-system-code="'. $system_code .'" title="Edit System Code">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_system_code > 0 && ($system_type != 'SYSTYPE' || $system_code != 'SYSTYPE')){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-system-code" data-system-type="'. $system_type .'" data-system-code="'. $system_code .'" title="Delete System Code">
                        <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
                    }

                    if($system_type != 'SYSTYPE' || $system_code != 'SYSTYPE'){
                        $check_box = '<input class="form-check-input datatable-checkbox-children" type="checkbox" data-system-type="'. $system_type .'" data-system-code="'. $system_code .'">';
                    }
                    else{
                        $check_box = '';
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
                        'CHECK_BOX' => $check_box,
                        'SYSTEM_TYPE' => $system_type,
                        'SYSTEM_CODE' => $system_code . '<p class="text-muted mb-0">'. $system_description .'</p>',
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
    # -------------------------------------------------------------

    # Upload setting table
    else if($type == 'upload setting table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_upload_setting = $api->check_role_permissions($username, 37);
            $delete_upload_setting = $api->check_role_permissions($username, 38);
            $view_transaction_log = $api->check_role_permissions($username, 39);

            $sql = $api->db_connection->prepare('SELECT UPLOAD_SETTING_ID, UPLOAD_SETTING, DESCRIPTION, MAX_FILE_SIZE, TRANSACTION_LOG_ID FROM global_upload_setting ORDER BY UPLOAD_SETTING');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $file_type = '';
                    $upload_setting_id = $row['UPLOAD_SETTING_ID'];
                    $upload_setting = $row['UPLOAD_SETTING'];
                    $description = $row['DESCRIPTION'];
                    $max_file_size = $row['MAX_FILE_SIZE'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];
                    $upload_file_type_details = $api->get_upload_file_type_details($upload_setting_id);

                    for($i = 0; $i < count($upload_file_type_details); $i++) {
                        $system_code_details = $api->get_system_code_details('FILETYPE', $upload_file_type_details[$i]['FILE_TYPE']);
                        $file_type .= '<span class="badge bg-info font-size-11">'. $system_code_details[0]['SYSTEM_DESCRIPTION'] .'</span> ';

                        if(($i + 1) % 3 == 0){
                            $file_type .= '<br/>';
                        }
                    }

                    if($delete_upload_setting > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-upload-setting" data-upload-setting-id="'. $upload_setting_id .'" title="Edit Upload Setting">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_upload_setting > 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-upload-setting" data-upload-setting-id="'. $upload_setting_id .'" title="Delete Upload Setting">
                                    <i class="bx bx-trash font-size-16 align-middle"></i>
                                </button>';
                    }
                    else{
                        $delete = '';
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
                        'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $upload_setting_id .'">',
                        'UPLOAD_SETTING_ID' => $upload_setting_id,
                        'UPLOAD_SETTING' => $upload_setting . '<p class="text-muted mb-0">'. $description .'</p>',
                        'MAX_FILE_SIZE' => $max_file_size . ' Mb',
                        'FILE_TYPE' => $file_type,
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
    # -------------------------------------------------------------

    # Company table
    else if($type == 'company table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_company = $api->check_role_permissions($username, 42);
            $delete_company = $api->check_role_permissions($username, 43);
            $view_transaction_log = $api->check_role_permissions($username, 44);

            $sql = $api->db_connection->prepare('SELECT COMPANY_ID, COMPANY_NAME, TRANSACTION_LOG_ID FROM global_company');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $company_id = $row['COMPANY_ID'];
                    $company_name = $row['COMPANY_NAME'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];

                    if($update_company > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-company" data-company-id="'. $company_id .'" title="Edit Company">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_company > 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-company" data-company-id="'. $company_id .'" title="Delete Company">
                            <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
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
                        'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $company_id .'">',
                        'COMPANY_ID' => $company_id,
                        'COMPANY_NAME' => $company_name,
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
    # -------------------------------------------------------------

    # Country table
    else if($type == 'country table'){
        if ($api->databaseConnection()) {
            # Get permission
            $update_country = $api->check_role_permissions($username, 47);
            $delete_country = $api->check_role_permissions($username, 48);
            $view_transaction_log = $api->check_role_permissions($username, 49);

            $sql = $api->db_connection->prepare('SELECT COUNTRY_ID, COUNTRY_NAME, TRANSACTION_LOG_ID FROM global_country');

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $country_id = $row['COUNTRY_ID'];
                    $country_name = $row['COUNTRY_NAME'];
                    $transaction_log_id = $row['TRANSACTION_LOG_ID'];

                    if($update_country > 0){
                        $update = '<button type="button" class="btn btn-info waves-effect waves-light update-country" data-country-id="'. $country_id .'" title="Edit Country">
                                        <i class="bx bx-pencil font-size-16 align-middle"></i>
                                    </button>';
                    }
                    else{
                        $update = '';
                    }

                    if($delete_country > 0){
                        $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-country" data-country-id="'. $country_id .'" title="Delete Country">
                            <i class="bx bx-trash font-size-16 align-middle"></i>
                        </button>';
                    }
                    else{
                        $delete = '';
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
                        'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $country_id .'">',
                        'COUNTRY_ID' => $country_id,
                        'COUNTRY_NAME' => $country_name,
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
    # -------------------------------------------------------------

    # State table
    else if($type == 'state table'){
        if(isset($_POST['filter_country'])){
            if ($api->databaseConnection()) {
                # Get permission
                $update_state = $api->check_role_permissions($username, 52);
                $delete_state = $api->check_role_permissions($username, 53);
                $view_transaction_log = $api->check_role_permissions($username, 54);

                $filter_country = $_POST['filter_country'];

                $query = 'SELECT STATE_ID, STATE_NAME, COUNTRY_ID, TRANSACTION_LOG_ID FROM global_state';

                if(!empty($filter_country)){
                    $query .= ' WHERE COUNTRY_ID = :filter_country';
                }
    
                $sql = $api->db_connection->prepare($query);

                if(!empty($filter_country)){
                    $sql->bindValue(':filter_country', $filter_country);
                }
    
                if($sql->execute()){
                    while($row = $sql->fetch()){
                        $state_id = $row['STATE_ID'];
                        $state_name = $row['STATE_NAME'];
                        $country_id = $row['COUNTRY_ID'];

                        $country_details = $api->get_country_details($country_id);
                        $country_name = $country_details[0]['COUNTRY_NAME'];
    
                        if($update_state > 0){
                            $update = '<button type="button" class="btn btn-info waves-effect waves-light update-state" data-state-id="'. $state_id .'" title="Edit State">
                                            <i class="bx bx-pencil font-size-16 align-middle"></i>
                                        </button>';
                        }
                        else{
                            $update = '';
                        }
    
                        if($delete_state > 0){
                            $delete = '<button type="button" class="btn btn-danger waves-effect waves-light delete-state" data-state-id="'. $state_id .'" title="Delete State">
                                <i class="bx bx-trash font-size-16 align-middle"></i>
                            </button>';
                        }
                        else{
                            $delete = '';
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
                            'CHECK_BOX' => '<input class="form-check-input datatable-checkbox-children" type="checkbox" value="'. $state_id .'">',
                            'STATE_ID' => $state_id,
                            'STATE_NAME' => $state_name,
                            'COUNTRY' => $country_name,
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

}

?>