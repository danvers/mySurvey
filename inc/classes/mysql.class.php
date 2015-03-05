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

class mySqlConnection
{
    private $result = NULL;
    private $sql = NULL;
    private $error = 0;

    private $connection;

    public function __construct()
    {
        $this->connect();
    }

    public function connect()
    {
        try {
            $this->connection = mysqli_connect(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        } catch (Exception $e) {
            echo 'Error:' . htmlspecialchars($e->getMessage());
        }
    }

    public function escape_string($input)
    {
        return mysqli_real_escape_string($this->connection, $input);
    }

    public function query($query)
    {
        if ($this->connection == false) $this->connect();
        if ($this->connection == false) return;

        $this->sql = $query;
        $this->result = mysqli_query($this->connection, $this->sql);

        if (!$this->result) {
            $this->error = mysqli_error($this->connection);
        }
    }

    public function numRows()
    {
        if ($this->error()) {
            echo $this->error;
            return false;
        }
        return mysqli_num_rows($this->result);
    }

    private function error()
    {
        return mysqli_error($this->connection);
    }

    public function fetch()
    {
        if ($this->error()) {
            echo $this->error;
            return false;
        }
        return mysqli_fetch_assoc($this->result);
    }

    public function fetchField()
    {
        if ($this->error()) {
            echo $this->error;
            return false;
        }
        return mysqli_fetch_field($this->result);
    }

    public function fetchArray()
    {
        if ($this->error()) {
            echo $this->error;
            return false;
        }
        return mysqli_fetch_array($this->result);
    }

    public function fetchRow()
    {
        if ($this->error()) {
            echo $this->error;
            return false;
        }
        return mysqli_fetch_row($this->result);
    }

    public function num_fields()
    {
        return mysqli_num_fields($this->result);
    }

    public function free()
    {
        mysqli_free_result($this->result);
    }

    public function __destruct()
    {
        if ($this->connection == false) return;
        mysqli_close($this->connection);
        $this->connection = false;
    }
}
