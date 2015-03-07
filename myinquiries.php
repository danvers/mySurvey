<?php

/**
 * @author Dan Verständig
 */

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

$Cats = new Categories($db);
$Avatar = new Avatar($db);

if (isset($_GET['position'])) {
    if (!isset($_GET['aID']) || !is_numeric($_GET['aID']) || !$Avatar->isLegal($_GET['aID'], $User->__get('id')))
        header('Location:myinquiries.php');
}
if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case 'edit':
            if (!isset($_GET['aID']) || !is_numeric($_GET['aID'])) {
                header('Location:myinquiries.php');
            } else {
                $id = $_GET['aID'];
                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }
                $messageStack->add_session('general', MSG_UPDATE_SUCCESS, 'success');

                header('Location:myinquiries.php?position=edit&category=' . $id);
            }
            break;
        case 'delete':
            if (!isset($_GET['aID']) || !is_numeric($_GET['aID'])) {
                header('Location:myinquiries.php');
            } else {

                $id = $_GET['aID'];

                if ($Avatar->delete($id, $User->__get('id'))) {
                    $messageStack->add_session('general', MSG_ENTRY_DELETED, 'success');
                    header('Location:myinquiries.php');
                } else {
                    header('Location:myinquiries.php');
                }

            }
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

                case 'confirm_delete':
                    if ((!isset($_GET['aID']) || !is_numeric($_GET['aID']))) {
                        ?>
                        <h2><?php echo TITLE_NO_ENTRY;?></h2>
                    <?php
                    } else {
                        $id = $_GET['aID'];
                        $data = array(':id'=>$id, ':userid'=>$User->__get('id'));
                        $db->query('SELECT title FROM ' . table_survey . ' WHERE id = :id AND userid = :userid LIMIT 1',$data);
                        $name = $db->fetch();
                        ?>
                        <h2><?php echo TEXT_DELETE;?></h2>
                        <form id="form" method="post" action="myinquiries.php?action=delete&amp;aID=<?php echo $id; ?>">

                            <p><?php echo sprintf(TEXT_DELETE_CONFIRM,$name['title']);?></p>

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
            <h2><?php echo TITLE_MY_ENTRIES;?></h2>
            <p id="subtitle">
                <a href="inquiry.php?position=add"><?php echo TEXT_ENTRY_ADD;?></a>
            </p>
            <?php
            $db->query('SELECT comments, id,userid,url,title,description FROM ' . table_survey . ' WHERE userid=:id ORDER BY id DESC',array(':id'=>$User->__get('id')));
            if ($db->rowCount() >0) {
                ?>
                <ol id="avatarlist">
                    <?php
                    $n = 0;
                    while ($row = $db->fetch()) {
                        $infoComments = '<small>';
                        $numComment = $row['comments'];

                        if ($numComment < 1) {
                            $infoComments .= TEXT_NO_COMMENTS;
                        } elseif ($numComment == 1) {
                            $infoComments .= TEXT_ONE_COMMENT;
                        } else {
                            $infoComments .= $row['comments'] .' ' .TEXT_COMMENTS;
                        }
                        $infoComments .= '</small>';
                        ?>
                        <li>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                            <p>
                                <a href="inquiry.php?position=evaluate&amp;cID=1&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT_ENTRY;?></a> |
                                <a href="feedback.php?position=show&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_FEEDBACK;?></a>
                                (<?php echo $infoComments; ?>) |
                                <a href="inquiry.php?position=edit&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT_SURVEY;?></a> |
                                <a href="myinquiries.php?position=confirm_delete&amp;aID=<?php echo $row['id']; ?>"><?php echo TEXT_DELETE_ENTRY;?></a> |
                                <?php echo TEXT_EXPORT;?>
                                <a href="csv_export.php?aID=<?php echo $row['id']; ?>">CSV</a> |
                                <a href="txt_export.php?aID=<?php echo $row['id']; ?>">TXT</a> |
                                <a href="xml_export.php?aID=<?php echo $row['id']; ?>">XML</a>
                            </p>
                            <p>
                                <?php if (strlen($row['url'])) { ?>
                                    <strong>URI</strong> <a
                                        href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a><br/>
                                <?php
                                }
                                if (strlen($row['description'])){
                                ?>
                                <strong><?php echo TEXT_DESCRIPTION;?></strong> <?php echo $row['description']; ?></p>
                            <?php
                            }
                            ?>
                        </li>
                        <?php
                        $n++;
                    }
                    ?>
                </ol>
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