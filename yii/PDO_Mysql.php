<?php
/**
 * 使用mysqli模拟PDO 服务器环境居然不支持PDO_mysql -_-||
 * @author xl
 * create on 2014-3-26
 */
class PDO_Mysql{//extends PDO
    
    private $handle = NULL;
    
    private $tmpParams = array();
    
    const MYSQL_ATTR_USE_BUFFERED_QUERY = 1000;
    const MYSQL_ATTR_LOCAL_INFILE       = 1001;
    const MYSQL_ATTR_INIT_COMMAND       = 1002;
    const MYSQL_ATTR_READ_DEFAULT_FILE  = 1003;
    const MYSQL_ATTR_READ_DEFAULT_GROUP = 1004;
    const MYSQL_ATTR_MAX_BUFFER_SIZE    = 1005;
    const MYSQL_ATTR_DIRECT_QUERY       = 1006;

    public function __construct($connectionString,$username,$password,$options=array()){ 
        //简单解析
        preg_match('/host=([\w\.]+);dbname=(\w+)/i', $connectionString,$matches);
        if(count($matches)<3){
            throw new PDOException('connectionString is invalid');
        }
        $this->handle = new mysqli($matches[1],$username,$password,$matches[2]);
        //$options
    }
    
    public function beginTransaction(){
        return $this->handle->autocommit(FALSE);
    }
    
    public function commit(){
        $ret = $this->handle->commit();
        $this->handle->autocommit(TRUE);
        return $ret;
    }
    
    public function rollBack(){
        $ret = $this->handle->rollback();
        $this->handle->autocommit(TRUE);
        return $ret;
    }

    public function errorCode(){
        return $this->handle->errno;
    }
    
    public function errorInfo(){
        return array_values($this->handle->error_list);
    }
    
    public function setAttribute($attribute, $value, &$source = null)
    {
        switch($attribute)
        {
            case PDO::ATTR_AUTOCOMMIT:
                $value = $value ? 1 : 0;
                if(!$this->handle->autocommit($value))
                {
                    throw  new PDOException('set autocommit faild');
                }
                
                return true;
            case PDO::ATTR_TIMEOUT:
                $value = intval($value);
                if($value > 1 && $this->handle->options( MYSQLI_OPT_CONNECT_TIMEOUT, $value))
                {
                    $source[PDO::ATTR_TIMEOUT] = $value;
                    return true;
                }
            break;
            
            case self::MYSQL_ATTR_LOCAL_INFILE:
                $value = $value ? true : false;
                if($this->handle->options(MYSQLI_OPT_LOCAL_INFILE, $value))
                {
                    $source[self::MYSQL_ATTR_LOCAL_INFILE] = $value;
                    return true;
                }
            break;
            
            case self::MYSQL_ATTR_INIT_COMMAND:
                if($value && $this->handle->options( MYSQLI_INIT_COMMAND, $value))
                {
                    $source[self::MYSQL_ATTR_INIT_COMMAND] = $value;
                    return true;
                }
            break;
            
            case self::MYSQL_ATTR_READ_DEFAULT_FILE:
                $value = $value ? true : false;
                if($this->handle->options(MYSQLI_READ_DEFAULT_FILE, $value))
                {
                    $source[self::MYSQL_ATTR_READ_DEFAULT_FILE] = $value;
                    return true;
                }
            break;
            
            case self::MYSQL_ATTR_READ_DEFAULT_GROUP:
                $value = $value ? true : false;
                if($this->handle->options(MYSQLI_READ_DEFAULT_GROUP, $value))
                {
                    $source[self::MYSQL_ATTR_READ_DEFAULT_GROUP] = $value;
                    return true;
                }
            break;    
        }
        
        return false;
    }
    
    public function getAttribute($attribute){
        if(PDO::ATTR_DRIVER_NAME == $attribute){
            return 'mysql';
        }
    }

    public function exec($statement){
        $result = $this->handle->query($statement);
        if(is_object($result)){
            mysqli_free_result($result);
            return 0;
        }
        return $this->handle->affected_rows;
    }


    public static function getAvailableDrivers(){
        return array('mysql');
    }
    
    public function prepare($statement){
        $this->tmpParams = array();
        $newstatement = preg_replace_callback('/(:\w+)/i', function($matches){
            $this->tmpParams[] = $matches[1];
            return '?';
        }, $statement);
        $s = $this->handle->prepare($newstatement);
        if($s==false) {
            throw new PDOException($this->handle->error);
        }
        $ostatement = new PDO_Mysql_Statement($s, $this);
        $ostatement->setPrepareParams($this->tmpParams);
        $ostatement->setStateSql($statement);
        return $ostatement;
    }

    public function lastInsertId(){
        return $this->handle->insert_id;
    }
    
    public function quote($param,$parameter_type=-1){
        switch($parameter_type)
        {
            case PDO::PARAM_BOOL:return $param ? 1 : 0;
            case PDO::PARAM_NULL:return 'NULL'; 
            case PDO::PARAM_INT: return is_null($param) ? 'NULL' : (is_int($param) ? $param : (float)$param); 
            default:return '\'' . $this->handle->real_escape_string($param) . '\'';
        }
    }
    
    public function close(){
        $this->handle->close();
    }
    
    public function disconnect(){
        $this->close();
    }
    
    public function __destruct() {
        $this->close();
    }
}

class PDO_Mysql_Statement {
    
    private $_statement = NULL;
    
    private $_connnection = NULL;
    
    private $_pql = 'unknow';
    
    private $_typeMap = array(
        'i'=>PDO::PARAM_INT,
        's'=>PDO::PARAM_STR,
        'd'=>PDO::PARAM_INT
    );   
   

    private $prepareParams =array();//
    
    private $readyTypes = array();
    
    private $readyValues = array();
    
    private $_result = NULL;
    
    private $_mode = MYSQL_BOTH;

    public function __construct($_statement,$connnection){
        $this->_statement = $_statement;
        $this->_connnection = $connnection;
    }
    
    public function getPdoType($type){
        static $map=array(
                'boolean'=>PDO::PARAM_BOOL,
                'integer'=>PDO::PARAM_INT,
                'string'=>PDO::PARAM_STR,
                'NULL'=>PDO::PARAM_NULL,
        );
        return isset($map[$type]) ? $map[$type] : PDO::PARAM_STR;
    }
    
    public function bindParam($parameter,$value,$type){
        $type = array_search($type, $this->_typeMap);
        $key = array_search($parameter, $this->prepareParams);
        if($key!==false and $type!==false){
            $this->readyTypes[$key] = $type;
            $this->readyValues[$key] = $value;
            return true;
        }else{
            return false;
        }
    }
    //这里bindValue已经失去了本应该有的特性
    public function bindValue($parameter,$value,$type){
        return $this->bindParam($parameter, $value, $type);
    }
    
    public function setStateSql($sql){
        $this->_pql = $sql;
    }


    public function execute($params=array()){
        if(!empty($params)){
            foreach($params as $_k=>$_v){
                $this->bindParam($_k, $_v, $this->getPdoType(gettype($_v)));
            }
        }
        if(!empty($this->readyTypes)){
            $params =$this->readyValues;
            ksort($params);
            array_unshift($params,implode($this->readyTypes));
            $tempstatement = $this->_statement;
            call_user_func_array(array($tempstatement,'bind_param'),$this->refValues($params));
        }
          $this->_statement->execute();        
    }
    
    public function rowCount(){
        return $this->_statement->affected_rows;
    }
    
    public function setFetchMode($mode){
        $mode = $this->transformFetchMode($mode);
        if($mode === false){
            return false;
        }
        $this->_mode = $mode;
        return true;
    }
    
    
    public function closeCursor(){
        //$this->_result = NULL;
        $this->prepareParams =array();
        $this->readyTypes = array();
        $this->readyValues = array();
        $this->_pql = 'unknow';
        $this->_mode = MYSQL_BOTH;
        
        if(!empty($this->_result)){
           $this->_result->free();
        }
        $this->_result = NULL;
       
        //$this->_connnection->close();
       return $this->_statement->reset();
    }
    
    public function columnCount(){
        return $this->_statement->field_count;
    }
    
    public function debugDumpParams(){
        echo $this->_pql;
    }
    
    public function errorCode(){
        return $this->_statement->errno;
    }
    
    public function errorInfo(){
        return array_values($this->_statement->error_list);
    }
    
    public function setPrepareParams($params){
        $this->prepareParams = $params;
    }
    
    public function fetch($mode=NULL){ 
        if($this->_result==NULL){
            $this->_result = $this->_statement->get_result(); 
        }
        if(empty($this->_result)){
            throw new PDOException($this->_statement->error);
        }
       
        $_mode = $this->_mode;
        if(!empty($mode) and ($mode = $this->transformFetchMode($mode))!=false){
            $_mode = $mode;
        }
        $result = $this->_result->fetch_array($_mode);
        return $result === NULL ? false : $result;
    }
    
    public function fetchColumn($column_number=0){
        $column = $this->fetch(PDO::FETCH_NUM);
        return $column[$column_number];
    }
    
    public function fetchAll($mode=NULL){
        if($this->_result==NULL){
            $this->_result = $this->_statement->get_result(); 
        }
        if(empty($this->_result)){
            throw new PDOException($this->_statement->error);
        }
        $_mode = $this->_mode;
        if(!empty($mode) and ($mode = $this->transformFetchMode($mode))!=false){
            $_mode = $mode;
        }
        $result = $this->_result->fetch_all($_mode);
        return $result === NULL ? false : $result;
    }
    
    public function fetchObject(){
        throw new PDOException('Not supported yet');
    }
    
    private function transformFetchMode($mode){
        switch ($mode){
            case PDO::FETCH_ASSOC : return MYSQLI_ASSOC;
            case PDO::FETCH_BOTH  : return MYSQLI_BOTH;
            case PDO::FETCH_NUM   : return MYSQLI_NUM;
            default : return false;
        }        
    }
    
    private function refValues($arr){
        $refs = array();
        foreach($arr as $key => $value){
            if($key!=0){
                $refs[$key] = &$arr[$key];
            }else{
                $refs[$key] = $value;
            }
        }
        return $refs;
    }
    
    public function __destruct(){
       if(!empty($this->_result)) {
           $this->_result->free();
       }
       if(!empty($this->_statement)){
           $this->_statement->close();
       }
    }
    
    
            
}
