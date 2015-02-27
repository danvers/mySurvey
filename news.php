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

                $db->query("INSERT INTO " . table_news . " (id, title, text, userid) VALUES ('','" . $db->escape_string($title) . "','" . $db->escape_string($text) . "','" . $User->__get('id') . "')");

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
                    $db->query("UPDATE " . table_news . " SET  title= '" . $db->escape_string($postbit['title']) . "', text = '" . $db->escape_string($postbit['text']) . "' WHERE id =  '" . $id . "' LIMIT 1");

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
                $db->query('SELECT usermail FROM ' . table_users . ' WHERE usermail != "' . $User->__get('usermail') . '"');

                while ($row = $db->fetchArray()) {
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
    if (!isset($_GET['eID']) || !$Message->exists($_GET['eID'], table_news)) {
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
        <title>Informationen - Online Kategorisierung</title>

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
                        <?php
                        if ($User->__get('userlevel') > DEMO_ACCOUNT){
                        ?>
                        <form action="news.php?action=add" method="post">
                            <?php
                            }else{
                            ?>
                            <form action="-">
                                <?php
                                }
                                ?>
                                <p class="left">Überschrift</p>

                                <p>
                                    <?php echo draw_input_field('title', '', 'style="width:300px;"');?>
                                </p>

                                <p class="left">Information</p>

                                <p>
                                    <?php
                                    echo draw_textarea_field('text', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');

                                    ?>
                                </p>

                                <p class="left">&nbsp;</p>

                                <p id="counter" class="error">&nbsp;</p>
                                <br/>
                                <?php
                                if ($User->__get('userlevel') > DEMO_ACCOUNT) {
                                    ?>
                                    <p class="left">&nbsp;</p>
                                    <p><?php echo draw_input_field('send', 'speichern', '', 'submit', false); ?></p>
                                <?php
                                } else {
                                    ?>
                                    <p class="left">&nbsp;</p><span class="demosubmit">speichern</span> [<a
                                        class="tooltip" href="#">?<span style="width:200px;">Nicht mit dem Demo-Account möglich.</span></a>]
                                <?php
                                }
                                ?>
                            </form>
                    </div>
                    <?php
                    break;
                case 'edit':
                    $id = $_GET['eID'];

                    $db->query('SELECT text,title FROM ' . table_news . ' WHERE id="' . $id . '" LIMIT 1');
                    $fields = $db->fetchArray();
                    ?>
                    <div>
                        <h2>Ankündigung/Information bearbeiten</h2>

                        <form action="news.php?action=edit&amp;eID=<?php echo $id;?>" method="post">
                            <p class="left">Überschrift</p>

                            <p>
                                <?php echo draw_input_field('title', $fields['title'], 'style="width:300px;"');?>
                            </p>

                            <p class="left">Information</p>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $fields['text'], 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');

                                ?>
                            </p>

                            <p class="left">&nbsp;</p>

                            <p id="counter" class="error">&nbsp;</p>
                            <br/>

                            <p class="left">&nbsp;</p>

                            <p><?php echo draw_input_field('send', 'speichern', '', 'submit', false);?></p>
                        </form>
                    </div>
                    <?php
                    break;
                case 'preview':
                    ?>
                    <div>
                        <h2>Vorschau</h2>

                        <form action="news.php?action=sendmail" method="post">
                            <p class="left">Betreff:</p>

                            <p>
                                <?php echo draw_input_field('title', $_POST['title'], 'readonly="readonly" style="width:300px;border:1px solid #fff;""');?>
                            </p>

                            <p class="left">Nachricht:</p>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $_POST['text'], 'readonly="readonly" style="border:1px solid #fff;"');
                                ?>
                            </p>

                            <p class="left">&nbsp;</p>

                            <p id="counter" class="error">&nbsp;</p>
                            <br/>

                            <p class="left">&nbsp;</p>

                            <p><?php echo draw_input_field('send', 'abschicken', '', 'submit', false);?></p>
                        </form>
                    </div>
                    <?php
                    break;
                case 'mail':
                    ?>
                    <div>
                        <h2>Rundmail verfassen</h2>
                        <?php
                        if ($User->__get('userlevel') > DEMO_ACCOUNT){
                        ?>
                        <form action="news.php?position=preview" method="post">
                            <?php
                            }else{
                            ?>
                            <form action="-">
                                <?php
                                }
                                ?>

                                <p class="left">Betreff:</p>

                                <p>
                                    <?php echo draw_input_field('title', '', 'style="width:300px;"');?>
                                </p>

                                <p class="left">Nachricht:</p>

                                <p>
                                    <?php
                                    echo draw_textarea_field('text', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');
                                    ?>
                                </p>

                                <p class="left">&nbsp;</p>

                                <p id="counter" class="error">&nbsp;</p>
                                <br/>
                                <?php
                                if ($User->__get('userlevel') > DEMO_ACCOUNT) {
                                    ?>
                                    <p class="left">&nbsp;</p>
                                    <p><?php echo draw_input_field('send', 'Vorschau', '', 'submit', false); ?></p>
                                <?php
                                } else {
                                    ?>
                                    <p class="left">&nbsp;</p><span class="demosubmit">Vorschau</span> [<a
                                        class="tooltip" href="#">?<span style="width:200px;">Nicht mit dem Demo-Account möglich..</span></a>]
                                <?php
                                }
                                ?>
                            </form>
                    </div>
                    <?php
                    break;

                case 'confirm_delete':
                    if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {
                        $id = $_GET['eID'];
                        ?>
                        <h2>Eintrag löschen</h2>
                        <form method="post" action="news.php?action=delete&amp;eID=<?echo $id; ?>">

                            <p>Information/Ankündigung wirklich löschen?
                                <br/><br/>
                                <a href="javascript:history.back();">abbrechen</a>
                                <input name="delete" type="submit" style="margin-left:10px;" value="l&ouml;schen"/>
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
            if ($db->numRows() > 0) {
                ?>

                <ul id="newslist">
                    <?php
                    $n = 0;
                    while ($row = $db->fetchArray()) {
                        $infoComments = '<small style="color:#999;font-weight:normal;">';
                        $infoComments .= '</small>';
                        ?>
                        <li <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>>
                            <h3><?php echo date('d.m.y', $row['timestamp']); ?> - <?php echo $row['title']; ?></h3>
                            <?php
                            if ($User->__get('userlevel') > DEMO_ACCOUNT) {
                                ?>
                                <a href="news.php?position=edit&amp;eID=<?php echo $row['id']; ?>">bearbeiten</a>&nbsp;|&nbsp;
                                <a href="news.php?position=confirm_delete&amp;eID=<?php echo $row['id']; ?>">löschen</a>
                            <?php
                            } else {
                                ?>
                                <span style="text-decoration:line-through">bearbeiten</span>&nbsp;|&nbsp;<span
                                    style="text-decoration:line-through">löschen</span>
                                [<a class="tooltip" href="#">?<span style="width:200px;">Nicht mit dem Demo-Account möglich.</span></a>]
                            <?php
                            }
                            ?>

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