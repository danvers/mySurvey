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

    public function getName($id)
    {
        $this->db->query('SELECT title FROM ' . table_survey . ' WHERE id=:id LIMIT 1',array(':id'=>$id) );
        $result = $this->db->fetch();
        return $result['title'];
    }

    public function isLegal($id, $uid)
    {
        $data = array(':id' => $id, ':userid'=>$uid);
        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id=:id AND userid=:userid LIMIT 1',$data);
        return ($this->db->rowCount() == 1);
    }

    public function delete($id, $uid)
    {
        $data = array(':id' => $id, ':userid'=>$uid);
        $this->db->query('SELECT id FROM ' . table_survey . ' WHERE id=:id AND userid= :userid LIMIT 1',$data);

        if ($this->db->rowCount() == 1) {
            $this->db->query('DELETE FROM ' . table_survey . ' WHERE id=:id AND userid= :userid LIMIT 1',$data);
            $this->db->query('DELETE FROM ' . table_feedback . ' WHERE avatarid=:aid',array(':aid'=>$id));
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
    public function update($inserts, $id, $uid)
    {
        $data = array(
            ':title'        =>  $inserts['title'],
            ':url'          =>  $inserts['url'],
            ':description'  =>  $inserts['description'],
            ':userid'       =>  $uid,
            ':id'           =>  $id
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
        return (int)$entries['sum'];
    }

    public function __destruct()
    {
        $this->db = null;
    }
}