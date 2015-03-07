<?php
require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

$Cats = new Categories($db);

$linkpath['position'] = 'edit';
$linkpath['filename'] = 'survey.php';

if (isset($_GET['action'])) {
    switch ($_GET['action']) {

        case 'add_field':
            $postbit = null;
            foreach ($_POST as $postbits => $element) {
                $postbit[$postbits] = db_prepare_input($element);
            }
            $params = '';

            if ($postbit['field_type'] == 0) {
                $param_multi = false;
                $minVal = '';
                $maxVal = '';

                if (isset($postbit['minVal'])) $minVal = $postbit['minVal'];
                if (isset($postbit['maxVal'])) $maxVal = $postbit['maxVal'];

                $add_params = array('minVal' => $minVal,
                    'maxVal' => $maxVal);
                $params = serialize($add_params);

            } elseif ($postbit['field_type'] == 1) {
                $add_params = array();
                for ($i = 0; $i <= 7; $i++) {
                    if (isset($postbit['cb_value' . $i]) && strlen($postbit['cb_value' . $i]) > 0) {
                        $add_params[$i]['id'] = $i;
                        $add_params[$i]['text'] = $postbit['cb_value' . $i];
                    }
                }
                $params = serialize($add_params);

            } elseif ($postbit['field_type'] == 3) {
                $add_params = array();
                for ($i = 0; $i <= 7; $i++) {
                    if (isset($postbit['dd_value' . $i]) && strlen($postbit['dd_value' . $i]) > 0) {
                        $add_params[$i]['id'] = $i;
                        $add_params[$i]['text'] = $postbit['dd_value' . $i];
                    }
                }
                $params = serialize($add_params);
            }
            $data = array(':name' => $postbit['field_name'], ':catid' => $postbit['cat_id']);
            $db->query('SELECT id FROM ' . table_fields . ' WHERE name = :name AND cat_id = :catid LIMIT 1', $data);

            if ($db->rowCount() > 0) {
                $messageStack->add_session('general', sprintf(MSG_E_FIELD_EXIST, $postbit['field_name']), 'error');

            } elseif (!isset($postbit['field_name']) || (strlen($postbit['field_name']) < FIELD_MIN_LENGTH)) {
                $messageStack->add_session('general', sprintf(MSG_E_FIELD_LENGTH, FIELD_MIN_LENGTH), 'error');
                header('Location:survey.php?position=add_field&cID=' . $postbit['cat_id']);
            } else {
                $notes = 0;
                if (isset($_POST['notes'])) {
                    $notes = 1;
                }
                $data = array(
                    ':name'     => $postbit['field_name'],
                    ':catid'    => $postbit['cat_id'],
                    ':notes'    => $notes,
                    ':type'     => $postbit['field_type'],
                    ':sort'     => $postbit['sort_order'],
                    ':params'   => $params,
                    ':info'     => $postbit['info']
                );
                $db->query('INSERT INTO ' . table_fields . ' (notes, name, type, cat_id, sort_order, params, info)
                                    VALUES (:notes,:name,:type,:catid,:sort,:params,:info )');
                $db->query('UPDATE ' . table_categories . ' SET empty = 0 WHERE id="' . $postbit['cat_id'] . '"');
                $db->query('SELECT id FROM ' . table_fields . ' ORDER BY id DESC LIMIT 1');
                $newID = $db->fetch();
                $db->query('ALTER TABLE ' . table_survey . ' add field_' . $newID['id'] . ' varchar(' . TEXTAREA_MAX_LENGTH * 2 . ')');

                $messageStack->add_session('general', sprintf(MSG_FIELD_ADDED, $postbit['field_name']), 'success');
                header('Location:survey.php?position=edit&cID=' . $postbit['cat_id']);
            }

            break;

        case 'add_category':
            $postbit = null;
            foreach ($_POST as $postbits => $element) {
                $postbit[$postbits] = db_prepare_input($element);
            }
            $db->query('SELECT id FROM ' . table_categories . ' WHERE name = "' . $postbit['cat_name'] . '"');
            if (strlen($postbit['cat_name']) < CAT_MIN_LENGTH || $db->rowCount() > 0) {
                $messageStack->add_session('general', sprintf(MSG_E_CATEGORY_ADD, $postbit['cat_name']), 'error');
                header('Location:survey.php?position=add_category');
            } else {
                $data = array(
                    ':name'     => $postbit['cat_name'],
                    ':parent'   => $postbit['parent'],
                    ':sort'     => $postbit['sort_order']
                );
                $db->query('INSERT INTO ' . table_categories . ' (name, parent, sort_order)
                                VALUES (:name,:parent,:sort)');
                $db->query("SELECT id FROM " . table_categories . " ORDER BY id DESC LIMIT 1");
                $newID = $db->fetch();
                $messageStack->add_session('general', sprintf(MSG_CATEGORY_ADDED,$postbit['cat_name']), 'success');
                header('Location:survey.php?position=edit&cID=' . $newID['id']);
            }

            break;

        case 'edit':
            if ((!isset($_GET['cID']) || !is_numeric($_GET['cID'])) &&
                (!isset($_GET['fID']) || !is_numeric($_GET['fID']))
            ) {
                header('Location:survey.php');
            } elseif (isset($_GET['cID']) && is_numeric($_GET['cID'])) {

                $id = $_GET['cID'];
                $postbit = null;
                foreach ($_POST as $postbits => $element) {

                    $postbit[$postbits] = db_prepare_input($element);

                }
                $data = array(':id'=>$id,':name'=>$postbit['cat_name']);
                $db->query('SELECT id FROM ' . table_categories . ' WHERE name = :name AND id=:id',$data);
                if ($db->rowCount() > 0) {
                    $messageStack->add_session('general', sprintf(MSG_E_CATEGORY_EXIST, $postbit['cat_name']), 'error');
                } elseif (strlen($postbit['cat_name']) < CAT_MIN_LENGTH) {
                    $messageStack->add_session('general', sprintf(MSG_E_CATEGORY_EXIST, CAT_MIN_LENGTH), 'error');
                } else {
                    $data = array(
                            ':name'=>$postbit['cat_name'],
                            ':sort'=>$postbit['sort_order'],
                            ':parent'=>$postbit['parent'],
                            ':id'=>$id
                            );
                    $db->query('UPDATE ' . table_categories . ' SET name =:name, parent=:parent, sort_order =:sort WHERE id=:id LIMIT 1',$data);
                    $messageStack->add_session('general', MSG_UPDATE_SUCCESS, 'success');
                }
                header('Location:survey.php?position=edit&cID=' . $id);

            } elseif (isset($_GET['fID']) && is_numeric($_GET['fID'])) {
                $id = $_GET['fID'];
                $db->query('SELECT type FROM ' . table_fields . ' WHERE id=:id LIMIT 1',array(':id'=>$id));
                $oldfield = $db->fetch();
                $postbit = null;
                foreach ($_POST as $postbits => $element) {
                    $postbit[$postbits] = db_prepare_input($element);
                }

                if ($oldfield['type'] != $postbit['field_type']) {
                    $db->query('UPDATE ' . table_survey . ' SET field_' . $id . ' = NULL');
                }
                $params = '';

                if ($postbit['field_type'] == 0) {
                    $param_multi = false;
                    $minVal = '';
                    $maxVal = '';

                    if (isset($postbit['minVal'])) $minVal = $postbit['minVal'];
                    if (isset($postbit['maxVal'])) $maxVal = $postbit['maxVal'];

                    $add_params = array('minVal' => $minVal,
                        'maxVal' => $maxVal);
                    $params = serialize($add_params);

                } elseif ($postbit['field_type'] == 1) {
                    $add_params = array();
                    for ($i = 0; $i <= 7; $i++) {
                        if (isset($postbit['cb_value' . $i]) && strlen($postbit['cb_value' . $i]) > 0) {
                            $add_params[$i]['id'] = $i;
                            $add_params[$i]['text'] = $postbit['cb_value' . $i];
                        }
                    }
                    $params = serialize($add_params);

                } elseif ($postbit['field_type'] == 3) {
                    $add_params = array();
                    for ($i = 0; $i <= 7; $i++) {
                        if (isset($postbit['dd_value' . $i]) && strlen($postbit['dd_value' . $i]) > 0) {
                            $add_params[$i]['id'] = $i;
                            $add_params[$i]['text'] = $postbit['dd_value' . $i];
                        }
                    }
                    $params = serialize($add_params);
                }
                if (strlen($postbit['field_name']) < FIELD_MIN_LENGTH) {
                    $messageStack->add_session('general', sprintf(MSG_E_FIELD_LENGTH,FIELD_MIN_LENGTH), 'error');
                } else {
                    $notes = 0;
                    if (isset($_POST['notes'])) $notes = 1;
                    $data = array(
                        ':name'     => $postbit['field_name'],
                        ':catid'    => $postbit['cat_id'],
                        ':notes'    => $notes,
                        ':type'     => $postbit['field_type'],
                        ':sort'     => $postbit['sort_order'],
                        ':params'   => $params,
                        ':info'     => $postbit['info'],
                        ':id'       => $id
                    );
                    $db->query('UPDATE ' . table_fields . '
                                    SET notes= :notes, name =:name, type=:type, cat_id =:catid, sort_order=:sort, params=:params, info=:info WHERE id =:id LIMIT 1',$data);
                    $db->query('UPDATE ' . table_categories . ' SET empty = 0 WHERE id =:id LIMIT 1',array(':id'=> $postbit['cat_id']));
                    $messageStack->add_session('general', sprintf(MSG_FIELD_UPDATED, $postbit['field_name']), 'success');
                    header('Location:survey.php?position=edit&fID=' . $id);
                }
            }
            break;
        case 'delete':
            if ((!isset($_GET['cID']) || !is_numeric($_GET['cID'])) &&
                (!isset($_GET['fID']) || !is_numeric($_GET['fID']))
            ) {
            } elseif (isset($_GET['cID']) && is_numeric($_GET['cID'])) {
                $id = $_GET['cID'];
                $CatsToDelete = $id . $Cats->getChildCats($id, 0);
                $db->query('DELETE FROM ' . table_categories . ' WHERE id IN(' . $CatsToDelete . ')');
                $db->query('SELECT id FROM ' . table_fields . ' WHERE cat_id IN(' . $CatsToDelete . ') ');

                $fieldsToDelete = "";
                $idsToDelete = "";

                while ($row = $db->fetch()) {
                    $fieldsToDelete .= ', DROP field_' . $row['id'];
                    $idsToDelete .= ',' . $row['id'];
                }
                $delFields = substr($fieldsToDelete, 1, strlen($fieldsToDelete));
                $delIds = substr($idsToDelete, 1, strlen($idsToDelete));

                if (strlen(trim($delFields))) {
                    $db->query('ALTER TABLE ' . table_survey . $delFields);
                }
                if (strlen(trim($delIds))) {
                    $db->query('DELETE FROM ' . table_fields . ' WHERE id IN(' . $delIds . ')');
                }
                $messageStack->add_session('general', MSG_DATA_DELETED, 'success');

            } elseif (isset($_GET['fID']) && is_numeric($_GET['fID'])) {

                $id = $_GET['fID'];
                $db->query('SELECT cat_id FROM ' . table_fields . ' WHERE id= "' . $id . '" LIMIT 1');
                $category = $db->fetch();
                $db->query('ALTER TABLE ' . table_survey . ' DROP field_' . $id);
                $db->query('DELETE FROM ' . table_fields . ' WHERE id ="' . $id . '" LIMIT 1');
                $messageStack->add_session('general', MSG_DATA_DELETED, 'success');
                header('Location:survey.php?position=edit&cID=' . $category['cat_id']);

            }
            break;
    }
}
if (isset($_GET['position']) && $_GET['position'] === 'add_field') {

    if (!isset($_GET['cID']) || !is_numeric($_GET['cID']))
        header('Location:survey.php');

}
if (isset($_GET['position']) && $_GET['position'] === 'edit') {
    if ((!isset($_GET['fID']) || !is_numeric($_GET['fID'])) &&
        (!isset($_GET['cID']) || !is_numeric($_GET['cID']))
    ) {
        header('Location:survey.php');
    }
}
?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="de" lang="de" dir="ltr">

    <head>
        <meta http-equiv="Content-Script-Type" content="text/javascript"/>
        <meta http-equiv="Content-Style-Type" content="text/css"/>
        <meta http-equiv="content-language" content="de"/>

        <title><?php echo TITLE; ?> | <?php echo WORKSPACE_TITLE; ?></title>

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

                case 'add_category':
                    ?>
                    <h2><?php echo TEXT_CATEGORY_ADD;?></h2>

                    <form id="form" action="survey.php?action=add_category" method="post">

                        <label for="cat_name"><?php echo LABEL_CATEGORY;?></label>

                        <p>
                            <?php
                            echo draw_input_field('cat_name');
                            ?>
                        </p>

                        <label for="parent"><?php echo LABEL_PARENT;?></label>

                        <p>
                            <select name="parent">
                                <option value="0">&nbsp;</option>
                                <?php
                                echo $Cats->drawCatOptions(0, 0, 0);
                                ?>
                            </select>
                        </p>
                        <label for="sort_order"><?php echo LABEL_SORT;?></label>

                        <p>
                            <?php
                            $counter = 0;
                            $sortVals = array();
                            for ($i = -10; $i <= 10; $i++) {
                                $sortVals[$counter]['id'] = $i;
                                $sortVals[$counter]['text'] = $i;
                                $counter++;
                            }
                            echo draw_pulldown_menu('sort_order', $sortVals, 0);
                            ?>
                        </p>

                        <div class="r2">
                            <p><?php echo draw_input_field('send', TEXT_CATEGORY_ADD, '', 'submit', false); ?></p>
                        </div>
                    </form>
                    <?php
                    break;
                case 'add_field':
                    $catname = $Cats->__get($_GET['cID']);
                    ?>
                    <h2><?php echo sprintf(TITLE_ADD_FIELD_TO_CATEGORY,$catname['name']);?></h2>

                    <form id="form" action="survey.php?action=add_field" method="post">

                        <label for="field_name"><?php echo LABEL_FIELD_NAME;?></label>

                        <p><?php echo draw_input_field('field_name', '', 'id="news-title"'); ?></p>

                        <label for="field_name"><?php echo LABEL_DESCRIPTION;?></label>

                        <p><?php echo draw_textarea_field('info', 80, 3, '', 'id="comment"'); ?></p>

                        <label for="cat_id"><?php echo LABEL_CATEGORY;?></label>

                        <p>
                            <select name="cat_id">
                                <option value="0">&nbsp;</option>
                                <?php
                                echo $Cats->drawCatOptions(0, $_GET['cID'], 0);
                                ?>
                            </select>
                        </p>
                        <label for="cat_id"><?php echo LABEL_FIELD_NAME;?></label>

                        <p>
                            <?php
                            echo draw_pulldown_menu('field_type', getFields(), 0, 'onchange="showhide(this.form,0);"');
                            ?>
                        </p>

                        <label for="sort_order"><?php echo LABEL_SORT;?></label>

                        <p>
                            <?php
                            $counter = 0;
                            $sortVals = array();
                            for ($i = -10; $i <= 10; $i++) {
                                $sortVals[$counter]['id'] = $i;
                                $sortVals[$counter]['text'] = $i;
                                $counter++;
                            }
                            echo draw_pulldown_menu('sort_order', $sortVals, 0);
                            ?>
                        </p>

                        <div style="height:300px;">
                            <div id="div-polar">
                                <label><?php echo LABEL_SLIDER;?></label>

                                <p>
                                    <?php
                                    echo draw_input_field('minVal');
                                    echo ' &lsaquo; &mdash; &rsaquo; ';
                                    echo draw_input_field('maxVal');
                                    ?>
                                </p>
                            </div>
                            <div id="div-dropdown" style="display:none;">
                                <?php
                                echo draw_input_field('dd_value0', '&nbsp;', '', 'hidden');
                                for ($i = 1; $i <= 7; $i++) {
                                    ?>
                                    <label for="dd_value_<?php echo $i; ?>"><?php echo LABEL_DD_VAL;?> <?php echo $i; ?>:</label>
                                    <p><?php echo draw_input_field('dd_value' . $i) . '<br />'; ?></p>
                                <?php
                                }
                                ?>
                            </div>
                            <div id="div-checkboxes" style="display:none;">
                                <?php
                                echo draw_input_field('cb_value0', '&nbsp;', '', 'hidden');
                                for ($i = 1; $i <= 7; $i++) {
                                    ?>
                                    <label for="cb_value_<?php echo $i; ?>"><?php echo LABEL_SELECT;?> <?php echo $i; ?>:</label>
                                    <p><?php echo draw_input_field('cb_value' . $i) . '<br />'; ?></p>
                                <?php
                                }
                                ?>
                            </div>
                        </div>

                        <label for="notes" class="chklbl"><?php echo LABEL_NOTES;?></label>

                        <p><?php echo draw_checkbox_field('notes'); ?></p>

                        <div class="r2">
                            <p><?php echo draw_input_field('send', TEXT_FIELD_ADD, '', 'submit', false); ?></p>
                        </div>
                    </form>
                    <?php
                    break;

                case 'edit':
                    if (isset($_GET['cID']) && is_numeric($_GET['cID'])) {
                        $catID = $_GET['cID'];
                        $fields = $Cats->__get($catID);
                        ?>
                        <h2>Kategorie bearbeiten</h2>

                        <p id="subtitle">
                            <a href="survey.php?position=add_field&amp;cID=<?php echo $catID; ?>">Feld hinzufügen</a>
                            |
                            <a href="survey.php?position=add_category">Kategorie hinzufügen</a>
                            |
                            <a href="survey.php?position=confirm_delete&amp;cID=<?php echo $catID; ?>">diese Kategorie
                                löschen</a>
                        </p>

                        <form id="form" action="survey.php?action=edit&amp;cID=<?php echo $catID; ?>" method="post">

                            <label for="cat_name">Name der Kategorie</label>

                            <p>
                                <?php
                                echo draw_input_field('cat_name', $fields['name'], 'id="news-title"');
                                ?>
                            </p>
                            <label for="parent">Parent</label>

                            <p>
                                <select name="parent">
                                    <option value="0">&nbsp;</option>
                                    <?php
                                    echo $Cats->drawCatOptions(0, $fields['parent'], 0);
                                    ?>
                                </select>
                            </p>
                            <label for="sort_order">Reihenfolge</label>

                            <p>
                                <?php
                                $counter = 0;
                                $sortVals = array();
                                for ($i = -10; $i <= 10; $i++) {
                                    $sortVals[$counter]['id'] = $i;
                                    $sortVals[$counter]['text'] = $i;
                                    $counter++;
                                }
                                echo draw_pulldown_menu('sort_order', $sortVals, $fields['sort_order']);
                                ?>
                            </p>

                            <div class="r2">
                                <p><?php echo draw_input_field('send', 'Kategorie bearbeiten', '', 'submit', false); ?></p>
                            </div>
                        </form>


                        <h2><?php echo TITLE_OVIERVEW_FIELDS;?></h2>
                        <table cellpadding="0" cellspacing="0" class="overview">
                            <?php
                            $db->query('SELECT * FROM ' . table_fields . ' WHERE cat_id = "' . $catID . '"');
                            $n = 0;
                            while ($row = $db->fetch()) {
                                ?>
                                <tr>
                                    <td>
                                        <?php echo $row['name']; ?>
                                    </td>

                                    <td>
                                        <?php
                                        switch ($row['type']) {
                                            case 2:
                                                echo draw_textarea_field('test', '40', '1');
                                                break;

                                            case 1:
                                                if (isset($row['params'])) {
                                                    $params = unserialize($row['params']);
                                                    foreach ($params as $value) {
                                                        if ($value['id'] > 0 && isset($value['text']) && strlen($value['text'])) {
                                                            echo '<label for="' . $value['id'] . '">' . $value['text'] . '</label>';
                                                            echo draw_checkbox_field($row['id'], $value['id'], '', 'style="text-align:left;vertical-align:middle;"');
                                                            echo ' ';
                                                        }
                                                    }
                                                }
                                                break;

                                            case 0:
                                                if (isset($row['params'])) {
                                                    $params = unserialize($row['params']);
                                                    if (isset($params['minVal']) || isset($params['maxVal'])) {
                                                        echo drawSlider($row['id'], 0, $params['minVal'], $params['maxVal']);
                                                    } else {
                                                        echo drawSlider($row['id'], 0);
                                                    }
                                                } else {
                                                    echo drawSlider($row['id'], 0);
                                                }
                                                break;

                                            case 3:
                                                if (isset($row['params'])) {
                                                    $params = unserialize($row['params']);
                                                    echo draw_pulldown_menu($row['id'], $params);
                                                }
                                                break;
                                        }
                                        ?>
                                    </td>

                                    <td>
                                        <a href="survey.php?position=edit&amp;fID=<?php echo $row['id']; ?>"><?php echo TEXT_EDIT;?></a>
                                        |
                                        <a href="survey.php?position=confirm_delete&amp;fID=<?php echo $row['id']; ?>"><?php echo TEXT_DELETE;?></a>

                                    </td>
                                </tr>
                                <?php
                                $n++;
                            }
                            ?>
                        </table>
                    <?php
                    } elseif (isset($_GET['fID']) && is_numeric($_GET['fID'])) {

                        $Fid = $_GET['fID'];
                        $db->query('SELECT * FROM ' . table_fields . ' WHERE id = "' . $Fid . '" LIMIT 1');
                        $Fvals = $db->fetch();
                        ?>
                        <h2><?php echo TITLE_FIELD_EDIT;?></h2>

                        <form id="form" action="survey.php?action=edit&amp;fID=<?php echo $Fid; ?>" method="post">
                            <label for="field_name"><?php echo LABEL_FIELD_NAME;?></label>

                            <p>
                                <?php
                                echo draw_input_field('field_name', $Fvals['name'], 'id="news-title"');
                                ?>
                            </p>
                            <label for="info"><?php echo LABEL_DESCRIPTION;?></label>

                            <p><?php echo draw_textarea_field('info', 80, 3, $Fvals['info']); ?></p>

                            <label for="cat_id"><?php echo LABEL_CATEGORY;?></label>

                            <p>
                                <select name="cat_id">
                                    <option value="0">&nbsp;</option>
                                    <?php
                                    echo $Cats->drawCatOptions(0, $Fvals['cat_id'], 0);
                                    ?>
                                </select>
                            </p>
                            <label for="field_type"><?php echo LABEL_FIELD_TYPE;?></label>

                            <p><?php echo draw_pulldown_menu('field_type', getFields(), $Fvals['type'], 'onchange="showhide(this.form,1);"'); ?></p>

                            <label for="sort_order"><?php echo LABEL_SORT;?></label>

                            <p>
                                <?php
                                $counter = 0;
                                $sortVals = array();
                                for ($i = -10; $i <= 10; $i++) {
                                    $sortVals[$counter]['id'] = $i;
                                    $sortVals[$counter]['text'] = $i;
                                    $counter++;
                                }
                                echo draw_pulldown_menu('sort_order', $sortVals, $Fvals['sort_order']);
                                ?>
                            </p>

                            <div style="height:300px;">
                                <?php
                                $showpol = "";
                                $showdd = "";
                                $showcb = "";
                                $edit_params = "";
                                if ($Fvals['type'] != 0) {
                                    $showpol = 'style="display:none;"';
                                }
                                if ($Fvals['type'] != 3) {
                                    $showdd = 'style="display:none;"';
                                }
                                if ($Fvals['type'] != 1) {
                                    $showcb = 'style="display:none;"';
                                }
                                if ($Fvals['type'] == 0 || $Fvals['type'] == 1 || $Fvals['type'] == 3 && isset($Fvals['params'])) {
                                    $edit_params = unserialize($Fvals['params']);
                                }
                                ?>
                                <div id="div-polar" <?php echo $showpol; ?>>
                                    <label><?php echo LABEL_SLIDER;?></label>
                                    <?php

                                    if (isset($Fvals['params']) && (isset($edit_params['minVal']) || isset($edit_params['maxVal']))) {
                                        echo draw_input_field('minVal', $edit_params['minVal']);
                                        echo ' &lsaquo; &mdash; &rsaquo; ';
                                        echo draw_input_field('maxVal', $edit_params['maxVal']);
                                    } else {
                                        echo draw_input_field('minVal');
                                        echo ' &lsaquo; &mdash; &rsaquo; ';
                                        echo draw_input_field('maxVal');
                                    }
                                    ?>
                                </div>
                                <div id="div-dropdown" <?php echo $showdd; ?>>
                                    <?php
                                    echo draw_input_field('dd_value0', '&nbsp;', '', 'hidden');
                                    for ($i = 1; $i <= 7; $i++) {
                                        ?>
                                        <label for="dd_value_<?php echo $i; ?>"><?php echo LABEL_DD_VAL;?> <?php echo $i; ?>:</label>
                                        <p>
                                            <?php
                                            if (isset($edit_params[$i]['text'])) {
                                                echo draw_input_field('dd_value' . $i, $edit_params[$i]['text']) . '<br />';
                                            } else {
                                                echo draw_input_field('dd_value' . $i) . '<br />';
                                            }
                                            ?>
                                        </p>
                                    <?php
                                    }
                                    ?>
                                </div>
                                <div id="div-checkboxes" <?php echo $showcb; ?>>
                                    <?php
                                    echo draw_input_field('cb_value0', '&nbsp;', '', 'hidden');
                                    for ($i = 1; $i <= 7; $i++) {
                                        ?>
                                        <label for="cb_value_<?php echo $i; ?>"><?php echo LABEL_SELECT;?>  <?php echo $i; ?>:</label>
                                        <p>
                                            <?php

                                            if (isset($edit_params[$i]['text'])) {
                                                echo draw_input_field('cb_value' . $i, $edit_params[$i]['text']) . '<br />';
                                            } else {
                                                echo draw_input_field('cb_value' . $i) . '<br />';
                                            }
                                            ?>
                                        </p>
                                    <?php
                                    }
                                    ?>
                                </div>
                            </div>

                            <label for="notes" class="chklbl"><?php echo LABEL_NOTES;?></label>

                            <p><?php echo draw_checkbox_field('notes', '', $Fvals['notes']); ?></p>


                            <div class="r2">
                                <p><?php echo draw_input_field('send', TEXT_SAVE, '', 'submit', false); ?></p>
                            </div>

                        </form>
                    <?php
                    }
                    break;
                case 'confirm_delete':
                    if ((!isset($_GET['cID']) || !is_numeric($_GET['cID'])) &&
                        (!isset($_GET['fID']) || !is_numeric($_GET['fID']))
                    ) {
                    } elseif (isset($_GET['cID']) && is_numeric($_GET['cID'])) {
                        $id = $_GET['cID'];
                        $catname = $Cats->__get($_GET['cID']);
                        ?>
                        <h2><?php echo TITLE_CAT_DELETE;?></h2>
                        <form id="form" method="post" action="survey.php?action=delete&amp;cID=<? echo $id; ?>">

                            <p><?php echo sprintf(TEXT_DELETE_CONFIRM,$catname['name']);?></p>

                            <p>
                                <a class="btn cancel" href="javascript:history.back();"><?php echo TEXT_CANCEL;?></a>
                                <button name="delete" class="proceed" type="submit"><?php echo TEXT_DELETE;?></button>
                            </p>
                        </form>
                    <?php
                    } elseif (isset($_GET['fID']) && is_numeric($_GET['fID'])) {

                        $id = $_GET['fID'];
                        $db->query('SELECT cat_id,name FROM ' . table_fields . ' WHERE id= "' . $id . '" LIMIT 1');
                        $fieldData = $db->fetch();
                        ?>
                        <h2><?php echo TITLE_FIELD_DELETE;?></h2>
                        <form id="form" method="post" action="survey.php?action=delete&amp;fID=<?echo $id; ?>">

                            <p><?php echo sprintf(TEXT_DELETE_CONFIRM,$fieldData['name']);?></p>

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
            <h2><?php echo TITLE_OVIERVEW;?></h2>

            <p id="subtitle">
                <a href="survey.php?position=add_category"><?php echo TEXT_CATEGORY_ADD;?></a>
            </p>
            <?php
            echo $Cats->listCategories(0, '', $linkpath, true);
        }
        ?>
    </div>

<?php require('inc/footer.php'); ?>