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

        $result = $this->db->fetch();

        return htmlspecialchars($result['title']);
    }

    public function isLegal($avatarID, $userID)
    {
        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id="' . $avatarID . '" AND userid="' . $userID . '" LIMIT 1');
        return ($this->db->rowCount() == 1);
    }

    public function delete($avatarID, $userID)
    {
        $data = array(':id' => $avatarID, ':userid'=>$userID);

        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id=:id AND userid= :userid LIMIT 1',$data);

        if ($this->db->rowCount() == 1) {
            $this->db->query('DELETE FROM ' . table_survey . ' WHERE id=:id AND userid= :userid LIMIT 1',$data);
            $this->db->query('DELETE FROM ' . table_feedback . ' WHERE avatarid=:aid',array(':aid'=>$avatarID));
            return true;
        }
        return false;
    }

    public function add($inserts, $user)
    {
        $data = array(
                ':title'        =>  $inserts['title'],
                ':url'          =>  $inserts['url'],
                ':description'  =>  $inserts['description'],
                ':userid'       =>  $user
                );
        $this->db->query('INSERT INTO ' . table_survey . ' ( title, url, description, userid)
                            VALUES (:title,:url,:description,:userid)',$data);
    }
    public function update($inserts, $avatar, $user)
    {
        $data = array(
            ':title'        =>  $inserts['title'],
            ':url'          =>  $inserts['url'],
            ':description'  =>  $inserts['description'],
            ':userid'       =>  $user,
            ':id'           =>  $avatar
        );
        return $this->db->query('UPDATE ' . table_survey . ' SET title=:title, url=:url, description=:description WHERE id=:id AND userid =:userid LIMIT 1', $data);
    }

    public function evaluate($query, $avatar, $user,$data)
    {
        $this->db->query('UPDATE ' . table_survey . ' SET ' . $query . ' WHERE id="' . $avatar . '" AND userid ="' . $user . '" LIMIT 1', $data);

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
        $entries = $this->db->fetch();
        return $entries['sum'];
    }

    public function __destruct()
    {
        $this->db = null;
    }
}