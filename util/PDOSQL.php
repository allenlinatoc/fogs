<?php

/**
 * Static class for PDO-MySQL implementations
 */
final class PDOSQL {
    
    public $query = "";
    public $lastprocess = "";
    public $rows_affected = null;
    
    /**
     * Connects to MySQL via PDO
     * @param String $supplementalpath (Optional) The parent path where this PHP class was called, include /.
     * @return \PDO
     */
    public static function Connect($supplementalpath = '') {
        $pdo_config = parse_ini_file($supplementalpath . 'config/database.ini');
        $dsn = 'mysql:host=' . $pdo_config['host'] . ';dbname=' . $pdo_config['db'] . ';port=' . $pdo_config['port'];
        $pdo = new PDO($dsn, $pdo_config['user'], $pdo_config['password']);
        return $pdo;
    }
    
    public static function __GetDriverInfo() {
        return pdo_drivers();
    }
    
    public function __construct() {
        $this->query = "";
        $this->lastprocess = null;
        $this->rows_affected = null;
    }
    
    public function InsertInto($tablename, $a_fields = array()) {
        $tablename = strtolower($tablename);
        $this->query = 'INSERT INTO ' . $tablename . '(';
        
        for ($x=0; $x<count($a_fields); $x++) {
            $this->query .= $a_fields[$x] . ($x < count($a_fields)-1 ? ',':') ');
        }
        return $this;
    }
    
    public function Values($a_values) {
        $this->query .= 'VALUES(';
        for ($x=0; $x<count($a_values); $x++) {
            $this->query .= $a_values[$x] . ($x < count($a_values)-1 ? ', ': ' );');
        }
        $PDO = self::Connect('../');
        $this->rows_affected = $PDO->exec($this->query);
        return $this;
    }
    
}

?>