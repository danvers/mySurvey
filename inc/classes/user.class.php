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
    private $options;
    private $cache = array();
    private $fields = array('firstname',
        'lastname', 'usermail', 'userlevel', 'userpass', 'id');

    public function __construct($id, $database)
    {
        $this->db = $database;
        $this->id = $id;
        $this->options = array(
                            'cost' => 11,
                            'salt' => mcrypt_create_iv(22, MCRYPT_DEV_URANDOM)
                            );
    }

    public function makeHash($pass){
        return password_hash($pass, PASSWORD_BCRYPT, $this->options);
    }
    public function changePass($pass)
    {
        $hash = $this->makehash($pass);
        $data = array(':pass'=> $hash, ':id'=>$this->id,':cp'=> null,':expires'=>null);
        $this->db->query('UPDATE ' . table_users . ' SET userpass=:pass, change_pass=:cp, expires=:expires WHERE id=:id LIMIT 1' , $data);
        return true;
    }

    public function passIsValid($pass)
    {
        $data = array(':id'=>$this->id);
        $this->db->query('SELECT userpass FROM ' . table_users . ' WHERE id =:id',$data);
        $hash = $this->db->fetch();
        return password_verify($pass,$hash['userpass']);
    }

    public function createNewUser($array)
    {
        $data = array(':mail' => $array['email']);
        $this->db->query('SELECT id FROM ' . table_users . ' WHERE usermail = :mail LIMIT 1',$data);

        if ($this->db->rowCount() > 0) {
            return false;
        }

        $invite = time();
        $code = md5($invite);
        $data[':firstname']     = $array['firstname'];
        $data[':lastname']      = $array['lastname'];
        $data[':changepass']    = $code;
        $data[':expires']       = ($invite + 60 * 60 * 24 * 7);
        $set_password_url       = HOME_DIR.'index.php?position=activate&code='.$code;
        $this->db->query('INSERT INTO ' . table_users . '  (firstname,lastname,usermail,userlevel,expires,change_pass)
                            VALUES (:firstname,:lastname,:mail,1,FROM_UNIXTIME(:expires),:changepass)',$data);

        $text = MAIL_TEXT_INVITE;
        $content = sprintf($text, $set_password_url);
        $this->UserMail($array, $content, MAIL_TITLE_INVITE);
        return true;
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
        $this->db->query('SELECT usermail,firstname,lastname FROM ' . table_users . ' WHERE id =:id LIMIT 1',array(':id'=> $userid));

        $result = $this->db->fetch();

        $invite = time();
        $array['lastname']      = $result['lastname'];
        $array['fistname']      = $result['firstname'];
        $array['email']         = $result['usermail'];
        $data[':change_pass']    = $invite;
        $data[':expires']       = ($invite + 60 * 60 * 24 * 7);
        $data[':id']             = $result['id'];
        $set_password_url       = HOME_DIR.'index.php?position=activate&code='.md5($invite);
        $text = MAIL_TEXT_INVITE;

        $content = sprintf($text, $array['email'], $set_password_url);

        if ($this->UserMail($array, $content, MAIL_TITLE_INVITE)) {
            $this->db->query('UPDATE ' . table_users . ' SET change_pass=:change_pass, expires =:expires WHERE id=:id LIMIT 1',$data);
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
        $this->db->query('SELECT usermail,id,firstname,lastname FROM ' . table_users . ' WHERE usermail = :mail LIMIT 1', array(':mail'=>$email));

        $result = $this->db->fetch();
        $invite = time();
        $array['lastname']      = $result['lastname'];
        $array['fistname']      = $result['firstname'];
        $array['email']         = $result['usermail'];
        $data[':change_pass']   = $invite;
        $data[':expires']       = ($invite + 60 * 60 * 24);
        $data[':id']            = $result['id'];
        $set_password_url       = HOME_DIR.'index.php?position=activate&code='.md5($invite);
        $text = MAIL_TEXT_PASS_RESET;

        $content = sprintf($text, $set_password_url);

        if ($this->UserMail($array, $content, MAIL_TITLE_PASS_RESET)) {
            $this->db->query('UPDATE ' . table_users . ' SET change_pass=:change_pass, expires =:expires WHERE id=:id LIMIT 1',$data);
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