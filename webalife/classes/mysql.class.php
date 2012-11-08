<?php
/*
MySQL PHP Class (c) 2004
*/
class mysql
{
    var $RecordSet;
    var $Active = NULL;
    var $Site = NULL;
    var $HBS = NULL;
    var $strSQL = NULL;

    function mysql()
    {
        $this->Reset();
    }

    function connect()
    {
        $this->Site = @mysql_connect(DB_HOST, DB_USERNAME, DB_USERPWRD);
        if (!$this->Site) {
            throw new Exception("Can't connect to database", 1040);
        }
        if (!mysql_select_db(DB_NAME, $this->Site)) {
            throw new Exception("Can't select " . DB_NAME . " database", 1050);
        }
        $this->Active = $this->Site;
        mysql_query("SET names 'utf8'");
    }

    function close($link_id = NULL)
    {
        if (!$link_id) $link_id = $this->Active;
        mysql_close($link_id);
    }

    function query($sql_query)
    {
        $r = @mysql_query($sql_query, $this->Active);
        return $r;
    }

    function convert($data)
    {
        $rows = @mysql_num_rows($data);
        $i = 0;
        while ($i < $rows) {
            $r[$i] = @mysql_fetch_array($data);
            $i++;
        }
        return $r;
    }

    function rows($data)
    {
        $r = @mysql_num_rows($data);
        return $r;
    }

    /* Reset all statuses */
    function Reset()
    {
        unset($this->RecordSet, $this->Active);
    }

    /* Simple query */
    function SimpleQuery($query = "", $fetch = false, $assoc = false)
    {
        if (eregi("select|update|delete|drop|truncate|alter|insert", strtolower($query))) { // If query is valid
            if (eregi("union", strtolower($query))) return false;
            $this->RecordSet = @mysql_query($query, $this->Active);
            print mysql_error();
            if ($fetch) { // Fetching
                return $this->Fetch($assoc);
            }
            return true;
        } else {
            return "Error: Query is not valid!";
        }
    }

    /* SQL query */
    function sql($query = "", $mode = 0)
    {
        $this->strSQL = $query;
        if (eregi("union", strtolower($query))) return false;
        if (eregi("select|update|delete|drop|truncate|alter|insert|show|optimize|replace", strtolower($query))) { // If query is valid
            $this->RecordSet = mysql_query($query, $this->Active);
            if (mysql_error()) echo '<p>SQL: ' . $this->getsql() . '</p>';
            if ($mode > 0) { // Fetching
                return $this->Fetch($mode);
            }
            return true;
        } else {
            echo "Error: Query is not valid!";
        }
    }

    /* Fetching result into array */
    function Fetch($mode = 1)
    {
        $rcrd = array();
        if ($mode == 1) {
            $r = mysql_fetch_array($this->RecordSet);
            if (!is_array($r)) return array();
            foreach ($r as $key => $value) {
                if (is_numeric($key)) continue;
                $rcrd[strtoupper($key)] = $value;
            }
        } else {
            $rows = @mysql_num_rows($this->RecordSet);
            while ($r = mysql_fetch_array($this->RecordSet)) {
                foreach ($r as $key => $value) {
                    if (is_numeric($key)) continue;
                    $r1[strtoupper($key)] = $value;
                }
                $rcrd[] = $r1;
            }
        }
        return $rcrd;
    }

    /* Get ID of insered data */
    function GetLastID()
    {
        return mysql_insert_id($this->Active);
    }

    /* Execute query */
    function Exec($sql)
    {
        mysql_query($sql, $this->Active);
        print mysql_error();
        return true;
    }

    /* Get string of sql query */
    function GetSQL()
    {
        return '<br/>' . $this->strSQL . '<br/>';
    }

    /* Read dump file */
    function DumpExec($sql_query)
    {
        $pieces = $this->_split_sql($sql_query);
        if (count($pieces) == 1 && !empty($pieces[0])) {
            $sql_query = trim($pieces[0]);
        }
        for ($i = 0; $i < count($pieces); $i++) {
            $pieces[$i] = (trim($pieces[$i]));
            if (!empty($pieces[$i]) && $pieces[$i] != "#") {
                $rst = mysql_query($pieces[$i], $this->Active);
                if (!$rst) {
                    echo mysql_error();
                    return false;
                }
            }
        }
        return true;
    }

    function _split_sql($sql)
    {
        $sql = trim($sql);
        $sql = str_replace("&#150;", "-", $sql);
        $sql = ereg_replace("#[^\n]*\n", "", $sql);
        $buffer = array();
        $ret = array();
        $in_string = false;

        for ($i = 0; $i < strlen($sql) - 1; $i++) {
            if ($sql[$i] == ";" && !$in_string) {
                $ret[] = substr($sql, 0, $i);
                $sql = substr($sql, $i + 1);
                $i = 0;
            }

            if ($in_string && ($sql[$i] == $in_string) && $buffer[0] != "\\") {
                $in_string = false;
            } elseif (!$in_string && ($sql[$i] == "\"" || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
                $in_string = $sql[$i];
            }
            if (isset($buffer[1])) {
                $buffer[0] = $buffer[1];
            }
            $buffer[1] = $sql[$i];
        }

        if (!empty($sql)) {
            $ret[] = $sql;
        }

        return ($ret);
    }

}

?>