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

class Avatar
{

    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    public function getName($avatarID)
    {

        $this->db->query('SELECT title FROM ' . table_survey . ' WHERE id="' . $avatarID . '" LIMIT 1');

        $result = $this->db->fetchArray();

        return htmlspecialchars($result['title']);
    }

    public function isLegal($avatarID, $userID)
    {
        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id="' . $avatarID . '" AND userid="' . $userID . '" LIMIT 1');
        return ($this->db->numRows() == 1);
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

    public function add($inserts, $user)
    {
        $this->db->query("INSERT INTO " . table_survey . " ( title, url, description, userid) VALUES ('" . $this->db->escape_string($inserts['name']) . "','" . $this->db->escape_string($inserts['url']) . "','" . $this->db->escape_string($inserts['description']) . "'," . $user . ")");

    }

    public function update($inserts, $avatar, $user)
    {

        return $this->db->query('UPDATE ' . table_survey . ' SET title="' . $this->db->escape_string($inserts['title']) . '", url="' . $this->db->escape_string($inserts['url']) . '", description="' . $this->db->escape_string($inserts['description']) . '" WHERE id="' . $avatar . '" AND userid ="' . $user . '" LIMIT 1');
    }

    public function evaluate($query, $avatar, $user)
    {

        $this->db->query('UPDATE ' . table_survey . ' SET ' . $query . ' WHERE id="' . $avatar . '" AND userid ="' . $user . '" LIMIT 1');

    }

    public function cleanExports($dir, $time)
    {

        $handle = @opendir($dir);

        while (false !== ($oldfile = readdir($handle))) {
            if (preg_match("=^\.{1,2}$=", $oldfile)) {
                continue;
            }
            if (is_dir($dir . $oldfile)) {
                continue;
            } else {
                $cTime = ceil((time() - filectime($dir . $oldfile)) / 60);

                if ($cTime > $time) {
                    unlink($dir . $oldfile);
                }
            }
        }

        @closedir($handle);
    }

    public function getEntries()
    {
        $this->db->query('SELECT COUNT(id) AS sum FROM ' . table_survey);
        $entries = $this->db->fetchArray();
        return $entries['sum'];
    }

    public function __destruct()
    {
        unset($this->db);
    }


}