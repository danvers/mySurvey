<?php

/**
 * @author Dan Verständig
 */

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'add':

            $error = false;

            foreach ($_POST as $postbits => $element) {
                $postbit[$postbits] = db_prepare_input($element);
            }
            if (isset($postbit['email'])) {
                if (!check_email($postbit['email']) || strlen($postbit['email']) < 5) {
                    $messageStack->add_session('general', MSG_E_MAIL, 'error');
                    $error = true;
                }
            }
            if (isset($postbit['firstname'])) {
                if (strlen($postbit['firstname']) < NAME_MIN_LENGTH) {
                    $messageStack->add_session('general', sprintf(MSG_E_FIRSTNAME,NAME_MIN_LENGTH), 'error');
                    $error = true;
                }
            }
            if (isset($postbit['lastname'])) {
                if (strlen($postbit['lastname']) < NAME_MIN_LENGTH) {
                    $messageStack->add_session('general', sprintf(MSG_E_LASTNAME,NAME_MIN_LENGTH), 'error');
                    $error = true;
                }
            }
            if ($error == false) {
                if ($User->createNewUser($postbit)) {
                    $messageStack->add_session('general', sprintf(MSG_USER_ADDED, $postbit['email']), 'success');
                    header('Location:users.php');
                } else {
                    $messageStack->add_session('general', MSG_E_USER_ADD, 'error');
                    header('Location:users.php?position=add');
                }

            } else {
                header('Location:users.php?position=add');
            }

            break;

        case 'edit':

            $error = false;

            if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {

                $id = $_GET['uID'];

                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }
                if (isset($postbit['email'])) {
                    if (!check_email($postbit['email']) || strlen($postbit['email']) < 5) {
                        $messageStack->add_session('general', MSG_E_MAIL, 'error');
                        $error = true;
                    }
                }
                if (isset($postbit['firstname'])) {

                    if (strlen($postbit['firstname']) < NAME_MIN_LENGTH) {
                        $messageStack->add_session('general', sprintf(MSG_E_FIRSTNAME,NAME_MIN_LENGTH), 'error');
                        $error = true;
                    }
                }
                if (isset($postbit['lastname'])) {

                    if (strlen($postbit['lastname']) < NAME_MIN_LENGTH) {
                        $messageStack->add_session('general', sprintf(MSG_E_LASTNAME,NAME_MIN_LENGTH), 'error');
                        $error = true;
                    }
                }
                if ($error == false) {
                    $User->editUser($id, $postbit);
                    $messageStack->add_session('general', MSG_USER_UPDATED, 'success');
                }
                header('Location:users.php?position=edit&uID=' . $id);
            }
            break;

        case 'reinvite':

            if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {

                $id = $_GET['uID'];
                $email = $User->__get('usermail');
                if ($User->reInviteUser($id)) {
                    $messageStack->add_session('general', sprintf(MSG_INVITE_RESEND, $email) , 'success');
                } else {
                    $messageStack->add_session('general', MSG_E_INVITE, 'error');
                }
                header('Location:users.php?position=edit&uID=' . $id);
            }
            break;

        case 'delete':

            if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {
                $id = $_GET['uID'];
                $User->deleteUser($id);
                $messageStack->add_session('general', MSG_USER_DELETED, 'success');

                $db->query('DELETE FROM ' . table_survey . ' WHERE userid="' . $id . '"');
                $messageStack->add_session('general', MSG_USER_ENTRIES_DELETED, 'success');
            }

            header('Location:users.php');
            break;
    }
}

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
        <?php
        if (isset($_GET['position'])) {
            switch ($_GET['position']) {
                case 'add':
                    ?>
                    <div>
                        <h2><?php echo TEXT_INVITE_USER;?></h2>

                        <form id="form" action="users.php?action=add" method="post">

                            <label for="email"><?php echo LABEL_EMAIL;?></label>

                            <p><?php echo draw_input_field('email');?></p>

                            <label for="firstname"><?php echo LABEL_FIRSTNAME;?></label>

                            <p><?php echo draw_input_field('firstname');?></p>

                            <label for="lastname"><?php echo LABEL_LASTNAME;?></label>

                            <p><?php echo draw_input_field('lastname');?></p>

                            <div class="r2">
                                <p><?php echo draw_input_field('send', TEXT_INVITE_USER, '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'edit':
                    if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {
                        $id = $_GET['uID'];
                        $data = array(':id'=> $id);
                        $db->query('SELECT firstname, lastname, usermail, UNIX_TIMESTAMP(last_seen) as last_online FROM ' . table_users . ' WHERE id=:id LIMIT 1',$data);
                        $UserData = $db->fetch();
                        ?>
                        <div>
                            <h2><?php echo TEXT_EDIT_USER;?></h2>
                            <?php
                            if (is_null($UserData['last_online'])) {
                                ?>
                                <p>
                                    <strong class="error"><?php echo MSG_E_NOT_LOGGED_IN;?></strong> <a
                                        href="users.php?action=reinvite&amp;uID=<?php echo $id; ?>"><?php echo TEXT_REINVITE;?></a>
                                </p>
                            <?php
                            }
                            ?>
                            <form id="form" action="users.php?action=edit&amp;uID=<?php echo $id; ?>" method="post">

                                <label for="email"><?php echo LABEL_EMAIL;?></label>

                                <p><?php echo draw_input_field('email', $UserData['usermail']); ?></p>

                                <label for="firstname"><?php echo LABEL_LASTNAME;?></label>

                                <p><?php echo draw_input_field('firstname', $UserData['firstname']); ?></p>

                                <label for="lastname"><?php echo LABEL_LASTNAME;?></label>

                                <p><?php echo draw_input_field('lastname', $UserData['lastname']); ?></p>

                                <div class="r2">
                                    <p><?php echo draw_input_field('send', TEXT_EDIT, '', 'submit', false); ?>
                                    <a class="btn cancel" href="users.php?position=confirm_delete&amp;uID=<?php echo $id; ?>"><?php echo TEXT_DELETE_USER;?></a></p>
                                </div>
                            </form>
                        </div>
                    <?php
                    }
                    break;
                case 'confirm_delete':
                    if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {
                        $id = (int)$_GET['uID'];
                        ?>
                        <h2><?php echo TEXT_USER_DELETE;?></h2>
                        <form id="form" method="post" action="users.php?action=delete&amp;uID=<?php echo $id; ?>">
                            <p><?php echo TEXT_USER_DELETE_CONFIRM;?></p>

                            <p>
                                <a class="btn cancel" href="javascript:history.back();"><?php echo TEXT_CANCEL;?></a>
                                <button name="delete" class="proceed" type="submit"><?php echo TEXT_DELETE;?></button>
                            </p>

                        </form>
                    <?php
                    }
                    break;
            }
        } else {
            ?>
            <h2><?php echo TITLE_OVERVIEW_USERS;?></h2>
            <p id="subtitle">
                <a href="users.php?position=add"><?php echo TEXT_INVITE_USER;?></a> |
                <a href="news.php?position=mail"><?php echo TEXT_MASS_MAIL;?></a>
            </p>

            <table cellpadding="0" cellspacing="0" id="users">
                <thead>
                <tr>
                    <th>
                        <?php echo TABLE_NAME ?>
                    </th>
                    <th class="last_seen">
                        <?php echo TABLE_LAST_SEEN?>
                    </th>
                    <th><?php echo TABLE_ACTION;?></th>
                </tr>
                </thead>
                <tbody>
                <?php
                $db->query('SELECT id, firstname, lastname, usermail,UNIX_TIMESTAMP(last_seen) as last_online FROM ' . table_users . ' ORDER BY id');
                $n = 0;
                while ($row = $db->fetch()) {
                    ?>
                    <tr>
                        <td>
                            <?php echo $row['firstname'] . ' ' . $row['lastname']; ?>
                        </td>
                        <td>
                            <?php
                            if ($row['last_online'] > 0) {
                                echo date('d.m.y H:i:s', $row['last_online']);
                            } else {
                                echo TEXT_NEVER;
                            }
                            ?>
                        </td>
                        <td>
                            <a href="users.php?position=edit&amp;uID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT;?></a> |
                            <a href="users.php?position=confirm_delete&amp;uID=<?php echo $row['id'] ?>"><?php echo TEXT_DELETE;?></a>
                        </td>
                    </tr>
                    <?php
                    $n++;
                }
                ?>
                </tbody>
            </table>
        <?php
        }
        ?>
    </div>
    <?php require('inc/footer.php'); ?>