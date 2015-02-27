<?php
/**
 *
 * @author < >
 * @version
 * @module
 */

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) {
    header('Location:' . HOME_DIR);
}

class User
{

    private $db, $id;

    private $cache = array();
    private $fields = array('firstname',
        'lastname', 'usermail', 'userlevel', 'userpass', 'id');

    public function __construct($id, $database)
    {
        $this->db = $database;
        $this->id = $id;
    }

    public function changePass($pass)
    {
        $pass = md5($pass);
        $this->db->query('UPDATE ' . table_users . ' SET userpass="' . $pass . '" WHERE id="' . $this->id . '" LIMIT 1');
        return ($this->db->error() == 0);
    }

    public function passIsValid($pass)
    {
        $pass = md5($pass);
        $this->db->query("SELECT * FROM " . table_users . " WHERE userpass = '" . $pass . "' AND id ='" . $this->id . "'");

        if ($this->db->numRows() == 1) {
            return true;
        }
        return false;
    }

    public function createNewUser($array)
    {
        $pass = $this->createRandomPass();
        $this->db->query("SELECT id FROM " . table_users . " WHERE usermail = '" . $this->db->escape_string($array['email']) . "' LIMIT 1");

        if ($this->db->numRows() > 0) {
            return false;
        }

        $text = "dies ist eine Einladung zum Arbeitsbereich\nDeine Login-Email lautet: %s\nDein Passwort lautet: %s\n\nEinloggen kannst Du dich unter: ". HOME_DIR;
        $content = sprintf($text, $array['email'], $pass);

        $this->UserMail($array, $content, 'Seminar "Avatare..." - Einladung zum Arbeitsbereich');

        $this->db->query("INSERT INTO " . table_users . "  (id,firstname,lastname,usermail,userpass,userlevel) VALUES ('','" . $array['firstname'] . "', '" . $array['lastname'] . "', '" . $array['email'] . "','" . md5($pass) . "','1')");


        return true;
    }

    private function createRandomPass()
    {
        $password = "";
        $pw = "ABCDEFGHJKMNOPQRSTUVWXYZabcdefghjkmnopqrstuvwxyz0123456789";
        srand((double)microtime() * 1000000);
        for ($i = 1; $i <= 5; $i++) {
            $password .= $pw{rand(0, strlen($pw) - 1)};
        }
        return $password;
    }

    private function UserMail($array, $content, $subject)
    {

        $header = "Hallo " . $array['firstname'] . ",\n";

        $body = $header . $content;

        $from_address = $this->__get('usermail');
        if (!strlen($from_address)) {
            $from_address = MAIL_OWNER_ADDRESS;
        }
        $from_name = $this->__get('firstname') . ' ' . $this->__get('lastname');
        if (!strlen($from_name)) {
            $from_name = MAIL_OWNER_NAME;
        }
        $to_address = $array['email'];

        $eol = "\r\n";

        $headers = "From: " . $from_name . "<" . $from_address . ">" . $eol;
        $headers .= "Reply-To: " . $from_name . "<" . $from_address . ">" . $eol;
        $headers .= "Return-Path: " . $from_name . "<" . $from_address . ">" . $eol;
        $headers .= "Message-ID: <" . time() . "-" . $from_address . ">" . $eol;
        $headers .= "X-Mailer: PHP v" . phpversion() . $eol;
        $headers .= "From: " . $from_name . " <" . $from_address . ">\n" .
            "Content-Type: text/plain; charset=ISO-8859-1\n";

        return mail($to_address, $subject, $body, $headers);
    }

    public function __get($field)
    {
        if (!in_array($field, $this->fields)) {
            return false;
        }

        if (!isset($this->cache[$field])) {
            $query = sprintf("SELECT %s FROM " . table_users . " WHERE id = '%d';", $field, $this->id);
            $this->db->query($query);
            $result = $this->db->fetch();
            $this->cache[$field] = $result[$field];
        }

        return $this->cache[$field];
    }

    public function __set($field, $value)
    {

        if (!in_array($field, $this->fields)) {
            return;
        }

        $querystring = sprintf("UPDATE " . table_users . " SET  %s='%s' WHERE id='%s' LIMIT 1;", $field, $this->db->escape_string($value), $this->id);

        $this->db->query($querystring);

        $this->cache[$field] = $value;

    }

    public function reInviteUser($userid)
    {

        $pass = $this->createRandomPass();

        $this->db->query("SELECT usermail,firstname,lastname FROM " . table_users . " WHERE id = '" . $this->db->escape_string($userid) . "' LIMIT 1");

        $res1 = $this->db->fetchArray();

        $array['lastname'] = $res1['lastname'];
        $array['fistname'] = $res1['firstname'];
        $array['email'] = $res1['usermail'];

        $text = "dies ist eine Einladung zum Arbeitsbereich\nDeine Login-Email lautet: %s\nDein Passwort lautet: %s\n\nEinloggen kannst Du dich unter: ". HOME_DIR;

        $content = sprintf($text, $array['email'], $pass);

        if ($this->UserMail($array, $content, 'Seminar "Avatare..." - Einladung zum Arbeitsbereich')) {

            $this->db->query("UPDATE " . table_users . " SET userpass = '" . md5($pass) . "' WHERE id='" . $res1->id . "' LIMIT 1");

            return true;
        } else {
            return false;
        }
    }

    public function isUniqueEmail($email)
    {
        $this->db->query("SELECT id FROM " . table_users . " WHERE usermail = '" . $this->db->escape_string($email) . "' LIMIT 1");
        return ($this->db->numRows() == 1);
    }

    public function resetPass($email)
    {

        $pass = $this->createRandomPass();

        $this->db->query("SELECT usermail,id,firstname,lastname FROM " . table_users . " WHERE usermail = '" . $this->db->escape_string($email) . "' LIMIT 1");

        $res1 = $this->db->fetchArray();

        $array['lastname'] = $res1['lastname'];
        $array['fistname'] = $res1['firstname'];
        $array['email'] = $res1['usermail'];

        $text = "Dein neues Passwort lautet: %s\nDieses Passwort wurde zufällig generiert. Daher wird empfohlen es selbst zu ändern.\n\nEinloggen kannst Du dich unter: ". HOME_DIR;

        $content = sprintf($text, $pass);

        if ($this->UserMail($array, $content, 'Seminar "Avatare..." - Passwortänderung')) {

            $this->db->query("UPDATE " . table_users . " SET userpass = '" . md5($pass) . "' WHERE id='" . $res1->id . "' LIMIT 1");

            return true;
        } else {
            return false;
        }

    }

    public function deleteUser($userid)
    {
        $this->db->query('DELETE FROM ' . table_users . ' WHERE id="' . $userid . '" LIMIT 1');
    }

    public function editUser($userid, $data)
    {
        $this->db->query("UPDATE " . table_users . " SET  firstname ='" . $data['firstname'] . "', lastname ='" . $data['lastname'] . "', usermail ='" . $data['email'] . "' WHERE id='" . $userid . "' LIMIT 1");

    }

    public function __destruct()
    {
        $this->db->__destruct();
    }
}