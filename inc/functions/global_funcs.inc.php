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
    $static_field_type_array = array(
        sprintf(FIELD_TEXTAREA,TEXTAREA_MAX_LENGTH),
        FIELD_POLAR,
        FIELD_DROPDOWN,
        FIELD_CHECKBOX
    );

    for ($x = 0; $x < sizeof($static_field_type_array); $x++) {
        $static_field_types[$x]['id'] = $x;
        $static_field_types[$x]['text'] = $static_field_type_array[$x];
    }

    return $static_field_types;
}

/**
 * @return string
 * @description just a workaround for a breadcrumb script..
 */
function getPosition()
{
    $result[] = NAV_BREADCRUMB;
    if(basename($_SERVER['SCRIPT_NAME']) == 'index.php'){
        $result[] = NAV_HOME;
    }else{
        if(isset($_GET['position'])){
            $result[] = '<a href="'. HOME_DIR . '">'.NAV_HOME.'</a>';
            $result[] = '<a href="'. HOME_DIR . basename($_SERVER['SCRIPT_NAME']).'">'.TITLE.'</a>';
        }else{
            $result[] = '<a href="'. HOME_DIR . '">'.NAV_HOME.'</a>';
            $result[] = TITLE;
        }
    }

    if(isset($_GET['position'])){
        $position = $_GET['position'];
        switch($position){
            case 'add':
            case 'add_field':
            case 'add_category':
                $result[] = NAV_ADD;
                break;
            case 'edit':
            case 'evaluate':
                $result[] = NAV_EDIT;
                break;
            case 'preview':
            case 'mail':
                $result[] = NAV_PREVIEW;
                break;
            case 'confirm_delete':
                $result[] = NAV_DELETE;
                break;
        }
    }
    $ret = '';
    for($i=0;$i<sizeof($result);$i++){
        $ret .= $result[$i];
        if($i>0 && $i <sizeof($result)-1){
            $ret .=  ' &raquo; ';
        }
    }
    return $ret;
}