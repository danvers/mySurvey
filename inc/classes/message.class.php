<?php
/**
 *
 * @author < Dan Verstaendig >
 * @version 0.1
 * @module AvatarClass
 */

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) die();

class Message
{

    private $db;
    private $tables = array(table_survey, table_feedback, table_news);

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function delete($avatarID, $userID)
    {

        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id="' . $avatarID . '" AND userid="' . $userID . '" LIMIT 1');

        if ($this->db->numRows() == 1) {

            $this->db->query('DELETE FROM ' . table_survey . ' WHERE id="' . $avatarID . '" AND userid="' . $userID . '" LIMIT 1');
            $this->db->query('DELETE FROM ' . table_feedback . ' WHERE avatarid="' . $avatarID . '"');

            return true;

        } else {

            return false;

        }

    }

    public function exists($editID, $table)
    {

        if (!in_array($table, $this->tables)) {
            return false;
        }

        $this->db->query('SELECT  id FROM ' . $table . ' WHERE  id = "' . $editID . '" LIMIT 1');

        return ($this->db->numRows() == 1);

    }

    public function massMail(User $user, $recipients, $content, $title)
    {

        $bcc = "";
        $headers = "";
        $from_address = $user->__get('usermail');

        $from_name = $user->__get('firstname') . ' ' . $user->__get('lastname');

        foreach ($recipients as $k => $v) {
            $bcc .= "Bcc: " . $v . PHP_EOL;
        }
        $headers .= "From: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Reply-To: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Return-Path: " . $from_name . "<" . $from_address . ">" . PHP_EOL;
        $headers .= "Message-ID: <" . time() . "-" . $from_address . ">" . PHP_EOL;
        $headers .= "X-Mailer: PHP v" . phpversion() . PHP_EOL;
        $headers .= $bcc . PHP_EOL;


        return mail($from_name, $title, $content, $headers);
    }

    public function __destruct()
    {
        $this->db->__destruct();
    }
}