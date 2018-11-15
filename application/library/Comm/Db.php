<?php
function db_error_log($error) {
    Comm_Log::fileLog($error,E_USER_ERROR);
}
function db_debug_sql($sql){
    $reg= array("\r\n", "\n", "\r");
    $replace =" ";
    $sql =  str_replace($reg, $replace, $sql);
    Comm_Log::fileLog($sql);
}

class Comm_Db extends mysqli {
    private static $_dbReadArr = array();
    private static $_dbWriteArr = array();
    public $_isMaster; // connection instance is master or not 0:slve 1:master
    public function __construct($dbHost, $dbPort, $dbUser, $dbPass, $dbName, $master = 0) {
        parent::__construct($dbHost, $dbUser, $dbPass, $dbName, $dbPort);
        $this->_isMaster = $master;
        if (mysqli_connect_error()) {
            die('Connect Error (' . mysqli_connect_errno() . ') ' . mysqli_connect_error());
        }
        $this->query("SET NAMES UTF8");
    
    }

    public static final function getDbRead($db) {
        $dbConfig = Comm_Config::getConf('config.'.DEVELOPMENT.'.'.$db);
        if (!self::$_dbReadArr[$dbConfig['NAME_R']]) {
            $dbHost = $dbConfig['HOST_R'];
            $dbPort = $dbConfig['PORT_R'];
            $dbUser = $dbConfig['USER_R'];
            $dbPass = $dbConfig['PASS_R'];
            $dbName = $dbConfig['NAME_R'];
            self::$_dbReadArr[$dbConfig['NAME_R']] = new self($dbHost, $dbPort, $dbUser, $dbPass, $dbName);
        }
        return self::$_dbReadArr[$dbConfig['NAME_R']];
    }

    // get the connection to master dbs
    public static final function getDbWrite($db) {
        $dbConfig = Comm_Config::getConf('config.'.DEVELOPMENT.'.'.$db);
        if (!self::$_dbWriteArr[$dbConfig['NAME']]) {
            $dbHost = $dbConfig['HOST'];
            $dbPort = $dbConfig['PORT'];
            $dbUser = $dbConfig['USER'];
            $dbPass = $dbConfig['PASS'];
            $dbName = $dbConfig['NAME'];
            self::$_dbWriteArr[$dbConfig['NAME']] = new self($dbHost, $dbPort, $dbUser, $dbPass, $dbName, 1);
        }
        return self::$_dbWriteArr[$dbConfig['NAME']];
    }
    
    /*
    public function query($sql) {
        $e = parent::query($sql);
        if (!$e) {
            db_error_log("SQL: \"" . $sql . "\" Occured DB Error: \"" . $this->error . "\"");
        }
        return $e;
    }
    */

    public function query($sql) {
        $e = parent::query($sql);
        db_debug_sql($sql);
        if (!$e) {
            db_error_log("SQL: \"" . $sql . "\" Occured DB Error: \"" . $this->error . "\"");
        }
        return $e;
    }

    public function getAll($sql){
        $ret = array();
        $obj = $this->query($sql);
        if(is_object($obj)){
            while($row = $obj->fetch_assoc()){
                $ret[] = $row;
            }
        }
        return $ret;
    }

    public function getRow($sql){
        $ret = array();
        $obj = $this->query($sql);
        if(is_object($obj)){
            $ret = $obj->fetch_assoc();
        }
        return $ret;
    }

    public function getOne($sql){
        $ret = false;
        $obj = $this->query($sql);
        if(is_object($obj)){
            $ret = $obj->fetch_row();
        }
        return $ret[0] ? $ret[0] : 0;
    }

    public function getCount($sql){
        $ret = 0;
        $obj = $this->query($sql);
        if(is_object($obj)){
            $ret = $obj->num_rows;
        }
        return $ret;
    }
    
    public function multi_query($sqlArray) {
        if (!is_array($sqlArray))
            return false;
        $this->autocommit(false);
        $result = true;
        foreach ($sqlArray as $sql) {
            if (!$this->query($sql)) {
                $this->rollback();
                $result = false;
                break;
            }
        }
        $result && $this->commit();
        $this->autocommit(true);
        return $result;
    }
    
    public function htmlspecialchars($data) {
        if (is_array($data)) {
            foreach ($data as $key => $val) {
                $data[$key] = self::htmlspecialchars($val);
            }
            return $data;
        }
        return htmlspecialchars($data);
    }
    
    public function escape_str($str,$like=false){
        if (is_array($str)){
            foreach ($str as $key => $val){
                $str[$key] = $this->escape_str($val);
            }
            return $str;
        }
        $str = $this->real_escape_string($str);
		// escape LIKE condition wildcards
		if ($like === TRUE)
		{
			$str = str_replace(array('%', '_'), array('\\%', '\\_'), $str);
		}
		
        return $str;
    }
    
    //自行查看吧,反正都看到这了
    public function update($table, $data, $where, $debug=false){
        if(!is_array($where)){
            $condition = $where;
        }else{
            $condition = "1=1";
            foreach($where as $key=>$value){
                $condition .= " AND ".$key."=".$this->checkInputs($value);
            }
        }
        $set = '';
        foreach($data as $key=>$value){
            $set .= $key ." = ". $this->checkInputs($value).","; 
        }
        $set = trim($set, ',');

        $sql = sprintf("UPDATE %s SET %s WHERE %s", $table, $set, $condition);
        if($debug){
            return $sql;
        }else{
            $msg = $this->query($sql);
            return $msg;
        }
    }
    /**
     * 数据保存，自动过滤SQL注入
     *
     * @param $table 表名称
     * @param $data 待保存的数据
     * @param $debug 调试模式，默认为false正常执行插入数据库操作，true返回sql语句
     */
    public function save($table,$data, $debug=false){
        $data = $this->checkInputs($data);
        $fields = implode(',', array_keys($data));
        $values = implode(',', array_values($data));
        $sql = sprintf("INSERT INTO %s(%s) VALUES(%s)", $table, $fields, $values);
        if($debug){
            return $sql;
        }else{
            $msg = $this->query($sql);
            if ($msg) {
                return $this->insert_id;
            } else {
                return $msg;
            }
            
        }
    }

    /**
     * 批量过滤SQL处理 所有输入必须处理
     * 
     * @param mix $value 需要处理的字符串或者数组
     */ 
    public function checkInputs($value){
        if (is_array($value)){
            foreach ($value as $k => $v){
                $value[$k] = $this->checkInputs($v);
            }
            return $value;
        }
        return $this->checkInput($value);
    }

    /**
     * 过滤SQL注入
     * 
     * $value  $value 需要处理的字符串或者数字
     */
    public function checkInput($value){
        // 去除斜杠
        if (get_magic_quotes_gpc())
        {
            $value = stripslashes($value);
        }
        // 如果不是数字则加引号
        if (is_string($value))
        {
            $value = "'" . $this->real_escape_string($value) . "'";
        } 
        return $value;
    }
}
