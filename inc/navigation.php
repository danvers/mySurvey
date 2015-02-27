<?php

/**
 * @author blabla
 * @copyright 2007
 */

?>
<div id="navigation">
    <?php
    if ($SessionManager->logged_in()) {
        ?>
        <p id="leftnav">
            <a href="index.php">Übersicht</a>&nbsp;|&nbsp;
            <a href="myinquiries.php">meine Einträge</a>
            <?php
            if ($User->__get('userlevel') > 1) {
                ?>
                &nbsp;|&nbsp;<a href="survey.php">Bogen bearbeiten</a>&nbsp;|&nbsp;
                <a href="users.php">Benutzer</a>&nbsp;|&nbsp;
                <a href="news.php">Informationen</a>
            <?php
            }
            ?>
        </p>
        <p id="rightnav">
            <a href="profile.php">mein Profil</a> (<?php echo $User->__get('usermail'); ?>)&nbsp;|&nbsp;
            <a href="index.php?do=logout">Ausloggen</a>
        </p>
        <p id="breadcrumb">
            &nbsp;<?php echo getPosition(); ?>
        </p>
    <?php
    } else {
        if (isset($_GET['position']) && $_GET['position'] === 'password') {
            ?>
            <p id="rightnav">
                <a href="index.php">Anmelden</a>
            </p>
        <?php
        } else {
            ?>
            <p id="rightnav">
                <a href="index.php?position=password">Passwort vergessen?</a>
            </p>
        <?php
        }
    }
    ?>
</div>