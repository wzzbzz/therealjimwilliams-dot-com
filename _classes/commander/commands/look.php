<?php
/*
 *  command - look
 *
 *  get a description of the user's current location
 *  
 */

require_once("_lib/php/user/user.php");

class look extends command{
    
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
        $location_data = $user->get_location($user_id);
        
        if (count($location_data)==0){
            //user is nowhere.  Move to limbo.
            $user->set_location($user_id, 1);
            $location_data = $user->get_location($user_id);
        }
        $location = _location();
        $location->set($user->get_location_id());
        $lit = $location->get_lighting();

        if (!($lit == "lit")){
            $result = array();
            $result['action']="OUTPUT";
            $result['data']="It's dark.";
            return $result;
        }
        $users = $user->get_users_in_location($location_data->id, $user_id);
        
        if (count($users)==0){
            $people = "You are alone here.";
        }
        
        elseif (count($users)==1){
            $username = $user->get_username($users[0]->user_id);
            $people= "$username is here.";
        }
        elseif (count($users)==2){
            $people = $user->get_username($users[0]->user_id);
            $people .= " and ";
            $people .= $user->get_username($users[1]->user_id);
            $people .= " are here.";
        }
        else{
            $people = "";
            $count = 1;
            foreach($users as $the_user){
                $username = $user->get_username($the_user->user_id);
                $people .= $username;
                if ($count == count($users)-1){
                    $people .= " and ";
                }
                elseif ($count==count($users)){
                    
                }
                else{
                    $people .= ", ";
                }
                $count++;
            }
            $people .= " are here";
        }
        
        _items();
        $itemsObj = new items();
        $items = $itemsObj->get_items_in_location($location_data->id);

        if (!$items){
            $print_items="";
        }
        else{
            foreach($items as $item){
                $itemObj = new item();
                $theItem = $itemObj->get_item($item->item_id);
                $title = $theItem->title;
                $print_items .= "<div>You see ".$title."</div>";
            }
        }
        
        $location = _location();
        $location->set($user->get_location_id());
        $connections = $location->get_connections();
        $directions = array("N"=>"North","E"=>"East","S"=>"South","W"=>"West");
        $exits = "<div>Exits:</div>";
        if (!$connections){
            $exits.="<div>There is no way out.</div>";
        }
        else{
            foreach($connections as $connection){
                if ($door = $connection->get_restriction("door")){
                    $exits .= "<div>A door leading ".$directions[strtoupper($connection->get_direction())]."</div>";
                }
                else{
                    $newloc = new Location();
                    $newloc->set($connection->get_dest());
                    $exits .= "<div>".$newloc->get_title()." to the ".$directions[strtoupper($connection->get_direction())]."</div>";
                }
            }
        }
        $result = array();
        $result['action'] = "OUTPUT";
        $output = "<div style='color:red'><b>".$location_data->title."</b></div>";
        $output .= "<div>".$location_data->long_desc."</div>";
        $output .= "<div>".$people."</div>";
        $output .= "<div>".$print_items."</div>";
        $output .="<div>".$exits."</div>";
        $result['data'] = $output;
        

        return $result;
    }
}
?>