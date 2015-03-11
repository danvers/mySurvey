<?php
/**
 * @author Dan Verständig
 */

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) die();

class SessionManagement
{
    private $db, $session_id, $uid;

    public function __construct($session_id)
    {
        $this->db = new database();
        $this->session_id = $session_id;;
        $this->uid = 0;
        $data = array(':session_id' => $this->session_id);
        $this->db->query('SELECT * FROM ' . table_sessions . ' WHERE session= :session_id', $data);

        if ($this->db->rowCount() < 1) {

            $data = array(
                    'session'   =>  $this->session_id,
                    'user'      =>  0,
                    'ip'        =>  $_SERVER['REMOTE_ADDR'],
                    'timestamp' =>  date("YmdHis", time())
                    );
            $this->db->query('INSERT INTO ' . table_sessions . ' (session,user,ip,timestamp) VALUES (:session,:user,:ip,:timestamp)', $data);
        }
    }

    public function logout()
    {
        session_unset();
        $data = array(':user'=> 0, ':session' => $this->session_id);
        $this->db->query('UPDATE  '. table_sessions . ' SET user=:user WHERE session=:session', $data );
        $this->delete_cookie(COOKIE_NAME);
    }
    private function set_cookie($value)
    {
        $expire     = time() + 60 * 60 * 24 * 7;
        $key        = hash_hmac( 'sha256', $value . $expire, COOKIE_SECRET);
        $hash       = hash_hmac( 'sha256', $value . $expire, $key );
        $content = base64_encode($value) . '|' . $expire . '|' . $hash;
        $data = array(':remember' => $content, ':id'=> $value);
        $this->db->query('UPDATE ' . table_users . ' SET remember=:remember WHERE usermail =:id LIMIT 1',$data);
        setcookie(COOKIE_NAME,$content,$expire);
    }
    private function delete_cookie()
    {
        setcookie(COOKIE_NAME, "", time()-3600);
    }

    public function login($login = '', $pass = '', $checked = 0)
    {
        if($this->authenticateUser('', '')){
            return true;
        }

        if ($this->authenticateUser($login, $pass, $checked)) {

            if ($checked) {
                $this->set_cookie(COOKIE_NAME, $login);
            }
            return true;

        }elseif(isset($_COOKIE[COOKIE_NAME])){

            $content = $_COOKIE[COOKIE_NAME];
            list( $user, $expire, $hmac ) = explode('|', $content);

            if($expire < time()){
                $this->delete_cookie();
                return false;
            }
            $key    = hash_hmac( 'sha256', $user . $expire, COOKIE_SECRET );
            $hash   = hash_hmac( 'sha256', $user . $expire, $key );

            if ( $hmac != $hash ){
                $this->delete_cookie();
                return false;
            }
            $data   = array(':usermail'=> base64_decode($user), ':remember'=>$content);
            $this->db->query('SELECT u.*, s.user
                                FROM ' . table_users . ' u LEFT JOIN '.table_sessions.' s on u.id = s.user
                                WHERE u.usermail=:usermail AND u.remember =:remember', $data);
            if($this->db->rowCount() == 1){
                $row = $this->db->fetch();
                $_SESSION['user']       = $row['username'];
                $_SESSION['pass']       = $row['userpass'];
                $_SESSION['email']      = $user;
                $_SESSION['userid']     = $row['id'];
                $_SESSION['userlevel']  = $row['userlevel'];
                $_SESSION['AuthedUser'] = true;
                $_SESSION['cookie']     = 1;
                $this->updateSession();
                return true;
            }
        }
        session_unset();
        return false;
    }

    private function authenticateUser($user = '', $pass = '', $checked = 0)
    {
        if ( (isset($_SESSION['user']) && isset($_SESSION['pass']) && isset($_SESSION['userid']) ) &&
             (isset($_SESSION['AuthedUser']) && $_SESSION['AuthedUser'] == true) ) {
            $user   = $_SESSION['user'];
            $pass   = $_SESSION['pass'];
            $uid    = $_SESSION['userid'];
            $data   = array(':usermail'=> $user, ':userpass'=>$pass,':userid'=>$uid);
            $this->db->query('SELECT u.id, s.user
                                FROM ' . table_users . ' u LEFT JOIN '.table_sessions.' s on u.id = s.user
                                WHERE u.usermail =:usermail AND u.userpass =:userpass AND s.user =:userid', $data);
            if($this->db->rowCount() == 1){
                return true;
            }
        }

        if (strlen($user) > 0) {
            $data =  array(':usermail'=> $user);
            $this->db->query('SELECT userpass FROM ' . table_users . ' WHERE usermail =:usermail', $data);
            $pass_check = $this->db->fetch();
            $hash = $pass_check['userpass'];

            if($this->db->rowCount()==1) {
                if (password_verify($pass, $hash)) {

                    $this->db->query('SELECT * FROM ' . table_users . ' WHERE usermail =:usermail', $data);
                    $row = $this->db->fetch();
                    $_SESSION['user']       = $user;
                    $_SESSION['pass']       = $hash;
                    $_SESSION['email']      = $row['usermail'];
                    $_SESSION['userid']     = $row['id'];
                    $_SESSION['userlevel']  = $row['userlevel'];
                    $_SESSION['AuthedUser'] = true;
                    $_SESSION['cookie']     = $checked;
                    $this->updateSession();
                    return true;
                } else {
                    $_SESSION['AuthedUser'] = false;
                }
            }
        }
        return false;
    }

    private function updateSession()
    {
        $time = date("YmdHis", time());
        $ip = $_SERVER['REMOTE_ADDR'];

        if (isset($_SESSION['userid'])) {

            $this->db->query('DELETE FROM ' . table_sessions . ' WHERE session != :session AND user = :user', array(':session' => $this->session_id, ':user'=>$_SESSION['userid']));

            $data = array(':timestamp' => $time, ':user'=>$_SESSION['userid'], ':cookie'=>$_SESSION['cookie'],':session'=>$this->session_id, ':ip'=>$ip);
            $this->db->query('UPDATE ' . table_sessions . ' SET timestamp=:timestamp, user=:user, cookie=:cookie
                                WHERE session=:session AND ip=:ip' , $data);

            $this->db->query('UPDATE ' . table_users . ' SET last_seen=NOW() WHERE id=:user LIMIT 1',  array(':user' => $_SESSION['userid']));
        } else {
            $data = array(':timestamp' => $time,':user'=>0,':session'=>$this->session_id, ':ip'=>$ip);
            $this->db->query('UPDATE ' . table_sessions . ' SET timestamp=:timestamp, user=:user WHERE session=:session AND ip=:ip',$data);
        }
        $this->cleanUp();
    }

    private function cleanUp()
    {
       $limit_user = date("YmdHis", time() - 60 * 25);
       $limit_anon = date("YmdHis", time() - 60);
       $this->db->query('DELETE FROM ' . table_sessions . ' WHERE timestamp < ' . $limit_user . ' AND user > 0');
       $this->db->query('DELETE FROM ' . table_sessions . ' WHERE timestamp < ' . $limit_anon . ' AND user = 0');
       $this->db->query('OPTIMIZE TABLE ' . table_sessions);
    }

    public function logged_in()
    {
        return (isset($_SESSION['AuthedUser']) && $_SESSION['AuthedUser'] === true);
    }

    public function __destruct()
    {
        unset($this->db);
    }
}
