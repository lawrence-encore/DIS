<?php
    $menu = '';

    $policy_page = $api->check_role_permissions($username, 1);
    $role_page = $api->check_role_permissions($username, 11);

    if($policy_page == 0 || $role_page == 0){
        if($policy_page == 0 || $role_page == 0){
            $menu .= '<li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle arrow-none" href="#" id="t-configuration-dropdown" role="button">
                            <i class="bx bx-wrench me-2"></i><span key="t-configuration">Configuration</span> <div class="arrow-down"></div>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="t-configuration-dropdown">';

                        if($policy_page == 0){
                            $menu .= '<a href="policy.php" class="dropdown-item" key="t-policy">Policy</a>';
                        }

                        if($role_page == 0){
                            $menu .= '<a href="role.php" class="dropdown-item" key="t-role">Role</a>';
                        }
                        
            $menu .= '</div>
                    </li>';
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