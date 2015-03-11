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
            if (strlen(htmlspecialchars($_POST['title']))) {
                $postbit = array();
                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }
                if (!isset($postbit['url'])) $postbit['url'] = "";
                if (!isset($postbit['description'])) $postbit['description'] = "";
                $Avatar->add($postbit, $User->__get('id'));

                $messageStack->add_session('general', MSG_ENTRY_ADDED, 'success');
                header('Location:myinquiries.php');
            } else {
                $messageStack->add_session('general', MSG_E_TITLE, 'error');
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
                    $messageStack->add_session('general', MSG_UPDATE_SUCCESS, 'success');
                    header('Location:inquiry.php?position=edit&aID=' . $aID);

                } else {
                    $messageStack->add_session('general', MSG_E_TITLE, 'error');
                    header('Location:inquiry.php?position=edit&aID=' . $aID);
                }
            }
            break;
        case 'evaluate':
            if (!isset($_GET['cID'])) {
                $messageStack->add_session('general', MSG_E_NO_CATEGORY, 'error');
                header('Location:myinquiries.php');
            }
            if (!isset($_GET['aID']) || !$Avatar->isLegal($_GET['aID'], $User->__get('id'))) {
                header('Location:myinquiries.php');
            } else {
                $querystring = '';
                $catID = $_GET['cID'];
                $aID = $_GET['aID'];
                $fields = array();
                $checkboxes = array();
                $db->query('SELECT id, params, type, notes  FROM ' . table_fields . ' WHERE cat_id=:catid',array(':catid'=> $catID));
                $data = array();
                while ($row = $db->fetch()) {

                    $add_values = array();

                    if ($row['type'] != 1) {
                        if ($row['notes']) {
                            $add_values = array('value' => strlen($_POST[$row['id']]) ? $_POST[$row['id']] : '',
                                'notes' => $_POST['note_' . $row['id']]);
                        } else {
                            $add_values = array('value' => strlen($_POST[$row['id']]) ? $_POST[$row['id']] : '',
                                'notes' => '');
                        }
                        $value = serialize($add_values);

                        if ((strlen($_POST[$row['id']]) || (isset($_POST['note_' . $row['id']])) && strlen($_POST['note_' . $row['id']]))) {

                            if ($row['type'] == 3 && $_POST[$row['id']] == 0) {
                                $data['field_' . $row['id']] = null;
                            } else {
                                $data['field_' . $row['id']] = $value;
                            }
                        } else {
                            $data['field_' . $row['id']] = null;
                        }
                    } else {

                        $cb_params = array();
                        $params = unserialize($row['params']);

                        foreach ($params as $param) {
                            if ($param['id'] > 0) {
                                if ((isset($_POST[$row['id'] . '_' . $param['id']]))) {
                                    $cb_params[] = $param['id'];
                                }
                            }
                        }

                        $cb_fields = serialize($cb_params);
                        if ($row['notes']) {
                            $add_values = array('value' => $cb_fields,
                                'notes' => $_POST['note_' . $row['id']]);
                        } else {
                            $add_values = array('value' => $cb_fields,
                                'notes' => '');
                        }

                        $value = serialize($add_values);

                        if ((isset($_POST['note_' . $row['id']]) && strlen($_POST['note_' . $row['id']])) || sizeof($cb_params) > 0) {
                            $data['field_' . $row['id']] = $value;
                        } else {
                            $data['field_' . $row['id']] = null;
                        }
                    }
                    $querystring .= ', field_' . $row['id'] . ' = :field_'.$row['id'];
                }
                if (strlen($querystring)) {
                    $querystring = trim(substr($querystring, 1, strlen($querystring)));
                }
                $Avatar->evaluate($querystring, $aID, $User->__get('id'), $data);
                $messageStack->add_session('general', MSG_CATEGORY_UPDATED, 'success');
                header('Location:inquiry.php?position=evaluate&cID=' . $catID . '&aID=' . $aID);
            }
            break;
    }
}
if (isset($_GET['position']) && $_GET['position'] == 'evaluate' && !isset($_GET['cID'])) {
    $messageStack->add_session('general', MSG_E_NO_CATEGORY, 'error');
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

        <title><?php echo TITLE;?> | <?php echo WORKSPACE_TITLE; ?></title>

        <link rel="stylesheet" type="text/css" href="inc/stylesheets/layout.css" media="screen"/>
        <!--

        <script type="text/javascript" src="inc/javascripts/prototype.js"></script>
        <script type="text/javascript" src="inc/javascripts/scriptaculous.js"></script>
        <script type="text/javascript" src="inc/javascripts/effects.js"></script>
        <script type="text/javascript" src="inc/javascripts/simplescripts.js"></script>
        !-->
        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
        <script type="text/javascript" src="inc/javascripts/limiter.js"></script>
        <script type="text/javascript" src="inc/javascripts/simplescripts.js"></script>
        <link rel="stylesheet" href="//code.jquery.com/ui/1.11.3/themes/smoothness/jquery-ui.css">
        <script src="//code.jquery.com/ui/1.11.3/jquery-ui.js"></script>
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
                    <h2><?php echo TITLE_ENTRY_ADD;?></h2>
                    <form id="form" action="inquiry.php?action=add" method="post">
                        <label for="news-title"><?php echo LABEL_ENTRY_TITLE;?></label>
                        <p><?php echo draw_input_field('title', '','id="news-title"'); ?></p>
                        <label>URI</label>
                        <p><?php echo draw_input_field('url'); ?></p>
                        <label><?php echo TEXT_DESCRIPTION;?></label>
                        <p><?php echo draw_textarea_field('description', '60', '10', '', 'id="comment"'); ?></p>

                        <div class="r2">
                            <p id="t_comment" class="error">&nbsp;</p>
                            <p><?php echo draw_input_field('send', TITLE_ENTRY_ADD, '', 'submit', false);?></p>
                        </div>

                    </form>
                    <?php
                    break;
                case 'edit':
                    $aID = $_GET['aID'];
                    $data = array(':id'=>$aID, ':userid'=>$User->__get('id'));
                    $db->query('SELECT id,title,url,description FROM ' . table_survey . ' WHERE id = :id AND userid = :userid LIMIT 1', $data);
                    $result = $db->fetch();
                    ?>
                    <h2><?php echo TEXT_EDIT;?></h2>
                    <form id="form" action="inquiry.php?action=edit&amp;aID=<?php echo $aID;?>" method="post">
                        <label for="news-title"><?php echo LABEL_ENTRY_TITLE;?></label>
                        <p><?php echo draw_input_field('title', $result['title'],'id="news-title"'); ?></p>
                        <label>URI</label>
                        <p><?php echo draw_input_field('url', $result['url']); ?></p>
                        <label><?php echo TEXT_DESCRIPTION;?></label>
                        <p><?php  echo draw_textarea_field('description', '60', '10', $result['description'], 'id="comment"'); ?></p>

                        <div class="r2">
                            <p id="t_comment" class="error">&nbsp;</p>
                            <p><?php echo draw_input_field('send', TEXT_EDIT, '', 'submit', false);?></p>
                        </div>
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
                        <h2><?php echo TITLE;?>: <?php echo $Avatar->getName($aID);?></h2>

                        <div id="left" class="menu">
                            <?php
                            echo $Cats->listCategories(0, 1, $linkpath, false, $aID);
                            ?>
                        </div>
                        <div id="right">
                            <h3><?php $catname = $Cats->__get($catID);
                                echo $catname['name'];?></h3>
                            <?php
                            $db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id = :catid',array(':catid'=>$catID));
                            if ($db->rowCount() > 0) {
                                ?>
                                <form id="form" method="post" action="inquiry.php?action=evaluate&amp;cID=<?php echo $catID; ?>&amp;aID=<?php echo $_GET['aID']; ?>">
                                    <table cellpadding="0" cellspacing="0" id="evaluation">
                                        <?php
                                        $db->query('SELECT * FROM ' . table_survey . ' WHERE id="' . $aID . '" AND userid ="' . $User->__get('id') . '" LIMIT 1');
                                        $fieldinputs = $db->fetch();
                                        $db->query('SELECT * FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                                        $n = 0;
                                        while ($row = $db->fetch()) {
                                            ?>
                                            <tr>
                                                <td class="inq_label">
                                                    <?php echo $row['name'];

                                                    if (isset($row['info']) && strlen($row['info'])) {
                                                        ?>
                                                        <a class="tooltip" href="#" title="<?php echo $row['info']; ?>">[?]</a>
                                                    <?php
                                                    }
                                                    ?>
                                                </td>

                                                <td class="inq_data">
                                                    <?php
                                                    switch ($row['type']) {
                                                        case 2:

                                                            $jsfieldname = 'field_' . $row['id'];

                                                            if (isset($fieldinputs['field_' . $row['id']])) {
                                                                $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                                $jsfieldname = 'feld_' . $row['id'];
                                                                echo draw_textarea_field($row['id'], '45', '5', stripslashes($data['value']), ' id="' . $jsfieldname . '"');
                                                            } else {
                                                                echo draw_textarea_field($row['id'], '45', '5', '', 'id="' . $jsfieldname . '"');
                                                            }
                                                            ?>
                                                            <p id="t_<?php echo $jsfieldname?>" class="error">&nbsp;</p>
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
                                                                                echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', 'true', 'id="' . $value['text'] . '"');
                                                                            } else {
                                                                                echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', '', 'id="' . $value['text'] . '"');
                                                                            }
                                                                        }

                                                                    }
                                                                } else {
                                                                    foreach ($params as $value) {
                                                                        if ($value['id'] > 0) {
                                                                            echo '<label for="' . $value['text'] . '">' . $value['text'] . '</label>';
                                                                            echo draw_checkbox_field($row['id'] . '_' . $value['id'], '', '', 'id="' . $value['text'] . '""');
                                                                        }
                                                                    }
                                                                }
                                                            }
                                                            if ($row['notes']) {
                                                                $jsfieldname = 'note_' . $row['id'];
                                                                ?>
                                                                <span class="notes_field"><?php echo LABEL_NOTES;?></span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '"');
                                                                ?>
                                                                <p id="t_<?php echo $jsfieldname; ?>" class="error">&nbsp;</p>
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
                                                                <span class="notes_field"><?php echo LABEL_NOTES;?></span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '"');
                                                                ?>
                                                                <p id="t_<?php echo $jsfieldname; ?>" class="error">&nbsp;</p>
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
                                                                <span class="notes_field"><?php echo LABEL_NOTES;?></span>
                                                                <?php
                                                                echo draw_textarea_field('note_' . $row['id'], '45', '3', stripslashes($notes), 'id="' . $jsfieldname . '"');
                                                                ?>
                                                                <p id="t_<?php echo $jsfieldname; ?>" class="error">&nbsp;</p>
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
                                    <div>
                                        <p><?php echo draw_input_field('send', TEXT_SAVE, '', 'submit', false); ?></p>
                                    </div>
                                </form>
                            <?php
                            } else {
                                ?>
                                <p><?php echo TEXT_CATEGORY_EMPTY;?></p>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="c">&nbsp;</div>
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
                        <h2><?php echo TITLE_OVIERVEW;?>: <?php echo $Avatar->getName($aID);?></h2>

                        <div id="left" class="menu">
                            <?php
                            echo $Cats->listCategories(0, 1, $linkpath, false, $aID);
                            ?>
                        </div>
                        <div id="right">
                            <h3><?php $catname = $Cats->__get($catID);
                                echo $catname['name'];?></h3>
                            <?php
                            $db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                            if ($db->rowCount() > 0) {
                                ?>
                                <table cellpadding="0" cellspacing="0" id="evaluation">
                                    <?php
                                    $db->query('SELECT * FROM ' . table_survey . ' WHERE id="' . $aID . '" LIMIT 1');
                                    $fieldinputs = $db->fetch();
                                    $db->query('SELECT * FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                                    $n = 0;
                                    while ($row = $db->fetch()) {
                                        ?>
                                        <tr>
                                            <td class="inq_label">
                                                <?php echo $row['name'];

                                                if (isset($row['info']) && strlen($row['info'])) {
                                                    ?>
					                                <a class="tooltip" href="#" title="<?php echo $row['info']; ?>">[?]</a>
                                                <?php
                                                }
                                                ?>
                                            </td>

                                            <td>
                                                <?php
                                                switch ($row['type']) {
                                                    case 2:
                                                        $data = array();

                                                        if (isset($fieldinputs['field_' . $row['id']])) {
                                                            $data = unserialize($fieldinputs['field_' . $row['id']]);
                                                        } else {
                                                            $data['value'] = '<span class="error">'.TEXT_NO_CONTENT.'</span>';
                                                        }
                                                        ?>
                                                        <div class="notes"><?php echo stripslashes(nl2br($data['value']));?></div>
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
                                                                <span class="notes_field"><?php echo TEXT_NOTES;?></span>
                                                                <div class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
                                                            <?php
                                                            }
                                                        } else {
                                                            echo '<span>'.TEXT_NO_SELECTION.'</span>';
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
                                                                echo '<span class="error">'.TEXT_NO_CONTENT.'</span>';
                                                            }
                                                        } else {
                                                            echo drawSlider($row['id'], 0, '', '', 1);
                                                        }
                                                        if ($row['notes'] && strlen($notes)) {
                                                            ?>
                                                            <span class="notes_field"><?php echo TEXT_NOTES;?></span>
                                                            <div class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
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
                                                            <span class="notes_field"><?php echo TEXT_NOTES;?></span>
                                                            <div class="notes"><?php echo nl2br(stripslashes($notes)); ?></div>
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
                                    <div class="r2">
                                        <p>
                                            <a class="btn" href="inquiry.php?position=evaluate&amp;aID=<?php echo $aID; ?>&amp;cID=<?php echo $catID; ?>"><?php echo TEXT_EDIT;?></a></p>
                                    </div>
                                <?php
                                }
                                ?>
                            <?php
                            } else {
                                ?>
                                <p><?php echo TEXT_CATEGORY_EMPTY;?></p>
                            <?php
                            }
                            ?>
                        </div>
                        <div class="c">&nbsp;</div>
                    </div>
                    <?php
                    break;
            }
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>