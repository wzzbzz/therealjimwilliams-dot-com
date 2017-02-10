<?php

require_once(BASEPATH."/_classes/commander/command.php");

class go extends command{
    
    function _execute($args){
        
        if (count($args)==0){
            $result['action']='OUTPUT';
            $result['data']='Where do you want to go?';
            return $result;
        }
        else{
            $user = _user();
            $user_id = $user->get_logged_in_user_id();
            $user->set($user_id);
            if ($user->get_attribute("position")!="standing"){
                $result["action"]='OUTPUT';
                $result["data"]="You can't go...you're ".$user->get_attribute("position");
                return $result;
            }
            $location = _location();
            $user_loc = $location->get_user_location($user_id);
            $direction = $args[0];
            $move = $location->move($user_loc, $direction);
            
            if ($move['result'] == false){
                $result['action']='OUTPUT';
                $result['data']=$move['message'];
            }

            else{            
                $location->set($move['dest']);
                $result['action']='OUTPUT';
                if ($location->get_lighting()!="lit"){
                    $result['data']="It's dark.";
                }
                else{
                    $result['data'] = "<div style='color:red;font-weight:bold'>".$location->get_title().'</div>';
                    $people = $this->users_in_room($move['dest'],$user);
                    $exits = $this->get_exits();
                    $result['data'].="<div>".$people."</div>";
                    $result['data'].="<div>".$exits."</div>";
                }
                $this->register_event('depart',$user_id,$user_loc);
                $this->register_event('arrive',$user_id,$move['dest']);
            }
        }
        
        return $result;
    }
    
    protected function users_in_room($loc, $user){
         $user_id = $user->get_logged_in_user_id();
         $users = $user->get_users_in_location($loc, $user_id);
        
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
            
                if ($the_user->user_id==$user_id)
                    continue;
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
        
        return $people;
    }
    
    private function get_exits(){
        
        $directions = array("N"=>"North","E"=>"East","S"=>"South","W"=>"West");

        $location = _location();
        $user = _user();
        $user->set($user->get_logged_in_user_id()); 
        $location->set($user->get_location_id());
        $connections = $location->get_connections();
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
        
        return $exits;
    }
}
?>