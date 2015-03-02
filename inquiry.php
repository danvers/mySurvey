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
            if (strlen(htmlspecialchars($_POST['name']))) {
                $postbit = array();
                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }
                if (!isset($postbit['url'])) $postbit['url'] = "";
                if (!isset($postbit['description'])) $postbit['description'] = "";

                $Avatar->add($postbit, $User->__get('id'));


                $messageStack->add_session('general', 'Eintrag eingefügt Kategorisierung kann beginnen', 'success');
                header('Location:myinquiries.php');
            } else {
                $messageStack->add_session('general', 'Es muss ein Titel angegeben werden', 'error');
                header('Location:inquiry.php?position=add');
            }


            break;
        case 'edit':
            if (!isset($_GET['aID']) || !$Avatar->isLegal($_GET['aID'], $User->__get('id'))) {

                header('Location:myinquiries.php');

            } else {
                $aID = $_GET['aID'];
                if (strlen(htmlspecialchars($_POST['title']))) {
                    foreach ($_POST as $postbits => $element) {

                        $postbit[$postbits] = db_prepare_input($element);

                    }

                    if (!isset($postbit['url'])) $postbit['url'] = "";
                    if (!isset($postbit['description'])) $postbit['description'] = "";


                    $Avatar->update($postbit, $aID, $User->__get('id'));


                    $messageStack->add_session('general', 'Daten aktualisiert', 'success');

                    header('Location:inquiry.php?position=edit&aID=' . $aID);

                } else {

                    $messageStack->add_session('general', 'Es muss ein Titel angegeben sein.', 'error');

                    header('Location:inquiry.php?position=edit&aID=' . $aID);
                }
            }
            break;
        case 'evaluate':
            if (!isset($_GET['cID'])) {

                $messageStack->add_session('general', 'Keine Kategorie übergeben', 'error');
                header('Location:myinquiries.php');
            }
            if (!isset($_GET['aID']) || !$Avatar->isLegal($_GET['aID'], $User->__get('id'))) {

                header('Location:myinquiries.php');

            } else {
                $querystring = "";
                $catID = $_GET['cID'];
                $aID = $_GET['aID'];
                $fields = array();
                $checkboxes = array();
                $db->query('SELECT id, params, type, notes  FROM ' . table_fields . ' WHERE cat_id="' . $catID . '"');

                while ($row = $db->fetchArray()) {

                    $add_data = array();

                    if ($row['type'] != 1) {
                        if ($row['notes']) {
                            $add_data = array('value' => strlen($_POST[$row['id']]) ? $_POST[$row['id']] : '',
                                'notes' => $_POST['note_' . $row['id']]);
                        } else {
                            $add_data = array('value' => strlen($_POST[$row['id']]) ? $_POST[$row['id']] : '',
                                'notes' => '');
                        }
                        $data = serialize($add_data);

                        if ((strlen($_POST[$row['id']]) || (isset($_POST['note_' . $row['id']])) && strlen($_POST['note_' . $row['id']]))) {

                            if ($row['type'] == 3 && $_POST[$row['id']] == 0) {

                                $querystring .= ', field_' . $row['id'] . ' = NULL';
                            } else {
                                $querystring .= ', field_' . $row['id'] . ' = "' . $db->escape_string($data) . '"';
                            }
                        } else {
                            $querystring .= ', field_' . $row['id'] . ' = NULL';
                        }
                    } else {

                        $cb_params = array();

                        $params = unserialize($row['params']);

                        foreach ($params as $value) {
                            if ($value['id'] > 0) {
                                if ((isset($_POST[$row['id'] . '_' . $value['id']]))) {
                                    $cb_params[] = $value['id'];
                                }
                            }
                        }

                        $cb_fields = serialize($cb_params);
                        if ($row['notes']) {
                            $add_data = array('value' => $cb_fields,
                                'notes' => $_POST['note_' . $row['id']]);
                        } else {
                            $add_data = array('value' => $cb_fields,
                                'notes' => '');
                        }


                        $data = serialize($add_data);

                        if ((isset($_POST['note_' . $row['id']]) && strlen($_POST['note_' . $row['id']])) || sizeof($cb_params) > 0) {
                            $querystring .= ', field_' . $row['id'] . ' = "' . $db->escape_string($data) . '"';
                        } else {
                            $querystring .= ', field_' . $row['id'] . ' = NULL';
                        }


                    }
                }
                if (strlen($querystring)) {
                    $querystring = substr($querystring, 1, strlen($querystring));
                }
                $Avatar->evaluate($querystring, $aID, $User->__get('id'));

                $messageStack->add_session('general', 'Kategorie aktualisiert', 'success');

                header('Location:inquiry.php?position=evaluate&cID=' . $catID . '&aID=' . $aID);

            }
            break;
    }
}
if (isset($_GET['position']) && $_GET['position'] == 'evaluate' && !isset($_GET['cID'])) {
    $messageStack->add_session('general', 'Keine Kategorie übergeben', 'error');
    header('Location:myinquiries.php');
}
if (isset($_GET['position']) && ($_GET['position'] == 'evaluate' || $_GET['position'] == 'edit')) {
    if (!isset($_GET['aID']) || !$Avatar->isLegal($_GET['aID'], $User->__get('id'))) {
        header('Location:myinquiries.php');
    }
}
if (isset($_GET['position']) && ($_GET['position'] == 'feedback')) {
    if (!isset($_GET['aID'])) {
        header('Location:index.php');
    }
}
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"/>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title>Inquiry - <?php echo WORKSPACE_TITLE; ?></title>

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
                case 'insert':
                    ?>
                    <h2>Titel</h2>
                    <p>
                        Hier steht noch nichts.
                        <br/>
                        An dieser Stelle sollte dann die Katalogisierung der Daten - abhängig von den Kategorien -
                        beginnen.
                    </p>
                    <?php
                    break;
                case 'add':
                    ?>
                    <h2>Eintrag erstellen</h2>
                    <form action="inquiry.php?action=add" method="post">
                        <p class="left">Name des Avatars</p>

                        <p>
                            <?php
                            echo draw_input_field('name', '', 'style="width:300px;"');
                            ?>
                        </p>

                        <p class="left">URI</p>

                        <p>
                            <?php
                            echo draw_input_field('url', '', 'style="width:300px;"');
                            ?>
                        </p>

                        <p class="left">Beschreibung</p>

                        <p>
                            <?php
                            echo draw_textarea_field('description', '60', '10', '', 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');
                            ?>
                        </p>

                        <p class="left">&nbsp;</p>

                        <p id="counter" class="error">&nbsp;</p>
                        <br/>

                        <p class="left">&nbsp;</p>

                        <p><?php echo draw_input_field('send', 'Eintrag hinzufügen', '', 'submit', false);?></p>

                    </form>
                    <?php
                    break;
                case 'edit':
                    $aID = $_GET['aID'];
                    $db->query('SELECT id,title,url,description FROM ' . table_survey . ' WHERE id="' . $aID . '" AND userid ="' . $User->__get('id') . '" LIMIT 1');
                    $result = $db->fetchArray();
                    ?>
                    <h2>Eintrag bearbeiten</h2>
                    <form action="inquiry.php?action=edit&amp;aID=<?php echo $aID;?>" method="post">
                        <p class="left">Name des Avatars</p>

                        <p>
                            <?php
                            echo draw_input_field('title', $result['title'], 'style="width:300px;"');
                            ?>
                        </p>

                        <p class="left">URI</p>

                        <p>
                            <?php
                            echo draw_input_field('url', $result['url'], 'style="width:300px;"');
                            ?>
                        </p>

                        <p class="left">Beschreibung</p>

                        <p>
                            <?php
                            echo draw_textarea_field('description', '60', '10', $result['description'], 'id="comment" onKeyDown="textLeft(\'comment\',\'counter\',200);"');
                            ?>
                        </p>

                        <p class="left">&nbsp;</p>

                        <p id="counter" class="error">&nbsp;</p>
                        <br/>

                        <p class="left">&nbsp;</p>

                        <p><?php echo draw_input_field('send', 'Eintrag bearbeiten', '', 'submit', false);?></p>

                    </form>
                    <?php
                    break;
                case 'evaluate':
                    $catID = $_GET['cID'];
                    $aID = $_GET['aID'];

                    $linkpath['position'] = 'evaluate';
                    $linkpath['filename'] = 'inquiry.php';

                    ?>
                    <div>
                        <h2>Kategorisierung: <?php echo $Avatar->getName($aID);?></h2>

                        <div id="left">

                            <?php
                            echo $Cats->listCategories(0, 1, $linkpath, false, $aID);
                            ?>
                            <br/>

                            <p id="legend">
                                <strong>Legende:</strong> <img src="img/complete.gif" alt=""/> = Kategorie abgearbeitet
                            </p>
                        </div>
                        <div id="right">
                            <h3><?php $catname = $Cats->__get($catID);
                                echo $catname['name'];?></h3>
                            <?php
                            $db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                            if ($db->numRows() > 0) {
                                ?>
                                <form method="post"
                                      action="inquiry.php?action=evaluate&amp;cID=<?php echo $catID; ?>&amp;aID=<?php echo $_GET['aID']; ?>">
                                    <table cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">
                                        <?php
                                        $db->query('SELECT * FROM ' . table_survey . ' WHERE id="' . $aID . '" AND userid ="' . $User->__get('id') . '" LIMIT 1');
                                        $fieldinputs = $db->fetchArray();
                                        $db->query('SELECT * FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                                        $n = 0;
                                        while ($row = $db->fetchArray()) {
                                            ?>
                                            <tr>
                                                <td style="width:300px;vertical-align:top;font-weight:bold;<?php if ($n % 2 == 0) echo 'background:#efefef;'; ?>">
                                                    <?php echo $row['name'];

                                                    if (isset($row['info']) && strlen($row['info'])) {
                                                        ?>
                                                        <span style="font-weight:normal;">
					[<a class="tooltip" href="#">?<span style="width:200px;"><?php echo $row['info']; ?></span></a>]
					</span>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>

                                                <td style="text-align:left;<?php if ($n % 2 == 0) echo 'background:#efefef;'; ?>">
                                                    <?php
                                                    switch ($row['type']) {
                                                        case 2:

                                                            $jsfieldname = 'feld_' . $row['id'];

                                                            if (isset($fieldinputs['field_' . $row['id']])) {
                                                                $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                $jsfieldname = 'feld_' . $row['id'];
                                                                echo draw_textarea_field($row['id'], '45', '5', stripslashes($data['value']), ' id="' . $jsfieldname . '" onKeyDown="textLeft(\'feld_' . $row['id'] . '\',\'counter_' . $row['id'] . '\',200);"');
                                                            } else {
                                                                echo draw_textarea_field($row['id'], '45', '5', '', 'id="' . $jsfieldname . '" onKeyDown="textLeft(\'feld_' . $row['id'] . '\',\'counter_' . $row['id'] . '\',200);"');
                                                            }
                                                            ?>
                                                            <br/>
                                                            <p id="counter_<?php echo $row['id']?>" class="error">
                                                                &nbsp;</p>
                                                            <?php

                                                            break;

                                                        case 1:
                                                            $notes = "";

                                                            if (isset($row['params'])) {

                                                                $params = unserialize($row['params']);

                                                                if (isset($fieldinputs['field_' . $row['id']]) && strlen($fieldinputs['field_' . $row['id']])) {

                                                                    foreach ($params as $value) {

                                                                        if ($value['id'] > 0) {

                                                                            echo '<label for="' . $value['text'] . '">' . $value['text'] . '</label>';

                                                                            $fieldparams = unserialize($fieldinputs['field_' . $row['id']]);
                                                                            $fieldvalues = unserialize($fieldparams['value']);
                                                                            $notes = $fieldparams['notes'];
                                                                            if (in_array($value['id'], $fieldvalues)) {
                                                                                echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', 'true', 'id="' . $value['text'] . '" style="text-align:left;vertical-align:middle;"');
                                                                            } else {
                                                                                echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', '', 'id="' . $value['text'] . '" style="text-align:left;vertical-align:middle;"');
                                                                            }

                                                                            echo '&nbsp;&nbsp;&nbsp;';
                                                                        }

                                                                    }
                                                                } else {
                                                                    foreach ($params as $value) {
                                                                        if ($value['id'] > 0) {
                                                                            echo '<label for="' . $value['text'] . '">' . $value['text'] . '</label>';
                                                                            echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', '', 'id="' . $value['text'] . '" style="text-align:left;vertical-align:middle;"');
                                                                            echo '&nbsp;&nbsp;&nbsp;';
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            if ($row['notes']) {
                                                                $jsfieldname = 'note_' . $row['id'];
                                                                ?>
                                                                <br/><br/>
                                                                <span style="float:left;">Anmerkungen</span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '" onKeyDown="textLeft(\'note_' . $row['id'] . '\',\'counter' . $row['id'] . '\',200);"');
                                                                ?>
                                                                <p id="counter<?php echo $row['id']; ?>" class="error">
                                                                    &nbsp;</p>
                                                            <?php
                                                            }

                                                            break;

                                                        case 0:
                                                            if (isset($row['params'])) {
                                                                $params = unserialize($row['params']);
                                                                $notes = "";
                                                                if (isset($fieldinputs['field_' . $row['id']])) {
                                                                    $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                    $defaultVal = ($data['value'] > 0) ? $data['value'] : 0;
                                                                    $notes = $data['notes'];
                                                                    echo drawSlider($row['id'], $defaultVal, $params['minVal'], $params['maxVal']);
                                                                } else {
                                                                    echo drawSlider($row['id'], 0, $params['minVal'], $params['maxVal']);
                                                                }
                                                            } else {
                                                                echo drawSlider($row['id'], 0);
                                                            }
                                                            if ($row['notes']) {
                                                                $jsfieldname = 'note_' . $row['id'];
                                                                ?>
                                                                <br/>
                                                                <span style="float:left;">Anmerkungen</span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '" onKeyDown="textLeft(\'note_' . $row['id'] . '\',\'counter' . $row['id'] . '\',200);"');
                                                                ?>
                                                                <p id="counter<?php echo $row['id']; ?>" class="error">
                                                                    &nbsp;</p>
                                                            <?php
                                                            }
                                                            break;

                                                        case 3:
                                                            $notes = "";
                                                            if (isset($row['params'])) {
                                                                $params = unserialize($row['params']);
                                                                if (isset($fieldinputs['field_' . $row['id']])) {
                                                                    $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                    $notes = $data['notes'];
                                                                    echo draw_pulldown_menu($row['id'], $params, $data['value']);
                                                                } else {
                                                                    echo draw_pulldown_menu($row['id'], $params);
                                                                }
                                                            }
                                                            if ($row['notes']) {
                                                                $jsfieldname = 'note_' . $row['id'];
                                                                ?>
                                                                <br/><br/>
                                                                <span>Anmerkungen</span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '" onKeyDown="textLeft(\'note_' . $row['id'] . '\',\'counter' . $row['id'] . '\',200);"');
                                                                ?>
                                                                <p id="counter<?php echo $row['id']; ?>" class="error">
                                                                    &nbsp;</p>
                                                            <?php
                                                            }

                                                            break;
                                                    }
                                                    ?>
                                                </td>
                                            </tr>
                                            <?php
                                            $n++;
                                        }
                                        ?>
                                    </table>
                                    <div style="border-top:1px dotted #ccc;">
                                        <p class="left">&nbsp;</p>

                                        <p style="text-align:left;"><?php echo draw_input_field('send', 'Kategorie bearbeiten', '', 'submit', false); ?></p>
                                    </div>
                                </form>
                            <?php
                            } else {
                                ?>
                                <p>Kategorie ist leer</p>
                            <?php
                            }
                            ?>
                        </div>
                        <div style="clear:both;">&nbsp;</div>
                    </div>
                    <?php
                    break;

                case 'view':
                    $catID = $_GET['cID'];
                    $aID = $_GET['aID'];

                    $linkpath['position'] = 'view';
                    $linkpath['filename'] = 'inquiry.php';
                    ?>
                    <div>
                        <h2>Datenübersicht: <?php echo $Avatar->getName($aID);?></h2>

                        <div id="left">
                            <?php
                            echo $Cats->listCategories(0, 1, $linkpath, false, $aID);
                            ?>
                        </div>
                        <div id="right">
                            <h3><?php $catname = $Cats->__get($catID);
                                echo $catname['name'];?></h3>
                            <?php
                            $db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                            if ($db->numRows() > 0) {
                                ?>
                                <table cellpadding="0" cellspacing="0" style="margin:auto;width:100%;">
                                    <?php
                                    $db->query('SELECT * FROM ' . table_survey . ' WHERE id="' . $aID . '" LIMIT 1');
                                    $fieldinputs = $db->fetchArray();
                                    $db->query('SELECT * FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                                    $n = 0;
                                    while ($row = $db->fetchArray()) {
                                        ?>
                                        <tr>
                                            <td style="width:300px;vertical-align:top;font-weight:bold;<?php if ($n % 2 == 0) echo 'background:#efefef;'; ?>">
                                                <?php echo $row['name'];

                                                if (isset($row['info']) && strlen($row['info'])) {
                                                    ?>
                                                    <span style="font-weight:normal;">
					[<a class="tooltip" href="#">?<span style="width:100px;"><?php echo $row['info']; ?></span></a>]
					</span>
                                                <?php
                                                }
                                                ?>
                                            </td>

                                            <td style="text-align:left;<?php if ($n % 2 == 0) echo 'background:#efefef;'; ?>">
                                                <?php
                                                switch ($row['type']) {
                                                    case 2:
                                                        $data = array();

                                                        if (isset($fieldinputs['field_' . $row['id']])) {
                                                            $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                        } else {
                                                            $data['value'] = '<span class="error">Bislang keine Angabe</span>';
                                                        }
                                                        ?>
                                                        <div
                                                            class="notes"><?php echo stripslashes(nl2br($data['value']));?></div>
                                                        <?php

                                                        break;

                                                    case 1:

                                                        $notes = "";
                                                        $cb_fields = "";
                                                        if (isset($row['params'])) {
                                                            $params = unserialize($row['params']);

                                                            foreach ($params as $value) {
                                                                if ($value['id'] > 0 && isset($value['text']) && strlen($value['text'])) {
                                                                    if (isset($fieldinputs['field_' . $row['id']]) && strlen($fieldinputs['field_' . $row['id']])) {
                                                                        $fieldparams = unserialize($fieldinputs['field_' . $row['id']]);

                                                                        $fieldvalues = unserialize($fieldparams['value']);

                                                                        if (in_array($value['id'], $fieldvalues)) {
                                                                            $cb_fields .= $value['text'];
                                                                            $cb_fields .= ', ';
                                                                        }
                                                                    }

                                                                }
                                                            }
                                                            echo substr($cb_fields, 0, $cb_fields - 2);
                                                        }
                                                        if (isset($fieldinputs['field_' . $row['id']])) {

                                                            $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                            $notes = $data['notes'];

                                                            if ($row['notes'] && strlen($notes)) {

                                                                ?>
                                                                <br/><br/>
                                                                <span>Anmerkungen:</span>
                                                                <div
                                                                    class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
                                                            <?php
                                                            }
                                                        } else {
                                                            echo '<span>Keine Auswahl geroffen</span>';
                                                        }
                                                        break;

                                                    case 0:
                                                        if (isset($row['params'])) {
                                                            $params = unserialize($row['params']);
                                                            $notes = "";
                                                            if (isset($fieldinputs['field_' . $row['id']])) {
                                                                $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                $defaultVal = ($data['value'] > 0) ? $data['value'] : 0;
                                                                $notes = $data['notes'];
                                                                echo drawSlider($row['id'], $defaultVal, $params['minVal'], $params['maxVal'], 1);
                                                            } else {
                                                                //echo drawSlider($row['id'],0,$params['minVal'],$params['maxVal'],1);
                                                                echo '<span class="error">Bislang keine Angabe</span>';
                                                            }
                                                        } else {
                                                            echo drawSlider($row['id'], 0, '', '', 1);
                                                        }
                                                        if ($row['notes'] && strlen($notes)) {
                                                            ?>
                                                            <span>Anmerkungen:</span>
                                                            <div
                                                                class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
                                                        <?php
                                                        }
                                                        break;

                                                    case 3:
                                                        $notes = "";
                                                        if (isset($row['params'])) {
                                                            $params = unserialize($row['params']);
                                                            if (isset($fieldinputs['field_' . $row['id']])) {

                                                                $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                $notes = $data['notes'];
                                                                echo $params[$data['value']]['text'];

                                                            } else {
                                                                echo '<span class="error">Bislang keine Angabe</span>';
                                                            }
                                                        }
                                                        if ($row['notes'] && strlen($notes)) {
                                                            ?>
                                                            <br/><br/>
                                                            <span>Anmerkungen:</span>
                                                            <div
                                                                class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
                                                        <?php
                                                        }

                                                        break;
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php
                                        $n++;
                                    }
                                    ?>
                                </table>
                                <?php
                                if ($Avatar->isLegal($aID, $User->__get('id'))) {
                                    ?>
                                    <div style="border-top:1px solid #ccc;">
                                        <p class="left">&nbsp;</p>

                                        <p style="text-align:left;">
                                            <a href="inquiry.php?position=evaluate&amp;aID=<?php echo $aID; ?>&amp;cID=<?php echo $catID; ?>">diese
                                                Kategorie bearbeiten</a></p>
                                    </div>
                                <?php
                                }
                                ?>
                            <?php
                            } else {
                                ?>
                                <p>Kategorie ist leer</p>
                            <?php
                            }
                            ?>
                        </div>
                        <div style="clear:both;">&nbsp;</div>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>