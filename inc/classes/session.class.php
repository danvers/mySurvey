<?php
/**
 *
 * @author < Dan Verstaendig >
 * @version 0.1
 * @module Session/Loginhandler
 */

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) {
    header('Location:' . HOME_DIR);
}

class SessionManagement
{

    private $db, $session_id, $uid;

    public function __construct($session_id)
    {
        $this->db = new mySqlConnection();
        $this->session_id = $session_id;;
        $this->uid = 0;
        $this->db->query("SELECT * FROM " . table_sessions . " WHERE session='" . $this->session_id . "'");
        if ($this->db->NumRows() < 1) {
            $this->db->query("INSERT INTO " . table_sessions . " (session,user,ip,timestamp) VALUES ('" . $this->session_id . "',0,'" . $_SERVER['REMOTE_ADDR'] . "','" . date("YmdHis", time()) . "')");
        }
    }

    public function logout()
    {
        $_SESSION['AuthedUser'] = false;
        @session_destroy();
        unset($_SESSION);
        $this->delete_cookie('surveyUI');
        $this->db->query("UPDATE " . table_sessions . " SET user='0' WHERE session='" . $this->session_id . "'");
    }

    private function delete_cookie($name)
    {
        setcookie($name, '', time() - 4000);
        unset($_COOKIE[$name]);
    }

    public function login($login = '', $pass = '', $checked = 0)
    {

        if ($this->authenticateUser('', '', $checked)) {
            return true;
        }
        if ($this->authenticateUser($login, $pass, $checked)) {

            if ($checked) {
                $content = serialize(array('user' => $login,
                    'pass' => $pass,
                    'cookie' => true));
                $this->set_cookie('surveyUI', base64_encode($content));
            }

            return true;

        } elseif (isset($_COOKIE['surveyUI'])) {

            $cookie = unserialize(base64_decode($_COOKIE['surveyUI']));

            if ($this->authenticateUser($cookie['user'], $cookie['pass'], $cookie['cookie'])) {
                $content = serialize(array('user' => $cookie['user'],
                    'pass' => $cookie['pass'],
                    'cookie' => $cookie['cookie']));
                $this->set_cookie('surveyUI', base64_encode($content));

                return true;
            } else {

                $this->delete_cookie('surveyUI');
                return false;
            }
        }
        return false;
    }

    public function authenticateUser($user = '', $pass = '', $checked = 0)
    {

        if (isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['AuthedUser']) && $_SESSION['AuthedUser'] == true && isset($_SESSION['cookie'])) {
            $user = $_SESSION['user'];
            $pass = $_SESSION['pass'];
            $checked = $_SESSION['cookie'];
        }

        if (strlen($user) > 0) {

            $this->db->query("SELECT usermail, id, userlevel
                  FROM " . table_users . " WHERE
                    usermail   = '" . $this->db->escape_string($user) . "'
                  AND userpass = '" . $this->db->escape_string($pass) . "'");
            $row = $this->db->fetch();
            if (is_array($row)) {
                $_SESSION['user'] = $user;
                $_SESSION['pass'] = $pass;
                $_SESSION['email'] = $row['usermail'];
                $_SESSION['userid'] = $row['id'];
                $_SESSION['userlevel'] = $row['userlevel'];
                $_SESSION['AuthedUser'] = true;
                $_SESSION['cookie'] = $checked;
                $this->updateSession($row['id']);
                return true;
            } else {
                $_SESSION['AuthedUser'] = false;
                @session_destroy();
            }
        }
        return false;
    }

    public function updateSession($user)
    {

        $time = date("YmdHis", time());

        if (isset($_SESSION['userid'])) {

            $this->db->query("DELETE FROM " . table_sessions . " WHERE session !='" . $this->session_id . "' AND user='" . $_SESSION['userid'] . "'");

            $this->db->query("UPDATE " . table_sessions . " SET timestamp='" . $time . "', user=" . $_SESSION['userid'] . ", cookie='" . $_SESSION['cookie'] . "' WHERE session='" . $this->session_id . "' AND ip='" . $_SERVER['REMOTE_ADDR'] . "'");

            $this->db->query("UPDATE " . table_users . " SET last_seen=NOW() WHERE id='" . $user . "' LIMIT 1");

        } else {
            $this->db->query("UPDATE " . table_sessions . " SET timestamp='" . $time . "', user='0' WHERE session='" . $this->session_id . "' AND ip='" . $_SERVER['REMOTE_ADDR'] . "'");
        }
        $this->cleanUp();
    }

    private function cleanUp()
    {
        $edgetime_session_user = date("YmdHis", time() - 60 * 25);
        $edgetime_session_misc = date("YmdHis", time() - 60);
        $this->db->query("DELETE FROM " . table_sessions . " WHERE timestamp < " . $edgetime_session_user . " AND user > 0");
        $this->db->query("DELETE FROM " . table_sessions . " WHERE timestamp < " . $edgetime_session_misc . " AND user = 0");

        $this->db->query('OPTIMIZE TABLE ' . table_sessions);
    }

    private function set_cookie($name, $value)
    {
        setcookie($name, $value, time() + 60 * 60 * 24 * 30);
        $_COOKIE[$name] = $value;
    }

    public function logged_in()
    {

        if (isset($_SESSION['AuthedUser']) && $_SESSION['AuthedUser'] === true) {
            return true;
        } else {
            return false;
        }
    }

    public function __destruct()
    {
        unset($this->db);

    }
}