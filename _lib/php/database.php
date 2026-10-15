<?php
/*
 Database class for managing connections.
 
 implement as Singleton just to get the hang.
 
*/

class DB {
    
    private static $m_pInstance;
    private static $m_link;
    public $last_error;
    public $last_result;
    public $last_query;
    
    private function DB()
    {
        // do mysql connection stuff.
        // store link.
        
    }
    
    public static function getInstance() 
    { 
        if (!self::$m_pInstance) 
        { 
            self::$m_pInstance = new DB(); 
        }
        
        if (!self::$m_link)
        {
            self::$m_link = self::connect();
        }
    
        return self::$m_pInstance; 
    }  
    
    public static function connect($db = 'therealjimwilliams')
    {
        
        // Live credentials live in db_config.php next to this file, which git
        // ignores (copy db_config.example.php). Without it, connect to the
        // local dev DB as root with no password.
        $config = __DIR__.'/db_config.php';
        if (file_exists($config)){
            require($config);
            $m_link = mysqli_connect(DB_HOST, DB_USER, DB_PASS);
        }
        else{
            $m_link = mysqli_connect('127.0.0.1', 'root', '');
        }
        
        if (!$m_link){
            echo "could not conect to database";
        }
        else{
           // echo "connected";
        }
        
        mysqli_select_db($m_link, $db);
        return $m_link;
    }
    
    public function escape($s)
    {
        return mysqli_real_escape_string(self::$m_link, (string)$s);
    }

    public function insert($i)
    {
        $sql = "INSERT INTO ".$i['table']." ";
        
        $keys = array();
        
        $vals = array();
        
        foreach($i['keyvals'] as $key=>$val){
            
            $keys[] = $key;
            
            $vals[] = "'".$this->escape($val)."'";
            
        }

        $keys = implode(",", $keys);

        
        $vals = implode(",", $vals);
        
        $sql .= "(".$keys.") VALUES (".$vals.")";
        $result = mysqli_query(self::$m_link, $sql);
        
        return $result;
    }
    
    
    public function insert_id()
    {
        return mysqli_insert_id(self::$m_link);
    }

    public function query($sql)
    {
        
        $return = array();
        
        $result = mysqli_query(self::$m_link, $sql);

        $this->last_query = $sql;
        
        if (mysqli_error(self::$m_link)){
            
            $this->last_error=mysqli_error(self::$m_link);
            
        }
        
        else{
            $this->last_error="";
            if(!is_bool($result)){
                while ($row = mysqli_fetch_object($result)){
                    
                    $return[]=$row;
                    
                }
            }
            // non-SELECT (bool result): return empty array so count() downstream is always safe
            
        }
        
        return $return;
    }
}
?>