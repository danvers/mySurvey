<?php
/**
 * @author Dan Verständig
 * @description this database class is just an old implementation based on mysql connections.
 * @todo rewrite the sql function and implement prepared statements
 */

/**
 * Security Handler
 */
if (!defined('IN_PAGE')) die();

require_once('inc/cfg/db_config.inc.php');

class database
{
    private $connection;
    private $stmt;

    public function __construct()
    {
        $dsn = DB_TYPE . ':host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8';

        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );

        try {
            $this->connection = new PDO($dsn, DB_USER, DB_PASS, $options);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
        }
    }
    public function error(){
        return $this->error();
    }
    public function query($query, $data = null){
        $this->stmt = $this->connection->prepare($query);
        if(!is_null($data)){
            $this->bind($data);
        }
        $this->execute();
    }
    public function execute(){
        return $this->stmt->execute();
    }
    public function lastInsertId(){
        return $this->connection->lastInsertId();
    }
    public function rowCount(){
        $this->execute();
        return $this->stmt->rowCount();
    }
    public function fetch(){
        return $this->stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function bind($data){
        foreach($data as $key => $value){

            switch ($value) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
            $this->stmt->bindValue($key, $value, $type);
        }
    }
    public function __destruct (){
        $this->connection = null;
    }
}
