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

if (isset($_GET['aID'])) {
    $avatar_id = htmlspecialchars($_GET['aID']);
    $filename = 'single-' . time() . '.csv';
    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id WHERE s.id =' . $avatar_id . ' LIMIT 1');

} else {
    $filename = 'all-' . time() . '.csv';
    $db->query('SELECT s.*, u.firstname, u.lastname FROM ' . table_survey . ' s LEFT JOIN ' . table_users . ' u ON s.userid = u.id');


}
$csv_dump = fopen('exports/' . $filename, 'w');

$CSV_DELIM = ';';
$first_line = 'Exportdatum' . $CSV_DELIM .
    'Ersteller' . $CSV_DELIM .
    'Avatar ID' . $CSV_DELIM .
    'Avatar Titel' . $CSV_DELIM;
$fieldnum = 1;
foreach ($fields as $fieldvar) {
    $first_line .= 'Feld-' . $fieldnum . ' ID' . $CSV_DELIM .
        'Feld-' . $fieldnum . ' Name' . $CSV_DELIM .
        'Kategorie' . $CSV_DELIM .
        'Feld-' . $fieldnum . ' Typ' . $CSV_DELIM .
        'Feld-' . $fieldnum . ' Wert' . $CSV_DELIM .
        'Feld-' . $fieldnum . ' Notizen' . $CSV_DELIM;
    $fieldnum++;
}


fwrite($csv_dump, $first_line . "\n");

$line = date('D, d M Y H:i:s') . $CSV_DELIM;

while ($row = $db->fetch()) {

    $line = "";
    $line = date('D, d M Y H:i:s') . $CSV_DELIM . $row['firstname'] . ' ' . $row['lastname'] . $CSV_DELIM;
    $line .= $row['id'] . $CSV_DELIM . strip_tags($row['title']) . $CSV_DELIM;

    foreach ($fields as $fieldvar) {

        $line .= $fieldvar['id'] . $CSV_DELIM;

        $line .= $categories[$fieldvar['cat_id']]['name'] . $CSV_DELIM;

        $field = $fields[$fieldvar['id']];

        $fieldtype = $static_field_types[$field['type']];
        if (!is_array($row['field_' . $fieldvar['id']])) {
            $fieldinputs = unserialize($row['field_' . $fieldvar['id']]);
        } else {
            $fieldinputs['value'] = $row['field_' . $fieldvar['id']];
        }


        $line .= $field['name'] . $CSV_DELIM;

        $line .= $fieldtype['text'] . $CSV_DELIM;

        $text = " ";

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
        $text = str_replace(array("\n", "\r", ";"), "", $text);
        $line .= stripslashes($text) . $CSV_DELIM;

        $notes = str_replace(array("\n", "\r", ";"), "", $fieldinputs['notes']);
        $line .= stripslashes($notes) . $CSV_DELIM;
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