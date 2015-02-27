<?php

/**
 * @author Dan Verständig
 */

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

$Cats = new Categories($db);

$Avatar = new Avatar($db);

if (isset($_GET['action'])) {
    switch ($_GET['action']) {
        case 'add':
            if (strlen(db_prepare_input($_POST['feedback'])) > 5) {

                $aID = db_prepare_input($_POST['aID']);

                $feedback = db_prepare_input($_POST['feedback']);

                $db->query("INSERT INTO " . table_feedback . " (id, avatarid, text, userid) VALUES ('','" . $db->escape_string($aID) . "','" . $db->escape_string($feedback) . "','" . $User->__get('id') . "')");

                $db->query('UPDATE ' . table_survey . ' SET comments = comments+1 WHERE id="' . $aID . '" LIMIT 1');

                $messageStack->add_session('general', 'Feedback wurde hinzugefügt', 'success');
                header('Location:index.php');
            }
            break;
    }
}
if (isset($_GET['position']) && ($_GET['position'] == 'add')) {
    if (!isset($_GET['aID'])) {
        header('Location:index.php');
    }
}
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title>Feedback - <?php WORKSPACE_TITLE;?></title>

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

                    $aID = $_GET['aID'];

                    ?>
                    <div>
                        <h2>Feedback zum Avatar: <?php echo $Avatar->getName($aID);?></h2>

                        <form action="feedback.php?action=add" method="post">
                            <p class="left">Feedback abgeben</p>

                            <p>
                                <?php
                                echo draw_textarea_field('feedback', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');
                                echo draw_hidden_field('aID', $aID);
                                ?>
                            </p>

                            <p class="left">&nbsp;</p>

                            <p id="counter" class="error">&nbsp;</p>
                            <br/>

                            <p class="left">&nbsp;</p>

                            <p><?php echo draw_input_field('send', 'Feedback abgeben', '', 'submit', false);?></p>
                        </form>
                    </div>
                    <?php
                    break;
                case 'view':

                    $aID = $_GET['aID'];

                    ?>
                    <div>
                        <h2>Feedback zum Avatar: <?php echo $Avatar->getName($aID);?></h2>

                        <p id="subtitle">
                            <a href="feedback.php?position=add&amp;aID=<?php echo $aID;?>">Feedback/Kommentar
                                hinzufügen</a>
                        </p>
                        <?php
                        $userid = $User->__get('id');
                        $db->query('SELECT f.userid, u.firstname, u.lastname, f.text, UNIX_TIMESTAMP(f.timestamp) AS timestamp FROM ' . table_users . ' u LEFT JOIN ' . table_feedback . ' f ON u.id = f.userid WHERE f.avatarid = "' . $aID . '" ORDER by f.id DESC');
                        if ($db->numRows() > 0) {
                            ?>
                            <ol id="avatarlist">
                                <?php
                                $n = 0;
                                while ($row = $db->fetchArray()) {
                                    ?>
                                    <li <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>>
                                        <?php
                                        if ($row['userid'] == $userid) {
                                            ?>
                                            <p>Mein Kommentar <span style="color:#999;font-weight:normal;">
			erstellt am:<?php echo date('d.m.y H:i', $row['timestamp']); ?></span></p>
                                        <?php
                                        } else {
                                            ?>
                                            <p>Kommentar von <?php echo $row['firstname'] . ' ' . $row['lastname']; ?>
                                                <span
                                                    style="color:#999;">
			erstellt am:<?php echo date('d.m.y H:i', $row['timestamp']); ?></span></p>
                                        <?php
                                        }
                                        ?>
                                        <div class="notes">
                                            <?php echo $row['text']; ?>
                                        </div>
                                    </li>
                                    <?php
                                    $n++;
                                }
                                ?>
                            </ol>
                        <?php
                        } else {
                            ?>
                            <p>Es ist noch kein Kommentar vorhanden.</p>
                        <?php
                        }
                        ?>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>