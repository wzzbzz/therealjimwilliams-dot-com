<?php
/*
 Database class for managing connections.
 
 implement at Singleton just to get the hang.
 
*/

class DB {
    
    private static $m_pInstance;
    private static $m_link;
    private static $last_error;
    
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
    
    public function connect()
    {
        $m_link = mysql_connect('localhost', 'root', '');
        
        if (!$m_link){
            echo "could not conect to database";
        }
        else{
           // echo "connected";
        }
        
        mysql_selectdb('therealjimwilliams');

        return $link;
    }
    
    public function insert($i)
    {
        $sql = "INSERT INTO ".$i['table']." ";
        
        $keys = array();
        
        $vals = array();
        
        foreach($i['keyvals'] as $key=>$val){
            
            $keys[] = $key;
            
            $vals[] = "'".$val."'";
            
        }

        $keys = implode(",", $keys);

        
        $vals = implode(",", $vals);
        
        $sql .= "(".$keys.") VALUES (".$vals.")";
        

        $result = mysql_query($sql);
        
        return $result;
    }
    
    
    public function query($sql)
    {
        $return = array();
        
        $result = mysql_query($sql);
        
        if (mysql_error()){
            
            echo mysql_error();
            
        }
        
        else{
            
            if(!is_bool($result)){
                while ($row = mysql_fetch_object($result)){
                    
                    $return[]=$row;
                    
                }
            }
            else{
                $return = $result;
            }
            
        }
        
        return $return;
    }
}
?>