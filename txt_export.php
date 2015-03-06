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

$db->query('SELECT id, name, params, type, notes, cat_id  FROM ' . table_fields);

$fields = array();
while ($row = $db->fetch()) {
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

while ($row = $db->fetch()) {
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
    $filename = 'single-' . time() . '.txt';

    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id WHERE s.id =' . $avatar_id . ' LIMIT 1');

} else {
    $filename = 'all-' . time() . '.txt';
    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id');

}
$csv_dump = fopen('exports/' . $filename, 'w');

$LINE_SEPARATOR = "-----------------------------------------\n";

$first_data = "Avatar Online Kategorisierung\n";

$first_data .= "Export am:\t" . date('D, d M Y H:i:s') . "\nEinträge:\t" . $entries . "\nFelder:\t" . count($fields) . "\n" . $LINE_SEPARATOR;

fwrite($csv_dump, $first_data);

while ($row = $db->fetch()) {

    $line = "\n" . $LINE_SEPARATOR;

    $line .= "Ersteller:\t" . $row['firstname'] . ' ' . $row['lastname'] . "\n";
    $line .= "Survey ID:\t" . $row['id'] . "\n";
    $line .= "Titel:\t" . strip_tags($row['title']) . "\n";
    $line .= $LINE_SEPARATOR;
    $cnt_fields = 0;
    foreach ($fields as $fieldvar) {

        $cnt_fields++;

        $line .= "Feld Nr.:\t" . $cnt_fields . "\n";

        $line .= "Feld ID:\t" . $fieldvar['id'] . "\n";

        $field = $fields[$fieldvar['id']];

        $fieldtype = $static_field_types[$field['type']];
        if (!is_array($row['field_' . $fieldvar['id']])) {
            $fieldinputs = unserialize($row['field_' . $fieldvar['id']]);
        } else {
            $fieldinputs['value'] = $row['field_' . $fieldvar['id']];
        }

        $line .= "Kategorie:\t" . $categories[$fieldvar['cat_id']]['name'] . "\n";

        $line .= "Feldname:\t" . $field['name'] . "\n";

        $line .= "Feldtyp:\t" . $fieldtype['text'] . "\n";

        $text = "";

        switch ($field['type']) {
            case 0:
                $text = $fieldinputs['value'];
                break;

            case 1:
                $cb_fields = " ";

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

        $line .= "Feldwert:\t" . trim(stripslashes($text)) . "\n";


        if (strlen($fieldinputs['notes'])) {
            $line .= "\n";
            $line .= "Notizen:\t" . stripslashes($fieldinputs['notes']) . "\n";
        }
        $line .= $LINE_SEPARATOR;
    }

    fwrite($csv_dump, $line . "\n");

}
fclose($csv_dump);

header("HTTP/1.1 200 OK");
header("Content-Type: application/octetstream; charset=UTF-8");
header("Content-Disposition: attachment;filename=" . $filename);

header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0,pre-check=0');
header('Pragma: public');

readfile('exports/' . $filename);