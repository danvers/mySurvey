<?php

$GLOBALS['classes'] = array(
    'database' => 'inc/classes/database.class.php',
    'User' => 'inc/classes/user.class.php',
    'SessionManagement' => 'inc/classes/session.class.php',
    'Categories' => 'inc/classes/categories.class.php',
    'messageStack' => 'inc/classes/mstack.class.php',
    'Avatar' => 'inc/classes/survey.class.php',
    'Message' => 'inc/classes/message.class.php'
);
function get_language($lang = LANGUAGE_CODE){
    $file = 'inc/languages/'.$lang.'/'.basename($_SERVER['SCRIPT_NAME']);

    include('inc/languages/'.$lang.'/general.php');

    include($file);

}
function __autoload($class)
{
    if (isset($GLOBALS['classes'][$class])) {
        require_once($GLOBALS['classes'][$class]);
    }
}

function preventCaching()
{
    header("Expires: Mon, 01 Jan 1997 08:00:00 GMT");
    header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
}

function parseGet($attr)
{
    if (isset($_GET[$attr]) && strlen($attr) > 0) {
        return preg_replace('/[^a-z0-9_]/i', '', $_GET[$attr]);
    } else {
        return false;
    }
}

function check_email($email)
{
    $nonascii = "\x80-\xff"; # Non-ASCII-Chars are not allowed

    $nqtext = "[^\\\\$nonascii\015\012\"]";
    $qchar = "\\\\[^$nonascii]";

    $protocol = '(?:mailto:)';

    $normuser = '[a-zA-Z0-9][a-zA-Z0-9_.-]*';
    $quotedstring = "\"(?:$nqtext|$qchar)+\"";
    $user_part = "(?:$normuser|$quotedstring)";

    $dom_mainpart = '[a-zA-Z0-9][a-zA-Z0-9._-]*\\.';
    $dom_subpart = '(?:[a-zA-Z0-9][a-zA-Z0-9._-]*\\.)*';
    $dom_tldpart = '[a-zA-Z]{2,5}';
    $domain_part = "$dom_subpart$dom_mainpart$dom_tldpart";

    $regex = "$protocol?$user_part\@$domain_part";

    return preg_match("/^$regex$/", $email);
}

function getFields()
{
    $static_field_types = null;
    $static_field_type_array = array('Polarwerte (Slider)', 'Multiple Choice (Checkboxes)', 'Textarea (max. ' . TEXTAREA_MAX_LENGTH . ' Zeichen)', 'Dropdown');

    for ($x = 0; $x < sizeof($static_field_type_array); $x++) {

        $static_field_types[$x]['id'] = $x;

        $static_field_types[$x]['text'] = $static_field_type_array[$x];
    }

    return $static_field_types;
}

function getPosition()
{

    $home = HOME_DIR;
    $pie = explode("/", $_SERVER['REQUEST_URI']);

    $fN = explode("/", $_SERVER['SCRIPT_NAME']);

    $name = $fN[count($fN) - 1];
    $file = $pie[count($pie) - 1];

    $result = 'Du bist hier: <a href="' . $home . '">Start</a>';

    if (strpos($file, 'position')) {

        if ($name === 'survey.php') $result .= ' - <a href="' . $home . $name . '">Bogen</a>';
        elseif ($name === 'inquiry.php') $result .= ' - Avatar';
        elseif ($name === 'myinquiries.php') $result .= ' - <a href="' . $home . $name . '">Meine Avatare</a>';
        elseif ($name === 'feedback.php') $result .= ' - Feedback';
        elseif ($name === 'users.php') $result .= ' - <a href="' . $home . $name . '">Benutzer</a>';
        elseif ($name === 'news.php') $result .= ' - <a href="' . $home . $name . '">Informationen</a>';
        elseif ($name === 'profile.php') $result .= ' - <a href="' . $home . $name . '">Mein Profil</a>';

        if (strpos($file, 'add_category')) {
            $result .= ' - Kategorie hinzufügen';
        } elseif (strpos($file, 'add_field')) {
            $result .= ' - Feld hinzufügen';
        } elseif (strpos($file, 'add')) {
            $result .= ' - hinzufügen';
        } elseif (strpos($file, 'confirm_delete')) {
            $result .= ' - Löschen';
        } elseif (strpos($file, 'edit')) {
            $result .= ' - Bearbeiten';
        } elseif (strpos($file, 'view')) {
            $result .= ' - Ansehen';
        } elseif (strpos($file, 'show')) {
            $result .= ' - Bearbeiten';
        } elseif (strpos($file, 'evaluate')) {
            $result .= ' - Auswerten';
        } elseif (strpos($file, 'mail')) {
            $result .= ' - Rundmail verfassen';
        }
    } else {
        if ($name === 'survey.php') $result .= ' - Bogen';
        elseif ($name === 'inquiry.php') $result .= ' - Avatar';
        elseif ($name === 'myinquiries.php') $result .= ' - Meine Avatare';
        elseif ($name === 'feedback.php') $result .= ' - Feedback';
        elseif ($name === 'users.php') $result .= ' - Benutzer';
        elseif ($name === 'news.php') $result .= ' - Informationen';
        elseif ($name === 'profile.php') $result .= ' - Mein Profil';
    }
    return str_replace('&', '&amp;', $result);
}