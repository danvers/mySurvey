<?php

/**
 * @author Dan Verständig
 */

define('IN_PAGE', true);
header('Content-Type: text/html; charset=UTF-8');

session_start();
error_reporting(E_ALL);

require_once('inc/functions/form_funcs.inc.php');
require_once('inc/functions/global_funcs.inc.php');
require_once('inc/cfg/config.inc.php');

preventCaching();


get_language();

$db = new database();

$messageStack = new messageStack();

$SessionManager = new SessionManagement(session_id());


if (isset($_GET['do'])) {

    switch ($_GET['do']) {

        case 'edit_userdata':

            $User = new User($_SESSION['userid'], $db);

            $userid = $_SESSION['userid'];

            foreach ($_POST as $postbits => $element) {
                $postbit[$postbits] = db_prepare_input($element);
            }
            if (isset($postbit['usermail'])) {

                if (!check_email($postbit['usermail']) || strlen($postbit['usermail']) < 5) {

                    $messageStack->add('general', 'Die E-Mailadresse ist entweder zu kurz oder fehlerhaft', 'error');

                }
            }
            if (isset($postbit['firstname'])) {

                if (strlen($postbit['firstname']) < NAME_MIN_LENGTH) {

                    $messageStack->add('general', 'Der Vorname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');


                }
            }
            if (isset($postbit['lastname'])) {

                if (strlen($postbit['lastname']) < NAME_MIN_LENGTH) {

                    $messageStack->add('general', 'Der Der Nachname muss mindestens ' . NAME_MIN_LENGTH . ' Zeichen lang sein.', 'error');

                }
            }

            if (isset($postbit['usermail']) && $messageStack->size('general') < 1) {

                if (check_email($postbit['usermail']) && $messageStack->size('general') < 1) {

                    $relogin = false;

                    if ($postbit['usermail'] != $User->__get('usermail')) {
                        $relogin = true;

                    }

                    foreach ($postbit as $postbits => $element) {
                        $User->__set($postbits, $element);
                    }
                    if ($relogin) {

                        $SessionManager->logout();

                        $SessionManager->login($postbit['usermail'], $User->__get('userpass'), 1);

                        $User = new User($userid, $db);

                    }
                    $messageStack->add('general', 'Profildaten aktualisiert', 'success');

                }

            }

            break;


        case 'edit_password':

            $User = new User($_SESSION['userid'], $db);
            $userid = $_SESSION['userid'];
            $usermail = $User->__get('usermail');

            $pw_old = htmlspecialchars($_POST["password0"]);

            $pw_a = htmlspecialchars($_POST["password1"]);

            $pw_b = htmlspecialchars($_POST["password2"]);

            if ($User->passIsValid($pw_old) && strlen($pw_a) > 5) {

                if ($pw_a === $pw_b) {

                    if ($User->changePass($pw_b)) {

                        $SessionManager->logout();

                        $SessionManager->login($usermail, md5($pw_b), 1);

                        $User = new User($userid, $db);

                        $messageStack->add('general', 'Passwort geändert', 'success');


                    } else {

                        $messageStack->add('general', 'Passwort konnte nicht geändert werden', 'error');

                    }
                } else {

                    $messageStack->add('general', 'Das neue Passwort muss richtig wiederholt werden', 'error');

                }
            } else {

                $messageStack->add('general', 'Das Passwort muss aus mindestens 6 Zeichen bestehen', 'error');

            }
            break;


        case 'resendpw':

            $User = new User(0, $db);

            $mail = htmlspecialchars($_POST['email']);
            if ($User->isUniqueEmail($mail)) {

                $User->resetPass($mail);

                $messageStack->add_session('general', 'Eine E-Mail mit den Informationen wurde an ' . $mail . ' versandt.', 'success');

                header('Location: index.php');
            } else {

                $messageStack->add_session('general', 'Die Anfrage wurde nicht bearbeitet.', 'error');

                header('Location: index.php?position=password');
            }

            break;


        case 'logout':

            $SessionManager->logout();

            header('Location: index.php');

            break;


        case 'login':

            $email = htmlspecialchars($_POST['email']);
            $pass = htmlspecialchars($_POST['pass']);

            if (isset($_POST['staylogged'])) {

                $SessionManager->login($email, md5($pass), 1);

            } else {

                $SessionManager->login($email, md5($pass), 0);

            }
            if ($SessionManager->logged_in()) {

                $User = new User($_SESSION['userid'], $db);

                header('Location: index.php');

            } else {

                $messageStack->add_session('general', 'E-Mail oder Passwort falsch.', 'error');

                header('Location: index.php');

            }

            break;


    }

} else {

    $SessionManager->login('', '');

    if ($SessionManager->logged_in()) {

        $User = new User($_SESSION['userid'], $db);

    }
}
if (isset($_GET['page']) && $_GET['page'] == 0) {
    header('Location: index.php');
}