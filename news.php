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
                $messageStack->add_session('general', MSG_POST_ADDED, 'success');
                header('Location:news.php');
            } else {
                $messageStack->add_session('general', sprintf(MSG_E_TEXT_MIN_LENGTH, FIELD_MIN_LENGTH), 'error');
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
                    $messageStack->add_session('general', sprintf(MSG_E_TEXT_MIN_LENGTH, FIELD_MIN_LENGTH), 'error');
                    header('Location:news.php?position=edit&eID=' . $id);
                } else {
                    $data = array(
                                ':title'    =>  $postbit['title'],
                                ':text'     =>  $postbit['text'],
                                ':id'       =>  $id
                        );
                    $db->query("UPDATE " . table_news . " SET  title= :title, text = :text WHERE id = :id LIMIT 1",$data);
                    $messageStack->add_session('general', MSG_POST_EDITED, 'success');
                    header('Location:news.php?position=edit&eID=' . $id);
                }
            }
            break;

        case 'sendmail':

            if (strlen(htmlspecialchars($_POST['title'])) < FIELD_MIN_LENGTH || strlen(htmlspecialchars($_POST['text'])) < FIELD_MIN_LENGTH) {
                $messageStack->add_session('general', sprintf(MSG_E_TEXT_MIN_LENGTH, FIELD_MIN_LENGTH), 'error');
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
                $messageStack->add_session('general', MSG_MAIL_SENT, 'success');
                header('Location:news.php');
            }
            break;

        case 'delete':
            if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {
                $id = $_GET['eID'];
                $db->query('DELETE FROM ' . table_news . ' WHERE id ="' . $id . '" LIMIT 1');
                $messageStack->add_session('general', MSG_POST_DELETED, 'success');
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
        $messageStack->add_session('general', sprintf(MSG_E_TEXT_MIN_LENGTH, FIELD_MIN_LENGTH), 'error');

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
                    ?>
                    <div>
                        <h2><?php echo TITLE_NEWS_ADD;?></h2>

                        <form id="form" action="news.php?action=add" method="post">
                            <label for="n-title"><?php echo TEXT_MESSAGE_TITLE;?></label>

                            <p>
                                <?php echo draw_input_field('title', '', 'class="n-title"');?>
                            </p>

                            <label for="comment"><?php echo TEXT_MESSAGE;?></label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', '', 'id="comment" data-limit="400"');
                                ?>
                            </p>

                            <div class="r2">
                                <p id="t_comment" class="error">&nbsp;</p>
                                <p class="submit"><?php echo draw_input_field('send', TEXT_SAVE, '', 'submit', false); ?></p>
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
                        <h2><?php echo TITLE_EDIT_NEWS;?></h2>

                        <form id="form" action="news.php?action=edit&amp;eID=<?php echo $id;?>" method="post">
                            <label for="n-title"><?php echo TEXT_MESSAGE_TITLE;?></label>

                            <p>
                                <?php echo draw_input_field('title', $fields['title'], 'class="n-title"');?>
                            </p>

                            <label for="comment"><?php echo TEXT_MESSAGE;?></label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $fields['text'], 'id="comment" data-limit="400"');

                                ?>
                            </p>
                            <p id="t_comment" class="error"></p>
                            <div class="r2">


                                <p class="submit"><?php echo draw_input_field('send', TEXT_SAVE, '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'preview':
                    ?>
                    <div>
                        <h2><?php echo LABEL_PREVIEW;?></h2>

                        <form id="form" class="preview" action="news.php?action=sendmail" method="post">

                            <label for="n-title"><?php echo TEXT_MESSAGE_TITLE;?></label>

                            <p>
                                <?php echo draw_input_field('title', $_POST['title'], 'class="n-title" readonly="readonly"');?>
                            </p>

                            <label for="comment"><?php echo TEXT_MESSAGE;?></label>

                            <p>
                                <?php
                                echo draw_textarea_field('text', '60', '10', $_POST['text'], 'readonly="readonly" id="comment"');
                                ?>
                            </p>

                            <div class="r2">
                                <p><?php echo draw_input_field('send', TEXT_SUBMIT, '', 'submit', false);?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;
                case 'mail':
                    ?>
                    <div>
                        <h2><?php echo TEXT_COMPOSE;?></h2>

                        <form id="form" action="news.php?position=preview" method="post">

                            <label for="n-title"><?php echo TEXT_MESSAGE_TITLE;?></label>

                            <p><?php echo draw_input_field('title', '', 'class="n-title"');?></p>

                            <label for="comment"><?php echo TEXT_MESSAGE;?></label>

                            <p><?php echo draw_textarea_field('text', '60', '10', '', 'id="comment" data-limit="0"'); ?></p>
                            <p id="t_comment" class="error">&nbsp;</p>
                            <div class="r2">
                                <p><?php echo draw_input_field('send', LABEL_PREVIEW, '', 'submit', false); ?></p>
                            </div>
                        </form>
                    </div>
                    <?php
                    break;

                case 'confirm_delete':
                    if (isset($_GET['eID']) && is_numeric($_GET['eID'])) {
                        $id = $_GET['eID'];
                        ?>
                        <h2><?php echo TEXT_DELETE_ENTRY;?></h2>
                        <form id="form" method="post" action="news.php?action=delete&amp;eID=<?php echo $id; ?>">

                            <p><?php echo TEXT_DELETE_NEWS_CONFIRM;?></p>

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
            <h2><?php echo TITLE_NEWS;?></h2>
            <p id="subtitle">
                <a href="news.php?position=add"><?php echo TEXT_ADD_NEWS;?></a> |
                <a href="news.php?position=mail"><?php echo TEXT_MASS_MAIL;?></a>
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
                        <li>
                            <h3><?php echo date('d.m.y', $row['timestamp']); ?> - <?php echo $row['title']; ?>
                            <a href="news.php?position=edit&amp;eID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT;?></a> |
                            <a href="news.php?position=confirm_delete&amp;eID=<?php echo $row['id']; ?>"><?php echo TEXT_DELETE;?></a></h3>

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
                <p><?php echo TEXT_NO_ENTRIES;?></p>
            <?php
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>