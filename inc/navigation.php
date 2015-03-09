<?php

/**
 * @author Dan Verständig
 */

?>
<div id="navigation">
    <?php
    if ($SessionManager->logged_in()) {
        ?>
        <p id="leftnav">
            <a href="index.php"><?php echo NAV_OVERVIEW;?></a> |
            <a href="myinquiries.php"><?php echo NAV_MY_ENTRIES;?></a>
            <?php
            if ($User->__get('userlevel') > 1) {
                ?>
                 |
                <a href="survey.php"><?php echo NAV_EDIT_SURVEY;?></a> |
                <a href="users.php"><?php echo NAV_USER;?></a> |
                <a href="news.php"><?php echo NAV_NEWS;?></a>
            <?php
            }
            ?>
        </p>
        <p id="rightnav">
            <a href="profile.php"><?php echo NAV_MY_PROFILE;?></a> (<?php echo $User->__get('usermail'); ?>) |
            <a href="index.php?do=logout"><?php echo NAV_LOGOUT;?></a>
        </p>
        <p id="breadcrumb"><?php echo getPosition(); ?></p>
    <?php
    } else {
        if (isset($_GET['position']) && $_GET['position'] === 'password') {
            ?>
            <p id="rightnav">
                <a href="index.php"><?php echo NAV_LOGIN;?></a>
            </p>
        <?php
        } elseif(!isset($_GET['position'])|| $_GET['position'] != 'activate') {
            ?>
            <p id="rightnav">
                <a href="index.php?position=password">Passwort vergessen?</a>
            </p>
            <p class="c"></p>
        <?php
        }
    }
    ?>
</div>