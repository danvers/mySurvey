<?php

/**
 * @author Dan Verständig
 */

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title>My Profile - <?php WORKSPACE_TITLE;?></title>

        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>
    </head>
<body>
<div id="wrapper">

<?php

require('inc/navigation.php');

if ($messageStack->size('general') > 0) echo $messageStack->output('general');
?>
    <div id="content">
        <div>
            <h2>Angaben zur Person</h2>
            <?php
            if ($User->__get('userlevel') > DEMO_ACCOUNT){
            ?>
            <form action="profile.php?do=edit_userdata" method="post">
                <?php
                }else{
                ?>
                <form action="-">
                    <?php
                    }
                    ?>
                    <p class="left">E-mail:</p>

                    <p>
                        <?php echo draw_input_field('usermail', $User->__get('usermail')); ?></p>

                    <p class="left">Vorname:</p>

                    <p><?php echo draw_input_field('firstname', $User->__get('firstname')); ?></p>

                    <p class="left">Nachname:</p>

                    <p><?php echo draw_input_field('lastname', $User->__get('lastname')); ?></p>

                    <?php
                    if ($User->__get('userlevel') > DEMO_ACCOUNT) {
                        ?>
                        <p class="left">&nbsp;</p>
                        <p><?php echo draw_input_field('send', 'Daten ändern', '', 'submit', false); ?></p>
                    <?php
                    } else {
                        ?>
                        <p class="left">&nbsp;</p><span class="demosubmit">Daten ändern</span> [<a class="tooltip"
                                                                                                   href="#">?<span
                                style="width:200px;">Nicht mit dem Demo-Account möglich.</span></a>]
                    <?php
                    }
                    ?>
                </form>
        </div>
        <br/>

        <div>
            <h2>Passwort &auml;ndern</h2>
            <?php
            if ($User->__get('userlevel') > DEMO_ACCOUNT){
            ?>
            <form action="profile.php?do=edit_password" method="post">
                <?php
                }else{
                ?>
                <form action="-">
                    <?php
                    }
                    ?>
                    <p class="left">altes Passwort:</p>

                    <p><?php echo draw_input_field('password0', '', '', 'password', false); ?></p>

                    <p class="left">neues Passwort:</p>

                    <p><?php echo draw_input_field('password1', '', '', 'password', false); ?></p>

                    <p class="left">Passwort (Wiederholen):</p>

                    <p><?php echo draw_input_field('password2', '', '', 'password', false); ?></p>
                    <?php
                    if ($User->__get('userlevel') > DEMO_ACCOUNT) {
                        ?>
                        <p class="left">&nbsp;</p>
                        <p><?php echo draw_input_field('send', 'Passwort ändern', '', 'submit', false); ?></p>
                    <?php
                    } else {
                        ?>
                        <p class="left">&nbsp;</p><span class="demosubmit">Passwort ändern</span> [<a class="tooltip"
                                                                                                      href="#">?<span
                                style="width:200px;">Nicht mit dem Demo-Account möglich.</span></a>]
                    <?php
                    }
                    ?>
                </form>
        </div>

    </div>


<?php require('inc/footer.php'); ?>