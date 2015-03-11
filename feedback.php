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
                $data = array(
                            ':aid'  =>   $aID,
                            ':text' =>  $feedback,
                            ':uid'  =>  $User->__get('id')
                            );
                $db->query('INSERT INTO ' . table_feedback . '
                                (avatarid, text, userid) VALUES (:aid,:text,:uid)',$data);

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

        <title><?php echo TITLE;?> | <?php echo WORKSPACE_TITLE; ?></title>

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
        <script type="text/javascript" src="inc/javascripts/helper.js"></script>
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
                    $aID = $_GET['aID'];
                    ?>
                    <div>
                        <h2><?php echo sprintf(TITLE_FEEDBACK, $Avatar->getName($aID) );?></h2>

                        <form id="form" action="feedback.php?action=add" method="post">
                            <label><?php echo TEXT_SUBMIT_FEEDBACK;?></label>
                            <p>
                                <?php
                                echo draw_textarea_field('feedback', '60', '10', '', 'id="comment" data-limit="200"');

                                echo draw_hidden_field('aID', $aID);
                                ?>
                            </p>

                            <div class="r2">
                                <p id="t_comment" class="error">&nbsp;</p>
                                <p><?php echo draw_input_field('send', TEXT_SUBMIT, '', 'submit', false);?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'view':

                    $aID = $_GET['aID'];

                    ?>
                    <div>
                        <h2><?php echo sprintf(TITLE_FEEDBACK, $Avatar->getName($aID) );?></h2>

                        <p id="subtitle">
                            <a href="feedback.php?position=add&amp;aID=<?php echo $aID;?>"><?php echo TEXT_FEEDBACK_ADD;?> </a>
                        </p>
                        <?php
                        $userid = $User->__get('id');
                        $db->query('SELECT f.userid, u.firstname, u.lastname, f.text, UNIX_TIMESTAMP(f.timestamp) AS timestamp FROM ' . table_users . ' u LEFT JOIN ' . table_feedback . ' f ON u.id = f.userid WHERE f.avatarid = "' . $aID . '" ORDER by f.id DESC');
                        if ($db->rowCount() > 0) {
                            ?>
                            <ol id="avatarlist">
                                <?php
                                $n = 0;
                                while ($row = $db->fetch()) {
                                    ?>
                                    <li>
                                        <?php
                                        if ($row['userid'] == $userid) {
                                            ?>
                                            <p><?php echo TEXT_MY_COMMENT;?>
                                                <span><?php echo TEXT_CREATED;?> <?php echo date('d.m.y H:i', $row['timestamp']); ?></span>
                                            </p>
                                        <?php
                                        } else {
                                            ?>
                                            <p><?php echo TEXT_COMMENT_BY;?> <?php echo $row['firstname'] . ' ' . $row['lastname']; ?>
                                                <span><?php echo TEXT_CREATED;?> <?php echo date('d.m.y H:i', $row['timestamp']); ?></span>
                                            </p>
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
                            <p><?php echo TEXT_NO_COMMENTS;?></p>
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