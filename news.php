<?php

/**
 * @author Dan Verständig
 */

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

$Cats = new Categories($db);
$Message = new Message($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'add':
            if (strlen(db_prepare_input($_POST['text'])) >= FIELD_MIN_LENGTH && strlen(db_prepare_input($_POST['title'])) >= FIELD_MIN_LENGTH) {

                $title = db_prepare_input($_POST['title']);
                $text = db_prepare_input($_POST['text']);
                $data = array(':title'=> $title, ':text' => $text, ':userid' => intval($User->__get('id')));
                $db->query("INSERT INTO " . table_news . " (title, text, userid)
                                VALUES (:title,:text,:userid)", $data);
                $messageStack->add_session('general', 'Information wurde hinzugefügt', 'success');
                header('Location:news.php');
            } else {
                $messageStack->add_session('general', 'Titel sowie Text müssen aus min. ' . FIELD_MIN_LENGTH . ' Zeichen bestehen', 'error');
                header('Location:news.php?position=add');
            }
            break;

        case 'edit':

            if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {

                $id = $_GET['eID'];
                $postbit = null;
                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }
                if (strlen($postbit['title']) < FIELD_MIN_LENGTH || strlen($postbit['text']) < FIELD_MIN_LENGTH) {
                    $messageStack->add_session('general', 'Der Titel sowie der Text müssen mindestens ' . FIELD_MIN_LENGTH . ' Zeichen lang sein', 'error');
                    header('Location:news.php?position=edit&eID=' . $id);
                } else {
                    $data = array(
                                ':title'    =>  $postbit['title'],
                                ':text'     =>  $postbit['text'],
                                ':id'       =>  $id
                        );
                    $db->query("UPDATE " . table_news . " SET  title= :title, text = :text WHERE id = :id LIMIT 1",$data);

                    $messageStack->add_session('general', 'Information wurde bearbeitet', 'success');
                    header('Location:news.php?position=edit&eID=' . $id);
                }
            }
            break;

        case 'sendmail':

            if (strlen(htmlspecialchars($_POST['title'])) < FIELD_MIN_LENGTH || strlen(htmlspecialchars($_POST['text'])) < FIELD_MIN_LENGTH) {

                $messageStack->add_session('general', 'Betreff sowie Text müssen aus min. ' . FIELD_MIN_LENGTH . ' Zeichen bestehen', 'error');

                header('Location:news.php?position=mail');
            } else {
                $db->query('SELECT usermail FROM ' . table_users . ' WHERE usermail != :usermail',array(':usermail' => $User->__get('usermail')));

                $recipients = array();
                while ($row = $db->fetch()) {
                    $recipients[] = $row['usermail'];
                }
                $title = $_POST['title'];
                $content = $_POST['text'];

                $Message->massMail($User, $recipients, $content, $title);

                $messageStack->add_session('general', 'Rundmail wurde an alle Benutzer verschickt', 'success');

                header('Location:news.php');
            }
            break;

        case 'delete':
            if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {

                $id = $_GET['eID'];

                $db->query('DELETE FROM ' . table_news . ' WHERE id ="' . $id . '" LIMIT 1');

                $messageStack->add_session('general', 'Information/Ankündigung gelöscht', 'success');

                header('Location:news.php');

            }


            break;
    }
}
if (isset($_GET['position']) && ($_GET['position'] == 'edit')) {
    if (!isset($_GET['eID'])) {
        header('Location:news.php');
    }
} elseif (isset($_GET['position']) && ($_GET['position'] == 'preview')) {
    if (strlen(htmlspecialchars($_POST['title'])) < FIELD_MIN_LENGTH || strlen(htmlspecialchars($_POST['text'])) < FIELD_MIN_LENGTH) {
        $messageStack->add_session('general', 'Betreff sowie Text müssen aus min. ' . FIELD_MIN_LENGTH . ' Zeichen bestehen', 'error');

        header('Location:news.php?position=mail');
    }
}
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <meta name="audience" content="all"/>
        <title>News - <?php echo WORKSPACE_TITLE; ?></title>

        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>

        <script type="text/javascript" src="inc/javascripts/prototype.js"></script>

        <script type="text/javascript" src="inc/javascripts/scriptaculous.js"></script>

        <script type="text/javascript" src="inc/javascripts/effects.js"></script>

        <script type="text/javascript" src="inc/javascripts/simplescripts.js"></script>
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
                        <h2>Ankündigung/Information veröffentlichen</h2>

                        <form id="form" action="news.php?action=add" method="post">
                            <label for="news-title">Überschrift</label>

                            <p>
                                <?php echo draw_input_field('title', '', 'id="news-title"');?>
                            </p>

                            <label for="comment">Information</label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');

                                ?>
                            </p>

                            <div class="r2">
                                <p id="counter" class="error">&nbsp;</p>

                                <p class="submit"><?php echo draw_input_field('send', 'speichern', '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'edit':
                    $id = (int)$_GET['eID'];

                    $db->query('SELECT text,title FROM ' . table_news . ' WHERE id= :id LIMIT 1',array(':id'=>$id));
                    $fields = $db->fetch();
                    ?>
                    <div>
                        <h2>Ankündigung bearbeiten</h2>

                        <form id="form" action="news.php?action=edit&amp;eID=<?php echo $id;?>" method="post">
                            <label for="news-title">Überschrift</label>

                            <p>
                                <?php echo draw_input_field('title', $fields['title'], 'id="news-title"');?>
                            </p>

                            <label for="comment">Information</label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $fields['text'], 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');

                                ?>
                            </p>

                            <div class="r2">
                                <p id="counter" class="error">&nbsp;</p>

                                <p class="submit"><?php echo draw_input_field('send', 'speichern', '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'preview':
                    ?>
                    <div>
                        <h2>Vorschau</h2>

                        <form id="form" class="preview" action="news.php?action=sendmail" method="post">

                            <label for="news-title">Betreff</label>

                            <p>
                                <?php echo draw_input_field('title', $_POST['title'], 'id="news-title" readonly="readonly"');?>
                            </p>

                            <label for="comment">Nachricht</label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $_POST['text'], 'readonly="readonly" id="comment"');
                                ?>
                            </p>

                            <div class="r2">
                                <p id="counter" class="error">&nbsp;</p>

                                <p><?php echo draw_input_field('send', 'abschicken', '', 'submit', false);?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'mail':
                    ?>
                    <div>
                        <h2>Rundmail verfassen</h2>

                        <form id="form" action="news.php?position=preview" method="post">

                            <label for="news-title">Nachricht</label>

                            <p>
                                <?php echo draw_input_field('title', '', 'id="news-title"');?>
                            </p>

                            <label for="comment">Nachricht</label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');
                                ?>
                            </p>

                            <div class="r2">
                                <p id="counter" class="error">&nbsp;</p>

                                <p><?php echo draw_input_field('send', 'Vorschau', '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;

                case 'confirm_delete':
                    if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {
                        $id = $_GET['eID'];
                        ?>
                        <h2>Eintrag löschen</h2>
                        <form id="form" method="post" action="news.php?action=delete&amp;eID=<?php echo $id; ?>">

                            <p>Ankündigung wirklich löschen?</p>

                            <p>
                                <a class="btn cancel" href="javascript:history.back();">abbrechen</a>
                                <button name="delete" class="proceed" type="submit">löschen</button>
                            </p>

                        </form>
                    <?php
                    }
                    break;
            }
        } else {
            ?>
            <h2>Informationen/Ankündigungen</h2>
            <p id="subtitle">
                <a href="news.php?position=add">Information/Ankündigung hinzufügen</a>&nbsp;|&nbsp;
                <a href="news.php?position=mail">Rundmail verfassen</a>
            </p>
            <?php
            $db->query('SELECT tn.id, tn.title, UNIX_TIMESTAMP(tn.timestamp) AS timestamp, tn.text, u.firstname, u.lastname FROM ' . table_news . ' tn, ' . table_users . ' u WHERE tn.userid = u.id ORDER BY tn.timestamp ASC');
            ?>

            <?php
            if ($db->rowCount() > 0) {
                ?>

                <ul id="newslist">
                    <?php
                    $n = 0;
                    while ($row = $db->fetch()) {
                        $infoComments = '<small>';
                        $infoComments .= '</small>';
                        ?>
                        <li <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>>
                            <h3><?php echo date('d.m.y', $row['timestamp']); ?> - <?php echo $row['title']; ?></h3>
                            <a href="news.php?position=edit&amp;eID=<?php echo $row['id']; ?>">bearbeiten</a>&nbsp;|&nbsp;
                            <a href="news.php?position=confirm_delete&amp;eID=<?php echo $row['id']; ?>">löschen</a>

                            <p><?php echo $row['text']; ?></p>

                        </li>
                        <?php
                        $n++;
                    }
                    ?>
                </ul>
            <?php
            } else {
                ?>
                <p>derzeit ist keine Information/Ankündigung eingetragen</p>
            <?php
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>