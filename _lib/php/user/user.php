<?php

require_once(BASEPATH."/_lib/includer.php");

class user{
    
    protected $db;
    protected $attributes;
    protected $id;
    protected $signature;
    protected $location_id;
    public $crypt;
    
    public function __construct(){
        define("USERPATH",BASEPATH."users/");
        $this->db = _db();
    }
    
    public function set($id){
        $sql = "SELECT * FROM users WHERE id='$id'";
        $user = $this->db->query($sql);
        if (count($user)==0)
            return false;
        $user=$user[0];
        $this->id = $id;
        $this->attributes = json_decode($user->attributes);
        
        $sql = "SELECT location_id from user_locations WHERE user_id='$id'";
        $location = $this->db->query($sql);
        if (count($location)>0){
            $this->location_id = $location[0]->location_id;
        }
    }
    
    public function save_attributes(){
        $attr = json_encode($this->attributes);
        $sql = "UPDATE users SET attributes='$attr' WHERE id='".$this->id."'";
        $this->db->query($sql);
    }
    public function doLogin($u,$p,$s,$d='therealjimwilliams.com'){

        if(!$this->checkpass($u,$p)){
            $result = array("result"=>"fail", "message"=>"PASSWORD INCORRECT");
        }
        else{
            $result = $this->login_user($u,$s,$d);
        }
        
        return $result;
    }
    
    public function create_user($u,$p){
        $created = $this->db->insert(array('table'=>'users','keyvals'=>array("username"=>$u, "password"=>md5($p))));
        if(true==$created)
        {
            $sql = "SELECT id FROM users WHERE username='$u'";
            $result = $this->db->query($sql);
            $user_id = $result[0]->id;
            $this->initialize_character($user_id);
            $this->initialize_user($user_id);
        };
        return $created;
    }
    
    public function initialize_character($user_id)
    {
        $attr = '{"status":"ready","position":"standing","piss":0,"shit":0,"hunger":0"thirst":0,"health":100,"tough":100,"smart":100,"strong":100,"coordinated":100,"height":190,"weight":105,"quick":100,"endurance":100,"fatigue":0,"belly":0,"seat":"0"}';
        $sql = "UPDATE users SET attributes='$attr' WHERE id='".$user_id."'";
        diebug($sql);
        $this->db->query($sql);
    }
    
    public function initialize_user($user_id)
    {
        $template = file_get_contents(BASEPATH."nativex/files/nativex.phpx");
        $username = $this->get_username($user_id);
        $template = str_replace("[[username]]",$username,$template);
        if(!file_exists(BASEPATH."_lib/php/nativex/users/".$username))
        {
            mkdir(BASEPATH."_lib/php/nativex/users/".$username);
        }
        if(!file_exists(BASEPATH."_lib/php/nativex/users/".$username."/scrypts")){
            mkdir(BASEPATH."_lib/php/nativex/users/".$username."/scrypts");
        }
        $fh = fopen(BASEPATH."_lib/php/nativex/users/$username/".$username."_nativex.php","w");
        fwrite($fh,$template);
        fclose($fh);

    }
    
    public function user_exists($u){
        $sql = "SELECT * from users where username='$u'";
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
        $sql = "SELECT * FROM users WHERE username='$u' AND password='".md5(strtoupper($p))."';";
        $result = $this->db->query($sql);
        if (count($result))
            return true;
        else
            return false;
    }
    
    public function login_user($u,$s,$d){

        $sql = "SELECT id FROM users WHERE username='$u'";
        $result = $this->db->query($sql);
        $id = $result[0]->id;
        //force logout of any current logins for same user.
        $sql = "DELETE FROM live_sessions WHERE user_id='$id' AND domain='$d'";
        $result = $this->db->query($sql);
        $this->db->insert(array('table'=>"live_sessions", 'keyvals'=>array('user_id'=>$id, "session_id"=>$s,'domain'=>$d)));
       
        $sql = "SELECT username FROM users WHERE id='$id'";
        $username = $this->db->query($sql);
        $username = $username[0]->username;
        
        
        if($d=="cryptstack.com")
        {
            global $docroot;
            include($docroot."/_lib/php/nativex/users/$u/".$u."_nativex.php");
            $class = $u."_NativeX";
            $this->crypt = new $class();
            $_SESSION['crypt'] = $this->crypt;
        }        
        $result = array('result'=>"success",'message'=>'LOGIN SUCCESSFUL', 'id'=>$id, 'username'=>$username,'domain'=>$d);
        
        return $result;
    }
    
    public function session_logged_in($domain=null){
    
        
        if (isset($_SESSION['username']) && isset($_SESSION['domain'])){

            return true;
        }
        else
            return false;
    }
    
    public function get_logged_in_userinfo(){
        return array('userid'=>$_SESSION['userid'], 'username'=>$_SESSION['username']);
    }
    
    public function get_logged_in_user_id(){
        return $_SESSION['userid'];
    }
    
    public function get_logged_in_username(){
        return $_SESSION['username'];
    }
    
    public function get_location($user_id){
        
        $sql = "SELECT * FROM locations JOIN user_locations ON locations.id = user_locations.location_id WHERE user_locations.user_id='".$user_id."'";
        $location_data = $this->db->query($sql);
        return $location_data[0];
        
    }
    
    public function get_username($user_id){
        $sql = "SELECT username FROM users WHERE id='".$user_id."'";
        $username = $this->db->query($sql);
        return $username[0]->username;
    }
    
    public function set_location($user_id,$location_id){
        $sql = "DELETE FROM user_locations WHERE user_id = '".$user_id."'";
        $result = $this->db->query($sql);
        $sql = "INSERT INTO user_locations (user_id, location_id) VALUES ('".$user_id."','".$location_id."')";
        $result = $this->db->query($sql);
        return;
    }
    
    public function get_users_in_location($location_id, $current_user){
        $sql = "SELECT user_id FROM user_locations WHERE location_id = '".$location_id."' AND user_id !='".$current_user."'";
        $users = $this->db->query($sql);
        return $users;
    }
    
    public function get_user_items($type=null){

        if ($type==null){
            $sql = "SELECT item_id FROM item_users WHERE user_id='".$this->id."'";
            $items = $this->db->query($sql);
            return $items;
        }
        
        else{
            _items();
            $item = new item();
            $type_id = $item->get_type_by_type_name($type);
            $types = $item->get_type_hierarchy($type_id);
            $types_in = implode(",", $types);
            $sql = "SELECT item_id FROM item_users JOIN items ON items.id = item_users.item_id WHERE item_users.user_id='".$this->id."' AND items.type_id IN($types_in)";
            $user_items = $this->db->query($sql);
            
            return $user_items;
        }
    }
    
    public function get_attributes(){
        return $this->attributes;
    }
    
    public function get_attribute($attr){
        return $this->attributes->$attr;
    }
    
    public function set_attribute($attr,$val){
        $this->attributes->$attr=$val;
    }
    
    public function update_attributes(){
        $attributes = json_encode($this->attributes);
        $sql = "UPDATE users SET attributes='$attributes' WHERE id='".$this->id."'";
        $this->db->query($sql);
        return;
    }
    
    public function add_attribute($name,$parent){
        
    }
    
    public function get_location_id(){
        return $this->location_id;
    }
    
    public function get_id(){
        return $this->id;
    }
    
    public function process_user_status(){
        $hunger=$this->get_attribute("hunger");
        $shit=$this->get_attribute("shit");
        $belly=$this->get_attribute("belly");
        
        $time=time();
        
        if (!isset($_SESSION['last_status'])){
            $_SESSION['last_status']=time();
        }
        $time_passed = $time-$_SESSION['last_status'];
        //uptick hunger
        if ($time_passed>30){

            $hunger++;
            $_SESSION['last_status']=$time;
        }
        
        if ($time_passed>30){
            $shit += round(.15*$belly);
            $belly = round(.8*$belly);
            $_SESSION['last_status']=$time;
        }
        

        $this->set_attribute("hunger",$hunger);
        $this->set_attribute("shit",$shit);
        $this->set_attribute("belly",$belly);
        $this->update_attributes();
        
    }
    
    
    public function get_logged_in_users($domain)
    {
        $sql = "SELECT users.* from users join live_sessions on users.id = live_sessions.user_id WHERE domain='$domain'";
        $result = $this->db->query($sql);
        return $result;
    }
    
    public function get_scrypts($username,$type="php")
    {
        $path=USERPATH.$username."/scrypts/".$type."/";
        $dh = opendir($path);
        $scrypts = array();
        while($entry=readdir($dh))
        {
            preg_match("/(.*)\.jsx/",$entry,$matches);
            
            if(count($matches))
            {
                if(count($matches))
                {
                    $scrypt = new stdClass();
                    $scrypt->name = $matches[1];
                    $scrypt->scrypt = trim(file_get_contents($path.$matches[0]));
                    $scrypts[] = $scrypt;
                }
            }
        }
        return $scrypts;
    }
}

?>