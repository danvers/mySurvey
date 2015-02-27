<?php

/**
 * @author Dan Verständig
 */
error_reporting(E_ERROR);

require_once('inc/header.php');

if (!$SessionManager->logged_in() || !(IN_PAGE)) header("Location:index.php");

$Cats = new Categories($db);

$Avatar = new Avatar($db);

$static_field_types = getFields();

$Avatar->cleanExports('exports/', 2);

$xml = xmlwriter_open_memory();

xmlwriter_set_indent($xml, TRUE);
xmlwriter_start_document($xml, '1.0', 'UTF-8');


$db->query('SELECT id, name, params, type, notes, cat_id  FROM ' . table_fields);

$fields = array();
while ($row = $db->fetchArray()) {
    $fields[$row['id']] = array('id' => $row['id'],
        'params' => $row['params'],
        'type' => $row['type'],
        'notes' => $row['notes'],
        'cat_id' => $row['cat_id'],
        'name' => $row['name']
    );
}

$db->query('SELECT id, name FROM ' . table_categories);

$categories = array();

while ($row = $db->fetchArray()) {

    $categories[$row['id']] = array('id' => $row['id'],
        'name' => $row['name']
    );
}
$field = array();
$fieldinputs = array();
$text = "";

$entries = $Avatar->getEntries();

if (isset($_GET['aID'])) {
    $avatar_id = htmlspecialchars($_GET['aID']);
    $filename = 'single-' . time() . '.xml';
    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id WHERE s.id =' . $avatar_id . ' LIMIT 1');


} else {
    $filename = 'all-' . time() . '.xml';
    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id');


}

xmlwriter_start_element($xml, 'Avatare');
xmlwriter_write_attribute($xml, 'Export', date('D, d M Y H:i:s'));
xmlwriter_write_attribute($xml, 'Avataranzahl', $entries);
xmlwriter_write_attribute($xml, 'Feldanzahl', count($fields));

$cnt = 0;
while ($row = $db->fetchArray()) {
    $cnt++;
    xmlwriter_start_element($xml, 'Avatar');
    xmlwriter_write_attribute($xml, 'num', $cnt);
    xmlwriter_write_attribute($xml, 'id', $row['id']);

    xmlwriter_start_element($xml, 'Ersteller');
    xmlwriter_text($xml, utf8_encode($row['firstname'] . ' ' . $row['lastname']));
    xmlwriter_end_element($xml);
    xmlwriter_start_element($xml, 'Titel');
    xmlwriter_text($xml, utf8_encode(strip_tags($row['title'])));
    xmlwriter_end_element($xml);

    xmlwriter_start_element($xml, 'Felder');
    $cnt_fields = 0;

    foreach ($fields as $fieldvar) {
        $cnt_fields++;
        xmlwriter_start_element($xml, 'Feld');
        xmlwriter_write_attribute($xml, 'num', $cnt_fields);
        xmlwriter_write_attribute($xml, 'id', $fieldvar['id']);
        $field = $fields[$fieldvar['id']];

        $fieldtype = $static_field_types[$field['type']];
        if (!is_array($row['field_' . $fieldvar['id']])) {
            $fieldinputs = unserialize($row['field_' . $fieldvar['id']]);
        } else {
            $fieldinputs['value'] = $row['field_' . $fieldvar['id']];
        }

        xmlwriter_start_element($xml, 'Kategorie');
        xmlwriter_text($xml, utf8_encode($categories[$fieldvar['cat_id']]['name']));
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, 'Feldname');
        xmlwriter_text($xml, utf8_encode($field['name']));
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, 'Feldtyp');
        xmlwriter_text($xml, utf8_encode($fieldtype['text']));
        xmlwriter_end_element($xml);

        $text = "";

        switch ($field['type']) {
            case 0:
                $text = $fieldinputs['value'];
                break;

            case 1:
                $cb_fields = "";

                $params = unserialize($field['params']);

                foreach ($params as $value) {
                    if ($value['id'] > 0 && isset($value['text']) && strlen($value['text'])) {
                        $cb_fields .= $value['text'];
                        $cb_fields .= ', ';
                    }
                }

                $text = substr($cb_fields, 0, $cb_fields - 2);
                break;

            case 2:
                $text = $fieldinputs['value'];
                break;

            case 3:

                if (isset($field['params']) && strlen($fieldinputs['value'])) {
                    $params = unserialize($field['params']);
                    if ($params[$fieldinputs['value']]['id'] > 0) {
                        $text = $params[$fieldinputs['value']]['text'];
                    }
                }
                break;
        }
        xmlwriter_start_element($xml, 'Feldwert');
        xmlwriter_text($xml, utf8_encode(html_entity_decode(stripslashes($text))));
        xmlwriter_end_element($xml);

        xmlwriter_start_element($xml, 'Notizen');
        if (strlen(utf8_encode(html_entity_decode(stripslashes($fieldinputs['notes']))))) {
            xmlwriter_text($xml, utf8_encode(html_entity_decode(stripslashes($fieldinputs['notes']))));
        }
        xmlwriter_end_element($xml);
        xmlwriter_end_element($xml);
    }
    xmlwriter_end_element($xml);
    xmlwriter_end_element($xml);
}

xmlwriter_end_element($xml);

xmlwriter_end_document($xml);

//echo xmlwriter_output_memory($xml);

$file = fopen('exports/' . $filename, "w");

if ($file !== false) {
    fwrite($file, xmlwriter_output_memory($xml));
    fflush($file);
    fclose($file);
}


header("HTTP/1.1 200 OK");
header("Content-Type: application/octetstream; charset=UTF-8");
header("Content-Disposition: attachment;filename=" . $filename);

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0,pre-check=0');
header('Pragma: public');

readfile('exports/' . $filename);