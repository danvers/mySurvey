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

                $messageStack->add_session('general', 'Alle Änderungen übernommen', 'success');

                header('Location:myinquiries.php?position=edit&category=' . $id);
            }
            break;
        case 'delete':
            if (!isset($_GET['aID']) || !is_numeric($_GET['aID'])) {
                header('Location:myinquiries.php');
            } else {

                $id = $_GET['aID'];

                if ($Avatar->delete($id, $User->__get('id'))) {
                    $messageStack->add_session('general', 'Eintrag gelöscht', 'success');
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

        <title>My Inquiries - <?php echo WORKSPACE_TITLE; ?></title>

        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>

        <script type="text/javascript" src="inc/javascripts/scriptaculous.js"></script>

        <script type="text/javascript" src="inc/javascripts/prototype.js"></script>

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

                case 'confirm_delete':
                    if ((!isset($_GET['aID']) || !is_numeric($_GET['aID']))) {
                        ?>
                        <h2>Fehler, keine ID angegeben</h2>
                    <?php
                    } else {
                        $id = $_GET['aID'];
                        $db->query('SELECT title FROM ' . table_survey . ' WHERE id="' . $id . '" AND userid="' . $User->__get('id') . '" LIMIT 1');
                        $name = $db->fetch();
                        ?>
                        <h2>Eintrag löschen</h2>
                        <form method="post" action="myinquiries.php?action=delete&amp;aID=<?echo $id; ?>">

                            <p>Eintrag &raquo;<?php echo $name['title']; ?>&laquo; &amp; erhobene Daten unwideruflich l&ouml;schen?
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
            <h2>Meine Avatare</h2>
            <p id="subtitle">
                <a href="inquiry.php?position=add">neue Eintrag einstellen</a>
            </p>
            <?php
            $db->query('SELECT comments, id,userid,url,title,description FROM ' . table_survey . ' WHERE userid="' . $User->__get('id') . '" ORDER BY id DESC');
            if ($db->numRows() > 0) {
                ?>
                <ol id="avatarlist">
                    <?php
                    $n = 0;
                    while ($row = $db->fetchArray()) {
                        $infoComments = '<small style="color:#999;font-weight:normal;">';
                        $numComment = $row['comments'];

                        if ($numComment < 1) {
                            $infoComments .= 'keine Kommentare';
                        } elseif ($numComment == 1) {
                            $infoComments .= '1 Kommentare';
                        } else {
                            $infoComments .= $row['comments'] . ' Kommentare';
                        }
                        $infoComments .= '</small>';
                        ?>
                        <li <?php if ($n % 2 == 0) echo 'style="background:#efefef;"'; ?>>
                            <h3><?php echo htmlspecialchars($row['title']); ?></h3>

                            <p>
                                <a href="inquiry.php?position=evaluate&amp;cID=1&amp;aID=<?php echo $row['id']; ?>">Avatar
                                    kategorisieren</a>&nbsp;|&nbsp;
                                <a href="feedback.php?position=show&amp;aID=<?php echo $row['id']; ?>">Feedback</a>
                                (<?php echo $infoComments; ?>)&nbsp;|&nbsp;

                                <a href="inquiry.php?position=edit&amp;aID=<?php echo $row['id']; ?>">Rahmendaten
                                    bearbeiten</a>&nbsp;|&nbsp;

                                <a href="myinquiries.php?position=confirm_delete&amp;aID=<?php echo $row['id']; ?>">Eintrag
                                    löschen</a>

                                &nbsp;|&nbsp;Exportieren als:
                                <a href="csv_export.php?aID=<?php echo $row['id']; ?>">CSV</a>&nbsp;|&nbsp;
                                <a href="txt_export.php?aID=<?php echo $row['id']; ?>">TXT</a>&nbsp;|&nbsp;
                                <a href="xml_export.php?aID=<?php echo $row['id']; ?>">XML</a>
                                <br/><br/>
                                <?php if (strlen($row['url'])) { ?>
                                    <strong>URI:</strong> <a
                                        href="<?php echo $row['url']; ?>"><?php echo $row['url']; ?></a><br/>
                                <?php
                                }
                                if (strlen($row['description'])){
                                ?>
                                <strong>Beschreibung:</strong> <?php echo $row['description']; ?></p>
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
                <p>noch keine Avatare bearbeitet.</p>
            <?php
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>