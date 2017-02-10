<?php

require_once(BASEPATH."/_lib/includer.php");

class user{
    private $db;
    
    public function __construct(){
        $this->db = _db();
    }
    
    public function doLogin($u,$p,$s){

        if (!$this->user_exists($u)){
            echo "USER DOESN'T EXIST";
            return;
        }
        elseif(!$this->checkpass($u,$p)){
            echo "PASSWORD INCORRECT";
            return;
        }
        $this->login_user($u,$s);
        $user = new user();
        $user->set($this->get_logged_in_user_id($s));
        $console = $user->get_attribute('console');
        if ($console==null){
            $user->set_attribute("console","apple");
        }
        $_SESSION['console']=$console;
    }
    
    public function create_user($u,$p){
        $result = $this->db->insert(array('table'=>'users','keyvals'=>array("userid"=>$u, "password"=>md5($p))));
    }
    
    public function user_exists($u){
        $sql = "SELECT * from users where userid='$u'";
        $result = $this->db->query($sql);
        if (count($result))
            return true;
        else
            return false;
    }
    
    public function is_logged_in_user($u){
        $sql = "SELECT * from live_sessions WHERE user_id='$u'";
        $user = $this->db->query($sql);
        return (count($user)>0);
    }
    
    public function checkpass($u,$p){
        $sql = "SELECT * FROM users WHERE userid='$u' AND password='".md5($p)."';";
        $result = $this->db->query($sql);
        if (count($result))
            return true;
        else
            return false;
    }
    
    public function login_user($u,$s){

        $sql = "SELECT id FROM users WHERE userid='$u'";
        $result = $this->db->query($sql);
        $id = $result[0]->id;
        $this->db->insert(array('table'=>"live_sessions", 'keyvals'=>array('user_id'=>$id, "session_id"=>$s)));
        
        echo mysql_error();
    }
    
    public function get_user_location($user_id){
        $sql = "SELECT * FROM locations JOIN user_locations WHERE user_locations.user_id='".$user_id."'";
        $location_data = $this->db->query($sql);
        return $location_data[0];
    }
    
    public function get_logged_in_user_id($sess_id){
        $sql = "SELECT id from users JOIN live_sessions ON users.id = live_sessions.user_id WHERE live_sessions.session_id = '".$this->session_id."'";
        $user_id = $this->db->query($sql);
        if (count($user_id)==0){
            return false;
        }
        
        else{
            $user_id = $user_id[0]->id;
            return $user_id;
        }
    }
    
    public function get_user_attributes($user_id)
    {
        $sql = "SELECT attributes from users WHERE user_id='$user_id'";
        debug($this->db->query($sql));
        die;
    }
}
?>