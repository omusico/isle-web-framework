<?php
/**
 * Implements functions for using PDO driver of PHP for sqlite
 *
 * Only sqlite3 is (good) supported.
 * Therefore an upgrade function is supplied, when you have also the
 * Sqlite Extension running.
 */
class helper_plugin_sqlite_adapter_pdosqlite extends helper_plugin_sqlite_adapter {
    protected $fileextension = '.sqlite3';
    /** @var $db PDO */
    protected $db;

    /**
     * return name of adapter
     */
    public function getName() {
        return DOKU_EXT_PDO;
    }

    /**
     * Registers a User Defined Function for use in SQL statements
     */
    public function create_function($function_name, $callback, $num_args) {
        $this->db->sqliteCreateFunction($function_name, $callback, $num_args);
    }

    /**
     * open db
     */
    public function opendb($init, $sqliteupgrade = false) {
        if($init) {
            $oldDbfile = substr($this->dbfile, 0, -1);

            if(@file_exists($oldDbfile)) {

                $notfound_msg = "SQLite: '".$this->dbname.$this->fileextension."' database not found. In the meta directory is '".$this->dbname.substr($this->fileextension, 0, -1)."' available. ";
                global $ID;
                if($this->isSqlite3db($oldDbfile)) {
                    msg($notfound_msg."PDO sqlite needs a rename of the file extension to '.sqlite3'. For admins more info via Admin > <a href=\"".wl($ID, array('do'=> 'admin', 'page'=> 'sqlite'))."\">Sqlite Interface</a>.", -1);
                    return false;
                } else {
                    //don't block connecting db, when upgrading
                    if(!$sqliteupgrade) {
                        msg($notfound_msg."PDO sqlite needs a upgrade of this sqlite2 db to sqlite3 format. For admins more info via Admin > <a href=\"".wl($ID, array('do'=> 'admin', 'page'=> 'sqlite'))."\">Sqlite Interface</a>.", -1);
                        return false;
                    }
                }
            }
        } else {
            if(!$this->isSqlite3db($this->dbfile)) {
                msg("SQLite: failed to open SQLite '".$this->dbname."' database (DB has not a sqlite3 format.)", -1);
                return false;
            }
        }

        $dsn = 'sqlite:'.$this->dbfile;

        try {
            $this->db = new PDO($dsn);
        } catch(PDOException $e) {
            msg("SQLite: failed to open SQLite '".$this->dbname."' database (".$e->getMessage().")", -1);
            return false;
        }
        $this->db->sqliteCreateAggregate(
            'group_concat',
            array($this, '_pdo_group_concat_step'),
            array($this, '_pdo_group_concat_finalize')
        );
        return true;
    }

    /**
     * close current db
     */
    public function closedb() {
        $this->db->close();
    }

    /**
     * Execute a query
     *
     * @param string $sql query
     * @return bool|PDOStatement
     */
    public function executeQuery($sql) {
        $res = $this->db->query($sql);

        $this->data = null;

        if(!$res) {
            $err = $this->db->errorInfo();
            msg($err[0].' '.$err[1].' '.$err[2].':<br /><pre>'.hsc($sql).'</pre>', -1);
            return false;
        }

        return $res;
    }

    /**
     * Close the result set and it's cursors
     *
     * @param PDOStatement $res
     * @return bool
     */
    public function res_close($res) {
        return $res->closeCursor();
    }

    /**
     * Returns a complete result set as array
     */
    public function res2arr($res, $assoc = true) {
        if(!$res) return array();

        if(!$this->data) {
            $mode       = $assoc ? PDO::FETCH_ASSOC : PDO::FETCH_NUM;
            $this->data = $res->fetchAll($mode);
        }
        return $this->data;
    }

    /**
     * Return the next row of the given result set as associative array
     */
    public function res2row($res) {
        if(!$res) return false;

        return $res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Return the first value from the next row.
     */
    public function res2single($res) {
        if(!$res) return false;

        $data = $res->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_ABS, 0);
        if(!count($data)) {
            return false;
        }
        return $data[0];
    }

    /**
     * Run sqlite_escape_string() on the given string and surround it
     * with quotes
     */
    public function quote_string($string) {
        return $this->db->quote($string);
    }

    /**
     * Escape string for sql
     */
    public function escape_string($str) {
        return trim($this->db->quote($str), "'");
    }

    /**
     * Aggregation function for SQLite via PDO
     *
     * @link http://devzone.zend.com/article/863-SQLite-Lean-Mean-DB-Machine
     */
    public function _pdo_group_concat_step(&$context, $rownumber, $string, $separator = ',') {
        if(is_null($context)) {
            $context = array(
                'sep'  => $separator,
                'data' => array()
            );
        }

        $context['data'][] = $string;
        return $context;
    }

    /**
     * Aggregation function for SQLite via PDO
     *
     * @link http://devzone.zend.com/article/863-SQLite-Lean-Mean-DB-Machine
     */
    public function _pdo_group_concat_finalize(&$context, $rownumber) {
        if(!is_array($context)) {
            return null;
        }
        $context['data'] = array_unique($context['data']);
        return join($context['sep'], $context['data']);
    }

    /**
     * fetch the next row as zero indexed array
     */
    public function res_fetch_array($res) {
        if(!$res) return false;

        return $res->fetch(PDO::FETCH_NUM);
    }

    /**
     * fetch the next row as assocative array
     */
    public function res_fetch_assoc($res) {
        if(!$res) return false;

        return $res->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Count the number of records in result
     *
     * This function is really inperformant in PDO and should be avoided!
     */
    public function res2count($res) {
        if(!$res) return 0;

        if(!$this->data) {
            $this->data = $this->res2arr($res);
        }

        return count($this->data);
    }

    /**
     * Count the number of records changed last time
     *
     * Don't work after a SELECT statement
     */
    public function countChanges($res) {
        if(!$res) return 0;

        return $res->rowCount();
    }
}

// vim:ts=4:sw=4:et:enc=utf-8:
