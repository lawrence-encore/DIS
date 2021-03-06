<?php
require('assets/libs/PHPMailer/src/Exception.php');
require('assets/libs/PHPMailer/src/PHPMailer.php');
require('assets/libs/PHPMailer/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

class Api{
    # @var object $db_connection The database connection
    public $db_connection = null;

    public $response = array();

    # -------------------------------------------------------------
    #   Custom methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : databaseConnection
    # Purpose    : Checks if database connection is opened.
    #              If not, then this method tries to open it.
    #              @return bool Success status of the
    #              database connecting process
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function databaseConnection(){
        // if connection already exists
        if ($this->db_connection != null) {
            return true;
        } 
        else {
            try {
                $this->db_connection = new PDO('mysql:host='. DB_HOST .';dbname='. DB_NAME . ';character_set=utf8', DB_USER, DB_PASS);
                return true;
            } 
            catch (PDOException $e) {
                $this->errors[] = $e->getMessage();
            }
        }
        // default return
        return false;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : backup_database
    # Purpose    : Backs-up the database.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function backup_database($file_name, $username){
        if ($this->databaseConnection()) {
            $backup_file = 'backup/' . $file_name . '_' . time() . '.sql';
            
            exec('C:\xampp\mysql\bin\mysqldump.exe --routines -u '. DB_USER .' -p'. DB_PASS .' '. DB_NAME .' -r "'. $backup_file .'"  2>&1', $output, $return);

            if(!$return) {
                return true;
            }
            else {
                return $return;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : format_date
    # Purpose    : Returns date with a custom formatting
    #              Avoids error when date is greater 
    #              than the year 2038 or less than 
    #              January 01, 1970.
    #
    # Returns    : Date
    #
    # -------------------------------------------------------------
    public function format_date($format, $date, $modify){
        if(!empty($modify)){
            $datestring = (new DateTime($date))->modify($modify)->format($format);
        }
        else{
            $datestring = (new DateTime($date))->format($format);
        }

        return $datestring;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : CryptRC4
    # Purpose    : Returns the encrypted password using RC4-40.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function CryptRC4($text) {
        return openssl_encrypt($text, 'RC4-40', ENCRYPTION_KEY, 1 | 2);
    }
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : ToHexDump
    # Purpose    : Encrypt the text or password to binary hex.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function ToHexDump($text) {
        return bin2hex($text);
    }
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : FromHexDump
    # Purpose    : Decrypt the text or password to binary hex.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function FromHexDump($text) {
        return hex2bin($text);
    }
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : encrypt_data
    # Purpose    : Encrypt the text.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function encrypt_data($text) {
        return $this->ToHexDump($this->CryptRC4($text));
    }
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : decrypt_data
    # Purpose    : Decrypt the text.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function decrypt_data($text) {
        return $this->CryptRC4($this->FromHexDump($text));
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : remove_comma
    # Purpose    : Removes comma from number.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function remove_comma($number){
        return str_replace(',', '', $number);
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : add_months
    # Purpose    : Add months to calculated date.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function add_months($months, DateTime $dateObject){
        # Format date to Y-m-d
        # Get the last day of the given month
        $next = new DateTime($dateObject->format('Y-m-d'));
        $next->modify('last day of +'.$months.' month');
    
        # If $dateObject day is greater than the day of $next
        # Return the difference
        # Else create a new interval
        if($dateObject->format('d') > $next->format('d')) {
            return $dateObject->diff($next);
        } else {
            return new DateInterval('P'.$months.'M');
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : validate_email
    # Purpose    : Validate if email is valid.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function validate_email($email){
        $regex = "/^([a-zA-Z0-9\.]+@+[a-zA-Z]+(\.)+[a-zA-Z]{2,3})$/";

        if (preg_match($regex, $email)) {
            return true;
        }
        else{
            return 'The email is not valid';
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : authenticate
    # Purpose    : Authenticates the user.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function authenticate($username, $password){
        if ($this->databaseConnection()) {
            $system_date = date('Y-m-d');
            $system_date_time = date('Y-m-d H:i:s');

            $check_user_account_exist = $this->check_user_account_exist($username);
           
            if($check_user_account_exist > 0){
                $user_account_details = $this->get_user_account_details($username);
                $user_status = $user_account_details[0]['USER_STATUS'];
                $login_attemp = $user_account_details[0]['FAILED_LOGIN'];
                $password_expiry_date = $user_account_details[0]['PASSWORD_EXPIRY_DATE'];
                $transaction_log_id = $user_account_details[0]['TRANSACTION_LOG_ID'];

                if($user_status == 'ACTIVE'){
                    if($login_attemp >= 5){
                        return 'Locked';
                    }
                    else{
                        if($user_account_details[0]['PASSWORD'] === $password){
                            if(strtotime($system_date) > strtotime($password_expiry_date)){
                                return 'Password Expired';
                            }
                            else{
                                $update_login_attempt = $this->update_login_attempt($username, 0, null);

                                if($update_login_attempt){
                                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Log In', 'User ' . $username . ' logged in.');
                                        
                                    if($insert_transaction_log){
                                        return true;
                                    }
                                    else{
                                        return $insert_transaction_log;
                                    }
                                }
                                else{
                                    return $update_login_attempt;
                                }
                            }
                        }
                        else{
                            $update_login_attempt = $this->update_login_attempt($username, ($login_attemp + 1), $system_date_time);

                            if($update_login_attempt){
                                $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Attempt Log In', 'User ' . $username . ' attempted to log in.');
                                        
                                if($insert_transaction_log){
                                    return 'Incorrect';
                                }
                                else{
                                    return $insert_transaction_log;
                                }
                            }
                            else{
                                return $update_login_attempt;
                            }
                        }   
                    }
                }
                else{
                    return 'Inactive';
                }
            }
            else{
                return 'Incorrect';
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : send_email_notification
    # Purpose    : Sends notification email.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function send_email_notification($notification_type, $email, $subject, $body, $link, $is_html, $character_set){
        $email_configuration_details = $this->get_email_configuration_details(1);
        $mail_host = $email_configuration_details[0]['MAIL_HOST'];
        $port = $email_configuration_details[0]['PORT'];
        $smtp_auth = $email_configuration_details[0]['SMTP_AUTH'];
        $smtp_auto_tls = $email_configuration_details[0]['SMTP_AUTO_TLS'];
        $mail_username = $email_configuration_details[0]['USERNAME'];
        $mail_password = $this->decrypt_data($email_configuration_details[0]['PASSWORD']);
        $mail_encryption = $email_configuration_details[0]['MAIL_ENCRYPTION'];
        $mail_from_name = $email_configuration_details[0]['MAIL_FROM_NAME'];
        $mail_from_email = $email_configuration_details[0]['MAIL_FROM_EMAIL'];

        $company_setting_details = $this->get_company_setting_details(1);
        $company_name = $company_setting_details[0]['COMPANY_NAME'];

        $mail = new PHPMailer();
        $mail->isSMTP();
        $mail->SMTPDebug = SMTP::DEBUG_OFF;

        $mail->Host = $mail_host;
        $mail->Port = $port;
        $mail->SMTPSecure = $mail_encryption;
        $mail->SMTPAuth = $smtp_auth;
        $mail->SMTPAutoTLS = $smtp_auto_tls;
        $mail->Username = $mail_username;
        $mail->Password = $mail_password;
        $mail->setFrom($mail_from_email, $mail_from_name);
        $mail->addAddress($email, $email);
        $mail->Subject = $subject;

        if($notification_type == 1 || $notification_type == 2 || $notification_type == 3 || $notification_type == 4 || $notification_type == 5 || $notification_type == 6 || $notification_type == 7 || $notification_type == 8 || $notification_type == 9 || $notification_type == 10 || $notification_type == 11 || $notification_type == 12 || $notification_type == 13 || $notification_type == 14 || $notification_type == 15 || $notification_type == 16 || $notification_type == 17 || $notification_type == 18 || $notification_type == 19){
            if(!empty($link)){
                $message = file_get_contents('email_template/basic-notification-with-button.html');
                $message = str_replace('@link', $link, $message);

                if($notification_type == 1 || $notification_type == 2){
                    $message = str_replace('@button_title', 'View Attendance Record', $message);
                }
                else if($notification_type == 3 || $notification_type == 5 || $notification_type == 7 || $notification_type == 9 || $notification_type == 11 || $notification_type == 13){
                    $message = str_replace('@button_title', 'View Attendance Creation', $message);
                }
                else if($notification_type == 4 || $notification_type == 6 || $notification_type == 8 || $notification_type == 10 || $notification_type == 12 || $notification_type == 14){
                    $message = str_replace('@button_title', 'View Attendance Adjustment', $message);
                }
                else if($notification_type == 15 || $notification_type == 16 || $notification_type == 17 || $notification_type == 18){
                    $message = str_replace('@button_title', 'View Leave Application', $message);
                }
                else if($notification_type == 19){
                    $message = str_replace('@button_title', 'View Payslip', $message);
                }
            }
            else{
                $message = file_get_contents('email_template/basic-notification.html'); 
            }
            
            $message = str_replace('@company_name', $company_name, $message);
            $message = str_replace('@year', date('Y'), $message);
            $message = str_replace('@title', $subject, $message);
            $message = str_replace('@body', $body, $message);
        }
        else if($notification_type == 'send payslip'){
            $message = $body;
        }

        if($is_html){
            $mail->isHTML(true);
            $mail->MsgHTML($message);
            $mail->CharSet = $character_set;
        }
        else{
            $mail->Body = $body;
        }

        if ($mail->send()) {
            return true;
        } 
        else {
            return 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : send_notification
    # Purpose    : Sends notification.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function send_notification($notification_id, $from, $sent_to, $title, $message, $username){
        $system_notification = $this->check_system_notification_exist($notification_id, 'S');
        $email_notification = $this->check_system_notification_exist($notification_id, 'E');

        if($system_notification > 0 || $email_notification > 0){
            $error = '';
            $employee_details = $this->get_employee_details($sent_to, $sent_to);
            $email = $employee_details[0]['EMAIL'] ?? null;
            $validate_email = $this->validate_email($email);

            $notification_details = $this->get_notification_details($notification_id);
            $system_link = $notification_details[0]['SYSTEM_LINK'] ?? null;
            $web_link = $notification_details[0]['WEB_LINK'] ?? null;

            if($system_notification > 0){
                $insert_system_notification = $this->insert_system_notification($notification_id, $from, $sent_to, $title, $message, $system_link, $username);

                if(!$insert_system_notification){
                    $error = $insert_system_notification;
                }
            }

            if($email_notification > 0){
                if(!empty($email) && $validate_email){
                    $send_email_notification = $this->send_email_notification($notification_id, $email, $title, $message, $web_link, 1, 'utf-8');
    
                    if(!$send_email_notification){
                        $error = $send_email_notification;
                    }
                }
            }

            if(empty($error)){
                return true;
            }
            else{
                return $error;
            }
        }
        else{
            return true;
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : time_elapsed_string
    # Purpose    : returns the time elapsed
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function time_elapsed_string($datetime, $full = false) {
        $now = new DateTime;
        $ago = new DateTime($datetime);
        $diff = $now->diff($ago);
    
        $diff->w = floor($diff->d / 7);
        $diff->d -= $diff->w * 7;
    
        $string = array(
            'y' => 'year',
            'm' => 'month',
            'w' => 'week',
            'd' => 'day',
            'h' => 'hour',
            'i' => 'minute',
            's' => 'second',
        );
        foreach ($string as $k => &$v) {
            if ($diff->$k) {
                $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
            } else {
                unset($string[$k]);
            }
        }
    
        if (!$full) $string = array_slice($string, 0, 1);
        return $string ? implode(', ', $string) . ' ago' : 'just now';
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Check data exist methods
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : check_user_account_exist
    # Purpose    : Checks if the user exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_user_account_exist($username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_user_account_exist(:username)');
            $sql->bindValue(':username', $username);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_policy_exist
    # Purpose    : Checks if the policy exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_policy_exist($policy_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_policy_exist(:policy_id)');
            $sql->bindValue(':policy_id', $policy_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_permission_exist
    # Purpose    : Checks if the permission exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_permission_exist($permission_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_permission_exist(:permission_id)');
            $sql->bindValue(':permission_id', $permission_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_role_exist
    # Purpose    : Checks if the role exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_role_exist($role_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_role_exist(:role_id)');
            $sql->bindValue(':role_id', $role_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_system_parameter_exist
    # Purpose    : Checks if the system parameter exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_system_parameter_exist($parameter_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_system_parameter_exist(:parameter_id)');
            $sql->bindValue(':parameter_id', $parameter_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_system_code_exist
    # Purpose    : Checks if the system code exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_system_code_exist($system_type, $system_code){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_system_code_exist(:system_type, :system_code)');
            $sql->bindValue(':system_type', $system_type);
            $sql->bindValue(':system_code', $system_code);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_upload_setting_exist
    # Purpose    : Checks if the upload setting exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_upload_setting_exist($upload_setting_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_upload_setting_exist(:upload_setting_id)');
            $sql->bindValue(':upload_setting_id', $upload_setting_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_company_exist
    # Purpose    : Checks if the company exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_company_exist($company_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_company_exist(:company_id)');
            $sql->bindValue(':company_id', $company_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_country_exist
    # Purpose    : Checks if the country exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_country_exist($country_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_country_exist(:country_id)');
            $sql->bindValue(':country_id', $country_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_state_exist
    # Purpose    : Checks if the state exists.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_state_exist($state_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL check_state_exist(:state_id)');
            $sql->bindValue(':state_id', $state_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Update methods
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : update_login_attempt
    # Purpose    : Updates the login attempt of the user.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_login_attempt($username, $login_attemp, $last_failed_attempt_date){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL update_login_attempt(:username, :login_attemp, :last_failed_attempt_date)');
            $sql->bindValue(':username', $username);
            $sql->bindValue(':login_attemp', $login_attemp);
            $sql->bindValue(':last_failed_attempt_date', $last_failed_attempt_date);

            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_system_parameter_value
    # Purpose    : Updates system parameter value.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_system_parameter_value($parameter_number, $parameter_id, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');

            $sql = $this->db_connection->prepare('CALL update_system_parameter_value(:parameter_id, :parameter_number, :record_log)');
            $sql->bindValue(':parameter_id', $parameter_id);
            $sql->bindValue(':parameter_number', $parameter_number);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_user_account_password
    # Purpose    : Updates the user account password.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_user_account_password($username, $password, $password_expiry_date){
        if ($this->databaseConnection()) {
            $user_account_details = $this->get_user_account_details($username);
            $transaction_log_id = $user_account_details[0]['TRANSACTION_LOG_ID'];

            $sql = $this->db_connection->prepare('CALL update_user_account_password(:username, :password, :password_expiry_date)');
            $sql->bindValue(':password', $password);
            $sql->bindValue(':password_expiry_date', $password_expiry_date);
            $sql->bindValue(':username', $username);

            if($sql->execute()){
                $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated user account password.');
                                        
                if($insert_transaction_log){
                    return true;
                }
                else{
                    return $insert_transaction_log;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_policy
    # Purpose    : Updates policy.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_policy($policy, $policy_id, $policy_description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $policy_details = $this->get_policy_details($policy_id);
            
            if(!empty($policy_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $policy_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_policy(:policy_id, :policy, :policy_description, :transaction_log_id, :record_log)');
            $sql->bindValue(':policy_id', $policy_id);
            $sql->bindValue(':policy', $policy);
            $sql->bindValue(':policy_description', $policy_description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($policy_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated policy.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated policy.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_permission
    # Purpose    : Updates permission.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_permission($permission_id, $policy_id, $permission, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $permission_details = $this->get_permission_details($permission_id);
            
            if(!empty($permission_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $permission_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_permission(:permission_id, :policy_id, :permission, :transaction_log_id, :record_log)');
            $sql->bindValue(':permission_id', $permission_id);
            $sql->bindValue(':policy_id', $policy_id);
            $sql->bindValue(':permission', $permission);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($permission_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated permission.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated permission.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_role
    # Purpose    : Updates role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_role($role_id, $role, $description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $role_details = $this->get_role_details($role_id);

            if(!empty($role_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $role_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_role(:role_id, :role, :description, :transaction_log_id, :record_log)');
            $sql->bindValue(':role_id', $role_id);
            $sql->bindValue(':role', $role);
            $sql->bindValue(':description', $description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($role_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated role.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated role.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_user_account
    # Purpose    : Updates user account.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_user_account($user_code, $password, $password_expiry_date, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $user_account_details = $this->get_user_account_details($user_code);

            if(!empty($user_account_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $user_account_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_user_account(:user_code, :password, :password_expiry_date, :transaction_log_id, :record_log)');
            $sql->bindValue(':user_code', $user_code);
            $sql->bindValue(':password', $password);
            $sql->bindValue(':password_expiry_date', $password_expiry_date);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($user_account_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated user account.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated user account.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_user_account_lock_status
    # Purpose    : Updates user account lock status.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_user_account_lock_status($user_code, $transaction_type, $system_date, $username){
        if ($this->databaseConnection()) {
            $user_account_details = $this->get_user_account_details($user_code);
            $transaction_log_id = $user_account_details[0]['TRANSACTION_LOG_ID'];

            if($transaction_type == 'unlock'){
                $record_log = 'ULCK->' . $username . '->' . date('Y-m-d h:i:s');
                $log_type = 'Unlock';
                $log = 'User ' . $username . ' unlocked user account.';
            }
            else{
                $record_log = 'LCK->' . $username . '->' . date('Y-m-d h:i:s');
                $log_type = 'Lock';
                $log = 'User ' . $username . ' locked user account.';
            }

            $sql = $this->db_connection->prepare('CALL update_user_account_lock_status(:user_code, :transaction_type, :system_date, :record_log)');
            $sql->bindValue(':user_code', $user_code);
            $sql->bindValue(':transaction_type', $transaction_type);
            $sql->bindValue(':system_date', $system_date);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, $log_type, $log);
                                    
                if($insert_transaction_log){
                    return true;
                }
                else{
                    return $insert_transaction_log;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_user_account_status
    # Purpose    : Updates user account status.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_user_account_status($user_code, $user_status, $username){
        if ($this->databaseConnection()) {
            $user_account_details = $this->get_user_account_details($user_code);
            $transaction_log_id = $user_account_details[0]['TRANSACTION_LOG_ID'];

            if($user_status == 'ACTIVE'){
                $record_log = 'ACT->' . $username . '->' . date('Y-m-d h:i:s');
                $log_type = 'Activate';
                $log = 'User ' . $username . ' activated user account.';
            }
            else{
                $record_log = 'DACT->' . $username . '->' . date('Y-m-d h:i:s');
                $log_type = 'Deactivated';
                $log = 'User ' . $username . ' deactivated user account.';
            }

            $sql = $this->db_connection->prepare('CALL update_user_account_status(:user_code, :user_status, :record_log)');
            $sql->bindValue(':user_code', $user_code);
            $sql->bindValue(':user_status', $user_status);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, $log_type, $log);
                                    
                if($insert_transaction_log){
                    return true;
                }
                else{
                    return $insert_transaction_log;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_system_parameter
    # Purpose    : Updates system parameter.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_system_parameter($parameter_id, $parameter, $parameter_description, $extension, $parameter_number, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $system_parameter_details = $this->get_system_parameter_details($parameter_id);
            
            if(!empty($system_parameter_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $system_parameter_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_system_parameter(:parameter_id, :parameter, :parameter_description, :extension, :parameter_number, :transaction_log_id, :record_log)');
            $sql->bindValue(':parameter_id', $parameter_id);
            $sql->bindValue(':parameter', $parameter);
            $sql->bindValue(':parameter_description', $parameter_description);
            $sql->bindValue(':extension', $extension);
            $sql->bindValue(':parameter_number', $parameter_number);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($system_parameter_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated system parameter.');
                                        
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated system parameter.');
                                        
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_system_code
    # Purpose    : Updates system code.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_system_code($system_type, $system_code, $system_description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $system_code_details = $this->get_system_code_details($system_type, $system_code);

            if(!empty($system_code_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $system_code_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_system_code(:system_type, :system_code, :system_description, :transaction_log_id, :record_log)');
            $sql->bindValue(':system_type', $system_type);
            $sql->bindValue(':system_code', $system_code);
            $sql->bindValue(':system_description', $system_description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($system_code_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated system code.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated system code.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_upload_setting
    # Purpose    : Updates upload setting.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_upload_setting($setting_id, $upload_setting, $description, $max_file_size, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $upload_setting_details = $this->get_upload_setting_details($setting_id);

            if(!empty($upload_setting_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $upload_setting_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_upload_setting(:setting_id, :upload_setting, :description, :max_file_size, :transaction_log_id, :record_log)');
            $sql->bindValue(':setting_id', $setting_id);
            $sql->bindValue(':upload_setting', $upload_setting);
            $sql->bindValue(':description', $description);
            $sql->bindValue(':max_file_size', $max_file_size);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($upload_setting_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated upload setting.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated upload setting.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_company_details
    # Purpose    : Updates company.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_company_details($company_id, $company_name, $email, $telephone, $mobile, $website, $tax_id, $street_1, $street_2, $country_id, $state, $city, $zip_code, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $country_details = $this->get_country_details($country_id);
            
            if(!empty($country_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $country_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_company_details(:company_id, :company_name, :email, :telephone, :mobile, :website, :tax_id, :street_1, :street_2, :country_id, :state, :city, :zip_code, :transaction_log_id, :record_log)');
            $sql->bindValue(':company_id', $company_id);
            $sql->bindValue(':company_name', $company_name);
            $sql->bindValue(':email', $email);
            $sql->bindValue(':telephone', $telephone);
            $sql->bindValue(':mobile', $mobile);
            $sql->bindValue(':website', $website);
            $sql->bindValue(':tax_id', $tax_id);
            $sql->bindValue(':street_1', $street_1);
            $sql->bindValue(':street_2', $street_2);
            $sql->bindValue(':country_id', $country_id);
            $sql->bindValue(':state', $state);
            $sql->bindValue(':city', $city);
            $sql->bindValue(':zip_code', $zip_code);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($country_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated company.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated company.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_country
    # Purpose    : Updates country.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_country($country_id, $country_name, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $country_details = $this->get_country_details($country_id);
            
            if(!empty($country_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $country_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_country(:country_id, :country_name, :transaction_log_id, :record_log)');
            $sql->bindValue(':country_id', $country_id);
            $sql->bindValue(':country_name', $country_name);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($country_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated country.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated country.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : update_state
    # Purpose    : Updates state.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function update_state($state_id, $state_name, $country_id, $username){
        if ($this->databaseConnection()) {
            $record_log = 'UPD->' . $username . '->' . date('Y-m-d h:i:s');
            $state_details = $this->get_state_details($state_id);
            
            if(!empty($state_details[0]['TRANSACTION_LOG_ID'])){
                $transaction_log_id = $state_details[0]['TRANSACTION_LOG_ID'];
            }
            else{
                # Get transaction log id
                $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
                $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
                $transaction_log_id = $transaction_log_system_parameter[0]['ID'];
            }

            $sql = $this->db_connection->prepare('CALL update_state(:state_id, :state_name, :country_id, :transaction_log_id, :record_log)');
            $sql->bindValue(':state_id', $state_id);
            $sql->bindValue(':state_name', $state_name);
            $sql->bindValue(':country_id', $country_id);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                if(!empty($state_details[0]['TRANSACTION_LOG_ID'])){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated state.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Update', 'User ' . $username . ' updated state.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Insert methods
    # -------------------------------------------------------------
    
    # -------------------------------------------------------------
    #
    # Name       : insert_transaction_log
    # Purpose    : Inserts user log activities.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_transaction_log($transaction_log_id, $username, $log_type, $log){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL insert_transaction_log(:transaction_log_id, :username, :log_type, :log_date, :log)');
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':username', $username);
            $sql->bindValue(':log_type', $log_type);
            $sql->bindValue(':log_date', date('Y-m-d H:i:s'));
            $sql->bindValue(':log', $log);

            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_policy
    # Purpose    : Insert policy.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_policy($policy, $policy_description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(3, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_policy(:id, :policy, :policy_description, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':policy', $policy);
            $sql->bindValue(':policy_description', $policy_description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 3, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted policy.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_permission
    # Purpose    : Insert permission.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_permission($policy_id, $permission, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(4, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_permission(:id, :policy_id, :permission, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':policy_id', $policy_id);
            $sql->bindValue(':permission', $permission);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 4, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted permission.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_role
    # Purpose    : Insert role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_role($role, $description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(5, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_role(:id, :role, :description, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':role', $role);
            $sql->bindValue(':description', $description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 5, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted role.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_permission_role
    # Purpose    : Insert role permission.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_permission_role($role_id, $permission_id, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            $sql = $this->db_connection->prepare('CALL insert_permission_role(:role_id, :permission_id, :record_log)');
            $sql->bindValue(':role_id', $role_id);
            $sql->bindValue(':permission_id', $permission_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_user_account
    # Purpose    : Insert user account.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_user_account($user_code, $password, $password_expiry_date, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_user_account(:user_code, :password, :password_expiry_date, :transaction_log_id, :record_log)');
            $sql->bindValue(':user_code', $user_code);
            $sql->bindValue(':password', $password);
            $sql->bindValue(':password_expiry_date', $password_expiry_date);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update transaction log value
                $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                if($update_system_parameter_value){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted user account.');
                                 
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------
     
    # -------------------------------------------------------------
    #
    # Name       : insert_user_account_role
    # Purpose    : Insert user account role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_user_account_role($user_code, $role, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            $sql = $this->db_connection->prepare('CALL insert_user_account_role(:user_code, :role, :record_log)');
            $sql->bindValue(':user_code', $user_code);
            $sql->bindValue(':role', $role);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_system_parameter
    # Purpose    : Insert system parameter.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_system_parameter($parameter, $parameter_description, $extension, $number, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(1, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_system_parameter(:id, :parameter, :parameter_description, :extension, :number, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':parameter', $parameter);
            $sql->bindValue(':parameter_description', $parameter_description);
            $sql->bindValue(':extension', $extension);
            $sql->bindValue(':number', $number);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 1, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted system parameter.');
                                        
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_system_code
    # Purpose    : Insert system code.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_system_code($system_type, $system_code, $system_description, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_system_code(:system_type, :system_code, :system_description, :transaction_log_id, :record_log)');
            $sql->bindValue(':system_type', $system_type);
            $sql->bindValue(':system_code', $system_code);
            $sql->bindValue(':system_description', $system_description);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log);
        
            if($sql->execute()){
                # Update transaction log value
                $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                if($update_system_parameter_value){
                    $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted system code.');
                                    
                    if($insert_transaction_log){
                        return true;
                    }
                    else{
                        return $insert_transaction_log;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_upload_setting
    # Purpose    : Insert upload setting.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_upload_setting($upload_setting, $description, $max_file_size, $file_types, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');
            $error = '';

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(6, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_upload_setting(:id, :upload_setting, :description, :max_file_size, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':upload_setting', $upload_setting);
            $sql->bindValue(':description', $description);
            $sql->bindValue(':max_file_size', $max_file_size);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                foreach($file_types as $file_type){
                    $insert_upload_file_type = $this->insert_upload_file_type($id, $file_type, $username);

                    if(!$insert_upload_file_type){
                        $error = $insert_upload_file_type;
                    }
                }

                if(empty($error)){
                    # Update system parameter value
                    $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 6, $username);

                    if($update_system_parameter_value){
                        # Update transaction log value
                        $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                        if($update_system_parameter_value){
                            $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted upload setting.');
                                        
                            if($insert_transaction_log){
                                return true;
                            }
                            else{
                                return $insert_transaction_log;
                            }
                        }
                        else{
                            return $update_system_parameter_value;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $error;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_upload_file_type
    # Purpose    : Insert upload file type.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_upload_file_type($setting_id, $file_type, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            $sql = $this->db_connection->prepare('CALL insert_upload_file_type(:setting_id, :file_type, :record_log)');
            $sql->bindValue(':setting_id', $setting_id);
            $sql->bindValue(':file_type', $file_type);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_country
    # Purpose    : Insert country.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_country($country_name, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(8, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_country(:id, :country_name, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':country_name', $country_name);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 8, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted country.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : insert_state
    # Purpose    : Insert state.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function insert_state($state_name, $country_id, $username){
        if ($this->databaseConnection()) {
            $record_log = 'INS->' . $username . '->' . date('Y-m-d h:i:s');

            # Get system parameter id
            $system_parameter = $this->get_system_parameter(9, 1);
            $parameter_number = $system_parameter[0]['PARAMETER_NUMBER'];
            $id = $system_parameter[0]['ID'];

            # Get transaction log id
            $transaction_log_system_parameter = $this->get_system_parameter(2, 1);
            $transaction_log_parameter_number = $transaction_log_system_parameter[0]['PARAMETER_NUMBER'];
            $transaction_log_id = $transaction_log_system_parameter[0]['ID'];

            $sql = $this->db_connection->prepare('CALL insert_state(:id, :state_name, :country_id, :transaction_log_id, :record_log)');
            $sql->bindValue(':id', $id);
            $sql->bindValue(':state_name', $state_name);
            $sql->bindValue(':country_id', $country_id);
            $sql->bindValue(':transaction_log_id', $transaction_log_id);
            $sql->bindValue(':record_log', $record_log); 
        
            if($sql->execute()){
                # Update system parameter value
                $update_system_parameter_value = $this->update_system_parameter_value($parameter_number, 9, $username);

                if($update_system_parameter_value){
                    # Update transaction log value
                    $update_system_parameter_value = $this->update_system_parameter_value($transaction_log_parameter_number, 2, $username);

                    if($update_system_parameter_value){
                        $insert_transaction_log = $this->insert_transaction_log($transaction_log_id, $username, 'Insert', 'User ' . $username . ' inserted state.');
                                    
                        if($insert_transaction_log){
                            return true;
                        }
                        else{
                            return $insert_transaction_log;
                        }
                    }
                    else{
                        return $update_system_parameter_value;
                    }
                }
                else{
                    return $update_system_parameter_value;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Delete methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_policy
    # Purpose    : Delete policy.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_policy($policy_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_policy(:policy_id)');
            $sql->bindValue(':policy_id', $policy_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_all_permission
    # Purpose    : Delete all permission linked to policy.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_all_permission($policy_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_all_permission(:policy_id)');
            $sql->bindValue(':policy_id', $policy_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_permission
    # Purpose    : Delete permission.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_permission($permission_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_permission(:permission_id)');
            $sql->bindValue(':permission_id', $permission_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_role
    # Purpose    : Delete role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_role($role_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_role(:role_id)');
            $sql->bindValue(':role_id', $role_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_permission_role
    # Purpose    : Delete assigned permissions to role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_permission_role($role_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_permission_role(:role_id)');
            $sql->bindValue(':role_id', $role_id);
        
            if($sql->execute()){
               return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_all_user_account_role
    # Purpose    : Delete all user role.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_all_user_account_role($user_code){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_all_user_account_role(:user_code)');
            $sql->bindValue(':user_code', $user_code);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_system_parameter
    # Purpose    : Delete system parameter.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_system_parameter($parameter_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_system_parameter(:parameter_id)');
            $sql->bindValue(':parameter_id', $parameter_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_system_code
    # Purpose    : Delete system code.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_system_code($system_type, $system_code, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_system_code(:system_type, :system_code)');
            $sql->bindValue(':system_type', $system_type);
            $sql->bindValue(':system_code', $system_code);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_upload_setting
    # Purpose    : Delete upload setitng.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_upload_setting($upload_setting_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_upload_setting(:upload_setting_id)');
            $sql->bindValue(':upload_setting_id', $upload_setting_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_all_upload_file_type
    # Purpose    : Delete upload file type.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_all_upload_file_type($upload_setting_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_all_upload_file_type(:upload_setting_id)');
            $sql->bindValue(':upload_setting_id', $upload_setting_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_country
    # Purpose    : Delete country.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_country($country_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_country(:country_id)');
            $sql->bindValue(':country_id', $country_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_all_state
    # Purpose    : Delete all state linked to country.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_all_state($country_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_all_state(:country_id)');
            $sql->bindValue(':country_id', $country_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : delete_state
    # Purpose    : Delete state.
    #
    # Returns    : Number/String
    #
    # -------------------------------------------------------------
    public function delete_state($state_id, $username){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL delete_state(:state_id)');
            $sql->bindValue(':state_id', $state_id);
        
            if($sql->execute()){
                return true;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Get details methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_user_account_details
    # Purpose    : Gets the user account details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_user_account_details($username){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_user_account_details(:username)');
            $sql->bindValue(':username', $username);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'PASSWORD' => $row['PASSWORD'],
                        'USER_STATUS' => $row['USER_STATUS'],
                        'PASSWORD_EXPIRY_DATE' => $row['PASSWORD_EXPIRY_DATE'],
                        'FAILED_LOGIN' => $row['FAILED_LOGIN'],
                        'LAST_FAILED_LOGIN' => $row['LAST_FAILED_LOGIN'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_policy_details
    # Purpose    : Gets the policy details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_policy_details($policy_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_policy_details(:policy_id)');
            $sql->bindValue(':policy_id', $policy_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'POLICY' => $row['POLICY'],
                        'POLICY_DESCRIPTION' => $row['POLICY_DESCRIPTION'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_permission_details
    # Purpose    : Gets the permission details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_permission_details($permission_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_permission_details(:permission_id)');
            $sql->bindValue(':permission_id', $permission_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'POLICY_ID' => $row['POLICY_ID'],
                        'PERMISSION' => $row['PERMISSION'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_role_details
    # Purpose    : Gets the role details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_role_details($role_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_role_details(:role_id)');
            $sql->bindValue(':role_id', $role_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'ROLE' => $row['ROLE'],
                        'ROLE_DESCRIPTION' => $row['ROLE_DESCRIPTION'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_role_permission_details
    # Purpose    : Gets the role permission details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_role_permission_details($role_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_role_permission_details(:role_id)');
            $sql->bindValue(':role_id', $role_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'PERMISSION_ID' => $row['PERMISSION_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_user_account_role_details
    # Purpose    : Gets the role user details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_user_account_role_details($role_id, $user_code){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_user_account_role_details(:role_id, :user_code)');
            $sql->bindValue(':role_id', $role_id);
            $sql->bindValue(':user_code', $user_code);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'ROLE_ID' => $row['ROLE_ID'],
                        'USERNAME' => $row['USERNAME'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_system_parameter_details
    # Purpose    : Gets the system parameter details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_system_parameter_details($parameter_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_system_parameter_details(:parameter_id)');
            $sql->bindValue(':parameter_id', $parameter_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'PARAMETER' => $row['PARAMETER'],
                        'PARAMETER_DESCRIPTION' => $row['PARAMETER_DESCRIPTION'],
                        'PARAMETER_EXTENSION' => $row['PARAMETER_EXTENSION'],
                        'PARAMETER_NUMBER' => $row['PARAMETER_NUMBER'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_system_code_details
    # Purpose    : Gets the system code details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_system_code_details($system_type, $system_code){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_system_code_details(:system_type, :system_code)');
            $sql->bindValue(':system_type', $system_type);
            $sql->bindValue(':system_code', $system_code);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'SYSTEM_DESCRIPTION' => $row['SYSTEM_DESCRIPTION'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_upload_setting_details
    # Purpose    : Gets the upload setting details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_upload_setting_details($upload_setting_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_upload_setting_details(:upload_setting_id)');
            $sql->bindValue(':upload_setting_id', $upload_setting_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'UPLOAD_SETTING' => $row['UPLOAD_SETTING'],
                        'DESCRIPTION' => $row['DESCRIPTION'],
                        'MAX_FILE_SIZE' => $row['MAX_FILE_SIZE'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_upload_file_type_details
    # Purpose    : Gets the upload file type details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_upload_file_type_details($upload_setting_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_upload_file_type_details(:upload_setting_id)');
            $sql->bindValue(':upload_setting_id', $upload_setting_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'FILE_TYPE' => $row['FILE_TYPE'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_country_details
    # Purpose    : Gets the country details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_country_details($country_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_country_details(:country_id)');
            $sql->bindValue(':country_id', $country_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'COUNTRY_NAME' => $row['COUNTRY_NAME'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_state_details
    # Purpose    : Gets the state details.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_state_details($state_id){
        if ($this->databaseConnection()) {
            $response = array();

            $sql = $this->db_connection->prepare('CALL get_state_details(:state_id)');
            $sql->bindValue(':state_id', $state_id);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $response[] = array(
                        'STATE_NAME' => $row['STATE_NAME'],
                        'COUNTRY_ID' => $row['COUNTRY_ID'],
                        'TRANSACTION_LOG_ID' => $row['TRANSACTION_LOG_ID'],
                        'RECORD_LOG' => $row['RECORD_LOG']
                    );
                }

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Get methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_next_date
    # Purpose    : Returns the calculated date 
    #              based on the frequency
    #
    # Returns    : Date
    #
    # -------------------------------------------------------------
    public function get_next_date($previous_date, $frequency){
        if($frequency == 'MONTHLY'){
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+1 month', '', '');
        }
        else if($frequency == 'DAILY'){
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+1 day', '', '');
        }
        else if($frequency == 'WEEKLY'){
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+1 week', '', '');
        }
        else if($frequency == 'BIWEEKLY'){
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+2 weeks', '', '');
        }
        else if($frequency == 'QUARTERLY'){
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+3 months', '', '');
        }
        else{
            $date = $this->check_date('empty', $previous_date, '', 'Y-m-d', '+1 year', '', '');
        }
    
        return $date;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_permission_count
    # Purpose    : Gets the roles' sub permission count.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function get_permission_count($role_id, $permission_id){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL get_permission_count(:role_id, :permission_id)');
            $sql->bindValue(':role_id', $role_id);
            $sql->bindValue(':permission_id', $permission_id);

            if($sql->execute()){
                $row = $sql->fetch();

                return $row['TOTAL'];
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_system_parameter
    # Purpose    : Gets the system parameter.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_system_parameter($parameter_id, $add){
        if ($this->databaseConnection()) {
            $sql = $this->db_connection->prepare('CALL get_system_parameter(:parameter_id)');
            $sql->bindValue(':parameter_id', $parameter_id);

            if($sql->execute()){
                $row = $sql->fetch();

                $parameter_number = $row['PARAMETER_NUMBER'] + $add;
                $parameter_extension = $row['PARAMETER_EXTENSION'];

                $response[] = array(
                    'PARAMETER_NUMBER' => $parameter_number,
                    'ID' => $parameter_extension . $parameter_number
                );

                return $response;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_user_account_status
    # Purpose    : Returns the status, badge.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_user_account_status($stat){
        $response = array();

        switch ($stat) {
            case 'ACTIVE':
                $status = 'Active';
                $button_class = 'bg-success';
                break;
            default:
                $status = 'Inactive';
                $button_class = 'bg-danger';
        }

        $response[] = array(
            'STATUS' => $status,
            'BADGE' => '<span class="badge '. $button_class .'">'. $status .'</span>'
        );

        return $response;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_user_account_lock_status
    # Purpose    : Returns the status, badge.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_user_account_lock_status($failed_login){
        $response = array();

        if ($failed_login >= 5) {
            $status = 'Locked';
            $button_class = 'bg-danger';
        }
        else{
            $status = 'Unlocked';
            $button_class = 'bg-success';
        }

        $response[] = array(
            'STATUS' => $status,
            'BADGE' => '<span class="badge '. $button_class .'">'. $status .'</span>'
        );

        return $response;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : get_date_difference
    # Purpose    : Returns the year, month and days difference.
    #
    # Returns    : Array
    #
    # -------------------------------------------------------------
    public function get_date_difference($date_1, $date_2){
        $response = array();

        $diff = abs(strtotime($date_2) - strtotime($date_1));

        $years = floor($diff / (365 * 60 * 60 * 24));
        $months = floor(($diff - $years * 365 * 60 * 60 * 24) / (30 * 60 * 60 * 24));
        $days = floor(($diff - $years * 365 * 60 * 60 * 24 - $months * 30 * 60 * 60 * 24)/ (60 * 60 * 24));

        if($years > 1 || $years == 0){
            $years = $years . ' Years';
        }
        else{
            $years = $years . ' Year';
        }

        if($months > 1 || $months == 0){
            $months = $months . ' Months';
        }
        else{
            $months = $months . ' Month';
        }

        if($days > 1 || $days == 0){
            $days = $days . ' Days';
        }
        else{
            $days = $days . ' Day';
        }

        $response[] = array(
            'YEARS' => $years,
            'MONTHS' => $months,
            'DAYS' => $days
        );

        return $response;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Check methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_role_permissions
    # Purpose    : Checks the permissions of the role.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_role_permissions($username, $permission_id){
        if ($this->databaseConnection()) {
            $response = array();
            $total = 0;

            $sql = $this->db_connection->prepare('SELECT ROLE_ID FROM global_role_user_account WHERE USERNAME = :username');
            $sql->bindValue(':username', $username);

            if($sql->execute()){
                while($row = $sql->fetch()){
                    $role_id = $row['ROLE_ID'];

                    $total += $this->get_permission_count($role_id, $permission_id);
                }
                
                return $total;
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_modal_scrollable
    # Purpose    : Check if the modal to be generated
    #              is scrollable or not.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function check_modal_scrollable($scrollable){
        if($scrollable){
            return 'modal-dialog-scrollable';
        }
        else{
            return '';
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_modal_size
    # Purpose    : Check the size of the modal.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function check_modal_size($size){
        if($size == 'SM'){
            return 'modal-sm';
        }
        else if($size == 'LG'){
            return 'modal-lg';
        }
        else if($size == 'XL'){
            return 'modal-xl';
        }
        else {
            return '';
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_number
    # Purpose    : Checks the number if empty or 0 
    #              return 0 or return number given.
    #
    # Returns    : Number
    #
    # -------------------------------------------------------------
    public function check_number($number){
        if(is_numeric($number) && (!empty($number) || $number > 0) && !empty($number)){
            return $number;
        }
        else{
            return '0';
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_date
    # Purpose    : Checks the date with different format
    #
    # Returns    : Date
    #
    # -------------------------------------------------------------
    public function check_date($type, $date, $time, $format, $modify, $system_date, $current_time){
        if($type == 'default'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify);
            }
            else{
                return $system_date;
            }
        }
        else if($type == 'empty'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify);
            }
            else{
                return null;
            }
        }
        else if($type == 'summary'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify);
            }
            else{
                return '--';
            }
        }
        else if($type == 'na'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify);
            }
            else{
                return 'N/A';
            }
        }
        else if($type == 'complete'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify) . ' ' . $time;
            }
            else{
                return 'N/A';
            }
        }
        else if($type == 'encoded'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify) . ' ' . $time;
            }
            else{
                return 'N/A';
            }
        }
        else if($type == 'date time'){
            if(!empty($date)){
                return $this->format_date($format, $date, $modify) . ' ' . $time;
            }
            else{
                return 'N/A';
            }
        }
        else if($type == 'default time'){
            if(!empty($date)){
                return $time;
            }
            else{
                return $current_time;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_image
    # Purpose    : Checks the image.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function check_image($image, $type){
        if(empty($image) || !file_exists($image)){
            switch ($type) {
                case 'profile':
                    return './assets/images/default/default-avatar.png';
                break;
                case 'login bg':
                    return './assets/images/default/default-bg.jpg';
                break;
                case 'logo light':
                    return './assets/images/default/default-logo-light-horizontal.png';
                break;
                case 'logo dark':
                    return './assets/images/default/default-logo-dark-horizontal.png';
                break;
                case 'logo icon light':
                    return './assets/images/default/default-logo-icon-light.png';
                break;
                case 'logo icon dark':
                    return './assets/images/default/default-logo-icon-dark.png';
                break;
                case 'favicon':
                    return './assets/images/default/default-logo-icon-light.png';
                break;
                case 'company logo':
                    return './assets/images/default/default-logo-dark-horizontal.png';
                break;
                default:
                    return './assets/images/default/default-image-placeholder.png';
            }
        }
        else{
            return $image;
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : check_user_account_status
    # Purpose    : Checks the user account status. 
    #
    # Returns    : Date
    #
    # -------------------------------------------------------------
    public function check_user_account_status($username){
        if ($this->databaseConnection()) {
            $user_account_details = $this->get_user_account_details($username);
            $user_status = $user_account_details[0]['USER_STATUS'];
            $failed_login = $user_account_details[0]['FAILED_LOGIN'];

            if($user_status == 'ACTIVE' || $failed_login < 5){
                return true;
            }
            else{
                return false;
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #   Generate methods
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_file_name
    # Purpose    : generates random file name.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_file_name($length) {
        $key = '';
        $keys = array_merge(range(0, 9), range('a', 'z'));
    
        for ($i = 0; $i < $length; $i++) {
            $key .= $keys[array_rand($keys)];
        }
    
        return $key;
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_role_permission_form
    # Purpose    : Generates permission check box.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_role_permission_form(){
        if ($this->databaseConnection()) {
            $counter = 0;
            $column = '<div class="accordion" id="permission-accordion">';
        
            $sql = $this->db_connection->prepare('SELECT POLICY_ID, POLICY FROM global_policy ORDER BY POLICY');
        
            if($sql->execute()){
                while($row = $sql->fetch()){
                    $policy_id = $row['POLICY_ID'];
                    $policy = $row['POLICY'];

                    $column .= ' 
                                    <div class="accordion-item">
                                        <h2 class="accordion-header" id="heading-'. $policy_id .'">
                                            <button class="accordion-button fw-medium collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-'. $policy_id .'" aria-expanded="false" aria-controls="collapse-'. $policy_id .'">
                                                '. $policy .'
                                            </button>
                                        </h2>
                                        <div id="collapse-'. $policy_id .'" class="accordion-collapse collapse" aria-labelledby="heading-'. $policy_id .'" data-bs-parent="#permission-accordion">
                                            <div class="accordion-body">
                                                <div class="table-responsive">
                                                    <table class="table table-bordered mb-0">
                                                        <tbody>';
                                                        
                                                        $sql2 = $this->db_connection->prepare('SELECT PERMISSION_ID, PERMISSION FROM global_permission WHERE POLICY_ID = :policy_id ORDER BY PERMISSION_ID');
                                                        $sql2->bindValue(':policy_id', $policy_id);
                                    
                                                        if($sql2->execute()){
                                                            while($res = $sql2->fetch()){
                                                                $permission_id = $res['PERMISSION_ID'];
                                                                $permission = $res['PERMISSION'];
                                    
                                                                $column .= '<tr>
                                                                    <td><label class="form-check-label" for="'. $permission_id .'">'. $permission .'</label></td>
                                                                    <td>
                                                                        <div class="form-check form-switch mb-3">
                                                                            <input class="form-check-input role-permissions" type="checkbox" id="'. $permission_id .'" value="'. $permission_id .'">
                                                                        </div>
                                                                    </td>
                                                                </tr>';
                                                            }
                                                        }

                                            $column .= '</tbody>   
                                                    </table>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                               ';
                }

                $column .= '</div>';

                return $column;
            }
            else{
                return $sql->errorInfo();
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_role_options
    # Purpose    : Generates role options of dropdown.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_role_options(){
        if ($this->databaseConnection()) {
            $option = '';
            
            $sql = $this->db_connection->prepare('CALL generate_role_options()');

            if($sql->execute()){
                $count = $sql->rowCount();
        
                if($count > 0){
                    while($row = $sql->fetch()){
                        $role_id = $row['ROLE_ID'];
                        $role = $row['ROLE'];
    
                        $option .= "<option value='". $role_id ."'>". $role ."</option>";
                    }
    
                    return $option;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_system_code_options
    # Purpose    : Generates system code options of dropdown.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_system_code_options($system_type){
        if ($this->databaseConnection()) {
            $option = '';
            
            $sql = $this->db_connection->prepare('CALL generate_system_code_options(:system_type)');
            $sql->bindValue(':system_type', $system_type);

            if($sql->execute()){
                $count = $sql->rowCount();
        
                if($count > 0){
                    while($row = $sql->fetch()){
                        $system_code = $row['SYSTEM_CODE'];
                        $system_description = $row['SYSTEM_DESCRIPTION'];
    
                        $option .= "<option value='". $system_code ."'>". $system_description ."</option>";
                    }
    
                    return $option;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_role_permission_form
    # Purpose    : Generates permission check box.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_state_options(){
        if ($this->databaseConnection()) {      
            $option = '';

            $sql = $this->db_connection->prepare('SELECT COUNTRY_ID, COUNTRY_NAME FROM global_country ORDER BY COUNTRY_NAME');
        
            if($sql->execute()){
                while($row = $sql->fetch()){
                    $country_id = $row['COUNTRY_ID'];
                    $country_name = $row['COUNTRY_NAME'];

                    $option .= '<optgroup label="'. $country_name .'">';

                                    $sql2 = $this->db_connection->prepare('SELECT STATE_ID, STATE_NAME FROM global_state WHERE COUNTRY_ID = :country_id ORDER BY STATE_NAME');
                                    $sql2->bindValue(':country_id', $country_id);
                
                                    if($sql2->execute()){
                                        while($res = $sql2->fetch()){
                                            $state_id = $res['STATE_ID'];
                                            $state_name = $res['STATE_NAME'];

                                            $option .= '<option value="'. $state_id .'">'. $state_name .'</option>';
                                        }
                                    }

                    $option .= '</optgroup>';
                }

                return $option;
            }
            else{
                return $sql->errorInfo();
            }
        }
    }
    # -------------------------------------------------------------

    # -------------------------------------------------------------
    #
    # Name       : generate_country_options
    # Purpose    : Generates country options of dropdown.
    #
    # Returns    : String
    #
    # -------------------------------------------------------------
    public function generate_country_options(){
        if ($this->databaseConnection()) {
            $option = '';
            
            $sql = $this->db_connection->prepare('CALL generate_country_options()');

            if($sql->execute()){
                $count = $sql->rowCount();
        
                if($count > 0){
                    while($row = $sql->fetch()){
                        $country_id = $row['COUNTRY_ID'];
                        $country_name = $row['COUNTRY_NAME'];
    
                        $option .= "<option value='". $country_id ."'>". $country_name ."</option>";
                    }
    
                    return $option;
                }
            }
            else{
                return $sql->errorInfo()[2];
            }
        }
    }
    # -------------------------------------------------------------

}

?>