<?php
    $menu = '';
?>

<div class="vertical-menu">
                <div data-simplebar class="h-100">
                    <div id="sidebar-menu">
                        <ul class="metismenu list-unstyled" id="side-menu">
                            <?php echo $menu; ?>
                            <li class="menu-title" key="t-menu">Administrator</li>
                            <li>
                                <a href="javascript: void(0);" class="has-arrow waves-effect">
                                    <i class="bx bx-cog"></i>
                                    <span key="t-settings">Settings</span>
                                </a>
                                <ul class="sub-menu" aria-expanded="false">
                                    <li><a href="policy.php" key="t-system-parameter">Policy</a></li>
                                </ul>
                            </li>
                            <li>
                                <a href="documentation.php" class="waves-effect">
                                    <i class="bx bx-book"></i>
                                    <span key="t-documentation">Documentation</span>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>