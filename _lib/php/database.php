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
    
    public function connect($db = 'therealjimwilliams')
    {
        
        switch($_SERVER['HTTP_HOST']){
            case 'local.therealjimwilliams.com':
                $m_link = mysqli_connect('127.0.0.1', 'root', 'vagrant');
                
                break;
            default:
                $m_link = mysqli_connect('mysql.therealjimwilliams.com', 'theduke', 'atriedes');
                break;
        }
        
        
        if (!$m_link){
            echo "could not conect to database";
        }
        else{
           // echo "connected";
        }
        
        mysqli_select_db($db);
        return $m_link;
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
        $result = mysqli_query($sql);
        
        return $result;
    }
    
    
    public function query($sql)
    {
        
        $return = array();
        
        $result = mysqli_query($sql);

        $this->last_query = $sql;
        
        if (mysqli_error()){
            
            $this->last_error=mysqli_error();
            
        }
        
        else{
            $this->last_error="";
            if(!is_bool($result)){
                while ($row = mysqli_fetch_object($result)){
                    
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