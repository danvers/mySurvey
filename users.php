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

                    $messageStack->add_session('general', 'Die E-Mailadresse ist entweder zu kurz oder fehlerhaft', 'error');
                    $error = true;
                }
            }
            if (isset($postbit['firstname'])) {

                if (strlen($postbit['firstname']) < NAME_MIN_LENGTH) {

                    $messageStack->add_session('general', 'Der Vorname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');

                    $error = true;
                }
            }
            if (isset($postbit['lastname'])) {

                if (strlen($postbit['lastname']) < NAME_MIN_LENGTH) {

                    $messageStack->add_session('general', 'Der Nachname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');

                    $error = true;
                }
            }
            if ($error == false) {

                if ($User->createNewUser($postbit)) {

                    $messageStack->add_session('general', 'Benutzer erfolgreich hinzugefügt eine E-mail mit den Daten wurde an ' . $postbit['email'] . ' versandt.', 'success');

                    header('Location:users.php');
                } else {

                    $messageStack->add_session('general', 'Der Benutzer konnte nicht hinzugefügt werden.', 'error');

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

                        $messageStack->add_session('general', 'Die E-Mailadresse ist entweder zu kurz oder fehlerhaft', 'error');
                        $error = true;
                    }
                }
                if (isset($postbit['firstname'])) {

                    if (strlen($postbit['firstname']) < NAME_MIN_LENGTH) {

                        $messageStack->add_session('general', 'Der Vorname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');

                        $error = true;
                    }
                }
                if (isset($postbit['lastname'])) {

                    if (strlen($postbit['lastname']) < NAME_MIN_LENGTH) {

                        $messageStack->add_session('general', 'Der Der Nachname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');

                        $error = true;
                    }
                }
                if ($error == false) {
                    $User->editUser($id, $postbit);

                    $messageStack->add_session('general', 'Benutzerdaten erfolgreich geändert.', 'success');
                }
                header('Location:users.php?position=edit&uID=' . $id);
            }
            break;

        case 'reinvite':

            if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {

                $id = $_GET['uID'];
                $email = $User->__get('usermail');
                if ($User->reInviteUser($id)) {
                    $messageStack->add_session('general', 'Benutzer wurde erneut benachrichtigt eine E-mail mit den Daten wurde an ' . $email . ' versandt.', 'success');
                } else {
                    $messageStack->add_session('general', 'Es trat ein Fehler auf, der Benutzer wurde nicht informiert.', 'error');
                }
                header('Location:users.php?position=edit&uID=' . $id);
            }

            break;

        case 'delete':

            if (isset($_GET['uID']) && is_numeric($_GET['uID'])) {
                $id = $_GET['uID'];
                $User->deleteUser($id);
                $messageStack->add_session('general', 'Nutzer gelöscht', 'success');

                $db->query('DELETE FROM ' . table_survey . ' WHERE userid="' . $id . '"');
                $messageStack->add_session('general', 'Einträge des Nutzers gelöscht', 'success');

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

    <title>Users - <?php echo WORKSPACE_TITLE; ?></title>

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
                        <h2>Nutzer einladen</h2>

                        <form id="form" action="users.php?action=add" method="post">

                            <label for="email">E-Mail</label>

                            <p><?php echo draw_input_field('email');?></p>

                            <label for="firstname">Vorname</label>

                            <p><?php echo draw_input_field('firstname');?></p>

                            <label for="lastname">Nachname</label>

                            <p><?php echo draw_input_field('lastname');?></p>

                            <div class="r2">
                                <p><?php echo draw_input_field('send', 'Benutzer einladen', '', 'submit', false); ?></p>
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
                            <h2>Nutzerdaten ändern</h2>
                            <?php
                            if (is_null($UserData['last_online'])) {
                                ?>
                                <p>
                                    <strong class="error">Der Benutzer hat sich noch nicht eingeloggt.</strong> <a
                                        href="users.php?action=reinvite&amp;uID=<?php echo $id; ?>">Benutzer erneut
                                        einladen.</a>
                                </p>
                            <?php
                            }
                            ?>
                            <form id="form" action="users.php?action=edit&amp;uID=<?php echo $id; ?>" method="post">

                                <label for="email">E-Mail</label>

                                <p><?php echo draw_input_field('email', $UserData['usermail']); ?></p>

                                <label for="firstname">Vorname</label>

                                <p><?php echo draw_input_field('firstname', $UserData['firstname']); ?></p>

                                <label for="lastname">Nachname</label>

                                <p><?php echo draw_input_field('lastname', $UserData['lastname']); ?></p>

                                <div class="r2">
                                    <p><?php echo draw_input_field('send', 'Daten bearbeiten', '', 'submit', false); ?>
                                    <a class="btn cancel" href="users.php?position=confirm_delete&amp;uID=<?php echo $id; ?>">diesen Benutzer löschen</a></p>
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
                        <h2>Benutzer löschen</h2>
                        <form id="form" method="post" action="users.php?action=delete&amp;uID=<?php echo $id; ?>">
                            <p>Benutzer wirklich löschen?</p>

                            <p>
                                <a class="btn cancel" href="javascript:history.back();">Abbrechen</a>
                                <button name="delete" class="proceed" type="submit">Löschen</button>
                            </p>

                        </form>
                    <?php
                    }
                    break;
            }
        } else {
            ?>
            <h2>Übersicht der angemeldeten Nutzer</h2>
            <p id="subtitle">
                <a href="users.php?position=add">Benutzer einladen</a>&nbsp;|&nbsp;
                <a href="news.php?position=mail">Rundmail verfassen</a>
            </p>

            <table cellpadding="0" cellspacing="0" id="users">
                <thead>
                <tr>
                    <th>
                        <strong>Name</strong>
                    </th>
                    <th class="last_seen">
                        <strong>zuletzt gesehen</strong>
                    </th>
                    <th>Aktion</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $db->query('SELECT id, firstname, lastname, usermail,UNIX_TIMESTAMP(last_seen) as last_online FROM ' . table_users . ' ORDER BY id');
                $n = 0;
                while ($row = $db->fetch()) {
                    ?>
                    <tr>
                        <td <?php if ($n % 2 == 0) echo 'class="odd"'; ?>>
                            <?php echo $row['firstname'] . ' ' . $row['lastname']; ?>
                        </td>
                        <td <?php if ($n % 2 == 0) echo 'class="odd"'; ?>>
                            <?php
                            if ($row['last_online'] > 0) {
                                echo date('d.m.y H:i:s', $row['last_online']);
                            } else {
                                echo 'noch nie';
                            }
                            ?>
                        </td>
                        <td <?php if ($n % 2 == 0) echo 'class="odd"'; ?>>
                            <a href="users.php?position=edit&amp;uID=<?php echo $row['id']; ?>">Daten Bearbeiten</a>&nbsp;|&nbsp;
                            <a href="users.php?position=confirm_delete&amp;uID=<?php echo $row['id'] ?>">Löschen</a>
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
