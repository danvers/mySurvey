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

        <title><?php echo TITLE;?> | <?php echo WORKSPACE_TITLE; ?></title>

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
            <h2><?php echo TITLE_EDIT_USER_DATA;?></h2>

            <form id="form" action="profile.php?do=edit_userdata" method="post">

                <label for="usermail"><?php echo LABEL_EMAIL;?></label>
                <p><?php echo draw_input_field('usermail', $User->__get('usermail')); ?></p>

                <label for="firstname"><?php echo LABEL_FIRSTNAME;?></label>
                <p><?php echo draw_input_field('firstname', $User->__get('firstname')); ?></p>

                <label for="lastname"><?php echo LABEL_LASTNAME;?></label>
                <p><?php echo draw_input_field('lastname', $User->__get('lastname')); ?></p>

                <div class="r2">
                    <p><?php echo draw_input_field('send', TEXT_SUBMIT, '', 'submit', false); ?></p>
                </div>
            </form>
        </div>

        <div>
            <h2><?php echo TITLE_CHANGE_PASS;?></h2>

            <form id="form" action="profile.php?do=edit_password" method="post">

                <label for="password0"><?php echo LABEL_PW_OLD;?></label>
                <p><?php echo draw_input_field('password0', '', '', 'password', false); ?></p>

                <label for="password1"><?php echo LABEL_PW_NEW;?></label>
                <p><?php echo draw_input_field('password1', '', '', 'password', false); ?></p>

                <label for="password2"><?php echo LABEL_PW_RPT;?></label>
                <p><?php echo draw_input_field('password2', '', '', 'password', false); ?></p>

                <div class="r2">
                    <p><?php echo draw_input_field('send', TEXT_CHANGE_PW, '', 'submit', false); ?></p>
                </div>
            </form>
        </div>

    </div>

<?php require('inc/footer.php'); ?>