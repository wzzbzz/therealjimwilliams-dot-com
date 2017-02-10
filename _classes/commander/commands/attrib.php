<?php
/*
 *  command - attrib
 *
 *  list user's attributes
 *  
 */

require_once("_lib/php/user/user.php");

class attrib extends command{
    
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
        $attributes = $user->get_attributes();
        
        $print_attribs= "<ul>";
        foreach($attributes as $attrib=>$value)
        {
            $print_attribs .="<li>$attrib : $value</li>";
        }
        $print_attribs .= "</ul>";

        $result = array();
        $result['action'] = "OUTPUT";

        $output = "<div>".$print_attribs."</div>";
        $result['data'] = $output;

        return $result;
    }
}