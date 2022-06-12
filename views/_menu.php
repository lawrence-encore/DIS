<?php
    $menu = '';

    $policy_page = $api->check_role_permissions($username, 1);
    $role_page = $api->check_role_permissions($username, 11);

    if($policy_page == 0 || $role_page == 0){
        $menu .= '<li class="menu-title" key="t-menu">Administrator</li>';

        if($policy_page == 0 || $role_page == 0){
            $menu .= '<li>
                        <a href="javascript: void(0);" class="has-arrow waves-effect">
                            <i class="bx bx-cog"></i>
                            <span key="t-settings">Settings</span>
                        </a>
                        <ul class="sub-menu" aria-expanded="false">';

                        if($policy_page == 0){
                            $menu .= '<li><a href="policy.php" key="t-policy">Policy</a></li>';
                        }

                        if($role_page == 0){
                            $menu .= '<li><a href="role.php" key="t-role">Role</a></li>';
                        }

            $menu .= '</ul>
                    </li>';
        }
    }
?>

<div class="vertical-menu">
                <div data-simplebar class="h-100">
                    <div id="sidebar-menu">
                        <ul class="metismenu list-unstyled" id="side-menu">
                            <?php echo $menu; ?>
                        </ul>
                    </div>
                </div>
            </div>