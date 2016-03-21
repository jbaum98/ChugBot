<?php
    include_once 'functions.php';

    class DbConn {
        function __construct() {
            $this->mysqli = connect_db();
        }
    
        function __destruct() {
            $this->mysqli->close();
        }
    
        // Add a column value, and its type.  Possible types are:
        // i => integer, d => double, s => string, b => blob
        public function addColumn($col, $val, $type) {
            array_push($this->colNames, $col);
            array_push($this->colVals, $val);
            $this->colTypes .= $type;
        }
        
        // The next three functions are variations on addColumn.  This one
        // is for SELECT clauses, where only the column name is needed.
        public function addSelectColumn($col) {
            array_push($this->colNames, $col);
        }
        
        // This function is for columns in raw queries, where we only need the
        // column value and type.
        public function addColVal($val, $type) {
            array_push($this->colVals, $val);
            $this->colTypes .= $type;
        }
        
        // This is similar to addColumn, except the column appears in a WHERE
        // clause.
        public function addWhereColumn($col, $val, $type) {
            array_push($this->whereColNames, $col);
            array_push($this->colVals, $val);
            $this->colTypes .= $type;
        }
        
        private function buildWhereClause() {
            $this->whereClause = "WHERE ";
            for ($i = 0; $i < count($this->whereColNames); $i++) {
                $colName = $this->whereColNames[$i];
                $this->whereClause .= "$colName = ? ";
                if ($i < (count($this->whereColNames)-1)) {
                    $this->whereClause .= "AND ";
                }
            }
        }
        
        // Do a simple SELECT foo, bar FROM baz WHERE blurfl query.  More
        // complicated SELECTs must be done with doQuery.
        // TODO: Add support for any kind of SELECT.
        public function simpleSelectFromTable($table, &$err) {
            $this->isSelect = TRUE;
            $selCols = "";
            for ($i = 0; $i < count($this->colNames); $i++) {
                $colName = $this->colNames[$i];
                $selCols .= "$colName";
                if ($i < (count($this->colNames)-1)) {
                    $selCols .= ",";
                }
            }
            
            $this->buildWhereClause();
            return $this->doQuery("SELECT $selCols FROM $table $this->whereClause", $err);
        }
        
        public function deleteFromTable($table, &$err) {
            $this->buildWhereClause();
            return $this->doQuery("DELETE FROM $table $this->whereClause", $err);
        }
        
        public function updateTable($table, &$err) {
            $setClause = "SET ";
            for ($i = 0; $i < count($this->colNames); $i++) {
                $colName = $this->colNames[$i];
                $setClause .= "$colName = ?";
                if ($i < (count($this->colNames)-1)) {
                    $setClause .= ",";
                }
            }
            $this->buildWhereClause();
            
            return $this->doQuery("UPDATE $table $setClause $this->whereClause", $err);
        }
        
        public function insertIntoTable($table, &$err) {
            $qmCsv = "";
            $colCsv = "";
            foreach ($this->colNames as $colName) {
                // Build the column and parameter strings.
                if (empty($qmCsv)) {
                    $qmCsv = "?";
                } else {
                    $qmCsv .= ", ?";
                }
                if (empty($colCsv)) {
                    $colCsv = $colName;
                } else {
                    $colCsv .= ", $colName";
                }
            }
            
            $insertOk = $this->doQuery("INSERT INTO $table ($colCsv) VALUES ($qmCsv)",
                                       $err);
            $this->insert_id = $this->mysqli->insert_id;

            return $insertOk;
        }
    
        public function doQuery($paramSql, &$err) {
            $this->stmt = $this->mysqli->prepare($paramSql);
            if ($this->stmt == FALSE) {
                $err = dbErrorString("Failed to prepare $paramSql", $this->mysqli->error);
                error_log($err);
                return FALSE;
            }
            if ($this->colTypes) {
                $paramsByRef[] = &$this->colTypes;
                for ($i = 0; $i < count($this->colVals); $i++) {
                    $paramsByRef[] = &$this->colVals[$i];
                }
                $bindOk = call_user_func_array(array(&$this->stmt, 'bind_param'), $paramsByRef);
                if ($bindOk == FALSE) {
                    $err = dbErrorString("Failed to bind $paramSql", $this->mysqli->error);
                    error_log($err);
                    $this->stmt->close();
                    return FALSE;
                }
            }
            $exOk = $this->stmt->execute();
            if ($exOk == FALSE) {
                $err = dbErrorString("Failed to execute $paramSql", $this->mysqli->error);
                error_log($err);
                $this->stmt->close();
                return FALSE;
            }
            $retVal = TRUE;
            if ($this->isSelect) {
                $retVal = $this->stmt->get_result();
            }
            $this->stmt->close();
            
            return $retVal;
        }
        
        public function insertId() {
            return $this->insert_id;
        }
        
        private $insert_id;
        private $mysqli;
        private $stmt;
        private $colNames = array();
        private $whereColNames = array();
        private $colVals = array();
        private $colTypes = "";
        private $whereClause = "";
        public $isSelect = FALSE;
    }
    
    // Utility function
    function fillId2Name(&$id2Name, &$dbErr,
                         $idColumn, $table, $secondIdColumn = NULL,
                         $secondTable = NULL) {
        $db = new DbConn();
        $db->isSelect = TRUE;
        $db->addSelectColumn($idColumn);
        if ($secondIdColumn) {
            $db->addSelectColumn($secondIdColumn);
        }
        $db->addSelectColumn("name");
        $result = $db->simpleSelectFromTable($table, $dbErr);
        if ($result == FALSE) {
            error_log($dbErr);
            return;
        }
        $secondId2Name = array();
        if ($secondTable) {
            $db = new DbConn();
            $db->isSelect = TRUE;
            $db->addSelectColumn($secondIdColumn);
            $db->addSelectColumn("name");
            $result2 = $db->simpleSelectFromTable($secondTable, $dbErr);
            if ($result2 == FALSE) {
                error_log($dbErr);
                return;
            }
            while ($row = $result2->fetch_array(MYSQLI_NUM)) {
                $secondId2Name[$row[0]] = $row[1];
            }
        }
        while ($row = $result->fetch_array(MYSQLI_NUM)) {
            if ($secondIdColumn) {
                $id2Name[$row[0]] = $row[2] . " - " . $secondId2Name[$row[1]];
            } else {
                $id2Name[$row[0]] = $row[1];
            }
        }
    }
    
    
