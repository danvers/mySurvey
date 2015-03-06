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
if (!defined('IN_PAGE')) die();

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
        $data = array(':pass'=> $pass, ':id'=>$this->id);
        $this->db->query('UPDATE ' . table_users . ' SET userpass=:pass WHERE id=:id LIMIT 1' , $data);
        return ($this->db->error() == 0);
    }

    public function passIsValid($pass)
    {
        $pass = md5($pass);
        $data = array(':pass'=> $pass, ':id'=>$this->id);
        $this->db->query('SELECT * FROM ' . table_users . ' WHERE userpass = :pass AND id =:id',$data);

        return ($this->db->rowCount() == 1);
    }

    public function createNewUser($array)
    {

        $data = array(':mail' => $array['email']);
        $this->db->query('SELECT id FROM ' . table_users . ' WHERE usermail = :mail LIMIT 1',$data);

        if ($this->db->rowCount() > 0) {
            return false;
        }
        $pass = $this->createRandomPass();
        $text = "dies ist eine Einladung zum Arbeitsbereich\nDeine Login-Email lautet: %s\nDein Passwort lautet: %s\n\nEinloggen kannst Du dich unter: " . HOME_DIR;
        $content = sprintf($text, $array['email'], $pass);

        $this->UserMail($array, $content, 'Seminar "Avatare..." - Einladung zum Arbeitsbereich');

        $data[':firstname'] = $array['firstname'];
        $data[':lastname'] = $array['lastname'];
        $data[':pass'] = md5($pass);
        $this->db->query('INSERT INTO ' . table_users . '  (firstname,lastname,usermail,userpass,userlevel)
                            VALUES (:firstname,:lastname,:mail,:pass,1)');
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

        $headers = "From: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Reply-To: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Return-Path: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Message-ID: <" . time() . "-" . $from_address . ">" . PHP_EOL;
        $headers .= "X-Mailer: PHP v" . phpversion() . PHP_EOL;
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
            $this->db->query('SELECT '.$field. ' FROM '.table_users.' WHERE id =:id', array(':id'=> $this->id));
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
        $this->db->query('UPDATE ' . table_users . ' SET  '.$field.'=:field WHERE id=:id LIMIT 1', array(':field'=>$value,':id'=> $this->id) );

        $this->cache[$field] = $value;

    }

    public function reInviteUser($userid)
    {

        $pass = $this->createRandomPass();

        $this->db->query('SELECT usermail,firstname,lastname FROM ' . table_users . ' WHERE id =:id LIMIT 1',array(':id'=> $userid));

        $result = $this->db->fetch();

        $array['lastname'] = $result['lastname'];
        $array['fistname'] = $result['firstname'];
        $array['email'] = $result['usermail'];

        $text = "dies ist eine Einladung zum Arbeitsbereich\nDeine Login-Email lautet: %s\nDein Passwort lautet: %s\n\nEinloggen kannst Du dich unter: " . HOME_DIR;

        $content = sprintf($text, $array['email'], $pass);

        if ($this->UserMail($array, $content, 'Seminar "Avatare..." - Einladung zum Arbeitsbereich')) {

            $data = array(':pass'=>md5($pass), ':id'=> $result['id']);
            $this->db->query('UPDATE ' . table_users .' SET userpass = :pass WHERE id=:id LIMIT 1',$data);

            return true;
        }
        return false;
    }

    public function isUniqueEmail($email)
    {
        $this->db->query('SELECT id FROM ' . table_users . ' WHERE usermail = :mail', array(':mail' => $email));
        return ($this->db->rowCount() == 1);
    }

    public function resetPass($email)
    {
        $pass = $this->createRandomPass();

        $this->db->query('SELECT usermail,id,firstname,lastname FROM ' . table_users . ' WHERE usermail = :mail LIMIT 1', array(':mail'=>$email));

        $result = $this->db->fetch();

        $array['lastname']  = $result['lastname'];
        $array['fistname']  = $result['firstname'];
        $array['email']     = $result['usermail'];

        $text = "Dein neues Passwort lautet: %s\nDieses Passwort wurde zufällig generiert. Daher wird empfohlen es selbst zu ändern.\n\nEinloggen kannst Du dich unter: " . HOME_DIR;

        $content = sprintf($text, $pass);

        if ($this->UserMail($array, $content, 'Seminar "Avatare..." - Passwortänderung')) {

            $data = array(':pass'=>md5($pass), 'id:'=> $result['id']);
            $this->db->query('UPDATE ' . table_users . ' SET userpass =:pass WHERE id=:id LIMIT 1',$data);

            return true;
        }
        return false;

    }

    public function deleteUser($userid)
    {
        $this->db->query('DELETE FROM ' . table_users . ' WHERE id=:id LIMIT 1',array(':id'=>$userid));
    }

    public function editUser($userid, $values)
    {
        $data = array(
                    ':id'       =>  $userid,
                    ':firstname' =>  $values['firstname'],
                    ':lastname'  =>  $values['lastname'],
                    ':mail' =>  $values['email']);

        $this->db->query('UPDATE ' . table_users . ' SET  firstname =:firstname, lastname=:lastname, usermail=:mail WHERE id=:id LIMIT 1',$data);

    }

    public function __destruct()
    {
        $this->db->__destruct();
    }
}