<?php
/*
 *  command - inventory
 *
 *  get a list of all items in user's posession
 *  
 */

require_once("_lib/php/user/user.php");

class inventory extends command{
    
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
        $items = $user->get_user_items();

        if (count($items)==0){
            $print_items = "You're empty handed.";
        }
        
        else{
            _items();
            $itemsObj = new items();
    
            $print_items ="<div>You are carrying:</div>";
            foreach($items as $item){
                $description = $itemsObj->get_item_description($item->item_id);
                $print_items .= "<div>$description</div>";
            }

        }

        $result = array();
        $result['action'] = "OUTPUT";

        $output = "<div>".$print_items."</div>";
        $result['data'] = $output;

        return $result;
    }
}
?>