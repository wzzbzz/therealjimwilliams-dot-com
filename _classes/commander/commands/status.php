<?php
/*
 *  command - status
 *
 *  gets the user's current status.
 *  
 */

require_once("_lib/php/user/user.php");

class status extends command{
    
    private $user;
    
    public function _execute(){
        
        $user = new user();
        if (!($user->session_logged_in($this->session_id))){
            $result = array();
            $result['action']="OUTPUT";
            $result['data']="LOGIN REQUIRED";
            return $result;
        }
        $user_id = $user->get_logged_in_user_id();
        $user->set($user_id);
        $this->user = $user;
        
        $attributes = $user->get_attributes();
        $status_msg="";
        foreach($attributes as $attribute=>$val){
            if (method_exists($this,"stat_msg_".$attribute)){
                $method = "stat_msg_".$attribute;
                $status_msg .= $this->$method();
                
            }
        }
        
        if ($status_msg==""){
            $status_msg = "Everything's hunky-dory";
        }
        
        $result = array();
        $result['action'] = "OUTPUT";
//        $output = "<div>You check yourself out:</div>";
        $output .= "<div>".$status_msg."</div>";
        $result['data'] = $output;
        

        return $result;
    }
    
    public function stat_msg_shit(){
        $status = $this->user->get_attribute("shit");
        
        if (0<=$status && $status<20){
            return "";
        }
        
        if (20<=$status && $status<40){
            return "You don't have to shit....yet.";
        }
        
        if (40<=$status && $status<60){
            return "you better find a place to pop a squat soon.";
        }
        
        if (60<=$status && $status<80){
            return "You've got to take a shit.  Badly.";
        }
        
        if (80<=$status){
            return "You're incapacitated by the need to shit.";
        }
    }
}
?>