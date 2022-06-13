<?php
    $menu = '';

    $general_setting_page = $api->check_role_permissions($username, 1);
    $policy_page = $api->check_role_permissions($username, 1);
    $role_page = $api->check_role_permissions($username, 11);

    if($policy_page == 0 || $role_page == 0){
        if($general_setting_page == 0){
            $menu .= '<li class="nav-item dropdown"><a href="general-setting.php" class="nav-link">General Setting</a></li>';
        }

        if($policy_page == 0){
            $menu .= '<li class="nav-item dropdown"><a href="policy.php" class="nav-link">Policy</a></li>';
        }

        if($role_page == 0){
            $menu .= '<li class="nav-item dropdown"><a href="role.php" class="nav-link">Role</a></li>';
        }
    }
?>


<div class="topnav">
            <div class="container-fluid">
                <nav class="navbar navbar-light navbar-expand-lg topnav-menu">

                    <div class="collapse navbar-collapse" id="topnav-menu-content">
                        <ul class="navbar-nav">
                            <?php echo $menu; ?>
                        </ul>
                    </div>
                </nav>
            </div>
        </div>